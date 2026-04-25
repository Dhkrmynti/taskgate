@extends('layouts.dashboard')

@section('title', 'Finance Rekon Monitoring')
@section('date_label', 'Finance')
@section('hello_name', 'Monitoring Rekon Finance')

@section('content')



<div class="space-y-3">
    <!-- Main Table Section -->
    <section class="rounded-xl border border-[#e6e7f0] bg-white p-3 shadow-soft dark:border-brand-darkLine dark:bg-brand-darkPanel">
        <div class="overflow-hidden rounded-xl border border-[#e6e7f0] dark:border-brand-darkLine">
            <div class="p-4">
                <table id="rekon-table" class="min-w-full divide-y divide-[#e6e7f0] dark:divide-brand-darkLine">
                    <thead class="bg-[#fbfbfe] dark:bg-[#161f35]">
                        <tr>
                            <th class="px-5 py-4 text-left text-xs font-bold uppercase tracking-[0.15em] text-brand-muted dark:text-slate-500">ID Rekon</th>
                            <th class="px-5 py-4 text-left text-xs font-bold uppercase tracking-[0.15em] text-brand-muted dark:text-slate-500">APM Number</th>
                            <th class="px-5 py-4 text-right text-xs font-bold uppercase tracking-[0.15em] text-brand-muted dark:text-slate-500">Total Jasa</th>
                            <th class="px-5 py-4 text-left text-xs font-bold uppercase tracking-[0.15em] text-brand-muted dark:text-slate-500">Created By</th>
                            <th class="px-5 py-4 text-center text-xs font-bold uppercase tracking-[0.15em] text-brand-muted dark:text-slate-500">Date</th>
                            <th class="px-5 py-4 text-center text-xs font-bold uppercase tracking-[0.15em] text-brand-muted dark:text-slate-500">Actions</th>
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
        ajax: "{{ route('finance-rekon.list') }}",
        pageLength: 25,
        scrollX: true,
        autoWidth: false,
        columns: [
            { 
                data: 'id', 
                render: function(data) {
                    return `<span class="text-xs font-black text-blue-600 dark:text-blue-400 font-mono tracking-tight">${data}</span>`;
                }
            },
            { 
                data: 'apm_number', 
                render: function(data) {
                    return `<span class="text-xs font-bold text-slate-700 dark:text-slate-300">${data || '-'}</span>`;
                }
            },
            { 
                data: 'total_amount_jasa', 
                render: function(data) {
                    let nominal = data ? data.replace('Rp ', '') : '0';
                    return `<div class="flex justify-between items-center min-w-[120px]">
                                <span class="text-[10px] font-bold text-slate-400 dark:text-slate-500 tracking-wider">Rp</span>
                                <span class="text-xs font-black text-slate-900 dark:text-white font-mono">${nominal}</span>
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
