@extends('layouts.auth')

@section('title', 'Create New Password - Taskgate')

@section('sidebar_text', 'Your project security is our priority. Set a strong new password to continue.')

@section('content')
   <div class="mt-12 sm:mt-16">
      <h1 class="text-[2.5rem] font-semibold leading-none tracking-[-0.03em] text-brand-loginText dark:text-white">
         New Password</h1>
      <p class="mt-3 text-sm text-brand-loginMuted dark:text-slate-400">Please enter your new password below.</p>
   </div>

   <form action="{{ route('password.update') }}" method="POST"
      class="mt-8 space-y-5 rounded-[28px] border border-brand-loginLine/80 bg-white/80 p-6 shadow-[0_18px_45px_rgba(140,152,180,0.12)] backdrop-blur sm:p-7 dark:border-brand-loginLineDark dark:bg-[#0b1428]/70 dark:shadow-none">
      @csrf
      <input type="hidden" name="token" value="{{ $token }}">

      @if ($errors->any())
         <div class="mb-5 animate-shake rounded-xl border border-red-100 bg-red-50 p-4 dark:border-red-900/30 dark:bg-red-900/20">
            <p class="text-sm font-medium text-red-800 dark:text-red-300">{{ $errors->first() }}</p>
         </div>
      @endif

      <div>
         <label for="email" class="mb-2 block text-sm font-medium text-brand-loginText dark:text-slate-100">Email Address</label>
         <input id="email" name="email" type="email" value="{{ $email ?? old('email') }}" required readonly
            class="h-12 w-full rounded-xl border border-[#d9e0ef] bg-slate-50 px-4 text-sm text-slate-500 outline-none dark:border-[#2c3956] dark:bg-[#0b1428]/40 dark:text-slate-400">
      </div>

      <div>
         <label for="password" class="mb-2 block text-sm font-medium text-brand-loginText dark:text-slate-100">New Password<span class="text-red-500">*</span></label>
         <input id="password" name="password" type="password" placeholder="••••••••" required autofocus
            class="h-12 w-full rounded-xl border border-[#d9e0ef] bg-white px-4 text-sm text-brand-loginText outline-none transition placeholder:text-[#9da7bc] focus:border-brand-vibrantBlue focus:shadow-field dark:border-[#2c3956] dark:bg-[#0b1428] dark:text-white dark:placeholder:text-slate-500">
      </div>

      <div>
         <label for="password_confirmation" class="mb-2 block text-sm font-medium text-brand-loginText dark:text-slate-100">Confirm Password<span class="text-red-500">*</span></label>
         <input id="password_confirmation" name="password_confirmation" type="password" placeholder="••••••••" required
            class="h-12 w-full rounded-xl border border-[#d9e0ef] bg-white px-4 text-sm text-brand-loginText outline-none transition placeholder:text-[#9da7bc] focus:border-brand-vibrantBlue focus:shadow-field dark:border-[#2c3956] dark:bg-[#0b1428] dark:text-white dark:placeholder:text-slate-500">
      </div>

      <button type="submit"
         class="flex h-12 w-full items-center justify-center rounded-xl bg-brand-vibrantBlue text-sm font-semibold text-white shadow-[0_14px_30px_rgba(76,111,255,0.35)] transition hover:bg-brand-vibrantBlueDeep hover:shadow-[0_18px_34px_rgba(62,88,244,0.4)]">
         Reset Password
      </button>
   </form>
@endsection
