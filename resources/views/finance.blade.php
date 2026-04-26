@extends('layouts.dashboard')

@section('title', 'Taskgate Finance')
@section('date_label', 'Finance')
@section('hello_name', 'Finance Data')
@section('hero_subtitle', 'Import file Finance (Excel) lalu kelola data pada tab Dashboard Finance.')

@section('content')

    <div class="space-y-3">
        <!-- Setup Error -->
        @if (!empty($setupError))
            <section class="rounded-xl border border-[#f2c8cc] bg-[#fff8f8] p-3 shadow-soft dark:border-[#5c2d34] dark:bg-[#2a171b]">
                <p class="text-sm font-semibold text-[#9f2b3a] dark:text-[#f0c8cf]">{{ $setupError }}</p>
                <p class="mt-2 text-sm text-[#7a3b43] dark:text-[#d2aeb4]">Silakan jalankan migration.</p>
            </section>
        @endif

        <section class="rounded-xl border border-[#e6e7f0] bg-white p-3 shadow-soft dark:border-brand-darkLine dark:bg-brand-darkPanel">
            <div class="flex flex-wrap items-center justify-between gap-4">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.18em] text-brand-muted dark:text-slate-500">Import Excel</p>
                    <h2 class="mt-2 text-2xl font-semibold text-brand-text dark:text-white">Upload Finance Database</h2>
                    <p class="mt-2 text-sm text-brand-muted dark:text-slate-400">File terbaru akan mengganti seluruh data Finance yang sebelumnya telah diimport.</p>
                </div>
                @if ($latestBatch)
                    <div class="rounded-xl border border-[#d9dceb] bg-[#fbfbfe] px-4 py-3 text-xs text-brand-muted dark:border-brand-darkLine dark:bg-[#161f35] dark:text-slate-400">
                        <p>File Terakhir: <span class="font-semibold text-brand-text dark:text-white">{{ $latestBatch->original_file_name }}</span></p>
                        <p class="mt-1">Diimport pada: {{ $latestBatch->imported_at?->format('Y-m-d H:i') ?: '-' }}</p>
                        <p class="mt-1">Total Baris: {{ number_format((int) $latestBatch->total_rows, 0, ',', '.') }}</p>
                    </div>
                @endif
            </div>

            @if (session('status'))
                <div class="mt-4 rounded-xl bg-green-50 p-4 text-sm text-green-700 dark:bg-green-900/20 dark:text-green-400">
                    {{ session('status') }}
                </div>
            @endif

            <form id="finance-import-form" action="{{ route('finance.import') }}" method="POST" enctype="multipart/form-data" class="mt-5 flex flex-wrap items-center gap-3">
                @csrf
                <input name="finance_file" type="file" accept=".xlsx,.xls" required class="block w-full sm:w-auto rounded-xl border border-[#d9dceb] bg-white px-3 py-2.5 text-sm file:mr-4 file:rounded-lg file:border-0 file:bg-[#f3f4f6] file:px-3 file:py-2 file:text-sm file:font-medium dark:border-brand-darkLine dark:bg-[#0f1728] dark:text-white dark:file:bg-[#1c2540]">
                <button type="submit" class="inline-flex h-11 w-full sm:w-auto items-center justify-center rounded-xl bg-brand-text px-5 text-sm font-semibold text-white transition hover:bg-[#2f3542] dark:bg-white dark:text-brand-darkBg">
                    Import Excel
                </button>
            </form>
        </section>

        <section class="rounded-xl border border-[#e6e7f0] bg-white p-3 shadow-soft dark:border-brand-darkLine dark:bg-brand-darkPanel">
            @if($tabs->isEmpty())
                <div class="rounded-xl border border-dashed border-[#d9dceb] bg-[#fbfbfe] px-5 py-6 text-sm text-brand-muted dark:border-brand-darkLine dark:bg-[#161f35] dark:text-slate-400">
                    Belum ada data Finance. Silakan upload file Excel terlebih dahulu.
                </div>
            @else
                <div class="flex flex-wrap gap-2 mb-4" id="finance-tab-buttons">
                    @foreach ($tabs as $tab)
                        <button type="button" data-tab="{{ $tab['key'] }}" class="finance-tab-btn rounded-xl border px-4 py-2.5 text-sm font-semibold transition {{ $activeTab === $tab['key'] ? 'border-[#bfc4d6] bg-[#f3f4f6] text-brand-text dark:border-[#465066] dark:bg-[#273142] dark:text-white' : 'border-[#e1e4f0] text-brand-muted hover:border-[#c8cedf] hover:bg-[#f8f9fd] dark:border-brand-darkLine dark:text-slate-400 dark:hover:bg-[#1b2438]' }}">
                            {{ $tab['label'] }} ({{ number_format((int) $tab['row_count'], 0, ',', '.') }})
                        </button>
                    @endforeach
                </div>
                
                <div class="overflow-hidden rounded-xl border border-[#e6e7f0] dark:border-brand-darkLine">
                    <div class="overflow-x-auto p-4">
                        <table id="finance-table" class="min-w-full text-sm"></table>
                    </div>
                </div>
            @endif
        </section>
    </div>

    @if($tabs->isNotEmpty())
        <script type="module">
            (function() {
                var tabs = @json($tabs->values());
                var activeTabKey = @json($activeTab);
                var dataUrl = @json(route('finance.data'));
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

                function formatValue(value, key, tabKey) {
                    if (value === null || value === undefined || value === '') return '';
                    
                    var cleanValue = String(value).trim();
                    var num = parseFloat(cleanValue.replace(',', '.'));

                    // Percentage formatting (blue text, comma decimal, % suffix)
                    if (key.toLowerCase().includes('growth')) {
                        if (isNaN(num)) return escapeHtml(cleanValue);
                        // Multiply by 100 if the value is a decimal representing percentage (e.g. 1.43 -> 143%)
                        var percentageValue = num * 100;
                        var formattedPct = percentageValue.toFixed(2).replace('.', ',') + '%';
                        return formattedPct;
                    }

                    // Indonesian number formatting (dot as thousand separator)
                    var amountKeywords = ['budget', 'cogs', 'release', 'revenue', 'gpm', 'procstplan', 'costs', 'revs', 'available'];
                    var isAmount = amountKeywords.some(function(kw) { return key.toLowerCase().includes(kw); });
                    
                    if (isAmount && !isNaN(num) && cleanValue !== '') {
                        // Check if it's likely a number (not an ID or something else)
                        // If it's pure numeric or has only dots/commas
                        if (/^-?[\d.,]+$/.test(cleanValue) || /^-?\d+$/.test(cleanValue)) {
                            return new Intl.NumberFormat('id-ID').format(num);
                        }
                    }

                    return escapeHtml(cleanValue);
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
                    var groupedRegex = /^(.*)_(plan|actual|act|bud|release|realease|material|jasa|perizinan|total|variance|var)$/i;
                    var currentGroupKey = null;

                    leafHeaders.forEach(function(header) {
                        var match = header.key.match(groupedRegex);

                        if (!match || header.key === '__row_number') {
                            topCells.push('<th rowspan=\"2\" class=\"text-left text-xs font-semibold uppercase tracking-[0.12em] text-brand-muted dark:text-slate-400\">' + escapeHtml(header.label) + '</th>');
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
                            topCells[topCells.length - 1] = '<th data-colspan=\"' + nextColspan + '\" colspan=\"' + nextColspan + '\" class=\"text-center text-xs font-semibold uppercase tracking-[0.12em] text-brand-muted dark:text-slate-400\">' + escapeHtml(groupLabel) + '</th>';
                        } else {
                            topCells.push('<th data-colspan=\"1\" colspan=\"1\" class=\"text-center text-xs font-semibold uppercase tracking-[0.12em] text-brand-muted dark:text-slate-400\">' + escapeHtml(groupLabel) + '</th>');
                            currentGroupKey = groupKey;
                        }

                        bottomCells.push('<th class=\"text-left text-xs font-semibold uppercase tracking-[0.12em] text-brand-muted dark:text-slate-400\">' + escapeHtml(childLabel) + '</th>');
                    });

                    var table = document.getElementById('finance-table');
                    if (!table) return leafHeaders;

                    if (!hasGrouped) {
                        table.innerHTML = '<thead><tr>' + leafHeaders.map(function(header) {
                            return '<th class=\"text-left text-xs font-semibold uppercase tracking-[0.12em] text-brand-muted dark:text-slate-400\">' + escapeHtml(header.label) + '</th>';
                        }).join('') + '</tr></thead>';
                    } else {
                        table.innerHTML = '<thead><tr>' + topCells.join('') + '</tr><tr>' + bottomCells.join('') + '</tr></thead>';
                    }
                    
                    return leafHeaders;
                }

                function buildColumns(leafHeaders, tabKey) {
                    return leafHeaders.map(function(header) {
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
                                var val = row.cells && row.cells[header.key] ? row.cells[header.key] : '';
                                return formatValue(val, header.key, tabKey);
                            }
                        };
                    });
                }

                function activateTabButton(tabKey) {
                    document.querySelectorAll('.finance-tab-btn').forEach(function(button) {
                        var isActive = button.getAttribute('data-tab') === tabKey;
                        button.classList.toggle('border-[#bfc4d6]', isActive);
                        button.classList.toggle('bg-[#f3f4f6]', isActive);
                        button.classList.toggle('text-brand-text', isActive);
                        button.classList.toggle('dark:border-[#465066]', isActive);
                        button.classList.toggle('dark:bg-[#273142]', isActive);
                        button.classList.toggle('dark:text-white', isActive);

                        button.classList.toggle('border-[#e1e4f0]', !isActive);
                        button.classList.toggle('text-brand-muted', !isActive);
                        button.classList.toggle('hover:border-[#c8cedf]', !isActive);
                        button.classList.toggle('hover:bg-[#f8f9fd]', !isActive);
                        button.classList.toggle('dark:border-brand-darkLine', !isActive);
                        button.classList.toggle('dark:text-slate-400', !isActive);
                        button.classList.toggle('dark:hover:bg-[#1b2438]', !isActive);
                    });
                }

                function loadTable(tabKey) {
                    var tab = tabs.find(function(item) {
                        return item.key === tabKey;
                    });

                    if (!tab) {
                        return;
                    }

                    activeTabKey = tabKey;
                    activateTabButton(tabKey);

                    if (dataTable) {
                        dataTable.destroy();
                    }

                    var leafHeaders = buildTableHeader(tab);

                    if (!document.getElementById('finance-table')) return;

                    dataTable = $('#finance-table').DataTable({
                        processing: true,
                        serverSide: true,
                        searching: true,
                        ordering: false,
                        lengthChange: true,
                        pageLength: 25,
                        autoWidth: false,
                        scrollX: true,
                        ajax: {
                            url: dataUrl,
                            data: function(payload) {
                                payload.tab = tabKey;
                            }
                        },
                        dom: '<"top"lf>rt<"bottom"ip><"clear">',
                        columns: buildColumns(leafHeaders, tabKey),
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

                document.querySelectorAll('.finance-tab-btn').forEach(function(button) {
                    button.addEventListener('click', function() {
                        var tabKey = button.getAttribute('data-tab');
                        if (tabKey) {
                            loadTable(tabKey);
                        }
                    });
                });

                if (activeTabKey || tabs.length > 0) {
                    loadTable(activeTabKey || tabs[0].key);
                }

                // Listen for real-time import notifications
                if (typeof Echo !== 'undefined') {
                    Echo.channel('imports')
                        .listen('.import.status.updated', (e) => {
                            if (e.type === 'finance') {
                                Swal.fire({
                                    title: e.status === 'success' ? 'Berhasil!' : 'Gagal!',
                                    text: e.message,
                                    icon: e.status,
                                    confirmButtonText: 'OKE',
                                    confirmButtonColor: '#111827'
                                }).then(() => {
                                    if (e.status === 'success') {
                                        window.location.reload();
                                    }
                                });
                            }
                        });
                }
            })();
        </script>
    @else
        <script type="module">
            (function() {
                var activeTabKey = @json($activeTab);
                
                function activateTabButton(tabKey) {
                    document.querySelectorAll('.finance-tab-btn').forEach(function(button) {
                        var isActive = button.getAttribute('data-tab') === tabKey;
                        button.classList.toggle('border-[#bfc4d6]', isActive);
                        button.classList.toggle('bg-[#f3f4f6]', isActive);
                        button.classList.toggle('text-brand-text', isActive);
                        button.classList.toggle('dark:border-[#465066]', isActive);
                        button.classList.toggle('dark:bg-[#273142]', isActive);
                        button.classList.toggle('dark:text-white', isActive);

                        button.classList.toggle('border-[#e1e4f0]', !isActive);
                        button.classList.toggle('text-brand-muted', !isActive);
                        button.classList.toggle('hover:border-[#c8cedf]', !isActive);
                        button.classList.toggle('hover:bg-[#f8f9fd]', !isActive);
                        button.classList.toggle('dark:border-brand-darkLine', !isActive);
                        button.classList.toggle('dark:text-slate-400', !isActive);
                        button.classList.toggle('dark:hover:bg-[#1b2438]', !isActive);
                    });

                    document.querySelectorAll('.tab-content').forEach(function(el) {
                        el.classList.add('hidden');
                        el.classList.remove('block');
                    });
                    var currentContent = document.getElementById('tab-content-' + tabKey);
                    if (currentContent) {
                        currentContent.classList.remove('hidden');
                        currentContent.classList.add('block');
                    }
                }

                document.querySelectorAll('.finance-tab-btn').forEach(function(button) {
                    button.addEventListener('click', function() {
                        var tabKey = button.getAttribute('data-tab');
                        if (tabKey) {
                            activateTabButton(tabKey);
                        }
                    });
                });

                activateTabButton(activeTabKey);
            })();
        </script>
    @endif
@push('scripts')
<script type="module">
    (function() {
        var importForm = document.getElementById('finance-import-form');
        if (importForm) {
            importForm.addEventListener('submit', function(e) {
                e.preventDefault();
                var formData = new FormData(this);
                var fileInput = this.querySelector('input[type="file"]');
                var fileName = fileInput.files[0] ? fileInput.files[0].name : "Finance Data";

                var xhr = new XMLHttpRequest();
                xhr.open('POST', this.getAttribute('action'), true);
                xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');

                if (window.trackUpload) {
                    window.trackUpload(xhr, fileName);
                }

                xhr.onload = function() {
                    if (xhr.status >= 200 && xhr.status < 300) {
                        try {
                            var res = JSON.parse(xhr.responseText);
                            Swal.fire({
                                icon: 'success',
                                title: 'Upload Berhasil',
                                text: res.message || 'File sedang diproses oleh sistem.',
                                confirmButtonText: 'OKE'
                            }).then(() => {
                                window.location.reload();
                            });
                        } catch(err) {
                            window.location.reload();
                        }
                    } else {
                        var errorMsg = 'Gagal mengunggah file';
                        try {
                            var res = JSON.parse(xhr.responseText);
                            errorMsg = res.message || errorMsg;
                        } catch(e) {}
                        Swal.fire({ icon: 'error', title: 'Gagal', text: errorMsg });
                    }
                };

                xhr.send(formData);
            });
        }
    })();
</script>
@endpush
@endsection
