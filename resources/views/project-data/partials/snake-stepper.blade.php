@php
    $phases = [
        'planning' => ['label' => 'Planning', 'icon' => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2'],
        'procurement' => ['label' => 'Procurement', 'icon' => 'M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z'],
        'konstruksi' => ['label' => 'Konstruksi', 'icon' => 'M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4'],
        'rekon' => ['label' => 'Commerce', 'icon' => 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z'],
        'warehouse' => ['label' => 'Warehouse', 'icon' => 'M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4'],
        'finance' => ['label' => 'Finance', 'icon' => 'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z'],
        'closed' => ['label' => 'Closed', 'icon' => 'M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z']
    ];

    $subfaseMapping = [
        'procurement' => [
            'procurement_selection' => 'Selection & Vendor',
            'procurement_po' => 'PO Issued'
        ],
        'konstruksi' => [
            'konstruksi_survey' => 'Survey & Site Visit',
            'konstruksi_permit' => 'Perizinan (Permit)',
            'konstruksi_delivery' => 'Delivery Material',
            'konstruksi_installasi' => 'Installasi & Powering',
            'konstruksi_teskon' => 'Test & Commissioning'
        ],
        'rekon' => [
            'rekonsiliasi' => 'Rekonsiliasi',
            'rekon_number' => 'Nomor Rekon',
            'rekon_evidence' => 'Evidence Rekon'
        ],
        'warehouse' => [
            'pemenuhan_material' => 'Pemenuhan Mat',
            'warehouse_evidence' => 'Warehouse Evidence'
        ],
        'finance' => [
            'apm_number' => 'Nomor APM',
            'finance_ba' => 'Evidence Finance'
        ]
    ];

    $phaseKeys = array_keys($phases);
    $currentPhase = $project->fase;
    $currentIndex = array_search($currentPhase, $phaseKeys);
    if ($currentIndex === false) $currentIndex = 0;
    
    $totalPhases = count($phases);
    
    // Calculate effective visual progress based on subphases
    $visualIndex = $currentIndex;
    $allSubphasesOfCurrentDone = true;
    
    if (isset($subfaseMapping[$currentPhase])) {
        foreach (array_keys($subfaseMapping[$currentPhase]) as $sk) {
            if (($unifiedSubfaseStatuses[$sk] ?? '') !== 'selesai') {
                $allSubphasesOfCurrentDone = false;
                break;
            }
        }
    } else {
        $allSubphasesOfCurrentDone = false;
    }

    if ($allSubphasesOfCurrentDone && $currentIndex < ($totalPhases - 1)) {
        $visualIndex = $currentIndex + 1;
    }
    if ($currentPhase === 'closed') {
        $visualIndex = $totalPhases - 1;
    }
@endphp

<style>
    .snake-wrapper {
        position: relative;
        padding: 8rem 0;
        overflow-x: visible;
        overflow-y: visible;
    }
    
    .snake-container {
        position: relative;
        width: calc(100% - 100px);
        min-width: 480px; 
        height: 48px;
        margin: 0 auto;
    }

    .snake-node {
        position: absolute;
        top: 50%;
        z-index: 2;
        display: flex;
        flex-direction: column;
        align-items: center;
        transform: translate(-50%, -50%) scale(0.8);
        opacity: 0;
        transition: all 0.6s cubic-bezier(0.34, 1.56, 0.64, 1);
    }
    
    .snake-node.js-loaded {
        transform: translate(-50%, -50%) scale(1);
        opacity: 1;
    }

    .snake-node.is-current.js-loaded {
        transform: translate(-50%, -50%) scale(1.15);
        z-index: 10;
        opacity: 1;
    }

    .snake-marker {
        width: 32px;
        height: 32px;
        border-radius: 50%;
        background: #fff;
        border: 3px solid #e6e7f0;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.5s ease;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        cursor: pointer;
    }
    .dark .snake-marker {
        background: #0f172a;
        border-color: #1e293b;
    }

    .snake-node:not(.is-current):hover .snake-marker {
        transform: scale(1.2) rotate(5deg);
        border-color: #64748b;
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
    }

    .snake-node:hover {
        z-index: 50 !important;
    }

    .snake-label {
        position: absolute;
        width: 100px;
        text-align: center;
        font-weight: 700;
        color: #94a3b8;
        font-size: 0.65rem;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        transition: color 0.3s;
        left: 50%;
        transform: translateX(-50%);
        line-height: 1.2;
    }
    .dark .snake-label {
        color: #64748b;
    }
    .snake-node.top-label .snake-label {
        bottom: calc(100% + 5px);
    }
    .snake-node.bottom-label .snake-label {
        top: calc(100% + 5px);
    }

    .snake-node.is-finished .snake-marker {
        background: #10b981;
        border-color: #059669;
        color: white;
    }
    .snake-node.is-finished .snake-label {
        color: #059669;
    }
    .dark .snake-node.is-finished .snake-label {
        color: #10b981;
    }

    .snake-node.is-current .snake-marker {
        border-color: #3b82f6;
        color: #3b82f6;
        border-width: 4px;
        width: 40px;
        height: 40px;
        animation: snake-pulse 2s infinite;
    }
    .snake-node.is-current .snake-label {
        color: #3b82f6;
        font-size: 0.75rem;
    }

    @keyframes snake-pulse {
        0% { box-shadow: 0 0 0 0 rgba(59, 130, 246, 0.6); }
        70% { box-shadow: 0 0 0 15px rgba(59, 130, 246, 0); }
        100% { box-shadow: 0 0 0 0 rgba(59, 130, 246, 0); }
    }
    
    #snake-cursor {
        position: absolute;
        width: 10px;
        height: 10px;
        background: white;
        border: 2px solid #3b82f6;
        border-radius: 50%;
        box-shadow: 0 0 8px #3b82f6;
        transition: opacity 0.5s;
        z-index: 1;
        transform: translate(-50%, -50%);
        pointer-events: none;
        opacity: 0;
    }

    .snake-tooltip {
        position: absolute;
        bottom: 120%;
        left: 50%;
        transform: translateX(-50%) translateY(10px);
        background: #0f172a;
        padding: 1rem;
        border-radius: 12px;
        box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
        border: 1px solid rgba(255, 255, 255, 0.1);
        width: 220px;
        opacity: 0;
        visibility: hidden;
        transition: all 0.2s ease-out;
        pointer-events: auto; /* Changed from none to allow clicking */
        z-index: 9999;
    }

    /* Bridge to prevent gap between marker and tooltip */
    .snake-tooltip::before {
        content: '';
        position: absolute;
        left: 0;
        right: 0;
        height: 20px;
        z-index: -1;
    }

    .snake-node.top-label .snake-tooltip::before {
        top: 100%;
    }

    .snake-node.bottom-label .snake-tooltip::before {
        bottom: 100%;
    }

    .snake-node.bottom-label .snake-tooltip {
        top: 120%;
        bottom: auto;
        transform: translateX(-50%) translateY(-10px);
    }

    .snake-node:hover .snake-tooltip {
        opacity: 1;
        visibility: visible;
        transform: translateX(-50%) translateY(0);
    }

    .snake-tooltip::after {
        content: '';
        position: absolute;
        left: 50%;
        transform: translateX(-50%);
        border-width: 6px;
        border-style: solid;
    }

    .snake-node.top-label .snake-tooltip::after {
        top: 100%;
        border-color: rgba(15, 23, 42, 0.95) transparent transparent transparent;
    }

    .snake-node.bottom-label .snake-tooltip::after {
        bottom: 100%;
        border-color: transparent transparent rgba(15, 23, 42, 0.95) transparent;
    }

    @media (max-width: 768px) {
        .snake-wrapper {
            padding: 4rem 0;
        }
        .snake-container {
            width: calc(100% - 40px);
            min-width: unset;
        }
        .snake-node {
            transform: translate(-50%, -50%) scale(0.65);
        }
        .snake-node.js-loaded {
            transform: translate(-50%, -50%) scale(0.75);
        }
        .snake-node.is-current.js-loaded {
            transform: translate(-50%, -50%) scale(0.9);
        }
        .snake-label {
            font-size: 0.55rem;
            width: 70px;
        }
    }
</style>

<div class="snake-wrapper">
    <div class="snake-container" id="snake-container">
        <div class="absolute inset-0 z-0 flex items-center opacity-70">
            <svg width="100%" height="48" viewBox="0 0 1200 48" preserveAspectRatio="none" id="snake-svg">
                <path id="snake-bg-path" 
                      d="M 0 12 
                         C 100 12, 100 36, 200 36 
                         C 300 36, 300 12, 400 12 
                         C 500 12, 500 36, 600 36 
                         C 700 36, 700 12, 800 12 
                         C 900 12, 900 36, 1000 36 
                         C 1100 36, 1100 12, 1200 12" 
                      fill="none" 
                      stroke="currentColor" 
                      stroke-width="3" 
                      class="text-[#e6e7f0] dark:text-[#1e293b]" />
                      
                <path id="snake-progress-path" 
                      d="M 0 12 
                         C 100 12, 100 36, 200 36 
                         C 300 36, 300 12, 400 12 
                         C 500 12, 500 36, 600 36 
                         C 700 36, 700 12, 800 12 
                         C 900 12, 900 36, 1000 36 
                         C 1100 36, 1100 12, 1200 12" 
                      fill="none" 
                      stroke="url(#progressGradient)" 
                      stroke-width="4"
                      stroke-linecap="round"
                      stroke-dasharray="1300"
                      stroke-dashoffset="1300" />
                      
                <defs>
                    <linearGradient id="progressGradient" x1="0%" y1="0%" x2="100%" y2="0%">
                        <stop offset="0%" stop-color="#10b981" />
                        <stop offset="100%" stop-color="#3b82f6" />
                    </linearGradient>
                </defs>
            </svg>
        </div>

        <div id="snake-cursor"></div>

        @foreach ($phases as $key => $data)
            @php
                $idx = $loop->index;
                
                // Advanced Node State Logic for Parallel Phases
                $isFinished = false;
                $isCurrent = false;

                // 1. Direct phase index check (Legacy Linear)
                if ($idx <= $visualIndex) {
                    $isFinished = true;
                    if ($idx === $visualIndex && $visualIndex < ($totalPhases - 1)) {
                        $isFinished = false;
                        $isCurrent = true;
                    }
                }

                // 2. Parallel Commerce & Warehouse Correction
                if ($currentPhase === 'rekon') {
                    if ($key === 'rekon' || $key === 'warehouse') {
                        $isCurrent = true;
                        $isFinished = false;
                    }
                }

                // 3. Sub-phase Completion Check (Overwrites state with actual data)
                if (isset($subfaseMapping[$key])) {
                    $subKeys = array_keys($subfaseMapping[$key]);
                    $doneSubCount = 0;
                    foreach($subKeys as $sk) {
                        if (($unifiedSubfaseStatuses[$sk] ?? '') === 'selesai') $doneSubCount++;
                    }
                    
                    if ($doneSubCount === count($subKeys) && count($subKeys) > 0) {
                        $isFinished = true;
                        $isCurrent = false;
                    }
                }

                $stateClass = '';
                if ($isFinished) $stateClass = 'is-finished';
                elseif ($isCurrent) $stateClass = 'is-current';
                
                $nodeClass = ($idx % 2 == 0) ? 'top-label' : 'bottom-label';
                
                $percentX = ($idx / ($totalPhases - 1)) * 100;
                $percentY = ($idx % 2 == 0) ? 25 : 75;
            @endphp

            <div class="snake-node {{ $stateClass }} {{ $nodeClass }}" style="left: {{ $percentX }}%; top: {{ $percentY }}%;" data-index="{{ $idx }}">
                <div class="snake-marker">
                    @if($isFinished)
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                        </svg>
                    @else
                        <!-- Made icons smaller -->
                        <svg xmlns="http://www.w3.org/2000/svg" class="{{ $isCurrent ? 'h-5 w-5' : 'h-3 w-3' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="{{ $data['icon'] }}" />
                        </svg>
                    @endif
                </div>

                <div class="snake-label">
                    {{ $data['label'] }}
                    @if($isFinished)
                        <div class="text-[9px] text-emerald-500 font-semibold mt-0.5">SELESAI</div>
                    @elseif($isCurrent)
                        <div class="text-[9px] text-blue-500 font-semibold mt-0.5 animate-pulse">AKTIF</div>
                    @endif
                </div>

                @if(isset($subfaseMapping[$key]))
                    <div class="snake-tooltip">
                        <div class="text-[10px] font-black mb-2 text-white/50 uppercase tracking-widest border-b border-white/10 pb-1.5 flex items-center justify-between">
                            <span>Sub-Phases</span>
                            <span class="text-[8px] font-bold text-blue-300 capitalize">{{ $data['label'] }}</span>
                        </div>
                        <div class="space-y-2">
                            @foreach($subfaseMapping[$key] as $sk => $sl)
                                @php 
                                    $st = $unifiedSubfaseStatuses[$sk] ?? 'waiting'; 
                                    $isDone = $st === 'selesai';
                                    $isOgp = $st === 'ogp';
                                @endphp
                                <div class="flex items-center justify-between gap-4">
                                    <span class="text-[10px] font-bold {{ $isDone ? 'text-emerald-400' : ($isOgp ? 'text-blue-400' : 'text-slate-400') }}">{{ $sl }}</span>
                                    @if($isDone)
                                        <div class="flex items-center gap-1.5">
                                            @php $subFiles = $evidenceMap->get($sk, collect()); @endphp
                                            @foreach($subFiles as $sf)
                                                @if(!str_starts_with($sf->file_path, 'text://'))
                                                    <a href="{{ route('project-data.evidence-files.download', [$project->id, $sf->id]) }}" target="_blank" class="text-[8px] font-black text-blue-400 hover:underline">LIHAT</a>
                                                @else
                                                    <span class="text-[8px] font-bold text-slate-400">{{ str_replace('text://', '', $sf->file_path) }}</span>
                                                @endif
                                            @endforeach
                                            <div class="flex h-4 w-4 items-center justify-center rounded-full bg-emerald-500/20">
                                                <svg class="h-2.5 w-2.5 text-emerald-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                                            </div>
                                        </div>
                                    @elseif($isOgp)
                                        <div class="flex items-center gap-1.5">
                                            <span class="text-[8px] font-black text-blue-400/80 tracking-tighter">PROSES</span>
                                            <div class="relative flex h-2 w-2">
                                                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-blue-400 opacity-75"></span>
                                                <span class="relative inline-flex rounded-full h-2 w-2 bg-blue-500"></span>
                                            </div>
                                        </div>
                                    @else
                                        <div class="h-1 w-1 rounded-full bg-slate-700"></div>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>
        @endforeach
    </div>
</div>

<script type="module">
document.addEventListener('DOMContentLoaded', function() {
    const totalPhases = {{ $totalPhases }};
    const currentIndex = {{ $currentIndex }};
    const visualIndex = {{ $visualIndex }};
    const nodes = document.querySelectorAll('.snake-node');
    const path = document.getElementById('snake-progress-path');
    const bgPath = document.getElementById('snake-bg-path');
    const cursor = document.getElementById('snake-cursor');
    const svgOverlay = document.getElementById('snake-svg');
    const container = document.getElementById('snake-container');
    
    nodes.forEach((node, i) => {
        setTimeout(() => {
            node.classList.add('js-loaded');
        }, i * 150);
    });

    if (path && bgPath) {
        const exactLength = path.getTotalLength();
        path.style.strokeDasharray = exactLength;
        path.style.strokeDashoffset = exactLength;
        
        const targetPercent = visualIndex / (totalPhases - 1);
        const targetOffset = exactLength - (exactLength * targetPercent);

        setTimeout(() => {
            path.style.transition = 'stroke-dashoffset 2s cubic-bezier(0.4, 0, 0.2, 1)';
            path.style.strokeDashoffset = targetOffset;
        }, 300);
        
        setTimeout(() => {
            cursor.style.opacity = '1';
            let startTime = null;
            const duration = 2000;
            
            function animateCursor(timestamp) {
                if (!startTime) startTime = timestamp;
                let progress = (timestamp - startTime) / duration;
                
                if (progress > 1) progress = 1;

                let pointAt = progress * targetPercent * exactLength;
                let pt = bgPath.getPointAtLength(pointAt);
                
                let xPercent = (pt.x / 1200) * 100;
                let yPercent = (pt.y / 48) * 100; // Adjusted for new viewBox height 48

                cursor.style.left = xPercent + '%';
                cursor.style.top = yPercent + '%';

                if (progress < 1) {
                    requestAnimationFrame(animateCursor);
                }
            }
            requestAnimationFrame(animateCursor);
        }, 300);
    }
});
</script>
