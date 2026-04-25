@extends('layouts.dashboard')

@section('title', 'Commerce Rekon Detail (TGIDRC)')
@section('date_label', 'Commerce')
@section('hello_name', 'Detail Rekon Commerce')

@section('content')
<div class="space-y-3">
    <!-- Header Actions -->
    <div class="flex justify-between items-center">
        <a href="{{ route('commerce-rekon.index') }}" 
           class="flex items-center gap-2 rounded-xl border border-[#e6e7f0] bg-white text-xs font-bold text-slate-600 transition hover:bg-slate-50 dark:border-brand-darkLine dark:bg-[#161f35] dark:text-slate-400 dark:hover:bg-slate-800 p-3 shadow-soft">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                <path d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            Kembali ke Monitoring
        </a>
        <div class="flex gap-3">
            @if($rekon->boq_file_path)
                <a href="{{ route('rekon.commerce-download', [$rekon->id, 'boq']) }}" 
                   class="flex items-center gap-2 rounded-xl border border-[#e6e7f0] bg-white px-4 py-2 text-xs font-bold text-emerald-600 transition hover:bg-emerald-50 dark:border-brand-darkLine dark:bg-brand-darkPanel dark:text-emerald-400">
                    Download Final BOQ
                </a>
            @endif
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
                <p class="text-[10px] font-semibold uppercase tracking-[0.2em] text-brand-muted dark:text-slate-500">ID Commerce</p>
                <p class="mt-3 text-lg font-bold text-blue-600 font-mono">{{ $rekon->id }}</p>
            </div>
            <div class="rounded-xl bg-[#fbfbfe] p-5 dark:bg-[#161f35]">
                <p class="text-[10px] font-semibold uppercase tracking-[0.2em] text-brand-muted dark:text-slate-500">Rekon Number</p>
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
                @if($rekon->batches && $rekon->batches->count() > 0)
                    @foreach($rekon->batches as $batch)
                        <span class="inline-flex items-center rounded-lg bg-slate-50 px-3 py-1.5 text-[10px] font-bold uppercase tracking-wider text-slate-600 dark:bg-slate-800 dark:text-slate-400 border border-slate-100 dark:border-slate-700">
                            {{ $batch->id }}
                        </span>
                    @endforeach
                @else
                    <span class="text-xs text-slate-500">-</span>
                @endif
            </div>
        </div>
    </section>

    <!-- Summary Stats Bar (Baseline Blue Style) -->
    <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
        <div class="rounded-xl bg-blue-600 p-6 text-white shadow-soft dark:bg-blue-700">
            <h4 class="text-[10px] font-bold uppercase tracking-[0.2em] opacity-80">Total Planning Amount</h4>
            <p class="mt-2 text-2xl font-bold tracking-tight">Rp {{ number_format($boqTotals['planning'], 0, ',', '.') }}</p>
        </div>
        <div class="rounded-xl bg-emerald-600 p-6 text-white shadow-soft dark:bg-emerald-700">
            <h4 class="text-[10px] font-bold uppercase tracking-[0.2em] opacity-80">Total Pemenuhan Amount</h4>
            <p class="mt-2 text-2xl font-bold tracking-tight">Rp {{ number_format($boqTotals['pemenuhan'], 0, ',', '.') }}</p>
        </div>
    </div>

    <!-- BoQ Detailed Table (Baseline Style) -->
    <section class="rounded-xl border border-[#e6e7f0] bg-white p-3 shadow-soft dark:border-brand-darkLine dark:bg-brand-darkPanel">
        <div class="flex items-center justify-between mb-6">
            <h3 class="text-xs font-semibold uppercase tracking-[0.2em] text-brand-muted dark:text-slate-500">BoQ Consolidated Details</h3>
            
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
                            <th class="px-3 py-3 text-left text-xs font-semibold uppercase tracking-[0.12em] text-brand-muted dark:text-slate-400">Item</th>
                            <th class="px-3 py-3 text-right text-xs font-semibold uppercase tracking-[0.12em] text-brand-muted dark:text-slate-400">Vol Plan</th>
                            <th class="px-3 py-3 text-right text-xs font-semibold uppercase tracking-[0.12em] text-brand-muted dark:text-slate-400">Vol Pemenuhan</th>
                            <th class="px-3 py-3 text-right text-xs font-semibold uppercase tracking-[0.12em] text-brand-muted dark:text-slate-400">Price</th>
                            <th class="px-3 py-3 text-right text-xs font-semibold uppercase tracking-[0.12em] text-brand-muted dark:text-slate-400">Amount Pemenuhan</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- CONSOLIDATED ROWS -->
                        @foreach($rekon->boqDetails as $item)
                            <tr class="whitespace-nowrap" data-site="all">
                                <td><span class="text-sm dark:text-white font-mono">{{ $item->designator }}</span></td>
                                <td><span class="text-xs text-brand-muted dark:text-slate-400 capitalize">{{ strtolower($item->description) }}</span></td>
                                <td class="text-right"><span class="text-sm dark:text-white">{{ number_format($item->volume_planning) }}</span></td>
                                <td class="text-right"><span class="text-sm text-emerald-600 font-bold">{{ number_format($item->volume_pemenuhan) }}</span></td>
                                <td class="text-right"><span class="text-sm text-brand-muted dark:text-slate-400">{{ number_format($item->price_planning, 0, ',', '.') }}</span></td>
                                <td class="text-right font-bold"><span class="text-sm dark:text-white font-mono">{{ number_format($item->volume_pemenuhan * $item->price_planning, 0, ',', '.') }}</span></td>
                            </tr>
                        @endforeach

                        <!-- BATCH SPECIFIC ROWS -->
                        @foreach($rekon->batches as $batch)
                            @foreach($batch->boqDetails as $item)
                                <tr class="whitespace-nowrap" data-site="{{ $batch->id }}">
                                    <td><span class="text-sm dark:text-white font-mono">{{ $item->designator }}</span></td>
                                    <td><span class="text-xs text-brand-muted dark:text-slate-400 capitalize">{{ strtolower($item->description) }}</span></td>
                                    <td class="text-right"><span class="text-sm dark:text-white">{{ number_format($item->volume_planning) }}</span></td>
                                    <td class="text-right"><span class="text-sm text-emerald-600 font-bold">{{ number_format($item->volume_pemenuhan) }}</span></td>
                                    <td class="text-right"><span class="text-sm text-brand-muted dark:text-slate-400">{{ number_format($item->price_planning, 0, ',', '.') }}</span></td>
                                    <td class="text-right font-bold"><span class="text-sm dark:text-white font-mono">{{ number_format($item->volume_pemenuhan * $item->price_planning, 0, ',', '.') }}</span></td>
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
        // Because dataTables loads all rows, we filter by the data-site attribute matching the dropdown value
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
