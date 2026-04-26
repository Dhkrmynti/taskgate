<?php

namespace App\Traits;

use App\Models\Project;
use App\Models\ProjectBatch;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

trait HandlesPhaseStepper
{
    protected function getStepperData($model): array
    {
        $cacheKey = "stepper_data_" . strtolower(class_basename($model)) . "_" . $model->id;
        
        // Cache for 1 hour, or until cleared
        return cache()->remember($cacheKey, now()->addHour(), function() use ($model) {
            $model->loadMissing(['unifiedSubfases', 'unifiedEvidences', 'projectState']);
            
            $unifiedSubfaseStatuses = collect($model->unifiedSubfases->pluck('status', 'subfase_key')->toArray());
            $evidenceMap = $model->unifiedEvidences->groupBy('type');

            $this->aggregateDescendants($model, $unifiedSubfaseStatuses, $evidenceMap);

            $parents = $this->collectAllParents($model);
            foreach ($parents as $parent) {
                // Optimized loading: only select key columns needed for progress/evidence
                $parent->loadMissing([
                    'unifiedSubfases:id,faseable_id,faseable_type,subfase_key,status', 
                    'unifiedEvidences:id,faseable_id,faseable_type,type,file_path,file_name'
                ]);
                
                $parentStatuses = $parent->unifiedSubfases->pluck('status', 'subfase_key')->toArray();
                foreach ($parentStatuses as $key => $val) {
                    $unifiedSubfaseStatuses[$key] = $val;
                }

                foreach ($parent->unifiedEvidences->groupBy('type') as $type => $files) {
                    if (!isset($evidenceMap[$type])) {
                        $evidenceMap[$type] = $files;
                    } else {
                        $existingPaths = collect($evidenceMap[$type])->pluck('file_path')->toArray();
                        $newFiles = $files->filter(fn($f) => !in_array($f->file_path, $existingPaths));
                        $evidenceMap[$type] = collect($evidenceMap[$type])->concat($newFiles);
                    }
                }
            }

            $phaseOrder = ['planning', 'procurement', 'konstruksi', 'rekon', 'warehouse', 'finance', 'closed'];
            $highestPhaseIdx = array_search($model->fase, $phaseOrder);
            
            foreach ($parents as $parent) {
                $pIdx = array_search($parent->fase, $phaseOrder);
                if ($pIdx !== false && $pIdx > $highestPhaseIdx) $highestPhaseIdx = $pIdx;
                
                if ($parent->projectState) {
                    $psIdx = array_search($parent->projectState->current_phase, $phaseOrder);
                    if ($psIdx !== false && $psIdx > $highestPhaseIdx) $highestPhaseIdx = $psIdx;
                }
            }
            
            if ($highestPhaseIdx !== false) {
                $model->fase = $phaseOrder[$highestPhaseIdx];
            }

            return [
                'unifiedSubfaseStatuses' => $unifiedSubfaseStatuses,
                'evidenceMap' => $evidenceMap
            ];
        });
    }

    protected function clearStepperCache($model)
    {
        $this->clearSingleStepperCache($model);
        
        // 1. Clear Parents (Upward)
        $parents = $this->collectAllParents($model);
        foreach ($parents as $parent) {
            $this->clearSingleStepperCache($parent);
        }

        // 2. Clear Descendants (Downward)
        $descendantIds = $this->collectAllDescendantIds($model);
        foreach ($descendantIds as $id => $class) {
            $cacheKey = "stepper_data_" . strtolower(class_basename($class)) . "_" . $id;
            cache()->forget($cacheKey);
            
            // Also financials if site
            if ($class === \App\Models\Project::class) {
                $pid = DB::table('projects')->where('id', $id)->value('pid');
                if ($pid) cache()->forget("financial_data_" . $pid);
            }
        }
    }

    private function clearSingleStepperCache($model)
    {
        $cacheKey = "stepper_data_" . strtolower(class_basename($model)) . "_" . $model->id;
        cache()->forget($cacheKey);
        
        if ($model instanceof \App\Models\Project && $model->pid) {
            cache()->forget("financial_data_" . $model->pid);
        }
    }

    protected function collectAllParents($model): array
    {
        $parents = [];
        $stack = [$model];
        $visited = [spl_object_hash($model) => true];
        
        while (!empty($stack)) {
            $current = array_pop($stack);
            
            $nextParents = [];
            if ($current instanceof \App\Models\Project) {
                if ($current->projectBatch) $nextParents[] = $current->projectBatch;
            } elseif ($current instanceof \App\Models\ProjectBatch) {
                if ($current->commerceRekon) $nextParents[] = $current->commerceRekon;
                if ($current->warehouseRekon) $nextParents[] = $current->warehouseRekon;
            } elseif ($current instanceof \App\Models\CommerceRekon || $current instanceof \App\Models\WarehouseRekon) {
                if ($current->financeRekon) $nextParents[] = $current->financeRekon;
            }

            foreach ($nextParents as $p) {
                $hash = spl_object_hash($p);
                if (!isset($visited[$hash])) {
                    $visited[$hash] = true;
                    $parents[] = $p;
                    $stack[] = $p;
                }
            }
        }
        return $parents;
    }

    protected function aggregateDescendants($model, &$unifiedSubfaseStatuses, &$evidenceMap)
    {
        // Get all descendant IDs flatly to avoid N+1 recursive loading
        $descendantIds = $this->collectAllDescendantIds($model);
        
        if ($descendantIds->isEmpty()) return;

        // Fetch all subfase statuses for all descendants in ONE query
        $allSubfases = \App\Models\UnifiedSubfase::whereIn('faseable_id', $descendantIds->keys())
            ->whereIn('faseable_type', $descendantIds->values()->unique())
            ->get(['id', 'subfase_key', 'status', 'faseable_id', 'faseable_type']);

        foreach ($allSubfases as $sub) {
            // Only update if not already set or override based on hierarchy logic if needed
            // Here we merge to get the most "complete" view
            if (!isset($unifiedSubfaseStatuses[$sub->subfase_key]) || $unifiedSubfaseStatuses[$sub->subfase_key] !== 'selesai') {
                $unifiedSubfaseStatuses[$sub->subfase_key] = $sub->status;
            }
        }

        // Fetch all evidence metadata for all descendants in ONE query
        $allEvidences = \App\Models\UnifiedEvidence::whereIn('faseable_id', $descendantIds->keys())
            ->whereIn('faseable_type', $descendantIds->values()->unique())
            ->get(['id', 'type', 'file_path', 'file_name', 'faseable_id', 'faseable_type']);

        foreach ($allEvidences->groupBy('type') as $type => $files) {
            if (!isset($evidenceMap[$type])) {
                $evidenceMap[$type] = $files;
            } else {
                $existingPaths = collect($evidenceMap[$type])->pluck('file_path')->toArray();
                $newFiles = $files->filter(fn($f) => !in_array($f->file_path, $existingPaths));
                $evidenceMap[$type] = collect($evidenceMap[$type])->concat($newFiles);
            }
        }
    }

    protected function collectAllDescendantIds($model): Collection
    {
        $ids = collect();
        $toProcess = [[$model->id, get_class($model)]];

        while (!empty($toProcess)) {
            [$currentId, $currentClass] = array_pop($toProcess);
            $children = collect();

            if ($currentClass === \App\Models\FinanceRekon::class) {
                $cIds = DB::table('commerce_rekons')->where('finance_rekon_id', $currentId)->pluck('id')->toArray();
                $wIds = DB::table('warehouse_rekons')->where('finance_rekon_id', $currentId)->pluck('id')->toArray();
                foreach($cIds as $id) { $ids->put($id, \App\Models\CommerceRekon::class); $toProcess[] = [$id, \App\Models\CommerceRekon::class]; }
                foreach($wIds as $id) { $ids->put($id, \App\Models\WarehouseRekon::class); $toProcess[] = [$id, \App\Models\WarehouseRekon::class]; }
            } elseif ($currentClass === \App\Models\CommerceRekon::class || $currentClass === \App\Models\WarehouseRekon::class) {
                // Determine column based on type
                $col = ($currentClass === \App\Models\CommerceRekon::class) ? 'rekon_id' : 'warehouse_rekon_id';
                $bIds = DB::table('project_batches')->where($col, $currentId)->pluck('id')->toArray();
                foreach($bIds as $id) { $ids->put($id, \App\Models\ProjectBatch::class); $toProcess[] = [$id, \App\Models\ProjectBatch::class]; }
            } elseif ($currentClass === \App\Models\ProjectBatch::class) {
                $pIds = DB::table('projects')->where('batch_id', $currentId)->pluck('id')->toArray();
                foreach($pIds as $id) { $ids->put($id, \App\Models\Project::class); }
            }
        }

        return $ids;
    }

    protected function updateProjectPhase($model, string $newPhase)
    {
        $model->update(['fase' => $newPhase]);
        $model->projectState()->updateOrCreate([], [
            'current_phase' => $newPhase,
            'history' => array_merge($model->projectState->history ?? [], [
                ['phase' => $newPhase, 'timestamp' => now()->toDateTimeString(), 'user_id' => auth()->id()]
            ])
        ]);

        if ($model instanceof \App\Models\FinanceRekon) {
            foreach ($model->commerceRekons as $c) $this->updateProjectPhase($c, $newPhase);
            foreach ($model->warehouseRekons as $w) $this->updateProjectPhase($w, $newPhase);
        } elseif ($model instanceof \App\Models\CommerceRekon || $model instanceof \App\Models\WarehouseRekon) {
            foreach ($model->batches as $b) $this->updateProjectPhase($b, $newPhase);
        } elseif ($model instanceof \App\Models\ProjectBatch) {
            foreach ($model->projects as $p) $this->updateProjectPhase($p, $newPhase);
        }
    }
}
