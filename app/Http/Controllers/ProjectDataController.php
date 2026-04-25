<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\ProjectBoqDetail;
use App\Models\ProcurementSp;
use App\Models\ProjectBatch;
use App\Models\UnifiedEvidence;
use App\Models\UnifiedSubfase;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Yajra\DataTables\Facades\DataTables;

use App\Traits\HandlesPhaseStepper;

use App\Traits\HandlesBoqSync;

class ProjectDataController extends Controller
{
    use HandlesBoqSync;
    use HandlesPhaseStepper;
    protected $taskController;

    public function __construct(\App\Http\Controllers\TaskController $taskController)
    {
        $this->taskController = $taskController;
    }

    public function index()
    {
        $activeBatches = \App\Models\ProjectBatch::withCount(['projects'])
            ->where('fase', '!=', 'close')
            ->orderBy('created_at', 'desc')
            ->get();

        foreach ($activeBatches as $batch) {
            $batch->progress_percent = $this->calculateBatchProgress($batch);
        }

        return view('project-data.index', compact('activeBatches'));
    }

    private function getPhaseLabel(string $phase): string
    {
        $timeline = $this->getPhaseTimeline();
        return $timeline[$phase] ?? ucfirst($phase);
    }

    private function getPhaseTimeline(): array
    {
        return [
            Project::PHASE_PLANNING => 'Site Planning',
            Project::PHASE_PROCUREMENT => 'Procurement',
            Project::PHASE_KONSTRUKSI => 'Konstruksi',
            Project::PHASE_REKON => 'Commerce/Rekon',
            Project::PHASE_WAREHOUSE => 'Warehouse',
            Project::PHASE_FINANCE => 'Finance',
            Project::PHASE_CLOSED => 'Selesai/Close',
        ];
    }

    private function calculateBatchProgress($project)
    {
        $weights = [
            Project::PHASE_PLANNING => 0,
            Project::PHASE_PROCUREMENT => 20,
            Project::PHASE_KONSTRUKSI => 30,
            Project::PHASE_REKON => 20,
            Project::PHASE_WAREHOUSE => 20,
            Project::PHASE_FINANCE => 10,
        ];

        $currentPhase = $project->fase;
        if ($currentPhase === Project::PHASE_CLOSED) return 100;

        $baseProgress = 0;
        foreach ($weights as $phase => $weight) {
            if ($phase === $currentPhase) break;
            $baseProgress += $weight;
        }

        $subfaseWeight = $weights[$currentPhase] ?? 0;
        if ($subfaseWeight > 0) {
            $subfases = UnifiedSubfase::where('faseable_id', $project->id)
                ->where('faseable_type', get_class($project))
                ->where('subfase_key', 'LIKE', $currentPhase . '_%')
                ->get();

            if ($subfases->count() > 0) {
                $completed = $subfases->where('status', 'selesai')->count();
                $baseProgress += ($completed / $subfases->count()) * $subfaseWeight;
            }
        }

        return round($baseProgress);
    }

    public function create()
    {
        $options = [
            'customers' => DB::table('customers')->pluck('name'),
            'portofolios' => DB::table('portofolios')->pluck('name'),
            'programs' => DB::table('programs')->pluck('name'),
            'executionTypes' => DB::table('execution_types')->pluck('name'),
            'branches' => DB::table('branches')->pluck('name'),
            'pmProjects' => DB::table('pm_projects')->pluck('name'),
            'waspangs' => DB::table('waspangs')->pluck('name'),
        ];
        return view('project-data.create', compact('options'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'project_name' => 'required|string|max:255',
            'pid' => 'nullable|string|max:255|unique:projects,pid',
            'customer' => 'required|string|max:255',
        ]);

        $project = DB::transaction(function () use ($request) {
            $id = $this->generateNextProjectIdentifier();
            $project = Project::create([
                'id' => $id,
                'project_name' => $request->project_name,
                'pid' => $request->pid,
                'fase' => Project::PHASE_PLANNING,
                'customer' => $request->customer,
                'portofolio' => $request->portofolio,
                'program' => $request->program,
                'jenis_eksekusi' => $request->jenis_eksekusi,
                'branch' => $request->branch,
                'pm_project' => $request->pm_project,
                'waspang' => $request->waspang,
                'start_project' => $request->start_project,
                'end_project' => $request->end_project,
            ]);

            $rawItems = $request->boq_items ?: $request->boq_items_json;
            $hasStoredBoqItems = false;

            if ($rawItems) {
                $items = json_decode($rawItems, true);
                if (is_array($items)) {
                    foreach ($items as $index => $item) {
                        ProjectBoqDetail::create([
                            'project_id' => $project->id,
                            'designator' => $item['designator'] ?? '',
                            'description' => $item['description'] ?? ($item['uraian_pekerjaan'] ?? 'Item'),
                            'volume_planning' => $item['volume'] ?? ($item['volume_planning'] ?? 0),
                            'price_planning' => $item['price'] ?? ($item['price_planning'] ?? 0),
                            'sort_order' => $index + 1,
                        ]);
                    }

                    $hasStoredBoqItems = !empty($items);
                }
            }
            // 3. Process Evidence Dasar Pekerjaan
            if ($request->hasFile('evidence_dasar_files')) {
                foreach ($request->file('evidence_dasar_files') as $file) {
                    $path = $file->store('evidences/' . $id, 'public');
                    $project->unifiedEvidences()->create([
                        'type' => 'dasar_pekerjaan',
                        'file_name' => $file->getClientOriginalName(),
                        'file_path' => $path,
                        'file_extension' => $file->getClientOriginalExtension(),
                        'file_size' => $file->getSize(),
                    ]);
                }
            }

            // 4. Process BoQ File (Original from Creation)
            if ($request->hasFile('boq')) {
                $file = $request->file('boq');
                $path = $file->store('evidences/' . $id, 'public');
                $project->unifiedEvidences()->create([
                    'type' => 'boq',
                    'file_name' => $file->getClientOriginalName(),
                    'file_path' => $path,
                    'file_extension' => $file->getClientOriginalExtension(),
                    'file_size' => $file->getSize(),
                ]);

                // Fallback: make sure project_boq_details is populated even if
                // the front-end preview JSON was not submitted.
                if (!$hasStoredBoqItems) {
                    $this->syncBoqFromExcel(false, $project, storage_path('app/public/' . $path));
                }
            }

            return $project;
        });

        return redirect()->route('project-data.show', $project)->with('status', 'Project berhasil dibuat.');
    }

    public function data(Request $request): JsonResponse
    {
        $query = DB::table('projects');
        $this->applyFilters($query, $request);
        return DataTables::query($query)
            ->addColumn('action', function ($row) {
                return '<div class="flex justify-center"><a href="'.route('project-data.show', $row->id).'" class="inline-flex h-8 w-8 items-center justify-center rounded-lg border border-brand-line bg-white text-brand-text hover:bg-slate-50 transition dark:border-brand-darkLine dark:bg-[#161f35] dark:text-white"><svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg></a></div>';
            })
            ->editColumn('fase', fn($row) => $this->phaseLabelBadge((string) $row->fase))
            ->rawColumns(['action', 'fase'])
            ->toJson();
    }

    public function boqData(Request $request, $id): JsonResponse
    {
        $project = $this->resolveProject($id);
        
        $query = $project->boqDetails();
        
        if ($request->site_id && $request->site_id !== 'all') {
            $site = Project::findOrFail($request->site_id);
            $query = $site->boqDetails();
        }

        if ($request->type === 'jasa') {
            $query->where('designator', 'LIKE', 'J-%');
        }

        return DataTables::of($query)
            ->editColumn('price_planning', fn($row) => 'Rp ' . number_format($row->price_planning, 0))
            ->addColumn('amount', fn($row) => 'Rp ' . number_format($row->volume_planning * $row->price_planning, 0))
            ->editColumn('volume_planning', fn($row) => number_format($row->volume_planning, 0))
            ->make(true);
    }

    public function batchIndex() { return view('project-data.batch-index'); }

    public function batchData(Request $request): JsonResponse
    {
        $query = DB::table('project_batches');
        $this->applyFilters($query, $request);

        return DataTables::query($query)
            ->addColumn('action', function ($row) {
                $detailUrl = route('project-batch.show', $row->id);
                $manageUrl = route('tasks.manage', ['procurement', $row->id]);
                
                $buttons = '<div class="flex justify-center gap-2">';
                
                // Always show Detail button
                $buttons .= '<a href="'.$detailUrl.'" class="inline-flex h-8 w-8 items-center justify-center rounded-lg border border-brand-line bg-white text-brand-text hover:bg-slate-50 transition dark:border-brand-darkLine dark:bg-[#161f35] dark:text-white" title="Detail"><svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg></a>';

                // Show Manage Task button ONLY for procurement/admin if in procurement phase
                if ($row->fase === Project::PHASE_PROCUREMENT) {
                    $buttons .= '<a href="'.$manageUrl.'" class="inline-flex h-8 px-2 items-center justify-center rounded-lg border border-blue-100 bg-blue-50 text-blue-600 hover:bg-blue-600 hover:text-white transition dark:border-blue-900/30 dark:bg-blue-900/20 text-[10px] font-bold uppercase" title="Manage Task">Manage</a>';
                }

                $buttons .= '</div>';
                return $buttons;
            })

            ->editColumn('fase', fn($row) => $this->phaseLabelBadge((string) $row->fase))
            ->orderColumn('id', 'project_batches.id $1')
            ->rawColumns(['action', 'fase'])
            ->toJson();

    }

    private function phaseLabelBadge(string $phase): string
    {
        $label = $this->getPhaseLabel($phase);
        $color = match ($phase) { 
            Project::PHASE_PLANNING => 'bg-slate-100 text-slate-700', 
            Project::PHASE_PROCUREMENT => 'bg-blue-100 text-blue-700', 
            Project::PHASE_KONSTRUKSI => 'bg-amber-100 text-amber-700', 
            Project::PHASE_CLOSED => 'bg-emerald-100 text-emerald-700', 
            default => 'bg-purple-100 text-purple-700' 
        };
        return '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium '.$color.'">'.$label.'</span>';
    }

    private function applyFilters($query, Request $request)
    {
        if ($request->branch) $query->where('branch', $request->branch);
    }

    public function show($id)
    {
        $project = $this->resolveProject($id);
        $isBatch = ($project instanceof \App\Models\ProjectBatch) || 
                   ($project instanceof \App\Models\CommerceRekon) ||
                   ($project instanceof \App\Models\WarehouseRekon) ||
                   ($project instanceof \App\Models\FinanceRekon);
        
        $parentSp = null;
        if ($project instanceof Project && $project->batch_id) {
            $parentSp = $project->projectBatch;
            if ($parentSp) {
                $phaseHierarchy = ['planning' => 0, 'procurement' => 1, 'konstruksi' => 2, 'rekon' => 3, 'warehouse' => 4, 'finance' => 5, 'closed' => 6];
                $projectPhaseLevel = $phaseHierarchy[$project->fase] ?? 0;
                $batchPhaseLevel = $phaseHierarchy[$parentSp->fase] ?? 0;
                if ($batchPhaseLevel > $projectPhaseLevel) {
                    $project->fase = $parentSp->fase;
                }
            }
        }

        $stepperData = $this->getStepperData($project);
        $unifiedSubfaseStatuses = $stepperData['unifiedSubfaseStatuses'];
        $evidenceMap = $stepperData['evidenceMap'];

        $role = Auth::user()->role;
        $displayProject = $project;
        $phaseTimeline = $this->getPhaseTimeline();
        $boqDetails = $project->boqDetails;

        $procurement1SubPhases = ($role === 'admin' || $role === 'procurement') ? $this->taskController->getSubPhasesForRole('procurement') : [];
        $konstruksiSubPhases = ($role === 'admin' || $role === 'konstruksi') ? $this->taskController->getSubPhasesForRole('konstruksi') : [];
        $commerceSubPhases = ($role === 'admin' || $role === 'commerce') ? $this->taskController->getSubPhasesForRole('commerce') : [];
        $warehouseSubPhases = ($role === 'admin' || $role === 'warehouse') ? $this->taskController->getSubPhasesForRole('warehouse') : [];
        $financeSubPhases = ($role === 'admin' || $role === 'finance') ? $this->taskController->getSubPhasesForRole('finance') : [];
        
        $batchSubfaseStatuses = $parentSp ? $parentSp->unifiedSubfases->pluck('status', 'subfase_key') : collect();
        $batchEvidences = $parentSp ? $parentSp->unifiedEvidences : collect();

        $boqTotals = [
            'plan' => $boqDetails->sum(fn($i) => $i->volume_planning * $i->price_planning),
            'aktual' => $boqDetails->sum(fn($i) => $i->volume_aktual * $i->price_aktual)
        ];
        
        $phaseAction = $isBatch ? $this->getPhaseAction($project->fase) : null;
        $canEditBoq = $role === 'admin' || $role === 'commerce';

        $viewName = "project-data.roles.{$role}";
        if (!view()->exists($viewName)) {
            $viewName = 'project-data.show';
        }

        return view($viewName, compact(
            'project', 'boqDetails', 'unifiedSubfaseStatuses', 'parentSp', 'displayProject', 'phaseTimeline',
            'procurement1SubPhases', 'konstruksiSubPhases', 'commerceSubPhases', 'warehouseSubPhases', 'financeSubPhases',
            'evidenceMap', 'boqTotals', 'phaseAction', 'canEditBoq', 'batchSubfaseStatuses', 'batchEvidences', 'isBatch'
        ));
    }

    private function getPhaseAction($phase)
    {
        $actions = [
            Project::PHASE_PLANNING => ['type' => Project::PHASE_PROCUREMENT, 'label' => 'Submit to Procurement', 'description' => 'Siapkan BoQ dan Evidence Dasar Pekerjaan.'],
            Project::PHASE_PROCUREMENT => ['type' => Project::PHASE_KONSTRUKSI, 'label' => 'Submit to Konstruksi', 'description' => 'Selesaikan proses procurement.'],
            Project::PHASE_KONSTRUKSI => ['type' => Project::PHASE_REKON, 'label' => 'Submit to Commerce', 'description' => 'Selesaikan instalasi lapangan.'],
            Project::PHASE_REKON => ['type' => Project::PHASE_WAREHOUSE, 'label' => 'Submit to Warehouse', 'description' => 'Verifikasi rekon commerce.'],
            Project::PHASE_WAREHOUSE => ['type' => Project::PHASE_FINANCE, 'label' => 'Submit to Finance', 'description' => 'Verifikasi pemenuhan material.'],
            Project::PHASE_FINANCE => ['type' => Project::PHASE_CLOSED, 'label' => 'Close Project', 'description' => 'Finalisasi data finance.'],
        ];
        return $actions[$phase] ?? null;
    }

    public function storeEvidence(Request $request, $id)
    {
        $request->validate(['type' => 'required', 'evidence_files' => 'nullable|array|max:3', 'evidence_files.*' => 'nullable|file|max:10240', 'evidence_file' => 'nullable|file|max:10240', 'files.*' => 'nullable|file|max:10240']);
        
        $project = $this->resolveProject($id);
        $isBatch = ($project instanceof \App\Models\ProjectBatch) || 
                   ($project instanceof \App\Models\CommerceRekon) ||
                   ($project instanceof \App\Models\WarehouseRekon) ||
                   ($project instanceof \App\Models\FinanceRekon);

        // Strict Role-to-Subfase Authorization
        $role = Auth::user()->role;
        $type = $request->type;
        $allowed = false;

        if ($role === 'admin') $allowed = true;
        elseif ($role === 'procurement' && (str_starts_with($type, 'procurement_') || $type === 'boq' || $type === 'dasar_pekerjaan')) $allowed = true;
        elseif ($role === 'konstruksi' && str_starts_with($type, 'konstruksi_')) $allowed = true;
        elseif ($role === 'commerce' && (str_starts_with($type, 'rekon_') || $type === 'rekonsiliasi')) $allowed = true;
        elseif ($role === 'warehouse' && (str_starts_with($type, 'rekon_') || $type === 'pemenuhan_material')) $allowed = true;
        elseif ($role === 'finance' && (str_starts_with($type, 'apm_') || str_starts_with($type, 'finance_'))) $allowed = true;

        if (!$allowed) abort(403, "Role $role tidak diizinkan mengubah subfase $type.");
        
        $files = $request->file('files') ?: ($request->file('evidence_files') ?: ($request->file('evidence_file') ? [$request->file('evidence_file')] : []));
        
        if ($request->has('value')) {
            $project->unifiedSubfases()->updateOrCreate(
                ['subfase_key' => $request->type],
                ['status' => 'selesai']
            );
            
            // If it's a batch/rekon, update the specific column
            if ($request->type === 'rekon_number' || $request->type === 'apm_number') {
                $project->update([explode('_', $request->type)[0] . '_number' => $request->value]);
            }
            
            // Also store as a virtual evidence for history/display (Unified)
            $project->unifiedEvidences()->create([
                'type' => $request->type,
                'file_name' => "VALUE: " . $request->value,
                'file_path' => 'text://' . $request->value,
                'file_extension' => 'txt',
                'file_size' => strlen($request->value),
            ]);

            // It's already updated at line 367, so we'll just remove the legacy call at 387
        } else {
            foreach ($files as $file) {
                $path = $file->store('evidences/' . $id, 'public');
                $project->unifiedEvidences()->create([
                    'type' => $request->type, 
                    'file_name' => $file->getClientOriginalName(), 
                    'file_path' => $path, 
                    'file_extension' => $file->getClientOriginalExtension(), 
                    'file_size' => $file->getSize()
                ]);

                // Auto-update subfase status to selesai (Unified)
                $project->unifiedSubfases()->updateOrCreate(
                    ['subfase_key' => $request->type],
                    ['status' => 'selesai']
                );

                // Auto-sync rekon files
                if ($request->type === 'rekon_evidence' || $request->type === 'warehouse_evidence') {
                    $project->update(['rekon_file_path' => $path]);
                }
                if ($request->type === 'finance_ba') {
                    $project->update(['evidence_path' => $path]);
                }

                // Auto-sync BoQ details if file type is 'boq'
                if ($request->type === 'boq') {
                    $this->syncBoqFromExcel($isBatch, $project, storage_path('app/public/' . $path));
                }
            }
        }

        // Invalidate stepper cache
        $this->clearStepperCache($project);

        if ($request->redirect_to === 'tasks.manage') {
            return redirect()->route('tasks.manage', ['role' => $request->role, 'project' => $id])->with('status', 'Evidence uploaded.');
        }

        return redirect()->route($this->getProjectRoute($id), $id)->with('status', 'Evidence uploaded and BoQ synced.');
    }

    private function syncBoqFromExcel($isBatch, $project, $fullPath)
    {
        $extension = strtolower(pathinfo($fullPath, PATHINFO_EXTENSION));
        if ($extension === 'csv') {
            $reader = new \App\Support\SimpleCsvReader();
        } else {
            $reader = new \App\Support\SimpleXlsxReader();
        }

        try {
            $worksheets = $reader->read($fullPath);
            $rows = $worksheets[0]['rows'] ?? [];
            
            $itemsToInsert = [];
            
            // Skip Header (row index 0)
            for ($i = 1; $i < count($rows); $i++) {
                $row = $rows[$i];
                $designator = trim((string) ($row[2] ?? ''));
                if ($designator === '') continue;

                $volume = (float) ($row[3] ?? 0);
                
                // Cari di database KHS
                $khs = \App\Models\KhsRecord::query()
                    ->where(function($q) use ($designator) {
                        $q->where('data->designator_material', (string)$designator)
                          ->orWhere('data->designator_jasa', (string)$designator)
                          ->orWhere('data->designator', (string)$designator);
                    })
                    ->latest('id')
                    ->first();

                $description = '-';
                $price = 0;

                if ($khs) {
                    $khsData = $khs->data;
                    $description = $khsData['uraian_pekerjaan'] ?? '-';
                    $isMaterial = str_starts_with(strtoupper($designator), 'M-');
                    $isJasa = str_starts_with(strtoupper($designator), 'J-');
                    
                    if ($isMaterial) {
                        $price = (float) ($khsData['paket_5_material'] ?? 0);
                    } elseif ($isJasa) {
                        $price = (float) ($khsData['paket_5_jasa'] ?? 0);
                    } else {
                        $price = (float) ($khsData['paket_5_material'] ?: ($khsData['paket_5_jasa'] ?: 0));
                    }
                }

                $itemsToInsert[] = [
                    'designator' => $designator,
                    'description' => $description,
                    'volume_planning' => $volume,
                    'price_planning' => $price,
                    'sort_order' => $i
                ];
            }

            if (!empty($itemsToInsert)) {
                // Clear existing details
                $project->boqDetails()->delete();
                
                // Batch insert (create() inside loop or insert() but careful with timestamps)
                foreach ($itemsToInsert as $item) {
                    $project->boqDetails()->create($item);
                }
            }
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("Failed to sync BoQ for project {$project->id}: " . $e->getMessage());
        }
    }

    public function updateSubfaseStatus(Request $request, $id)
    {
        $request->validate(['subfase_key' => 'required', 'status' => 'required|in:waiting,ogp,selesai']);
        $project = $this->resolveProject($id);

        // Corrected to use only unified architecture
        $project->unifiedSubfases()->updateOrCreate(
            ['subfase_key' => $request->subfase_key], 
            ['status' => $request->status]
        );

        // Invalidate stepper cache
        $this->clearStepperCache($project);

        if ($request->redirect_to === 'tasks.manage') {
            return redirect()->route('tasks.manage', ['role' => $request->role, 'project' => $id])->with('status', 'Status updated.');
        }

        return request()->ajax() ? response()->json(['success' => true]) : redirect()->route($this->getProjectRoute($id), $id);
    }

    public function downloadEvidenceFile($id, $fileId)
    {
        $isBatch = str_starts_with($id, 'TGIDSP-') || str_starts_with($id, 'TGIDRC-') || str_starts_with($id, 'TGIDRM-') || str_starts_with($id, 'TGIDRF-');
        $project = $this->resolveProject($id);
        
        if ($fileId === 'batch-dp' && $isBatch) {
            return response()->file(storage_path('app/public/' . $project->dasar_pekerjaan_file_path));
        }

        if (in_array($fileId, ['commerce_rekon', 'warehouse_rekon', 'finance_rekon'])) {
            $batch = $isBatch ? $project : ($project->batch_id ? $project->projectBatch : null);
            if ($batch) {
                if ($fileId === 'commerce_rekon' && $batch->commerceRekon) {
                    return response()->file(storage_path('app/public/' . $batch->commerceRekon->rekon_file_path));
                }
                if ($fileId === 'warehouse_rekon' && $batch->warehouseRekon) {
                    return response()->file(storage_path('app/public/' . $batch->warehouseRekon->rekon_file_path));
                }
                if ($fileId === 'finance_rekon' && $batch->commerceRekon && $batch->commerceRekon->financeRekon) {
                    return response()->file(storage_path('app/public/' . $batch->commerceRekon->financeRekon->evidence_path));
                }
            }
            abort(404);
        }

        // 1. Try Unified Evidence (New Source of Truth)
        $evidenceFile = $project->unifiedEvidences()->find($fileId);
        
        // 2. If not found and it's a batch, try Site Evidence from its sites (Unified)
        if (!$evidenceFile && $isBatch) {
            $evidenceFile = \App\Models\UnifiedEvidence::whereIn('faseable_id', $project->projects->pluck('id'))
                ->where('faseable_type', Project::class)
                ->find($fileId);
        }

        if (!$evidenceFile) abort(404);

        return response()->file(storage_path('app/public/' . $evidenceFile->file_path));
    }

    public function submitProcurement1(Request $request, $id) { 
        $project = $this->resolveProject($id);
        if (!$this->isStageDone($project, 'procurement')) {
            return back()->with('error', 'Harap selesaikan semua sub-fase Procurement (PO & BoQ) terlebih dahulu.');
        }

        // Unified Mark completion
        $project->unifiedSubfases()->updateOrCreate(['subfase_key' => 'procurement_done'], ['status' => 'selesai']);

        $this->updateProjectPhase($project, Project::PHASE_KONSTRUKSI); 
        $this->clearStepperCache($project);
        return redirect()->route('dashboard')->with('status', 'Procurement diverifikasi.'); 
    }
    public function submitKonstruksi(Request $request, $id) { 
        $project = $this->resolveProject($id);
        if (!$this->isStageDone($project, 'konstruksi')) {
            return back()->with('error', 'Harap selesaikan semua sub-fase Konstruksi terlebih dahulu.');
        }

        // Unified Mark completion
        $project->unifiedSubfases()->updateOrCreate(['subfase_key' => 'konstruksi_done'], ['status' => 'selesai']);

        $this->updateProjectPhase($project, Project::PHASE_REKON); 
        $this->clearStepperCache($project);
        
        return redirect()->route('dashboard')->with('status', 'Konstruksi diverifikasi.'); 
    }
    public function submitCommerce(Request $request, $id) { 
        $project = $this->resolveProject($id);
        if (!$this->isStageDone($project, 'commerce')) {
            return back()->with('error', 'Harap selesaikan semua sub-fase Commerce terlebih dahulu.');
        }

        // Unified Mark completion
        $project->unifiedSubfases()->updateOrCreate(['subfase_key' => 'commerce_done'], ['status' => 'selesai']);

        // Check if Warehouse stage is done
        $warehouseDone = $this->isStageDone($project, 'warehouse');

        $this->updateProjectPhase($project, $warehouseDone ? Project::PHASE_FINANCE : Project::PHASE_REKON);
        
        $message = $warehouseDone ? 'Rekontruksi selesai. Data telah diteruskan ke Finance.' : 'Sub-fase Commerce berhasil disubmit.';
        return redirect()->route('dashboard')->with('status', $message);
    }

    public function submitWarehouse(Request $request, $id) { 
        $project = $this->resolveProject($id);
        if (!$this->isStageDone($project, 'warehouse')) {
            return back()->with('error', 'Harap selesaikan semua sub-fase Warehouse terlebih dahulu.');
        }

        // Unified Mark completion
        $project->unifiedSubfases()->updateOrCreate(['subfase_key' => 'warehouse_done'], ['status' => 'selesai']);

        // Check if Commerce stage is done
        $commerceDone = $this->isStageDone($project, 'commerce');

        $this->updateProjectPhase($project, $commerceDone ? Project::PHASE_FINANCE : Project::PHASE_WAREHOUSE);
        
        $message = $commerceDone ? 'Rekontruksi selesai. Data telah diteruskan ke Finance.' : 'Sub-fase Warehouse berhasil disubmit.';
        return redirect()->route('rekon.index')->with('status', $message);
    }

    public function submitFinance(Request $request, $id) { 
        $project = $this->resolveProject($id);
        if (!$this->isStageDone($project, 'finance')) {
            return back()->with('error', 'Harap selesaikan semua sub-fase Finance terlebih dahulu.');
        }
        
        $this->markSubfaseDoneRecursive($project, 'finance_done');
        $this->updateProjectPhase($project, Project::PHASE_CLOSED);
        return redirect()->route('finance-rekon.index')->with('status', 'Finance diverifikasi. Project Selesai (Closed).'); 
    }

    protected function markSubfaseDoneRecursive($model, string $key)
    {
        $model->unifiedSubfases()->updateOrCreate(['subfase_key' => $key], ['status' => 'selesai']);

        $constituents = collect();
        if (method_exists($model, 'constituents')) {
            $constituents = $model->constituents;
        } elseif (method_exists($model, 'batches')) {
            $constituents = $model->batches;
        } elseif ($model instanceof \App\Models\ProjectBatch) {
            $constituents = $model->projects;
        }

        foreach ($constituents as $child) {
            $this->markSubfaseDoneRecursive($child, $key);
        }
    }

    private function isStageDone($project, $role)
    {
        $markerKey = $role . '_done';
        
        // Unified Marker Check
        if ($project->unifiedSubfases()->where('subfase_key', $markerKey)->where('status', 'selesai')->exists()) {
            return true;
        }

        $subPhases = match ($role) {
            'procurement' => ['procurement_selection' => 1, 'procurement_po' => 1],
            'konstruksi' => ['konstruksi_survey' => 1, 'konstruksi_permit' => 1, 'konstruksi_delivery' => 1, 'konstruksi_installasi' => 1, 'konstruksi_teskon' => 1],
            'commerce' => ['rekonsiliasi' => 1, 'rekon_number' => 1, 'rekon_evidence' => 1],
            'warehouse' => ['pemenuhan_material' => 1, 'warehouse_evidence' => 1],
            'finance' => ['apm_number' => 1, 'finance_ba' => 1],
            default => [],
        };

        if (empty($subPhases)) return true;
        
        $stepperData = $this->getStepperData($project);
        $statuses = $stepperData['unifiedSubfaseStatuses'];
        
        foreach (array_keys($subPhases) as $key) {
            if (($statuses[$key] ?? '') !== 'selesai') return false;
        }
        return true;
    }

    // Removed propagatePhase as it is replaced by updateProjectPhase trait method

    private function resolveProject($id) {
        if (str_starts_with($id, 'TGIDSP-')) return \App\Models\ProjectBatch::findOrFail($id);
        if (str_starts_with($id, 'TGIDRC-')) return \App\Models\CommerceRekon::findOrFail($id);
        if (str_starts_with($id, 'TGIDRM-')) return \App\Models\WarehouseRekon::findOrFail($id);
        if (str_starts_with($id, 'TGIDRF-')) return \App\Models\FinanceRekon::findOrFail($id);
        return Project::findOrFail($id);
    }

    public function storeBoqItem(Request $request, $id) { 
        $project = $this->resolveProject($id);
        $project->boqDetails()->create($request->all()); 
        return redirect()->route($this->getProjectRoute($id), $id); 
    }
    
    public function updateBoqItem(Request $request, $id, $boqId) { 
        $project = $this->resolveProject($id);
        $boqItem = $project->boqDetails()->findOrFail($boqId);
        $boqItem->update($request->all()); 

        if ($request->has('volume_pemenuhan')) {
            $this->syncPemenuhan($project, $boqItem->designator, $request->volume_pemenuhan);
        }

        return response()->json(['success' => true]); 
    }
    
    public function destroyBoqItem($id, $boqId) { 
        $project = $this->resolveProject($id);
        $project->boqDetails()->findOrFail($boqId)->delete();
        return redirect()->route($this->getProjectRoute($id), $id); 
    }
    /**
     * Memverifikasi baris-baris BoQ dari file Excel/JSON terhadap database KHS.
     */
    public function verifyBoq(Request $request): JsonResponse
    {
        $items = [];
        $summary = ['total' => 0, 'valid' => 0, 'invalid' => 0];

        try {
            // 1. Parsing Input (Excel atau JSON)
            if ($request->hasFile('boq')) {
                $file = $request->file('boq');
                $extension = strtolower($file->getClientOriginalExtension());
                
                if ($extension === 'csv') {
                    $reader = new \App\Support\SimpleCsvReader();
                } else {
                    $reader = new \App\Support\SimpleXlsxReader();
                }

                $worksheets = $reader->read($file->getRealPath());
                $rows = $worksheets[0]['rows'] ?? [];
                
                // Skip Header (row index 0 in SimpleXlsxReader/SimpleCsvReader is row 1)
                for ($i = 1; $i < count($rows); $i++) {
                    $row = $rows[$i];
                    $designator = trim((string) ($row[2] ?? ''));
                    if ($designator === '') continue;

                    $items[] = [
                        'designator' => $designator,
                        'volume_planning' => (float) ($row[3] ?? 0),
                    ];
                }
            } else {
                $items = is_array($request->items) ? $request->items : (json_decode($request->items, true) ?: []);
            }

            // 2. Cross Check dengan KHS Database
            $validatedItems = [];
            foreach ($items as $item) {
                $designator = $item['designator'];
                $isMaterial = str_starts_with(strtoupper($designator), 'M-');
                $isJasa = str_starts_with(strtoupper($designator), 'J-');

                // Cari di database KHS (JSON Search)
                $khs = \App\Models\KhsRecord::query()
                    ->where(function($q) use ($designator) {
                        $q->where('data->designator_material', $designator)
                          ->orWhere('data->designator_jasa', $designator)
                          ->orWhere('data->designator', $designator); // Backwards compatibility for tests
                    })
                    ->latest('id')
                    ->first();

                if ($khs) {
                    $khsData = $khs->data;
                    $price = 0;
                    if ($isMaterial) {
                        $price = (float) ($khsData['paket_5_material'] ?? 0);
                    } elseif ($isJasa) {
                        $price = (float) ($khsData['paket_5_jasa'] ?? 0);
                    } else {
                        // Fallback jika tidak diawali M-/J- tapi ketemu di salah satu kolom
                        $price = (float) ($khsData['paket_5_material'] ?: ($khsData['paket_5_jasa'] ?: 0));
                    }

                    $validatedItems[] = [
                        'designator' => $designator,
                        'description' => $khsData['uraian_pekerjaan'] ?? '-',
                        'volume_planning' => $item['volume_planning'] ?? 0,
                        'price_planning' => $price,
                        'is_valid' => true,
                    ];
                    $summary['valid']++;
                } else {
                    $validatedItems[] = [
                        'designator' => $designator,
                        'description' => 'TIDAK DITEMUKAN DI KHS',
                        'volume_planning' => $item['volume_planning'] ?? 0,
                        'price_planning' => 0,
                        'is_valid' => false,
                    ];
                    $summary['invalid']++;
                }
                $summary['total']++;
            }

            return response()->json([
                'success' => true,
                'items' => $validatedItems,
                'summary' => $summary
            ]);

        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('BoQ Verification Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Gagal memproses file: ' . $e->getMessage()
            ], 500);
        }
    }
    public function downloadPlanningTemplate() 
    { 
        $path = storage_path('app/templates/boq-planning-template.xlsx');
        if (!file_exists($path)) {
            return back()->with('error', 'Template BoQ tidak ditemukan. Harap hubungi admin.');
        }
        return response()->download($path, 'template-boq-planning-taskgate.xlsx');
    }

    public function markNotificationAsRead($id)
    {
        $notification = auth()->user()->notifications()->findOrFail($id);
        $notification->markAsRead();

        if (isset($notification->data['url'])) {
            return redirect($notification->data['url']);
        }

        return back();
    }

    public function markAllNotificationsAsRead()
    {
        auth()->user()->unreadNotifications->markAsRead();
        return back()->with('status', 'Semua notifikasi telah ditandai dibaca.');
    }

    private function generateNextProjectIdentifier() { $prefix = 'TGIDOP-'.now()->format('Ymd').'-'; $last = Project::where('id', 'LIKE', $prefix.'%')->orderBy('id', 'desc')->first(); $num = $last ? (int) substr($last->id, -4) + 1 : 1; return $prefix.str_pad($num, 4, '0', STR_PAD_LEFT); }
    private function getProjectRoute($id) { 
        return str_starts_with($id, 'TGIDSP-') ? 'project-batch.show' : 'project-data.show'; 
    }

    public function getFinancialsByPid($pid): JsonResponse
    {
        $pid = trim($pid);
        $cacheKey = "financial_data_" . $pid;
        $result = cache()->remember($cacheKey, now()->addHour(), function() use ($pid) {
            // Default empty structure
            $data = [
            'budgeting' => ['release' => 0, 'actual' => 0, 'available' => 0],
            'material' => [
                'total' => ['release' => 0, 'actual' => 0, 'available' => 0],
                'stok' => ['release' => 0, 'actual' => 0, 'available' => 0],
                'non_stok' => ['release' => 0, 'actual' => 0, 'available' => 0],
            ],
            'non_material' => [
                'total' => ['release' => 0, 'actual' => 0, 'available' => 0],
                'jasa' => ['release' => 0, 'actual' => 0, 'available' => 0],
                'depresiasi' => ['release' => 0, 'actual' => 0, 'available' => 0],
                'operasional' => ['release' => 0, 'actual' => 0, 'available' => 0],
                'sewa' => ['release' => 0, 'actual' => 0, 'available' => 0],
                'tenaga_kerja' => ['release' => 0, 'actual' => 0, 'available' => 0],
            ],
            'performance' => [
                'revenue' => 0,
                'gpm' => 0,
                'gpm_percent' => 0,
            ]
        ];

        // Fetch records for this PID
        $records = \App\Models\FinanceRecord::where('project_id', $pid)->get();

        foreach ($records as $record) {
            $rd = $record->data;
            $tab = strtolower($record->tab_key);
            
            // Release/Actual keys usually vary, we'll try common patterns
            $release = (float) str_replace(['.', ','], ['', '.'], $rd['release'] ?? $rd['budget'] ?? $rd['plan'] ?? 0);
            $actual = (float) str_replace(['.', ','], ['', '.'], $rd['actual'] ?? $rd['realisasi'] ?? $rd['aktual'] ?? 0);
            $available = $release - $actual;

            if (str_contains($tab, 'budget') || str_contains($tab, 'dashboard')) {
                $data['budgeting']['release'] += $release;
                $data['budgeting']['actual'] += $actual;
                $data['budgeting']['available'] += $available;
                
                // Revenue/GPM usually on dashboard
                $data['performance']['revenue'] = (float) str_replace(['.', ','], ['', '.'], $rd['revenue'] ?? 0);
                $data['performance']['gpm'] = (float) str_replace(['.', ','], ['', '.'], $rd['gpm'] ?? 0);
            } 
            elseif (str_contains($tab, 'material')) {
                $type = str_contains(strtolower($rd['description'] ?? ''), 'non stok') ? 'non_stok' : 'stok';
                $data['material'][$type]['release'] += $release;
                $data['material'][$type]['actual'] += $actual;
                $data['material'][$type]['available'] += $available;
                
                $data['material']['total']['release'] += $release;
                $data['material']['total']['actual'] += $actual;
                $data['material']['total']['available'] += $available;
            }
            else {
                // Non-Material Mapping
                $subKey = 'operasional';
                if (str_contains($tab, 'jasa')) $subKey = 'jasa';
                elseif (str_contains($tab, 'depresiasi')) $subKey = 'depresiasi';
                elseif (str_contains($tab, 'sewa')) $subKey = 'sewa';
                elseif (str_contains($tab, 'tenaga') || str_contains($tab, 'labor')) $subKey = 'tenaga_kerja';

                $data['non_material'][$subKey]['release'] += $release;
                $data['non_material'][$subKey]['actual'] += $actual;
                $data['non_material'][$subKey]['available'] += $available;
                
                $data['non_material']['total']['release'] += $release;
                $data['non_material']['total']['actual'] += $actual;
                $data['non_material']['total']['available'] += $available;
            }
        }

        if ($data['performance']['revenue'] > 0) {
            $data['performance']['gpm_percent'] = ($data['performance']['gpm'] / $data['performance']['revenue']) * 100;
        }

        return $data;
        });

        return response()->json($result);
    }
}
