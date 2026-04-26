@extends('layouts.dashboard')

@section('title', 'Taskgate Project Detail')
@section('date_label', 'Project Detail')
@section('hello_name', 'Project Detail')
@section('hero_subtitle', 'Halaman ini menampilkan rincian project, progres fase, evidence tiap tahap, serta penyesuaian volume dan price aktual item BoQ.')



@section('content')
    <div class="space-y-3">
        <!-- Universal Top Phase Tracker for both TGIDOP and Batch -->
        <section class="rounded-xl border border-[var(--panel-border)] bg-[var(--panel-bg)] p-3 shadow-soft">
            <p class="mb-2 text-xs font-semibold uppercase tracking-[0.2em] text-brand-muted dark:text-slate-500 text-center">Fase Pekerjaan Utama</p>
            @include('project-data.partials.snake-stepper')
        </section>

        <section class="rounded-xl border border-[var(--panel-border)] bg-[var(--panel-bg)] p-3 shadow-soft dark:border-brand-darkLine dark:bg-brand-darkPanel">
            @if($isBatch)
                <!-- Batch Summary Header -->
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

                @php
                    $idPrefix = strtok($project->id, '-');
                    $childLabel = match($idPrefix) {
                        'TGIDRF' => 'Rekons (TGIDRC)',
                        'TGIDRC', 'TGIDRM' => 'Batches (TGIDSP)',
                        'TGIDSP' => 'Sites (TGIDOP)',
                        default => 'Constituent Items'
                    };
                    $constituents = $project->constituents ?? collect();
                @endphp

                @if($constituents->isNotEmpty())
                <!-- Constituent Section -->
                <div class="mt-10 pt-10 border-t border-slate-100 dark:border-brand-darkLine">
                    <div class="flex items-center justify-between mb-6">
                        <h3 class="text-sm font-bold uppercase tracking-widest text-slate-400 flex items-center gap-2">
                            <i class="fas fa-layer-group text-blue-500"></i>
                            {{ count($constituents) }} Constituent {{ $childLabel }}
                        </h3>
                    </div>
                    
                    <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 pb-10">
                        @foreach($constituents as $item)
                            <div class="group relative rounded-xl border border-[#e6e7f0] bg-white p-3 transition-all hover:shadow-xl hover:-translate-y-1 dark:border-brand-darkLine dark:bg-[#0f1728]">
                                <div class="flex items-start justify-between">
                                    <div class="flex flex-col">
                                        <p class="text-[10px] font-bold uppercase tracking-wider text-blue-600 dark:text-blue-400">{{ $item->id }}</p>
                                        <h4 class="mt-1 text-sm font-bold text-slate-800 dark:text-white line-clamp-1" title="{{ $item->project_name ?? $item->id }}">
                                            {{ $item->project_name ?? $item->id }}
                                        </h4>
                                    </div>
                                    <a href="{{ route('project-data.show', $item->id) }}" class="h-10 w-10 rounded-full bg-slate-50 dark:bg-slate-800 flex items-center justify-center text-slate-400 hover:text-blue-600 transition-all shadow-sm group-hover:bg-blue-50">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" /></svg>
                                    </a>
                                </div>
                                <div class="mt-4 flex flex-col gap-2 text-[11px] font-medium text-slate-500 dark:text-slate-400">
                                    <span class="flex items-center gap-2">
                                        <i class="fas fa-building text-blue-400 w-3"></i>
                                        {{ $item->customer }}
                                    </span>
                                    <span class="flex items-center gap-2">
                                        <i class="fas fa-map-marker-alt text-red-400 w-3"></i>
                                        {{ $item->branch }}
                                    </span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
                @endif
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
                        <p class="text-xs font-semibold uppercase tracking-[0.2em] text-brand-muted dark:text-slate-500">PID</p>
                        <p class="mt-3 text-lg font-semibold">{{ $project->pid ?: '-' }}</p>
                    </div>
                    <div class="rounded-xl bg-[#fbfbfe] p-3 dark:bg-[#161f35]">
                        <p class="text-xs font-semibold uppercase tracking-[0.2em] text-brand-muted dark:text-slate-500">Customer</p>
                        <p class="mt-3 text-lg font-semibold">{{ $project->customer ?: '-' }}</p>
                    </div>
                    <div class="rounded-xl bg-[#fbfbfe] p-3 dark:bg-[#161f35]">
                        <p class="text-xs font-semibold uppercase tracking-[0.2em] text-brand-muted dark:text-slate-500">Portofolio</p>
                        <p class="mt-3 text-lg font-semibold">{{ $project->portofolio ?: '-' }}</p>
                    </div>
                    <div class="rounded-xl bg-[#fbfbfe] p-3 dark:bg-[#161f35]">
                        <p class="text-xs font-semibold uppercase tracking-[0.2em] text-brand-muted dark:text-slate-500">Program</p>
                        <p class="mt-3 text-lg font-semibold">{{ $project->program ?: '-' }}</p>
                    </div>
                    <div class="rounded-xl bg-[#fbfbfe] p-3 dark:bg-[#161f35]">
                        <p class="text-xs font-semibold uppercase tracking-[0.2em] text-brand-muted dark:text-slate-500">Jenis Eksekusi</p>
                        <p class="mt-3 text-lg font-semibold">{{ $project->jenis_eksekusi ?: '-' }}</p>
                    </div>
                    <div class="rounded-xl bg-[#fbfbfe] p-3 dark:bg-[#161f35]">
                        <p class="text-xs font-semibold uppercase tracking-[0.2em] text-brand-muted dark:text-slate-500">Branch</p>
                        <p class="mt-3 text-lg font-semibold">{{ $project->branch ?: '-' }}</p>
                    </div>
                    <div class="rounded-xl bg-[#fbfbfe] p-3 dark:bg-[#161f35]">
                        <p class="text-xs font-semibold uppercase tracking-[0.2em] text-brand-muted dark:text-slate-500">PM Project</p>
                        <p class="mt-3 text-lg font-semibold">{{ $project->pm_project ?: '-' }}</p>
                    </div>
                    <div class="rounded-xl bg-[#fbfbfe] p-3 dark:bg-[#161f35]">
                        <p class="text-xs font-semibold uppercase tracking-[0.2em] text-brand-muted dark:text-slate-500">Waspang</p>
                        <p class="mt-3 text-lg font-semibold">{{ $project->waspang ?: '-' }}</p>
                    </div>
                    <div class="rounded-xl bg-[#fbfbfe] p-3 dark:bg-[#161f35]">
                        <p class="text-xs font-semibold uppercase tracking-[0.2em] text-brand-muted dark:text-slate-500">Start Project</p>
                        <p class="mt-3 text-lg font-semibold">{{ $project->start_project?->format('Y-m-d') ?: '-' }}</p>
                    </div>
                    <div class="rounded-xl bg-[#fbfbfe] p-3 dark:bg-[#161f35]">
                        <p class="text-xs font-semibold uppercase tracking-[0.2em] text-brand-muted dark:text-slate-500">End Project</p>
                        <p class="mt-3 text-lg font-semibold">{{ $project->end_project?->format('Y-m-d') ?: '-' }}</p>
                    </div>
                </div>
            @endif
        </section>




        <section class="rounded-xl border border-[#e6e7f0] bg-white p-3 shadow-soft dark:border-brand-darkLine dark:bg-brand-darkPanel">
            <div class="flex items-center justify-between gap-4">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.2em] text-brand-muted dark:text-slate-500">Detail BoQ</p>
                    <h2 class="mt-2 text-2xl font-semibold">BoQ Items</h2>
                </div>
                
                @if($isBatch)
                <div class="flex items-center gap-3">
                    <label for="boqSiteFilter" class="text-xs font-bold uppercase tracking-widest text-slate-500">Filter By Site</label>
                    <select id="boqSiteFilter" class="min-w-[200px] rounded-lg border border-[#e6e7f0] bg-[#fbfbfe] px-3 py-1.5 text-xs font-bold text-slate-700 shadow-sm focus:border-blue-500 focus:outline-none dark:border-brand-darkLine dark:bg-[#161f35] dark:text-white">
                        <option value="all">Semua (Gabungan)</option>
                        @foreach($project->projects as $site)
                            <option value="{{ $site->id }}">{{ $site->id }} - {{ $site->project_name }}</option>
                        @endforeach
                    </select>
                </div>
                @endif
            </div>



            <div class="mt-3 overflow-hidden rounded-xl border border-[#e6e7f0] dark:border-brand-darkLine">
                <div class="p-4">
                    <table id="boq-table" class="min-w-full text-sm">
                        <thead>
                            <tr>
                                <th class="px-3 py-3 text-left text-xs font-semibold uppercase tracking-[0.12em] text-brand-muted dark:text-slate-400">Designator</th>
                                <th class="px-3 py-3 text-left text-xs font-semibold uppercase tracking-[0.12em] text-brand-muted dark:text-slate-400">Description</th>
                                <th class="px-3 py-3 text-right text-xs font-semibold uppercase tracking-[0.12em] text-brand-muted dark:text-slate-400">Volume</th>
                                <th class="px-3 py-3 text-right text-xs font-semibold uppercase tracking-[0.12em] text-brand-muted dark:text-slate-400">Harga KHS</th>
                                <th class="px-3 py-3 text-right text-xs font-semibold uppercase tracking-[0.12em] text-brand-muted dark:text-slate-400">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($project->boqDetails as $item)
                                <tr class="whitespace-nowrap" data-site="all">
                                    <td>
                                        <span class="text-sm dark:text-white font-mono">{{ $item->designator }}</span>
                                    </td>
                                    <td>
                                        <span class="text-xs text-brand-muted dark:text-slate-400 capitalize">{{ strtolower($item->description) }}</span>
                                    </td>
                                    <td class="text-right">
                                        <span class="text-sm dark:text-white">{{ number_format((int) $item->volume_planning, 0, ',', '.') }}</span>
                                    </td>
                                    <td class="text-right">
                                        <span class="text-sm dark:text-white">Rp {{ number_format((float) $item->price_planning, 0, ',', '.') }}</span>
                                    </td>
                                    <td class="text-right font-bold">
                                        <span class="text-sm dark:text-white font-mono">Rp {{ number_format((float) ($item->volume_planning * $item->price_planning), 0, ',', '.') }}</span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td></td>
                                    <td class="py-6 text-center text-sm text-brand-muted dark:text-slate-400">Belum ada detail BoQ agregasi untuk project ini.</td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                </tr>
                            @endforelse

                            @if($isBatch)
                                <!-- SITE SPECIFIC ROWS -->
                                @foreach($project->projects as $site)
                                    @foreach($site->boqDetails as $item)
                                        <tr class="whitespace-nowrap" data-site="{{ $site->id }}">
                                            <td>
                                                <span class="text-sm dark:text-white font-mono">{{ $item->designator }}</span>
                                            </td>
                                            <td>
                                                <span class="text-xs text-brand-muted dark:text-slate-400 capitalize">{{ strtolower($item->description) }}</span>
                                            </td>
                                            <td class="text-right">
                                                <span class="text-sm dark:text-white">{{ number_format((int) $item->volume_planning, 0, ',', '.') }}</span>
                                            </td>
                                            <td class="text-right">
                                                <span class="text-sm dark:text-white">Rp {{ number_format((float) $item->price_planning, 0, ',', '.') }}</span>
                                            </td>
                                            <td class="text-right font-bold">
                                                <span class="text-sm dark:text-white font-mono">Rp {{ number_format((float) ($item->volume_planning * $item->price_planning), 0, ',', '.') }}</span>
                                            </td>
                                        </tr>
                                    @endforeach
                                @endforeach
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>

            @php
                $totalMaterial = $project->boqDetails
                    ->filter(fn($i) => str_starts_with(strtoupper($i->designator), 'M-'))
                    ->sum(fn($i) => $i->volume_planning * $i->price_planning);
                $totalJasa = $project->boqDetails
                    ->filter(fn($i) => str_starts_with(strtoupper($i->designator), 'J-'))
                    ->sum(fn($i) => $i->volume_planning * $i->price_planning);
                $grandTotal = $totalMaterial + $totalJasa;
            @endphp

            <div class="mt-3 grid grid-cols-1 gap-3 sm:grid-cols-3">
                <div class="flex items-center justify-between rounded-xl border border-blue-100 bg-blue-50/60 p-3 dark:border-blue-900/30 dark:bg-blue-900/10">
                    <div>
                        <p class="text-[10px] font-bold uppercase tracking-widest text-blue-500 dark:text-blue-400">Total Harga Material</p>
                        <p class="mt-1 text-lg font-bold text-blue-700 dark:text-blue-300">Rp {{ number_format($totalMaterial, 0, ',', '.') }}</p>
                    </div>
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-blue-100 text-blue-600 dark:bg-blue-900/30">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10"/></svg>
                    </div>
                </div>
                <div class="flex items-center justify-between rounded-xl border border-violet-100 bg-violet-50/60 p-3 dark:border-violet-900/30 dark:bg-violet-900/10">
                    <div>
                        <p class="text-[10px] font-bold uppercase tracking-widest text-violet-500 dark:text-violet-400">Total Harga Jasa</p>
                        <p class="mt-1 text-lg font-bold text-violet-700 dark:text-violet-300">Rp {{ number_format($totalJasa, 0, ',', '.') }}</p>
                    </div>
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-violet-100 text-violet-600 dark:bg-violet-900/30">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0"/></svg>
                    </div>
                </div>
                <div class="flex items-center justify-between rounded-xl border border-emerald-100 bg-emerald-50/60 p-3 dark:border-emerald-900/30 dark:bg-emerald-900/10">
                    <div>
                        <p class="text-[10px] font-bold uppercase tracking-widest text-emerald-500 dark:text-emerald-400">Grand Total</p>
                        <p class="mt-1 text-lg font-bold text-emerald-700 dark:text-emerald-300">Rp {{ number_format($grandTotal, 0, ',', '.') }}</p>
                    </div>
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-emerald-100 text-emerald-600 dark:bg-emerald-900/30">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                </div>
            </div>
        </section>

        <section class="rounded-xl border border-[#e6e7f0] bg-white p-3 shadow-soft dark:border-brand-darkLine dark:bg-brand-darkPanel">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.2em] text-brand-muted dark:text-slate-500">Evidence</p>
                <h2 class="mt-2 text-2xl font-semibold">Uploaded Files</h2>
            </div>

            <div class="mt-3 grid gap-3 md:grid-cols-2">
                <div class="space-y-3 rounded-xl border border-dashed border-[#d9dceb] p-3 dark:border-brand-darkLine">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <p class="text-sm font-semibold">{{ $isBatch ? 'Site Evidence (TGIDOP)' : 'Dasar Pekerjaan RAR' }}</p>
                            <p class="mt-1 text-xs text-brand-muted dark:text-slate-500">{{ $isBatch ? 'Kumpulan file dasar dari site penyusun.' : 'Paket dasar pekerjaan dipisah jadi info file dan update file.' }}</p>
                        </div>
                        <span class="rounded-full bg-[#f3f4f6] px-3 py-1 text-xs font-semibold text-brand-muted dark:bg-[#1b2438] dark:text-slate-400">{{ $isBatch ? 'Site Files' : 'RAR Package' }}</span>
                    </div>

                    <div class="grid gap-3">
                        <div class="rounded-xl bg-[#fbfbfe] p-3 dark:bg-[#161f35]">
                            <p class="text-xs font-semibold uppercase tracking-tight text-brand-muted dark:text-slate-500">Current Files</p>
                            @php $dasarFiles = $evidenceMap->get('dasar_pekerjaan', collect()); @endphp
                            @if ($dasarFiles->isNotEmpty())
                                <div class="mt-3 grid gap-3 sm:grid-cols-2">
                                    @foreach ($dasarFiles as $file)
                                        <div class="rounded-xl border border-[#e6e7f0] bg-white p-3 dark:border-brand-darkLine dark:bg-[#0f1728]">
                                            <p class="text-sm font-semibold truncate" title="{{ $file->file_name }}">{{ $file->file_name }}</p>
                                            <div class="mt-2 flex flex-wrap gap-x-3 gap-y-1 text-xs text-brand-muted dark:text-slate-500">
                                                <span>{{ strtoupper($file->file_extension ?? '-') }}</span>
                                                <span class="opacity-50">#{{ $file->project_id }}</span>
                                            </div>
                                            <div class="mt-3">
                                                <a href="{{ route('project-data.evidence-files.download', [$project, $file->id]) }}" target="_blank" class="inline-flex h-10 items-center rounded-xl border border-[#d9dceb] px-4 text-sm font-semibold text-brand-text transition hover:border-[#9ca3af] dark:border-brand-darkLine dark:text-white">
                                                    Lihat File
                                                </a>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <p class="mt-3 text-sm text-brand-muted dark:text-slate-400">Belum ada file dasar pekerjaan.</p>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="rounded-xl border border-dashed border-[#d9dceb] p-3 dark:border-brand-darkLine">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <p class="text-sm font-semibold">{{ $isBatch ? 'Site BoQ Originals (TGIDOP)' : 'BoQ Attachment' }}</p>
                            <p class="mt-1 text-xs text-brand-muted dark:text-slate-500">{{ $isBatch ? 'Kumpulan file BoQ original dari masing-masing site penyusun.' : 'File BoQ untuk project ini.' }}</p>
                        </div>
                        <span class="rounded-full bg-[#f3f4f6] px-3 py-1 text-xs font-semibold text-brand-muted dark:bg-[#1b2438] dark:text-slate-400">Excel/CSV</span>
                    </div>

                    <div class="mt-3 grid gap-3">
                        <div class="rounded-xl bg-[#fbfbfe] p-3 dark:bg-[#161f35]">
                            <p class="text-xs font-semibold uppercase tracking-tight text-brand-muted dark:text-slate-500">Current Files</p>
                            @if($isBatch)
                                @php $siteBoqs = $evidenceMap->get('boq', collect()); @endphp
                                @if($siteBoqs->isNotEmpty())
                                    <div class="mt-3 grid gap-3 sm:grid-cols-2">
                                        @foreach($siteBoqs as $sb)
                                            <div class="rounded-xl border border-[#e6e7f0] bg-white p-3 dark:border-brand-darkLine dark:bg-[#0f1728]">
                                                <p class="text-sm font-semibold truncate" title="{{ $sb->file_name }}">{{ $sb->file_name }}</p>
                                                <div class="mt-2 flex flex-wrap gap-x-3 gap-y-1 text-xs text-brand-muted dark:text-slate-400">
                                                    <span>{{ strtoupper($sb->file_extension ?? '-') }}</span>
                                                    <span class="opacity-60">#{{ $sb->project_id ?? ($sb->project_batch_id ?? 'BATCH') }}</span>
                                                </div>
                                                <div class="mt-3">
                                                    <a href="{{ route('project-data.evidence-files.download', [$project, $sb->id]) }}" target="_blank" class="text-xs font-bold text-brand-blue dark:text-blue-400 hover:underline">Lihat</a>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                @else
                                    <p class="mt-3 text-sm text-brand-muted dark:text-slate-400 text-center py-4">Belum ada file BoQ site.</p>
                                @endif
                            @else
                                @php $boqFile = $evidenceMap->get('boq')?->first(); @endphp
                                @if ($boqFile)
                                    <div class="mt-3 rounded-xl border border-[#e6e7f0] bg-white p-3 dark:border-brand-darkLine dark:bg-[#0f1728]">
                                        <p class="text-sm font-semibold truncate">{{ $boqFile->file_name }}</p>
                                        <div class="mt-3">
                                            <a href="{{ route('project-data.evidence-files.download', [$project, $boqFile->id]) }}" target="_blank" class="text-xs font-bold text-brand-blue dark:text-blue-400 hover:underline">Lihat BoQ</a>
                                        </div>
                                    </div>
                                @else
                                    <p class="mt-3 text-sm text-brand-muted dark:text-slate-400">Belum ada file BoQ.</p>
                                @endif
                            @endif
                        </div>
                    </div>

                    @if(in_array(Auth::user()->role, ['admin', 'commerce']))
                        <div class="mt-6 pt-4 border-t border-dashed border-[#d9dceb] dark:border-brand-darkLine">
                            <form id="boq-evidence-form" action="{{ route('project-data.evidences.store', $project) }}" 
                                  method="POST" 
                                  enctype="multipart/form-data" 
                                  class="space-y-3">
                                @csrf
                                <input type="hidden" name="type" value="boq">
                                <input name="evidence_file" type="file" accept=".xls,.xlsx,.csv" class="block w-full rounded-xl border border-[#d9dceb] bg-white px-3 py-2 text-xs file:mr-4 file:rounded-lg file:border-0 file:bg-[#f3f4f6] file:px-3 file:py-2 file:text-xs file:font-medium dark:border-brand-darkLine dark:bg-[#0f1728] dark:text-white dark:file:bg-[#1c2540]">
                                <button type="submit" class="w-full rounded-xl bg-slate-800 py-2.5 text-xs font-bold text-white p-3 shadow-soft transition hover:bg-slate-700 dark:bg-brand-blue dark:hover:bg-brand-blue/90">
                                    {{ $isBatch ? 'Update Consolidated BoQ' : 'Save/Re-upload BoQ' }}
                                </button>
                            </form>
                        </div>
                    @endif
                </div>
            </div>
        </section>
    </div>

@endsection

@push('scripts')
<script type="module">
    $(document).ready(function() {
        $.fn.dataTable.ext.search.push(
            function(settings, data, dataIndex) {
                if (settings.nTable.id !== 'boq-table') return true;
                
                var siteFilter = $('#boqSiteFilter').val();
                if (!siteFilter || siteFilter === 'all') {
                    return $(settings.aoData[dataIndex].nTr).attr('data-site') === 'all';
                }
                
                return $(settings.aoData[dataIndex].nTr).attr('data-site') === siteFilter;
            }
        );

        var table = $('#boq-table').DataTable({
            scrollX: true,
            autoWidth: false,
            ordering: false,
            destroy: true
        });

        $('#boqSiteFilter').on('change', function() {
            table.draw();
        });

        // Evidence Upload Progress Tracker
        const boqForm = document.getElementById('boq-evidence-form');
        if (boqForm) {
            boqForm.addEventListener('submit', function(e) {
                e.preventDefault();
                const formData = new FormData(this);
                const fileInput = this.querySelector('input[name="evidence_file"]');
                const fileName = fileInput.files[0] ? fileInput.files[0].name : "BoQ File";

                const xhr = new XMLHttpRequest();
                xhr.open('POST', this.getAttribute('action'), true);
                xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');

                if (window.trackUpload) {
                    window.trackUpload(xhr, fileName);
                }

                xhr.onload = function() {
                    if (xhr.status >= 200 && xhr.status < 300) {
                        if (window.showToast) {
                            window.showToast('File BoQ telah diperbarui.', 'success');
                        }
                        setTimeout(() => {
                            window.location.reload();
                        }, 1000);
                    } else {
                        let errorMsg = 'Gagal mengunggah file';
                        try {
                            const res = JSON.parse(xhr.responseText);
                            errorMsg = res.message || errorMsg;
                        } catch(err) {}
                        if (window.showToast) {
                            window.showToast(errorMsg, 'error');
                        }
                    }
                };

                xhr.send(formData);
            });
        }
    });
</script>
@endpush
