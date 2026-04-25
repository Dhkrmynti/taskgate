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
                    <input
                        name="{{ $field['name'] }}"
                        type="{{ $field['type'] }}"
                        step="{{ $field['type'] === 'number' ? '0.01' : '' }}"
                        value="{{ old($field['name']) }}"
                        placeholder="{{ $field['placeholder'] }}"
                        class="h-12 w-full rounded-xl border border-[#d9dceb] bg-white px-4 text-sm outline-none dark:border-brand-darkLine dark:bg-[#0f1728] dark:text-white">
                @endforeach
                <button type="submit" class="h-12 rounded-xl bg-brand-text px-5 text-sm font-semibold text-white transition hover:bg-[#2f3542] dark:bg-white dark:text-brand-darkBg">
                    Save
                </button>
            </form>
        </section>

        <section class="rounded-xl border border-[#e6e7f0] bg-white p-3 shadow-soft dark:border-brand-darkLine dark:bg-brand-darkPanel">

            <div class="mt-3 overflow-hidden rounded-xl border border-[#e6e7f0] dark:border-brand-darkLine">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-[#e6e7f0] dark:divide-brand-darkLine">
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
                                    <form id="master-data-{{ $resource }}-{{ $item->id }}" action="{{ route('master-data.update', [$resource, $item->id]) }}" method="POST" class="hidden">
                                        @csrf
                                        @method('PUT')
                                    </form>
                                    @foreach ($config['fields'] as $field)
                                        <td class="px-5 py-4">
                                            <input
                                                form="master-data-{{ $resource }}-{{ $item->id }}"
                                                name="{{ $field['name'] }}"
                                                type="{{ $field['type'] }}"
                                                step="{{ $field['type'] === 'number' ? '0.01' : '' }}"
                                                value="{{ $item->{$field['name']} }}"
                                                class="h-11 w-full rounded-xl border border-[#d9dceb] bg-white px-4 text-sm outline-none dark:border-brand-darkLine dark:bg-[#0f1728] dark:text-white">
                                        </td>
                                    @endforeach
                                    <td class="px-5 py-4 text-sm text-brand-muted dark:text-slate-400">
                                        {{ \Illuminate\Support\Carbon::parse($item->updated_at)->format('Y-m-d H:i') }}
                                    </td>
                                    <td class="px-5 py-4">
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
@endsection
