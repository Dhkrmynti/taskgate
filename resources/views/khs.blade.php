@extends('layouts.dashboard')

@section('title', 'Taskgate KHS')
@section('date_label', 'Master Data')
@section('hello_name', 'KHS Database')
@section('hero_subtitle', 'Import file KHS Excel lalu kelola data besar per tab dengan DataTable server-side (Yajra).')

@section('content')

    <div class="space-y-3">
        @if (!empty($setupError))
            <section class="rounded-xl border border-[#f2c8cc] bg-[#fff8f8] p-3 shadow-soft dark:border-[#5c2d34] dark:bg-[#2a171b]">
                <p class="text-sm font-semibold text-[#9f2b3a] dark:text-[#f0c8cf]">{{ $setupError }}</p>
                <p class="mt-2 text-sm text-[#7a3b43] dark:text-[#d2aeb4]">Perintah: <span class="font-mono">php artisan migrate</span></p>
            </section>
        @endif

        <section class="rounded-xl border border-[#e6e7f0] bg-white p-3 shadow-soft dark:border-brand-darkLine dark:bg-brand-darkPanel">
            <div class="flex flex-wrap items-center justify-between gap-4">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.18em] text-brand-muted dark:text-slate-500">Import Excel</p>
                    <h2 class="mt-2 text-2xl font-semibold text-brand-text dark:text-white">Upload KHS Database</h2>
                    <p class="mt-2 text-sm text-brand-muted dark:text-slate-400">File terbaru akan mengganti data KHS sebelumnya (reset total).</p>
                </div>
                @if ($latestBatch)
                    <div class="rounded-xl border border-[#d9dceb] bg-[#fbfbfe] px-4 py-3 text-xs text-brand-muted dark:border-brand-darkLine dark:bg-[#161f35] dark:text-slate-400">
                        <p>File: <span class="font-semibold text-brand-text dark:text-white">{{ $latestBatch->original_file_name }}</span></p>
                        <p class="mt-1">Imported: {{ $latestBatch->imported_at?->format('Y-m-d H:i') ?: '-' }}</p>
                        <p class="mt-1">Rows: {{ number_format((int) $latestBatch->total_rows, 0, ',', '.') }}</p>
                    </div>
                @endif
            </div>

            <form id="khs-import-form" action="{{ route('khs.import') }}" method="POST" enctype="multipart/form-data" class="mt-5 flex flex-wrap items-center gap-3">
                @csrf
                <input name="khs_file" type="file" accept=".xlsx,.csv" required class="block rounded-xl border border-[#d9dceb] bg-white px-3 py-2.5 text-sm file:mr-4 file:rounded-lg file:border-0 file:bg-[#f3f4f6] file:px-3 file:py-2 file:text-sm file:font-medium dark:border-brand-darkLine dark:bg-[#0f1728] dark:text-white dark:file:bg-[#1c2540]">
                <button type="submit" class="inline-flex h-11 items-center rounded-xl bg-brand-text px-5 text-sm font-semibold text-white transition hover:bg-[#2f3542] dark:bg-white dark:text-brand-darkBg">
                    Import Excel/CSV
                </button>
                <a href="{{ route('khs.template.download') }}" class="inline-flex h-11 items-center rounded-xl border border-[#d9dceb] px-5 text-sm font-semibold text-brand-text transition hover:border-[#9ca3af] dark:border-brand-darkLine dark:text-white">
                    Download Template OSP-FO (Excel)
                </a>
                <a href="{{ route('khs.template.download') }}?format=csv" class="ml-2 inline-flex h-11 items-center rounded-xl border border-[#d9dceb] px-5 text-sm font-semibold text-brand-text transition hover:border-[#9ca3af] dark:border-brand-darkLine dark:text-white">
                    Download Template OSP-FO (CSV)
                </a>
            </form>
        </section>

        <section class="rounded-xl border border-[#e6e7f0] bg-white p-3 shadow-soft dark:border-brand-darkLine dark:bg-brand-darkPanel">
            @if (!$tab)
                <div class="rounded-xl border border-dashed border-[#d9dceb] bg-[#fbfbfe] px-5 py-6 text-sm text-brand-muted dark:border-brand-darkLine dark:bg-[#161f35] dark:text-slate-400">
                    Belum ada data OSP-FO. Upload file Excel yang berisi sheet OSP-FO terlebih dahulu.
                </div>
            @else
                <div class="flex items-center justify-between gap-4">
                    <h3 class="text-lg font-semibold text-brand-text dark:text-white">Data OSP-FO</h3>
                    <div class="flex items-center gap-4">
                        <div class="text-xs text-brand-muted dark:text-slate-400">
                            Total Baris: {{ number_format((int) $tab['row_count'], 0, ',', '.') }}
                        </div>
                        <button type="button" onclick="openKhsModal()" class="inline-flex h-9 items-center rounded-lg bg-brand-text px-4 text-xs font-semibold text-white transition hover:bg-[#2f3542] dark:bg-brand-primary dark:hover:bg-brand-primary/80">
                            <i class="fas fa-plus mr-1.5"></i> Tambah Data
                        </button>
                    </div>
                </div>

                <div class="mt-5 overflow-hidden rounded-xl border border-[#e6e7f0] dark:border-brand-darkLine">
                    <div class="p-4">
                        <table id="khs-table" class="min-w-full text-sm"></table>
                    </div>
                </div>
            @endif
        </section>
    </div>

    <div id="khs-modal" class="fixed inset-0 z-50 hidden overflow-y-auto bg-slate-900/50 backdrop-blur-sm transition-opacity" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex min-h-screen items-center justify-center p-4">
            <div class="relative w-full max-w-2xl transform overflow-hidden rounded-2xl bg-white text-left align-middle shadow-2xl transition-all dark:bg-brand-darkBg dark:border dark:border-brand-darkLine">
                <div class="border-b border-slate-100 px-6 py-4 dark:border-brand-darkLine">
                    <h3 class="text-lg font-semibold text-slate-800 dark:text-white" id="modal-title">Tambah Data KHS</h3>
                </div>
                <form id="khs-form" onsubmit="submitKhsForm(event)">
                    <div class="max-h-[60vh] overflow-y-auto p-6">
                        <input type="hidden" id="khs-id" name="id" value="">
                        <div id="dynamic-form-fields" class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                            <!-- Fields will be dynamically populated via JS -->
                        </div>
                    </div>
                    <div class="flex items-center justify-end gap-3 border-t border-slate-100 bg-slate-50/50 px-6 py-4 dark:border-brand-darkLine dark:bg-brand-darkPanel">
                        <button type="button" onclick="closeKhsModal()" class="rounded-xl border border-slate-200 bg-white px-5 py-2.5 text-sm font-medium text-slate-600 transition hover:bg-slate-50 dark:border-brand-darkLine dark:bg-brand-darkBg dark:text-slate-300 dark:hover:bg-brand-darkLine">Batal</button>
                        <button type="submit" id="btn-submit-khs" class="rounded-xl bg-brand-text px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-slate-800 dark:bg-brand-primary dark:hover:bg-brand-primary/80">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @if ($tab)
        <script type="module">
            (function() {
                var tab = @json($tab);
                var dataUrl = @json(route('khs.data'));
                var dataTable = null;

                function escapeHtml(value) {
                    var text = value === null || value === undefined ? '' : String(value);
                    return text
                        .replaceAll('&', '&amp;')
                        .replaceAll('<', '&lt;')
                        .replaceAll('>', '&gt;')
                        .replaceAll('"', '&quot;')
                        .replaceAll("'", '&#039;');
                }

                function toTitleCase(input) {
                    return String(input || '')
                        .replaceAll('_', ' ')
                        .replaceAll('-', ' ')
                        .replace(/\s+/g, ' ')
                        .trim()
                        .split(' ')
                        .filter(Boolean)
                        .map(function(word) {
                            return word.charAt(0).toUpperCase() + word.slice(1);
                        })
                        .join(' ');
                }

                function buildLeafHeaders(tab) {
                    var headers = (tab.headers || []).map(function(header) {
                        var safeKey = String(header.key || '');
                        var labelSource = String(header.label || header.key || '');
                        return {
                            key: safeKey,
                            label: toTitleCase(labelSource)
                        };
                    });

                    var hasNoColumn = headers.some(function(header) {
                        return header.key.toLowerCase() === 'no';
                    });

                    if (!hasNoColumn) {
                        headers.unshift({
                            key: '__row_number',
                            label: 'No'
                        });
                    }

                    return headers;
                }

                function buildTableHeader(tab) {
                    var leafHeaders = buildLeafHeaders(tab);
                    var topCells = [];
                    var bottomCells = [];
                    var hasGrouped = false;
                    var groupedRegex = /^(.*)_(material|jasa|perizinan|total)$/i;
                    var currentGroupKey = null;

                    leafHeaders.forEach(function(header) {
                        var match = header.key.match(groupedRegex);

                        if (!match || header.key === '__row_number') {
                            topCells.push('<th rowspan=\"2\" class=\"px-3 py-3 text-left text-xs font-semibold uppercase tracking-[0.12em] text-brand-muted dark:text-slate-400\">' + escapeHtml(header.label) + '</th>');
                            currentGroupKey = null;
                            return;
                        }

                        hasGrouped = true;
                        var groupKey = match[1];
                        var childKey = match[2];
                        var groupLabel = toTitleCase(groupKey);
                        var childLabel = toTitleCase(childKey);

                        if (currentGroupKey === groupKey && topCells.length > 0) {
                            var last = topCells[topCells.length - 1];
                            var colspanMatch = last.match(/data-colspan=\"(\\d+)\"/);
                            var currentColspan = colspanMatch ? parseInt(colspanMatch[1], 10) : 1;
                            var nextColspan = currentColspan + 1;
                            topCells[topCells.length - 1] = '<th data-colspan=\"' + nextColspan + '\" colspan=\"' + nextColspan + '\" class=\"px-3 py-3 text-center text-xs font-semibold uppercase tracking-[0.12em] text-brand-muted dark:text-slate-400\">' + escapeHtml(groupLabel) + '</th>';
                        } else {
                            topCells.push('<th data-colspan=\"1\" colspan=\"1\" class=\"px-3 py-3 text-center text-xs font-semibold uppercase tracking-[0.12em] text-brand-muted dark:text-slate-400\">' + escapeHtml(groupLabel) + '</th>');
                            currentGroupKey = groupKey;
                        }

                        bottomCells.push('<th class=\"px-3 py-3 text-left text-xs font-semibold uppercase tracking-[0.12em] text-brand-muted dark:text-slate-400\">' + escapeHtml(childLabel) + '</th>');
                    });

                    var table = document.getElementById('khs-table');
                    if (!table) return leafHeaders;

                    if (!hasGrouped) {
                        table.innerHTML = '<thead><tr>' + leafHeaders.map(function(header) {
                            return '<th class=\"px-3 py-3 text-left text-xs font-semibold uppercase tracking-[0.12em] text-brand-muted dark:text-slate-400\">' + escapeHtml(header.label) + '</th>';
                        }).join('') + '<th class=\"px-3 py-3 text-center text-xs font-semibold uppercase tracking-[0.12em] text-brand-muted dark:text-slate-400\">Aksi</th></tr></thead>';
                    } else {
                        topCells.push('<th rowspan=\"2\" class=\"px-3 py-3 text-center text-xs font-semibold uppercase tracking-[0.12em] text-brand-muted dark:text-slate-400\">Aksi</th>');
                        table.innerHTML = '<thead><tr>' + topCells.join('') + '</tr><tr>' + bottomCells.join('') + '</tr></thead>';
                    }

                    return leafHeaders;
                }

                function buildColumns(leafHeaders) {
                    var columns = leafHeaders.map(function(header) {
                        if (header.key === '__row_number') {
                            return {
                                data: 'row_number',
                                className: 'whitespace-nowrap',
                                orderable: false
                            };
                        }

                        return {
                            data: null,
                            className: 'whitespace-nowrap',
                            orderable: false,
                            render: function(_, __, row) {
                                return escapeHtml(row.cells && row.cells[header.key] ? row.cells[header.key] : '');
                            }
                        };
                    });

                    columns.push({
                        data: null,
                        className: 'whitespace-nowrap text-center',
                        orderable: false,
                        render: function(_, __, row) {
                            var jsonRow = JSON.stringify(row).replace(/"/g, '&quot;');
                            return `
                                <div class="flex items-center justify-center gap-2">
                                    <button type="button" onclick="editKhsRecord(${jsonRow})" class="inline-flex items-center justify-center rounded-lg bg-amber-500/10 px-2 py-1.5 text-xs font-medium text-amber-600 transition hover:bg-amber-500/20 dark:bg-amber-500/20 dark:text-amber-400">
                                        Edit
                                    </button>
                                    <button type="button" onclick="deleteKhsRecord(${row.id})" class="inline-flex items-center justify-center rounded-lg bg-red-500/10 px-2 py-1.5 text-xs font-medium text-red-600 transition hover:bg-red-500/20 dark:bg-red-500/20 dark:text-red-400">
                                        Hapus
                                    </button>
                                </div>
                            `;
                        }
                    });

                    return columns;
                }

                function loadTable() {
                    if (dataTable) {
                        dataTable.destroy();
                    }

                    var leafHeaders = buildTableHeader(tab);

                    dataTable = $('#khs-table').DataTable({
                        processing: true,
                        serverSide: true,
                        searching: true,
                        ordering: false,
                        lengthChange: true,
                        pageLength: 25,
                        scrollX: true,
                        autoWidth: false,
                        dom: '<"top"lf>rt<"bottom"ip><"clear">',
                        ajax: {
                            url: dataUrl
                        },
                        columns: buildColumns(leafHeaders),
                        language: {
                            search: 'Cari:',
                            zeroRecords: 'Data tidak ditemukan',
                            paginate: {
                                previous: 'Prev',
                                next: 'Next'
                            }
                        }
                    });
                }

                loadTable();

                window.openKhsModal = function() {
                    $('#khs-id').val('');
                    $('#modal-title').text('Tambah Data KHS');
                    
                    var dynamicFields = '';
                    var leafHeaders = buildLeafHeaders(tab);
                    
                    leafHeaders.forEach(function(header) {
                        if (header.key !== '__row_number') {
                            dynamicFields += `
                                <div>
                                    <label class="mb-1.5 block text-xs font-medium text-slate-700 dark:text-slate-300">${escapeHtml(header.label)}</label>
                                    <input type="text" name="${escapeHtml(header.key)}" class="block w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm text-slate-800 transition focus:border-brand-primary focus:outline-none focus:ring-1 focus:ring-brand-primary dark:border-brand-darkLine dark:bg-[#0f1728] dark:text-white" placeholder="Masukkan ${escapeHtml(header.label)}">
                                </div>
                            `;
                        }
                    });
                    
                    $('#dynamic-form-fields').html(dynamicFields);
                    $('#khs-modal').removeClass('hidden');
                };

                window.closeKhsModal = function() {
                    $('#khs-modal').addClass('hidden');
                };

                window.editKhsRecord = function(row) {
                    $('#khs-id').val(row.id);
                    $('#modal-title').text('Edit Data KHS');
                    
                    var dynamicFields = '';
                    var leafHeaders = buildLeafHeaders(tab);
                    
                    leafHeaders.forEach(function(header) {
                        if (header.key !== '__row_number') {
                            var val = row.cells && row.cells[header.key] ? row.cells[header.key] : '';
                            dynamicFields += `
                                <div>
                                    <label class="mb-1.5 block text-xs font-medium text-slate-700 dark:text-slate-300">${escapeHtml(header.label)}</label>
                                    <input type="text" name="${escapeHtml(header.key)}" value="${escapeHtml(val)}" class="block w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm text-slate-800 transition focus:border-brand-primary focus:outline-none focus:ring-1 focus:ring-brand-primary dark:border-brand-darkLine dark:bg-[#0f1728] dark:text-white" placeholder="Masukkan ${escapeHtml(header.label)}">
                                </div>
                            `;
                        }
                    });
                    
                    $('#dynamic-form-fields').html(dynamicFields);
                    $('#khs-modal').removeClass('hidden');
                };

                window.submitKhsForm = function(e) {
                    e.preventDefault();
                    var id = $('#khs-id').val();
                    var url = id ? `/khs/${id}` : '/khs';
                    var method = id ? 'PUT' : 'POST';
                    var formData = $('#khs-form').serialize();
                    
                    var submitBtn = $('#btn-submit-khs');
                    submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i> Menyimpan...');

                    $.ajax({
                        url: url,
                        type: method,
                        data: formData + '&_token={{ csrf_token() }}',
                        success: function(res) {
                            closeKhsModal();
                            dataTable.ajax.reload(null, false);
                            Swal.fire({
                                icon: 'success',
                                title: 'Berhasil',
                                text: res.message,
                                timer: 1500,
                                showConfirmButton: false
                            });
                        },
                        error: function(err) {
                            var msg = err.responseJSON && err.responseJSON.message ? err.responseJSON.message : 'Terjadi kesalahan sistem';
                            Swal.fire({
                                icon: 'error',
                                title: 'Gagal',
                                text: msg
                            });
                        },
                        complete: function() {
                            submitBtn.prop('disabled', false).text('Simpan');
                        }
                    });
                };

                window.deleteKhsRecord = function(id) {
                    Swal.fire({
                        title: 'Hapus Data?',
                        text: "Data KHS akan dihapus permanen, tidak bisa dikembalikan!",
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#ef4444',
                        cancelButtonColor: '#64748b',
                        confirmButtonText: 'Ya, Hapus!',
                        cancelButtonText: 'Batal'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            $.ajax({
                                url: `/khs/${id}`,
                                type: 'DELETE',
                                data: {
                                    _token: '{{ csrf_token() }}'
                                },
                                success: function(res) {
                                    dataTable.ajax.reload(null, false);
                                    Swal.fire({
                                        icon: 'success',
                                        title: 'Dihapus!',
                                        text: res.message,
                                        timer: 1500,
                                        showConfirmButton: false
                                    });
                                },
                                error: function(err) {
                                    var msg = err.responseJSON && err.responseJSON.message ? err.responseJSON.message : 'Gagal menghapus data';
                                    Swal.fire({
                                        icon: 'error',
                                        title: 'Error',
                                        text: msg
                                    });
                                }
                            });
                        }
                    });
                };
                $('#khs-import-form').on('submit', function(e) {
                    e.preventDefault();
                    var forData = new FormData(this);
                    var fileInput = $(this).find('input[type="file"]')[0];
                    var fileName = fileInput.files[0] ? fileInput.files[0].name : "KHS Database";

                    var xhr = new XMLHttpRequest();
                    xhr.open('POST', $(this).attr('action'), true);
                    xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');

                    window.trackUpload(xhr, fileName);

                    xhr.onload = function() {
                        if (xhr.status >= 200 && xhr.status < 300) {
                            var res = JSON.parse(xhr.responseText);
                            Swal.fire({
                                icon: 'success',
                                title: 'Import Berhasil',
                                text: res.message || 'Data KHS telah diperbarui.',
                                confirmButtonText: 'Oke'
                            }).then(() => {
                                window.location.reload();
                            });
                        } else {
                            var errorMsg = 'Gagal mengunggah file';
                            try {
                                var res = JSON.parse(xhr.responseText);
                                errorMsg = res.message || errorMsg;
                            } catch(e) {}
                            Swal.fire({ icon: 'error', title: 'Gagal', text: errorMsg });
                        }
                    };

                    xhr.send(forData);
                });
            })();
        </script>
    @endif
@endsection
