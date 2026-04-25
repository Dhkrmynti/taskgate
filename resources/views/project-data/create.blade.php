@extends('layouts.dashboard')

@php
    $isCommercePage = request()->routeIs('commerce');
@endphp

@section('title', $isCommercePage ? 'Taskgate Commerce' : 'Taskgate Create Project')
@section('date_label', $isCommercePage ? 'Commerce' : 'Project Data')
@section('hello_name', $isCommercePage ? 'Commerce' : 'Create Project')
@section('hero_subtitle', $isCommercePage
    ? 'Form tambah project ditempatkan di menu Commerce. Upload BoQ di sini menjadi baseline planning item per designator.'
    : 'Lengkapi data project utama terlebih dahulu. Detail BoQ akan mengikuti baseline planning dari upload Commerce.')

@section('hero_actions')
    <a href="{{ route('project-data.index') }}" class="rounded-full border border-[#d9dceb] bg-white px-5 py-2.5 text-sm font-semibold text-brand-text transition hover:border-[#9ca3af] dark:border-brand-darkLine dark:bg-brand-darkPanel dark:text-white">
        Back to List
    </a>
@endsection

@section('content')
    <form action="{{ route('project-data.store') }}" method="POST" enctype="multipart/form-data" class="space-y-3 pb-20">
        @csrf

        <!-- Main Form Grid -->
        <div class="grid gap-3 lg:grid-cols-12">
            
            <!-- Island 1: Project Identity -->
            <div class="lg:col-span-4 space-y-3">
                <section class="rounded-xl border border-[#e6e7f0] bg-white p-3 shadow-soft dark:border-brand-darkLine dark:bg-brand-darkPanel h-full">
                    <div class="mb-5 flex items-center gap-3">
                        <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-blue-50 text-blue-600 dark:bg-blue-900/20 dark:text-blue-400">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><line x1="10" y1="9" x2="8" y2="9"/></svg>
                        </div>
                        <h2 class="text-lg font-bold tracking-tight text-brand-text dark:text-white">Project Identity</h2>
                    </div>

                    <div class="space-y-4">
                        <div>
                            <label for="pid_input_mode" class="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-brand-muted dark:text-slate-400">Input PID</label>
                            <select id="pid_input_mode" name="pid_input_mode" class="h-11 w-full rounded-xl border border-[#d9dceb] bg-white px-4 text-sm outline-none transition focus:border-brand-text focus:ring-1 focus:ring-brand-text dark:border-brand-darkLine dark:bg-[#0f1728] dark:text-white">
                                <option value="auto" @selected(old('pid_input_mode', 'auto') === 'auto')>Auto (Finance)</option>
                                <option value="manual" @selected(old('pid_input_mode') === 'manual')>Manual</option>
                            </select>
                        </div>

                        <div>
                            <label for="project_name" class="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-brand-muted dark:text-slate-400">Project Name</label>
                            <input id="project_name" name="project_name" type="text" value="{{ old('project_name') }}" placeholder="Enter project name..." class="h-11 w-full rounded-xl border border-[#d9dceb] bg-white px-4 text-sm outline-none transition focus:border-brand-text focus:ring-1 focus:ring-brand-text dark:border-brand-darkLine dark:bg-[#0f1728] dark:text-white">
                        </div>

                        <div class="relative">
                            <label for="pid" class="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-brand-muted dark:text-slate-400">PID</label>
                            <input id="pid" name="pid" type="text" value="{{ old('pid') }}" autocomplete="off" placeholder="Cari dari Finance / isi manual..." class="h-11 w-full rounded-xl border border-[#d9dceb] bg-white px-4 text-sm outline-none transition focus:border-brand-text focus:ring-1 focus:ring-brand-text dark:border-brand-darkLine dark:bg-[#0f1728] dark:text-white font-mono">
                            <div id="pid_suggestions" class="absolute z-20 mt-1 hidden max-h-60 w-full overflow-y-auto rounded-xl border border-[#d9dceb] bg-white py-2 shadow-lg dark:border-brand-darkLine dark:bg-[#0f1728]">
                                <!-- Suggestions injected here -->
                            </div>
                            <p class="mt-1 text-[10px] text-brand-muted">ID Taskgate akan dibuat otomatis (Contoh: TGID-20260418-0001).</p>
                        </div>

                        <div>
                            <label for="customer" class="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-brand-muted dark:text-slate-400">Customer</label>
                            <select id="customer" name="customer" data-searchable-select class="h-11 w-full rounded-xl border border-[#d9dceb] bg-white px-4 text-sm outline-none dark:border-brand-darkLine dark:bg-[#0f1728] dark:text-white">
                                <option value="">Select Customer</option>
                                @foreach ($options['customers'] as $option)
                                    <option value="{{ $option }}" @selected(old('customer') === $option)>{{ $option }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </section>
            </div>

            <!-- Island 2: Categorization -->
            <div class="lg:col-span-4 space-y-3">
                <section class="rounded-xl border border-[#e6e7f0] bg-white p-3 shadow-soft dark:border-brand-darkLine dark:bg-brand-darkPanel h-full">
                    <div class="mb-5 flex items-center gap-3">
                        <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-orange-50 text-orange-600 dark:bg-orange-900/20 dark:text-orange-400">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 20h16a2 2 0 0 0 2-2V8a2 2 0 0 0-2-2h-7.93a2 2 0 0 1-1.66-.9l-.82-1.2A2 2 0 0 0 7.93 3H4a2 2 0 0 0-2 2v13a2 2 0 0 0 2 2Z"/><path d="M2 10h20"/></svg>
                        </div>
                        <h2 class="text-lg font-bold tracking-tight text-brand-text dark:text-white">Categorization</h2>
                    </div>

                    <div class="space-y-4">
                        <div>
                            <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-brand-muted dark:text-slate-400">Initial Phase</label>
                            <input type="hidden" name="fase" value="start">
                            <div class="flex h-11 items-center rounded-xl border border-[#d9dceb] bg-[#f8fafc] px-4 text-sm font-bold text-brand-text dark:border-brand-darkLine dark:bg-[#161f35] dark:text-white">
                                <span class="flex h-2 w-2 mr-3 rounded-full bg-blue-500"></span>
                                START
                            </div>
                        </div>

                        <div>
                            <label for="portofolio" class="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-brand-muted dark:text-slate-400">Portofolio</label>
                            <select id="portofolio" name="portofolio" data-searchable-select class="h-11 w-full rounded-xl border border-[#d9dceb] bg-white px-4 text-sm outline-none dark:border-brand-darkLine dark:bg-[#0f1728] dark:text-white">
                                <option value="">Select Portofolio</option>
                                @foreach ($options['portofolios'] as $option)
                                    <option value="{{ $option }}" @selected(old('portofolio') === $option)>{{ $option }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label for="program" class="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-brand-muted dark:text-slate-400">Program</label>
                            <select id="program" name="program" data-searchable-select class="h-11 w-full rounded-xl border border-[#d9dceb] bg-white px-4 text-sm outline-none dark:border-brand-darkLine dark:bg-[#0f1728] dark:text-white">
                                <option value="">Select Program</option>
                                @foreach ($options['programs'] as $option)
                                    <option value="{{ $option }}" @selected(old('program') === $option)>{{ $option }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label for="jenis_eksekusi" class="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-brand-muted dark:text-slate-400">Jenis Eksekusi</label>
                            <select id="jenis_eksekusi" name="jenis_eksekusi" data-searchable-select class="h-11 w-full rounded-xl border border-[#d9dceb] bg-white px-4 text-sm outline-none dark:border-brand-darkLine dark:bg-[#0f1728] dark:text-white">
                                <option value="">Select Execution Type</option>
                                @foreach ($options['executionTypes'] as $option)
                                    <option value="{{ $option }}" @selected(old('jenis_eksekusi') === $option)>{{ $option }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </section>
            </div>

            <!-- Island 3: Management & Timeline -->
            <div class="lg:col-span-4 space-y-3">
                <section class="rounded-xl border border-[#e6e7f0] bg-white p-3 shadow-soft dark:border-brand-darkLine dark:bg-brand-darkPanel h-full">
                    <div class="mb-5 flex items-center gap-3">
                        <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-emerald-50 text-emerald-600 dark:bg-emerald-900/20 dark:text-emerald-400">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                        </div>
                        <h2 class="text-lg font-bold tracking-tight text-brand-text dark:text-white">Project Management</h2>
                    </div>

                    <div class="space-y-4">
                        <div>
                            <label for="branch" class="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-brand-muted dark:text-slate-400">Branch</label>
                            <select id="branch" name="branch" data-searchable-select class="h-11 w-full rounded-xl border border-[#d9dceb] bg-white px-4 text-sm outline-none dark:border-brand-darkLine dark:bg-[#0f1728] dark:text-white">
                                <option value="">Pilih branch</option>
                                @foreach ($options['branches'] as $option)
                                    <option value="{{ $option }}" @selected(old('branch') === $option)>{{ $option }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label for="pm_project" class="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-brand-muted dark:text-slate-400">PM Project</label>
                            <select id="pm_project" name="pm_project" data-searchable-select class="h-11 w-full rounded-xl border border-[#d9dceb] bg-white px-4 text-sm outline-none dark:border-brand-darkLine dark:bg-[#0f1728] dark:text-white">
                                <option value="">Select Project Manager</option>
                                @foreach ($options['pmProjects'] as $option)
                                    <option value="{{ $option }}" @selected(old('pm_project') === $option)>{{ $option }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label for="waspang" class="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-brand-muted dark:text-slate-400">Waspang</label>
                            <select id="waspang" name="waspang" data-searchable-select class="h-11 w-full rounded-xl border border-[#d9dceb] bg-white px-4 text-sm outline-none dark:border-brand-darkLine dark:bg-[#0f1728] dark:text-white">
                                <option value="">Select Waspang</option>
                                @foreach ($options['waspangs'] as $option)
                                    <option value="{{ $option }}" @selected(old('waspang') === $option)>{{ $option }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label for="start_project" class="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-brand-muted dark:text-slate-400">Start Project</label>
                                <input id="start_project" name="start_project" type="date" value="{{ old('start_project') }}" class="h-11 w-full rounded-xl border border-[#d9dceb] bg-white px-4 text-sm outline-none dark:border-brand-darkLine dark:bg-[#0f1728] dark:text-white">
                            </div>
                            <div>
                                <label for="end_project" class="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-brand-muted dark:text-slate-400">End Project</label>
                                <input id="end_project" name="end_project" type="date" value="{{ old('end_project') }}" class="h-11 w-full rounded-xl border border-[#d9dceb] bg-white px-4 text-sm outline-none dark:border-brand-darkLine dark:bg-[#0f1728] dark:text-white">
                            </div>
                        </div>
                    </div>
                </section>
            </div>

            @if($isCommercePage)
            <!-- Financial Detail & Performance Dashboard -->
            <div class="lg:col-span-12">
                <div class="grid grid-cols-1 gap-3 lg:grid-cols-12 mb-0">
                    <!-- Left: Detail Financial Table -->
                    <div class="lg:col-span-8 rounded-xl border border-[#e6e7f0] bg-white p-4 shadow-soft dark:border-brand-darkLine dark:bg-brand-darkPanel">
                        <div class="flex items-center justify-between mb-3 border-b border-slate-100 pb-2 dark:border-brand-darkLine">
                            <h3 class="text-[10px] font-bold uppercase tracking-[0.2em] text-blue-600">Detail Financial</h3>
                            <div class="flex items-center gap-2">
                                <select class="rounded-lg border border-slate-200 bg-white px-2 py-1 text-[10px] font-bold text-slate-700 outline-none transition focus:border-blue-500 dark:border-brand-darkLine dark:bg-[#161f35] dark:text-white">
                                    <option>25KT03R211-0002</option>
                                    <option>25KT03R211-0003</option>
                                </select>
                                <span class="text-[10px] font-bold text-slate-500 dark:text-slate-400">MD_KAMPUNG SELANG</span>
                            </div>
                        </div>
                        
                        <div class="overflow-x-auto">
                            <table class="w-full text-left text-[11px]">
                                <thead>
                                    <tr class="bg-slate-800 text-white dark:bg-slate-900">
                                        <th class="p-2 font-bold uppercase tracking-wider rounded-tl-lg">Detail Financial</th>
                                        <th class="p-2 text-right font-bold uppercase tracking-wider">Release</th>
                                        <th class="p-2 text-right font-bold uppercase tracking-wider">Actual</th>
                                        <th class="p-2 text-right font-bold uppercase tracking-wider rounded-tr-lg">Available</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100 dark:divide-brand-darkLine text-slate-700 dark:text-slate-300">
                                    <tr class="bg-blue-50/50 font-bold dark:bg-blue-900/10">
                                        <td class="p-2 text-blue-700 dark:text-blue-400">Budgeting</td>
                                        <td id="fin-budgeting-release" class="p-2 text-right">0</td>
                                        <td id="fin-budgeting-actual" class="p-2 text-right">0</td>
                                        <td id="fin-budgeting-available" class="p-2 text-right text-emerald-600">0</td>
                                    </tr>
                                    
                                    <tr class="bg-slate-50/30 dark:bg-slate-800/10">
                                        <td class="p-2 font-bold text-slate-800 dark:text-white">Material</td>
                                        <td id="fin-material-total-release" class="p-2 text-right font-bold">0</td>
                                        <td id="fin-material-total-actual" class="p-2 text-right font-bold">0</td>
                                        <td id="fin-material-total-available" class="p-2 text-right font-bold text-emerald-600">0</td>
                                    </tr>
                                    <tr>
                                        <td class="p-2 pl-4 text-slate-500">Material Non Stok</td>
                                        <td id="fin-material-non_stok-release" class="p-2 text-right">0</td>
                                        <td id="fin-material-non_stok-actual" class="p-2 text-right">0</td>
                                        <td id="fin-material-non_stok-available" class="p-2 text-right">0</td>
                                    </tr>
                                    <tr>
                                        <td class="p-2 pl-4 text-slate-500">Material Stok</td>
                                        <td id="fin-material-stok-release" class="p-2 text-right">0</td>
                                        <td id="fin-material-stok-actual" class="p-2 text-right">0</td>
                                        <td id="fin-material-stok-available" class="p-2 text-right">0</td>
                                    </tr>
                                    
                                    <tr class="bg-slate-50/30 dark:bg-slate-800/10">
                                        <td class="p-2 font-bold text-slate-800 dark:text-white">Non Material</td>
                                        <td id="fin-non_material-total-release" class="p-2 text-right font-bold">0</td>
                                        <td id="fin-non_material-total-actual" class="p-2 text-right font-bold">0</td>
                                        <td id="fin-non_material-total-available" class="p-2 text-right font-bold text-emerald-600">0</td>
                                    </tr>
                                    <tr>
                                        <td class="p-2 pl-4 text-slate-500">Jasa</td>
                                        <td id="fin-non_material-jasa-release" class="p-2 text-right">0</td>
                                        <td id="fin-non_material-jasa-actual" class="p-2 text-right">0</td>
                                        <td id="fin-non_material-jasa-available" class="p-2 text-right">0</td>
                                    </tr>
                                    <tr>
                                        <td class="p-2 pl-4 text-slate-500">Depresiasi</td>
                                        <td id="fin-non_material-depresiasi-release" class="p-2 text-right">0</td>
                                        <td id="fin-non_material-depresiasi-actual" class="p-2 text-right">0</td>
                                        <td id="fin-non_material-depresiasi-available" class="p-2 text-right">0</td>
                                    </tr>
                                    <tr>
                                        <td class="p-2 pl-4 text-slate-500">Operasional</td>
                                        <td id="fin-non_material-operasional-release" class="p-2 text-right">0</td>
                                        <td id="fin-non_material-operasional-actual" class="p-2 text-right">0</td>
                                        <td id="fin-non_material-operasional-available" class="p-2 text-right">0</td>
                                    </tr>
                                    <tr>
                                        <td class="p-2 pl-4 text-slate-500">Sewa</td>
                                        <td id="fin-non_material-sewa-release" class="p-2 text-right">0</td>
                                        <td id="fin-non_material-sewa-actual" class="p-2 text-right">0</td>
                                        <td id="fin-non_material-sewa-available" class="p-2 text-right">0</td>
                                    </tr>
                                    <tr>
                                        <td class="p-2 pl-4 text-slate-500">Tenaga Kerja</td>
                                        <td id="fin-non_material-tenaga_kerja-release" class="p-2 text-right">0</td>
                                        <td id="fin-non_material-tenaga_kerja-actual" class="p-2 text-right">0</td>
                                        <td id="fin-non_material-tenaga_kerja-available" class="p-2 text-right">0</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Right: Performance Overlays -->
                    <div class="lg:col-span-4 flex flex-col gap-3">
                        <div class="flex-1 rounded-xl border border-[#e6e7f0] bg-white p-4 shadow-soft dark:border-brand-darkLine dark:bg-brand-darkPanel">
                            <table class="w-full text-left text-xs">
                                <thead>
                                    <tr class="bg-slate-800 text-white dark:bg-slate-900">
                                        <th class="p-2 font-bold uppercase tracking-wider rounded-tl-lg">Revenue</th>
                                        <th class="p-2 text-center font-bold uppercase tracking-wider"></th>
                                        <th class="p-2 text-right font-bold uppercase tracking-wider rounded-tr-lg">Gpm</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr class="bg-white dark:bg-brand-darkPanel">
                                        <td id="perf-revenue" class="p-3 text-lg font-black text-slate-800 dark:text-white">0</td>
                                        <td class="p-3 text-center">
                                            <div class="flex h-8 w-8 items-center justify-center rounded-full bg-blue-50 text-blue-600 dark:bg-blue-900/30 dark:text-blue-400">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                                                </svg>
                                            </div>
                                        </td>
                                        <td id="perf-gpm" class="p-3 text-right text-lg font-black text-emerald-600">0</td>
                                    </tr>
                                    <tr>
                                        <td colspan="3" class="p-4 text-center border-t border-slate-50 dark:border-brand-darkLine">
                                            <p class="text-[9px] font-bold uppercase tracking-[0.3em] text-slate-400 mb-1">Profitability Ratio</p>
                                            <div id="perf-gpm-percent" class="text-3xl font-black text-blue-600 drop-shadow-sm">0%</div>
                                            <p id="perf-status-label" class="mt-1 text-[9px] font-bold text-emerald-500 uppercase tracking-widest">Highly Optimized GPM</p>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <div class="flex h-24 items-center justify-center rounded-xl border-2 border-dashed border-slate-200 bg-slate-50/50 p-4 dark:border-brand-darkLine dark:bg-brand-darkPanel/50">
                            <div class="text-center">
                                <span class="text-2xl">👍</span>
                                <p class="mt-1 text-[9px] font-bold uppercase tracking-widest text-slate-400">System Healthy</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @endif

            <!-- Island 4: Evidence & Files (Full Width) -->
            <div class="lg:col-span-12">
                <section class="rounded-xl border border-[#e6e7f0] bg-white p-3 shadow-soft dark:border-brand-darkLine dark:bg-brand-darkPanel">
                    <div class="mb-5 flex items-center gap-3">
                        <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-purple-50 text-purple-600 dark:bg-purple-900/20 dark:text-purple-400">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
                        </div>
                        <h2 class="text-lg font-bold tracking-tight text-brand-text dark:text-white">Evidence & BoQ Baseline</h2>
                    </div>

                    <div class="grid gap-3 lg:grid-cols-2">
                        <div class="flex flex-col">
                            <div class="mb-3 flex h-9 items-center">
                                <label for="evidence_dasar_files" class="text-sm font-semibold text-brand-text dark:text-white">Evidence Dasar Pekerjaan</label>
                            </div>
                            <p class="mb-4 text-xs text-brand-muted dark:text-slate-500 h-8">Upload MoM, NDE, ASP/KML. Maksimal 3 file sekaligus.</p>
                            <input id="evidence_dasar_files" name="evidence_dasar_files[]" type="file" multiple accept=".pdf,.xls,.xlsx,.kml,.kmz" class="block w-full rounded-xl border border-dashed border-[#d9dceb] bg-[#f8fafc] px-4 py-8 text-center text-sm file:hidden dark:border-brand-darkLine dark:bg-[#0f1728] dark:text-white">
                            <p class="mt-2 text-[10px] text-brand-muted">Allowed formats: PDF, ASP, Excel, KML, KMZ</p>
                        </div>

                        <div class="flex flex-col">
                            <div class="mb-3 flex h-9 items-center justify-between gap-3">
                                <label for="boq" class="text-sm font-semibold text-brand-text dark:text-white">Baseline BoQ (KHS)</label>
                                <a href="{{ route('commerce.template-planning') }}" class="inline-flex h-8 items-center rounded-lg border border-[#d9dceb] px-3 text-[11px] font-bold text-brand-text transition hover:border-[#9ca3af] dark:border-brand-darkLine dark:text-white">
                                    Download Template
                                </a>
                            </div>
                            <p class="mb-4 text-xs text-brand-muted dark:text-slate-500 h-8">Upload baseline BoQ untuk divalidasi harganya dengan data KHS.</p>
                            <input id="boq" name="boq" type="file" accept=".xls,.xlsx" class="block w-full rounded-xl border border-dashed border-[#d9dceb] bg-[#f8fafc] px-4 py-8 text-center text-sm file:hidden dark:border-brand-darkLine dark:bg-[#0f1728] dark:text-white">
                            <p class="mt-2 text-[10px] text-brand-muted">Requirement: Format Excel sesuai template.</p>
                        </div>
                    </div>

                    <!-- BoQ Preview Table Section -->
                    <div id="boq_preview_container" class="mt-3 hidden border-t border-[#f1f2f6] pt-3 dark:border-brand-darkLine">
                        <div class="mb-4 flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <h3 class="font-bold text-brand-text dark:text-white">Preview BoQ Items</h3>
                                <div id="boq_summary_badge" class="flex gap-2"></div>
                            </div>
                        </div>
                        <div class="overflow-hidden rounded-xl border border-[#e6e7f0] dark:border-brand-darkLine">
                            <div class="max-h-[500px] overflow-y-auto">
                                <table class="w-full text-left text-xs border-collapse">
                                    <thead class="sticky top-0 z-10 bg-[#f8fafc] dark:bg-[#161f35]">
                                        <tr>
                                            <th class="px-4 py-3 text-left text-[10px] font-bold uppercase tracking-[0.2em] text-brand-muted dark:text-slate-500">Item Details</th>
                                            <th class="px-4 py-3 text-center text-[10px] font-bold uppercase tracking-[0.2em] text-brand-muted dark:text-slate-500">Project Plan</th>
                                            <th class="px-4 py-3 text-center text-[10px] font-bold uppercase tracking-[0.2em] text-brand-muted dark:text-slate-500">Unit</th>
                                            <th class="px-4 py-3 text-right text-[10px] font-bold uppercase tracking-[0.2em] text-brand-muted dark:text-slate-500">Price</th>
                                            <th class="px-4 py-3 text-center text-[10px] font-bold uppercase tracking-[0.2em] text-brand-muted dark:text-slate-500">Total</th>
                                            <th class="px-4 py-3 text-center text-[10px] font-bold uppercase tracking-[0.2em] text-brand-muted dark:text-slate-500">Status</th>
                                        </tr>
                                    </thead>
                                    <tbody id="boq_preview_body" class="divide-y divide-[#f1f2f6] dark:divide-brand-darkLine">
                                        <!-- Items injected here -->
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <input type="hidden" id="boq_items_json" name="boq_items_json">
                    </div>
                </section>
            </div>
        </div>

        <!-- Sticky Action Bar -->
        <div class="fixed bottom-6 left-1/2 -translate-x-1/2 lg:left-[calc(50%+136px)] z-30">
            <div class="flex items-center rounded-full bg-brand-text/90 p-1.5 backdrop-blur-md shadow-float dark:bg-white/95">
                <button type="submit" class="rounded-full bg-white px-12 py-3 text-sm font-bold text-brand-text transition hover:bg-[#f8fafc] dark:bg-brand-darkBg dark:text-white dark:hover:bg-[#2f3542]">
                    Create Final Project
                </button>
            </div>
        </div>
    </form>
@endsection

@push('scripts')
<script type="module">
    document.addEventListener('DOMContentLoaded', function () {
        const pidModeSelect = document.getElementById('pid_input_mode');
        const pidInput = document.getElementById('pid');
        const pidSuggestions = document.getElementById('pid_suggestions');
        let debounceTimer;
        let currentFocus = -1;
        let lastQuery = '';

        if (pidModeSelect && pidInput && pidSuggestions) {
            function isAutoMode() {
                return pidModeSelect.value === 'auto';
            }

            function hideSuggestions() {
                pidSuggestions.classList.add('hidden');
                currentFocus = -1;
            }

            function applyPidMode() {
                if (isAutoMode()) {
                    pidInput.placeholder = 'Ketik minimal 4 karakter untuk cari PID dari Finance...';
                } else {
                    pidInput.placeholder = 'Isi PID manual...';
                    hideSuggestions();
                }
            }

            function removeActive(items) {
                for (let i = 0; i < items.length; i++) {
                    items[i].classList.remove('bg-[#f3f4f6]', 'dark:bg-[#1f2937]', 'text-brand-text', 'dark:text-white');
                }
            }

            function addActive(items) {
                if (!items || items.length === 0) return;
                removeActive(items);

                if (currentFocus >= items.length) currentFocus = 0;
                if (currentFocus < 0) currentFocus = (items.length - 1);

                const activeItem = items[currentFocus];
                activeItem.classList.add('bg-[#f3f4f6]', 'dark:bg-[#1f2937]', 'text-brand-text', 'dark:text-white');

                if (activeItem.scrollIntoViewIfNeeded) {
                    activeItem.scrollIntoViewIfNeeded(false);
                } else {
                    activeItem.scrollIntoView({ block: 'nearest' });
                }
            }

            function loadSuggestions(query) {
                if (!isAutoMode()) {
                    hideSuggestions();
                    return;
                }

                fetch(`{{ route('finance.pid-suggestions') }}?q=${encodeURIComponent(query)}`, {
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (!isAutoMode()) {
                        hideSuggestions();
                        return;
                    }

                    pidSuggestions.innerHTML = '';
                    if (data.length > 0) {
                        data.forEach((item, index) => {
                            const div = document.createElement('div');
                            div.className = 'suggestion-item cursor-pointer px-4 py-2.5 text-sm text-brand-text dark:text-slate-300 transition-colors hover:bg-[#f8fafc] dark:hover:bg-[#161f35]';

                            const pidSpan = document.createElement('div');
                            pidSpan.className = 'font-bold';
                            pidSpan.textContent = item.project_id;

                            const descSpan = document.createElement('div');
                            descSpan.className = 'text-xs text-brand-muted dark:text-slate-500 truncate';
                            descSpan.textContent = item.description || '-';

                            div.appendChild(pidSpan);
                            div.appendChild(descSpan);

                            div.addEventListener('click', () => {
                                pidInput.value = item.project_id;
                                hideSuggestions();
                                // Trigger dynamic financial load
                                if (window.loadFinancialData) {
                                    window.loadFinancialData(item.project_id);
                                }
                            });

                            div.addEventListener('mouseenter', () => {
                                removeActive(pidSuggestions.querySelectorAll('.suggestion-item'));
                                currentFocus = index;
                                addActive(pidSuggestions.querySelectorAll('.suggestion-item'));
                            });

                            pidSuggestions.appendChild(div);
                        });

                        pidSuggestions.classList.remove('hidden');
                    } else {
                        hideSuggestions();
                    }
                })
                .catch(() => {
                    hideSuggestions();
                });
            }

            pidModeSelect.addEventListener('change', applyPidMode);

            pidInput.addEventListener('input', function () {
                clearTimeout(debounceTimer);
                const query = this.value.trim();
                currentFocus = -1;

                if (!isAutoMode() || query.length < 4) {
                    hideSuggestions();
                    lastQuery = '';
                    return;
                }

                lastQuery = query;
                debounceTimer = setTimeout(() => {
                    loadSuggestions(query);
                }, 300);
            });

            pidInput.addEventListener('focus', function () {
                const query = this.value.trim();
                if (!isAutoMode()) {
                    hideSuggestions();
                    return;
                }

                if (query.length >= 4) {
                    if (query === lastQuery && pidSuggestions.innerHTML !== '') {
                        pidSuggestions.classList.remove('hidden');
                    } else {
                        lastQuery = query;
                        loadSuggestions(query);
                    }
                }
            });

            pidInput.addEventListener('keydown', function(e) {
                const items = pidSuggestions.querySelectorAll('.suggestion-item');

                if (!isAutoMode() || pidSuggestions.classList.contains('hidden') || items.length === 0) {
                    return;
                }

                if (e.key === 'ArrowDown') {
                    e.preventDefault();
                    currentFocus++;
                    addActive(items);
                } else if (e.key === 'ArrowUp') {
                    e.preventDefault();
                    currentFocus--;
                    addActive(items);
                } else if (e.key === 'Enter') {
                    e.preventDefault();
                    if (currentFocus > -1 && items[currentFocus]) {
                        items[currentFocus].click();
                    }
                } else if (e.key === 'Escape') {
                    hideSuggestions();
                }
            });

            document.addEventListener('click', function (e) {
                if (!pidInput.contains(e.target) && !pidSuggestions.contains(e.target)) {
                    hideSuggestions();
                }
            });

            applyPidMode();
        }

        // --- BoQ Verification & Preview Logic ---
        const boqInput = document.getElementById('boq');
        const previewContainer = document.getElementById('boq_preview_container');
        const previewBody = document.getElementById('boq_preview_body');
        const summaryBadge = document.getElementById('boq_summary_badge');
        const boqJsonInput = document.getElementById('boq_items_json');
        let boqItems = [];

        // --- Financial Dashboard AJAX Logic ---
        const formatIDR = (num) => new Intl.NumberFormat('id-ID').format(Math.round(num));

        window.loadFinancialData = function(pid) {
            if (!pid || pid.length < 4) return;

            // Optional: Start loading state
            document.querySelectorAll('[id^="fin-"], #perf-revenue, #perf-gpm').forEach(el => el.classList.add('opacity-50'));

            fetch(`/project-data/pid/${encodeURIComponent(pid)}/financials`, {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.json())
            .then(data => {
                // Update Budgeting
                document.getElementById('fin-budgeting-release').textContent = formatIDR(data.budgeting.release);
                document.getElementById('fin-budgeting-actual').textContent = formatIDR(data.budgeting.actual);
                document.getElementById('fin-budgeting-available').textContent = formatIDR(data.budgeting.available);

                // Update Material
                document.getElementById('fin-material-total-release').textContent = formatIDR(data.material.total.release);
                document.getElementById('fin-material-total-actual').textContent = formatIDR(data.material.total.actual);
                document.getElementById('fin-material-total-available').textContent = formatIDR(data.material.total.available);
                
                document.getElementById('fin-material-stok-release').textContent = formatIDR(data.material.stok.release);
                document.getElementById('fin-material-stok-actual').textContent = formatIDR(data.material.stok.actual);
                document.getElementById('fin-material-stok-available').textContent = formatIDR(data.material.stok.available);
                
                document.getElementById('fin-material-non_stok-release').textContent = formatIDR(data.material.non_stok.release);
                document.getElementById('fin-material-non_stok-actual').textContent = formatIDR(data.material.non_stok.actual);
                document.getElementById('fin-material-non_stok-available').textContent = formatIDR(data.material.non_stok.available);

                // Update Non-Material
                document.getElementById('fin-non_material-total-release').textContent = formatIDR(data.non_material.total.release);
                document.getElementById('fin-non_material-total-actual').textContent = formatIDR(data.non_material.total.actual);
                document.getElementById('fin-non_material-total-available').textContent = formatIDR(data.non_material.total.available);
                
                ['jasa', 'depresiasi', 'operasional', 'sewa', 'tenaga_kerja'].forEach(key => {
                    document.getElementById(`fin-non_material-${key}-release`).textContent = formatIDR(data.non_material[key].release);
                    document.getElementById(`fin-non_material-${key}-actual`).textContent = formatIDR(data.non_material[key].actual);
                    document.getElementById(`fin-non_material-${key}-available`).textContent = formatIDR(data.non_material[key].available);
                });

                // Update Performance
                document.getElementById('perf-revenue').textContent = formatIDR(data.performance.revenue);
                document.getElementById('perf-gpm').textContent = formatIDR(data.performance.gpm);
                document.getElementById('perf-gpm-percent').textContent = data.performance.gpm_percent.toFixed(2) + '%';
                
                const statusLabel = document.getElementById('perf-status-label');
                if (data.performance.gpm_percent > 100) {
                    statusLabel.textContent = 'Highly Optimized GPM';
                    statusLabel.className = 'mt-1 text-[9px] font-bold text-emerald-500 uppercase tracking-widest';
                } else if (data.performance.gpm_percent > 30) {
                    statusLabel.textContent = 'Healthy GPM';
                    statusLabel.className = 'mt-1 text-[9px] font-bold text-blue-500 uppercase tracking-widest';
                } else {
                    statusLabel.textContent = 'Check Cost Components';
                    statusLabel.className = 'mt-1 text-[9px] font-bold text-amber-500 uppercase tracking-widest';
                }

                // Remove loading state
                document.querySelectorAll('[id^="fin-"], #perf-revenue, #perf-gpm').forEach(el => el.classList.remove('opacity-50'));
            })
            .catch(err => {
                console.error('Error fetching financial data:', err);
            });
        };

        // Attach to PID select/input logic in Island 1
        // We'll hook into click events in the suggestions and also on manual input blur
        const originalSuggestionClick = (div, item) => {
            // Already have logic in index.js or inline scripts to set pidInput.value
            // We just need to trigger loadFinancialData
            loadFinancialData(item.project_id);
        };

        // If user types manual PID and leaves
        if (pidInput) {
            pidInput.addEventListener('blur', () => loadFinancialData(pidInput.value));
        }

        if (boqInput) {
            boqInput.addEventListener('change', function() {
                const file = this.files[0];
                if (!file) return;

                const formData = new FormData();
                formData.append('boq', file);
                formData.append('_token', '{{ csrf_token() }}');

                // Show loading state if needed
                previewContainer.classList.remove('hidden');
                previewBody.innerHTML = '<tr><td colspan="5" class="py-10 text-center text-brand-muted italic">Memverifikasi BoQ...</td></tr>';
                summaryBadge.innerHTML = '';

                fetch('{{ route("commerce.boq-verify") }}', {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.error) {
                        previewBody.innerHTML = `<tr><td colspan="5" class="py-10 text-center text-red-500 font-medium">${data.error}</td></tr>`;
                        return;
                    }

                    boqItems = data.items;
                    renderPreviewTable();
                    renderSummary(data.summary);
                    updateBoqJson();
                })
                .catch(error => {
                    console.error('Error verifying BoQ:', error);
                    previewBody.innerHTML = '<tr><td colspan="5" class="py-10 text-center text-red-500 font-medium">Terjadi kesalahan sistem saat verifikasi.</td></tr>';
                });
            });
        }

        function renderPreviewTable() {
            previewBody.innerHTML = '';
            
            if (boqItems.length === 0) {
                previewBody.innerHTML = '<tr><td colspan="6" class="py-10 text-center text-brand-muted italic">Tidak ada data untuk ditampilkan.</td></tr>';
                return;
            }

            boqItems.forEach((item, index) => {
                const tr = document.createElement('tr');
                tr.className = item.is_valid 
                    ? 'bg-white hover:bg-slate-50 transition-colors dark:bg-[#0f1728] dark:hover:bg-[#161f35]' 
                    : 'bg-red-50/50 dark:bg-red-900/5';
                
                const totalPrice = (item.volume_planning || 0) * (item.price_planning || 0);

                tr.innerHTML = `
                    <td class="px-5 py-3 font-semibold ${item.is_valid ? 'text-brand-text dark:text-slate-300' : 'text-red-600 dark:text-red-400'}">
                        ${item.designator}
                    </td>
                    <td class="px-5 py-3 text-brand-muted dark:text-slate-500 max-w-xs truncate" title="${item.description}">
                        ${item.description || '-'}
                    </td>
                    <td class="px-5 py-3 text-brand-muted dark:text-slate-500 font-mono text-center">
                        ${item.volume_planning || 0}
                    </td>
                    <td class="px-5 py-3 text-right font-mono text-brand-muted dark:text-slate-500">
                        ${new Intl.NumberFormat('id-ID').format(item.price_planning || 0)}
                    </td>
                    <td class="px-5 py-3 text-right font-mono font-bold text-brand-text dark:text-white">
                        ${new Intl.NumberFormat('id-ID').format(totalPrice)}
                    </td>
                    <td class="px-5 py-3 text-center">
                        ${item.is_valid 
                            ? '<span class="inline-flex h-5 w-5 items-center justify-center rounded-full bg-emerald-100 text-[10px] text-emerald-600 dark:bg-emerald-900/30 dark:text-emerald-400">✓</span>' 
                            : '<span class="inline-flex h-5 w-5 items-center justify-center rounded-full bg-red-100 text-[10px] text-red-600 dark:bg-red-900/30 dark:text-red-400" title="Designator tidak ditemukan di KHS">!</span>'}
                    </td>
                `;
                previewBody.appendChild(tr);
            });
        }

        function renderSummary(summary) {
            summaryBadge.innerHTML = `
                <span class="rounded-full bg-blue-100 px-2.5 py-0.5 text-[10px] font-bold text-blue-700 dark:bg-blue-900/30 dark:text-blue-400">Total: ${summary.total}</span>
                <span class="rounded-full bg-green-100 px-2.5 py-0.5 text-[10px] font-bold text-green-700 dark:bg-green-900/30 dark:text-green-400">Valid: ${summary.valid}</span>
                ${summary.invalid > 0 ? `<span class="rounded-full bg-red-100 px-2.5 py-0.5 text-[10px] font-bold text-red-700 dark:bg-red-900/30 dark:text-red-400">Invalid: ${summary.invalid}</span>` : ''}
            `;
        }

        window.updateItemVolume = function(index, value) {
            if (boqItems[index]) {
                boqItems[index].volume_planning = parseInt(value) || 0;
                updateBoqJson();
            }
        };

        function updateBoqJson() {
            boqJsonInput.value = JSON.stringify(boqItems);
        }
    });
</script>
@endpush
