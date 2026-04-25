@extends('layouts.dashboard')

@section('title', 'Commerce Rekon Monitoring')
@section('date_label', 'Commerce')
@section('hello_name', 'Monitoring Rekon Commerce')

@section('content')

<div class="space-y-3">
    <!-- Main Table Section -->
    <section class="rounded-xl border border-[#e6e7f0] bg-white p-3 shadow-soft dark:border-brand-darkLine dark:bg-brand-darkPanel">
        <div class="flex items-center justify-between mb-6">
            <div>
                <h2 class="text-2xl font-bold text-brand-text dark:text-white">Monitoring Rekon Commerce</h2>
            </div>
        </div>

        <div class="overflow-hidden rounded-xl border border-[#e6e7f0] dark:border-brand-darkLine">
            <div class="p-4">
                <table id="rekon-table" class="min-w-full divide-y divide-[#e6e7f0] dark:divide-brand-darkLine">
                    <thead class="bg-[#fbfbfe] dark:bg-[#161f35]">
                        <tr>
                            <th class="px-5 py-4 text-left text-xs font-semibold uppercase tracking-[0.15em] text-brand-muted dark:text-slate-500">ID Rekon (TGIDRC)</th>
                            <th class="px-5 py-4 text-left text-xs font-semibold uppercase tracking-[0.15em] text-brand-muted dark:text-slate-500">Rekon Number</th>
                            <th class="px-5 py-4 text-right text-xs font-semibold uppercase tracking-[0.15em] text-brand-muted dark:text-slate-500">Total Amount Planning</th>
                            <th class="px-5 py-4 text-left text-xs font-semibold uppercase tracking-[0.15em] text-brand-muted dark:text-slate-500">Created By</th>
                            <th class="px-5 py-4 text-center text-xs font-semibold uppercase tracking-[0.15em] text-brand-muted dark:text-slate-500">Date</th>
                            <th class="px-5 py-4 text-center text-xs font-semibold uppercase tracking-[0.15em] text-brand-muted dark:text-slate-500">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#e6e7f0] dark:divide-brand-darkLine bg-white dark:bg-brand-darkPanel">
                        {{-- DataTables AJAX --}}
                    </tbody>
                </table>
            </div>
        </div>
    </section>
</div>

@push('scripts')
<script type="module">
$(document).ready(function() {
    $('#rekon-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: "{{ route('commerce-rekon.list') }}",
        pageLength: 25,
        scrollX: true,
        autoWidth: false,
        columns: [
            { 
                data: 'id', 
                render: function(data) {
                    return `<span class="block text-sm font-bold text-blue-600 dark:text-blue-400 font-mono tracking-tight">${data}</span>`;
                }
            },
            { 
                data: 'rekon_number', 
                render: function(data) {
                    return `<span class="block text-sm font-semibold text-slate-800 dark:text-white">${data || '-'}</span>`;
                }
            },
            { 
                data: 'total_amount_planning', 
                className: 'text-right',
                render: function(data) {
                    let nominal = data ? data.replace('Rp ', '') : '0';
                    return `<div class="flex justify-end items-center gap-1.5 font-mono">
                                <span class="text-[10px] font-bold text-slate-400 dark:text-slate-500">Rp</span>
                                <span class="text-sm font-black text-slate-900 dark:text-white">${nominal}</span>
                            </div>`;
                }
            },
            { 
                data: 'creator.name', 
                render: function(data) {
                    return `<span class="text-[10px] font-bold text-slate-500 uppercase">${data || 'System'}</span>`;
                }
            },
            { 
                data: 'created_at', 
                className: 'text-center',
                render: function(data) {
                    return `<span class="text-[10px] font-medium text-slate-400">${data}</span>`;
                }
            },
            { 
                data: 'action', 
                className: 'text-center',
                orderable: false, 
                searchable: false 
            }
        ],
        dom: '<"top"lf>rt<"bottom"ip><"clear">',
        language: {
            zeroRecords: "Data tidak ditemukan",
            paginate: {
                previous: "Prev",
                next: "Next"
            }
        }
    });
});
</script>
@endpush
@endsection
