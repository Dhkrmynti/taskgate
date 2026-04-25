<?php

namespace App\Traits;

use App\Models\Project;
use App\Models\ProjectBatch;
use App\Models\ProjectBoqDetail;
use App\Models\ProjectBatchBoqDetail;
use App\Models\CommerceRekonBoqDetail;
use App\Models\WarehouseRekonBoqDetail;
use Illuminate\Support\Facades\DB;

trait HandlesBoqSync
{
    /**
     * Synchronize Pemenuhan volume across all project levels.
     * Usually triggered when Warehouse Rekon (TGIDRM) is updated.
     */
    protected function syncPemenuhan($projectModel, $designator, $volume)
    {
        $id = $projectModel->id;

        if (str_starts_with($id, 'TGIDRM-')) {
            // Updated from Warehouse Rekon
            // 1. Update constituent Batches
            $batchIds = DB::table('project_batches')
                ->where('warehouse_rekon_id', $id)
                ->pluck('id');

            ProjectBatchBoqDetail::whereIn('project_batch_id', $batchIds)
                ->where('designator', $designator)
                ->update(['volume_pemenuhan' => $volume]);

            // 2. Update constituent Sites
            $siteIds = DB::table('projects')
                ->whereIn('batch_id', $batchIds)
                ->pluck('id');

            ProjectBoqDetail::whereIn('project_id', $siteIds)
                ->where('designator', $designator)
                ->update(['volume_pemenuhan' => $volume]);

            // 3. Update related Commerce Rekons (if any)
            $rekonIds = DB::table('project_batches')
                ->whereIn('id', $batchIds)
                ->whereNotNull('rekon_id')
                ->pluck('rekon_id')
                ->unique();

            CommerceRekonBoqDetail::whereIn('commerce_rekon_id', $rekonIds)
                ->where('designator', $designator)
                ->update(['volume_pemenuhan' => $volume]);
        }
        // Add other propagation directions if needed in the future
    }
}
