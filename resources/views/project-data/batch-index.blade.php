@extends('layouts.dashboard')

@section('title', 'Dashboard Procurement')
@section('date_label', 'Procurement')
@section('hello_name', 'Dashboard Procurement')
@section('hero_subtitle', 'Monitoring daftar semua batch pengerjaan (TGIDSP) yang telah dibuat.')

@section('hero_actions')
    <a href="{{ route('project-batch.index') }}" class="rounded-full border border-[#d9dceb] bg-white px-5 py-2.5 text-sm font-semibold text-brand-text transition hover:border-[#9ca3af] dark:border-brand-darkLine dark:bg-brand-darkPanel dark:text-white">
        Refresh
    </a>
@endsection

@section('content')
    <div class="space-y-3">
        <section class="rounded-xl border border-[#e6e7f0] bg-white p-3 shadow-soft dark:border-brand-darkLine dark:bg-brand-darkPanel">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-[1fr_1fr_1fr_auto] gap-4 rounded-xl bg-[#fbfbfe] p-4 dark:bg-[#161f35]">
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

                <label class="block">
                    <span class="mb-2 block text-xs font-semibold uppercase tracking-[0.18em] text-brand-muted dark:text-slate-500">Created To</span>
                    <input id="filter-created-to" type="date" class="h-11 w-full rounded-xl border border-[#d9dceb] bg-white px-4 text-sm outline-none dark:border-brand-darkLine dark:bg-brand-darkPanel">
                </label>

                <div class="flex items-end">
                    <button id="reset-filters" type="button" class="h-11 w-full sm:w-auto rounded-xl border border-[#d9dceb] px-5 text-sm font-semibold transition hover:border-[#9ca3af] dark:border-brand-darkLine">
                        Reset
                    </button>
                </div>
            </div>

            <div class="mt-3 overflow-hidden rounded-xl border border-[#e6e7f0] dark:border-brand-darkLine">
                <div class="p-4">
                    <table id="batch-table" class="overflow-x-auto min-w-full divide-y divide-[#e6e7f0] dark:divide-brand-darkLine">
                        <thead class="bg-[#fbfbfe] dark:bg-[#161f35]">
                            <tr>
                                <th class="px-5 py-4 text-left text-xs font-semibold uppercase tracking-[0.15em] text-brand-muted dark:text-slate-500">ID Taskgate</th>
                                <th class="px-5 py-4 text-left text-xs font-semibold uppercase tracking-[0.15em] text-brand-muted dark:text-slate-500">PO Number</th>
                                <th class="px-5 py-4 text-left text-xs font-semibold uppercase tracking-[0.15em] text-brand-muted dark:text-slate-500">Project Name</th>
                                <th class="px-5 py-4 text-left text-xs font-semibold uppercase tracking-[0.15em] text-brand-muted dark:text-slate-500">Customer</th>
                                <th class="px-5 py-4 text-left text-xs font-semibold uppercase tracking-[0.15em] text-brand-muted dark:text-slate-500">Fase</th>
                                <th class="px-5 py-4 text-center text-xs font-semibold uppercase tracking-[0.15em] text-brand-muted dark:text-slate-500">Action</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </section>
    </div>

    <script type="module">
        document.addEventListener('DOMContentLoaded', function () {
            const branchFilter = document.getElementById('filter-branch');
            const createdFromFilter = document.getElementById('filter-created-from');
            const createdToFilter = document.getElementById('filter-created-to');
            const resetFiltersButton = document.getElementById('reset-filters');

            const table = $('#batch-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: '{{ route('project-batch.data') }}',
                    data: function (d) {
                        d.branch = branchFilter.value;
                        d.created_from = createdFromFilter.value;
                        d.created_to = createdToFilter.value;
                    }
                },
                pageLength: 10,
                scrollX: true,
                autoWidth: false,
                order: [[0, 'desc']],
                columns: [
                    { 
                        data: 'id', 
                        name: 'id', 
                        render: function(data) {
                            return `<span class="block text-sm font-extrabold text-blue-600 dark:text-blue-400">${data}</span>`;
                        }
                    },
                    { 
                        data: 'po_number', 
                        name: 'po_number', 
                        render: function(data) {
                            return `<span class="block text-sm font-semibold dark:text-white">${data || '-'}</span>`;
                        }
                    },
                    { 
                        data: 'project_name', 
                        name: 'project_name', 
                        render: function(data) {
                            return `<span class="block text-sm font-medium dark:text-slate-300">${data}</span>`;
                        }
                    },
                    { 
                        data: 'customer', 
                        name: 'customer', 
                        render: function(data) {
                            return `<span class="block text-xs text-brand-muted dark:text-slate-400">${data || '-'}</span>`;
                        }
                    },
                    { 
                        data: 'fase', 
                        name: 'fase', 
                        render: function(data) {
                            return `<div class="">${data}</div>`;
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
                dom: '<"top"lf>rt<"bottom"ip><"clear">',
                language: {
                    search: "_INPUT_",
                    searchPlaceholder: "Cari batch...",
                    info: 'Menampilkan _START_ sampai _END_ dari _TOTAL_ batch',
                }
            });

            [branchFilter, createdFromFilter, createdToFilter].forEach(function (element) {
                if (element) {
                    element.addEventListener('change', () => table.ajax.reload());
                }
            });

            if (resetFiltersButton) {
                resetFiltersButton.addEventListener('click', function () {
                    if(branchFilter) branchFilter.value = '';
                    if(createdFromFilter) createdFromFilter.value = '';
                    if(createdToFilter) createdToFilter.value = '';
                    table.search('').draw();
                    table.ajax.reload();
                });
            }
        });
    </script>
@endsection
