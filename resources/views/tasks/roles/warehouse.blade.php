@extends('layouts.dashboard')

@section('title', 'Manage Warehouse Task')
@section('date_label', 'Warehouse')
@section('hello_name', 'Task Management: ' . $project->id)

@section('content')
<div class="space-y-3">
    <!-- Back Button -->
    <div class="flex items-center justify-between">
        <a href="{{ route('rekon.index') }}" class="inline-flex items-center gap-2 text-sm font-semibold text-brand-muted hover:text-brand-text transition dark:text-slate-400 dark:hover:text-white">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            Kembali ke Dashboard Warehouse
        </a>
        <div class="flex items-center gap-3">
            <span class="rounded-full bg-blue-50 px-3 py-1 text-xs font-bold text-blue-600 dark:bg-blue-900/30 dark:text-blue-400">Mode: Warehouse Editor</span>
        </div>
    </div>

    <!-- Project Quick Info -->
    <section class="rounded-xl border border-[#e6e7f0] bg-white p-3 shadow-soft dark:border-brand-darkLine dark:bg-brand-darkPanel">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h2 class="text-xl font-bold text-brand-text dark:text-white">{{ $project->project_name }}</h2>
                <div class="mt-1 flex items-center gap-3 text-sm text-brand-muted dark:text-slate-400">
                    <span class="font-mono font-bold text-blue-600 dark:text-blue-400">{{ $project->id }}</span>
                    <span>•</span>
                    <span class="font-semibold">{{ $project->customer }}</span>
                </div>
            </div>
            <a href="{{ route('project-data.show', $project->id) }}" target="_blank" class="inline-flex items-center gap-2 rounded-xl border border-[#d9dceb] px-4 py-2 text-xs font-bold transition hover:bg-slate-50 dark:border-brand-darkLine dark:hover:bg-[#161f35]">
                View Full Details
                <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
            </a>
        </div>
    </section>

    <!-- Stepper Progress -->
    <section class="rounded-xl border border-[#e6e7f0] bg-white p-3 shadow-soft dark:border-brand-darkLine dark:bg-brand-darkPanel">
        <div class="mb-8">
            <h3 class="text-lg font-bold">Sub-Fase Progress: Warehouse</h3>
            <p class="text-sm text-brand-muted">Lengkapi evidence untuk setiap sub-fase di bawah ini.</p>
        </div>

        @include('project-data.partials.subfase-stepper', [
            'project' => $project,
            'currentSubPhases' => $subPhases,
            'unifiedSubfaseStatuses' => $unifiedSubfaseStatuses,
            'canUpload' => true,
            'viewOnly' => $isAlreadySubmitted ?? false,
            'submitRoute' => $submitRoute,
            'submitLabel' => $submitLabel,
            'allSubphasesDone' => $allSubphasesDone,
            'role' => $role,
            'roleLabel' => $roleLabel,
        ])
    </section>

    <!-- Material Fulfillment Section -->
    <section class="rounded-xl border border-[#e6e7f0] bg-white p-3 shadow-soft dark:border-brand-darkLine dark:bg-brand-darkPanel">
        <div class="mb-4 flex items-center justify-between px-2">
            <div>
                <h3 class="text-lg font-bold text-brand-text dark:text-white">Material Fulfillment</h3>
                <p class="text-sm text-brand-muted dark:text-slate-400">Update volume pemenuhan untuk item material (M-).</p>
            </div>
            <div class="flex items-center gap-3">
                @if(!($isAlreadySubmitted ?? false))
                <button onclick="openBulkEdit()" class="inline-flex items-center gap-2 rounded-xl bg-slate-900 px-4 py-2 text-xs font-black text-white shadow-lg shadow-slate-900/20 transition hover:bg-slate-800 active:scale-95 dark:bg-white dark:text-slate-900 dark:shadow-none">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M3 14h18m-9-4v8m-7 0h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                    BULK EDIT MODE
                </button>
                @endif
                <div class="flex items-center gap-2 border-l border-slate-200 pl-3 dark:border-slate-800">
                    <span class="flex h-2 w-2 rounded-full bg-emerald-500 animate-pulse"></span>
                    <span class="text-[10px] font-bold uppercase tracking-wider text-emerald-600">Autosave Active</span>
                </div>
            </div>
        </div>

        <div class="overflow-hidden rounded-xl border border-[#e6e7f0] dark:border-brand-darkLine">
            <table id="material-fulfillment-table" class="min-w-full divide-y divide-[#e6e7f0] dark:divide-brand-darkLine">
                <thead class="bg-[#fbfbfe] dark:bg-[#161f35]">
                    <tr>
                        <th class="px-5 py-4 text-left text-xs font-bold uppercase tracking-[0.15em] text-brand-muted dark:text-slate-500">Designator</th>
                        <th class="px-5 py-4 text-left text-xs font-bold uppercase tracking-[0.15em] text-brand-muted dark:text-slate-500">Description</th>
                        <th class="px-5 py-4 text-center text-xs font-bold uppercase tracking-[0.15em] text-brand-muted dark:text-slate-500">Vol Planning</th>
                        <th class="px-5 py-4 text-center text-xs font-bold uppercase tracking-[0.15em] text-brand-muted dark:text-slate-500">Vol Pemenuhan</th>
                        <th class="px-5 py-4 text-center text-xs font-bold uppercase tracking-[0.15em] text-brand-muted dark:text-slate-500">Deviasi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#e6e7f0] dark:divide-brand-darkLine bg-white dark:bg-brand-darkPanel">
                    @php
                        $materials = $project->boqDetails->filter(fn($item) => str_starts_with(strtoupper($item->designator), 'M-'));
                    @endphp
                    @forelse($materials as $item)
                    <tr class="group hover:bg-slate-50/50 dark:hover:bg-[#1e293b]/30 transition-colors">
                        <td class="px-5 py-4 font-mono text-[11px] font-black text-blue-600 dark:text-blue-400 uppercase tracking-tighter">{{ $item->designator }}</td>
                        <td class="px-5 py-4 text-xs font-semibold text-slate-700 dark:text-slate-300">{{ $item->description }}</td>
                        <td class="px-5 py-4 text-center font-black text-slate-900 dark:text-white">{{ (float)$item->volume_planning }}</td>
                        <td class="px-5 py-4">
                            <div class="flex justify-center">
                                <input type="number" 
                                    id="main-input-{{ $item->id }}"
                                    step="1"
                                    value="{{ (float)$item->volume_pemenuhan }}" 
                                    @if(!($isAlreadySubmitted ?? false))
                                        oninput="updateVolume(this, {{ $item->id }}, {{ $item->volume_planning }})"
                                    @else
                                        disabled
                                    @endif
                                    class="h-8 w-24 rounded-lg border border-slate-200 bg-slate-50 px-3 text-center text-xs font-black shadow-none transition focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 dark:border-slate-700 dark:bg-[#0f1728] dark:text-white {{ ($isAlreadySubmitted ?? false) ? 'opacity-60 cursor-not-allowed' : '' }}"
                                >
                            </div>
                        </td>
                        <td class="px-5 py-4 text-center">
                            @php
                                $dev = (float)($item->volume_deviasi ?? ($item->volume_pemenuhan - $item->volume_planning));
                            @endphp
                            <span id="deviasi-{{ $item->id }}" class="inline-flex min-w-[48px] justify-center items-center rounded-lg px-2.5 py-1 text-[10px] font-black {{ $dev < 0 ? 'bg-amber-50 text-amber-600 dark:bg-amber-900/20 dark:text-amber-400' : 'bg-emerald-50 text-emerald-600 dark:bg-emerald-900/20 dark:text-emerald-400' }}">
                                {{ $dev > 0 ? '+' . $dev : $dev }}
                            </span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-5 py-12 text-center">
                            <div class="flex flex-col items-center gap-2 text-slate-300">
                                <svg class="h-8 w-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path></svg>
                                <p class="text-sm font-semibold text-slate-400 italic">Tidak ada item material ditemukan.</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>

    <!-- Bulk Edit Modal -->
    <div id="bulkEditModal" class="fixed inset-0 z-[9999] hidden items-center justify-center bg-slate-900/60 backdrop-blur-sm transition-all duration-300">
        <div class="relative w-full max-w-4xl rounded-2xl bg-white shadow-2xl dark:bg-brand-darkPanel overflow-hidden mx-4">
                <!-- Modal Header -->
                <div class="flex items-center justify-between border-b border-slate-100 bg-[#fbfbfe] px-6 py-4 dark:border-slate-800 dark:bg-[#161f35]">
                    <div>
                        <h2 class="text-xl font-black text-slate-900 dark:text-white tracking-tight">Bulk Editor</h2>
                        <p class="text-[10px] font-bold text-slate-500 uppercase tracking-wider">↑ ↓ Enter untuk navigasi</p>
                    </div>
                    <button onclick="closeBulkEdit()" class="group rounded-2xl bg-white p-3 text-slate-400 shadow-sm transition hover:bg-red-50 hover:text-red-500 dark:bg-slate-800/50 dark:hover:bg-red-900/20">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <!-- Modal Body -->
                <div class="max-h-[60vh] overflow-y-auto px-6 py-4 custom-scrollbar">
                    <table class="w-full text-left">
                        <thead class="sticky top-0 bg-white dark:bg-brand-darkPanel z-10">
                            <tr class="border-b border-slate-100 dark:border-slate-800">
                                <th class="pb-2 pr-4 text-[9px] font-black uppercase text-brand-muted tracking-widest">Designator</th>
                                <th class="pb-2 pr-4 text-[9px] font-black uppercase text-brand-muted tracking-widest">Description</th>
                                <th class="pb-2 pr-4 text-center text-[9px] font-black uppercase text-brand-muted tracking-widest">Planning</th>
                                <th class="pb-2 pr-4 text-center text-[9px] font-black uppercase text-brand-muted tracking-widest">Pemenuhan</th>
                                <th class="pb-2 text-center text-[9px] font-black uppercase text-brand-muted tracking-widest">Deviasi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-50 dark:divide-slate-800/50">
                            @foreach($materials as $idx => $item)
                            <tr class="group transition-colors hover:bg-blue-50/30 dark:hover:bg-blue-900/10">
                                <td class="py-2 pr-4 font-mono text-[9px] font-black text-blue-600 dark:text-blue-400">{{ $item->designator }}</td>
                                <td class="py-2 pr-4 text-[9px] font-bold text-slate-600 dark:text-slate-400 leading-tight max-w-[180px] truncate" title="{{ $item->description }}">{{ $item->description }}</td>
                                <td class="py-2 pr-4 text-center text-[9px] font-black text-slate-900 dark:text-white">{{ (float)$item->volume_planning }}</td>
                                <td class="py-2 pr-4">
                                    <div class="flex justify-center">
                                        <input type="number" 
                                            id="bulk-input-{{ $item->id }}"
                                            data-index="{{ $idx }}"
                                            step="1"
                                            value="{{ (float)$item->volume_pemenuhan }}" 
                                            @if(!($isAlreadySubmitted ?? false))
                                                oninput="updateVolumeBulk(this, {{ $item->id }}, {{ $item->volume_planning }})"
                                                onkeydown="handleBulkKeydown(event, {{ $idx }})"
                                            @else
                                                disabled
                                            @endif
                                            class="bulk-input h-7 w-20 rounded-lg border border-slate-200 bg-slate-50 px-2 text-center text-[10px] font-black shadow-none transition focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 dark:border-slate-700 dark:bg-[#0f1728] dark:text-white {{ ($isAlreadySubmitted ?? false) ? 'opacity-60 cursor-not-allowed' : '' }}"
                                        >
                                    </div>
                                </td>
                                <td class="py-2 text-center">
                                    @php $dev = (float)($item->volume_deviasi ?? ($item->volume_pemenuhan - $item->volume_planning)); @endphp
                                    <span id="bulk-deviasi-{{ $item->id }}" class="inline-flex min-w-[32px] justify-center items-center rounded-lg px-2 py-0.5 text-[9px] font-black {{ $dev < 0 ? 'bg-amber-50 text-amber-600 dark:bg-amber-900/20 dark:text-amber-400' : 'bg-emerald-50 text-emerald-600 dark:bg-emerald-900/20 dark:text-emerald-400' }}">
                                        {{ $dev > 0 ? '+' . $dev : $dev }}
                                    </span>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Modal Footer -->
                <div class="bg-[#fbfbfe] px-6 py-4 dark:bg-[#161f35] flex justify-end gap-3 border-t border-slate-100 dark:border-slate-800">
                    <button onclick="closeBulkEdit()" class="rounded-xl bg-slate-900 px-6 py-2 text-[10px] font-black text-white dark:bg-white dark:text-slate-900 transition hover:scale-105 active:scale-95 uppercase tracking-wider">Selesai & Simpan</button>
                </div>
            </div>
        </div>

    @push('scripts')
    <script type="module">
        $(document).ready(function() {
            $('#material-fulfillment-table').DataTable({
                paging: false,
                searching: false,
                info: false,
                ordering: true,
                scrollX: true,
                autoWidth: false,
                language: {
                    zeroRecords: "Data tidak ditemukan"
                }
            });
        });

        // Bulk Edit Utils
        function openBulkEdit() {
            $('#bulkEditModal').removeClass('hidden').addClass('flex');
            document.body.style.overflow = 'hidden';
            setTimeout(() => $('.bulk-input').first().focus(), 100);
        }

        function closeBulkEdit() {
            $('#bulkEditModal').addClass('hidden').removeClass('flex');
            document.body.style.overflow = '';
        }

        function handleBulkKeydown(e, index) {
            const inputs = $('.bulk-input');
            if (e.key === 'ArrowDown' || e.key === 'Enter') {
                e.preventDefault();
                const next = inputs[index + 1];
                if (next) next.focus();
            } else if (e.key === 'ArrowUp') {
                e.preventDefault();
                const prev = inputs[index - 1];
                if (prev) prev.focus();
            }
        }

        function updateVolumeBulk(input, itemId, volPlanning) {
            // Sync current input to main table input immediately
            const mainInput = document.getElementById(`main-input-${itemId}`);
            if (mainInput) mainInput.value = input.value;
            
            // Re-use standard updateVolume logic (which includes UI calc + AJAX)
            // But we need a custom one for bulk to update both UI sets
            const volPemenuhan = parseFloat(input.value) || 0;
            const devSpanBulk = document.getElementById(`bulk-deviasi-${itemId}`);
            const devSpanMain = document.getElementById(`deviasi-${itemId}`);
            
            const deviasi = parseFloat((volPemenuhan - volPlanning).toFixed(2));
            const displayDeviasi = deviasi > 0 ? '+' + deviasi : deviasi;
            
            // Update Modal UI
            if (devSpanBulk) {
                devSpanBulk.textContent = displayDeviasi;
                devSpanBulk.className = `inline-flex min-w-[36px] justify-center items-center rounded-lg px-2 py-0.5 text-[10px] font-black ${deviasi < 0 ? 'bg-amber-50 text-amber-600 dark:bg-amber-900/20 dark:text-amber-400' : 'bg-emerald-50 text-emerald-600 dark:bg-emerald-900/20 dark:text-emerald-400'}`;
            }

            // Update Main Table UI
            if (devSpanMain) {
                devSpanMain.textContent = displayDeviasi;
                devSpanMain.className = `inline-flex min-w-[48px] justify-center items-center rounded-lg px-2.5 py-1 text-[10px] font-black ${deviasi < 0 ? 'bg-amber-50 text-amber-600 dark:bg-amber-900/20 dark:text-amber-400' : 'bg-emerald-50 text-emerald-600 dark:bg-emerald-900/20 dark:text-emerald-400'}`;
            }

            // Trigger actual AJAX sync (debounced)
            syncToServer(itemId, volPemenuhan, mainInput || input);
        }

        let debounceTimer;
        function updateVolume(input, itemId, volPlanning) {
            const volPemenuhan = parseFloat(input.value) || 0;
            const deviasiSpan = document.getElementById(`deviasi-${itemId}`);
            const bulkInput = document.getElementById(`bulk-input-${itemId}`);
            if (bulkInput) bulkInput.value = input.value;

            const deviasi = parseFloat((volPemenuhan - volPlanning).toFixed(2));
            const displayDeviasi = deviasi > 0 ? '+' + deviasi : deviasi;
            
            if (deviasiSpan) {
                deviasiSpan.textContent = displayDeviasi;
                deviasiSpan.className = `inline-flex min-w-[48px] justify-center items-center rounded-lg px-2.5 py-1 text-[10px] font-black ${deviasi < 0 ? 'bg-amber-50 text-amber-600 dark:bg-amber-900/20 dark:text-amber-400' : 'bg-emerald-50 text-emerald-600 dark:bg-emerald-900/20 dark:text-emerald-400'}`;
            }
            
            syncToServer(itemId, volPemenuhan, input);
        }

        function syncToServer(itemId, volPemenuhan, feedbackInput) {
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(() => {
                feedbackInput.classList.add('animate-pulse', 'border-blue-500');
                fetch("{{ route('tasks.warehouse.update-boq') }}", {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                    body: JSON.stringify({ id: itemId, volume_pemenuhan: volPemenuhan })
                })
                .then(response => response.json())
                .then(data => {
                    feedbackInput.classList.remove('animate-pulse', 'border-blue-500');
                })
                .catch(error => {
                    feedbackInput.classList.remove('animate-pulse', 'border-blue-500');
                    console.error(error);
                });
            }, 500);
        }
    </script>
    <style>
        .custom-scrollbar::-webkit-scrollbar { width: 6px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #e2e8f0; border-radius: 10px; }
        .dark .custom-scrollbar::-webkit-scrollbar-thumb { background: #1e293b; }
        .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #cbd5e1; }
    </style>
    @endpush
</div>
@endsection
