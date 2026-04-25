<?php

namespace App\Http\Controllers;

use App\Models\CommerceRekon;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use App\Traits\HandlesPhaseStepper;

class CommerceRekonController extends Controller
{
    use HandlesPhaseStepper;
    public function index()
    {
        return view('commerce-rekon.index');
    }

    public function list(Request $request)
    {
        $query = CommerceRekon::with('creator')->withExists(['unifiedSubfases as is_done' => function($q) {
            $q->where('subfase_key', 'commerce_done')->where('status', 'selesai');
        }]);
        
        return DataTables::of($query)
            ->addColumn('total_amount_planning', function($item) {
                // Call relationship to get boq sum (might be N+1 if not careful, but typically few records or done via join if large)
                $total = $item->boqDetails->sum(fn($detail) => $detail->volume_planning * $detail->price_planning);
                return 'Rp ' . number_format($total, 0, ',', '.');
            })
            ->editColumn('created_at', function($item) {
                return $item->created_at->format('d M Y, H:i');
            })
            ->addColumn('action', function($item) {
                $isSubmitted = $item->is_done || !in_array($item->fase, [\App\Models\Project::PHASE_PLANNING, \App\Models\Project::PHASE_PROCUREMENT, \App\Models\Project::PHASE_KONSTRUKSI, \App\Models\Project::PHASE_REKON]);
                $manageBtn = '';

                if (!$isSubmitted) {
                    $manageBtn = '<a href="'.route('tasks.manage', ['commerce', $item->id]).'" class="inline-flex h-8 px-2 items-center justify-center rounded-lg border border-blue-100 bg-blue-50 text-blue-600 hover:bg-blue-600 hover:text-white transition dark:border-blue-900/30 dark:bg-blue-900/20 text-[10px] font-bold uppercase" title="Manage Task">
                        Manage
                    </a>';
                }

                return '<div class="flex justify-center items-center gap-2">
                    <a href="'.route('commerce-rekon.show', $item->id).'" class="inline-flex h-8 w-8 items-center justify-center rounded-lg border border-brand-line bg-white text-brand-text hover:bg-slate-50 transition dark:border-brand-darkLine dark:bg-[#161f35] dark:text-white" title="View Detail">
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
        $rekons = CommerceRekon::where('id', 'LIKE', '%'.$term.'%')
            ->orWhere('rekon_number', 'LIKE', '%'.$term.'%')
            ->limit(10)
            ->get(['id', 'rekon_number']);
            
        return response()->json($rekons);
    }

    public function show(CommerceRekon $commerceRekon)
    {
        $commerceRekon->load(['boqDetails', 'creator', 'batches.boqDetails', 'batches.projects.boqDetails']);
        
        $boqTotals = [
            'planning' => $commerceRekon->boqDetails->sum(fn($item) => $item->volume_planning * $item->price_planning),
            'pemenuhan' => $commerceRekon->boqDetails->sum(fn($item) => $item->volume_pemenuhan * $item->price_planning),
        ];

        $stepperData = $this->getStepperData($commerceRekon);

        return view('commerce-rekon.show', [
            'rekon' => $commerceRekon,
            'boqTotals' => $boqTotals,
            'unifiedSubfaseStatuses' => $stepperData['unifiedSubfaseStatuses'],
            'evidenceMap' => $stepperData['evidenceMap']
        ]);
    }
}
