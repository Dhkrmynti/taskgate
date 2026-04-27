<!DOCTYPE html>
<html lang="en" class="h-full">

<head>
   <meta charset="UTF-8">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>@yield('title', 'Taskgate')</title>
   <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
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

<body class="h-full bg-brand-surfaceSoft font-sans text-brand-loginText transition-colors duration-300 dark:bg-brand-surfaceDark dark:text-white">
   <div class="min-h-screen">
      <main class="grid min-h-screen w-full overflow-hidden bg-white transition-colors duration-300 lg:grid-cols-2 dark:bg-brand-cardDark">
         <section class="relative flex items-center justify-center bg-white px-6 py-8 sm:px-10 lg:px-16 xl:px-24 dark:bg-brand-cardDark">
            <div class="absolute inset-y-0 right-0 hidden w-px bg-brand-loginLine lg:block dark:bg-brand-loginLineDark"></div>

            <div class="w-full max-w-[460px]">
               <div class="flex items-center justify-end gap-4">
                  <!-- Global floating theme toggle used instead -->
               </div>

               @yield('content')
            </div>
         </section>

         <aside class="relative hidden overflow-hidden bg-brand-navy lg:flex lg:min-h-full lg:items-center lg:justify-center">
            <div class="absolute inset-0 bg-[linear-gradient(rgba(255,255,255,0.04)_1px,transparent_1px),linear-gradient(90deg,rgba(255,255,255,0.04)_1px,transparent_1px)] bg-[size:34px_34px]"></div>
            <div class="absolute inset-0 bg-[radial-gradient(circle_at_top_right,rgba(255,255,255,0.09),transparent_18%),radial-gradient(circle_at_bottom_left,rgba(255,255,255,0.06),transparent_22%)]"></div>
            
            <div class="relative z-10 max-w-xl px-10 text-center">
               <div class="mx-auto mb-8 h-px w-24 bg-white/15"></div>
               <p class="text-[3.7rem] font-medium tracking-[-0.04em] text-white xl:text-[4.75rem]">TASKGATE</p>
               <p class="mx-auto mt-5 max-w-md text-sm leading-7 text-white/60 xl:text-base">
                  @yield('sidebar_text', 'Organize access, teams, and project handoffs in one clean workflow.')
               </p>
            </div>
         </aside>
      </main>

      <button id="theme-toggle" type="button" aria-label="Toggle theme"
         class="group fixed bottom-6 right-6 z-50 flex h-12 w-12 items-center justify-center rounded-full bg-brand-vibrantBlue text-white shadow-lg shadow-[#0b1458]/35 transition hover:bg-brand-vibrantBlueDeep focus:outline-none focus:ring-2 focus:ring-white/40">
         <svg id="theme-icon-moon" xmlns="http://www.w3.org/2000/svg" class="theme-icon-moon h-5 w-5 transition group-hover:rotate-12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
            <path stroke-linecap="round" stroke-linejoin="round" d="M21 12.79A9 9 0 1111.21 3c-.05.33-.08.66-.08 1a7 7 0 009.87 6.36z" />
         </svg>
         <svg id="theme-icon-sun" xmlns="http://www.w3.org/2000/svg" class="theme-icon-sun hidden h-5 w-5 transition group-hover:rotate-12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v2.25M12 18.75V21M3 12h2.25M18.75 12H21M5.636 5.636l1.591 1.591M16.773 16.773l1.591 1.591M5.636 18.364l1.591-1.591M16.773 7.227l1.591-1.591M15.75 12A3.75 3.75 0 1112 8.25 3.75 3.75 0 0115.75 12z" />
         </svg>
      </button>
   </div>

   <script>
      (function() {
         var root = document.documentElement;
         var toggle = document.getElementById('theme-toggle');
         function syncIcons() {
            var isDark = root.classList.contains('dark');
            document.querySelectorAll('.theme-icon-moon').forEach(i => i.classList.toggle('hidden', isDark));
            document.querySelectorAll('.theme-icon-sun').forEach(i => i.classList.toggle('hidden', !isDark));
         }
         syncIcons();
         if (toggle) {
            toggle.addEventListener('click', () => {
               var isDark = root.classList.toggle('dark');
               localStorage.setItem('taskgate-theme', isDark ? 'dark' : 'light');
               syncIcons();
            });
         }

         window.togglePasswordVisibility = function(inputId, btn) {
            const input = document.getElementById(inputId);
            const eyeOpen = btn.querySelector('.eye-open');
            const eyeClosed = btn.querySelector('.eye-closed');
            
            if (input.type === 'password') {
               input.type = 'text';
               eyeOpen.classList.add('hidden');
               eyeClosed.classList.remove('hidden');
            } else {
               input.type = 'password';
               eyeOpen.classList.remove('hidden');
               eyeClosed.classList.add('hidden');
            }
         };
      })();
   </script>
   @stack('scripts')
</body>

</html>
