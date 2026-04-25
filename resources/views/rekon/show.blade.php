@extends('layouts.dashboard')

@section('title', 'Warehouse Rekon Detail')
@section('date_label', 'Warehouse')
@section('hello_name', 'Detail Rekon Material')

@section('content')
<div class="space-y-3">
    <!-- Header Actions -->
    <div class="flex justify-between items-center">
        <a href="{{ route('rekon.index') }}" 
           class="flex items-center gap-2 rounded-xl border border-[#e6e7f0] bg-white text-xs font-bold text-slate-600 transition hover:bg-slate-50 dark:border-brand-darkLine dark:bg-[#161f35] dark:text-slate-400 dark:hover:bg-slate-800 p-3 shadow-soft">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                <path d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            Kembali ke Monitoring
        </a>
        <div class="flex gap-3">
            @if($rekon->rekon_file_path)
                <a href="{{ route('rekon.warehouse-download', [$rekon->id, 'rekon']) }}" 
                   class="flex items-center gap-2 rounded-xl border border-[#e6e7f0] bg-white px-4 py-2 text-xs font-bold text-emerald-600 transition hover:bg-emerald-50 dark:border-brand-darkLine dark:bg-brand-darkPanel dark:text-emerald-400">
                    Download BARM
                </a>
            @endif
            <a href="{{ route('rekon.print', $rekon->id) }}" target="_blank"
               class="flex items-center gap-2 rounded-xl bg-slate-900 px-6 py-2 text-xs font-black text-white shadow-lg transition hover:bg-black dark:bg-blue-600 dark:hover:bg-blue-700">
                Cetak BA Rekon
            </a>
        </div>
    </div>

    <!-- Universal Top Phase Tracker -->
    <section class="rounded-xl border border-[var(--panel-border)] bg-[var(--panel-bg)] p-3 shadow-soft mb-3">
        <p class="mb-2 text-xs font-semibold uppercase tracking-[0.2em] text-brand-muted dark:text-slate-500 text-center">Fase Pekerjaan Utama</p>
        @php
            $realFase = $rekon->batches->first()->fase ?? 'ogp_rekon';
        @endphp
        @include('project-data.partials.snake-stepper', [
            'project' => (object)['id' => $rekon->id, 'fase' => $realFase],
            'unifiedSubfaseStatuses' => $unifiedSubfaseStatuses,
            'evidenceMap' => $evidenceMap
        ])
    </section>

    <!-- General Info Card (Baseline Style) -->
    <section class="rounded-xl border border-[#e6e7f0] bg-white p-3 shadow-soft dark:border-brand-darkLine dark:bg-brand-darkPanel">
        <div class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-4">
            <div class="rounded-xl bg-[#fbfbfe] p-5 dark:bg-[#161f35]">
                <p class="text-[10px] font-semibold uppercase tracking-[0.2em] text-brand-muted dark:text-slate-500">ID Taskgate</p>
                <p class="mt-3 text-lg font-bold text-blue-600 font-mono">{{ $rekon->id }}</p>
            </div>
            <div class="rounded-xl bg-[#fbfbfe] p-5 dark:bg-[#161f35]">
                <p class="text-[10px] font-semibold uppercase tracking-[0.2em] text-brand-muted dark:text-slate-500">Nomor Rekon</p>
                <p class="mt-3 text-lg font-bold text-slate-800 dark:text-white">{{ $rekon->rekon_number ?? '-' }}</p>
            </div>
            <div class="rounded-xl bg-[#fbfbfe] p-5 dark:bg-[#161f35]">
                <p class="text-[10px] font-semibold uppercase tracking-[0.2em] text-brand-muted dark:text-slate-500">Created By</p>
                <p class="mt-3 text-lg font-bold text-slate-800 dark:text-white truncate">{{ $rekon->creator->name ?? 'System' }}</p>
            </div>
            <div class="rounded-xl bg-[#fbfbfe] p-5 dark:bg-[#161f35]">
                <p class="text-[10px] font-semibold uppercase tracking-[0.2em] text-brand-muted dark:text-slate-500">Date Created</p>
                <p class="mt-3 text-lg font-bold text-slate-800 dark:text-white">{{ $rekon->created_at->format('d M Y') }}</p>
            </div>
        </div>

        <div class="mt-6 pt-6 border-t border-slate-100 dark:border-brand-darkLine">
            <p class="text-xs font-semibold uppercase tracking-[0.2em] text-brand-muted dark:text-slate-500 mb-4">Consolidated Batches</p>
            <div class="flex flex-wrap gap-2">
                @foreach($rekon->batches as $batch)
                    <span class="inline-flex items-center rounded-lg bg-slate-50 px-3 py-1.5 text-[10px] font-bold uppercase tracking-wider text-slate-600 dark:bg-slate-800 dark:text-slate-400 border border-slate-100 dark:border-slate-700">
                        {{ $batch->id }}
                    </span>
                @endforeach
            </div>
        </div>
    </section>

    <!-- Summary Stats Bar (Baseline Blue Style) -->
    <div class="grid grid-cols-1 gap-6 md:grid-cols-3">
        <div class="rounded-xl bg-blue-600 p-6 text-white shadow-lg shadow-blue-500/20">
            <h4 class="text-[10px] font-bold uppercase tracking-[0.2em] opacity-80">Total Planning</h4>
            <p class="mt-2 text-2xl font-bold tracking-tight">Rp {{ number_format($boqTotals['planning'], 0, ',', '.') }}</p>
        </div>
        <div class="rounded-xl bg-emerald-600 p-6 text-white shadow-lg shadow-emerald-500/20">
            <h4 class="text-[10px] font-bold uppercase tracking-[0.2em] opacity-80">Total Pemenuhan</h4>
            <p class="mt-2 text-2xl font-bold tracking-tight">Rp {{ number_format($boqTotals['pemenuhan'], 0, ',', '.') }}</p>
        </div>
        <div class="rounded-xl bg-slate-900 p-6 text-white shadow-xl dark:bg-slate-800">
            <h4 class="text-[10px] font-bold uppercase tracking-[0.2em] opacity-80">Vol Pemenuhan</h4>
            <p class="mt-2 text-2xl font-bold tracking-tight">
                {{ number_format($rekon->boqDetails->sum('volume_pemenuhan')) }} Unit
            </p>
        </div>
    </div>

    <!-- BoQ Detailed Table (Baseline Style) -->
    <section class="rounded-xl border border-[#e6e7f0] bg-white p-3 shadow-soft dark:border-brand-darkLine dark:bg-brand-darkPanel">
        <div class="flex items-center justify-between mb-6">
            <h3 class="text-xs font-semibold uppercase tracking-[0.2em] text-brand-muted dark:text-slate-500">Consolidated BoQ Details</h3>
            
            <div class="flex items-center gap-3">
                <label for="boqSiteFilter" class="text-xs font-bold uppercase tracking-widest text-slate-500">Filter By Batch</label>
                <select id="boqSiteFilter" class="min-w-[200px] rounded-lg border border-[#e6e7f0] bg-[#fbfbfe] px-3 py-1.5 text-xs font-bold text-slate-700 shadow-sm focus:border-blue-500 focus:outline-none dark:border-brand-darkLine dark:bg-[#161f35] dark:text-white">
                    <option value="all">Semua (Gabungan)</option>
                    @foreach($rekon->batches as $batch)
                        <option value="{{ $batch->id }}">{{ $batch->id }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        
        <div class="overflow-hidden rounded-xl border border-[#e6e7f0] dark:border-brand-darkLine">
            <div class="p-4">
                <table id="boq-table" class="min-w-full text-sm">
                    <thead>
                        <tr>
                            <th class="px-3 py-3 text-left text-xs font-semibold uppercase tracking-[0.12em] text-brand-muted dark:text-slate-400">Designator</th>
                            <th class="px-3 py-3 text-left text-xs font-semibold uppercase tracking-[0.12em] text-brand-muted dark:text-slate-400">Description</th>
                            <th class="px-3 py-3 text-right text-xs font-semibold uppercase tracking-[0.12em] text-brand-muted dark:text-slate-400">Planning</th>
                            <th class="px-3 py-3 text-right text-xs font-semibold uppercase tracking-[0.12em] text-brand-muted dark:text-slate-400">Pemenuhan</th>
                            <th class="px-3 py-3 text-center text-xs font-semibold uppercase tracking-[0.12em] text-brand-muted dark:text-slate-400">GAP</th>
                            <th class="px-3 py-3 text-right text-xs font-semibold uppercase tracking-[0.12em] text-brand-muted dark:text-slate-400">Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- CONSOLIDATED ROWS (M- ONLY) -->
                        @foreach($rekon->boqDetails->filter(fn($i) => str_starts_with(strtoupper($i->designator), 'M-')) as $item)
                        @php $gap = $item->volume_pemenuhan - $item->volume_planning; @endphp
                        <tr class="whitespace-nowrap" data-site="all">
                            <td>
                                <span class="text-sm dark:text-white font-mono">{{ $item->designator }}</span>
                            </td>
                            <td>
                                <span class="text-xs text-brand-muted dark:text-slate-400 capitalize">{{ strtolower($item->description) }}</span>
                            </td>
                            <td class="text-right">
                                <span class="text-sm dark:text-white">{{ number_format($item->volume_planning) }}</span>
                            </td>
                            <td class="text-right">
                                <span class="text-sm text-emerald-600 font-bold">{{ number_format($item->volume_pemenuhan) }}</span>
                            </td>
                            <td class="text-center">
                                <span class="inline-flex items-center justify-center min-w-[32px] text-[10px] font-bold px-2 py-0.5 rounded {{ $gap >= 0 ? 'bg-emerald-50 text-emerald-600 dark:bg-emerald-900/40 dark:text-emerald-400' : 'bg-red-50 text-red-600 dark:bg-red-900/40 dark:text-red-400' }}">
                                    {{ ($gap > 0 ? '+' : '') . $gap }}
                                </span>
                            </td>
                            <td class="text-right font-bold">
                                <span class="text-sm dark:text-white font-mono">Rp {{ number_format($item->volume_pemenuhan * $item->price_planning, 0, ',', '.') }}</span>
                            </td>
                        </tr>
                        @endforeach

                        <!-- BATCH SPECIFIC ROWS (M- ONLY) -->
                        @foreach($rekon->batches as $batch)
                            @foreach($batch->boqDetails->filter(fn($i) => str_starts_with(strtoupper($i->designator), 'M-')) as $item)
                            @php $gap = ($item->volume_pemenuhan ?? 0) - ($item->volume_planning ?? 0); @endphp
                            <tr class="whitespace-nowrap" data-site="{{ $batch->id }}">
                                <td>
                                    <span class="text-sm dark:text-white font-mono">{{ $item->designator }}</span>
                                </td>
                                <td>
                                    <span class="text-xs text-brand-muted dark:text-slate-400 capitalize">{{ strtolower($item->description) }}</span>
                                </td>
                                <td class="text-right">
                                    <span class="text-sm dark:text-white">{{ number_format($item->volume_planning ?? 0) }}</span>
                                </td>
                                <td class="text-right">
                                    <span class="text-sm text-emerald-600 font-bold">{{ number_format($item->volume_pemenuhan ?? 0) }}</span>
                                </td>
                                <td class="text-center">
                                    <span class="inline-flex items-center justify-center min-w-[32px] text-[10px] font-bold px-2 py-0.5 rounded {{ $gap >= 0 ? 'bg-emerald-50 text-emerald-600 dark:bg-emerald-900/40 dark:text-emerald-400' : 'bg-red-50 text-red-600 dark:bg-red-900/40 dark:text-red-400' }}">
                                        {{ ($gap > 0 ? '+' : '') . $gap }}
                                    </span>
                                </td>
                                <td class="text-right font-bold">
                                    <span class="text-sm dark:text-white font-mono">Rp {{ number_format(($item->volume_pemenuhan ?? 0) * ($item->price_planning ?? 0), 0, ',', '.') }}</span>
                                </td>
                            </tr>
                            @endforeach
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </section>
</div>
@endsection

@push('scripts')
<script type="module">
    $.fn.dataTable.ext.search.push(function(settings, data, dataIndex, rowData, counter) {
        if (settings.nTable.id !== 'boq-table') return true;
        var selected = $('#boqSiteFilter').val();
        var siteData = $(settings.aoData[dataIndex].nTr).attr('data-site');
        return siteData === selected;
    });

    $(document).ready(function() {
        var table = $('#boq-table').DataTable({
            scrollX: true,
            autoWidth: false,
            pageLength: 25,
            ordering: false,
            dom: '<"top"lf>rt<"bottom"ip><"clear">',
            language: {
                search: 'Cari:',
                zeroRecords: 'Data tidak ditemukan',
                paginate: {
                    previous: 'Prev',
                    next: 'Next'
                }
            }
        });

        $('#boqSiteFilter').on('change', function() {
            table.draw();
        });
    });
</script>
@endpush
