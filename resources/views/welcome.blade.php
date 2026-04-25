<!DOCTYPE html>
<html lang="en" class="h-full">

<head>
   <meta charset="UTF-8">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Taskgate Sign In</title>
   <script>
      (function() {
         var storedTheme = localStorage.getItem('taskgate-theme');
         var prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;

         if (storedTheme === 'dark' || (!storedTheme && prefersDark)) {
            document.documentElement.classList.add('dark');
         }
      })();
   </script>
   @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body
   class="h-full bg-brand-surfaceSoft font-sans text-brand-loginText transition-colors duration-300 dark:bg-brand-surfaceDark dark:text-white">
   <div class="min-h-screen">
      <main
         class="grid min-h-screen w-full overflow-hidden bg-white transition-colors duration-300 lg:grid-cols-2 dark:bg-brand-cardDark">
         <section
            class="relative flex items-center justify-center bg-white px-6 py-8 sm:px-10 lg:px-16 xl:px-24 dark:bg-brand-cardDark">
            <div class="absolute inset-y-0 right-0 hidden w-px bg-brand-loginLine lg:block dark:bg-brand-loginLineDark"></div>

            <div class="w-full max-w-[460px]">
               <div class="flex items-center justify-between gap-4">
                  <button id="theme-toggle-mobile" type="button" aria-label="Toggle theme"
                     class="flex h-10 w-10 items-center justify-center rounded-full border border-brand-loginLine bg-white text-brand-loginText transition hover:border-brand-vibrantBlue hover:text-brand-vibrantBlue dark:border-brand-loginLineDark dark:bg-[#0b1428] dark:text-white lg:hidden">
                     <svg xmlns="http://www.w3.org/2000/svg" class="theme-icon-moon h-4.5 w-4.5" viewBox="0 0 24 24"
                        fill="none" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round"
                           d="M21 12.79A9 9 0 1111.21 3c-.05.33-.08.66-.08 1a7 7 0 009.87 6.36z" />
                     </svg>
                     <svg xmlns="http://www.w3.org/2000/svg" class="theme-icon-sun hidden h-4.5 w-4.5"
                        viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round"
                           d="M12 3v2.25M12 18.75V21M3 12h2.25M18.75 12H21M5.636 5.636l1.591 1.591M16.773 16.773l1.591 1.591M5.636 18.364l1.591-1.591M16.773 7.227l1.591-1.591M15.75 12A3.75 3.75 0 1112 8.25 3.75 3.75 0 0115.75 12z" />
                     </svg>
                  </button>
               </div>

               <div
                  class="mt-12 rounded-[28px] bg-[linear-gradient(135deg,#1a206d_0%,#20297f_100%)] px-6 py-6 text-white shadow-[0_16px_50px_rgba(22,27,103,0.22)] sm:px-7 lg:hidden">
                  <p class="text-xs uppercase tracking-[0.35em] text-white/60">Taskgate</p>
                  <h2 class="mt-4 text-3xl font-semibold tracking-[-0.04em]">Welcome back</h2>
                  <p class="mt-2 max-w-xs text-sm text-white/70">Sign in to continue managing your workspace and project
                     flow.</p>
               </div>

               <div class="mt-12 sm:mt-16">
                  <h1
                     class="text-[2.5rem] font-semibold leading-none tracking-[-0.03em] text-brand-loginText dark:text-white">
                     Sign In</h1>
                  <p class="mt-3 text-sm text-brand-loginMuted dark:text-slate-400">Enter your email and password to sign in!
                  </p>
               </div>

               <form action="{{ url('/login') }}" method="POST"
                  class="mt-8 space-y-5 rounded-[28px] border border-brand-loginLine/80 bg-white/80 p-6 shadow-[0_18px_45px_rgba(140,152,180,0.12)] backdrop-blur sm:p-7 dark:border-brand-loginLineDark dark:bg-[#0b1428]/70 dark:shadow-none">
                  @csrf
                  <div>
                     <label for="email" class="mb-2 block text-sm font-medium text-brand-loginText dark:text-slate-100">
                        Email<span class="text-red-500">*</span>
                     </label>
                     <input id="email" name="email" type="email" placeholder="Enter your email" required
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
                        <option value="" disabled selected>Select your role</option>
                        <option value="admin">Admin</option>
                        <option value="warehouse">Warehouse</option>
                        <option value="finance">Finance</option>
                        <option value="procurement">Procurement</option>
                        <option value="konstruksi">Konstruksi</option>
                        <option value="commerce">Commerce</option>
                     </select>
                  </div>



                  <div
                     class="flex flex-col gap-3 pt-1 text-sm text-brand-loginMuted sm:flex-row sm:items-center sm:justify-between dark:text-slate-400">
                     <label class="inline-flex items-center gap-3">
                        <input type="checkbox"
                           class="h-4 w-4 rounded border-[#c7d0e2] text-brand-vibrantBlue focus:ring-brand-vibrantBlue dark:border-[#33415d] dark:bg-[#0b1428]">
                        <span>Keep me logged in</span>
                     </label>

                     <a href="#" class="font-medium text-brand-vibrantBlue transition hover:text-brand-vibrantBlueDeep">Forgot
                        password?</a>
                  </div>

                  <button type="submit"
                     class="flex h-12 w-full items-center justify-center rounded-xl bg-brand-vibrantBlue text-sm font-semibold text-white shadow-[0_14px_30px_rgba(76,111,255,0.35)] transition hover:bg-brand-vibrantBlueDeep hover:shadow-[0_18px_34px_rgba(62,88,244,0.4)]">
                     Sign In
                  </button>
               </form>
            </div>
         </section>

         <aside
            class="relative hidden overflow-hidden bg-brand-navy lg:flex lg:min-h-full lg:items-center lg:justify-center">
            <div
               class="absolute inset-0 bg-[linear-gradient(rgba(255,255,255,0.04)_1px,transparent_1px),linear-gradient(90deg,rgba(255,255,255,0.04)_1px,transparent_1px)] bg-[size:34px_34px]">
            </div>
            <div
               class="absolute inset-0 bg-[radial-gradient(circle_at_top_right,rgba(255,255,255,0.09),transparent_18%),radial-gradient(circle_at_bottom_left,rgba(255,255,255,0.06),transparent_22%)]">
            </div>
            <div class="absolute inset-0 bg-[radial-gradient(circle_at_center,rgba(255,255,255,0.04),transparent_34%)]">
            </div>

            <div class="absolute right-12 top-6 grid grid-cols-2 gap-2 opacity-80">
               <span class="h-8 w-8 rounded-[4px] bg-[#252D82]"></span>
               <span class="h-8 w-8 rounded-[4px] bg-[#2B3490]"></span>
               <span class="h-8 w-8 rounded-[4px] bg-[#21297A]"></span>
            </div>

            <div class="absolute bottom-10 left-12 grid grid-cols-2 gap-2 opacity-80">
               <span class="h-8 w-8 rounded-[4px] bg-[#232B7F]"></span>
               <span class="h-8 w-8 rounded-[4px] bg-[#2A338D]"></span>
               <span class="h-8 w-8 rounded-[4px] bg-[#1F2776]"></span>
            </div>

            <div class="relative z-10 max-w-xl px-10 text-center">
               <div class="mx-auto mb-8 h-px w-24 bg-white/15"></div>
               <p class="text-[3.7rem] font-medium tracking-[-0.04em] text-white xl:text-[4.75rem]">TASKGATE</p>
               <p class="mx-auto mt-5 max-w-md text-sm leading-7 text-white/60 xl:text-base">
                  Organize access, teams, and project handoffs in one clean workflow.
               </p>
            </div>

            <button id="theme-toggle" type="button" aria-label="Toggle theme"
               class="group absolute bottom-6 right-6 flex h-12 w-12 items-center justify-center rounded-full bg-brand-vibrantBlue text-white shadow-lg shadow-[#0b1458]/35 transition hover:bg-brand-vibrantBlueDeep focus:outline-none focus:ring-2 focus:ring-white/40">
               <svg id="theme-icon-moon" xmlns="http://www.w3.org/2000/svg"
                  class="theme-icon-moon h-5 w-5 transition group-hover:rotate-12" viewBox="0 0 24 24" fill="none"
                  stroke="currentColor" stroke-width="1.8">
                  <path stroke-linecap="round" stroke-linejoin="round"
                     d="M21 12.79A9 9 0 1111.21 3c-.05.33-.08.66-.08 1a7 7 0 009.87 6.36z" />
               </svg>
               <svg id="theme-icon-sun" xmlns="http://www.w3.org/2000/svg"
                  class="theme-icon-sun hidden h-5 w-5 transition group-hover:rotate-12" viewBox="0 0 24 24"
                  fill="none" stroke="currentColor" stroke-width="1.8">
                  <path stroke-linecap="round" stroke-linejoin="round"
                     d="M12 3v2.25M12 18.75V21M3 12h2.25M18.75 12H21M5.636 5.636l1.591 1.591M16.773 16.773l1.591 1.591M5.636 18.364l1.591-1.591M16.773 7.227l1.591-1.591M15.75 12A3.75 3.75 0 1112 8.25 3.75 3.75 0 0115.75 12z" />
               </svg>
            </button>
         </aside>
      </main>
   </div>

   <script>
      (function() {
         var root = document.documentElement;
         var toggles = [
            document.getElementById('theme-toggle'),
            document.getElementById('theme-toggle-mobile')
         ].filter(Boolean);

         function syncIcons() {
            var isDark = root.classList.contains('dark');
            document.querySelectorAll('.theme-icon-moon').forEach(function(icon) {
               icon.classList.toggle('hidden', isDark);
            });
            document.querySelectorAll('.theme-icon-sun').forEach(function(icon) {
               icon.classList.toggle('hidden', !isDark);
            });
         }

         syncIcons();

         toggles.forEach(function(toggle) {
            toggle.addEventListener('click', function() {
               var isDark = root.classList.toggle('dark');
               localStorage.setItem('taskgate-theme', isDark ? 'dark' : 'light');
               syncIcons();
            });
         });
      })();
   </script>
</body>

</html>
