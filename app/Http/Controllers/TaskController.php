<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\ProcurementSp;
use App\Models\Mitra;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Yajra\DataTables\Facades\DataTables;

use App\Traits\HandlesBoqSync;
use App\Traits\HandlesPhaseStepper;

class TaskController extends Controller
{
    use HandlesBoqSync, HandlesPhaseStepper;
    /**
     * Display the search/cart interface for tasks based on role.
     */
    public function index(string $role): View
    {
        $roleLabel = $this->getRoleLabel($role);
        $mitras = ($role === 'procurement') ? Mitra::all() : collect();
        return view('tasks.index', compact('role', 'roleLabel', 'mitras'));
    }

    public function data(string $role, Request $request): JsonResponse
    {
        $phaseMapping = [
            'procurement' => Project::PHASE_PLANNING,
            'konstruksi' => Project::PHASE_KONSTRUKSI,
            'commerce' => Project::PHASE_REKON,
            'warehouse' => Project::PHASE_REKON,
            'finance' => Project::PHASE_FINANCE,
        ];

        $targetPhase = $phaseMapping[$role] ?? Project::PHASE_PLANNING;

        // Base Query
        if ($role === 'procurement') {
            // Sites in 'start' phase
            $sitesQuery = DB::table('projects')
                ->where('fase', Project::PHASE_PLANNING)
                ->whereNull('batch_id')
                ->select(['id', 'project_name', 'customer', 'fase', 'updated_at', DB::raw('"site" as type')]);

            $query = $sitesQuery;
        } elseif ($role === 'commerce') {
            // 1. Batches in 'rekon' phase that HAVE NOT been batched into TGIDRC
            $batchesQuery = DB::table('project_batches')
                ->join('project_states', function($join) {
                    $join->on('project_batches.id', '=', 'project_states.stateable_id')
                         ->where('project_states.stateable_type', '=', \App\Models\ProjectBatch::class);
                })
                ->where('project_states.current_phase', Project::PHASE_REKON)
                ->whereNull('project_batches.rekon_id')
                ->whereNotExists(function($q) {
                    $q->select(DB::raw(1))
                      ->from('unified_subfases')
                      ->whereColumn('unified_subfases.faseable_id', 'project_batches.id')
                      ->where('unified_subfases.faseable_type', \App\Models\ProjectBatch::class)
                      ->where('subfase_key', 'commerce_done')
                      ->where('status', 'selesai');
                })
                ->select(['project_batches.id', 'project_batches.project_name', 'project_batches.customer', 'project_states.current_phase as fase', 'project_batches.updated_at', DB::raw('"batch" as type')]);

            // 2. Commerce Rekons (TGIDRC) that are still in 'rekon' phase
            $rekonsQuery = DB::table('commerce_rekons')
                ->join('project_states', function($join) {
                    $join->on('commerce_rekons.id', '=', 'project_states.stateable_id')
                         ->where('project_states.stateable_type', '=', \App\Models\CommerceRekon::class);
                })
                ->where('project_states.current_phase', Project::PHASE_REKON)
                ->whereNotExists(function($q) {
                    $q->select(DB::raw(1))
                      ->from('unified_subfases')
                      ->whereColumn('unified_subfases.faseable_id', 'commerce_rekons.id')
                      ->where('unified_subfases.faseable_type', \App\Models\CommerceRekon::class)
                      ->where('subfase_key', 'commerce_done')
                      ->where('status', 'selesai');
                })
                ->select(['commerce_rekons.id', DB::raw('commerce_rekons.id as project_name'), DB::raw('"REKON BATCH" as customer'), 'project_states.current_phase as fase', 'commerce_rekons.updated_at', DB::raw('"rekon" as type')]);

            $query = $batchesQuery->union($rekonsQuery);

        } elseif ($role === 'warehouse') {
            // 1. Batches that HAVE NOT been batched into TGIDRM
            $batchesQuery = DB::table('project_batches')
                ->join('project_states', function($join) {
                    $join->on('project_batches.id', '=', 'project_states.stateable_id')
                         ->where('project_states.stateable_type', '=', \App\Models\ProjectBatch::class);
                })
                ->whereIn('project_states.current_phase', [Project::PHASE_REKON, Project::PHASE_WAREHOUSE])
                ->whereNull('project_batches.warehouse_rekon_id')
                ->whereNotExists(function($q) {
                    $q->select(DB::raw(1))
                      ->from('unified_subfases')
                      ->whereColumn('unified_subfases.faseable_id', 'project_batches.id')
                      ->where('unified_subfases.faseable_type', \App\Models\ProjectBatch::class)
                      ->where('subfase_key', 'warehouse_done')
                      ->where('status', 'selesai');
                })
                ->select(['project_batches.id', 'project_batches.project_name', 'project_batches.customer', 'project_states.current_phase as fase', 'project_batches.updated_at', DB::raw('"batch" as type')]);

            // 2. Warehouse Rekons (TGIDRM)
            $rekonsQuery = DB::table('warehouse_rekons')
                ->join('project_states', function($join) {
                    $join->on('warehouse_rekons.id', '=', 'project_states.stateable_id')
                         ->where('project_states.stateable_type', '=', \App\Models\WarehouseRekon::class);
                })
                ->whereIn('project_states.current_phase', [Project::PHASE_REKON, Project::PHASE_WAREHOUSE])
                ->whereNotExists(function($q) {
                    $q->select(DB::raw(1))
                      ->from('unified_subfases')
                      ->whereColumn('unified_subfases.faseable_id', 'warehouse_rekons.id')
                      ->where('unified_subfases.faseable_type', \App\Models\WarehouseRekon::class)
                      ->where('subfase_key', 'warehouse_done')
                      ->where('status', 'selesai');
                })
                ->select(['warehouse_rekons.id', DB::raw('warehouse_rekons.id as project_name'), DB::raw('"REKON MATERIAL" as customer'), 'project_states.current_phase as fase', 'warehouse_rekons.updated_at', DB::raw('"rekon" as type')]);

            $query = $batchesQuery->union($rekonsQuery);

        } elseif ($role === 'finance') {
            // ONLY Commerce Rekons that HAVE NOT been batched into TGIDRF
            $query = DB::table('commerce_rekons')
                ->join('project_states', function($join) {
                    $join->on('commerce_rekons.id', '=', 'project_states.stateable_id')
                         ->where('project_states.stateable_type', '=', \App\Models\CommerceRekon::class);
                })
                ->whereIn('project_states.current_phase', [Project::PHASE_REKON, Project::PHASE_WAREHOUSE, Project::PHASE_FINANCE])
                ->whereNull('commerce_rekons.finance_rekon_id')
                ->whereExists(function($q) {
                    $q->select(DB::raw(1))
                      ->from('unified_subfases')
                      ->whereColumn('unified_subfases.faseable_id', 'commerce_rekons.id')
                      ->where('unified_subfases.faseable_type', \App\Models\CommerceRekon::class)
                      ->where('subfase_key', 'commerce_done')
                      ->where('status', 'selesai');
                })
                ->select(['commerce_rekons.id', DB::raw('commerce_rekons.id as project_name'), DB::raw('"Commerce/WF" as customer'), 'project_states.current_phase as fase', 'commerce_rekons.updated_at', DB::raw('"rekon" as type')]);

        } else {
            // Default for other roles
            $table = ($role === 'konstruksi') ? 'project_batches' : 'commerce_rekons';
            $query = DB::table($table)
                ->where('fase', $targetPhase)
                ->select(['id', DB::raw($table === 'project_batches' ? 'project_name' : 'id as project_name'), 
                         DB::raw($table === 'project_batches' ? 'customer' : '"Commerce/WF" as customer'), 
                         'fase', 'updated_at', DB::raw('"entity" as type')]);
            
            if ($role === 'konstruksi') $query->addSelect(DB::raw('"batch" as type'));
            else $query->addSelect(DB::raw('"rekon" as type'));
        }

        return DataTables::of($query)
            ->addColumn('checkbox', function ($row) use ($role) {
                // Determine if this item can be batched for the current role
                $canBatch = false;
                if ($role === 'procurement' && $row->type === 'site') $canBatch = true;
                if ($role === 'commerce' && $row->type === 'batch') $canBatch = true;
                if ($role === 'warehouse' && $row->type === 'batch') $canBatch = true;
                if ($role === 'finance' && $row->type === 'rekon') $canBatch = true;

                if ($canBatch) {
                    return '<div class="flex justify-center"><input type="checkbox" name="selected_items[]" value="' . $row->id . '" class="project-checkbox h-5 w-5 rounded-lg border-slate-300 text-blue-600 focus:ring-4 focus:ring-blue-500/20 dark:border-slate-700 dark:bg-slate-800"></div>';
                }
                return '<div class="flex justify-center"><svg class="h-4 w-4 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg></div>';
            })
            ->editColumn('project_info', function ($row) {
                $siteInfo = '';
                if (isset($row->projects_count) && $row->projects_count > 0) {
                    $siteInfo = ' • ' . $row->projects_count . ' Site' . ($row->projects_count > 1 ? 's' : '');
                }
                return '<div class="flex flex-col"><span class="text-sm font-bold text-brand-text dark:text-white line-clamp-1 truncate max-w-[300px]" title="' . $row->project_name . '">' . $row->project_name . '</span><span class="text-[10px] text-brand-muted dark:text-slate-500">Customer: ' . ($row->customer ?? '-') . $siteInfo . '</span></div>';
        })
            ->editColumn('id', function ($row) {
            return '<span class="text-xs font-black text-blue-600 dark:text-blue-400 font-mono">' . $row->id . '</span>';
        })
            ->editColumn('fase', function ($row) {
            $label = ucwords(str_replace('_', ' ', $row->fase));
            return '<span class="inline-flex items-center rounded-lg bg-blue-50 px-2.5 py-1 text-[10px] font-bold uppercase tracking-wider text-blue-600 dark:bg-blue-900/20 dark:text-blue-400">' . $label . '</span>';
        })
            ->addColumn('action', function ($row) use ($role) {
            $manageablePhases = [
                'procurement' => [Project::PHASE_PLANNING, Project::PHASE_PROCUREMENT],
                'konstruksi' => [Project::PHASE_KONSTRUKSI],
                'commerce' => [Project::PHASE_REKON],
                'warehouse' => [Project::PHASE_REKON, Project::PHASE_WAREHOUSE],
                'finance' => [Project::PHASE_FINANCE],
            ];

            $phases = $manageablePhases[$role] ?? [];
            if (!in_array($row->fase, $phases)) {
                $detailUrl = route('project-data.show', $row->id);
                return '<div class="flex justify-center"><a href="' . $detailUrl . '" class="inline-flex h-8 w-8 items-center justify-center rounded-lg border border-brand-line bg-white text-brand-text hover:bg-slate-50 transition dark:border-brand-darkLine dark:bg-[#161f35] dark:text-white" title="Detail"><svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg></a></div>';
            }

            // Focus Taskgate on stage-to-stage processing. 
            // If the item type belongs to a previous stage (to be batched), hide Manage button.
            $isProcessOnly = false;
            if ($role === 'procurement' && $row->type === 'site') $isProcessOnly = true;
            if ($role === 'commerce' && $row->type === 'batch') $isProcessOnly = true;
            if ($role === 'warehouse' && $row->type === 'batch') $isProcessOnly = true;
            if ($role === 'finance' && $row->type === 'rekon') $isProcessOnly = true;

            if ($isProcessOnly) {
                 $detailUrl = route('project-data.show', $row->id);
                 return '<div class="flex justify-center"><a href="' . $detailUrl . '" class="inline-flex h-8 w-8 items-center justify-center rounded-lg border border-brand-line bg-white text-brand-text hover:bg-slate-50 transition dark:border-brand-darkLine dark:bg-[#161f35] dark:text-white" title="Detail"><svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg></a></div>';
            }

            return '<div class="flex justify-center"><a href="' . route('tasks.manage', [$role, $row->id]) . '" class="inline-flex items-center gap-1.5 rounded-lg border border-blue-100 bg-blue-50/50 px-3 py-1.5 text-[10px] font-black uppercase text-blue-600 transition hover:bg-blue-600 hover:text-white dark:border-blue-900/30 dark:bg-blue-900/20">Manage Task <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg></a></div>';
        })
            ->rawColumns(['checkbox', 'project_info', 'id', 'fase', 'action'])
            ->toJson();
    }

    /**
     * Manage specific project evidence and progress for a role.
     */
    public function manage(string $role, $id): View
    {
        $validRoles = ['commerce', 'procurement', 'konstruksi', 'warehouse', 'finance'];

        if (!in_array($role, $validRoles)) {
            abort(404);
        }
        
        if (str_starts_with($id, 'TGIDSP-')) {
            $isBatch = true;
            $model = \App\Models\ProjectBatch::class;
        } elseif (str_starts_with($id, 'TGIDRC-')) {
            $isBatch = true;
            $model = \App\Models\CommerceRekon::class;
        } elseif (str_starts_with($id, 'TGIDRM-')) {
            $isBatch = true;
            $model = \App\Models\WarehouseRekon::class;
        } elseif (str_starts_with($id, 'TGIDRF-')) {
            $isBatch = true;
            $model = \App\Models\FinanceRekon::class;
        } else {
            $isBatch = false;
            $model = \App\Models\Project::class;
        }

        $project = $model::with(['unifiedEvidences', 'unifiedSubfases'])->findOrFail($id);

        $stepperData = $this->getStepperData($project);
        $unifiedSubfaseStatuses = $stepperData['unifiedSubfaseStatuses'];
        
        $data = [
            'role' => $role,
            'roleLabel' => $this->getRoleLabel($role),
            'project' => $project,
            'isBatch' => $isBatch,
            'unifiedSubfaseStatuses' => $unifiedSubfaseStatuses,
            'subPhases' => $this->getSubPhasesForRole($role),
            'currentSubPhases' => $this->getSubPhasesForRole($role),
            'canUpload' => true,
            'allSubphasesDone' => $this->checkAllSubphasesDone($project, $role),
            'isAlreadySubmitted' => $this->isPhaseSubmitted($role, $project->fase),
            'submitRoute' => $this->getSubmitRouteForRole($role),
            'submitLabel' => $this->getSubmitLabelForRole($role),
            'evidenceMap' => $stepperData['evidenceMap'],
        ];

        $viewName = "tasks.roles.{$role}";
        if (!view()->exists($viewName)) {
            $viewName = 'tasks.manage';
        }

        return view($viewName, $data);
    }

    public function tgidSuggestions(Request $request): JsonResponse
    {
        $search = $request->get('q');

        $projects = Project::query()
            ->where('id', 'LIKE', "%{$search}%")
            ->orWhere('project_name', 'LIKE', "%{$search}%")
            ->limit(10)
            ->get(['id', 'project_name', 'customer']);

        return response()->json($projects);
    }

    public function getProjectDetail($id): JsonResponse
    {
        $project = Project::find($id);

        if (!$project) {
            return response()->json(['error' => 'Project not found'], 404);
        }

        return response()->json($project);
    }

    public function processProcurementBatch(Request $request): JsonResponse
    {
        $request->validate([
            'po_number' => 'required|string',
            'mitra_id' => 'required|exists:mitras,id',
            'po_evidence' => 'nullable|file|mimes:pdf|max:10240',
            'project_ids' => 'required|array|min:1',
            'project_ids.*' => 'exists:projects,id',
        ]);

        try {
            return DB::transaction(function () use ($request) {
                // Fetch projects with counts for validation
                $projects = Project::whereIn('id', $request->project_ids)
                    ->withCount(['boqDetails', 'unifiedEvidences' => function ($q) {
                    $q->where('type', 'dasar_pekerjaan');
                }
                ])
                    ->get();

                // 1. Validation check
                foreach ($projects as $project) {
                    if ($project->boq_details_count === 0) {
                        throw new \Exception("Project {$project->id} ({$project->project_name}) tidak bisa di-batch karena data BoQ masih kosong.");
                    }
                    if ($project->evidence_files_count === 0) {
                        throw new \Exception("Project {$project->id} ({$project->project_name}) tidak bisa di-batch karena Evidence Dasar Pekerjaan belum di-upload.");
                    }
                }

                $firstProject = $projects->first();

                $datePrefix = "TGIDSP-" . now()->format('Ymd');
                $lastSp = ProcurementSp::where('id', 'LIKE', "{$datePrefix}%")
                    ->orderBy('id', 'desc')
                    ->first();

                $sequence = 1;
                if ($lastSp) {
                    $lastId = $lastSp->id;
                    $lastSequence = (int)substr($lastId, strrpos($lastId, '-') + 1);
                    $sequence = $lastSequence + 1;
                }
                $newId = "{$datePrefix}-" . str_pad($sequence, 4, '0', STR_PAD_LEFT);

                $path = null;
                if ($request->hasFile('po_evidence')) {
                    $path = $request->file('po_evidence')->store("evidence/procurement/po", 'public');
                }

                $allBoqItems = \App\Models\ProjectBoqDetail::whereIn('project_id', $request->project_ids)
                    ->get()
                    ->groupBy(function ($item) {
                    return $item->designator . '|' . $item->description;
                }
                );

                $aggregatedBoqs = [];
                $sort = 1;
                foreach ($allBoqItems as $key => $items) {
                    $first = $items->first();
                    $aggregatedBoqs[] = [
                        'project_batch_id' => $newId,
                        'designator' => $first->designator,
                        'description' => $first->description,
                        'volume_planning' => $items->sum('volume_planning'),
                        'price_planning' => $first->price_planning,
                        'volume_pemenuhan' => 0,
                        'volume_aktual' => 0,
                        'price_aktual' => 0,
                        'sort_order' => $sort++,
                    ];
                }

                // 2. Create Project Batch record
                $superProject = \App\Models\ProjectBatch::create([
                    'id' => $newId,
                    'project_name' => "BATCH PROCUREMENT: " . $request->po_number,
                    'customer' => $firstProject->customer,
                    'fase' => Project::PHASE_PROCUREMENT,
                    'branch' => $firstProject->branch,
                    'po_number' => $request->po_number,
                    'mitra_id' => $request->mitra_id,
                    'dasar_pekerjaan_file_path' => $path,
                    'created_by' => auth()->id(),
                ]);

                if (!empty($aggregatedBoqs)) {
                    foreach ($aggregatedBoqs as $boqData) {
                        \App\Models\ProjectBatchBoqDetail::create($boqData);
                    }
                }

                // 3. Generate & Save Consolidated BoQ Excel
                if (!empty($aggregatedBoqs)) {
                    $boqPath = $this->generateConsolidatedBoqExcel($superProject, $aggregatedBoqs);
                    $superProject->update(['boq_file_path' => $boqPath]);
                    
                    // Register as Evidence for display (Unified)
                    $superProject->unifiedEvidences()->create([
                        'type' => 'boq',
                        'file_name' => basename($boqPath),
                        'file_path' => $boqPath,
                        'file_extension' => 'xlsx',
                        'file_size' => \Illuminate\Support\Facades\Storage::disk('public')->exists($boqPath) ? \Illuminate\Support\Facades\Storage::disk('public')->size($boqPath) : 0,
                    ]);
                }

                // 4. Create ProcurementSp
                $sp = ProcurementSp::create([
                    'id' => $newId,
                    'po_number' => $request->po_number,
                    'po_file_path' => $path,
                    'created_by' => auth()->id(),
                ]);

                // Register PO as Evidence Procurement PO for Batch IF uploaded (Unified)
                if ($request->hasFile('po_evidence')) {
                    $superProject->unifiedEvidences()->create([
                        'type' => 'procurement_po',
                        'file_name' => $request->file('po_evidence')->getClientOriginalName(),
                        'file_path' => $path,
                        'file_extension' => $request->file('po_evidence')->getClientOriginalExtension(),
                        'file_size' => $request->file('po_evidence')->getSize(),
                    ]);

                    // Automatically mark subfase as selesai (Unified)
                    $superProject->unifiedSubfases()->create([
                        'subfase_key' => Project::PHASE_PROCUREMENT . '_po',
                        'status' => 'selesai'
                    ]);
                }

                // Always mark selection subfase as selesai for any batch (Unified)
                $superProject->unifiedSubfases()->create([
                    'subfase_key' => Project::PHASE_PROCUREMENT . '_selection',
                    'status' => 'selesai'
                ]);

                // 5. Update Constituent Projects
                Project::whereIn('id', $request->project_ids)->update([
                    'batch_id' => $newId,
                    'fase' => Project::PHASE_PROCUREMENT,
                ]);

                return response()->json([
                    'success' => true,
                    'message' => "Batch berhasil diproses dengan ID: {$newId}. BOQ telah digabung dan projects diteruskan ke fase Konstruksi.",
                    'id' => $newId,
                    'redirect_url' => route('project-batch.index')
                ]);
            });
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => "Gagal memproses batch: " . $e->getMessage()
            ], 500);
        }
    }

    public function processCommerceRekonBatch(Request $request): JsonResponse
    {
        $request->validate([
            'batch_ids' => 'required|array',
            'batch_ids.*' => 'exists:project_batches,id',
        ]);

        try {
            return DB::transaction(function () use ($request) {
                // 1. Generate ID (TGIDRC-YYYYMMDD-XXXX)
                $datePrefix = "TGIDRC-" . now()->format('Ymd');
                $lastRekon = \App\Models\CommerceRekon::where('id', 'LIKE', "{$datePrefix}%")
                    ->orderBy('id', 'desc')
                    ->first();

                $sequence = 1;
                if ($lastRekon) {
                    $lastId = $lastRekon->id;
                    $lastSequence = (int)substr($lastId, strrpos($lastId, '-') + 1);
                    $sequence = $lastSequence + 1;
                }
                $newId = "{$datePrefix}-" . str_pad($sequence, 4, '0', STR_PAD_LEFT);

                // 2. Aggregate BoQ Items from selected Batches
                $allBoqItems = \App\Models\ProjectBatchBoqDetail::whereIn('project_batch_id', $request->batch_ids)
                    ->get()
                    ->groupBy(function ($item) {
                        return $item->designator . '|' . $item->description;
                    });

                $aggregatedBoqs = [];
                $sort = 1;
                foreach ($allBoqItems as $key => $items) {
                    $first = $items->first();
                    $aggregatedBoqs[] = [
                        'commerce_rekon_id' => $newId,
                        'designator' => $first->designator,
                        'description' => $first->description,
                        'volume_planning' => $items->sum('volume_planning'),
                        'price_planning' => $first->price_planning,
                        'volume_pemenuhan' => $items->sum('volume_pemenuhan'),
                        'volume_aktual' => $items->sum('volume_aktual'),
                        'price_aktual' => $first->price_aktual,
                        'sort_order' => $sort++,
                    ];
                }

                // 3. Create Rekon Record
                $rekon = \App\Models\CommerceRekon::create([
                    'id' => $newId,
                    'rekon_number' => 'PENDING',
                    'fase' => Project::PHASE_REKON, 
                    'created_by' => auth()->id(),
                ]);

                // 4. Store Aggregated BoQ
                foreach ($aggregatedBoqs as $boqData) {
                    \App\Models\CommerceRekonBoqDetail::create($boqData);
                }

                // Auto-mark rekonsiliasi as done (Unified)
                \App\Models\UnifiedSubfase::updateOrCreate(
                    ['faseable_id' => $newId, 'faseable_type' => \App\Models\CommerceRekon::class, 'subfase_key' => 'rekonsiliasi'],
                    ['status' => 'selesai']
                );

                // 5. Update Batches
                \App\Models\ProjectBatch::whereIn('id', $request->batch_ids)->update([
                    'rekon_id' => $newId,
                    'fase' => Project::PHASE_REKON 
                ]);

                return response()->json([
                    'success' => true,
                    'message' => "Commerce Rekon berhasil dibuat dengan ID: {$newId}. Silakan lengkapi evidence di menu Dashboard.",
                    'id' => $newId,
                    'redirect_url' => route('commerce-rekon.index')
                ]);
            });
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => "Gagal memproses rekon: " . $e->getMessage()
            ], 500);
        }
    }
    
    public function processFinanceRekonBatch(Request $request): JsonResponse
    {
        $request->validate([
            'rekon_ids' => 'required|array',
            'rekon_ids.*' => 'exists:commerce_rekons,id',
            'apm_number' => 'required|string|max:255',
        ]);

        try {
            return DB::transaction(function () use ($request) {
                $datePrefix = "TGIDRF-" . now()->format('Ymd');
                $lastFinance = \App\Models\FinanceRekon::where('id', 'LIKE', "{$datePrefix}%")
                    ->orderBy('id', 'desc')
                    ->first();

                $sequence = 1;
                if ($lastFinance) {
                    $lastId = $lastFinance->id;
                    $lastSequence = (int)substr($lastId, strrpos($lastId, '-') + 1);
                    $sequence = $lastSequence + 1;
                }
                $newId = "{$datePrefix}-" . str_pad($sequence, 4, '0', STR_PAD_LEFT);

                $allBoqItems = \App\Models\CommerceRekonBoqDetail::whereIn('commerce_rekon_id', $request->rekon_ids)
                    ->where('designator', 'LIKE', 'J-%')
                    ->get()
                    ->groupBy(function ($item) {
                        return $item->designator . '|' . $item->description;
                    });

                $aggregatedBoqs = [];
                $sort = 1;
                $totalAmount = 0;

                foreach ($allBoqItems as $key => $items) {
                    $first = $items->first();
                    $volume = $items->sum('volume_planning');
                    $price = $first->price_planning;

                    $aggregatedBoqs[] = [
                        'finance_rekon_id' => $newId,
                        'designator' => $first->designator,
                        'description' => $first->description,
                        'volume' => $volume,
                        'price' => $price,
                        'sort_order' => $sort++,
                    ];

                    $totalAmount += ($volume * $price);
                }

                $finance = \App\Models\FinanceRekon::create([
                    'id' => $newId,
                    'apm_number' => $request->apm_number,
                    'fase' => Project::PHASE_FINANCE,
                    'total_amount' => $totalAmount,
                    'created_by' => auth()->id(),
                ]);

                $excelPath = $this->generateConsolidatedFinanceExcel($finance, $aggregatedBoqs);
                $finance->update(['boq_file_path' => $excelPath]);

                foreach ($aggregatedBoqs as $boqData) {
                    \App\Models\FinanceRekonBoqDetail::create($boqData);
                }

                \App\Models\UnifiedSubfase::updateOrCreate(
                    ['faseable_id' => $newId, 'faseable_type' => \App\Models\FinanceRekon::class, 'subfase_key' => 'apm_number'],
                    ['status' => 'selesai']
                );
                
                \App\Models\UnifiedEvidence::create([
                    'faseable_id' => $newId,
                    'faseable_type' => \App\Models\FinanceRekon::class,
                    'type' => 'apm_number',
                    'file_name' => "VALUE: " . $request->apm_number,
                    'file_path' => 'text://' . $request->apm_number,
                    'file_extension' => 'txt',
                    'file_size' => strlen($request->apm_number),
                ]);

                \App\Models\CommerceRekon::whereIn('id', $request->rekon_ids)->update([
                    'finance_rekon_id' => $newId,
                    'fase' => Project::PHASE_FINANCE
                ]);

                $warehouseIds = \App\Models\ProjectBatch::whereIn('rekon_id', $request->rekon_ids)
                    ->whereNotNull('warehouse_rekon_id')
                    ->pluck('warehouse_rekon_id')
                    ->unique();

                if ($warehouseIds->isNotEmpty()) {
                    \App\Models\WarehouseRekon::whereIn('id', $warehouseIds)->update([
                        'finance_rekon_id' => $newId,
                        'fase' => Project::PHASE_FINANCE
                    ]);
                }

                return response()->json([
                    'success' => true,
                    'message' => "Finance Rekon berhasil dibuat dengan ID: {$newId}. Silakan lengkapi evidence di Dashboard.",
                    'id' => $newId,
                    'redirect_url' => route('finance-rekon.index')
                ]);
            });
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => "Gagal memproses finance rekon: " . $e->getMessage()
            ], 500);
        }
    }

    public function warehouseRekonForm(Request $request)
    {
        $batchIds = $request->batch_ids;
        if (!$batchIds || !is_array($batchIds)) {
            return redirect()->route('tasks.index', ['role' => 'warehouse'])->with('error', 'Pilih minimal satu batch.');
        }

        $batches = \App\Models\ProjectBatch::whereIn('id', $batchIds)->get();
        
        $allBoqItems = \App\Models\ProjectBatchBoqDetail::whereIn('project_batch_id', $batchIds)
            ->where('designator', 'LIKE', 'M-%')
            ->get()
            ->groupBy(function ($item) {
                return $item->designator . '|' . $item->description;
            });

        $aggregatedBoqs = [];
        foreach ($allBoqItems as $key => $items) {
            $first = $items->first();
            $aggregatedBoqs[] = (object)[
                'designator' => $first->designator,
                'description' => $first->description,
                'volume_planning' => $items->sum('volume_planning'),
                'price_planning' => $first->price_planning,
                'volume_aktual' => $items->sum('volume_aktual'),
                'price_aktual' => $first->price_aktual,
            ];
        }

        return view('tasks.warehouse-rekon-form', compact('batches', 'aggregatedBoqs', 'batchIds'));
    }

    public function processWarehouseRekonBatch(Request $request): JsonResponse
    {
        $request->validate([
            'batch_ids' => 'required|array',
            'batch_ids.*' => 'exists:project_batches,id',
        ]);

        try {
            return DB::transaction(function () use ($request) {
                // 1. Generate ID (TGIDRM-YYYYMMDD-XXXX)
                $datePrefix = "TGIDRM-" . now()->format('Ymd');
                $lastRekon = \App\Models\WarehouseRekon::where('id', 'LIKE', "{$datePrefix}%")
                    ->orderBy('id', 'desc')
                    ->first();

                $sequence = 1;
                if ($lastRekon) {
                    $lastId = $lastRekon->id;
                    $lastSequence = (int)substr($lastId, strrpos($lastId, '-') + 1);
                    $sequence = $lastSequence + 1;
                }
                $newId = "{$datePrefix}-" . str_pad($sequence, 4, '0', STR_PAD_LEFT);

                // 2. Aggregate BoQ Items (M- only)
                $allBoqItems = \App\Models\ProjectBatchBoqDetail::whereIn('project_batch_id', $request->batch_ids)
                    ->get()
                    ->groupBy(function ($item) {
                        return $item->designator . '|' . $item->description;
                    });

                $aggregatedBoqs = [];
                $sort = 1;
                foreach ($allBoqItems as $key => $items) {
                    $first = $items->first();
                    $aggregatedBoqs[] = [
                        'warehouse_rekon_id' => $newId,
                        'designator' => $first->designator,
                        'description' => $first->description,
                        'volume_planning' => $items->sum('volume_planning'),
                        'price_planning' => $first->price_planning,
                        'volume_pemenuhan' => 0, // Placeholder
                        'volume_aktual' => $items->sum('volume_aktual'),
                        'price_aktual' => $first->price_aktual,
                        'sort_order' => $sort++,
                    ];
                }

                // Get rekon_number from first batch's commerce rekon
                $firstBatch = \App\Models\ProjectBatch::with('commerceRekon')->find($request->batch_ids[0]);
                $rekonNumber = $firstBatch && $firstBatch->commerceRekon ? $firstBatch->commerceRekon->rekon_number : 'REKON-AUTO-' . time();

                // 3. Create Record
                $rekon = \App\Models\WarehouseRekon::create([
                    'id' => $newId,
                    'fase' => Project::PHASE_WAREHOUSE,
                    'rekon_number' => $rekonNumber,
                    'created_by' => auth()->id(),
                ]);

                // 4. Store Aggregated BoQ
                foreach ($aggregatedBoqs as $boqData) {
                    \App\Models\WarehouseRekonBoqDetail::create($boqData);
                }

                // 5. Update Batches
                \App\Models\ProjectBatch::whereIn('id', $request->batch_ids)->update([
                    'warehouse_rekon_id' => $newId,
                    'fase' => Project::PHASE_WAREHOUSE
                ]);

                return response()->json([
                    'success' => true,
                    'message' => "Warehouse Rekon berhasil dibuat dengan ID: {$newId}. Silakan lengkapi volume pemenuhan di menu Dashboard.",
                    'id' => $newId,
                    'redirect_url' => route('rekon.index')
                ]);
            });
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => "Gagal memproses warehouse rekon: " . $e->getMessage()
            ], 500);
        }
    }

    public function updateWarehouseBoq(Request $request): JsonResponse
    {
        $request->validate([
            'id' => 'required|exists:warehouse_rekon_boq_details,id',
            'volume_pemenuhan' => 'required|numeric|min:0',
        ]);

        try {
            $item = \App\Models\WarehouseRekonBoqDetail::findOrFail($request->id);
            $volumePemenuhan = (float) $request->volume_pemenuhan;
            
            // Deviasi = Volume Pemenuhan - Volume Planning
            // Sesuai request user: "kalo krurang - kalo lebih +"
            $deviasi = $volumePemenuhan - $item->volume_planning;

            $item->update([
                'volume_pemenuhan' => $volumePemenuhan,
                'volume_deviasi' => $deviasi
            ]);

            // Sync Pemenuhan to constituent batches and sites
            $rekon = \App\Models\WarehouseRekon::find($item->warehouse_rekon_id);
            if ($rekon) {
                $this->syncPemenuhan($rekon, $item->designator, $volumePemenuhan);
            }

            return response()->json([
                'success' => true,
                'deviasi' => (float) $deviasi
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    private function generateConsolidatedWarehouseExcel(\App\Models\WarehouseRekon $rekon, array $aggregatedBoqs): string
    {
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('BOQ MATERIAL FINAL');

        $headers = [
            'No', 'Designator', 'Description', 
            'Volume PO', 'Price PO', 'Amount PO', 
            'Volume Pemenuhan', 'Amount Pemenuhan',
            'Deviasi (Gap)'
        ];
        
        $column = 'A';
        foreach ($headers as $header) {
            $sheet->setCellValue($column . '1', $header);
            $sheet->getStyle($column . '1')->getFont()->setBold(true);
            $sheet->getStyle($column . '1')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FFD9EAD3');
            $column++;
        }

        $row = 2;
        foreach ($aggregatedBoqs as $idx => $item) {
            $volPo = $item['volume_planning'];
            $pricePo = $item['price_planning'];
            $volPemenuhan = $item['volume_pemenuhan'];
            $deviasi = $item['volume_deviasi'];

            $sheet->setCellValue('A' . $row, $idx + 1);
            $sheet->setCellValue('B' . $row, $item['designator']);
            $sheet->setCellValue('C' . $row, $item['description']);
            $sheet->setCellValue('D' . $row, $volPo);
            $sheet->setCellValue('E' . $row, $pricePo);
            $sheet->setCellValue('F' . $row, $volPo * $pricePo);
            $sheet->setCellValue('G' . $row, $volPemenuhan);
            $sheet->setCellValue('H' . $row, $volPemenuhan * $pricePo);
            $sheet->setCellValue('I' . $row, $deviasi);
            $row++;
        }

        foreach (range('A', 'M') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $filename = "BOQ_MATERIAL_{$rekon->id}_" . time() . ".xlsx";
        $directory = "evidence/warehouse/boq_master";
        if (!\Illuminate\Support\Facades\Storage::disk('public')->exists($directory)) {
            \Illuminate\Support\Facades\Storage::disk('public')->makeDirectory($directory);
        }
        
        $path = "{$directory}/{$filename}";
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        
        $tempFile = tempnam(sys_get_temp_dir(), 'excel');
        $writer->save($tempFile);
        \Illuminate\Support\Facades\Storage::disk('public')->put($path, file_get_contents($tempFile));
        unlink($tempFile);

        return $path;
    }

    private function generateConsolidatedRekonExcel(\App\Models\CommerceRekon $rekon, array $aggregatedBoqs): string
    {
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('BOQ FINAL');

        $headers = [
            'No', 'Designator', 'Description', 
            'Volume PO', 'Price PO', 'Total Volume PO', 
            'Volume Pemenuhan', 'Total Volume Pemenuhan',
            'Volume Aktual', 'Price Aktual', 'Total Aktual'
        ];
        $column = 'A';
        foreach ($headers as $header) {
            $sheet->setCellValue($column . '1', $header);
            $sheet->getStyle($column . '1')->getFont()->setBold(true);
            $sheet->getStyle($column . '1')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FFD9EAD3');
            $column++;
        }

        // Data
        $row = 2;
        foreach ($aggregatedBoqs as $idx => $item) {
            $sheet->setCellValue('A' . $row, $idx + 1);
            $sheet->setCellValue('B' . $row, $item['designator']);
            $sheet->setCellValue('C' . $row, $item['description']);
            $sheet->setCellValue('D' . $row, $item['volume_planning']);
            $sheet->setCellValue('E' . $row, $item['price_planning']);
            $sheet->setCellValue('F' . $row, $item['volume_planning'] * $item['price_planning']);
            $sheet->setCellValue('G' . $row, $item['volume_pemenuhan']);
            $sheet->setCellValue('H' . $row, $item['volume_pemenuhan'] * $item['price_planning']);
            $sheet->setCellValue('I' . $row, $item['volume_aktual']);
            $sheet->setCellValue('J' . $row, $item['price_aktual']);
            $sheet->setCellValue('K' . $row, $item['volume_aktual'] * $item['price_aktual']);
            $row++;
        }

        // Auto size columns
        foreach (range('A', 'K') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $filename = "BOQ_FINAL_{$rekon->id}_" . time() . ".xlsx";
        $directory = "evidence/rekon/boq_master";
        if (!Storage::disk('public')->exists($directory)) {
            Storage::disk('public')->makeDirectory($directory);
        }
        
        $path = "{$directory}/{$filename}";
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $tempPath = tempnam(sys_get_temp_dir(), 'rekon');
        $writer->save($tempPath);
        Storage::disk('public')->put($path, file_get_contents($tempPath));
        unlink($tempPath);

        return $path;
    }

    private function generateConsolidatedBoqExcel(\App\Models\ProjectBatch $batch, array $aggregatedBoqs): string
    {
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Consolidated BoQ');

        $headers = ['Designator', 'Description', 'Volume Planning', 'Price Planning', 'Amount Planning'];

        // Styling headers (Blue/White style like Master Data)
        foreach ($headers as $index => $label) {
            $col = chr(65 + $index);
            $sheet->setCellValue($col . '1', $label);

            $style = $sheet->getStyle($col . '1');
            $style->getFont()->setBold(true)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('FFFFFF'));
            $style->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('4472C4');
            $style->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $rowNum = 2;
        foreach ($aggregatedBoqs as $item) {
            $sheet->setCellValue('A' . $rowNum, $item['designator']);
            $sheet->setCellValue('B' . $rowNum, $item['description']);
            $sheet->setCellValue('C' . $rowNum, $item['volume_planning']);
            $sheet->setCellValue('D' . $rowNum, $item['price_planning']);
            $sheet->setCellValue('E' . $rowNum, $item['volume_planning'] * $item['price_planning']);

            $sheet->getStyle('D' . $rowNum . ':E' . $rowNum)->getNumberFormat()->setFormatCode('#,##0');
            $rowNum++;
        }

        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $fileName = 'Consolidated_BoQ_' . $batch->id . '_' . time() . '.xlsx';
        $path = 'evidence/procurement/boq_batch/' . $fileName;

        if (!\Illuminate\Support\Facades\Storage::disk('public')->exists('evidence/procurement/boq_batch')) {
            \Illuminate\Support\Facades\Storage::disk('public')->makeDirectory('evidence/procurement/boq_batch');
        }

        $tempFile = tempnam(sys_get_temp_dir(), 'boq_b');
        $writer->save($tempFile);

        \Illuminate\Support\Facades\Storage::disk('public')->put($path, file_get_contents($tempFile));
        unlink($tempFile);

        return $path;
    }


    private function generateConsolidatedFinanceExcel(\App\Models\FinanceRekon $rekon, array $aggregatedBoqs): string
    {
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('REALISASI JASA FINAL');

        $headers = [
            'No', 'Designator', 'Description', 
            'Volume', 'Harga', 'Total'
        ];
        
        $column = 'A';
        foreach ($headers as $header) {
            $sheet->setCellValue($column . '1', $header);
            $sheet->getStyle($column . '1')->getFont()->setBold(true);
            $sheet->getStyle($column . '1')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FFD9EAD3');
            $column++;
        }

        $row = 2;
        foreach ($aggregatedBoqs as $idx => $item) {
            $sheet->setCellValue('A' . $row, $idx + 1);
            $sheet->setCellValue('B' . $row, $item['designator']);
            $sheet->setCellValue('C' . $row, $item['description']);
            $sheet->setCellValue('D' . $row, $item['volume']);
            $sheet->setCellValue('E' . $row, $item['price']);
            $sheet->setCellValue('F' . $row, $item['volume'] * $item['price']);
            $row++;
        }

        foreach (range('A', 'F') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $filename = "FINANCE_JASA_{$rekon->id}_" . time() . ".xlsx";
        $directory = "evidence/finance/boq_master";
        if (!\Illuminate\Support\Facades\Storage::disk('public')->exists($directory)) {
            \Illuminate\Support\Facades\Storage::disk('public')->makeDirectory($directory);
        }
        
        $path = "{$directory}/{$filename}";
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $tempFile = tempnam(sys_get_temp_dir(), 'excel');
        $writer->save($tempFile);
        \Illuminate\Support\Facades\Storage::disk('public')->put($path, file_get_contents($tempFile));
        unlink($tempFile);

        return $path;
    }

    private function getRoleLabel(string $role): string
    {
        return match ($role) {
                'commerce' => 'Commerce',
                'procurement' => 'Procurement',
                'konstruksi' => 'Konstruksi',
                'warehouse' => 'Warehouse',
                'finance' => 'Finance',
                default => ucwords($role),
            };
    }

    private function isPhaseSubmitted(string $role, string $currentPhase): bool
    {
        $phaseHierarchy = [
            'planning' => 1,
            'procurement' => 2,
            'konstruksi' => 3,
            'commerce' => 4,
            'rekon' => 4,
            'warehouse' => 5,
            'finance' => 6,
            'closed' => 7
        ];

        $targetLevel = $phaseHierarchy[$role] ?? 0;
        $currentLevel = $phaseHierarchy[$currentPhase] ?? 0;

        return $currentLevel > $targetLevel;
    }

    public function getSubPhasesForRole(string $role): array
    {
        return match ($role) {
            'procurement' => [
                'procurement_selection' => 'Pemilihan Mitra & Nomor PO',
                'procurement_po' => 'Upload Evidence PO',
            ],
            'konstruksi' => [
                'konstruksi_survey' => 'Survey Lapangan',
                'konstruksi_permit' => 'Perizinan / Permit',
                'konstruksi_delivery' => 'Delivery Material',
                'konstruksi_installasi' => 'Instalasi Mandor',
                'konstruksi_teskon' => 'Comtest & Uji',
            ],
            'commerce' => [
                'rekonsiliasi' => 'Rekonsiliasi (Proses)',
                'rekon_number' => 'Input Nomor Rekon',
                'rekon_evidence' => 'Upload Evidence Rekon',
            ],
            'warehouse' => [
                'pemenuhan_material' => 'Volume Pemenuhan',
                'warehouse_evidence' => 'Evidence Rekon',
            ],
            'finance' => [
                'apm_number' => 'Nomor APM',
                'finance_ba' => 'Evidence Finance',
            ],
            default => [],
        };
    }

    private function checkAllSubphasesDone($project, string $role): bool
    {
        $subPhases = $this->getSubPhasesForRole($role);
        if (empty($subPhases))
            return false;

        $statuses = $project->unifiedSubfases->pluck('status', 'subfase_key');

        foreach (array_keys($subPhases) as $key) {
            $isStatusSelesai = ($statuses[$key] ?? '') === 'selesai';

            if (!$isStatusSelesai) {
                return false;
            }
        }
        return true;
    }

    private function getSubmitRouteForRole(string $role): string
    {
        return match ($role) {
            'procurement' => 'project-data.procurement1.submit',
            'konstruksi' => 'project-data.konstruksi.submit',
            'commerce' => 'project-data.commerce.submit',
            'warehouse' => 'project-data.warehouse.submit',
            'finance' => 'project-data.finance.submit',
            default => 'dashboard',
        };
    }

    private function getSubmitLabelForRole(string $role): string
    {
        return match ($role) {
                'procurement' => 'Submit Procurement',
                'konstruksi' => 'Submit Konstruksi',
                'commerce' => 'Submit Commerce',
                'warehouse' => 'Submit Warehouse',
                'finance' => 'Submit Finance',
                default => 'Submit Stage',
            };
    }

    public function downloadCommerceRekonFile($id, $type)
    {
        $rekon = \App\Models\CommerceRekon::findOrFail($id);
        $path = $type === 'excel' ? $rekon->boq_file_path : $rekon->evidence_path;

        if (!$path || !\Illuminate\Support\Facades\Storage::disk('public')->exists($path)) {
            return back()->with('error', 'File tidak ditemukan.');
        }

        return \Illuminate\Support\Facades\Storage::disk('public')->download($path);
    }

    public function downloadWarehouseRekonFile($id, $type)
    {
        $rekon = \App\Models\WarehouseRekon::findOrFail($id);
        $path = $type === 'excel' ? $rekon->boq_file_path : $rekon->rekon_file_path;

        if (!$path || !\Illuminate\Support\Facades\Storage::disk('public')->exists($path)) {
            return back()->with('error', 'File tidak ditemukan.');
        }

        return \Illuminate\Support\Facades\Storage::disk('public')->download($path);
    }

    public function downloadFinanceRekonFile($id, $type)
    {
        $rekon = \App\Models\FinanceRekon::findOrFail($id);
        $path = $type === 'excel' ? $rekon->boq_file_path : $rekon->evidence_path;

        if (!$path || !\Illuminate\Support\Facades\Storage::disk('public')->exists($path)) {
            return back()->with('error', 'File tidak ditemukan.');
        }

        return \Illuminate\Support\Facades\Storage::disk('public')->download($path);
    }

    public function downloadBatchBoq($id)
    {
        $batch = \App\Models\ProjectBatch::findOrFail($id);
        $path = $batch->boq_file_path;

        if (!$path || !\Illuminate\Support\Facades\Storage::disk('public')->exists($path)) {
            return back()->with('error', 'File tidak ditemukan.');
        }

        return \Illuminate\Support\Facades\Storage::disk('public')->download($path);
    }
}
