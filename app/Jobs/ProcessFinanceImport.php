<?php

namespace App\Jobs;

use App\Models\FinanceImportBatch;
use App\Models\FinanceRecord;
use App\Models\FinanceTabSchema;
use App\Models\ActivityLog;
use App\Events\ImportStatusUpdated;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ProcessFinanceImport implements ShouldQueue
{
    use Queueable;

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
            
            FinanceRecord::query()->delete();
            FinanceTabSchema::query()->delete();
            FinanceImportBatch::query()->delete();

            $batch = FinanceImportBatch::query()->create([
                'original_file_name' => $this->originalFileName,
                'total_rows' => 0,
                'imported_at' => now(),
            ]);

            foreach ($reader->readStream($this->filePath) as $worksheet) {
                $sheetName = trim($worksheet['name'] ?? '-');
                $normalizedName = Str::of($sheetName)->lower()->replace(' ', '_')->value();
                
                if (!str_contains($normalizedName, 'finance') || str_contains($normalizedName, 'detail')) {
                    continue;
                }

                $tabKey = $normalizedName;
                $rowsGenerator = $worksheet['rows'];
                
                $headers = [];
                $processedCount = 0;
                $insertBuffer = [];

                foreach ($rowsGenerator as $rowIdx => $row) {
                    if (empty($headers)) {
                        if (count($row) > 3) {
                            $headers = $this->extractHeadersFromRow($row);
                            
                            FinanceTabSchema::query()->create([
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
                    $projectId = $payload['project_id'] ?? null;

                    $insertBuffer[] = [
                        'batch_id' => $batch->id,
                        'tab_key' => $tabKey,
                        'row_number' => ++$processedCount,
                        'project_id' => $projectId,
                        'data' => json_encode($payload, JSON_UNESCAPED_UNICODE),
                        'search_text' => Str::limit($searchText, 2000),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];

                    if (count($insertBuffer) >= 500) {
                        DB::table('finance_records')->insert($insertBuffer);
                        $insertBuffer = [];
                    }
                }

                if (!empty($insertBuffer)) {
                    DB::table('finance_records')->insert($insertBuffer);
                }

                $totalRows += $processedCount;
                FinanceTabSchema::where('batch_id', $batch->id)->where('tab_key', $tabKey)
                    ->update(['row_count' => $processedCount]);
            }

            $batch->update(['total_rows' => $totalRows]);
            
            ActivityLog::create([
                'module' => 'finance',
                'action' => 'import',
                'target_type' => 'ImportBatch',
                'description' => "Import data Finance berhasil: {$this->originalFileName} (" . number_format($totalRows, 0, ',', '.') . " baris)",
                'metadata' => ['file_name' => $this->originalFileName, 'total_rows' => $totalRows]
            ]);

            DB::commit();
            if (file_exists($this->filePath)) unlink($this->filePath);
            cache()->forget('finance_index_data');
            
            event(new ImportStatusUpdated('finance', 'success', "Import Finance berhasil: {$totalRows} baris data diproses."));

        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Finance streaming import failed', ['message' => $e->getMessage()]);
            event(new ImportStatusUpdated('finance', 'error', "Import Finance gagal: " . $e->getMessage()));
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
