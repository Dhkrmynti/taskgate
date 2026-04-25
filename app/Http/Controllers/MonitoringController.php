<?php

namespace App\Http\Controllers;

use App\Models\ProjectBatch;
use App\Models\CommerceRekon;
use App\Models\WarehouseRekon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MonitoringController extends Controller
{
    public function index()
    {
        $batches = ProjectBatch::with(['projects.unifiedSubfases', 'creator', 'commerceRekon.financeRekon', 'warehouseRekon'])
            ->latest()
            ->paginate(15);
        return view('dashboard', compact('batches'));
    }

    /**
     * Helper to determine department status based on cross-model data.
     */
    public static function getDeptStatus($batch, $dept)
    {
        switch ($dept) {
            case 'procurement':
                return [
                    'label' => 'Completed',
                    'color' => 'bg-green-100 text-green-700 dark:bg-green-500/20 dark:text-green-400',
                    'details' => $batch->id,
                    'links' => [
                        ['label' => 'Download BoQ', 'url' => route('rekon.batch-download-boq', $batch->id)],
                    ]
                ];
            
            case 'konstruksi':
                $totalSites = $batch->projects->count();
                
                // Check batch-level status first
                $batchTeskon = $batch->unifiedSubfases->where('subfase_key', 'konstruksi_teskon')->where('status', 'selesai')->isNotEmpty();
                if ($batchTeskon) return ['label' => 'Finished', 'color' => 'bg-green-100 text-green-700 dark:bg-green-500/20 dark:text-green-400'];

                if ($totalSites === 0) return ['label' => 'N/A', 'color' => 'bg-slate-100 text-slate-500 dark:bg-slate-800 dark:text-slate-500'];
                
                // Count how many sites have finished 'konstruksi_teskon'
                $finishedSites = $batch->projects->filter(function($p) {
                    return $p->unifiedSubfases->where('subfase_key', 'konstruksi_teskon')->where('status', 'selesai')->isNotEmpty();
                })->count();

                $percent = round(($finishedSites / $totalSites) * 100);
                
                if ($percent === 100) return ['label' => 'Finished', 'color' => 'bg-green-100 text-green-700 dark:bg-green-500/20 dark:text-green-400'];
                if ($percent > 0 || $batch->unifiedSubfases->isNotEmpty()) return ['label' => "On-Progress", 'color' => 'bg-brand-blue/10 text-brand-blue dark:bg-brand-vibrantBlue/20 dark:text-brand-vibrantBlue'];
                
                return ['label' => 'Pending', 'color' => 'bg-amber-100 text-amber-700 dark:bg-amber-500/20 dark:text-amber-400'];

            case 'commerce':
                if ($batch->rekon_id) {
                    return [
                        'label' => 'Batched',
                        'secondary' => $batch->rekon_id,
                        'color' => 'bg-indigo-100 text-indigo-700 dark:bg-indigo-500/20 dark:text-indigo-400',
                        'links' => [
                            ['label' => 'Excel', 'url' => route('rekon.commerce-download', [$batch->rekon_id, 'excel'])],
                            ['label' => 'BARM', 'url' => route('rekon.commerce-download', [$batch->rekon_id, 'evidence'])],
                        ]
                    ];
                }
                return ['label' => 'Waiting', 'color' => 'bg-slate-100 text-slate-500 dark:bg-slate-800 dark:text-slate-500'];

            case 'warehouse':
                if ($batch->warehouse_rekon_id) {
                    return [
                        'label' => 'Batched',
                        'secondary' => $batch->warehouse_rekon_id,
                        'color' => 'bg-purple-100 text-purple-700 dark:bg-purple-500/20 dark:text-purple-400',
                        'links' => [
                            ['label' => 'Excel', 'url' => route('rekon.warehouse-download', [$batch->warehouse_rekon_id, 'excel'])],
                            ['label' => 'BA-RM', 'url' => route('rekon.warehouse-download', [$batch->warehouse_rekon_id, 'evidence'])],
                        ]
                    ];
                }
                return ['label' => 'Waiting', 'color' => 'bg-slate-100 text-slate-500 dark:bg-slate-800 dark:text-slate-500'];

            case 'finance':
                // Check if through CommerceRekon it has been batched by Finance
                if ($batch->commerceRekon && $batch->commerceRekon->finance_rekon_id) {
                    return [
                        'label' => 'Realized',
                        'secondary' => $batch->commerceRekon->finance_rekon_id,
                        'color' => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-500/20 dark:text-emerald-400',
                        'links' => [
                            ['label' => 'Excel', 'url' => route('rekon.finance-download', [$batch->commerceRekon->finance_rekon_id, 'excel'])],
                            ['label' => 'APM', 'url' => route('rekon.finance-download', [$batch->commerceRekon->finance_rekon_id, 'evidence'])],
                        ]
                    ];
                }
                
                if ($batch->fase === ProjectBatch::PHASE_FINANCE) {
                    return ['label' => 'Invoicing', 'color' => 'bg-blue-100 text-blue-700 dark:bg-blue-500/20 dark:text-blue-400'];
                }
                return ['label' => 'Waiting', 'color' => 'bg-slate-100 text-slate-500 dark:bg-slate-800 dark:text-slate-500'];
        }

        return ['label' => 'Unknown', 'color' => 'bg-slate-100 text-slate-500 dark:bg-slate-800 dark:text-slate-500'];
    }
}
