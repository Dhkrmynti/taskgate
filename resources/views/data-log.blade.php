@extends('layouts.dashboard')

@section('title', $title)
@section('date_label', 'Data Log')
@section('hello_name', $title)
@section('hero_subtitle', 'Riwayat aktivitas dan histori data untuk modul ' . ucfirst($module) . '.')

@section('content')

    <div class="space-y-3">
        <section class="rounded-xl border border-[#e6e7f0] bg-white p-3 shadow-soft dark:border-brand-darkLine dark:bg-brand-darkPanel">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h2 class="text-xl font-semibold text-brand-text dark:text-white">Activity Log</h2>
                    <p class="text-sm text-brand-muted dark:text-slate-400">Daftar aktivitas yang tercatat di dalam sistem.</p>
                </div>
            </div>

            <div class="overflow-hidden rounded-xl border border-[#e6e7f0] dark:border-brand-darkLine">
                <div class="p-4">
                    <table id="data-log-table" class="min-w-full divide-y divide-[#e6e7f0] dark:divide-brand-darkLine">
                        <thead class="bg-[#fbfbfe] dark:bg-[#161f35]">
                            <tr>
                            <th class="px-5 py-4 text-left text-xs font-semibold uppercase tracking-[0.15em] text-brand-muted dark:text-slate-500">Time</th>
                            <th class="px-5 py-4 text-center text-xs font-semibold uppercase tracking-[0.15em] text-brand-muted dark:text-slate-500">User</th>
                            <th class="px-5 py-4 text-center text-xs font-semibold uppercase tracking-[0.15em] text-brand-muted dark:text-slate-500">Action</th>
                            <th class="px-5 py-4 text-left text-xs font-semibold uppercase tracking-[0.15em] text-brand-muted dark:text-slate-500">Description</th>
                            <th class="px-5 py-4 text-center text-xs font-semibold uppercase tracking-[0.15em] text-brand-muted dark:text-slate-500">Status</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </section>
    </div>


    <script type="module">
        $(document).ready(function() {
            $('#data-log-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: '{{ route($module . ".data-log.data") }}',
                pageLength: 25,
                scrollX: true,
                autoWidth: false,
                order: [[0, 'desc']],
                columns: [
                    { 
                        data: 'created_at', 
                        name: 'created_at',
                        render: function(data) {
                            return `<span class="block text-sm font-medium dark:text-white">${data}</span>`;
                        }
                    },
                    { 
                        data: 'user_name', 
                        name: 'user_name',
                        className: 'text-center',
                        render: function(data) {
                            return `<span class="inline-flex items-center justify-center">
                                <span class="px-2.5 py-0.5 rounded-full text-xs font-medium bg-slate-100 text-slate-800 border border-slate-200 dark:bg-slate-800 dark:text-slate-200 dark:border-slate-700">${data}</span>
                            </span>`;
                        }
                    },
                    { 
                        data: 'action', 
                        name: 'action',
                        className: 'text-center',
                        render: function(data) {
                            let colorClass = 'bg-blue-100 text-blue-800 border-blue-200 dark:bg-blue-900/20 dark:text-blue-400 dark:border-blue-800';
                            if (data === 'IMPORT') colorClass = 'bg-emerald-100 text-emerald-800 border-emerald-200 dark:bg-emerald-900/20 dark:text-emerald-400 dark:border-emerald-800';
                            if (data === 'CREATE') colorClass = 'bg-violet-100 text-violet-800 border-violet-200 dark:bg-violet-900/20 dark:text-violet-400 dark:border-violet-800';
                            if (data === 'SUBMIT') colorClass = 'bg-amber-100 text-amber-800 border-amber-200 dark:bg-amber-900/20 dark:text-amber-400 dark:border-amber-800';
                            
                            return `<span class="inline-flex items-center justify-center">
                                <span class="px-2.5 py-0.5 rounded-full text-xs font-bold ${colorClass} border uppercase">${data}</span>
                            </span>`;
                        }
                    },
                    { 
                        data: 'description', 
                        name: 'description',
                        render: function(data) {
                            return `<span class="block text-sm dark:text-slate-400 text-brand-text truncate max-w-md" title="${data}">${data}</span>`;
                        }
                    },
                    { 
                        data: null, 
                        className: 'text-center',
                        orderable: false,
                        searchable: false,
                        render: function() {
                            return `<span class="inline-flex items-center justify-center">
                                <span class="inline-flex items-center rounded-full bg-green-50 px-2 py-1 text-xs font-medium text-green-700 ring-1 ring-inset ring-green-600/20 dark:bg-green-900/20 dark:text-green-400">Success</span>
                            </span>`;
                        }
                    }
                ],
                dom: '<"top"lf>rt<"bottom"ip><"clear">',
                language: {
                    search: "_INPUT_",
                    searchPlaceholder: "Filter tabel...",
                    info: 'Showing _START_ to _END_ of _TOTAL_ rows',
                    emptyTable: 'Belum ada riwayat import'
                }
            });
        });
    </script>
@endsection
