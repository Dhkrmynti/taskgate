@extends('layouts.dashboard')

@section('title', 'Project Detail - Finance')
@section('date_label', 'Finance Detail')
@section('hello_name', 'Payment Details: ' . $project->id)

@section('content')
    <div class="space-y-3">
        <!-- Phase Tracker -->
        <section class="rounded-xl border border-[var(--panel-border)] bg-[var(--panel-bg)] p-3 shadow-soft">
            <p class="mb-2 text-xs font-semibold uppercase tracking-[0.2em] text-brand-muted dark:text-slate-500 text-center">Fase Pekerjaan Utama</p>
            @include('project-data.partials.snake-stepper')
        </section>

        <!-- Finance Info Header -->
        <section class="rounded-xl border border-violet-500/20 bg-violet-50/10 p-4 dark:border-violet-900/30 dark:bg-violet-900/10 shadow-soft">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-xl font-black text-slate-800 dark:text-white">{{ $project->project_name }}</h2>
                    <p class="text-xs font-bold text-violet-600 dark:text-violet-400 mt-1 uppercase tracking-widest">Verifikasi Jasa & Penagihan (Finance)</p>
                </div>
                <div class="text-right">
                    <p class="text-[9px] text-slate-400 font-bold uppercase">APM Number</p>
                    <p class="text-xs font-black">{{ $project->apm_number ?: 'NOT SET' }}</p>
                </div>
            </div>
        </section>

        <!-- Jasa BoQ Section -->
        <section class="rounded-xl border border-[#e6e7f0] bg-white p-4 shadow-soft dark:border-brand-darkLine dark:bg-brand-darkPanel">
            <h3 class="text-lg font-bold mb-4 flex items-center gap-2">
                <i class="fas fa-hand-holding-usd text-violet-500"></i>
                Rincian Tagihan Jasa (BoQ J-)
            </h3>
            
            <div class="overflow-hidden rounded-xl border border-[#e6e7f0] dark:border-brand-darkLine">
                <table id="boq-table" class="min-w-full text-sm">
                    <thead>
                        <tr class="bg-slate-50 dark:bg-slate-800/50 text-violet-700 dark:text-violet-300">
                            <th class="px-3 py-3 text-left">Designator</th>
                            <th class="px-3 py-3 text-left">Description</th>
                            <th class="px-3 py-3 text-right">Volume</th>
                            <th class="px-3 py-3 text-right">Price</th>
                            <th class="px-3 py-3 text-right">Total Jasa</th>
                        </tr>
                    </thead>
                    <tbody>
                        {{-- Data dimuat via AJAX --}}
                    </tbody>
                </table>
            </div>

            <div class="mt-4 flex justify-end">
                <div class="p-4 bg-violet-50 rounded-xl dark:bg-violet-900/20 border border-violet-100 dark:border-violet-800">
                    <p class="text-[10px] font-black text-violet-400 uppercase">Total Tagihan Jasa</p>
                    <p class="text-2xl font-black text-violet-700 dark:text-violet-300 font-mono">Rp {{ number_format($boqTotals['totalJasa'] ?? 0, 0, ',', '.') }}</p>
                </div>
            </div>
        </section>

        <!-- Finance Evidence -->
        <section class="rounded-xl border border-[#e6e7f0] bg-white p-4 shadow-soft dark:border-brand-darkLine dark:bg-brand-darkPanel">
             <h3 class="text-lg font-bold mb-4">Evidence Pembayaran & BA</h3>
             <div class="grid gap-3 sm:grid-cols-2 md:grid-cols-3">
                 @php $fFiles = $evidenceMap->get('finance_rekon') ?: collect(); @endphp
                 @forelse($fFiles as $file)
                    <div class="rounded-xl border border-slate-100 p-3 flex flex-col gap-2 dark:border-brand-darkLine">
                        <span class="px-2 py-0.5 rounded-lg bg-violet-100 text-violet-700 text-[9px] font-black uppercase w-fit">{{ $file->type }}</span>
                        <p class="text-xs font-bold truncate mt-1">{{ $file->file_name }}</p>
                        <a href="{{ route('project-data.evidence-files.download', [$project, $file->id]) }}" target="_blank" class="mt-1 text-[10px] font-black text-blue-600 hover:underline">LIHAT DOKUMEN</a>
                    </div>
                 @empty
                    <div class="col-span-full py-4 text-center border-2 border-dashed border-slate-100 rounded-xl dark:border-brand-darkLine text-slate-400 font-bold text-xs uppercase tracking-widest">
                        Data Evidence Kosong
                    </div>
                 @endforelse
             </div>
        </section>
    </div>
@endsection

@push('scripts')
<script type="module">
    $(document).ready(function() {
        $('#boq-table').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: "{{ route('project-data.boq-data', $project->id) }}",
                data: { type: 'jasa' }
            },
            columns: [
                { data: 'designator', name: 'designator', className: 'font-mono text-[11px] font-bold' },
                { data: 'description', name: 'description', className: 'text-[11px] text-slate-600 dark:text-slate-400' },
                { data: 'volume_planning', name: 'volume_planning', className: 'text-right text-xs' },
                { data: 'price_planning', name: 'price_planning', className: 'text-right text-xs font-mono' },
                { data: 'amount', name: 'amount', className: 'text-right text-xs font-black text-violet-600 dark:text-violet-400 font-mono', orderable: false, searchable: false }
            ],
            scrollX: true,
            ordering: true,
            order: [[0, 'asc']],
            destroy: true,
            language: {
                search: "Cari Jasa:",
            }
        });
    });
</script>
@endpush
