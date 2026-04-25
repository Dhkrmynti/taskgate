@extends('layouts.dashboard')

@section('title', 'Project Detail - Konstruksi')
@section('date_label', 'Konstruksi Detail')
@section('hello_name', 'Project Detail: ' . $project->id)

@section('content')
    <div class="space-y-3">
        <!-- Phase Tracker -->
        <section class="rounded-xl border border-[var(--panel-border)] bg-[var(--panel-bg)] p-3 shadow-soft">
            <p class="mb-2 text-xs font-semibold uppercase tracking-[0.2em] text-brand-muted dark:text-slate-500 text-center">Fase Pekerjaan Utama</p>
            @include('project-data.partials.snake-stepper')
        </section>

        <!-- Project Info -->
        <section class="rounded-xl border border-[var(--panel-border)] bg-[var(--panel-bg)] p-3 shadow-soft dark:border-brand-darkLine dark:bg-brand-darkPanel">
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
                    <p class="text-xs font-semibold uppercase tracking-[0.2em] text-brand-muted dark:text-slate-500">Program</p>
                    <p class="mt-3 text-lg font-semibold">{{ $project->program ?: '-' }}</p>
                </div>
                <div class="rounded-xl bg-[#fbfbfe] p-3 dark:bg-[#161f35]">
                    <p class="text-xs font-semibold uppercase tracking-[0.2em] text-brand-muted dark:text-slate-500">Branch</p>
                    <p class="mt-3 text-lg font-semibold">{{ $project->branch ?: '-' }}</p>
                </div>
            </div>
        </section>

        <!-- BoQ Static Table -->
        <section class="rounded-xl border border-[#e6e7f0] bg-white p-3 shadow-soft dark:border-brand-darkLine dark:bg-brand-darkPanel">
            <h3 class="text-lg font-bold mb-3 text-brand-text dark:text-white">Daftar BoQ Planning</h3>
            <div class="overflow-hidden rounded-xl border border-[#e6e7f0] dark:border-brand-darkLine">
                <table id="boq-table" class="min-w-full text-sm">
                    <thead>
                        <tr class="bg-slate-50 dark:bg-slate-800/50">
                            <th class="px-3 py-2 text-left">Designator</th>
                            <th class="px-3 py-2 text-left">Description</th>
                            <th class="px-3 py-2 text-right">Volume</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($project->boqDetails as $item)
                            <tr class="border-b border-slate-50 dark:border-slate-800">
                                <td class="px-3 py-2 font-mono text-xs">{{ $item->designator }}</td>
                                <td class="px-3 py-2 text-[11px]">{{ $item->description }}</td>
                                <td class="px-3 py-2 text-right text-xs font-bold">{{ number_format($item->volume_planning, 0) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </section>

        <!-- Evidence Dasar -->
        <section class="rounded-xl border border-[#e6e7f0] bg-white p-3 shadow-soft dark:border-brand-darkLine dark:bg-brand-darkPanel">
            <h3 class="text-lg font-bold mb-3 text-brand-text dark:text-white">Evidence Dasar Pekerjaan</h3>
            <div class="grid gap-3 sm:grid-cols-2">
                @foreach ($dasarFiles as $file)
                    <div class="flex items-center justify-between rounded-xl border border-slate-100 p-3 dark:border-brand-darkLine dark:bg-[#0f1728]">
                        <span class="text-xs font-bold truncate max-w-[200px]" title="{{ $file->file_name }}">{{ $file->file_name }}</span>
                        <a href="{{ route('project-data.evidence-files.download', [$project, $file]) }}" target="_blank" class="text-[10px] font-black text-blue-600 hover:underline">LIHAT PDF</a>
                    </div>
                @endforeach
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
                search: "Cari BoQ:",
            }
        });
    });
</script>
@endpush
