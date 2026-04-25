@extends('layouts.dashboard')

@section('title', 'Taskgate Project Data')
@section('date_label', 'Project Data')
@section('hello_name', 'Project Data')
@section('hero_subtitle', 'Daftar project aktif. Data di tabel ini sekarang membaca record project yang tersimpan di database.')

@section('hero_actions')
    <a href="{{ route('project-data.index') }}" class="rounded-full border border-[#d9dceb] bg-white px-5 py-2.5 text-sm font-semibold text-brand-text transition hover:border-[#9ca3af] dark:border-brand-darkLine dark:bg-brand-darkPanel dark:text-white">
        Refresh
    </a>
    <a id="export-button" href="{{ route('project-data.export') }}" class="rounded-full border border-[#d9dceb] bg-white px-5 py-2.5 text-sm font-semibold text-brand-text transition hover:border-[#9ca3af] dark:border-brand-darkLine dark:bg-brand-darkPanel dark:text-white">
        Export CSV
    </a>
@endsection

@section('content')
     {{-- DataTables CSS removed --}}

    <div class="space-y-3">

    <section class="rounded-xl border border-[#e6e7f0] bg-white p-3 shadow-soft dark:border-brand-darkLine dark:bg-brand-darkPanel">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-[1fr_1fr_1fr_auto] gap-4 rounded-xl bg-[#fbfbfe] p-4 dark:bg-[#161f35]">
            <label class="block">
                <span class="mb-2 block text-xs font-semibold uppercase tracking-[0.18em] text-brand-muted dark:text-slate-500">Program</span>
                <select id="filter-program" data-searchable-select class="h-11 w-full rounded-xl border border-[#d9dceb] bg-white px-4 text-sm outline-none dark:border-brand-darkLine dark:bg-brand-darkPanel">
                    <option value="">All program</option>
                    <option value="proactive">Proactive</option>
                    <option value="migration">Migration</option>
                    <option value="modernization">Modernization</option>
                </select>
            </label>

            <label class="block">
                <span class="mb-2 block text-xs font-semibold uppercase tracking-[0.18em] text-brand-muted dark:text-slate-500">Branch</span>
                <select id="filter-branch" data-searchable-select class="h-11 w-full rounded-xl border border-[#d9dceb] bg-white px-4 text-sm outline-none dark:border-brand-darkLine dark:bg-brand-darkPanel">
                    <option value="">All branch</option>
                    <option value="jakarta">Jakarta</option>
                    <option value="bandung">Bandung</option>
                    <option value="surabaya">Surabaya</option>
                    <option value="makassar">Makassar</option>
                </select>
            </label>

            <label class="block">
                <span class="mb-2 block text-xs font-semibold uppercase tracking-[0.18em] text-brand-muted dark:text-slate-500">Created From</span>
                <input id="filter-created-from" type="date" class="h-11 w-full rounded-xl border border-[#d9dceb] bg-white px-4 text-sm outline-none dark:border-brand-darkLine dark:bg-brand-darkPanel">
            </label>

            <label class="block lg:col-span-1">
                <span class="mb-2 block text-xs font-semibold uppercase tracking-[0.18em] text-brand-muted dark:text-slate-500">Created To</span>
                <input id="filter-created-to" type="date" class="h-11 w-full rounded-xl border border-[#d9dceb] bg-white px-4 text-sm outline-none dark:border-brand-darkLine dark:bg-brand-darkPanel">
            </label>

            <div class="flex items-end sm:col-span-2 lg:col-span-4">
                <button id="reset-filters" type="button" class="h-11 w-full sm:w-auto rounded-xl border border-[#d9dceb] px-5 text-sm font-semibold transition hover:border-[#9ca3af] dark:border-brand-darkLine">
                    Reset
                </button>
            </div>
        </div>

        <div class="mt-3 overflow-hidden rounded-xl border border-[#e6e7f0] dark:border-brand-darkLine">
            <div class="p-4">
                <table id="records-table" class="overflow-x-auto min-w-full divide-y divide-[#e6e7f0] dark:divide-brand-darkLine">
                    <thead class="bg-[#fbfbfe] dark:bg-[#161f35]">
                        <tr>
                            <th class="px-5 py-4 text-left text-xs font-semibold uppercase tracking-[0.15em] text-brand-muted dark:text-slate-500">ID Taskgate</th>
                            <th class="px-5 py-4 text-left text-xs font-semibold uppercase tracking-[0.15em] text-brand-muted dark:text-slate-500">Project Name</th>
                            <th class="px-5 py-4 hidden sm:table-cell text-left text-xs font-semibold uppercase tracking-[0.15em] text-brand-muted dark:text-slate-500">Customer</th>
                            <th class="px-5 py-4 text-center text-xs font-semibold uppercase tracking-[0.15em] text-brand-muted dark:text-slate-500">Action</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
        </section>
    </div>

    <style>
        /* Custom filter UI adjustments */
        #records-table_filter { display: none; }
        
        /* Prevent layout shift */
        #records-table {
            width: 100% !important;
        }
    </style>

    <script type="module">
        document.addEventListener('DOMContentLoaded', function () {
            const programFilter = document.getElementById('filter-program');
            const branchFilter = document.getElementById('filter-branch');
            const createdFromFilter = document.getElementById('filter-created-from');
            const createdToFilter = document.getElementById('filter-created-to');
            const resetFiltersButton = document.getElementById('reset-filters');
            const exportButton = document.getElementById('export-button');

            const table = $('#records-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: '{{ route('project-data.data') }}',
                    data: function (d) {
                        d.program = programFilter.value;
                        d.branch = branchFilter.value;
                        d.created_from = createdFromFilter.value;
                        d.created_to = createdToFilter.value;
                    }
                },
                pageLength: 10,
                dom: '<"top"lf>rt<"bottom"ip><"clear">',
                scrollX: true,
                autoWidth: false,
                order: [[0, 'asc']],
                columns: [
                    { 
                        data: 'id', 
                        name: 'id', 
                        render: function(data) {
                            return `<span class="block text-sm font-bold text-brand-blue dark:text-blue-400">${data || '-'}</span>`;
                        }
                    },
                    { 
                        data: 'project_name', 
                        name: 'project_name', 
                        render: function(data) {
                            return `<span class="block text-sm font-semibold dark:text-white">${data}</span>`;
                        }
                    },
                    { 
                        data: 'customer', 
                        name: 'customer', 
                        className: 'hidden sm:table-cell',
                        render: function(data) {
                            return `<span class="block text-xs text-brand-muted dark:text-slate-400 font-medium">${data || '-'}</span>`;
                        }
                    },
                    { 
                        data: 'action', 
                        name: 'action', 
                        orderable: false, 
                        searchable: false, 
                        className: 'text-center'
                    }
                ],
                dom: '<"top"f>rt<"bottom"ip><"clear">',
                language: {
                    search: "_INPUT_",
                    searchPlaceholder: "Filter tabel...",
                    info: 'Showing _START_ to _END_ of _TOTAL_ rows',
                    emptyTable: 'No data available yet'
                }
            });

            function updateExportUrl() {
                const params = new URLSearchParams();
                const searchValue = table.search();

                if (programFilter.value) params.set('program', programFilter.value);
                if (branchFilter.value) params.set('branch', branchFilter.value);
                if (createdFromFilter.value) params.set('created_from', createdFromFilter.value);
                if (createdToFilter.value) params.set('created_to', createdToFilter.value);
                if (searchValue) params.set('search', searchValue);

                exportButton.href = '{{ route('project-data.export') }}' + (params.toString() ? `?${params.toString()}` : '');
            }

            [programFilter, branchFilter, createdFromFilter, createdToFilter].forEach(function (element) {
                element.addEventListener('change', function () {
                    table.ajax.reload();
                    updateExportUrl();
                });
            });

            resetFiltersButton.addEventListener('click', function () {
                programFilter.value = '';
                branchFilter.value = '';
                createdFromFilter.value = '';
                createdToFilter.value = '';
                table.search('').draw();
                table.ajax.reload();
                updateExportUrl();
            });

            table.on('search.dt', updateExportUrl);
            table.on('draw.dt', updateExportUrl);
            updateExportUrl();
        });
    </script>
@endsection
