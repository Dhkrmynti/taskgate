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
        $cacheKey = "stepper_data_" . strtolower(class_basename($model)) . "_" . $model->id;
        cache()->forget($cacheKey);
        
        // Also clear financials if it's a project or batch
        if ($model instanceof \App\Models\Project && $model->pid) {
            cache()->forget("financial_data_" . $model->pid);
        }
        
        // Also clear parents' cache because children's change affects them
        $parents = $this->collectAllParents($model);
        foreach ($parents as $parent) {
            $pKey = "stepper_data_" . strtolower(class_basename($parent)) . "_" . $parent->id;
            cache()->forget($pKey);
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
        $constituents = collect();
        if (method_exists($model, 'constituents')) {
            $constituents = $model->constituents;
        } elseif (method_exists($model, 'batches')) {
            $constituents = $model->batches;
        } elseif ($model instanceof ProjectBatch) {
            $constituents = $model->projects;
        }

        foreach ($constituents as $child) {
            $child->loadMissing(['unifiedSubfases', 'unifiedEvidences']);
            
            $unifiedSubfaseStatuses = $child->unifiedSubfases->pluck('status', 'subfase_key')->merge($unifiedSubfaseStatuses);

            foreach ($child->unifiedEvidences->groupBy('type') as $type => $files) {
                if (!isset($evidenceMap[$type])) {
                    $evidenceMap[$type] = $files;
                } else {
                    $existingPaths = collect($evidenceMap[$type])->pluck('file_path')->toArray();
                    $newFiles = $files->filter(fn($f) => !in_array($f->file_path, $existingPaths));
                    $evidenceMap[$type] = collect($evidenceMap[$type])->concat($newFiles);
                }
            }

            $siblings = [];
            if ($child instanceof \App\Models\CommerceRekon && method_exists($child, 'batches')) {
                foreach($child->batches as $b) if ($b->warehouseRekon) $siblings[] = $b->warehouseRekon;
            }
            if ($child instanceof \App\Models\WarehouseRekon && method_exists($child, 'batches')) {
                foreach($child->batches as $b) if ($b->commerceRekon) $siblings[] = $b->commerceRekon;
            }
            foreach ($siblings as $sibling) {
                $sibling->loadMissing('unifiedSubfases');
                $unifiedSubfaseStatuses = $sibling->unifiedSubfases->pluck('status', 'subfase_key')->merge($unifiedSubfaseStatuses);
            }

            $this->aggregateDescendants($child, $unifiedSubfaseStatuses, $evidenceMap);
        }
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
