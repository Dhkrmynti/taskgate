@extends('layouts.dashboard')

@section('title', 'Finance Rekon - Realisasi Jasa')

@section('content')
<div class="container-fluid py-4 min-h-screen bg-[#f8fafc] dark:bg-[#0f172a]">
    <div class="max-w-[1400px] mx-auto">
        <!-- Header -->
        <div class="flex items-center justify-between mb-8">
            <div class="flex items-center gap-4">
                <div class="bg-blue-600 p-3 rounded-xl shadow-lg shadow-blue-500/20">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <div>
                    <h1 class="text-2xl font-black text-slate-900 dark:text-white tracking-tight">Finance Rekon (TGIDRF)</h1>
                    <p class="text-sm text-slate-500 font-medium">Konsolidasi Realisasi Jasa untuk {{ count($rekons) }} Commerce Rekons</p>
                </div>
            </div>
            <a href="{{ route('tasks.index', ['role' => 'finance']) }}" class="group flex items-center gap-2 px-4 py-2 rounded-xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-sm font-bold text-slate-600 dark:text-slate-400 transition hover:bg-slate-50 dark:hover:bg-slate-700">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 transition group-hover:-translate-x-1" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
                </svg>
                Kembali
            </a>
        </div>

        <form id="finance-rekon-form" enctype="multipart/form-data" class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
            @csrf
            @foreach($rekonIds as $id)
                <input type="hidden" name="rekon_ids[]" value="{{ $id }}">
            @endforeach

            <!-- Primary Content -->
            <div class="lg:col-span-8 space-y-3">
                <!-- Selected Rekons List -->
                <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 p-3 shadow-soft">
                    <h3 class="text-xs font-black uppercase text-slate-400 tracking-widest mb-4">Mengkonsolidasi TGIDRC:</h3>
                    <div class="flex flex-wrap gap-2">
                        @foreach($rekons as $rekon)
                            <div class="flex items-center gap-2 px-3 py-1.5 rounded-lg bg-blue-50 dark:bg-blue-900/20 text-[10px] font-bold text-blue-600 dark:text-blue-400 border border-blue-100 dark:border-blue-900/30">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                {{ $rekon->id }}
                            </div>
                        @endforeach
                    </div>
                </div>

                <!-- Jasa BoQ Table -->
                <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 p-3 shadow-soft overflow-hidden">
                    <div class="px-6 py-5 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between">
                        <h3 class="text-sm font-black text-slate-900 dark:text-white uppercase tracking-wider">Daftar Pekerjaan Jasa (Designator J-)</h3>
                        <span class="px-2 py-1 bg-emerald-50 text-emerald-600 text-[10px] font-black rounded-lg border border-emerald-100 dark:bg-emerald-900/20 dark:border-emerald-900/30">FILTERED: JASA ONLY</span>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="bg-slate-50 dark:bg-slate-800/50">
                                    <th class="px-6 py-4 text-[10px] font-black text-slate-500 uppercase tracking-widest">Designator</th>
                                    <th class="px-6 py-4 text-[10px] font-black text-slate-500 uppercase tracking-widest">Description</th>
                                    <th class="px-6 py-4 text-[10px] font-black text-slate-500 uppercase tracking-widest text-center">Qty Planning</th>
                                    <th class="px-6 py-4 text-[10px] font-black text-slate-500 uppercase tracking-widest text-center">Qty Realisasi</th>
                                    <th class="px-6 py-4 text-[10px] font-black text-slate-500 uppercase tracking-widest text-right">Unit Price</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-50 dark:divide-slate-800">
                                @foreach($aggregatedBoqs as $item)
                                @php $key = $item->designator . '|' . ($item->description ?? ''); @endphp
                                <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/30 transition-colors">
                                    <td class="px-6 py-4">
                                        <span class="text-xs font-black text-blue-600 dark:text-blue-400 font-mono">{{ $item->designator }}</span>
                                        <input type="hidden" name="boq[{{ $key }}][designator]" value="{{ $item->designator }}">
                                        <input type="hidden" name="boq[{{ $key }}][description]" value="{{ $item->description }}">
                                        <input type="hidden" name="boq[{{ $key }}][volume_planning]" value="{{ $item->volume_planning }}">
                                        <input type="hidden" name="boq[{{ $key }}][price_planning]" value="{{ $item->price_planning }}">
                                    </td>
                                    <td class="px-6 py-4">
                                        <p class="text-xs font-semibold text-slate-700 dark:text-slate-300 line-clamp-2 max-w-[300px]">{{ $item->description }}</p>
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <span class="text-xs font-bold text-slate-400">{{ $item->volume_planning }}</span>
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <input type="number" name="boq[{{ $key }}][volume_realisasi]" 
                                               class="w-20 bg-slate-50 dark:bg-slate-800 border-none rounded-lg text-center text-xs font-black text-slate-900 dark:text-white focus:ring-2 focus:ring-blue-500" 
                                               value="{{ $item->volume_realisasi }}" min="0">
                                        <input type="hidden" name="boq[{{ $key }}][price_realisasi]" value="{{ $item->price_realisasi }}">
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        <span class="text-xs font-black text-slate-900 dark:text-white">Rp {{ number_format($item->price_planning, 0, ',', '.') }}</span>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Sidebar / Order Summary -->
            <div class="lg:col-span-4 space-y-3">
                <!-- Summary Card -->
                <div class="bg-[#1e293b] dark:bg-slate-900 rounded-[2rem] p-8 text-white shadow-2xl relative overflow-hidden">
                    <div class="absolute -top-10 -right-10 w-40 h-40 bg-blue-500/10 rounded-full blur-3xl"></div>
                    <div class="absolute -bottom-10 -left-10 w-40 h-40 bg-emerald-500/10 rounded-full blur-3xl"></div>
                    
                    <h3 class="text-xs font-black uppercase tracking-[0.2em] text-slate-400 mb-8 flex items-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                        </svg>
                        Ringkasan Realisasi
                    </h3>

                    <div class="space-y-3 relative z-10">
                        <div class="flex justify-between items-center bg-white/5 p-4 rounded-xl border border-white/5">
                            <span class="text-xs font-medium text-slate-400">Total Planning (Jasa)</span>
                            <span class="text-sm font-bold text-slate-300">Rp {{ number_format($totalPlanning, 0, ',', '.') }}</span>
                        </div>
                        
                        <div class="flex justify-between items-center p-4">
                            <span class="text-xs font-black uppercase text-blue-400 tracking-wider">Total Realisasi</span>
                            <div class="text-right">
                                <span class="block text-2xl font-black text-white">Rp {{ number_format($totalRealisasi, 0, ',', '.') }}</span>
                                <span class="text-[10px] text-emerald-400 font-bold uppercase">Ready to Process</span>
                            </div>
                        </div>

                        <div class="pt-6 border-t border-white/10 space-y-3">
                            <div>
                                <label class="block text-[10px] font-black uppercase text-slate-400 mb-2">Nomor APM</label>
                                <input type="text" name="apm_number" required placeholder="Masukkan APM..." 
                                    class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-3 text-sm text-white placeholder:text-slate-600 focus:ring-2 focus:ring-blue-500 outline-none transition">
                            </div>

                            <div>
                                <label class="block text-[10px] font-black uppercase text-slate-400 mb-2">Bukti Pembayaran / Evidence</label>
                                <div class="relative group">
                                    <input type="file" name="evidence_file" required accept=".pdf,.jpg,.jpeg,.png" class="hidden" id="evidence-input">
                                    <label for="evidence-input" class="flex items-center gap-3 w-full bg-white/5 border border-white/10 border-dashed rounded-xl px-4 py-4 text-xs font-bold text-slate-400 cursor-pointer group-hover:border-blue-500/50 group-hover:bg-blue-500/5 transition">
                                        <div class="p-2 bg-white/5 rounded-lg">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path d="M4 16v1a2 2 0 002 2h12a2 2 0 002-2v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                                            </svg>
                                        </div>
                                        <span id="file-name">Upload (.pdf, .jpg, .png)</span>
                                    </label>
                                </div>
                            </div>
                        </div>

                        <button type="submit" class="w-full bg-blue-600 hover:bg-blue-500 text-white font-black py-4 rounded-2xl shadow-xl shadow-blue-600/20 transition-all flex items-center justify-center gap-2 group active:scale-[0.98]">
                            SUBMIT REALISASI JASA
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 transition group-hover:translate-x-1" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                            </svg>
                        </button>
                    </div>
                </div>

                <!-- Info Alert -->
                <div class="bg-blue-50 dark:bg-blue-900/20 rounded-2xl p-6 border border-blue-100 dark:border-blue-900/30">
                    <div class="flex gap-4">
                        <div class="text-blue-600 dark:text-blue-400 shrink-0">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="text-[11px] leading-relaxed text-blue-700 dark:text-blue-400 font-medium">
                            <p class="font-black uppercase mb-1 tracking-wider">Informasi Finance</p>
                            Sesuai aturan bisnis, Finance hanya melakukan konsolidasi pada item bertanda <span class="font-black italic">Designator J-</span>. Item material akan tetap dimonitor namun pembayarannya berada di manajemen terpisah.
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script type="module">
    document.getElementById('evidence-input').addEventListener('change', function(e) {
        document.getElementById('file-name').textContent = e.target.files[0] ? e.target.files[0].name : 'Upload (.pdf, .jpg, .png)';
    });

    document.getElementById('finance-rekon-form').addEventListener('submit', function(e) {
        e.preventDefault();
        
        let formData = new FormData(this);
        
        Swal.fire({
            title: '<span class="text-2xl font-black">Konfirmasi Pembayaran?</span>',
            html: '<p class="text-sm text-slate-500">Pastikan Nomor APM dan nominal realisasi sudah benar.</p>',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#2563eb',
            cancelButtonColor: '#ef4444',
            confirmButtonText: 'YA, SUBMIT TGIDRF',
            cancelButtonText: 'BATAL',
            customClass: {
                popup: 'rounded-[2rem] border-0',
                confirmButton: 'rounded-xl font-black px-6 py-3',
                cancelButton: 'rounded-xl font-black px-6 py-3'
            },
            showLoaderOnConfirm: true,
            preConfirm: () => {
                return fetch('{{ route("tasks.finance.rekon-process") }}', {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(response => {
                    if (!response.ok) {
                        return response.json().then(err => { throw new Error(err.error || 'Terjadi kesalahan sistem') });
                    }
                    return response.json();
                })
                .catch(error => {
                    Swal.showValidationMessage(`Gagal: ${error}`);
                });
            },
            allowOutsideClick: () => !Swal.isLoading()
        }).then((result) => {
            if (result.isConfirmed && result.value.success) {
                Swal.fire({
                    title: '<span class="font-black">Berhasil!</span>',
                    text: result.value.message,
                    icon: 'success',
                    customClass: {
                        popup: 'rounded-[2rem] border-0',
                        confirmButton: 'rounded-xl font-black px-6 py-3'
                    }
                }).then(() => {
                    window.location.href = result.value.redirect;
                });
            }
        });
    });
</script>
@endpush
@endsection
