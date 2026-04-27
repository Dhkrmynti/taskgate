@extends('layouts.auth')

@section('title', '500 - Server Error')

@section('content')
<div class="mt-12 sm:mt-16 text-center lg:text-left">
    <div class="mb-6 inline-flex h-20 w-20 items-center justify-center rounded-3xl bg-amber-500/10 text-amber-500 dark:bg-amber-500/20">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
        </svg>
    </div>
    
    <h1 class="text-6xl font-bold tracking-tighter text-brand-loginText dark:text-white">500</h1>
    <h2 class="mt-4 text-2xl font-semibold text-brand-loginText dark:text-slate-100">Kesalahan Internal Server</h2>
    <p class="mt-4 text-slate-500 dark:text-slate-400 leading-7">Terjadi kendala teknis saat memproses permintaan Anda. Tim pengembang kami telah menerima laporan ini dan sedang melakukan penanganan.</p>
    
    <div class="mt-10">
        <a href="{{ url('/dashboard') }}" class="inline-flex h-12 items-center justify-center rounded-xl bg-brand-vibrantBlue px-8 text-sm font-semibold text-white shadow-[0_14px_30px_rgba(76,111,255,0.35)] transition hover:bg-brand-vibrantBlueDeep hover:shadow-[0_18px_34px_rgba(62,88,244,0.4)]">
            Kembali ke Dashboard
        </a>
    </div>
</div>
@endsection

@section('sidebar_text', 'Pemeliharaan berkala dilakukan untuk memastikan sistem tetap berjalan dengan performa yang optimal.')
