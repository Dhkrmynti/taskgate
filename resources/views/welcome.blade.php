@extends('layouts.auth')

@section('title', 'Taskgate Sign In')

@section('content')
   <div class="mt-12 sm:mt-16">
      <h1 class="text-[2.5rem] font-semibold leading-none tracking-[-0.03em] text-brand-loginText dark:text-white">
         Sign In</h1>
      <p class="mt-3 text-sm text-brand-loginMuted dark:text-slate-400">Enter your email and password to sign in!</p>
   </div>

   <form action="{{ url('/login') }}" method="POST"
      class="mt-8 space-y-5 rounded-[28px] border border-brand-loginLine/80 bg-white/80 p-6 shadow-[0_18px_45px_rgba(140,152,180,0.12)] backdrop-blur sm:p-7 dark:border-brand-loginLineDark dark:bg-[#0b1428]/70 dark:shadow-none">
      @csrf

      @if ($errors->any())
         <div class="mb-5 animate-shake rounded-xl border border-red-100 bg-red-50 p-4 dark:border-red-900/30 dark:bg-red-900/20">
            <div class="flex items-center gap-3">
               <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-red-100 text-red-600 dark:bg-red-900/40 dark:text-red-400">
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                     <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" />
                  </svg>
               </div>
               <p class="auth-error-message text-sm font-medium text-red-800 dark:text-red-300">
                  {{ $errors->first() }}
               </p>
            </div>
         </div>
      @endif
      <div>
         <label for="email" class="mb-2 block text-sm font-medium text-brand-loginText dark:text-slate-100">
            Email<span class="text-red-500">*</span>
         </label>
         <input id="email" name="email" type="email" value="{{ old('email') }}" placeholder="Enter your email" required
            class="h-12 w-full rounded-xl border border-[#d9e0ef] bg-white px-4 text-sm text-brand-loginText outline-none transition placeholder:text-[#9da7bc] focus:border-brand-vibrantBlue focus:shadow-field dark:border-[#2c3956] dark:bg-[#0b1428] dark:text-white dark:placeholder:text-slate-500">
      </div>

      <div>
         <label for="password" class="mb-2 block text-sm font-medium text-brand-loginText dark:text-slate-100">
            Password<span class="text-red-500">*</span>
         </label>
         <input id="password" name="password" type="password" value="hello123" required
            class="h-12 w-full rounded-xl border border-[#d9e0ef] bg-white px-4 text-sm text-brand-loginText outline-none transition placeholder:text-[#9da7bc] focus:border-brand-vibrantBlue focus:shadow-field dark:border-[#2c3956] dark:bg-[#0b1428] dark:text-white dark:placeholder:text-slate-500">
      </div>

      <div>
         <label for="role" class="mb-2 block text-sm font-medium text-brand-loginText dark:text-slate-100">
            Role<span class="text-red-500">*</span>
         </label>
         <select id="role" name="role" required
            class="h-12 w-full rounded-xl border border-[#d9e0ef] bg-white px-4 text-sm text-brand-loginText outline-none transition focus:border-brand-vibrantBlue focus:shadow-field dark:border-[#2c3956] dark:bg-[#0b1428] dark:text-white">
            <option value="" disabled {{ old('role') ? '' : 'selected' }}>Select your role</option>
            <option value="admin" {{ old('role') == 'admin' ? 'selected' : '' }}>Admin</option>
            <option value="warehouse" {{ old('role') == 'warehouse' ? 'selected' : '' }}>Warehouse</option>
            <option value="finance" {{ old('role') == 'finance' ? 'selected' : '' }}>Finance</option>
            <option value="procurement" {{ old('role') == 'procurement' ? 'selected' : '' }}>Procurement</option>
            <option value="konstruksi" {{ old('role') == 'konstruksi' ? 'selected' : '' }}>Konstruksi</option>
            <option value="commerce" {{ old('role') == 'commerce' ? 'selected' : '' }}>Commerce</option>
         </select>
      </div>

      <div class="flex flex-col gap-3 pt-1 text-sm text-brand-loginMuted sm:flex-row sm:items-center sm:justify-between dark:text-slate-400">
         <label class="inline-flex items-center gap-3">
            <input type="checkbox" name="remember" id="remember" {{ old('remember') ? 'checked' : '' }}
               class="h-4 w-4 rounded border-[#c7d0e2] text-brand-vibrantBlue focus:ring-brand-vibrantBlue dark:border-[#33415d] dark:bg-[#0b1428]">
            <span>Keep me logged in</span>
         </label>

         <a href="{{ route('password.request') }}" class="font-medium text-brand-vibrantBlue transition hover:text-brand-vibrantBlueDeep">Forgot password?</a>
      </div>

      <button type="submit"
         class="flex h-12 w-full items-center justify-center rounded-xl bg-brand-vibrantBlue text-sm font-semibold text-white shadow-[0_14px_30px_rgba(76,111,255,0.35)] transition hover:bg-brand-vibrantBlueDeep hover:shadow-[0_18px_34px_rgba(62,88,244,0.4)]">
         Sign In
      </button>
   </form>
@endsection

@push('scripts')
   <script>
      document.addEventListener('DOMContentLoaded', () => {
         const errorText = document.querySelector('.auth-error-message');
         if (errorText) {
            const match = errorText.innerText.match(/(\d+)\sdetik/);
            if (match) {
               let seconds = parseInt(match[1]);
               const timer = setInterval(() => {
                  seconds--;
                  if (seconds <= 0) {
                     clearInterval(timer);
                     errorText.innerHTML = '<span class="text-green-600 dark:text-green-400">Silakan coba lagi sekarang.</span>';
                  } else {
                     errorText.innerText = errorText.innerText.replace(/\d+\sdetik/, seconds + " detik");
                  }
               }, 1000);
            }
         }
      });
   </script>
@endpush
