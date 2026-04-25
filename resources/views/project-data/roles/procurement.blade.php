@extends('layouts.dashboard')

@section('title', 'Project Detail - Procurement')
@section('date_label', 'Procurement Detail')
@section('hello_name', 'Project Detail: ' . $project->id)

@section('content')
    <div class="space-y-3">
        <!-- Phase Tracker -->
        <section class="rounded-xl border border-[var(--panel-border)] bg-[var(--panel-bg)] p-3 shadow-soft">
            <p class="mb-2 text-xs font-semibold uppercase tracking-[0.2em] text-brand-muted dark:text-slate-500 text-center">Fase Pekerjaan Utama</p>
            @include('project-data.partials.snake-stepper')
        </section>

        <!-- Project/Batch Info -->
        <section class="rounded-xl border border-[var(--panel-border)] bg-[var(--panel-bg)] p-3 shadow-soft dark:border-brand-darkLine dark:bg-brand-darkPanel">
            @if($isBatch)
                <div class="grid gap-3 md:grid-cols-3">
                    <div class="rounded-xl bg-blue-50 p-3 dark:bg-blue-900/20 border border-blue-100 dark:border-blue-800">
                        <p class="text-xs font-semibold uppercase tracking-[0.2em] text-blue-600 dark:text-blue-400">Batch Name</p>
                        <p class="mt-2 text-xl font-bold dark:text-white">{{ $project->project_name }}</p>
                    </div>
                    <div class="rounded-xl bg-slate-50 p-3 dark:bg-slate-800/20 border border-slate-100 dark:border-slate-800">
                        <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-500 dark:text-slate-400">Batch ID</p>
                        <p class="mt-2 text-xl font-bold dark:text-white">{{ $project->id }}</p>
                    </div>
                    <div class="rounded-xl bg-slate-50 p-3 dark:bg-slate-800/20 border border-slate-100 dark:border-slate-800">
                        <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-500 dark:text-slate-400">PO Number</p>
                        <p class="mt-2 text-xl font-bold dark:text-white">{{ $project->po_number ?: '-' }}</p>
                    </div>
                </div>
            @else
                <div class="grid gap-3 md:grid-cols-2 xl:grid-cols-4">
                    <div class="rounded-xl bg-[#fbfbfe] p-3 dark:bg-[#161f35]">
                        <p class="text-xs font-semibold uppercase tracking-[0.2em] text-brand-muted dark:text-slate-500">Project Name</p>
                        <p class="mt-3 text-lg font-semibold">{{ $project->project_name }}</p>
                    </div>
                    <div class="rounded-xl bg-[#fbfbfe] p-3 dark:bg-[#161f35]">
                        <p class="text-xs font-semibold uppercase tracking-[0.2em] text-brand-muted dark:text-slate-500">ID Taskgate</p>
                        <p class="mt-3 text-lg font-semibold">{{ $project->id }}</p>
                    </div>
                    <div class="rounded-xl bg-[#fbfbfe] p-3 dark:bg-[#161f35]">
                        <p class="text-xs font-semibold uppercase tracking-[0.2em] text-brand-muted dark:text-slate-500">Customer</p>
                        <p class="mt-3 text-lg font-semibold">{{ $project->customer ?: '-' }}</p>
                    </div>
                    <div class="rounded-xl bg-[#fbfbfe] p-3 dark:bg-[#161f35]">
                        <p class="text-xs font-semibold uppercase tracking-[0.2em] text-brand-muted dark:text-slate-500">Branch</p>
                        <p class="mt-3 text-lg font-semibold">{{ $project->branch ?: '-' }}</p>
                    </div>
                </div>
            @endif
        </section>

        <!-- BoQ Section -->
        <section class="rounded-xl border border-[#e6e7f0] bg-white p-3 shadow-soft dark:border-brand-darkLine dark:bg-brand-darkPanel">
            <div class="flex items-center justify-between gap-4">
                <h3 class="text-lg font-bold">Detail BoQ Items</h3>
                @if($isBatch)
                <select id="boqSiteFilter" class="rounded-lg border border-[#e6e7f0] px-3 py-1.5 text-xs font-bold dark:border-brand-darkLine dark:bg-[#161f35] dark:text-white">
                    <option value="all">Semua Site</option>
                    @foreach($project->projects as $site)
                        <option value="{{ $site->id }}">{{ $site->id }}</option>
                    @endforeach
                </select>
                @endif
            </div>

            <div class="mt-3 overflow-hidden rounded-xl border border-[#e6e7f0] dark:border-brand-darkLine">
                <div class="p-4">
                    <table id="boq-table" class="min-w-full text-sm">
                        <thead>
                            <tr class="bg-slate-50 dark:bg-slate-800/50">
                                <th class="px-3 py-3 text-left">Designator</th>
                                <th class="px-3 py-3 text-left">Description</th>
                                <th class="px-3 py-3 text-right">Volume</th>
                                <th class="px-3 py-3 text-right">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            {{-- Data dimuat via AJAX --}}
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Totals -->
            <div class="mt-4 grid grid-cols-1 gap-3 sm:grid-cols-3">
                <div class="rounded-xl border border-emerald-100 bg-emerald-50/20 p-3 dark:border-emerald-900/30">
                    <p class="text-[10px] font-black uppercase text-emerald-600 dark:text-emerald-400">Grand Total</p>
                    <p class="mt-1 text-xl font-black text-emerald-700 dark:text-emerald-300">Rp {{ number_format($boqTotals['grandTotal'] ?? 0, 0, ',', '.') }}</p>
                </div>
            </div>
        </section>

        <!-- Evidence Files -->
        <section class="rounded-xl border border-[#e6e7f0] bg-white p-3 shadow-soft dark:border-brand-darkLine dark:bg-brand-darkPanel">
             <h3 class="text-lg font-bold mb-3">Evidence Files</h3>
             <div class="grid gap-3 md:grid-cols-2">
                 @php $poFile = $evidenceMap->get('procurement_po')?->first(); @endphp
                 <div class="rounded-xl border border-dashed border-[#d9dceb] p-3 dark:border-brand-darkLine">
                     <p class="text-sm font-bold">PO Document</p>
                     @if($poFile)
                        <a href="{{ route('project-data.evidence-files.download', [$project, $poFile->id]) }}" target="_blank" class="mt-2 inline-flex items-center gap-2 text-xs font-black text-blue-600 hover:underline">
                            <i class="fas fa-file-pdf"></i> {{ $poFile->file_name }}
                        </a>
                     @else
                        <p class="mt-2 text-xs text-slate-400 italic">Belum ada file PO.</p>
                     @endif
                 </div>
             </div>
        </section>
    </div>
@endsection

@push('scripts')
<script type="module">
    $(document).ready(function() {
        const boqSiteFilter = document.getElementById('boqSiteFilter');
        
        var table = $('#boq-table').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: "{{ route('project-data.boq-data', $project->id) }}",
                data: function(d) {
                    d.site_id = boqSiteFilter ? boqSiteFilter.value : 'all';
                }
            },
            columns: [
                { data: 'designator', name: 'designator', className: 'font-mono' },
                { data: 'description', name: 'description', className: 'text-xs' },
                { data: 'volume_planning', name: 'volume_planning', className: 'text-right' },
                { data: 'amount', name: 'amount', className: 'text-right font-bold font-mono', orderable: false, searchable: false }
            ],
            scrollX: true,
            ordering: true,
            order: [[0, 'asc']],
            destroy: true,
            language: {
                search: "Cari BoQ:",
            }
        });

        if (boqSiteFilter) {
            boqSiteFilter.addEventListener('change', () => table.ajax.reload());
        }
    });
</script>
@endpush
