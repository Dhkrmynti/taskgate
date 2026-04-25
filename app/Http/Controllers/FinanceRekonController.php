<?php

namespace App\Http\Controllers;

use App\Models\FinanceRekon;
use App\Models\FinanceRekonBoqDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;
use App\Traits\HandlesPhaseStepper;

class FinanceRekonController extends Controller
{
    use HandlesPhaseStepper;
    public function index()
    {
        return view('finance-rekon.index');
    }

    public function list(Request $request)
    {
        $query = FinanceRekon::with('creator')
            ->select('finance_rekons.*');
        
        return DataTables::of($query)
            ->addColumn('total_amount_jasa', function($item) {
                return 'Rp ' . number_format($item->total_amount, 0, ',', '.');
            })
            ->editColumn('created_at', function($item) {
                return $item->created_at->format('d M Y, H:i');
            })
            ->addColumn('action', function($item) {
                $isClosed = $item->fase === \App\Models\Project::PHASE_CLOSED;
                $manageBtn = '';

                if (!$isClosed) {
                    $manageBtn = '<a href="'.route('tasks.manage', ['finance', $item->id]).'" class="inline-flex h-8 px-2 items-center justify-center rounded-lg border border-blue-100 bg-blue-50 text-blue-600 hover:bg-blue-600 hover:text-white transition dark:border-blue-900/30 dark:bg-blue-900/20 text-[10px] font-bold uppercase" title="Manage Task">
                        Manage
                    </a>';
                }

                return '<div class="flex justify-center gap-2">
                    <a href="'.route('finance-rekon.show', $item->id).'" class="inline-flex h-8 w-8 items-center justify-center rounded-lg border border-slate-200 bg-white text-slate-600 hover:bg-slate-50 transition dark:border-slate-800 dark:bg-[#161f35] dark:text-slate-400" title="View Detail">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                    </a>
                    ' . $manageBtn . '
                </div>';
            })
            ->rawColumns(['action'])
            ->make(true);
    }

    public function autocomplete(Request $request)
    {
        $term = $request->get('term');
        $rekons = FinanceRekon::where('id', 'LIKE', '%'.$term.'%')
            ->orWhere('apm_number', 'LIKE', '%'.$term.'%')
            ->limit(10)
            ->get(['id', 'apm_number']);
            
        return response()->json($rekons);
    }

    public function show(FinanceRekon $financeRekon)
    {
        $financeRekon->load(['boqDetails', 'creator', 'commerceRekons.batches.projects.boqDetails']);
        
        $boqTotals = [
            'total' => $financeRekon->boqDetails->sum(fn($item) => $item->volume * $item->price),
        ];

        $stepperData = $this->getStepperData($financeRekon);

        return view('finance-rekon.show', [
            'rekon' => $financeRekon,
            'boqTotals' => $boqTotals,
            'unifiedSubfaseStatuses' => $stepperData['unifiedSubfaseStatuses'],
            'evidenceMap' => $stepperData['evidenceMap']
        ]);
    }
}
