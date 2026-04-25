<?php

namespace App\Http\Controllers;

use App\Models\FinanceImportBatch;
use App\Models\FinanceRecord;
use App\Models\FinanceTabSchema;
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
use Yajra\DataTables\Facades\DataTables;

class FinanceController extends Controller
{
    public function index(Request $request): View
    {
        if (! $this->hasFinanceTables()) {
            return view('finance', [
                'latestBatch' => null,
                'tabs' => collect(),
                'activeTab' => '',
                'setupError' => 'Tabel Finance belum tersedia. Jalankan migration terbaru terlebih dahulu.',
            ]);
        }

        // Caching the latest batch and tabs for 1 hour, or until a new import clears it
        $cacheKey = 'finance_index_data';
        $viewData = cache()->remember($cacheKey, now()->addHour(), function () {
            /** @var FinanceImportBatch|null $latestBatch */
            $latestBatch = FinanceImportBatch::query()
                ->with(['tabs' => fn ($query) => $query->orderBy('tab_label')])
                ->latest('id')
                ->first();

            $tabs = $latestBatch
                ? $latestBatch->tabs
                    ->filter(fn (FinanceTabSchema $tab) => !Str::contains(Str::lower($tab->tab_label), 'detail'))
                    ->map(fn (FinanceTabSchema $tab) => [
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

        return view('finance', [
            'latestBatch' => $viewData['latestBatch'],
            'tabs' => $tabs,
            'activeTab' => $activeTab,
            'setupError' => null,
            'importing' => session('importing', false),
        ]);
    }

    public function data(Request $request): JsonResponse
    {
        if (! $this->hasFinanceTables()) {
            return DataTables::collection(collect())->toJson();
        }

        $tabKey = trim((string) $request->query('tab'));

        if ($tabKey === '') {
            return DataTables::collection(collect())->toJson();
        }

        /** @var FinanceTabSchema|null $schema */
        $schema = FinanceTabSchema::query()
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

        $query = FinanceRecord::query()
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
            ->addColumn('cells', function (FinanceRecord $record) use ($headerKeys) {
                $data = $record->data;
                $cells = [];

                foreach ($headerKeys as $key) {
                    $cells[$key] = isset($data[$key]) ? (string) $data[$key] : '';
                }

                return $cells;
            })
            ->editColumn('row_number', fn (FinanceRecord $record) => $record->row_number)
            ->toJson();
    }

    public function import(Request $request): RedirectResponse
    {
        $request->validate([
            'finance_file' => 'required|file|mimes:xlsx,xls|max:20480', // 20MB
        ]);

        if (! $this->hasFinanceTables()) {
            throw ValidationException::withMessages([
                'finance_file' => 'Import gagal: tabel Finance belum tersedia. Jalankan migration terbaru lalu coba lagi.',
            ]);
        }

        $file = $request->file('finance_file');
        $originalName = $file->getClientOriginalName();
        
        // Save file to storage/app/temp
        $tempPath = $file->storeAs('temp', 'finance_' . time() . '_' . Str::random(5) . '.' . $file->getClientOriginalExtension());
        $fullPath = storage_path('app/private/' . $tempPath);

        // Dispatch background job
        \App\Jobs\ProcessFinanceImport::dispatch($fullPath, $originalName);

        // Clear cache
        cache()->forget('finance_index_data');

        return redirect()
            ->route('finance.index')
            ->with('status', 'File diterima dan sedang diproses di latar belakang. Silakan refresh halaman dalam beberapa saat.')
            ->with('importing', true);
    }


    public function pidSuggestions(Request $request): JsonResponse
    {
        $rawKeyword = trim((string) $request->query('q'));
        $keyword = mb_strtolower($rawKeyword);

        if (mb_strlen($rawKeyword) < 4) {
            return response()->json([]);
        }

        if (! $this->hasFinanceTables()) {
            return response()->json([]);
        }

        $suggestions = cache()->remember('pid_suggestions_v2_' . $keyword, now()->addMinutes(30), function () use ($keyword) {
            // Prioritize dashboard_finance, fallback ke tab terbaru jika key berbeda.
            $latestSchema = FinanceTabSchema::query()
                ->where('tab_key', 'dashboard_finance')
                ->latest('batch_id')
                ->first();

            if (! $latestSchema) {
                $latestSchema = FinanceTabSchema::query()
                    ->latest('batch_id')
                    ->first();
            }

            if (! $latestSchema) {
                return [];
            }

            $records = FinanceRecord::query()
                ->where('batch_id', $latestSchema->batch_id)
                ->where('tab_key', $latestSchema->tab_key)
                ->where(function ($query) use ($keyword) {
                    $query
                        ->whereRaw('LOWER(COALESCE(project_id, "")) LIKE ?', ["%{$keyword}%"])
                        ->orWhereRaw('LOWER(data) LIKE ?', ["%{$keyword}%"]);
                })
                ->select(['project_id', 'data'])
                ->limit(100)
                ->get();

            return $records
                ->map(function ($record) use ($keyword) {
                    $data = is_string($record->data) ? json_decode($record->data, true) : $record->data;
                    $data = is_array($data) ? $data : [];

                    $pid = trim((string) ($record->project_id
                        ?? $data['project_id']
                        ?? $data['project id']
                        ?? ''));

                    if ($pid === '' || mb_stripos($pid, $keyword) === false) {
                        return null;
                    }

                    return [
                        'project_id' => $pid,
                        'description' => $data['description'] ?? null,
                    ];
                })
                ->filter(fn ($item) => $item !== null)
                ->unique('project_id')
                ->take(10)
                ->values()
                ->all();
        });

        return response()->json($suggestions);
    }

    private function hasFinanceTables(): bool
    {
        return Schema::hasTable('finance_import_batches')
            && Schema::hasTable('finance_tab_schemas')
            && Schema::hasTable('finance_records');
    }
}
