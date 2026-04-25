@extends('layouts.dashboard')

@section('title', 'Command Center - Workflow Monitoring')
@section('hello_name', 'Command Center')
@section('date_label', 'Workflow Hub V1')
@section('hero_subtitle', 'Lifecycle & Workflow Monitoring Hub V1')

@section('hero_actions')
    <div class="rounded-xl bg-white p-4 shadow-sm dark:bg-[#161f35] border border-slate-100 dark:border-brand-darkLine">
        <span class="block text-xs font-bold uppercase tracking-widest text-slate-400 dark:text-slate-500">Active Batches</span>
        <span class="mt-1 text-2xl font-black text-brand-blue dark:text-blue-400">{{ $batches->total() }}</span>
    </div>
@endsection

@section('content')
<div class="space-y-3">
    <div class="overflow-hidden rounded-xl border border-slate-200 bg-white p-3 shadow-soft dark:border-brand-darkLine dark:bg-[#161f35]">
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead>
                    <tr class="bg-slate-50/50 text-xs font-bold uppercase tracking-widest text-slate-400 dark:text-slate-500 dark:bg-slate-800/50">
                        <th class="px-6 py-5">Batch Profile</th>
                        <th class="px-6 py-5">
                            <span class="flex items-center gap-2">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" /></svg>
                                Proc
                            </span>
                        </th>
                        <th class="px-6 py-5">
                            <span class="flex items-center gap-2">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" /></svg>
                                Konstr
                            </span>
                        </th>
                        <th class="px-6 py-5">
                            <span class="flex items-center gap-2">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                Comm
                            </span>
                        </th>
                        <th class="px-6 py-5">
                            <span class="flex items-center gap-2">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" /></svg>
                                Wareh
                            </span>
                        </th>
                        <th class="px-6 py-5">
                            <span class="flex items-center gap-2">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" /></svg>
                                Fin
                            </span>
                        </th>
                        <th class="px-6 py-5 text-right whitespace-nowrap">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-brand-darkLine">
                    @php $itemCount = 0; @endphp
                    @foreach ($batches as $batch)
                        @php $itemCount++; @endphp
                        <tr class="group hover:bg-slate-50/50 dark:hover:bg-slate-800/30 transition-colors">
                            <td class="px-6 py-5">
                                <div class="flex items-center gap-4">
                                    <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-brand-blue/10 text-brand-blue dark:bg-brand-vibrantBlue/20 dark:text-brand-vibrantBlue font-bold">
                                        {{ substr($batch->id, -2) }}
                                    </div>
                                    <div>
                                        <p class="text-sm font-bold text-slate-800 dark:text-white">{{ $batch->id }}</p>
                                        <p class="text-[11px] text-slate-400 dark:text-slate-500 mt-0.5 line-clamp-1">{{ $batch->project_name }}</p>
                                    </div>
                                </div>
                            </td>
                            
                            @php 
                                $depts = ['procurement', 'konstruksi', 'commerce', 'warehouse', 'finance']; 
                            @endphp
                            @foreach($depts as $dept)
                                @php $status = \App\Http\Controllers\MonitoringController::getDeptStatus($batch, $dept); @endphp
                                <td class="px-6 py-5">
                                    <div class="inline-flex flex-col">
                                        <span class="rounded-lg px-2.5 py-1 text-[10px] font-bold {{ $status['color'] }} inline-block">
                                            {{ strtoupper($status['label']) }}
                                        </span>
                                        @if(isset($status['secondary']))
                                            <span class="mt-1 text-[9px] font-medium text-slate-400 dark:text-slate-500 font-mono">{{ $status['secondary'] }}</span>
                                        @endif
                                        
                                        @if(isset($status['links']))
                                            <div class="mt-2 flex flex-wrap gap-2">
                                                @foreach($status['links'] as $link)
                                                    <a href="{{ $link['url'] }}" class="text-[9px] font-black text-brand-blue dark:text-brand-vibrantBlue hover:underline uppercase tracking-tighter decoration-brand-blue/30 underline-offset-2">
                                                        {{ $link['label'] }}
                                                    </a>
                                                @endforeach
                                            </div>
                                        @endif
                                    </div>
                                </td>
                            @endforeach

                            <td class="px-6 py-5 text-right">
                                <a href="{{ route('project-batch.show', $batch->id) }}" class="inline-flex h-9 items-center justify-center rounded-xl border border-slate-200 px-4 text-xs font-bold text-slate-600 transition hover:bg-slate-100 dark:border-brand-darkLine dark:text-slate-400 dark:hover:bg-slate-800">
                                    Details
                                </a>
                            </td>
                        </tr>
                    @endforeach

                    @if ($itemCount === 0)
                        <tr>
                            <td colspan="7" class="px-6 py-20 text-center">
                                <p class="text-sm text-slate-400 dark:text-slate-500 italic">Belum ada data batch untuk ditampilkan.</p>
                            </td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>
        
        @if($batches->hasPages())
            <div class="border-t border-slate-100 p-5 dark:border-brand-darkLine">
                {{ $batches->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
