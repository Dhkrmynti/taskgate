@extends('layouts.dashboard')

@section('title', 'Finance Rekon Detail (TGIDRF)')
@section('date_label', 'Finance')
@section('hello_name', 'Detail Rekon Finance')

@section('content')
<div class="space-y-3">
    <!-- Header Actions -->
    <div class="flex justify-between items-center">
        <a href="{{ route('finance-rekon.index') }}" 
           class="flex items-center gap-2 rounded-xl border border-[#e6e7f0] bg-white text-xs font-bold text-slate-600 transition hover:bg-slate-50 dark:border-brand-darkLine dark:bg-[#161f35] dark:text-slate-400 dark:hover:bg-slate-800 p-3 shadow-soft">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                <path d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            Kembali ke Monitoring
        </a>
        <div class="flex gap-3">
            @if($rekon->boq_file_path)
                <a href="{{ route('rekon.finance-download', [$rekon->id, 'boq']) }}" 
                   class="flex items-center gap-2 rounded-xl border border-[#e6e7f0] bg-white px-4 py-2 text-xs font-bold text-emerald-600 transition hover:bg-emerald-50 dark:border-brand-darkLine dark:bg-brand-darkPanel dark:text-emerald-400">
                    Download BOQ Final
                </a>
            @endif
        </div>
    </div>

    <!-- Universal Top Phase Tracker -->
    <section class="rounded-xl border border-[var(--panel-border)] bg-[var(--panel-bg)] p-3 shadow-soft mb-3">
        <p class="mb-2 text-xs font-semibold uppercase tracking-[0.2em] text-brand-muted dark:text-slate-500 text-center">Fase Pekerjaan Utama</p>
        @include('project-data.partials.snake-stepper', ['project' => $rekon])
    </section>

    <!-- General Info Card (Baseline Style) -->
    <section class="rounded-xl border border-[#e6e7f0] bg-white p-3 shadow-soft dark:border-brand-darkLine dark:bg-brand-darkPanel">
        <div class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-4">
            <div class="rounded-xl bg-[#fbfbfe] p-5 dark:bg-[#161f35]">
                <p class="text-[10px] font-semibold uppercase tracking-[0.2em] text-brand-muted dark:text-slate-500">ID Finance</p>
                <p class="mt-3 text-lg font-bold text-blue-600 font-mono">{{ $rekon->id }}</p>
            </div>
            <div class="rounded-xl bg-[#fbfbfe] p-5 dark:bg-[#161f35]">
                <p class="text-[10px] font-semibold uppercase tracking-[0.2em] text-brand-muted dark:text-slate-500">APM Number</p>
                <p class="mt-3 text-lg font-bold text-slate-800 dark:text-white">{{ $rekon->apm_number ?? '-' }}</p>
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
                <!-- Fetch the commerce rekons directly related to this finance rekon -->
                @forelse($rekon->commerceRekons as $commerce)
                    <span class="inline-flex items-center rounded-lg bg-slate-50 px-3 py-1.5 text-[10px] font-bold uppercase tracking-wider text-slate-600 dark:bg-slate-800 dark:text-slate-400 border border-slate-100 dark:border-slate-700">
                        {{ $commerce->id }}
                    </span>
                @empty
                    <span class="text-xs text-slate-500">-</span>
                @endforelse
            </div>
        </div>
    </section>

    <!-- Summary Stats Bar -->
    <div class="grid grid-cols-1 gap-6">
        <div class="rounded-xl bg-blue-600 p-6 text-white shadow-lg shadow-blue-500/20">
            <h4 class="text-[10px] font-bold uppercase tracking-[0.2em] opacity-80">Total Amount Jasa</h4>
            <p class="mt-2 text-2xl font-bold tracking-tight">Rp {{ number_format($rekon->total_amount, 0, ',', '.') }}</p>
        </div>
    </div>

    <!-- BoQ Detailed Table -->
    <section class="rounded-xl border border-[#e6e7f0] bg-white p-3 shadow-soft dark:border-brand-darkLine dark:bg-brand-darkPanel">
        <div class="flex items-center justify-between mb-6">
            <h3 class="text-xs font-semibold uppercase tracking-[0.2em] text-brand-muted dark:text-slate-500">BoQ Final (JASA)</h3>
            
            <div class="flex items-center gap-3">
                <label for="boqSiteFilter" class="text-xs font-bold uppercase tracking-widest text-slate-500">Filter By Site</label>
                <select id="boqSiteFilter" class="min-w-[200px] rounded-lg border border-[#e6e7f0] bg-[#fbfbfe] px-3 py-1.5 text-xs font-bold text-slate-700 shadow-sm focus:border-blue-500 focus:outline-none dark:border-brand-darkLine dark:bg-[#161f35] dark:text-white">
                    <option value="all">Semua (Gabungan)</option>
                    @foreach($rekon->commerceRekons as $crekon)
                        @foreach($crekon->batches as $batch)
                            @foreach($batch->projects as $site)
                                <option value="{{ $site->id }}">{{ $site->id }} - {{ $site->project_name }}</option>
                            @endforeach
                        @endforeach
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
                            <th class="px-3 py-3 text-left text-xs font-semibold uppercase tracking-[0.12em] text-brand-muted dark:text-slate-400">Item Jasa</th>
                            <th class="px-3 py-3 text-right text-xs font-semibold uppercase tracking-[0.12em] text-brand-muted dark:text-slate-400">Volume</th>
                            <th class="px-3 py-3 text-right text-xs font-semibold uppercase tracking-[0.12em] text-brand-muted dark:text-slate-400">Harga</th>
                            <th class="px-3 py-3 text-right text-xs font-semibold uppercase tracking-[0.12em] text-brand-muted dark:text-slate-400">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- CONSOLIDATED ROWS -->
                        @foreach($rekon->boqDetails as $item)
                            <tr class="whitespace-nowrap" data-site="all">
                                <td>
                                    <span class="text-sm dark:text-white font-mono">{{ $item->designator }}</span>
                                </td>
                                <td>
                                    <span class="text-xs text-brand-muted dark:text-slate-400 capitalize">{{ strtolower($item->description) }}</span>
                                </td>
                                <td class="text-right">
                                    <span class="text-sm dark:text-white font-bold">{{ number_format($item->volume) }}</span>
                                </td>
                                <td class="text-right">
                                    <span class="text-sm text-brand-muted dark:text-slate-400">{{ number_format($item->price, 0, ',', '.') }}</span>
                                </td>
                                <td class="text-right font-bold">
                                    <span class="text-sm dark:text-white font-mono">{{ number_format($item->volume * $item->price, 0, ',', '.') }}</span>
                                </td>
                            </tr>
                        @endforeach

                        <!-- SITE SPECIFIC ROWS -->
                        @foreach($rekon->commerceRekons as $crekon)
                            @foreach($crekon->batches as $batch)
                                @foreach($batch->projects as $site)
                                    @foreach($site->boqDetails->filter(fn($d) => str_starts_with($d->designator, 'J-')) as $item)
                                        <tr class="whitespace-nowrap" data-site="{{ $site->id }}">
                                            <td>
                                                <span class="text-sm dark:text-white font-mono">{{ $item->designator }}</span>
                                            </td>
                                            <td>
                                                <span class="text-xs text-brand-muted dark:text-slate-400 capitalize">{{ strtolower($item->description) }}</span>
                                            </td>
                                            <td class="text-right">
                                                <span class="text-sm dark:text-white font-bold">{{ number_format($item->volume_planning) }}</span>
                                            </td>
                                            <td class="text-right">
                                                <span class="text-sm text-brand-muted dark:text-slate-400">{{ number_format($item->price_planning, 0, ',', '.') }}</span>
                                            </td>
                                            <td class="text-right font-bold">
                                                <span class="text-sm dark:text-white font-mono">{{ number_format($item->volume_planning * $item->price_planning, 0, ',', '.') }}</span>
                                            </td>
                                        </tr>
                                    @endforeach
                                @endforeach
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
