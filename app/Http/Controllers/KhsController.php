<?php

namespace App\Http\Controllers;

use App\Http\Requests\ImportKhsRequest;
use App\Models\KhsImportBatch;
use App\Models\ActivityLog;
use App\Models\KhsRecord;
use App\Models\KhsTabSchema;
use App\Support\SimpleXlsxReader;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use RuntimeException;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Yajra\DataTables\Facades\DataTables;

class KhsController extends Controller
{
    public function downloadTemplate(Request $request): \Symfony\Component\HttpFoundation\Response
    {
        if ($request->query('format') === 'csv') {
            $headers = ['No', 'Designator', 'Designator Material', 'Designator Jasa', 'Uraian Pekerjaan', 'Satuan', 'Paket 5 Material', 'Paket 5 Jasa'];
            $data = ['1', 'OSP.01.01', 'DM-01', 'DJ-01', 'Pekerjaan Galian Tanah', 'M', '0', '45500'];
            
            $callback = function() use ($headers, $data) {
                $file = fopen('php://output', 'w');
                fputcsv($file, $headers);
                fputcsv($file, $data);
                fclose($file);
            };

            return response()->stream($callback, 200, [
                "Content-type"        => "text/csv",
                "Content-Disposition" => "attachment; filename=template-khs-ospfo.csv",
                "Pragma"              => "no-cache",
                "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
                "Expires"             => "0"
            ]);
        }

        $templatePath = storage_path('app/templates/khs-ospfo-template.xlsx');

        abort_unless(file_exists($templatePath), 404, 'Template OSP-FO tidak ditemukan.');

        return response()->download($templatePath, 'template-khs-ospfo.xlsx');
    }

    public function index(): View
    {
        if (! $this->hasKhsTables()) {
            return view('khs', [
                'latestBatch' => null,
                'tab' => null,
                'setupError' => 'Tabel KHS belum tersedia. Jalankan migration terbaru terlebih dahulu.',
            ]);
        }

        $latestBatch = KhsImportBatch::query()
            ->latest('id')
            ->first();

        $tab = null;
        if ($latestBatch) {
            $schema = KhsTabSchema::query()
                ->where('batch_id', $latestBatch->id)
                ->where('tab_key', 'osp_fo')
                ->first();

            if ($schema) {
                $tab = [
                    'key' => $schema->tab_key,
                    'label' => $schema->tab_label,
                    'row_count' => $schema->row_count,
                    'headers' => $schema->headers ?? [],
                ];
            }
        }

        return view('khs', [
            'latestBatch' => $latestBatch,
            'tab' => $tab,
            'setupError' => null,
        ]);
    }

    public function data(Request $request): JsonResponse
    {
        if (! $this->hasKhsTables()) {
            return DataTables::collection(collect())->toJson();
        }

        $tabKey = 'osp_fo';

        $schema = KhsTabSchema::query()
            ->where('tab_key', $tabKey)
            ->latest('batch_id')
            ->first();

        if (! $schema) {
            return DataTables::collection(collect())->toJson();
        }

        $headerKeys = collect($schema->headers ?? [])
            ->pluck('key')
            ->filter(fn ($value) => is_string($value) && $value !== '')
            ->values()
            ->all();

        $query = KhsRecord::query()
            ->where('batch_id', $schema->batch_id)
            ->where('tab_key', $tabKey)
            ->select(['id', 'row_number', 'data']);

        return DataTables::eloquent($query)
            ->filter(function ($builder) use ($request) {
                $keyword = mb_strtolower(trim((string) $request->input('search.value', '')));

                if ($keyword !== '') {
                    $builder->where('search_text', 'like', '%'.$keyword.'%');
                }
            }, true)
            ->addColumn('cells', function (KhsRecord $record) use ($headerKeys) {
                $data = is_array($record->data) ? $record->data : [];
                $cells = [];

                foreach ($headerKeys as $key) {
                    $cells[$key] = isset($data[$key]) ? (string) $data[$key] : '';
                }

                return $cells;
            })
            ->editColumn('row_number', fn (KhsRecord $record) => $record->row_number)
            ->toJson();
    }

    public function import(ImportKhsRequest $request): RedirectResponse
    {
        if (! $this->hasKhsTables()) {
            throw ValidationException::withMessages([
                'khs_file' => 'Import gagal: tabel KHS belum tersedia. Jalankan migration terbaru lalu coba lagi.',
            ]);
        }

        $file = $request->file('khs_file');
        $extension = strtolower($file->getClientOriginalExtension());
        
        if ($extension === 'csv') {
            $reader = new \App\Support\SimpleCsvReader();
        } else {
            $reader = new SimpleXlsxReader();
        }

        Log::info('KHS import started', [
            'name' => $file?->getClientOriginalName(),
            'size' => $file?->getSize(),
            'mime' => $file?->getClientMimeType(),
            'path' => $file?->getRealPath(),
        ]);

        try {
            $worksheets = $reader->read($file->getRealPath());
            Log::info('KHS import workbook parsed', [
                'sheet_count' => count($worksheets),
                'sheet_names' => array_map(
                    static fn (array $sheet) => $sheet['name'] ?? '-',
                    $worksheets
                ),
            ]);
        } catch (RuntimeException $exception) {
            Log::error('KHS import failed while reading workbook', [
                'message' => $exception->getMessage(),
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
            ]);

            throw ValidationException::withMessages([
                'khs_file' => $exception->getMessage(),
            ]);
        } catch (\Throwable $exception) {
            Log::error('KHS import unexpected parser error', [
                'message' => $exception->getMessage(),
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
                'trace' => $exception->getTraceAsString(),
            ]);

            throw ValidationException::withMessages([
                'khs_file' => 'Import gagal karena error parser. Cek laravel.log untuk detail debug.',
            ]);
        }

        $parsedTabs = [];
        $totalRows = 0;

        foreach ($worksheets as $worksheet) {
            $tabMeta = $this->detectTabMeta($worksheet['name']);
            Log::info('KHS import sheet processing', [
                'sheet_name' => $worksheet['name'] ?? '-',
                'detected_tab' => $tabMeta['key'] ?? null,
                'raw_row_count' => count($worksheet['rows'] ?? []),
            ]);

            if ($tabMeta === null) {
                continue;
            }

            $parsed = $this->parseWorksheetRows($worksheet['rows']);
            Log::info('KHS import sheet parsed', [
                'sheet_name' => $worksheet['name'] ?? '-',
                'tab_key' => $tabMeta['key'],
                'header_count' => count($parsed['headers'] ?? []),
                'data_row_count' => count($parsed['rows'] ?? []),
            ]);

            if ($parsed['rows'] === []) {
                continue;
            }

            $tabKey = $tabMeta['key'];
            if (! isset($parsedTabs[$tabKey])) {
                $parsedTabs[$tabKey] = [
                    'label' => $tabMeta['label'],
                    'headers' => $parsed['headers'],
                    'rows' => [],
                ];
            }

            $existingHeaderKeys = collect($parsedTabs[$tabKey]['headers'])->pluck('key')->all();
            foreach ($parsed['headers'] as $header) {
                if (! in_array($header['key'], $existingHeaderKeys, true)) {
                    $parsedTabs[$tabKey]['headers'][] = $header;
                    $existingHeaderKeys[] = $header['key'];
                }
            }

            foreach ($parsed['rows'] as $row) {
                $parsedTabs[$tabKey]['rows'][] = $row;
                $totalRows++;
            }
        }

        if ($parsedTabs === []) {
            Log::warning('KHS import parsed tabs empty', [
                'sheet_count' => count($worksheets),
            ]);

            throw ValidationException::withMessages([
                'khs_file' => 'Tidak ada sheet OSP-FO yang terdeteksi dari file ini.',
            ]);
        }

        DB::transaction(function () use ($parsedTabs, $file, $totalRows): void {
            KhsRecord::query()->delete();
            KhsTabSchema::query()->delete();
            KhsImportBatch::query()->delete();

            $batch = KhsImportBatch::query()->create([
                'original_file_name' => $file->getClientOriginalName(),
                'total_rows' => $totalRows,
                'imported_at' => now(),
            ]);

            foreach ($parsedTabs as $tabKey => $tab) {
                $headers = $tab['headers'];
                $rows = $tab['rows'];

                KhsTabSchema::query()->create([
                    'batch_id' => $batch->id,
                    'tab_key' => $tabKey,
                    'tab_label' => $tab['label'],
                    'row_count' => count($rows),
                    'headers' => $headers,
                ]);

                $insertRows = [];
                $lineNumber = 1;

                foreach ($rows as $row) {
                    $payload = [];
                    foreach ($headers as $header) {
                        $key = $header['key'];
                        $payload[$key] = (string) ($row[$key] ?? '');
                    }

                    $searchText = mb_strtolower(
                        implode(' ', array_filter(array_map(
                            fn ($value) => trim((string) $value),
                            $payload
                        )))
                    );

                    $insertRows[] = [
                        'batch_id' => $batch->id,
                        'tab_key' => $tabKey,
                        'row_number' => $lineNumber++,
                        'data' => json_encode($payload, JSON_UNESCAPED_UNICODE),
                        'search_text' => $searchText,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }

                foreach (array_chunk($insertRows, 500) as $chunk) {
                    DB::table('khs_records')->insert($chunk);
                }

                ActivityLog::create([
                    'user_id' => auth()->id(),
                    'module' => 'khs',
                    'action' => 'import',
                    'target_type' => 'ImportBatch',
                    'target_id' => $batch->id,
                    'description' => "Import data KHS berhasil: {$file->getClientOriginalName()} (" . number_format($totalRows, 0, ',', '.') . " baris)",
                    'metadata' => [
                        'file_name' => $file->getClientOriginalName(),
                        'total_rows' => $totalRows,
                    ]
                ]);
            }
        });

        Log::info('KHS import completed', [
            'tab_count' => count($parsedTabs),
            'total_rows' => $totalRows,
        ]);

        return redirect()
            ->route('khs')
            ->with('status', 'Import KHS berhasil. Database KHS sudah diganti dengan data terbaru.');
    }

    /**
     * @param  string  $sheetName
     * @return array{key: string, label: string}|null
     */
    private function detectTabMeta(string $sheetName): ?array
    {
        if ($sheetName === 'OSP-FO') {
            return ['key' => 'osp_fo', 'label' => 'OSP-FO'];
        }

        $normalized = Str::of($sheetName)
            ->lower()
            ->replace('&', ' ')
            ->replace('-', ' ')
            ->replace('/', ' ')
            ->replaceMatches('/\s+/', ' ')
            ->trim()
            ->value();

        return match (true) {
            str_contains($normalized, 'osp')
                || str_contains($normalized, 'osp fo')
                || str_contains($normalized, 'outside plant')
                || str_contains($normalized, 'fiber optik osp') => ['key' => 'osp_fo', 'label' => 'OSP-FO'],
            default => null,
        };
    }

    /**
     * @param  array<int, array<int, string>>  $rows
     * @return array{headers: array<int, array{key: string, label: string}>, rows: array<int, array<string, string>>}
     */
    private function parseWorksheetRows(array $rows): array
    {
        if ($rows === []) {
            return ['headers' => [], 'rows' => []];
        }

        $headerRowIndex = $this->detectHeaderRowIndex($rows);
        if ($headerRowIndex === null) {
            return ['headers' => [], 'rows' => []];
        }

        $headerRow = $rows[$headerRowIndex] ?? [];
        $subHeaderRow = $rows[$headerRowIndex + 1] ?? [];
        $useTwoRows = $this->isLikelySubHeaderRow($subHeaderRow);
        $headers = $this->buildHeaders($headerRow, $subHeaderRow, $useTwoRows);

        if ($headers === []) {
            return ['headers' => [], 'rows' => []];
        }

        $startRowIndex = $headerRowIndex + ($useTwoRows ? 2 : 1);
        $dataRows = [];
        $currentCategory = '';
        $currentRegion = '';

        for ($index = $startRowIndex; $index < count($rows); $index++) {
            $row = $rows[$index] ?? [];
            if ($this->isRowEmpty($row)) {
                continue;
            }

            $rawValues = $this->extractRowValues($row, $headers);
            if ($this->isRepeatingHeader($rawValues, $headers)) {
                continue;
            }

            $markerValues = array_values(array_filter(array_map(
                fn ($value) => trim((string) $value),
                $rawValues
            )));

            if (count($markerValues) === 1 && str_contains(mb_strtolower($markerValues[0]), 'pekerjaan pengadaan')) {
                continue;
            }

            if ($this->isCategoryMarkerRow($markerValues)) {
                $currentCategory = $markerValues[1];
                continue;
            }

            if ($this->isRegionMarkerRow($markerValues)) {
                $currentRegion = $markerValues[0];
                continue;
            }

            $payload = [];
            foreach ($headers as $header) {
                $value = trim((string) ($rawValues[$header['key']] ?? ''));
                $payload[$header['key']] = $value;
            }

            if ($currentCategory !== '' && ! array_key_exists('kategori', $payload)) {
                $payload['kategori'] = $currentCategory;
            }

            if ($currentRegion !== '' && ! array_key_exists('region', $payload)) {
                $payload['region'] = $currentRegion;
            }

            if (implode('', $payload) === '') {
                continue;
            }

            $dataRows[] = $payload;
        }

        $headersWithContext = $headers;

        if ($currentCategory !== '' && ! collect($headersWithContext)->contains(fn ($header) => $header['key'] === 'kategori')) {
            $headersWithContext[] = ['key' => 'kategori', 'label' => 'Kategori'];
        }

        if ($currentRegion !== '' && ! collect($headersWithContext)->contains(fn ($header) => $header['key'] === 'region')) {
            $headersWithContext[] = ['key' => 'region', 'label' => 'Region'];
        }

        return [
            'headers' => array_values($headersWithContext),
            'rows' => $dataRows,
        ];
    }

    /**
     * @param  array<int, array<int, string>>  $rows
     */
    private function detectHeaderRowIndex(array $rows): ?int
    {
        $maxRowsToInspect = min(count($rows), 40);
        $bestIndex = null;
        $bestCount = 0;

        for ($i = 0; $i < $maxRowsToInspect; $i++) {
            $nonEmpty = $this->nonEmptyCount($rows[$i] ?? []);

            if ($nonEmpty > $bestCount) {
                $bestCount = $nonEmpty;
                $bestIndex = $i;
            }
        }

        if ($bestIndex === null || $bestCount < 2) {
            return null;
        }

        return $bestIndex;
    }

    /**
     * @param  array<int, string>  $row
     */
    private function nonEmptyCount(array $row): int
    {
        $count = 0;
        foreach ($row as $value) {
            if (trim((string) $value) !== '') {
                $count++;
            }
        }

        return $count;
    }

    /**
     * @param  array<int, string>  $row
     */
    private function isLikelySubHeaderRow(array $row): bool
    {
        $values = array_values(array_filter(array_map(
            fn ($value) => mb_strtolower(trim((string) $value)),
            $row
        )));

        if ($values === []) {
            return false;
        }

        $allowedTokens = ['material', 'jasa', 'total', 'perizinan'];
        $allowedCount = 0;
        $numericCount = 0;

        foreach ($values as $value) {
            if (in_array($value, $allowedTokens, true)) {
                $allowedCount++;
            }

            if (preg_match('/^-?[0-9][0-9\.,]*$/', $value) === 1) {
                $numericCount++;
            }
        }

        // Jika ada angka, sangat besar kemungkinan ini sudah masuk baris data.
        if ($numericCount > 0) {
            return false;
        }

        // Sub-header valid minimal berisi 2 token (contoh: Material/Jasa).
        if ($allowedCount < 2) {
            return false;
        }

        // Mayoritas isi baris harus token sub-header.
        return ($allowedCount / count($values)) >= 0.6;
    }

    /**
     * @param  array<int, string>  $headerRow
     * @param  array<int, string>  $subHeaderRow
     * @return array<int, array{key: string, label: string, col: int}>
     */
    private function buildHeaders(array $headerRow, array $subHeaderRow, bool $useTwoRows): array
    {
        $maxCol = max(
            $headerRow === [] ? 1 : max(array_keys($headerRow)),
            $subHeaderRow === [] ? 1 : max(array_keys($subHeaderRow))
        );

        $headers = [];
        $usedKeys = [];
        $lastTop = '';

        for ($col = 1; $col <= $maxCol; $col++) {
            $top = trim((string) ($headerRow[$col] ?? ''));
            $sub = trim((string) ($subHeaderRow[$col] ?? ''));

            if ($top !== '') {
                $lastTop = $top;
            } elseif ($useTwoRows && $sub !== '' && $lastTop !== '') {
                $top = $lastTop;
            }

            $label = $useTwoRows
                ? trim(implode(' ', array_filter([$top, $sub])))
                : $top;

            $label = $this->normalizeHeaderLabel($label);

            if ($label === '') {
                continue;
            }

            $key = $this->headerToKey($label);
            if ($key === '') {
                continue;
            }

            $originalKey = $key;
            $counter = 2;
            while (isset($usedKeys[$key])) {
                $key = $originalKey.'_'.$counter++;
            }
            $usedKeys[$key] = true;

            $headers[] = [
                'key' => $key,
                'label' => $label,
                'col' => $col,
            ];
        }

        return $headers;
    }

    private function normalizeHeaderLabel(string $label): string
    {
        $normalized = str_replace(["\n", "\r", "\t"], ' ', $label);
        $normalized = preg_replace('/\s+/', ' ', $normalized) ?? '';
        $normalized = trim($normalized);

        if (strtolower($normalized) === 'n') {
            return 'No';
        }

        return $normalized;
    }

    private function headerToKey(string $label): string
    {
        $normalized = Str::of($label)->lower()->replace('&', ' dan ')->value();
        $normalized = preg_replace('/[^a-z0-9]+/', '_', $normalized) ?? '';
        $normalized = trim($normalized, '_');

        return match ($normalized) {
            'n' => 'no',
            'harga_paket_5_p_jawa' => 'harga_paket_5_p_jawa',
            default => $normalized,
        };
    }

    /**
     * @param  array<int, string>  $row
     * @param  array<int, array{key: string, label: string, col: int}>  $headers
     * @return array<string, string>
     */
    private function extractRowValues(array $row, array $headers): array
    {
        $values = [];

        foreach ($headers as $header) {
            $value = trim((string) ($row[$header['col']] ?? ''));
            $values[$header['key']] = $value;
        }

        return $values;
    }

    /**
     * @param  array<string, string>  $values
     * @param  array<int, array{key: string, label: string, col: int}>  $headers
     */
    private function isRepeatingHeader(array $values, array $headers): bool
    {
        $matchCount = 0;
        $total = 0;

        foreach ($headers as $header) {
            $key = $header['key'];
            $cellValue = mb_strtolower(trim((string) ($values[$key] ?? '')));
            if ($cellValue === '') {
                continue;
            }

            $total++;
            $headerValue = mb_strtolower(trim($header['label']));
            if ($cellValue === $headerValue) {
                $matchCount++;
            }
        }

        return $total > 0 && ($matchCount / $total) >= 0.6;
    }

    /**
     * @param  array<int, string>  $values
     */
    private function isCategoryMarkerRow(array $values): bool
    {
        if (count($values) < 2) {
            return false;
        }

        $first = mb_strtolower(trim($values[0] ?? ''));
        $second = mb_strtolower(trim($values[1] ?? ''));

        return in_array($first, ['a', 'b', 'c'], true)
            && in_array($second, ['jasa', 'material'], true);
    }

    /**
     * @param  array<int, string>  $values
     */
    private function isRegionMarkerRow(array $values): bool
    {
        if (count($values) !== 1) {
            return false;
        }

        $value = mb_strtolower(trim($values[0] ?? ''));

        return str_contains($value, 'jabo')
            || str_contains($value, 'jabar')
            || str_contains($value, 'jateng')
            || str_contains($value, 'jatim');
    }

    /**
     * @param  array<int, string>  $row
     */
    private function isRowEmpty(array $row): bool
    {
        foreach ($row as $value) {
            if (trim((string) $value) !== '') {
                return false;
            }
        }

        return true;
    }

    private function hasKhsTables(): bool
    {
        return Schema::hasTable('khs_import_batches')
            && Schema::hasTable('khs_tab_schemas')
            && Schema::hasTable('khs_records');
    }

    public function store(Request $request): JsonResponse
    {
        if (! $this->hasKhsTables()) {
            return response()->json(['message' => 'Tabel KHS belum tersedia.'], 400);
        }

        $latestBatch = KhsImportBatch::latest('id')->first();
        if (!$latestBatch) {
            return response()->json(['message' => 'Belum ada batch KHS. Harap import template terlebih dahulu.'], 400);
        }

        $tabKey = 'osp_fo';

        $payload = $request->except(['_token', '_method']);
        
        $maxRow = KhsRecord::where('batch_id', $latestBatch->id)
            ->where('tab_key', $tabKey)
            ->max('row_number') ?? 0;

        $searchText = mb_strtolower(
            implode(' ', array_filter(array_map(
                fn ($value) => trim((string) $value),
                $payload
            )))
        );

        $record = KhsRecord::create([
            'batch_id' => $latestBatch->id,
            'tab_key' => $tabKey,
            'row_number' => $maxRow + 1,
            'data' => $payload, // mutator takes array and saves as json
            'search_text' => $searchText,
        ]);

        // increment row_count in schema if exists
        KhsTabSchema::where('batch_id', $latestBatch->id)
            ->where('tab_key', $tabKey)
            ->increment('row_count');

        ActivityLog::create([
            'user_id' => auth()->id(),
            'module' => 'khs',
            'action' => 'create_record',
            'target_type' => 'KhsRecord',
            'target_id' => $record->id,
            'description' => "Menambahkan baris KHS OSP-FO secara manual.",
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Data KHS berhasil ditambahkan.',
            'data' => $record
        ]);
    }

    public function update(Request $request, KhsRecord $record): JsonResponse
    {
        $payload = $request->except(['_token', '_method']);
        
        $currentData = is_array($record->data) ? $record->data : [];
        $mergedData = array_merge($currentData, $payload);

        $searchText = mb_strtolower(
            implode(' ', array_filter(array_map(
                fn ($value) => trim((string) $value),
                $mergedData
            )))
        );

        $record->update([
            'data' => $mergedData,
            'search_text' => $searchText,
        ]);

        ActivityLog::create([
            'user_id' => auth()->id(),
            'module' => 'khs',
            'action' => 'update_record',
            'target_type' => 'KhsRecord',
            'target_id' => $record->id,
            'description' => "Memperbarui baris KHS OSP-FO secara manual.",
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Data KHS berhasil diperbarui.',
            'data' => $record
        ]);
    }

    public function destroy(KhsRecord $record): JsonResponse
    {
        $batchId = $record->batch_id;
        $tabKey = $record->tab_key;
        
        $record->delete();

        // decrement row_count
        KhsTabSchema::where('batch_id', $batchId)
            ->where('tab_key', $tabKey)
            ->decrement('row_count');

        ActivityLog::create([
            'user_id' => auth()->id(),
            'module' => 'khs',
            'action' => 'delete_record',
            'target_type' => 'KhsRecord',
            'target_id' => $record->id,
            'description' => "Menghapus baris KHS OSP-FO secara manual.",
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Data KHS berhasil dihapus.'
        ]);
    }
}
