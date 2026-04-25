@extends('layouts.dashboard')

@section('title', 'Manage ' . $roleLabel . ' Task')
@section('date_label', $roleLabel)
@section('hello_name', 'Task Management: ' . $project->id)

@section('content')
<div class="space-y-3">
    <!-- Back Button & Header -->
    <div class="flex items-center justify-between">
        @php
            // Map roles to their specific dashboards
            $dashboardMap = [
                'procurement' => ['route' => 'project-batch.index', 'label' => 'Dashboard Procurement'],
                'konstruksi' => ['route' => 'dashboard', 'label' => 'Monitoring Dashboard'],
                'commerce' => ['route' => 'commerce-rekon.index', 'label' => 'Dashboard Commerce'],
                'warehouse' => ['route' => 'rekon.index', 'label' => 'Dashboard Warehouse'],
                'finance' => ['route' => 'finance-rekon.index', 'label' => 'Dashboard Finance'],
            ];

            $backUrl = route('tasks.index', $role);
            $backLabel = 'Kembali ke Daftar Tugas';

            if (isset($dashboardMap[$role])) {
                $backUrl = route($dashboardMap[$role]['route']);
                $backLabel = 'Kembali ke ' . $dashboardMap[$role]['label'];
            }
        @endphp
        <a href="{{ $backUrl }}" class="inline-flex items-center gap-2 text-sm font-semibold text-brand-muted hover:text-brand-text transition dark:text-slate-400 dark:hover:text-white">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            {{ $backLabel }}
        </a>
        <div class="flex items-center gap-3">
            <span class="rounded-full bg-blue-50 px-3 py-1 text-xs font-bold text-blue-600 dark:bg-blue-900/30 dark:text-blue-400">Mode: {{ $roleLabel }} Editor</span>
        </div>
    </div>

    <!-- Project Quick Info -->
    @if($role !== 'commerce')
    <section class="rounded-xl border border-[#e6e7f0] bg-white p-3 shadow-soft dark:border-brand-darkLine dark:bg-brand-darkPanel">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h2 class="text-xl font-bold text-brand-text dark:text-white">{{ $project->project_name }}</h2>
                <div class="mt-1 flex items-center gap-3 text-sm text-brand-muted dark:text-slate-400">
                    <span class="font-mono font-bold">{{ $project->id }}</span>
                    <span>•</span>
                    <span>{{ $project->customer }}</span>
                    @if($project->mitra)
                        <span>•</span>
                        <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-lg bg-blue-50 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400 font-bold uppercase text-[10px]">
                            <i class="fas fa-handshake"></i>
                            Mitra: {{ $project->mitra->nama_mitra ?? $project->mitra->name }}
                        </span>
                    @endif
                </div>
            </div>
            <a href="{{ route('project-data.show', $project) }}" target="_blank" class="inline-flex items-center gap-2 rounded-xl border border-[#d9dceb] px-4 py-2 text-xs font-bold transition hover:bg-slate-50 dark:border-brand-darkLine dark:hover:bg-[#161f35]">
                View Details
                <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
            </a>
        </div>
    </section>
    @else
    <!-- Clean Minimal Header for Commerce -->
    <section class="rounded-xl border border-blue-500/20 bg-blue-50/10 p-4 dark:border-blue-900/30 dark:bg-blue-900/10">
        <h2 class="text-xl font-black text-slate-800 dark:text-white">{{ $project->project_name }}</h2>
        <p class="text-sm font-bold text-blue-600 dark:text-blue-400 mt-1">Rekonsiliasi Commerce Phase</p>
    </section>
    @endif

    <!-- Stepper Progress for Role -->
    <section class="rounded-xl border border-[#e6e7f0] bg-white p-3 shadow-soft dark:border-brand-darkLine dark:bg-brand-darkPanel">
        <div class="mb-8">
            <h3 class="text-lg font-bold">Sub-Fase Progress: {{ $roleLabel }}</h3>
            <p class="text-sm text-brand-muted">Lengkapi evidence untuk setiap sub-fase di bawah ini.</p>
        </div>

        @include('project-data.partials.subfase-stepper', [
            'project' => $project,
            'currentSubPhases' => $subPhases,
            'unifiedSubfaseStatuses' => $unifiedSubfaseStatuses,
            'canUpload' => true,
            'viewOnly' => false,
            'submitRoute' => $submitRoute,
            'submitLabel' => $submitLabel,
            'allSubphasesDone' => $allSubphasesDone,
            'role' => $role,
            'roleLabel' => $roleLabel,
        ])
    </section>
</div>
@endsection
