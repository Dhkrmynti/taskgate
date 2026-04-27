@extends('layouts.auth')

@section('title', '404 - Halaman Tidak Ditemukan')

@section('content')
<div class="mt-12 sm:mt-16 text-center lg:text-left">
    <div class="mb-6 inline-flex h-20 w-20 items-center justify-center rounded-3xl bg-brand-vibrantBlue/10 text-brand-vibrantBlue dark:bg-brand-vibrantBlue/20">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
    </div>
    
    <h1 class="text-6xl font-bold tracking-tighter text-brand-loginText dark:text-white">404</h1>
    <h2 class="mt-4 text-2xl font-semibold text-brand-loginText dark:text-slate-100">Halaman Tidak Ditemukan</h2>
    <p class="mt-4 text-slate-500 dark:text-slate-400 leading-7">Maaf, halaman yang Anda cari tidak tersedia atau telah dipindahkan. Silakan kembali ke Dashboard untuk melanjutkan aktivitas Anda.</p>
    
    <div class="mt-10">
        <a href="{{ url('/dashboard') }}" class="inline-flex h-12 items-center justify-center rounded-xl bg-brand-vibrantBlue px-8 text-sm font-semibold text-white shadow-[0_14px_30px_rgba(76,111,255,0.35)] transition hover:bg-brand-vibrantBlueDeep hover:shadow-[0_18px_34px_rgba(62,88,244,0.4)]">
            Kembali ke Dashboard
        </a>
    </div>
</div>
@endsection

@section('sidebar_text', 'Sistem Taskgate membantu Anda mengelola alur kerja proyek secara terstruktur dan efisien.')
