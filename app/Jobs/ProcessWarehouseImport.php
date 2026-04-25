<?php

namespace App\Jobs;

use App\Models\WarehouseImportBatch;
use App\Models\WarehouseRecord;
use App\Models\WarehouseTabSchema;
use App\Models\ActivityLog;
use App\Models\Project;
use App\Models\ProjectBoqDetail;
use App\Models\UnifiedSubfase;
use App\Traits\HandlesPhaseStepper;
use App\Events\ImportStatusUpdated;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ProcessWarehouseImport implements ShouldQueue
{
    use Queueable, HandlesPhaseStepper;

    public $timeout = 600;

    public function __construct(
        public string $filePath,
        public string $originalFileName
    ) {}

    public function handle(): void
    {
        $reader = new \App\Support\StreamXlsxReader();
        $totalRows = 0;
        
        try {
            DB::beginTransaction();
            
            WarehouseRecord::query()->delete();
            WarehouseTabSchema::query()->delete();
            WarehouseImportBatch::query()->delete();

            $batch = WarehouseImportBatch::query()->create([
                'original_file_name' => $this->originalFileName,
                'total_rows' => 0,
                'imported_at' => now(),
            ]);

            $summaries = [];
            $affectedPids = [];

            foreach ($reader->readStream($this->filePath) as $worksheet) {
                $sheetName = trim($worksheet['name'] ?? '-');
                $tabKey = Str::of($sheetName)->lower()->replace(' ', '_')->value();
                $rowsGenerator = $worksheet['rows'];
                
                $headers = [];
                $processedCount = 0;
                $insertBuffer = [];

                foreach ($rowsGenerator as $rowIdx => $row) {
                    if (empty($headers)) {
                        if (count($row) > 3) {
                            $headers = $this->extractHeadersFromRow($row);
                            
                            WarehouseTabSchema::query()->create([
                                'batch_id' => $batch->id,
                                'tab_key' => $tabKey,
                                'tab_label' => Str::of($sheetName)->replace('_', ' ')->title()->value(),
                                'row_count' => 0,
                                'headers' => $headers,
                            ]);
                        }
                        continue;
                    }

                    $payload = [];
                    foreach ($headers as $header) {
                        $raw = (string) ($row[$header['col']] ?? '');
                        $payload[$header['key']] = $this->formatNumericValue($raw);
                    }

                    if (implode('', $payload) === '') continue;

                    $searchText = mb_strtolower(implode(' ', array_filter($payload)));
                    $pid = (string) ($payload['pid'] ?? $payload['project_id'] ?? '');
                    $designator = (string) ($payload['designator'] ?? '');
                    $vol = (float) ($payload['pemenuhan'] ?? $payload['out'] ?? 0);

                    if ($pid !== '' && $designator !== '') {
                        $summaries[$pid][$designator] = ($summaries[$pid][$designator] ?? 0) + $vol;
                        $affectedPids[$pid] = true;
                    }

                    $insertBuffer[] = [
                        'batch_id' => $batch->id,
                        'tab_key' => $tabKey,
                        'row_number' => ++$processedCount,
                        'project_id' => $pid ?: null,
                        'data' => json_encode($payload, JSON_UNESCAPED_UNICODE),
                        'search_text' => Str::limit($searchText, 2000),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];

                    if (count($insertBuffer) >= 500) {
                        DB::table('warehouse_records')->insert($insertBuffer);
                        $insertBuffer = [];
                    }
                }

                if (!empty($insertBuffer)) {
                    DB::table('warehouse_records')->insert($insertBuffer);
                }

                $totalRows += $processedCount;
                WarehouseTabSchema::where('batch_id', $batch->id)->where('tab_key', $tabKey)
                    ->update(['row_count' => $processedCount]);
            }

            if ($affectedPids !== []) {
                $pids = array_keys($affectedPids);
                ProjectBoqDetail::join('projects', 'project_boq_details.project_id', '=', 'projects.id')
                    ->whereIn('projects.pid', $pids)
                    ->update(['volume_pemenuhan' => 0]);

                foreach ($summaries as $pid => $items) {
                    $project = Project::where('pid', $pid)->first();
                    if ($project) {
                        foreach ($items as $designator => $totalVol) {
                            ProjectBoqDetail::where('project_id', $project->id)
                                ->where('designator', $designator)
                                ->update(['volume_pemenuhan' => $totalVol]);
                        }
                        UnifiedSubfase::updateOrCreate(
                            ['faseable_id' => $project->id, 'faseable_type' => Project::class, 'subfase_key' => 'warehouse_aktual'],
                            ['status' => 'selesai']
                        );
                        $this->clearStepperCache($project);
                    }
                }
            }

            $batch->update(['total_rows' => $totalRows]);
            
            ActivityLog::create([
                'module' => 'warehouse',
                'action' => 'import',
                'target_type' => 'ImportBatch',
                'description' => "Import data Warehouse berhasil: {$this->originalFileName} (" . number_format($totalRows, 0, ',', '.') . " baris)",
                'metadata' => ['file_name' => $this->originalFileName, 'total_rows' => $totalRows]
            ]);

            DB::commit();
            if (file_exists($this->filePath)) unlink($this->filePath);
            
            event(new ImportStatusUpdated('warehouse', 'success', "Import Warehouse berhasil: {$totalRows} baris data diproses."));

        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Warehouse streaming import failed', ['message' => $e->getMessage()]);
            event(new ImportStatusUpdated('warehouse', 'error', "Import Warehouse gagal: " . $e->getMessage()));
        }
    }

    private function extractHeadersFromRow(array $row): array
    {
        $headers = [];
        foreach ($row as $col => $val) {
            $label = trim((string)$val);
            if ($label === '') continue;
            $key = Str::of($label)->lower()->replace([' ', '&'], ['_', 'and'])->replaceMatches('/[^a-z0-9_]+/', '')->value();
            $headers[] = ['key' => $key, 'label' => $label, 'col' => $col];
        }
        return $headers;
    }

    private function formatNumericValue(string $value): string
    {
        $value = trim($value);
        if ($value === '') return '';
        $value = html_entity_decode($value, ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML5);
        if (preg_match('/^-?\d+(\.\d+)?[eE][-+]?\d+$/', $value)) {
            $floatVal = (float) $value;
            if ($floatVal == floor($floatVal)) return sprintf('%.0f', $floatVal);
            $formatted = sprintf('%f', $floatVal);
            return rtrim(rtrim($formatted, '0'), '.');
        }
        if (preg_match('/^-?\d+\.0+$/', $value)) return (string) explode('.', $value)[0];
        return $value;
    }
}
