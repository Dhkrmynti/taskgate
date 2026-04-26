@php
    $viewOnly = $viewOnly ?? false;
    $totalCount = count($currentSubPhases);
    $finishedCount = 0;
    $currentIndex = -1;
    $tempIndex = 0;
    foreach($currentSubPhases as $sk => $sl) {
        $hasEv = $project->unifiedEvidences->where('type', $sk)->isNotEmpty();
        $st = $unifiedSubfaseStatuses[$sk] ?? ($hasEv ? 'selesai' : 'waiting');
        if($st === 'selesai') {
            $finishedCount++;
        } elseif ($currentIndex === -1) {
            $currentIndex = $tempIndex;
        }
        $tempIndex++;
    }

    // Default to first step if nothing started yet
    if ($currentIndex === -1 && $totalCount > 0 && !$allSubphasesDone) {
        $currentIndex = 0;
    }

    $progressPercent = 0;
    if ($totalCount > 1) {
        if ($allSubphasesDone) {
            $progressPercent = 100;
        } else {
            $progressPercent = ($currentIndex / ($totalCount - 1)) * 100;
        }
    } elseif ($totalCount === 1) {
        $progressPercent = $allSubphasesDone ? 100 : 0;
    }
@endphp

<style>
    .linear-stepper-wrapper {
        position: relative;
        padding: 2.5rem 0;
        width: 100%;
        overflow-x: auto;
        scroll-snap-type: x mandatory;
        -webkit-overflow-scrolling: touch;
        scrollbar-width: none; /* Firefox */
        -ms-overflow-style: none; /* IE 10+ */
    }
    .linear-stepper-wrapper::-webkit-scrollbar {
        display: none; /* Chrome/Safari */
    }

    .stepper-progress-track {
        position: absolute;
        top: 24px; /* Align with marker center */
        left: 0;
        right: 0;
        height: 4px;
        background: #e2e8f0;
        border-radius: 2px;
        z-index: 1;
    }
    .dark .stepper-progress-track { background: #1e293b; }

    .stepper-progress-bar {
        position: absolute;
        top: 0;
        left: 0;
        height: 100%;
        background: linear-gradient(to right, #10b981, #3b82f6);
        border-radius: 2px;
        transition: width 0.8s cubic-bezier(0.4, 0, 0.2, 1);
        z-index: 2;
    }

    .stepper-items-container {
        position: relative;
        display: flex;
        justify-content: space-between;
        z-index: 3;
        min-width: {{ max(450, $totalCount * 130) }}px;
        padding: 0 1rem;
    }

    @media (max-width: 640px) {
        .stepper-items-container {
            min-width: {{ max(100, $totalCount * 110) }}px;
        }
    }

    .stepper-item {
        flex: 1;
        display: flex;
        flex-direction: column;
        align-items: center;
        text-align: center;
        position: relative;
        scroll-snap-align: center;
    }

    .stepper-marker {
        width: 48px;
        height: 48px;
        border-radius: 12px;
        background: #fff;
        border: 2px solid #e2e8f0;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.4s cubic-bezier(0.34, 1.56, 0.64, 1);
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
        z-index: 4;
        margin-bottom: 12px;
    }
    .dark .stepper-marker { background: #0f172a; border-color: #1e293b; }

    .stepper-item.is-finished .stepper-marker {
        background: #10b981;
        border-color: #059669;
        color: white;
        box-shadow: 0 10px 15px -3px rgba(16, 185, 129, 0.2);
    }

    .stepper-item.is-current .stepper-marker {
        border-color: #3b82f6;
        color: #3b82f6;
        border-width: 3px;
        width: 52px;
        height: 52px;
        background: #eff6ff;
        box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.1);
        transform: translateY(-2px);
    }
    .dark .stepper-item.is-current .stepper-marker { background: #1e293b; }

    .stepper-label {
        font-size: 10px;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        color: #64748b;
        max-width: 120px;
        line-height: 1.2;
    }

    .stepper-item.is-current .stepper-label { color: #3b82f6; }
    .stepper-item.is-finished .stepper-label { color: #10b981; }

    .stepper-action-zone {
        margin-top: 8px;
        min-height: 40px;
    }

    @media (max-width: 640px) {
        .stepper-marker {
            width: 40px;
            height: 40px;
            border-radius: 10px;
        }
        .stepper-item.is-current .stepper-marker {
            width: 44px;
            height: 44px;
        }
        .stepper-progress-track {
            top: 20px;
        }
        .stepper-label {
            font-size: 8.5px;
            max-width: 80px;
        }
        .stepper-marker svg {
            width: 1rem;
            height: 1rem;
        }
    }
</style>

<div class="linear-stepper-wrapper">
    <div class="relative px-12">
        <div class="stepper-progress-track">
            <div class="stepper-progress-bar" style="width: {{ $progressPercent }}%"></div>
        </div>

        <div class="stepper-items-container">
            @php 
                $isPreviousFinished = true;
                $nodeIdx = 0;
            @endphp
            
            @foreach ($currentSubPhases as $subKey => $subLabel)
                @php
                    $evidenceFile = $project->unifiedEvidences->where('type', $subKey)->first();
                    $hasEvidence = (bool)$evidenceFile;
                    $status = $unifiedSubfaseStatuses[$subKey] ?? ($hasEvidence ? 'selesai' : 'waiting');
                    
                    $isCurrent = ($currentIndex === $nodeIdx);
                    $isFinished = ($status === 'selesai');
                    $isLocked = !$isPreviousFinished;
                    
                    $stateClass = '';
                    if ($isFinished) $stateClass = 'is-finished';
                    elseif ($isCurrent) $stateClass = 'is-current';
                    elseif ($isLocked) $stateClass = 'is-locked';
                @endphp

                <div class="stepper-item {{ $stateClass }}">
                    <div class="stepper-marker">
                        @if($isFinished)
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                            </svg>
                        @elseif($status === 'ogp')
                            <div class="relative flex h-6 w-6 items-center justify-center">
                                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-blue-400 opacity-20"></span>
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-blue-600 animate-pulse" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 2" />
                                </svg>
                            </div>
                        @elseif($isLocked)
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 opacity-40" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                            </svg>
                        @else
                            <span class="text-xs font-black">{{ $nodeIdx + 1 }}</span>
                        @endif
                    </div>

                    <div class="stepper-label">
                        {{ $subLabel }}
                        @if($isCurrent)
                            <div class="mt-1"><span class="inline-flex items-center rounded-full bg-blue-100 px-2 py-0.5 text-[8px] font-black text-blue-700 dark:bg-blue-900/40 dark:text-blue-300">AKTIF</span></div>
                        @endif
                    </div>

                    <div class="stepper-action-zone">
                        @if(!$viewOnly && $canUpload && ($isCurrent || $isFinished))
                            <div class="flex flex-col items-center gap-1.5">
                                @if($isCurrent && $status === 'waiting')
                                    <form action="{{ route('project-data.subfase-status.update', $project) }}" method="POST">
                                        @csrf
                                        <input type="hidden" name="subfase_key" value="{{ $subKey }}">
                                        <input type="hidden" name="status" value="ogp">
                                        <input type="hidden" name="redirect_to" value="tasks.manage">
                                        <input type="hidden" name="role" value="{{ $role ?? '' }}">
                                        <button type="submit" class="rounded-lg bg-slate-900 px-3 py-1.5 text-[10px] font-bold text-white transition hover:bg-slate-800 active:scale-95">
                                            Mulai Progres
                                        </button>
                                    </form>
                                @endif

                                @if($hasEvidence)
                                    @if(str_starts_with($evidenceFile->file_path, 'text://'))
                                        <div class="mt-0.5 px-2 py-0.5 rounded bg-slate-100 dark:bg-slate-800 border border-slate-200 dark:border-slate-700">
                                            <span class="text-[9px] font-black text-slate-600 dark:text-slate-400">
                                                {{ str_replace('text://', '', $evidenceFile->file_path) }}
                                            </span>
                                        </div>
                                    @else
                                        <a href="{{ route('project-data.evidence-files.download', [$project->id, $evidenceFile->id]) }}" target="_blank" class="inline-flex items-center gap-1 text-[10px] font-black text-blue-600 hover:underline">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                            LIHAT EVIDENCE
                                        </a>
                                    @endif
                                @endif

                                @if(!($isAlreadySubmitted ?? false))
                                    @if($subKey === 'rekon_number' || $subKey === 'apm_number')
                                        <form action="{{ route('project-data.evidences.store', $project) }}" method="POST" class="flex gap-1">
                                            @csrf
                                            <input type="hidden" name="type" value="{{ $subKey }}">
                                            <input type="hidden" name="redirect_to" value="tasks.manage">
                                            <input type="hidden" name="role" value="{{ $role ?? '' }}">
                                            @php
                                                $currentVal = $hasEvidence && str_starts_with($evidenceFile->file_path, 'text://') 
                                                    ? str_replace('text://', '', $evidenceFile->file_path) 
                                                    : '';
                                            @endphp
                                            <input type="text" name="value" placeholder="Input..." required value="{{ $currentVal }}"
                                                class="w-20 rounded-lg border border-slate-200 bg-white px-2 py-1 text-[10px] outline-none focus:border-blue-400 dark:border-slate-700 dark:bg-slate-900">
                                            <button type="submit" class="rounded-lg bg-blue-600 px-2.5 py-1 text-[10px] font-bold text-white transition hover:bg-blue-700">SET</button>
                                        </form>
                                    @elseif($subKey === 'rekonsiliasi')
                                        <form action="{{ route('project-data.subfase-status.update', $project) }}" method="POST">
                                            @csrf
                                            <input type="hidden" name="subfase_key" value="{{ $subKey }}">
                                            <input type="hidden" name="status" value="selesai">
                                            <input type="hidden" name="redirect_to" value="tasks.manage">
                                            <input type="hidden" name="role" value="{{ $role ?? '' }}">
                                            <button type="submit" class="rounded-lg bg-green-600 px-3 py-1.5 text-[10px] font-black text-white transition hover:bg-green-700 active:scale-95">
                                                SELESAI REKON
                                            </button>
                                        </form>
                                    @elseif($subKey === 'pemenuhan_material')
                                        <form action="{{ route('project-data.subfase-status.update', $project) }}" method="POST">
                                            @csrf
                                            <input type="hidden" name="subfase_key" value="{{ $subKey }}">
                                            <input type="hidden" name="status" value="selesai">
                                            <input type="hidden" name="redirect_to" value="tasks.manage">
                                            <input type="hidden" name="role" value="{{ $role ?? '' }}">
                                            <button type="submit" class="rounded-lg bg-blue-600 px-3 py-1.5 text-[10px] font-black text-white transition hover:bg-blue-700 active:scale-95">
                                                KONFIRMASI
                                            </button>
                                        </form>
                                    @elseif($subKey === 'procurement_selection')
                                        <span class="text-[9px] font-black text-green-600 uppercase tracking-widest">Tervalidasi</span>
                                    @else
                                        <form action="{{ route('project-data.evidences.store', $project) }}" method="POST" enctype="multipart/form-data" class="evidence-upload-form">
                                            @csrf
                                            <input type="hidden" name="type" value="{{ $subKey }}">
                                            <input type="hidden" name="redirect_to" value="tasks.manage">
                                            <input type="hidden" name="role" value="{{ $role ?? '' }}">
                                            <input type="file" name="evidence_file" accept=".jpg,.jpeg,.png,.pdf,.csv,.xlsx" 
                                                onchange="this.form.submit()" class="hidden" id="file-{{ $subKey }}">
                                            <label for="file-{{ $subKey }}" class="inline-flex cursor-pointer items-center justify-center rounded-lg border-2 border-dashed border-blue-200 bg-blue-50/50 px-4 py-1.5 text-[10px] font-black text-blue-600 transition hover:border-blue-400 hover:bg-blue-100/50 dark:border-blue-900/30 dark:bg-blue-900/20 dark:text-blue-400">
                                                <span class="hidden sm:inline">{{ $hasEvidence ? 'RE-UPLOAD' : 'UPLOAD EVIDENCE' }}</span>
                                                <span class="sm:hidden">{{ $hasEvidence ? 'RE-UP' : 'UPLOAD' }}</span>
                                            </label>
                                        </form>
                                    @endif
                                @else
                                    <div class="flex items-center gap-1.5 py-1 px-2.5 rounded-lg bg-emerald-50 dark:bg-emerald-900/20 text-emerald-600 dark:text-emerald-400 border border-emerald-100 dark:border-emerald-800/50">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" /></svg>
                                        <span class="text-[9px] font-black uppercase tracking-wider">Submitted</span>
                                    </div>
                                @endif
                            </div>
                        @endif
                    </div>
                </div>
                
                @php 
                    $isPreviousFinished = $isFinished;
                    $nodeIdx++; 
                @endphp
            @endforeach
        </div>
    </div>
</div>

@if ($allSubphasesDone && !($isAlreadySubmitted ?? false))
    <div class="mt-8 animate-in fade-in slide-in-from-bottom-4 duration-700 rounded-2xl border border-emerald-500/20 bg-emerald-500/5 p-6 backdrop-blur-sm">
        <div class="flex items-center justify-between gap-6 flex-wrap">
            <div class="flex items-center gap-4">
                <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-emerald-500 text-white shadow-xl shadow-emerald-500/30">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                </div>
                <div>
                    <h4 class="text-sm font-black text-emerald-800 dark:text-emerald-300 uppercase tracking-wider">Tahapan Selesai!</h4>
                    <p class="text-xs font-bold text-emerald-700/60 dark:text-emerald-400/60">Seluruh berkas evidence telah divalidasi dan siap untuk disubmit.</p>
                </div>
            </div>
            <form action="{{ route($submitRoute, $project) }}" method="POST" data-confirm-form data-confirm-title="Konfirmasi Submit {{ $roleLabel }}" data-confirm-message="Apakah Anda yakin seluruh evidence sudah benar dan siap dikirim ke tahap selanjutnya?">
                @csrf
                <input type="hidden" name="role" value="{{ $role ?? '' }}">
                <button type="submit" class="group relative inline-flex h-12 items-center justify-center overflow-hidden rounded-2xl bg-emerald-500 px-8 font-black text-white shadow-xl shadow-emerald-500/20 transition-all hover:bg-emerald-600 hover:shadow-emerald-500/40 active:scale-95">
                    <span class="relative z-10 flex items-center gap-2">
                        {{ $submitLabel ?? 'SUBMIT BATCH' }}
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 transition-transform group-hover:translate-x-1" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                    </span>
                </button>
            </form>
        </div>
    </div>
@endif

@push('scripts')
<script type="module">
document.addEventListener('DOMContentLoaded', function() {
    const uploadForms = document.querySelectorAll('.evidence-upload-form');
    
    uploadForms.forEach(form => {
        const fileInput = form.querySelector('input[type="file"]');
        
        fileInput.addEventListener('change', function() {
            if (!this.files.length) return;
            
            const formData = new FormData(form);
            const fileName = this.files[0].name;
            
            const xhr = new XMLHttpRequest();
            xhr.open('POST', form.getAttribute('action'), true);
            xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
            
            if (window.trackUpload) {
                window.trackUpload(xhr, fileName);
            }
            
            xhr.onload = function() {
                if (xhr.status >= 200 && xhr.status < 300) {
                    if (window.showToast) {
                        window.showToast('Evidence telah disimpan.', 'success');
                    }
                    setTimeout(() => {
                        window.location.reload();
                    }, 1000);
                } else {
                    let msg = 'Gagal upload file.';
                    try {
                        const res = JSON.parse(xhr.responseText);
                        msg = res.message || msg;
                    } catch(e) {}
                    if (window.showToast) {
                        window.showToast(msg, 'error');
                    }
                }
            };
            
            xhr.send(formData);
        });
    });
});
</script>
@endpush
