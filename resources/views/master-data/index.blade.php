@extends('layouts.dashboard')

@section('title', 'Taskgate '.$config['label'])
@section('date_label', 'Master Data')
@section('hello_name', $config['label'])
@section('hero_subtitle', 'Kelola data referensi untuk field select di form Commerce. Saat role system sudah jadi, menu ini bisa dipisahkan lagi.')

@section('content')
    <div class="space-y-3">
        <section class="rounded-xl border border-[#e6e7f0] bg-white p-3 shadow-soft dark:border-brand-darkLine dark:bg-brand-darkPanel">
            <div class="flex items-center justify-between gap-4">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.2em] text-brand-muted dark:text-slate-500">Create {{ $config['label'] }}</p>
                    <h2 class="mt-2 text-2xl font-semibold">Tambah Data Baru</h2>
                </div>
                <div class="rounded-full bg-[#f3f4f6] px-4 py-2 text-sm font-semibold text-brand-muted dark:bg-[#1b2438] dark:text-slate-400">
                    {{ $items->count() }} rows
                </div>
            </div>

            <form action="{{ route('master-data.store', $resource) }}" method="POST" class="mt-6 grid gap-4 {{ count($config['fields']) > 1 ? 'lg:grid-cols-[1fr_1.6fr_0.8fr_auto]' : 'lg:grid-cols-[minmax(0,420px)_auto]' }}">
                @csrf
                @foreach ($config['fields'] as $field)
                    @if ($field['type'] === 'select')
                        <select name="{{ $field['name'] }}" class="h-12 w-full rounded-xl border border-[#d9dceb] bg-white px-4 text-sm outline-none dark:border-brand-darkLine dark:bg-[#0f1728] dark:text-white">
                            @foreach ($field['options'] as $value => $label)
                                <option value="{{ $value }}" {{ old($field['name']) == $value ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    @else
                        <input
                            name="{{ $field['name'] }}"
                            type="{{ $field['type'] }}"
                            step="{{ $field['type'] === 'number' ? '0.01' : '' }}"
                            value="{{ old($field['name']) }}"
                            placeholder="{{ $field['placeholder'] }}"
                            class="h-12 w-full rounded-xl border border-[#d9dceb] bg-white px-4 text-sm outline-none dark:border-brand-darkLine dark:bg-[#0f1728] dark:text-white">
                    @endif
                @endforeach
                <button type="submit" class="h-12 rounded-xl bg-brand-text px-5 text-sm font-semibold text-white transition hover:bg-[#2f3542] dark:bg-white dark:text-brand-darkBg">
                    Save
                </button>
            </form>
        </section>

        <section class="rounded-xl border border-[#e6e7f0] bg-white p-3 shadow-soft dark:border-brand-darkLine dark:bg-brand-darkPanel">

            <style>
                @media (max-width: 1023px) {
                    .responsive-table, .responsive-table thead, .responsive-table tbody, .responsive-table th, .responsive-table td, .responsive-table tr { display: block; }
                    .responsive-table thead { display: none; }
                    .responsive-table tr { margin-bottom: 1rem; border: 1px solid #e6e7f0; border-radius: 1rem; padding: 1rem; background: white; }
                    .dark .responsive-table tr { border-color: #374151; background: #1d2230; }
                    .responsive-table td { border: none !important; padding: 0 0 1rem 0 !important; width: 100% !important; box-shadow: none !important; }
                    .responsive-table td:last-child { padding-bottom: 0 !important; border-top: 1px solid #e6e7f0 !important; padding-top: 1rem !important; margin-top: 0.5rem; }
                    .dark .responsive-table td:last-child { border-top-color: #374151 !important; }
                    .responsive-table td::before { content: attr(data-label); display: block; font-size: 0.7rem; font-weight: 700; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.1em; margin-bottom: 0.5rem; }
                }
            </style>
            <div class="mt-3 overflow-hidden rounded-xl border border-[#e6e7f0] dark:border-brand-darkLine lg:bg-transparent">
                <div class="overflow-x-auto lg:overflow-visible">
                    <table class="min-w-full divide-y divide-[#e6e7f0] dark:divide-brand-darkLine responsive-table">
                        <thead class="bg-[#fbfbfe] dark:bg-[#161f35]">
                            <tr>
                                @foreach ($config['fields'] as $field)
                                    <th class="px-5 py-4 text-left text-xs font-bold uppercase tracking-[0.15em] text-slate-500">{{ $field['label'] }}</th>
                                @endforeach
                                <th class="px-5 py-4 text-left text-xs font-bold uppercase tracking-[0.15em] text-slate-500 w-48">Date</th>
                                <th class="px-5 py-4 text-left text-xs font-bold uppercase tracking-[0.15em] text-slate-500 w-48">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[#e6e7f0] dark:divide-brand-darkLine">
                            @forelse ($items as $item)
                                <tr>
                                    <form id="master-data-{{ $resource }}-{{ $item->id }}" action="{{ route('master-data.update', [$resource, $item->id]) }}" method="POST" class="hidden ajax-update-form">
                                        @csrf
                                        @method('PUT')
                                    </form>
                                    @foreach ($config['fields'] as $field)
                                        <td class="px-5 py-4" data-label="{{ $field['label'] }}">
                                            @if ($field['type'] === 'select')
                                                <select form="master-data-{{ $resource }}-{{ $item->id }}" name="{{ $field['name'] }}" class="h-11 w-full rounded-xl border border-[#d9dceb] bg-white px-4 text-sm outline-none dark:border-brand-darkLine dark:bg-[#0f1728] dark:text-white">
                                                    @foreach ($field['options'] as $value => $label)
                                                        <option value="{{ $value }}" {{ $item->{$field['name']} == $value ? 'selected' : '' }}>{{ $label }}</option>
                                                    @endforeach
                                                </select>
                                            @elseif ($field['type'] === 'password')
                                                <div class="relative">
                                                    <input
                                                        id="pass-{{ $item->id }}"
                                                        form="master-data-{{ $resource }}-{{ $item->id }}"
                                                        name="{{ $field['name'] }}"
                                                        type="password"
                                                        placeholder="********"
                                                        class="h-11 w-full rounded-xl border border-[#d9dceb] bg-white pl-4 pr-12 text-sm outline-none dark:border-brand-darkLine dark:bg-[#0f1728] dark:text-white">
                                                    <button type="button" onclick="togglePasswordVisibility('pass-{{ $item->id }}', this)" class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-brand-vibrantBlue dark:text-slate-500">
                                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 eye-open" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
                                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 eye-closed hidden" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.542-7a10.025 10.025 0 014.132-5.411m0 0L21 21m-1.401-1.401L12 12m0 0L3 3m3.374 3.374L12 12" /></svg>
                                                    </button>
                                                </div>
                                            @else
                                                <input
                                                    form="master-data-{{ $resource }}-{{ $item->id }}"
                                                    name="{{ $field['name'] }}"
                                                    type="{{ $field['type'] }}"
                                                    step="{{ $field['type'] === 'number' ? '0.01' : '' }}"
                                                    value="{{ $item->{$field['name']} }}"
                                                    class="h-11 w-full rounded-xl border border-[#d9dceb] bg-white px-4 text-sm outline-none dark:border-brand-darkLine dark:bg-[#0f1728] dark:text-white">
                                            @endif
                                        </td>
                                    @endforeach
                                    <td class="px-5 py-4 text-sm text-brand-muted dark:text-slate-400" data-label="Terakhir Diubah">
                                        {{ \Illuminate\Support\Carbon::parse($item->updated_at)->format('Y-m-d H:i') }}
                                    </td>
                                    <td class="px-5 py-4" data-label="Aksi">
                                        <div class="flex flex-wrap gap-2">
                                            <button form="master-data-{{ $resource }}-{{ $item->id }}" type="submit" class="inline-flex h-10 items-center rounded-xl border border-[#d9dceb] px-4 text-sm font-semibold text-brand-text transition hover:border-[#9ca3af] dark:border-brand-darkLine dark:text-white">
                                                Save
                                            </button>
                                            <form action="{{ route('master-data.destroy', [$resource, $item->id]) }}" method="POST" data-confirm-form data-confirm-title="Hapus Master Data" data-confirm-message="Data ini akan dihapus permanen. Lanjutkan?">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="inline-flex h-10 items-center rounded-xl border border-[#f2c8cc] px-4 text-sm font-semibold text-[#8b4b53] transition hover:bg-[#fff7f7] dark:border-[#5c2d34] dark:text-[#f3c7cd]">
                                                    Delete
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="{{ count($config['fields']) + 2 }}" class="px-5 py-6 text-sm text-brand-muted dark:text-slate-400">Belum ada data.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </section>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const forms = document.querySelectorAll('.ajax-update-form');
            
            forms.forEach(form => {
                form.addEventListener('submit', async (e) => {
                    e.preventDefault();
                    
                    const submitBtn = document.querySelector(`button[form="${form.id}"]`);
                    const originalText = submitBtn.innerHTML;
                    
                    try {
                        submitBtn.disabled = true;
                        submitBtn.innerHTML = `
                            <svg class="animate-spin h-4 w-4 text-brand-text dark:text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                        `;

                        const formData = new FormData(form);
                        // Add inputs that are connected to this form via "form" attribute
                        document.querySelectorAll(`input[form="${form.id}"], select[form="${form.id}"]`).forEach(input => {
                            formData.append(input.name, input.value);
                        });

                        const response = await fetch(form.action, {
                            method: 'POST',
                            body: formData,
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content
                            }
                        });

                        const result = await response.json();

                        if (response.ok) {
                            if (window.showToast) {
                                window.showToast(result.message, 'success');
                            } else {
                                alert(result.message);
                            }
                        } else {
                            const errorMsg = result.errors ? Object.values(result.errors).flat().join('\n') : result.message;
                            if (window.showToast) {
                                window.showToast(errorMsg, 'error');
                            } else {
                                alert('Error: ' + errorMsg);
                            }
                        }
                    } catch (error) {
                        console.error('Error:', error);
                        alert('Terjadi kesalahan saat menyimpan data.');
                    } finally {
                        submitBtn.disabled = false;
                        submitBtn.innerHTML = originalText;
                    }
                });
            });
        });
    </script>
@endsection
