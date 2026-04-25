<?php

namespace App\Http\Controllers;

use App\Models\WarehouseRekon;
use App\Models\WarehouseRekonBoqDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;
use App\Traits\HandlesPhaseStepper;

class RekonController extends Controller
{
    use HandlesPhaseStepper;

    public function index()
    {
        return view('rekon.index');
    }

    public function list(Request $request)
    {
        $query = WarehouseRekon::with('creator')
            ->withSum('boqDetails as total_vol_pemenuhan', 'volume_pemenuhan')
            ->withExists(['unifiedSubfases as is_done' => function($q) {
                $q->where('subfase_key', 'warehouse_done')->where('status', 'selesai');
            }]);
        
        return DataTables::of($query)
            ->addColumn('total_vol_pemenuhan', function($item) {
                // Fallback to direct calculation if alias not passed by DT
                return $item->total_vol_pemenuhan ?? $item->boqDetails->sum('volume_pemenuhan');
            })
            ->addColumn('total_amount', function($item) {
                $amount = DB::table('warehouse_rekon_boq_details')
                    ->where('warehouse_rekon_id', $item->id)
                    ->selectRaw('SUM(volume_pemenuhan * price_planning) as total')
                    ->value('total') ?? 0;
                return 'Rp ' . number_format($amount, 0, ',', '.');
            })
            ->editColumn('created_at', function($item) {
                return $item->created_at->format('d M Y, H:i');
            })
            ->addColumn('action', function($item) {
                $isSubmitted = $item->is_done || in_array($item->fase, [\App\Models\Project::PHASE_FINANCE, \App\Models\Project::PHASE_CLOSED]);
                $manageBtn = '';

                if (!$isSubmitted) {
                    $manageBtn = '<a href="'.route('tasks.manage', ['warehouse', $item->id]).'" class="inline-flex h-8 px-2 items-center justify-center rounded-lg border border-blue-100 bg-blue-50 text-blue-600 hover:bg-blue-600 hover:text-white transition dark:border-blue-900/30 dark:bg-blue-900/20 text-[10px] font-bold uppercase" title="Manage Task">
                        Manage
                    </a>';
                }

                return '<div class="flex justify-center items-center gap-2">
                    <a href="'.route('rekon.show', $item->id).'" class="inline-flex h-8 w-8 items-center justify-center rounded-lg border border-brand-line bg-white text-brand-text hover:bg-slate-50 transition dark:border-brand-darkLine dark:bg-[#161f35] dark:text-white" title="View Detail">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                    </a>
                    ' . $manageBtn . '
                    <a href="'.route('rekon.print', $item->id).'" target="_blank" class="inline-flex h-8 w-8 items-center justify-center rounded-lg border border-brand-line bg-white text-brand-text hover:bg-slate-50 transition dark:border-brand-darkLine dark:bg-[#161f35] dark:text-white" title="Cetak Berita Acara">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 00-2 2h2m2 4h10a2 2 0 002-2v-3a2 2 0 00-2-2H5a2 2 0 00-2 2v3a2 2 0 00-2 2zm0 0v-8a2 2 0 00-2-2H9a2 2 0 00-2 2v8"/></svg>
                    </a>
                </div>';
            })
            ->rawColumns(['action'])
            ->make(true);
    }

    public function autocomplete(Request $request)
    {
        $term = $request->get('term');
        $rekons = WarehouseRekon::where('id', 'LIKE', '%'.$term.'%')
            ->orWhere('rekon_number', 'LIKE', '%'.$term.'%')
            ->limit(10)
            ->get(['id', 'rekon_number']);
            
        return response()->json($rekons);
    }

    public function show(WarehouseRekon $warehouseRekon)
    {
        $warehouseRekon->load(['boqDetails', 'creator', 'batches.boqDetails', 'batches.projects.boqDetails']);
        
        $boqTotals = [
            'planning' => $warehouseRekon->boqDetails->sum(fn($item) => $item->volume_planning * $item->price_planning),
            'pemenuhan' => $warehouseRekon->boqDetails->sum(fn($item) => $item->volume_pemenuhan * $item->price_planning),
            'aktual' => $warehouseRekon->boqDetails->sum(fn($item) => $item->volume_aktual * $item->price_planning),
        ];

        $stepperData = $this->getStepperData($warehouseRekon);

        return view('rekon.show', [
            'rekon' => $warehouseRekon,
            'boqTotals' => $boqTotals,
            'unifiedSubfaseStatuses' => $stepperData['unifiedSubfaseStatuses'],
            'evidenceMap' => $stepperData['evidenceMap']
        ]);
    }

    public function print(WarehouseRekon $warehouseRekon)
    {
        if ($warehouseRekon->fase !== 'selesai') {
            abort(403, 'Berita Acara hanya dapat dicetak untuk project yang sudah selesai.');
        }
        $warehouseRekon->load(['boqDetails', 'creator']);
        return view('rekon.print-ba', ['rekon' => $warehouseRekon]);
    }
}
