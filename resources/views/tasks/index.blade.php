@extends('layouts.dashboard')

@section('title', 'Task ' . $roleLabel)
@section('date_label', $roleLabel)
@section('hello_name', 'Task ' . $roleLabel)

@section('content')
     {{-- DataTables CSS removed --}}
    
    @if ($errors->any())
        <div class="mb-5 rounded-xl border border-red-100 bg-[var(--panel-strong)] p-3 shadow-soft dark:border-red-900/30">
            <div class="flex items-start gap-4">
                <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-[#fff4f4] text-[#c65b68] dark:bg-[#2b171b] dark:text-[#f3c7cd]">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v4m0 4h.01M10.29 3.86l-8.11 14A1 1 0 0 0 3.05 19h17.9a1 1 0 0 0 .87-1.5l-8.11-14a1 1 0 0 0-1.74 0Z" />
                    </svg>
                </div>
                <div class="min-w-0 flex-1">
                    <p class="text-sm font-semibold text-[var(--panel-text)]">Terjadi Kesalahan!</p>
                    <ul class="mt-1 list-inside list-disc text-xs text-brand-muted dark:text-slate-400">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    @endif

    <div class="space-y-3">
        <!-- Header Section -->
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-bold text-brand-text dark:text-white">Pending Tasks</h2>
                <p class="text-sm text-brand-muted dark:text-slate-400">Daftar project yang memerlukan upload evidence untuk role <strong>{{ $roleLabel }}</strong>.</p>
            </div>
            <div class="flex items-center gap-3">
                <span class="rounded-full bg-blue-100 px-4 py-1.5 text-xs font-bold text-blue-700 dark:bg-blue-900/30 dark:text-blue-400">
                    {{ $roleLabel }} Workspace
                </span>
            </div>
        </div>

        <!-- Projects Table Section -->
        <section class="rounded-xl border border-[#e6e7f0] bg-white p-3 shadow-soft dark:border-brand-darkLine dark:bg-brand-darkPanel">
            <div class="overflow-hidden rounded-xl border border-[#e6e7f0] dark:border-brand-darkLine">
                <div class="p-4">
                    <table id="tasks-table" class="min-w-full divide-y divide-[#e6e7f0] dark:divide-brand-darkLine">
                        <thead class="bg-[#fbfbfe] dark:bg-[#161f35]">
                            <tr>
                                @if($role === 'procurement' || $role === 'commerce' || $role === 'warehouse' || $role === 'finance')
                                    <th class="w-12 px-5 py-4">
                                        <div class="flex justify-center">
                                            <input type="checkbox" id="select-all-projects" class="h-5 w-5 rounded-lg border-slate-300 text-blue-600 focus:ring-4 focus:ring-blue-500/20 dark:border-slate-700 dark:bg-slate-800">
                                        </div>
                                    </th>
                                @endif
                            <th class="px-5 py-4 text-left text-xs font-bold uppercase tracking-[0.15em] text-brand-muted dark:text-slate-500">Project Information</th>
                            <th class="px-5 py-4 text-left text-xs font-bold uppercase tracking-[0.15em] text-brand-muted dark:text-slate-500">ID Taskgate</th>
                            <th class="px-5 py-4 text-left text-xs font-bold uppercase tracking-[0.15em] text-brand-muted dark:text-slate-500">Current Phase</th>
                            <th class="px-5 py-4 text-center text-xs font-bold uppercase tracking-[0.15em] text-brand-muted dark:text-slate-500">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[#e6e7f0] dark:divide-brand-darkLine bg-white dark:bg-brand-darkPanel">
                            <!-- DataTables will populate this -->
                        </tbody>
                    </table>
                </div>
            </div>
        </section>
    </div>
    
    @if($role === 'procurement' || $role === 'commerce' || $role === 'warehouse' || $role === 'finance')
        <!-- Batch Action Bar -->
        <div id="batch-action-bar" class="fixed bottom-8 left-1/2 z-[60] -translate-x-1/2 transform transition-all duration-300 translate-y-24 opacity-0 pointer-events-none">
            <div class="flex items-center gap-6 rounded-xl border border-slate-200 bg-white/90 px-6 py-4 shadow-float backdrop-blur-xl dark:border-slate-800 dark:bg-slate-900/90">
                <div class="flex items-center gap-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-blue-100 dark:bg-blue-900/30">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-blue-600 dark:text-blue-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/>
                        </svg>
                    </div>
                    <div>
                        <p id="selected-count-text" class="text-xs font-bold text-slate-900 dark:text-white">0 Items Selected</p>
                        <p class="text-[10px] text-slate-500">
                            @if($role === 'procurement') Group these projects into one TGIDSP
                            @elseif($role === 'commerce') Create Commerce Rekon (TGIDRC)
                            @elseif($role === 'warehouse') Create Warehouse Rekon (TGIDRM)
                            @elseif($role === 'finance') Create Finance Rekon (TGIDRF)
                            @endif
                        </p>
                    </div>
                </div>
                <div class="h-10 w-px bg-slate-200 dark:bg-slate-800"></div>
                <button onclick="openBatchModal()" class="rounded-xl bg-blue-600 px-6 py-3 text-sm font-bold text-white shadow-lg transition hover:bg-blue-700 hover:shadow-blue-500/30">
                    Proses @if($role === 'procurement') TGIDSP @elseif($role === 'commerce') TGIDRC @elseif($role === 'warehouse') TGIDRM @else TGIDRF @endif
                </button>
            </div>
        </div>

        @if($role === 'procurement' || $role === 'commerce' || $role === 'warehouse' || $role === 'finance')
        <!-- Batch/Rekon Process Modal -->
        <div id="batch-modal" class="fixed inset-0 z-[70] hidden items-center justify-center">
            <div class="absolute inset-0 bg-slate-950/40 backdrop-blur-sm" onclick="closeBatchModal()"></div>
            <div class="relative w-full max-w-lg overflow-hidden rounded-xl border border-slate-200 bg-white shadow-2xl dark:border-slate-800 dark:bg-slate-900">
                <div class="bg-slate-50 px-8 py-6 dark:bg-slate-800/50">
                    <h3 class="text-xl font-bold text-slate-900 dark:text-white">
                        Konfirmasi Proses @if($role === 'procurement') (TGIDSP) @elseif($role === 'commerce') (TGIDRC) @elseif($role === 'warehouse') (TGIDRM) @else (TGIDRF) @endif
                    </h3>
                    <p class="mt-1 text-sm text-slate-500">
                        @if($role === 'procurement') Pilih mitra dan masukkan nomor PO untuk project terpilih.
                        @else Anda akan memproses item yang dipilih menjadi entitas baru. Hubungi admin jika ragu.
                        @endif
                    </p>
                </div>
                
                <form id="batch-form" onsubmit="event.preventDefault(); submitBatch();" class="p-8">
                    <div class="space-y-4">
                        @if($role === 'procurement')
                        <div>
                            <label class="mb-2 block text-xs font-bold uppercase tracking-wider text-slate-500">Pilih Mitra</label>
                            <select id="mitra_id" required class="h-12 w-full rounded-xl border border-slate-200 bg-slate-50 px-4 text-sm text-slate-900 outline-none transition focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 dark:border-slate-800 dark:bg-slate-950 dark:text-white">
                                <option value="">-- Pilih Mitra --</option>
                                @foreach($mitras as $mitra)
                                    <option value="{{ $mitra->id }}">{{ $mitra->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="mb-2 block text-xs font-bold uppercase tracking-wider text-slate-500">Nomor PO</label>
                            <input type="text" id="po_number" required placeholder="Masukkan nomor PO..." class="h-12 w-full rounded-xl border border-slate-200 bg-slate-50 px-4 text-sm text-slate-900 outline-none transition focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 dark:border-slate-800 dark:bg-slate-950 dark:text-white">
                        </div>
                        @elseif($role === 'finance')
                        <div>
                            <label class="mb-2 block text-xs font-bold uppercase tracking-wider text-slate-500">Nomor APM</label>
                            <input type="text" id="apm_number" required placeholder="Masukkan nomor APM..." class="h-12 w-full rounded-xl border border-slate-200 bg-slate-50 px-4 text-sm text-slate-900 outline-none transition focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 dark:border-slate-800 dark:bg-slate-950 dark:text-white">
                        </div>
                        @else
                        <div class="py-4 text-center">
                            <div class="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-full bg-blue-50 text-blue-600 dark:bg-blue-900/20 dark:text-blue-400">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                                </svg>
                            </div>
                            <p class="text-sm font-medium text-slate-700 dark:text-slate-300">Konfirmasi proses data terpilih?</p>
                        </div>
                        @endif

                        <div id="selected-projects-preview" class="rounded-xl bg-slate-50 p-4 dark:bg-slate-800/50">
                            <p class="mb-2 text-[10px] font-bold uppercase tracking-widest text-slate-500">Selected Items:</p>
                            <div id="preview-list" class="max-h-32 space-y-2 overflow-y-auto pr-2 text-xs">
                                <!-- Previews will be injected here -->
                            </div>
                        </div>
                    </div>

                    <div class="mt-8 flex gap-3">
                        <button type="button" onclick="closeBatchModal()" 
                            class="flex-1 rounded-xl bg-slate-100 py-3.5 text-sm font-bold text-slate-600 transition hover:bg-slate-200 dark:bg-slate-800 dark:text-slate-400 dark:hover:bg-slate-700">
                            Batal
                        </button>
                        <button type="submit" id="submit-btn"
                            class="flex-[2] rounded-xl bg-blue-600 py-3.5 text-sm font-bold text-white shadow-lg transition hover:bg-blue-700 hover:shadow-blue-500/30">
                            Konfirmasi & Proses
                        </button>
                    </div>
                </form>
            </div>
        </div>
        @endif
    @endif

    @push('scripts')
        <script type="module">
            let selectedIds = new Set();
            const role = "{{ $role }}";

            $(document).ready(function() {
                const table = $('#tasks-table').DataTable({
                    processing: true,
                    serverSide: true,
                    ajax: "{{ route('tasks.data', $role) }}",
                    pageLength: 25,
                    scrollX: true,
                    autoWidth: false,
                    columns: [
                        @if($role === 'procurement' || $role === 'commerce' || $role === 'warehouse' || $role === 'finance')
                        { data: 'checkbox', name: 'checkbox', orderable: false, searchable: false },
                        @endif
                        { data: 'project_info', name: 'project_name' },
                        { data: 'id', name: 'id' },
                        { data: 'fase', name: 'fase' },
                        { data: 'action', name: 'action', orderable: false, searchable: false, className: 'text-center' }
                    ],
                    dom: '<"top"lf>rt<"bottom"ip><"clear">',
                    language: {
                        search: "Cari:",
                        zeroRecords: "Data tidak ditemukan",
                        paginate: {
                            previous: "Prev",
                            next: "Next"
                        }
                    },
                    drawCallback: function() {
                        // Re-bind click events for newly loaded checkboxes
                        $('.project-checkbox').each(function() {
                            if (selectedIds.has($(this).val())) {
                                $(this).prop('checked', true);
                            }
                        });

                        $('.project-checkbox').off('change').on('change', function() {
                            const id = $(this).val();
                            if ($(this).is(':checked')) selectedIds.add(id);
                            else selectedIds.delete(id);
                            updateActionBar();
                        });
                    }
                });

                // Select All functionality
                $('#select-all-projects').on('change', function() {
                    const isChecked = $(this).is(':checked');
                    $('.project-checkbox').each(function() {
                        $(this).prop('checked', isChecked).trigger('change');
                    });
                });
            });

            function updateActionBar() {
                const bar = document.getElementById('batch-action-bar');
                const countText = document.getElementById('selected-count-text');
                
                if (selectedIds.size > 0 && bar) {
                    bar.classList.remove('translate-y-24', 'opacity-0', 'pointer-events-none');
                    countText.textContent = `${selectedIds.size} Item${selectedIds.size > 1 ? 's' : ''} Selected`;
                    
                    // Special handling for warehouse/finance: Update hidden inputs for redirect
                    if (role === 'warehouse' || role === 'finance') {
                        const containerId = role === 'warehouse' ? 'hidden-batch-inputs' : 'hidden-finance-inputs';
                        const inputName = role === 'warehouse' ? 'batch_ids[]' : 'rekon_ids[]';
                        const container = document.getElementById(containerId);
                        if (container) {
                            container.innerHTML = Array.from(selectedIds).map(id => 
                                `<input type="hidden" name="${inputName}" value="${id}">`
                            ).join('');
                        }
                    }
                } else if (bar) {
                    bar.classList.add('translate-y-24', 'opacity-0', 'pointer-events-none');
                }
            }

            function openBatchModal() {
                const modal = document.getElementById('batch-modal');
                const previewList = document.getElementById('preview-list');
                if (!modal) return;
                modal.classList.remove('hidden');
                modal.classList.add('flex');
                
                previewList.innerHTML = Array.from(selectedIds).map(id => `
                    <div class="flex items-center justify-between rounded-lg bg-white px-3 py-2 text-xs font-medium text-slate-700 shadow-sm dark:bg-slate-900 dark:text-slate-300">
                        <span>${id}</span>
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 text-emerald-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3">
                            <path d="M20 6L9 17l-5-5"/>
                        </svg>
                    </div>
                `).join('');
            }

            function closeBatchModal() {
                const modal = document.getElementById('batch-modal');
                if (modal) {
                    modal.classList.add('hidden');
                    modal.classList.remove('flex');
                }
            }

            function updateFileName() {
                const input = document.getElementById('batch_evidence');
                const label = document.getElementById('file-label');
                if (input && input.files && input.files[0]) {
                    label.textContent = input.files[0].name;
                    label.classList.add('text-blue-600');
                }
            }

            async function submitBatch() {
                const btn = document.getElementById('submit-btn');
                btn.disabled = true;
                btn.innerHTML = 'Processing...';

                const formData = new FormData();
                formData.append('_token', '{{ csrf_token() }}');

                if (role === 'procurement') {
                    const poNumber = document.getElementById('po_number').value;
                    const mitraId = document.getElementById('mitra_id').value;
                    const poEvidence = document.getElementById('batch_evidence');
                    
                    if (!poNumber || !mitraId) {
                        showToast('error', 'Pilih mitra dan masukkan nomor PO!');
                        btn.disabled = false;
                        btn.innerHTML = 'Konfirmasi & Proses';
                        return;
                    }

                    formData.append('po_number', poNumber);
                    formData.append('mitra_id', mitraId);
                    if (poEvidence && poEvidence.files[0]) {
                        formData.append('po_evidence', poEvidence.files[0]);
                    }
                    selectedIds.forEach(id => formData.append('project_ids[]', id));
                } else if (role === 'commerce') {
                    selectedIds.forEach(id => formData.append('batch_ids[]', id));
                } else if (role === 'warehouse') {
                    selectedIds.forEach(id => formData.append('batch_ids[]', id));
                } else if (role === 'finance') {
                    const apmNumber = document.getElementById('apm_number').value;
                    if (!apmNumber) {
                        showToast('error', 'Masukkan nomor APM!');
                        btn.disabled = false;
                        btn.innerHTML = 'Konfirmasi & Proses';
                        return;
                    }
                    formData.append('apm_number', apmNumber);
                    selectedIds.forEach(id => formData.append('rekon_ids[]', id));
                }

                let submitRoute = '';
                switch(role) {
                    case 'procurement': submitRoute = '{{ route("tasks.procurement.batch") }}'; break;
                    case 'commerce': submitRoute = '{{ route("tasks.commerce.batch") }}'; break;
                    case 'warehouse': submitRoute = '{{ route("tasks.warehouse.rekon-process") }}'; break;
                    case 'finance': submitRoute = '{{ route("tasks.finance.rekon-process") }}'; break;
                }

                try {
                    const response = await fetch(submitRoute, {
                        method: 'POST',
                        body: formData
                    });
                    
                    const result = await response.json();
                    
                    if (result.success) {
                        showToast('success', result.message);
                        setTimeout(() => {
                            if (result.redirect_url) {
                                window.location.href = result.redirect_url;
                            } else {
                                window.location.reload();
                            }
                        }, 1500);
                    } else {
                        showToast('error', result.error || 'Failed to process batch');
                        btn.disabled = false;
                        btn.innerHTML = 'Konfirmasi & Proses';
                    }
                } catch (error) {
                    showToast('error', 'An error occurred. Please try again.');
                    btn.disabled = false;
                    btn.innerHTML = 'Konfirmasi & Proses';
                }
            }

            function showToast(type, message) {
                const toastId = type === 'success' ? 'status-toast' : 'error-toast';
                const containerId = toastId + '-container';
                
                // Remove existing if any
                const existing = document.getElementById(containerId);
                if (existing) existing.remove();

                const isSuccess = type === 'success';
                const toastHtml = `
                    <div id="${containerId}" class="toast-enter fixed right-4 top-4 z-[9999] w-[min(92vw,420px)]">
                        <div class="overflow-hidden rounded-xl border ${isSuccess ? 'border-[#d6dfd0] dark:border-[#38513d]' : 'border-[#f2c8cc] dark:border-[#5c2d34]'} bg-[var(--panel-strong)] shadow-float">
                            <div class="flex items-start gap-4 px-5 py-4">
                                <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl ${isSuccess ? 'bg-[#eef8e9] text-[#4f8a42] dark:bg-[#15201a] dark:text-[#bfd7c2]' : 'bg-[#fff4f4] text-[#c65b68] dark:bg-[#2b171b] dark:text-[#f3c7cd]'}">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        ${isSuccess ? '<path stroke-linecap="round" stroke-linejoin="round" d="m5 13 4 4L19 7" />' : '<path stroke-linecap="round" stroke-linejoin="round" d="M12 9v4m0 4h.01M10.29 3.86l-8.11 14A1 1 0 0 0 3.05 19h17.9a1 1 0 0 0 .87-1.5l-8.11-14a1 1 0 0 0-1.74 0Z" />'}
                                    </svg>
                                </div>
                                <div class="min-w-0 flex-1">
                                    <p class="text-sm font-semibold text-[var(--panel-text)]">${isSuccess ? 'Berhasil' : 'Error'}</p>
                                    <p class="mt-1 text-sm leading-6 text-[var(--panel-muted)]">${message}</p>
                                </div>
                                <button onclick="this.closest('#${containerId}').remove()" type="button" class="rounded-full p-2 text-[var(--panel-muted)] transition hover:bg-[#f4f5f9] hover:text-[var(--panel-text)]">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            </div>
                            <div class="h-1.5 w-full ${isSuccess ? 'bg-[#eef8e9] dark:bg-[#1c2a22]' : 'bg-[#fff1f1] dark:bg-[#2b171b]'}">
                                <div class="toast-bar h-full w-full origin-left ${isSuccess ? 'bg-[#70c26a] dark:bg-[#5fa85a]' : 'bg-[#d97782]'}"></div>
                            </div>
                        </div>
                    </div>
                `;

                document.body.insertAdjacentHTML('beforeend', toastHtml);
                const toast = document.getElementById(containerId);
                const bar = toast.querySelector('.toast-bar');

                bar.animate([{ transform: 'scaleX(1)' }, { transform: 'scaleX(0)' }], {
                    duration: isSuccess ? 4200 : 5200,
                    easing: 'linear',
                    fill: 'forwards'
                });

                setTimeout(() => {
                    if (toast) {
                        toast.classList.remove('toast-enter');
                        toast.classList.add('toast-exit');
                        setTimeout(() => toast.remove(), 200);
                    }
                }, isSuccess ? 4200 : 5200);
            }
        </script>
    @endpush
@endsection
