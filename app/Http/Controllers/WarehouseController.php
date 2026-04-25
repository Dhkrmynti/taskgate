<?php

namespace App\Http\Controllers;

use App\Models\WarehouseImportBatch;
use App\Models\WarehouseRecord;
use App\Models\WarehouseTabSchema;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Yajra\DataTables\Facades\DataTables;

class WarehouseController extends Controller
{
    public function downloadTemplate(): \Symfony\Component\HttpFoundation\Response
    {
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Template Import Warehouse');

        $headers = ['PID', 'Nama Project', 'Designator', 'Out', 'Return', 'Pemenuhan'];
        
        // Styling headers
        foreach ($headers as $index => $label) {
            $col = chr(65 + $index);
            $sheet->setCellValue($col . '1', $label);
            
            // Premium KHS-like Style: Blue Background, White Bold Text
            $style = $sheet->getStyle($col . '1');
            $style->getFont()->setBold(true)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('FFFFFF'));
            $style->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('4472C4');
            $style->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // Add dummy data for clarity
        $sheet->setCellValue('A2', 'PID2026-001');
        $sheet->setCellValue('B2', 'Project Example A');
        $sheet->setCellValue('C2', 'DM-01');
        $sheet->setCellValue('D2', 10);
        $sheet->setCellValue('E2', 1);
        $sheet->setCellValue('F2', 9);

        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $tempFile = tempnam(sys_get_temp_dir(), 'wh_tpl');
        $writer->save($tempFile);

        return response()->download($tempFile, 'template-import-warehouse.xlsx')->deleteFileAfterSend(true);
    }


    public function index(Request $request): View
    {
        if (! $this->hasWarehouseTables()) {
            return view('warehouse', [
                'latestBatch' => null,
                'tabs' => collect(),
                'activeTab' => '',
                'setupError' => 'Tabel Warehouse belum tersedia. Jalankan migration terbaru terlebih dahulu.',
            ]);
        }

        $cacheKey = 'warehouse_index_data';
        $viewData = cache()->remember($cacheKey, now()->addHour(), function () {
            /** @var WarehouseImportBatch|null $latestBatch */
            $latestBatch = WarehouseImportBatch::query()
                ->with(['tabs' => fn ($query) => $query->orderBy('tab_label')])
                ->latest('id')
                ->first();

            $tabs = $latestBatch
                ? $latestBatch->tabs->map(fn (WarehouseTabSchema $tab) => [
                    'key' => $tab->tab_key,
                    'label' => Str::of($tab->tab_label)->replace('_', ' ')->title()->value(),
                    'row_count' => $tab->row_count,
                    'headers' => $tab->headers ?? [],
                ])->values()
                : collect();

            return [
                'latestBatch' => $latestBatch,
                'tabs' => $tabs,
            ];
        });

        $tabs = $viewData['tabs'];
        $activeTab = (string) $request->query('tab');
        if ($activeTab === '' || ! $tabs->contains(fn (array $t) => $t['key'] === $activeTab)) {
            $activeTab = (string) ($tabs->first()['key'] ?? '');
        }

        return view('warehouse', [
            'latestBatch' => $viewData['latestBatch'],
            'tabs' => $tabs,
            'activeTab' => $activeTab,
            'setupError' => null,
            'importing' => session('importing', false),
        ]);
    }

    public function data(Request $request): JsonResponse
    {
        if (! $this->hasWarehouseTables()) {
            return DataTables::collection(collect())->toJson();
        }

        $tabKey = trim((string) $request->query('tab'));

        if ($tabKey === '') {
            return DataTables::collection(collect())->toJson();
        }

        /** @var WarehouseTabSchema|null $schema */
        $schema = WarehouseTabSchema::query()
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

        $query = WarehouseRecord::query()
            ->where('batch_id', $schema->batch_id)
            ->where('tab_key', $tabKey)
            ->select(['id', 'row_number', 'data']);

        return DataTables::eloquent($query)
            ->filter(function ($builder) use ($request) {
                $keyword = trim((string) $request->input('search.value', ''));
                if ($keyword !== '') {
                    $builder->whereFullText('search_text', $keyword);
                }
            }, true)
            ->addColumn('cells', function (WarehouseRecord $record) use ($headerKeys) {
                $data = $record->data;
                $cells = [];

                foreach ($headerKeys as $key) {
                    $keyStr = (string)$key;
                    $cells[$keyStr] = isset($data[$keyStr]) ? (string) $data[$keyStr] : '';
                }

                return $cells;
            })
            ->editColumn('row_number', fn (WarehouseRecord $record) => $record->row_number)
            ->toJson();
    }

    public function import(Request $request): RedirectResponse
    {
        $request->validate([
            'warehouse_file' => 'required|file|mimes:xlsx,xls|max:20480', // 20MB
        ]);

        if (! $this->hasWarehouseTables()) {
            throw ValidationException::withMessages([
                'warehouse_file' => 'Import gagal: tabel Warehouse belum tersedia. Jalankan migration terbaru lalu coba lagi.',
            ]);
        }

        $file = $request->file('warehouse_file');
        $originalName = $file->getClientOriginalName();
        
        $tempPath = $file->storeAs('temp', 'warehouse_' . time() . '_' . Str::random(5) . '.' . $file->getClientOriginalExtension());
        $fullPath = storage_path('app/private/' . $tempPath);

        \App\Jobs\ProcessWarehouseImport::dispatch($fullPath, $originalName);

        cache()->forget('warehouse_index_data');

        return redirect()
            ->route('warehouse.index')
            ->with('status', 'File diterima dan sedang diproses di latar belakang. Silakan refresh halaman dalam beberapa saat.')
            ->with('importing', true);
    }

    private function hasWarehouseTables(): bool
    {
        return Schema::hasTable('warehouse_import_batches')
            && Schema::hasTable('warehouse_tab_schemas')
            && Schema::hasTable('warehouse_records');
    }
}
