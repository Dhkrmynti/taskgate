@extends('layouts.dashboard')

@section('title', 'Warehouse Rekon - Input Pemenuhan')
@section('hello_name', 'Input Pemenuhan Material')
@section('date_label', 'Warehouse Rekon')

@section('content')
@php
    $totalVolPo = collect($aggregatedBoqs)->sum('volume_planning');
    $grandTotalPo = collect($aggregatedBoqs)->sum(function($item) {
        return $item->volume_planning * $item->price_planning;
    });
@endphp
<div class="min-h-screen">
    <!-- Action Bar -->
    <div class="mb-6 flex justify-end">
        <a href="{{ route('rekon.index') }}" 
           class="flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2 text-xs font-bold text-slate-600 transition hover:bg-slate-50 dark:border-slate-800 dark:bg-slate-900 dark:text-slate-400 dark:hover:bg-slate-800">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            Kembali ke Dashboard Warehouse
        </a>
    </div>

    <form id="warehouse-rekon-form" enctype="multipart/form-data">
        @csrf
        @foreach($batchIds as $id)
            <input type="hidden" name="batch_ids[]" value="{{ $id }}">
        @endforeach

        <div class="space-y-3">
            <!-- Top Section: General Info & Evidence -->
            <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
                <div class="overflow-hidden rounded-xl border border-[#e6e7f0] bg-white p-3 shadow-soft dark:border-brand-darkLine dark:bg-brand-darkPanel">
                    <div class="border-b border-slate-100 p-5 dark:border-brand-darkLine flex justify-between items-center bg-[#fbfbfe] dark:bg-[#161f35]">
                        <h3 class="text-[10px] font-bold uppercase tracking-[0.2em] text-brand-muted dark:text-slate-500">General Info</h3>
                        <span class="text-[10px] font-black text-blue-600 bg-blue-50 px-2 py-1 rounded dark:bg-blue-900/30">STEP 1 OF 3</span>
                    </div>
                    <div class="p-5">
                        <label class="mb-2 block text-[10px] font-black uppercase tracking-[0.2em] text-brand-muted dark:text-slate-500">Nomor Rekon Material</label>
                        <input type="text" name="rekon_number" required placeholder="RM-{{ date('Y') }}-..." 
                               class="h-11 w-full rounded-xl border border-[#d9dceb] bg-slate-50/50 px-4 text-sm font-medium text-slate-900 outline-none transition focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 dark:border-brand-darkLine dark:bg-slate-950 dark:text-white">
                    </div>
                </div>

                <div class="overflow-hidden rounded-xl border border-[#e6e7f0] bg-white p-3 shadow-soft dark:border-brand-darkLine dark:bg-brand-darkPanel">
                    <div class="border-b border-slate-100 p-5 dark:border-brand-darkLine flex justify-between items-center bg-[#fbfbfe] dark:bg-[#161f35]">
                        <h3 class="text-[10px] font-bold uppercase tracking-[0.2em] text-brand-muted dark:text-slate-500">Evidence / BARM</h3>
                        <span class="text-[10px] font-black text-emerald-600 bg-emerald-50 px-2 py-1 rounded dark:bg-emerald-900/30">ATTACHMENT</span>
                    </div>
                    <div class="p-5">
                        <div class="relative">
                            <input type="file" name="rekon_evidence" id="rekon_evidence" required class="hidden" onchange="document.getElementById('file-name').textContent = this.files[0].name">
                            <button type="button" onclick="document.getElementById('rekon_evidence').click()" 
                                    class="flex w-full items-center gap-4 rounded-xl border-2 border-dashed border-[#d9dceb] p-3 transition hover:border-blue-400 hover:bg-blue-50 dark:border-brand-darkLine dark:hover:border-blue-900/50 dark:hover:bg-blue-900/10 text-left">
                                <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-blue-50 text-blue-600 dark:bg-blue-900/30 dark:text-blue-400">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                                    </svg>
                                </div>
                                <span id="file-name" class="text-xs font-bold text-slate-600 dark:text-slate-400">Click to upload BARM Document</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Middle Section: Full-Width Summary Stats Bar -->
            <div class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-4">
                <div class="rounded-xl bg-blue-600 p-5 text-white shadow-lg shadow-blue-500/20">
                    <h4 class="text-[10px] font-black uppercase tracking-widest opacity-60">Total Vol PO</h4>
                    <p class="mt-2 text-xl font-black tracking-tight" id="sidebar-total-vol-po">{{ number_format($totalVolPo) }} Unit</p>
                </div>
                <div class="rounded-xl bg-slate-900 p-5 text-white shadow-xl dark:bg-brand-darkPanel dark:border dark:border-slate-800">
                    <h4 class="text-[10px] font-black uppercase tracking-widest opacity-60 text-slate-400">Total Amount PO</h4>
                    <p class="mt-2 text-xl font-black tracking-tight" id="sidebar-total-po">Rp {{ number_format($grandTotalPo) }}</p>
                </div>
                <div class="rounded-xl bg-emerald-600 p-5 text-white shadow-lg shadow-emerald-500/20">
                    <h4 class="text-[10px] font-black uppercase tracking-widest opacity-60">Total Vol Pemenuhan</h4>
                    <p class="mt-2 text-xl font-black tracking-tight" id="sidebar-total-vol-pemenuhan">{{ number_format($totalVolPo) }} Unit</p>
                </div>
                <div class="rounded-xl bg-indigo-600 p-5 text-white shadow-lg shadow-indigo-500/20">
                    <h4 class="text-[10px] font-black uppercase tracking-widest opacity-60">Total Amount Pemenuhan</h4>
                    <p class="mt-2 text-xl font-black tracking-tight" id="sidebar-total-pemenuhan">Rp {{ number_format($grandTotalPo) }}</p>
                </div>
            </div>

            <!-- Bottom Section: BoQ Table -->
            <div class="overflow-hidden rounded-xl border border-[#e6e7f0] bg-white p-3 shadow-soft dark:border-brand-darkLine dark:bg-brand-darkPanel">
                <div class="border-b border-slate-100 px-6 py-5 dark:border-brand-darkLine flex justify-between items-center bg-[#fbfbfe] dark:bg-[#161f35]">
                    <div>
                        <h3 class="text-[10px] font-bold uppercase tracking-[0.2em] text-brand-muted dark:text-slate-500">BoQ Details Consolidator</h3>
                        <p class="text-[10px] text-slate-400 uppercase font-bold mt-1">Review items from {{ count($batchIds) }} selected TGIDSP batches</p>
                    </div>
                    <button type="submit" form="warehouse-rekon-form" class="flex items-center gap-2 rounded-xl bg-blue-600 px-8 py-4 text-sm font-black text-white shadow-lg shadow-blue-500/20 transition hover:bg-blue-700 active:scale-95">
                        SUBMIT REKON MATERIAL
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                        </svg>
                    </button>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-[#e6e7f0] dark:divide-brand-darkLine" id="boq-table">
                        <thead class="bg-[#fbfbfe] dark:bg-[#161f35]">
                            <tr>
                                <th class="text-left text-xs font-bold uppercase tracking-[0.12em] text-slate-500">No</th>
                                <th class="text-left text-xs font-bold uppercase tracking-[0.12em] text-slate-500">Material Name</th>
                                <th class="text-center text-xs font-bold uppercase tracking-[0.12em] text-slate-500">Vol Planning</th>
                                <th class="text-center text-xs font-bold uppercase tracking-[0.12em] text-slate-500">Vol Rekon</th>
                                <th class="text-center text-xs font-bold uppercase tracking-[0.12em] text-slate-500">Uom</th>
                                <th class="text-right text-xs font-bold uppercase tracking-[0.12em] text-slate-500">Price</th>
                                <th class="text-center text-xs font-bold uppercase tracking-[0.12em] text-slate-500">Deviation</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                            @php $grandTotalPo = 0; @endphp
                            @foreach($aggregatedBoqs as $idx => $item)
                            @php 
                                $key = $item->designator . '|' . ($item->description ?? ''); 
                                $amountPo = $item->volume_planning * $item->price_planning;
                                $grandTotalPo += $amountPo;
                            @endphp
                            <tr class="boq-row transition-colors hover:bg-slate-50/50 dark:hover:bg-slate-800/20" 
                                data-price="{{ $item->price_planning }}" data-vol-po="{{ $item->volume_planning }}">
                                <td class="px-8 py-6 text-xs font-black text-slate-300">{{ $idx + 1 }}</td>
                                <td class="px-4 py-6">
                                    <div class="flex flex-col">
                                        <span class="text-sm font-black text-slate-900 dark:text-white">{{ $item->designator }}</span>
                                        <span class="text-[11px] text-slate-500 dark:text-slate-400 line-clamp-1 truncate max-w-[200px]" title="{{ $item->description }}">{{ $item->description }}</span>
                                    </div>
                                </td>
                                <td class="px-4 py-6 text-center">
                                    <div class="flex flex-col items-center">
                                        <span class="text-xs font-bold text-slate-700 dark:text-slate-300">{{ number_format($item->volume_planning) }} Unit</span>
                                        <span class="mt-1 text-[10px] font-medium text-slate-400">@ Rp {{ number_format($item->price_planning) }}</span>
                                    </div>
                                </td>
                                <td class="px-4 py-6">
                                    <div class="flex justify-center">
                                        <input type="number" name="boq[{{ $key }}][volume_pemenuhan]" 
                                               class="h-10 w-24 rounded-xl border border-slate-200 bg-slate-50 text-center text-sm font-black text-blue-600 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 dark:border-slate-700 dark:bg-slate-900 vol-pemenuhan" 
                                               value="{{ $item->volume_planning }}" min="0" required>
                                    </div>
                                    
                                    <input type="hidden" name="boq[{{ $key }}][designator]" value="{{ $item->designator }}">
                                    <input type="hidden" name="boq[{{ $key }}][description]" value="{{ $item->description }}">
                                    <input type="hidden" name="boq[{{ $key }}][volume_planning]" value="{{ $item->volume_planning }}">
                                    <input type="hidden" name="boq[{{ $key }}][price_planning]" value="{{ $item->price_planning }}">
                                    <input type="hidden" name="boq[{{ $key }}][volume_aktual]" value="{{ $item->volume_aktual }}">
                                    <input type="hidden" name="boq[{{ $key }}][price_aktual]" value="{{ $item->price_aktual }}">
                                </td>
                                <td class="px-4 py-6 text-center">
                                    <div class="flex flex-col items-end">
                                        <span class="text-xs font-black text-blue-600 dark:text-blue-400 total-pemenuhan">Rp {{ number_format($amountPo) }}</span>
                                        <span class="mt-1 text-[10px] font-medium text-slate-400 line-through">PO: Rp {{ number_format($amountPo) }}</span>
                                    </div>
                                </td>
                                <td class="px-8 py-6">
                                    <div class="flex justify-center gap-text">
                                        <span class="inline-flex items-center rounded-lg bg-emerald-100 px-2.5 py-1 text-[10px] font-bold text-emerald-700 dark:bg-emerald-900/20 dark:text-emerald-400">
                                            0 GAP
                                        </span>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </form>
</div>

@push('scripts')
<script type="module">
    function formatCurrency(num) {
        return 'Rp ' + new Intl.NumberFormat('id-ID').format(Math.round(num));
    }

    function calculateRow(row) {
        const price = parseFloat(row.dataset.price) || 0;
        const volPo = parseInt(row.dataset.volPo) || 0;
        const volPemenuhanInput = row.querySelector('.vol-pemenuhan');
        const volPemenuhan = parseInt(volPemenuhanInput.value) || 0;

        const totalPemenuhan = volPemenuhan * price;
        const gap = volPemenuhan - volPo;

        row.querySelector('.total-pemenuhan').textContent = formatCurrency(totalPemenuhan);
        
        const gapContainer = row.querySelector('.gap-text');
        if (gap === 0) {
            gapContainer.innerHTML = '<span class="inline-flex items-center rounded-lg bg-emerald-100 px-2.5 py-1 text-[10px] font-bold text-emerald-700 dark:bg-emerald-900/20 dark:text-emerald-400">0 GAP (MATCH)</span>';
        } else if (gap < 0) {
            gapContainer.innerHTML = `<span class="inline-flex items-center rounded-lg bg-rose-100 px-2.5 py-1 text-[10px] font-bold text-rose-700 dark:bg-rose-900/20 dark:text-rose-400">${gap} GAP (LESS)</span>`;
        } else {
            gapContainer.innerHTML = `<span class="inline-flex items-center rounded-lg bg-amber-100 px-2.5 py-1 text-[10px] font-bold text-amber-700 dark:bg-amber-900/20 dark:text-amber-400">+${gap} GAP (OVER)</span>`;
        }

        updateGrandTotal();
    }

    function updateGrandTotal() {
        let totalAmountPemenuhan = 0;
        let totalVolPemenuhan = 0;
        document.querySelectorAll('.boq-row').forEach(row => {
            const price = parseFloat(row.dataset.price) || 0;
            const volPemenuhan = parseInt(row.querySelector('.vol-pemenuhan').value) || 0;
            totalAmountPemenuhan += (volPemenuhan * price);
            totalVolPemenuhan += volPemenuhan;
        });
        
        document.getElementById('sidebar-total-pemenuhan').textContent = formatCurrency(totalAmountPemenuhan);
        document.getElementById('sidebar-total-vol-pemenuhan').textContent = new Intl.NumberFormat('id-ID').format(totalVolPemenuhan) + ' Unit';
    }

    document.querySelectorAll('.vol-pemenuhan').forEach(input => {
        input.addEventListener('input', function() {
            calculateRow(this.closest('.boq-row'));
        });
    });

    function showToast(type, message) {
        const containerId = 'warehouse-toast-container';
        let existing = document.getElementById(containerId);
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

    function openConfirmManual(title, message, onAccept) {
        const modal = document.getElementById('confirm-modal');
        if (!modal) {
            if (confirm(message)) onAccept();
            return;
        }
        document.getElementById('confirm-title').textContent = title;
        document.getElementById('confirm-message').textContent = message;
        modal.classList.remove('hidden');
        modal.classList.add('flex');
        
        const acceptBtn = document.getElementById('confirm-accept');
        const newAcceptBtn = acceptBtn.cloneNode(true);
        acceptBtn.parentNode.replaceChild(newAcceptBtn, acceptBtn);
        
        newAcceptBtn.addEventListener('click', () => {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
            onAccept();
        });
    }

    document.getElementById('warehouse-rekon-form').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const form = this;
        const btn = form.querySelector('button[type="submit"]');
        const originalText = btn.innerHTML;
        
        openConfirmManual('Proses Rekon Material?', 'Apakah Anda yakin ingin memproses data konsolidasi pemenuhan material ini?', () => {
            btn.disabled = true;
            btn.innerHTML = '<span class="animate-pulse">Processing...</span>';
            
            let formData = new FormData(form);
            const fileInput = document.getElementById('rekon_evidence');
            const fileName = fileInput.files[0] ? fileInput.files[0].name : "BARM Document";
            
            const xhr = new XMLHttpRequest();
            xhr.open('POST', '{{ route("tasks.warehouse.rekon-process") }}', true);
            xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
            xhr.setRequestHeader('X-CSRF-TOKEN', '{{ csrf_token() }}');

            if (window.trackUpload) {
                window.trackUpload(xhr, fileName);
            }

            xhr.onload = function() {
                if (xhr.status >= 200 && xhr.status < 300) {
                    try {
                        const result = JSON.parse(xhr.responseText);
                        if (result.success) {
                            showToast('success', result.message);
                            setTimeout(() => {
                                window.location.href = result.redirect;
                            }, 1500);
                        } else {
                            throw new Error(result.error || 'Server error');
                        }
                    } catch (e) {
                        showToast('error', e.message);
                        btn.disabled = false;
                        btn.innerHTML = originalText;
                    }
                } else {
                    let errorMsg = 'Server error';
                    try {
                        const result = JSON.parse(xhr.responseText);
                        errorMsg = result.error || result.message || errorMsg;
                    } catch(e) {}
                    showToast('error', errorMsg);
                    btn.disabled = false;
                    btn.innerHTML = originalText;
                }
            };

            xhr.onerror = function() {
                showToast('error', 'Network error occurred');
                btn.disabled = false;
                btn.innerHTML = originalText;
            };

            xhr.send(formData);
        });
    });
</script>
@endpush
@endsection
