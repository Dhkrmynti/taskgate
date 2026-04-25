@extends('layouts.dashboard')

@section('title', 'Project Detail - Commerce')
@section('date_label', 'Commerce Detail')
@section('hello_name', 'Project Detail: ' . $project->id)

@section('content')
    <div class="space-y-3">
        <!-- Phase Tracker -->
        <section class="rounded-xl border border-[var(--panel-border)] bg-[var(--panel-bg)] p-3 shadow-soft">
            <p class="mb-2 text-xs font-semibold uppercase tracking-[0.2em] text-brand-muted dark:text-slate-500 text-center">Fase Pekerjaan Utama</p>
            @include('project-data.partials.snake-stepper')
        </section>

        <!-- Commerce Summary -->
        <section class="rounded-xl border border-blue-500/20 bg-blue-50/10 p-4 dark:border-blue-900/30 dark:bg-blue-900/10 shadow-soft">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div>
                    <h2 class="text-xl font-black text-slate-800 dark:text-white">{{ $project->project_name }}</h2>
                    <p class="text-xs font-bold text-blue-600 dark:text-blue-400 mt-1 uppercase tracking-widest">Analisis Rekonsiliasi Commerce</p>
                </div>
                <div class="text-right">
                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-tighter">Budget Grand Total</p>
                    <p class="text-xl font-black text-slate-800 dark:text-white">Rp {{ number_format($boqTotals['grandTotal'] ?? 0, 0, ',', '.') }}</p>
                </div>
            </div>
        </section>

        <!-- BoQ Upload & View -->
        <section class="rounded-xl border border-[#e6e7f0] bg-white p-4 shadow-soft dark:border-brand-darkLine dark:bg-brand-darkPanel">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-bold">Rincian Item BoQ</h3>
                <div class="flex items-center gap-2">
                    <span class="text-[10px] font-bold text-slate-400 uppercase">Input / Update:</span>
                    <form action="{{ route('project-data.evidences.store', $project) }}" method="POST" enctype="multipart/form-data" class="flex items-center gap-2">
                        @csrf
                        <input type="hidden" name="type" value="boq">
                        <input name="evidence_file" type="file" accept=".xls,.xlsx" class="text-[10px] bg-slate-50 p-1 rounded font-bold border border-slate-200">
                        <button type="submit" class="rounded bg-slate-800 px-3 py-1 text-[10px] font-black text-white hover:bg-slate-700">UPLOAD</button>
                    </form>
                </div>
            </div>

            <div class="overflow-hidden rounded-xl border border-[#e6e7f0] dark:border-brand-darkLine">
                <table id="boq-table" class="min-w-full text-sm">
                    <thead>
                        <tr class="bg-slate-50 dark:bg-slate-800/50">
                            <th class="px-3 py-2 text-left">Designator</th>
                            <th class="px-3 py-2 text-left">Description</th>
                            <th class="px-3 py-2 text-right">Volume</th>
                            <th class="px-3 py-2 text-right">Price</th>
                            <th class="px-3 py-2 text-right">Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        {{-- Data dimuat via AJAX --}}
                    </tbody>
                </table>
            </div>
        </section>

        <!-- Financial Summary Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-3 pb-6">
            <div class="rounded-xl border border-blue-100 bg-blue-50/20 p-4 dark:border-blue-900/30">
                <p class="text-[10px] font-black uppercase text-blue-500 tracking-widest">Total Material</p>
                <p class="text-2xl font-black text-blue-700 dark:text-blue-300 mt-1">Rp {{ number_format($boqTotals['totalMaterial'] ?? 0, 0, ',', '.') }}</p>
            </div>
            <div class="rounded-xl border border-violet-100 bg-violet-50/20 p-4 dark:border-violet-900/30">
                <p class="text-[10px] font-black uppercase text-violet-500 tracking-widest">Total Jasa</p>
                <p class="text-2xl font-black text-violet-700 dark:text-violet-300 mt-1">Rp {{ number_format($boqTotals['totalJasa'] ?? 0, 0, ',', '.') }}</p>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script type="module">
    $(document).ready(function() {
        $('#boq-table').DataTable({
            processing: true,
            serverSide: true,
            ajax: "{{ route('project-data.boq-data', $project->id) }}",
            columns: [
                { data: 'designator', name: 'designator', className: 'font-mono text-[11px]' },
                { data: 'description', name: 'description', className: 'text-[11px] font-medium text-slate-600 dark:text-slate-300' },
                { data: 'volume_planning', name: 'volume_planning', className: 'text-right text-xs font-bold' },
                { data: 'price_planning', name: 'price_planning', className: 'text-right text-xs' },
                { data: 'amount', name: 'amount', className: 'text-right text-xs font-black', orderable: false, searchable: false }
            ],
            scrollX: true,
            ordering: true,
            order: [[0, 'asc']],
            destroy: true,
            language: {
                search: "Cari BoQ:",
            }
        });
    });
</script>
@endpush
