@extends('layouts.auth')

@section('title', '503 - Sistem Dalam Pemeliharaan')

@section('content')
<div class="mt-12 sm:mt-16 text-center lg:text-left">
    <div class="mb-6 inline-flex h-20 w-20 items-center justify-center rounded-3xl bg-blue-500/10 text-blue-500 dark:bg-blue-500/20">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M11 4a2 2 0 114 0v1a1 1 0 001 1h3a1 1 0 011 1v3a1 1 0 01-1 1h-1a2 2 0 100 4h1a1 1 0 011 1v3a1 1 0 01-1 1h-3a1 1 0 01-1-1v-1a2 2 0 10-4 0v1a1 1 0 01-1 1H7a1 1 0 01-1-1v-3a1 1 0 00-1-1H4a2 2 0 110-4h1a1 1 0 001-1V7a1 1 0 011-1h3a1 1 0 001-1V4z" />
        </svg>
    </div>
    
    <h1 class="text-6xl font-bold tracking-tighter text-brand-loginText dark:text-white">503</h1>
    <h2 class="mt-4 text-2xl font-semibold text-brand-loginText dark:text-slate-100">Sistem Dalam Pemeliharaan</h2>
    <p class="mt-4 text-slate-500 dark:text-slate-400 leading-7">Maaf, sistem saat ini sedang dalam proses pemeliharaan berkala untuk meningkatkan layanan kami. Mohon tunggu beberapa saat, kami akan segera kembali online.</p>
    
    <div class="mt-10">
        <button onclick="window.location.reload()" class="inline-flex h-12 items-center justify-center rounded-xl bg-brand-vibrantBlue px-8 text-sm font-semibold text-white shadow-[0_14px_30px_rgba(76,111,255,0.35)] transition hover:bg-brand-vibrantBlueDeep hover:shadow-[0_18px_34px_rgba(62,88,244,0.4)]">
            Segarkan Halaman
        </button>
    </div>
</div>
@endsection

@section('sidebar_text', 'Kami sedang bekerja di balik layar untuk memberikan pengalaman terbaik bagi Anda.')
