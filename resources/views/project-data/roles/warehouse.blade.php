@extends('layouts.dashboard')

@section('title', 'Project Detail - Warehouse')
@section('date_label', 'Warehouse Detail')
@section('hello_name', 'Material Details: ' . $project->id)

@section('content')
    <div class="space-y-3">
        <!-- Phase Tracker -->
        <section class="rounded-xl border border-[var(--panel-border)] bg-[var(--panel-bg)] p-3 shadow-soft">
            <p class="mb-2 text-xs font-semibold uppercase tracking-[0.2em] text-brand-muted dark:text-slate-500 text-center">Fase Pekerjaan Utama</p>
            @include('project-data.partials.snake-stepper')
        </section>

        <!-- Warehouse Info Header -->
        <section class="rounded-xl border border-emerald-500/20 bg-emerald-50/10 p-4 dark:border-emerald-900/30 dark:bg-emerald-900/10 shadow-soft">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-xl font-black text-slate-800 dark:text-white">{{ $project->project_name }}</h2>
                    <p class="text-xs font-bold text-emerald-600 dark:text-emerald-400 mt-1 uppercase tracking-widest">Manajemen Stok & Distribusi Material</p>
                </div>
                <div class="rounded-lg bg-white px-3 py-1 dark:bg-slate-800 border border-slate-200 dark:border-slate-700">
                    <p class="text-[9px] text-slate-400 font-bold uppercase">Batch ID</p>
                    <p class="text-xs font-black">{{ $project->id }}</p>
                </div>
            </div>
        </section>

        <!-- Material BoQ Section -->
        <section class="rounded-xl border border-[#e6e7f0] bg-white p-4 shadow-soft dark:border-brand-darkLine dark:bg-brand-darkPanel">
            <h3 class="text-lg font-bold mb-4 flex items-center gap-2">
                <i class="fas fa-boxes text-emerald-500"></i>
                Rincian Material (BoQ M-)
            </h3>
            
            <div class="overflow-hidden rounded-xl border border-[#e6e7f0] dark:border-brand-darkLine">
                <table id="boq-table" class="min-w-full text-sm">
                    <thead>
                        <tr class="bg-slate-50 dark:bg-slate-800/50 text-emerald-700 dark:text-emerald-300">
                            <th class="px-3 py-3 text-left">Designator</th>
                            <th class="px-3 py-3 text-left">Description</th>
                            <th class="px-3 py-3 text-right">Volume Plan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php 
                            $materialItems = $project->boqDetails->filter(fn($i) => str_starts_with(strtoupper($i->designator), 'M-'));
                        @endphp
                        @foreach ($materialItems as $item)
                        <tr class="border-b border-slate-50 dark:border-slate-800 hover:bg-slate-50/50 dark:hover:bg-slate-800/50 transition">
                            <td class="px-3 py-2 font-mono text-[11px] font-bold">{{ $item->designator }}</td>
                            <td class="px-3 py-2 text-[11px] text-slate-600 dark:text-slate-400">{{ $item->description }}</td>
                            <td class="px-3 py-2 text-right text-xs font-black text-emerald-600 dark:text-emerald-400">{{ number_format($item->volume_planning, 0) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if($materialItems->isEmpty())
                <div class="mt-4 p-8 text-center bg-slate-50 rounded-xl dark:bg-slate-800/50">
                    <p class="text-sm text-slate-400 italic">Tidak ada item material (M-) untuk project ini.</p>
                </div>
            @endif
        </section>

        <!-- Warehouse Evidence -->
        <section class="rounded-xl border border-[#e6e7f0] bg-white p-4 shadow-soft dark:border-brand-darkLine dark:bg-brand-darkPanel">
             <h3 class="text-lg font-bold mb-4">Evidence Warehouse</h3>
             <div class="grid gap-3 sm:grid-cols-2 md:grid-cols-3">
                 @php $wFiles = $evidenceMap->get('warehouse_rekon') ?: collect(); @endphp
                 @forelse($wFiles as $file)
                    <div class="rounded-xl border border-slate-100 p-3 flex flex-col gap-2 dark:border-brand-darkLine">
                        <p class="text-[10px] font-black uppercase text-slate-400">{{ $file->type }}</p>
                        <p class="text-xs font-bold truncate">{{ $file->file_name }}</p>
                        <a href="{{ route('project-data.evidence-files.download', [$project, $file->id]) }}" target="_blank" class="mt-1 text-[10px] font-black text-blue-600 hover:underline inline-flex items-center gap-1">
                            <i class="fas fa-download"></i> DOWNLOAD
                        </a>
                    </div>
                 @empty
                    <div class="col-span-full py-4 text-center border-2 border-dashed border-slate-100 rounded-xl dark:border-brand-darkLine">
                        <p class="text-xs text-slate-400 font-bold">BELUM ADA EVIDENCE UPLOADED</p>
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
            scrollX: true,
            ordering: false,
            destroy: true,
            language: {
                search: "Cari Material:",
            }
        });
    });
</script>
@endpush
