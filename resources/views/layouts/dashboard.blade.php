<!DOCTYPE html>
<html lang="en" class="h-full">

<head>
   <meta charset="UTF-8">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>@yield('title', 'Taskgate Dashboard')</title>
   <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script>
        // Theme initialization (must run immediately)
        (function() {
             var storedTheme = localStorage.getItem('taskgate-theme');
             var prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
             if (storedTheme === 'dark' || (!storedTheme && prefersDark)) {
                document.documentElement.classList.add('dark');
             }
             
             var sidebarCollapsed = localStorage.getItem('taskgate-sidebar-collapsed');
             if (sidebarCollapsed === 'true') {
                document.documentElement.classList.add('sidebar-collapsed');
             }
        })();

       document.addEventListener('DOMContentLoaded', function() {
          const sidebarGroups = document.querySelectorAll('details[data-sidebar-group]');
          sidebarGroups.forEach(group => {
             const label = group.querySelector('.menu-text')?.textContent.trim();
             if (!label) return;
             const storageKey = 'sidebar_group_' + label.toLowerCase().replace(/\s+/g, '_');
             const savedState = localStorage.getItem(storageKey);
             if (savedState !== null) {
                if (savedState === 'true') group.setAttribute('open', '');
                else group.removeAttribute('open');
             }
             group.addEventListener('toggle', function() {
                localStorage.setItem(storageKey, group.open);
             });
          });
       });
    </script>
   <style>
      :root {
         --app-bg: #f5f5f5;
         --panel-bg: rgba(255, 255, 255, 0.92);
         --panel-strong: #ffffff;
         --panel-border: #e5e7eb;
         --panel-text: #111827;
         --panel-muted: #6b7280;
         --sidebar-bg: rgba(255, 255, 255, 0.96);
         --sidebar-hover: #f9fafb;
         --sidebar-active-border: #d1d5db;
      }

      .dark {
         --app-bg: #151821;
         --panel-bg: rgba(29, 34, 48, 0.9);
         --panel-strong: #1d2230;
         --panel-border: #374151;
         --panel-text: #f6f4ef;
         --panel-muted: #99a1b3;
         --sidebar-bg: rgba(22, 26, 36, 0.95);
         --sidebar-hover: rgba(37, 43, 59, 0.95);
         --sidebar-active-border: rgba(239, 91, 72, 0.32);
      }

      * {
         scrollbar-width: thin;
         scrollbar-color: #d1d5db transparent;
      }

      *::-webkit-scrollbar {
         width: 6px;
         height: 6px;
      }

      *::-webkit-scrollbar-track {
         background: transparent;
      }

      *::-webkit-scrollbar-thumb {
         background-color: #d1d5db;
         border-radius: 99px;
      }

      *::-webkit-scrollbar-thumb:hover {
         background-color: #9ca3af;
      }

      .dark * {
         scrollbar-color: #374151 transparent;
      }

      .dark *::-webkit-scrollbar-thumb {
         background-color: #374151;
      }

      .dark *::-webkit-scrollbar-thumb:hover {
         background-color: #4b5563;
      }

      body {
         background: var(--app-bg);
         color: var(--panel-text);
      }

      .sidebar-scroll {
         scrollbar-width: none; /* Firefox */
         -ms-overflow-style: none; /* IE/Edge */
      }

      .sidebar-scroll::-webkit-scrollbar {
         display: none; /* Chrome/Safari/Webkit */
      }

      .dark .sidebar-scroll {
         scrollbar-width: none;
      }

      .toast-enter {
         animation: toast-in 220ms ease-out forwards;
      }

      .toast-exit {
         animation: toast-out 180ms ease-in forwards;
      }

      @keyframes toast-in {
         from {
            opacity: 0;
            transform: translateY(-12px) scale(0.98);
         }

         to {
            opacity: 1;
            transform: translateY(0) scale(1);
         }
      }

      @keyframes toast-out {
         from {
            opacity: 1;
            transform: translateY(0) scale(1);
         }

         to {
            opacity: 0;
            transform: translateY(-10px) scale(0.98);
         }
      }

      .choices {
         margin-bottom: 0;
         font-size: 0.875rem;
      }

      .choices__inner {
         min-height: 46px;
         border-radius: 1rem;
         border: 1px solid var(--panel-border);
         background: rgba(255, 255, 255, 0.9);
         padding: 8px 12px;
         font-size: 0.875rem;
         color: var(--panel-text);
      }

      .choices__list--dropdown,
      .choices__list[aria-expanded] {
         border-radius: 1rem;
         border: 1px solid var(--panel-border);
         background: var(--panel-strong);
         box-shadow: 0 24px 60px rgba(79, 61, 43, 0.12);
         overflow: hidden;
      }

      .choices__list--dropdown .choices__item--selectable.is-highlighted,
      .choices__list[aria-expanded] .choices__item--selectable.is-highlighted {
         background: #f3f4f6;
         color: var(--panel-text);
      }

      .choices__input {
         background-color: transparent !important;
         font-size: 0.875rem !important;
         color: var(--panel-text) !important;
         padding: 4px 8px !important;
         margin-bottom: 8px !important;
         border-radius: 0.5rem !important;
         border: 1px solid var(--panel-border) !important;
      }

      .choices__list--single,
      .choices__item,
      .choices__placeholder {
         color: var(--panel-text);
      }

      .dark .choices__inner {
         background: #1f2937;
         border-color: #374151;
         color: #f6f4ef;
      }

      .dark .choices__input {
         background-color: #111827 !important;
         border-color: #374151 !important;
         color: #f6f4ef !important;
      }

      .dark .choices__placeholder {
         color: #9ca3af;
         opacity: 0.8;
      }

      .dark .choices__list--dropdown,
      .dark .choices__list[aria-expanded] {
         background: #1f2937;
         border-color: #374151;
         color: #f6f4ef;
      }

      .dark .choices__list--dropdown .choices__item,
      .dark .choices__list[aria-expanded] .choices__item,
      .dark .choices__list--single,
      .dark .choices__placeholder {
         color: #f6f4ef;
      }

      .dark .choices__list--dropdown .choices__item--selectable.is-highlighted,
      .dark .choices__list[aria-expanded] .choices__item--selectable.is-highlighted {
         background: #273142;
         color: #f6f4ef;
      }

      .choices[data-type*=select-one]::after {
         right: 16px;
         border-color: var(--panel-muted) transparent transparent;
      }

      .detail-toggle summary::-webkit-details-marker {
         display: none;
      }

      .theme-switch-knob {
         transition: transform 220ms ease, background-color 220ms ease, box-shadow 220ms ease;
      }

      .dark .theme-switch-knob {
         transform: translateX(24px);
      }

      .theme-switch-icon {
         transition: opacity 180ms ease, transform 220ms ease;
      }

      .theme-switch .theme-switch-icon-sun,
      .dark .theme-switch .theme-switch-icon-moon {
         opacity: 0;
         transform: scale(0.9);
      }

      .dark .theme-switch .theme-switch-icon-sun {
         opacity: 1;
         transform: scale(1);
      }

      .sidebar-search-hidden {
         display: none !important;
      }

      /* Sidebar Toggle Transitions */
      #sidebar {
         transition: transform 400ms cubic-bezier(0.4, 0, 0.2, 1), width 400ms cubic-bezier(0.4, 0, 0.2, 1), left 400ms cubic-bezier(0.4, 0, 0.2, 1);
      }

      #main-content, #header {
         transition: margin-left 400ms cubic-bezier(0.4, 0, 0.2, 1), left 400ms cubic-bezier(0.4, 0, 0.2, 1), right 400ms cubic-bezier(0.4, 0, 0.2, 1), padding 400ms cubic-bezier(0.4, 0, 0.2, 1), width 400ms cubic-bezier(0.4, 0, 0.2, 1);
      }

      .sidebar-collapsed #sidebar {
         width: 72px;
      }

      .sidebar-collapsed #sidebar .search-container,
      .sidebar-collapsed #sidebar .profile-box,
      .sidebar-collapsed #sidebar [data-sidebar-group] {
         border-color: transparent !important;
         background-color: transparent !important;
         padding-left: 0 !important;
         padding-right: 0 !important;
         box-shadow: none !important;
      }

      .sidebar-collapsed #sidebar .search-container,
      .sidebar-collapsed #sidebar .menu-text,
      .sidebar-collapsed #sidebar p.uppercase,
      .sidebar-collapsed #sidebar .workspace-text,
      .sidebar-collapsed #sidebar .profile-text,
      .sidebar-collapsed #sidebar summary > span:last-child {
         display: none !important;
      }

      .sidebar-collapsed #sidebar summary,
      .sidebar-collapsed #sidebar [data-sidebar-item] {
         justify-content: center !important;
         padding-left: 0 !important;
         padding-right: 0 !important;
      }
      
      .sidebar-collapsed #sidebar .profile-box > div,
      .sidebar-collapsed #sidebar .brand-container,
      .sidebar-collapsed #sidebar > div:first-child > div:first-child {
         justify-content: center !important;
         padding-left: 0 !important;
         padding-right: 0 !important;
         gap: 0 !important;
      }

      .sidebar-collapsed #sidebar .menu-icon-wrapper {
         margin-right: 0 !important;
      }

      /* Floating Island & Overhaul Styles */
      @media (min-width: 1024px) {
         #sidebar {
            top: 12px;
            bottom: 12px;
            left: 12px;
            height: calc(100vh - 24px);
            border-radius: 16px;
            border: 1px solid var(--panel-border);
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.05), 0 8px 10px -6px rgba(0, 0, 0, 0.05);
         }

         #main-content {
            margin-left: 296px !important;
            padding-top: 12px;
         }

          #header-desktop {
             left: 296px !important;
             top: 0 !important;
             right: 0 !important;
             z-index: 50 !important;
             transition: all 400ms cubic-bezier(0.4, 0, 0.2, 1);
          }

         .sidebar-collapsed #sidebar {
            width: 72px;
         }

         .sidebar-collapsed #main-content {
            margin-left: 96px !important;
         }

          .sidebar-collapsed #header-desktop,
          .sidebar-collapsed #top-shroud {
             left: 96px !important;
             margin-left: 0 !important;
          }
      }

      /* Active Accent Bar */
      a[data-sidebar-item].active-link::before {
         content: "";
         position: absolute;
         left: 0;
         top: 50%;
         transform: translateY(-50%);
         height: 18px;
         width: 3px;
         border-radius: 0 4px 4px 0;
         background: #3b82f6; 
         box-shadow: 2px 0 8px rgba(59, 130, 246, 0.4);
         z-index: 10;
      }

      .sidebar-collapsed a[data-sidebar-item].active-link::before {
         left: 0;
      }

      /* Hover Scale FX */
      [data-sidebar-item]:hover .menu-icon-wrapper {
         transform: scale(1.1);
         transition: transform 200ms ease;
      }

      /* Tooltips for Mini Mode */
      .sidebar-collapsed #sidebar [data-sidebar-item]:hover::after,
      .sidebar-collapsed #sidebar summary:hover::after {
         content: attr(data-sidebar-label);
         position: absolute;
         left: 80px;
         top: 50%;
         transform: translateY(-50%);
         padding: 6px 12px;
         background: #1f2937;
         color: white;
         font-size: 11px;
         font-weight: 500;
         border-radius: 8px;
         white-space: nowrap;
         z-index: 50;
         box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.3);
         text-transform: uppercase;
         letter-spacing: 0.05em;
      }

      .dark #sidebar {
         backdrop-filter: blur(12px);
         -webkit-backdrop-filter: blur(12px);
      }

      .dark #sidebar {
         backdrop-filter: blur(12px);
         -webkit-backdrop-filter: blur(12px);
      }

      .dataTable {
         border-collapse: separate !important;
         border-spacing: 0;
         min-width: 100%;
      }

      .dataTable thead th,
      .dataTable tbody td {
         border-right: 1px solid var(--panel-border) !important;
      }

      .dataTable thead th:first-child,
      .dataTable tbody td:first-child {
         border-left: 1px solid var(--panel-border) !important;
      }

      .dataTable thead th {
         border-bottom: 1px solid #d9dceb !important;
         background-color: #f8fafc;
         box-shadow: inset -1px 0 0 var(--panel-border);
         padding: 0.4rem 0.75rem !important;
         font-size: 0.70rem !important;
         font-weight: 700 !important;
         letter-spacing: 0.12em;
         text-transform: uppercase;
         color: var(--panel-muted);
         text-align: left;
      }

      .dataTable thead tr:last-child th {
         border-bottom: 1px solid #cfd6e4 !important;
      }

      .dataTable tbody td {
         border-bottom: 1px solid #eef0f6 !important;
         box-shadow: inset -1px 0 0 var(--panel-border);
         padding: 0.35rem 0.75rem !important;
         text-align: left;
      }

      /* Allow override classes */
      .dataTable th.text-center, .dataTable td.text-center { text-align: center !important; }
      .dataTable th.text-right, .dataTable td.text-right { text-align: right !important; }

      .dark .dataTable thead th {
         border-bottom-color: var(--panel-border) !important;
         background-color: #1b2438;
         box-shadow: inset -1px 0 0 var(--panel-border);
         color: #94a3b8;
      }

      .dark .dataTable thead tr:first-child th {
         border-bottom-color: #4a5670 !important;
      }

      .dark .dataTable tbody td {
         border-bottom-color: #2d374d !important;
         box-shadow: inset -1px 0 0 var(--panel-border);
      }


      /* Fix for DataTables duplicate headers in scroll container */
      .dataTables_scrollBody thead th, 
      .dataTables_scrollBody thead td {
         padding-top: 0 !important;
         padding-bottom: 0 !important;
         border-top-width: 0 !important;
         border-bottom-width: 0 !important;
         height: 0 !important;
         color: transparent !important;
      }
      .dataTables_scrollBody thead tr {
         height: 0 !important;
      }
      .dataTables_scrollBody thead th .dataTables_sizing {
         height: 0 !important;
         overflow: hidden !important;
      }

      .dataTables_wrapper .top {
         padding: 0 0 1rem 0;
         display: flex;
         flex-wrap: wrap;
         justify-content: space-between;
         align-items: center;
         gap: 0.5rem;
      }

      .dataTables_wrapper .dataTables_length,
      .dataTables_wrapper .dataTables_filter {
         margin-bottom: 0 !important;
         color: var(--panel-muted) !important;
         font-size: 0.8rem !important;
         font-weight: 500 !important;
      }

      .dataTables_wrapper .dataTables_length select,
      .dataTables_wrapper .dataTables_filter input {
         border: 1px solid #d9dceb !important;
         border-radius: 0.75rem !important;
         padding: 0.4rem 0.75rem !important;
         background-color: white !important;
         color: var(--panel-text) !important;
         outline: none !important;
         font-size: 0.8rem !important;
         transition: all 0.2s ease;
      }

      .dark .dataTables_wrapper .dataTables_length select,
      .dark .dataTables_wrapper .dataTables_filter input {
         background-color: #161f35 !important;
         border-color: #2d374d !important;
         color: #f8fafc !important;
      }

      .dataTables_wrapper .bottom {
         display: flex;
         flex-wrap: wrap;
         justify-content: space-between;
         align-items: center;
         gap: 0.5rem;
         padding: 1rem;
         border-top: 1px solid var(--panel-border);
         margin-top: 1rem;
         background-color: #fbfbfe;
         border-radius: 0 0 1rem 1rem;
      }

      .dark .dataTables_wrapper .bottom {
         background-color: #161f35;
      }

      .dataTables_wrapper .dataTables_info {
         font-size: 0.75rem !important;
         color: var(--panel-muted) !important;
         padding: 0 !important;
         font-weight: 500;
      }

      .dataTables_wrapper .dataTables_paginate {
         padding: 0 !important;
         display: flex;
         align-items: center;
         flex-wrap: wrap;
         gap: 0.25rem;
         margin: 0 !important;
      }

      .dataTables_wrapper .dataTables_paginate .paginate_button {
         border: 1px solid #d9dceb !important;
         background: white !important;
         border-radius: 10px !important;
         padding: 6px 12px !important;
         font-size: 0.75rem !important;
         font-weight: 700 !important;
         color: var(--panel-muted) !important;
         transition: all 0.2s ease !important;
         margin: 0 !important;
         cursor: pointer !important;
      }

      .dataTables_wrapper .dataTables_paginate .paginate_button:hover {
         border-color: #cbd5e1 !important;
         background: #f8fafc !important;
         color: var(--panel-text) !important;
      }

      .dataTables_wrapper .dataTables_paginate .paginate_button.current, 
      .dataTables_wrapper .dataTables_paginate .paginate_button.current:hover {
         background: #111827 !important;
         border-color: #111827 !important;
         color: white !important;
      }

      .dark .dataTables_wrapper .dataTables_paginate .paginate_button {
         border-color: #374151 !important;
         background: #1f2937 !important;
         color: white !important;
      }

      .dark .dataTables_wrapper .dataTables_paginate .paginate_button.current {
         background: white !important;
         border-color: white !important;
         color: #111827 !important;
      }
      .dataTables_wrapper .dataTables_scroll {
         width: 100% !important;
         overflow-x: auto;
      }
      .dataTables_wrapper .dataTables_scrollBody {
         overflow-x: auto !important;
      }
      .dataTables_wrapper table.dataTable {
         width: auto !important;
         margin: 0 !important;
      }
      @media (max-width: 640px) {
         .dataTables_wrapper .dataTables_length label {
            font-size: 0 !important;
         }
         .dataTables_wrapper .dataTables_length label select {
            font-size: 0.8rem !important;
         }
         .dataTables_wrapper .dataTables_paginate .paginate_button:not(.previous):not(.next) {
            display: none !important;
         }
         .dataTables_wrapper .dataTables_paginate .paginate_button.previous,
         .dataTables_wrapper .dataTables_paginate .paginate_button.next {
            padding: 7px 16px !important;
            font-size: 0.75rem !important;
         }
         .dataTables_wrapper .bottom {
            justify-content: center;
            gap: 0.75rem;
         }
         .dataTables_wrapper .dataTables_info {
            text-align: center;
            width: 100%;
         }
         .dataTables_wrapper .dataTables_paginate {
            justify-content: center;
         }
         .dataTables_wrapper .top {
            justify-content: space-between;
         }
      }
   </style>
</head>

<body class="h-full text-brand-text transition-colors duration-300 dark:text-white">
   @php
      $warehouseMenus = [
          'warehouse.dashboard' => 'Dashboard Warehouse',
          'warehouse.index' => 'Import Data Warehouse',
      ];
      $masterDataMenus = [
          'customers' => 'Customer',
          'portofolios' => 'Portofolio',
          'programs' => 'Program',
          'execution-types' => 'Jenis Eksekusi',
          'branches' => 'Branch',
          'pm-projects' => 'PM Project',
          'waspangs' => 'Waspang',
          'mitras' => 'Mitra',
          'users' => 'Manajemen Pengguna',
      ];
$activeMainMenu = request()->routeIs('dashboard')
          ? 'dashboard'
          : (request()->routeIs('project-data.*')
              ? 'project-data'
              : (request()->routeIs('tasks.manage')
                  ? 'manage-' . request('role')
                  : (request()->is('tasks/*') || request()->routeIs('tasks.*')
                      ? 'tasks'
                      : (request()->routeIs('commerce')
                      ? 'commerce'
                      : (request()->routeIs('khs*')
                          ? 'khs'
                           : (request()->routeIs('finance*')
                               ? 'finance'
                                : (request()->routeIs('rekon*')
                                    ? 'rekon'
                                    : null)))))));
      $currentTitle =
          trim($__env->yieldContent('hello_name')) !== '' ? trim($__env->yieldContent('hello_name')) : 'Dashboard';
      $currentLabel =
          trim($__env->yieldContent('date_label')) !== '' ? trim($__env->yieldContent('date_label')) : 'Overview';
   @endphp

   <div class="min-h-screen">
      <!-- Fixed shroud to hide content scrolling above the floating topbar -->
      <div id="top-shroud" class="fixed top-0 left-0 lg:left-[296px] transition-all duration-300 right-0 h-3 z-[55] bg-[var(--app-bg)]"></div>

      <div id="sidebar-backdrop" class="fixed inset-0 z-30 hidden bg-slate-950/45 backdrop-blur-[2px] lg:hidden"></div>

      <aside id="sidebar"
         class="fixed inset-y-0 left-0 z-[60] flex w-[260px] lg:w-[272px] -translate-x-full flex-col border-r border-[var(--panel-border)] bg-[var(--sidebar-bg)] lg:translate-x-0">
         <div class="flex h-full flex-col px-4 py-3">
            <div class="flex items-center justify-between gap-3 px-2 pb-3">
               <div class="brand-container flex min-w-0 items-center gap-3">
                  <div
                     class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl border border-[var(--panel-border)] bg-[var(--panel-strong)] text-[13px] font-bold tracking-[-0.04em] text-[var(--panel-text)] p-3 shadow-soft">
                     TG</div>
                  <div class="workspace-text min-w-0">
                     <p class="truncate text-[16px] font-semibold tracking-[-0.03em] text-[var(--panel-text)]">Taskgate
                     </p>
                     <p class="text-xs font-medium text-[var(--panel-muted)]">Workspace</p>
                  </div>
               </div>
               <button id="close-sidebar" type="button"
                  class="rounded-full p-2 text-[var(--panel-muted)] transition hover:bg-[var(--sidebar-hover)] hover:text-[var(--panel-text)] lg:hidden">
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none"
                     stroke="currentColor" stroke-width="2">
                     <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                  </svg>
               </button>
            </div>

            <div class="search-container px-1 pb-2 pt-0.5">
               <label class="relative block">
                  <span class="pointer-events-none absolute left-4 top-1/2 -translate-y-1/2 text-[var(--panel-muted)]">
                     <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2">
                        <circle cx="11" cy="11" r="7"></circle>
                        <path d="m20 20-3.5-3.5"></path>
                     </svg>
                  </span>
                  <input id="sidebar-search" type="text" placeholder="Search menu..."
                     class="h-10 w-full rounded-xl border border-[var(--panel-border)] bg-[#fafafa] pl-11 pr-4 text-sm text-[var(--panel-text)] outline-none placeholder:text-[var(--panel-muted)] dark:bg-[#18202d]">
               </label>
            </div>

            <div class="sidebar-scroll mt-1 flex-1 overflow-y-auto pr-1">
               <div class="rounded-xl border border-[var(--panel-border)] bg-[var(--panel-bg)] px-2 py-2"
                  data-sidebar-group>
                  <p class="px-2 text-[11px] font-semibold uppercase tracking-[0.22em] text-[var(--panel-muted)]">
                     Navigation</p>
                <nav class="mt-2 space-y-1" data-sidebar-nav>

                   <a href="{{ route('dashboard') }}" data-sidebar-item data-sidebar-label="dashboard"
                      class="relative flex items-center justify-between rounded-xl border px-3 py-2 text-sm font-medium transition {{ $activeMainMenu === 'dashboard' ? 'active-link border-[var(--sidebar-active-border)] bg-[#f3f4f6] text-[var(--panel-text)] dark:border-[#465066] dark:bg-[#273142] dark:text-white' : 'border-transparent text-[var(--panel-text)] hover:border-[var(--panel-border)] hover:bg-[var(--sidebar-hover)]' }}">
                      <span class="flex items-center gap-3 truncate">
                         <span class="menu-icon-wrapper flex h-5 w-5 shrink-0 items-center justify-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect width="7" height="7" x="3" y="3" rx="1"/><rect width="7" height="7" x="14" y="3" rx="1"/><rect width="7" height="7" x="14" y="14" rx="1"/><rect width="7" height="7" x="3" y="14" rx="1"/></svg>
                         </span>
                         <span class="menu-text">Dashboard</span>
                      </span>
                      <span class="menu-text text-xs text-[var(--panel-muted)]">01</span>
                   </a>
                   
                   @if(in_array(Auth::user()->role, ['admin', 'finance', 'warehouse', 'konstruksi', 'procurement', 'commerce']))
                   <a href="{{ route('project-data.index') }}" data-sidebar-item
                      data-sidebar-label="project data"
                      class="relative flex items-center justify-between rounded-xl border px-3 py-2 text-sm font-medium transition {{ $activeMainMenu === 'project-data' ? 'active-link border-[var(--sidebar-active-border)] bg-[#f3f4f6] text-[var(--panel-text)] dark:border-[#465066] dark:bg-[#273142] dark:text-white' : 'border-transparent text-[var(--panel-text)] hover:border-[var(--panel-border)] hover:bg-[var(--sidebar-hover)]' }}">
                      <span class="flex items-center gap-3 truncate">
                         <span class="menu-icon-wrapper flex h-5 w-5 shrink-0 items-center justify-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><ellipse cx="12" cy="5" rx="9" ry="3"/><path d="M3 5V19A9 3 0 0 0 21 19V5"/><path d="M3 12A9 3 0 0 0 21 12"/></svg>
                         </span>
                         <span class="menu-text">Project Data</span>
                      </span>
                      <span class="menu-text text-xs text-[var(--panel-muted)]">02</span>
                   </a>


                   @endif
 
                 </nav>
             </div>

                @if(in_array(Auth::user()->role, ['admin', 'commerce']))
                <details
                   class="detail-toggle group mt-2 rounded-xl border border-[var(--panel-border)] bg-[var(--panel-bg)] px-2 py-2"
                   data-sidebar-group {{ ($activeMainMenu === 'commerce' || request()->routeIs('commerce-rekon.*') || $activeMainMenu === 'manage-commerce' || ($activeMainMenu === 'tasks' && request()->is('tasks/commerce*'))) ? 'open' : '' }}>
                   <summary
                      class="flex cursor-pointer list-none items-center justify-between rounded-xl px-2 py-1.5 text-sm font-semibold text-[var(--panel-text)] transition hover:bg-[var(--sidebar-hover)]">
                      <span class="flex items-center gap-3 truncate font-semibold">
                         <span class="menu-icon-wrapper flex h-5 w-5 shrink-0 items-center justify-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="8" cy="21" r="1"/><circle cx="19" cy="21" r="1"/><path d="M2.05 2.05h2l2.66 12.42a2 2 0 0 0 2 1.58h9.78a2 2 0 0 0 1.95-1.57l1.65-7.43H5.12"/></svg>
                         </span>
                         <span class="menu-text">Commerce</span>
                      </span>
                      <span class="text-[var(--panel-muted)] transition group-open:rotate-180">
                         <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m9 6 6 6-6 6" />
                         </svg>
                      </span>
                   </summary>
                   <nav class="mt-1.5 space-y-1" data-sidebar-nav>
                      <a href="{{ route('commerce-rekon.index') }}" data-sidebar-item
                         data-sidebar-label="dashboard commerce tgidrc"
                         class="relative flex items-center rounded-xl border px-3 py-2 text-sm transition {{ (request()->routeIs('commerce-rekon.*') || $activeMainMenu === 'manage-commerce') ? 'active-link border-[var(--sidebar-active-border)] bg-[#f3f4f6] text-[var(--panel-text)] dark:border-[#465066] dark:bg-[#273142] dark:text-white' : 'border-transparent text-[var(--panel-muted)] hover:border-[var(--panel-border)] hover:bg-[var(--sidebar-hover)] hover:text-[var(--panel-text)]' }}">
                         <span class="flex items-center gap-3">
                            <span class="menu-icon-wrapper flex h-5 w-5 shrink-0 items-center justify-center">
                               <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/></svg>
                            </span>
                            <span class="menu-text">Dashboard Commerce</span>
                         </span>
                      </a>
                      <a href="{{ route('commerce') }}" data-sidebar-item
                         data-sidebar-label="project data commerce"
                         class="relative flex items-center rounded-xl border px-3 py-2 text-sm transition {{ request()->routeIs('commerce') ? 'active-link border-[var(--sidebar-active-border)] bg-[#f3f4f6] text-[var(--panel-text)] dark:border-[#465066] dark:bg-[#273142] dark:text-white' : 'border-transparent text-[var(--panel-muted)] hover:border-[var(--panel-border)] hover:bg-[var(--sidebar-hover)] hover:text-[var(--panel-text)]' }}">
                         <span class="flex items-center gap-3">
                            <span class="menu-icon-wrapper flex h-5 w-5 shrink-0 items-center justify-center">
                               <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/></svg>
                            </span>
                            <span class="menu-text">Project Data Commerce</span>
                         </span>
                      </a>
                      <a href="{{ route('tasks.index', 'commerce') }}" data-sidebar-item
                         data-sidebar-label="task commerce"
                         class="relative flex items-center rounded-xl border px-3 py-2 text-sm transition {{ (request()->routeIs('tasks.index') && request('role') === 'commerce') ? 'active-link border-[var(--sidebar-active-border)] bg-[#f3f4f6] text-[var(--panel-text)] dark:border-[#465066] dark:bg-[#273142] dark:text-white' : 'border-transparent text-[var(--panel-muted)] hover:border-[var(--panel-border)] hover:bg-[var(--sidebar-hover)] hover:text-[var(--panel-text)]' }}">
                         <span class="flex items-center gap-3">
                            <span class="menu-icon-wrapper flex h-5 w-5 shrink-0 items-center justify-center">
                               <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/></svg>
                            </span>
                            <span class="menu-text">Task Commerce</span>
                         </span>
                      </a>
                      <a href="{{ route('commerce.data-log') }}" data-sidebar-item data-sidebar-label="data log commerce"
                         class="relative flex items-center rounded-xl border px-3 py-2 text-sm transition border-transparent text-[var(--panel-muted)] hover:border-[var(--panel-border)] hover:bg-[var(--sidebar-hover)] hover:text-[var(--panel-text)]">
                         <span class="flex items-center gap-3">
                            <span class="menu-icon-wrapper flex h-5 w-5 shrink-0 items-center justify-center">
                               <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/></svg>
                            </span>
                            <span class="menu-text">Data Log</span>
                         </span>
                      </a>
                   </nav>
                </details>
                @endif
 
                @if(in_array(Auth::user()->role, ['admin', 'finance']))
                <details
                   class="detail-toggle group mt-2 rounded-xl border border-[var(--panel-border)] bg-[var(--panel-bg)] px-2 py-2"
                   data-sidebar-group {{ ($activeMainMenu === 'finance' || $activeMainMenu === 'manage-finance' || ($activeMainMenu === 'tasks' && request()->is('tasks/finance*'))) ? 'open' : '' }}>
                   <summary
                      class="flex cursor-pointer list-none items-center justify-between rounded-xl px-2 py-1.5 text-sm font-semibold text-[var(--panel-text)] transition hover:bg-[var(--sidebar-hover)]">
                      <span class="flex items-center gap-3 truncate font-semibold">
                         <span class="menu-icon-wrapper flex h-5 w-5 shrink-0 items-center justify-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect width="20" height="14" x="2" y="5" rx="2"/><line x1="2" y1="10" x2="22" y2="10"/></svg>
                         </span>
                         <span class="menu-text">Finance</span>
                      </span>
                      <span class="text-[var(--panel-muted)] transition group-open:rotate-180">
                         <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m9 6 6 6-6 6" />
                         </svg>
                      </span>
                   </summary>
                   <nav class="mt-1.5 space-y-1" data-sidebar-nav>
                      <a href="{{ route('finance-rekon.index') }}" data-sidebar-item
                         data-sidebar-label="dashboard finance tgidrf"
                         class="relative flex items-center rounded-xl border px-3 py-2 text-sm transition {{ (request()->routeIs('finance-rekon.*') || $activeMainMenu === 'manage-finance') ? 'active-link border-[var(--sidebar-active-border)] bg-[#f3f4f6] text-[var(--panel-text)] dark:border-[#465066] dark:bg-[#273142] dark:text-white' : 'border-transparent text-[var(--panel-muted)] hover:border-[var(--panel-border)] hover:bg-[var(--sidebar-hover)] hover:text-[var(--panel-text)]' }}">
                         <span class="flex items-center gap-3">
                            <span class="menu-icon-wrapper flex h-5 w-5 shrink-0 items-center justify-center">
                               <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/></svg>
                            </span>
                            <span class="menu-text">Dashboard Finance</span>
                         </span>
                      </a>
                      <a href="{{ route('finance.index') }}" data-sidebar-item
                         data-sidebar-label="import data finance"
                         class="relative flex items-center rounded-xl border px-3 py-2 text-sm transition {{ request()->routeIs('finance.index') ? 'active-link border-[var(--sidebar-active-border)] bg-[#f3f4f6] text-[var(--panel-text)] dark:border-[#465066] dark:bg-[#273142] dark:text-white' : 'border-transparent text-[var(--panel-muted)] hover:border-[var(--panel-border)] hover:bg-[var(--sidebar-hover)] hover:text-[var(--panel-text)]' }}">
                         <span class="flex items-center gap-3">
                            <span class="menu-icon-wrapper flex h-5 w-5 shrink-0 items-center justify-center">
                               <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/></svg>
                            </span>
                            <span class="menu-text">Import Data Finance</span>
                         </span>
                      </a>
                      <a href="{{ route('tasks.index', 'finance') }}" data-sidebar-item
                         data-sidebar-label="task finance"
                         class="relative flex items-center rounded-xl border px-3 py-2 text-sm transition {{ (request()->routeIs('tasks.index') && request('role') === 'finance') ? 'active-link border-[var(--sidebar-active-border)] bg-[#f3f4f6] text-[var(--panel-text)] dark:border-[#465066] dark:bg-[#273142] dark:text-white' : 'border-transparent text-[var(--panel-muted)] hover:border-[var(--panel-border)] hover:bg-[var(--sidebar-hover)] hover:text-[var(--panel-text)]' }}">
                         <span class="flex items-center gap-3">
                            <span class="menu-icon-wrapper flex h-5 w-5 shrink-0 items-center justify-center">
                               <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/></svg>
                            </span>
                            <span class="menu-text">Task Finance</span>
                         </span>
                      </a>
                      <a href="{{ route('finance.data-log') }}" data-sidebar-item data-sidebar-label="data log finance"
                         class="relative flex items-center rounded-xl border px-3 py-2 text-sm transition border-transparent text-[var(--panel-muted)] hover:border-[var(--panel-border)] hover:bg-[var(--sidebar-hover)] hover:text-[var(--panel-text)]">
                         <span class="flex items-center gap-3">
                            <span class="menu-icon-wrapper flex h-5 w-5 shrink-0 items-center justify-center">
                               <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/></svg>
                            </span>
                            <span class="menu-text">Data Log</span>
                         </span>
                      </a>
                   </nav>
                </details>
                @endif

                @if(in_array(Auth::user()->role, ['admin', 'procurement']))
                <details
                   class="detail-toggle group mt-2 rounded-xl border border-[var(--panel-border)] bg-[var(--panel-bg)] px-2 py-2"
                   data-sidebar-group {{ ($activeMainMenu === 'project-batch' || $activeMainMenu === 'manage-procurement' || (request()->routeIs('tasks.manage') && request('role') === 'procurement')) ? 'open' : '' }}>
                   <summary
                      class="flex cursor-pointer list-none items-center justify-between rounded-xl px-2 py-1.5 text-sm font-semibold text-[var(--panel-text)] transition hover:bg-[var(--sidebar-hover)]">
                      <span class="flex items-center gap-3 truncate font-semibold">
                         <span class="menu-icon-wrapper flex h-5 w-5 shrink-0 items-center justify-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"/><rect x="8" y="2" width="8" height="4" rx="1" ry="1"/><path d="M9 14h6"/><path d="M9 18h6"/><path d="M9 10h6"/></svg>
                         </span>
                         <span class="menu-text">Procurement</span>
                      </span>
                      <span class="text-[var(--panel-muted)] transition group-open:rotate-180">
                         <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m9 6 6 6-6 6" />
                         </svg>
                      </span>
                   </summary>
                   <nav class="mt-1.5 space-y-1" data-sidebar-nav>
                      <a href="{{ route('project-batch.index') }}" data-sidebar-item
                         data-sidebar-label="dashboard procurement"
                         class="relative flex items-center rounded-xl border px-3 py-2 text-sm transition {{ ($activeMainMenu === 'project-batch' || $activeMainMenu === 'manage-procurement') ? 'active-link border-[var(--sidebar-active-border)] bg-[#f3f4f6] text-[var(--panel-text)] dark:border-[#465066] dark:bg-[#273142] dark:text-white' : 'border-transparent text-[var(--panel-muted)] hover:border-[var(--panel-border)] hover:bg-[var(--sidebar-hover)] hover:text-[var(--panel-text)]' }}">
                         <span class="flex items-center gap-3">
                            <span class="menu-icon-wrapper flex h-5 w-5 shrink-0 items-center justify-center">
                               <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/></svg>
                            </span>
                            <span class="menu-text">Dashboard Procurement</span>
                         </span>
                      </a>
                      <a href="{{ route('tasks.index', 'procurement') }}" data-sidebar-item
                         data-sidebar-label="task procurement"
                         class="relative flex items-center rounded-xl border px-3 py-2 text-sm transition {{ (request()->routeIs('tasks.index') && request('role') === 'procurement') ? 'active-link border-[var(--sidebar-active-border)] bg-[#f3f4f6] text-[var(--panel-text)] dark:border-[#465066] dark:bg-[#273142] dark:text-white' : 'border-transparent text-[var(--panel-muted)] hover:border-[var(--panel-border)] hover:bg-[var(--sidebar-hover)] hover:text-[var(--panel-text)]' }}">
                         <span class="flex items-center gap-3">
                            <span class="menu-icon-wrapper flex h-5 w-5 shrink-0 items-center justify-center">
                               <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/></svg>
                            </span>
                            <span class="menu-text">Task Procurement</span>
                         </span>
                      </a>
                      <a href="{{ route('procurement.data-log') }}" data-sidebar-item data-sidebar-label="data log procurement"
                         class="relative flex items-center rounded-xl border px-3 py-2 text-sm transition {{ request()->routeIs('procurement.data-log') ? 'active-link border-[var(--sidebar-active-border)] bg-[#f3f4f6] text-[var(--panel-text)] dark:border-[#465066] dark:bg-[#273142] dark:text-white' : 'border-transparent text-[var(--panel-muted)] hover:border-[var(--panel-border)] hover:bg-[var(--sidebar-hover)] hover:text-[var(--panel-text)]' }}">
                         <span class="flex items-center gap-3">
                            <span class="menu-icon-wrapper flex h-5 w-5 shrink-0 items-center justify-center">
                               <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/></svg>
                            </span>
                            <span class="menu-text">Data Log</span>
                         </span>
                      </a>
                   </nav>
                </details>
                @endif

               @if(in_array(Auth::user()->role, ['admin', 'konstruksi']))
                <details
                   class="detail-toggle group mt-2 rounded-xl border border-[var(--panel-border)] bg-[var(--panel-bg)] px-2 py-2"
                   data-sidebar-group {{ (($activeMainMenu === 'tasks' && request()->is('tasks/konstruksi*')) || request()->routeIs('rekon.*')) ? 'open' : '' }}>
                   <summary
                      class="flex cursor-pointer list-none items-center justify-between rounded-xl px-2 py-1.5 text-sm font-semibold text-[var(--panel-text)] transition hover:bg-[var(--sidebar-hover)]">
                      <span class="flex items-center gap-3 truncate font-semibold">
                         <span class="menu-icon-wrapper flex h-5 w-5 shrink-0 items-center justify-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z"/></svg>
                         </span>
                         <span class="menu-text">Konstruksi</span>
                      </span>
                      <span class="text-[var(--panel-muted)] transition group-open:rotate-180">
                         <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m9 6 6 6-6 6" />
                         </svg>
                      </span>
                   </summary>
                   <nav class="mt-1.5 space-y-1" data-sidebar-nav>
                      <a href="{{ route('tasks.index', 'konstruksi') }}" data-sidebar-item
                         data-sidebar-label="task konstruksi"
                         class="relative flex items-center rounded-xl border px-3 py-2 text-sm transition {{ request()->is('tasks/konstruksi*') ? 'active-link border-[var(--sidebar-active-border)] bg-[#f3f4f6] text-[var(--panel-text)] dark:border-[#465066] dark:bg-[#273142] dark:text-white' : 'border-transparent text-[var(--panel-muted)] hover:border-[var(--panel-border)] hover:bg-[var(--sidebar-hover)] hover:text-[var(--panel-text)]' }}">
                         <span class="flex items-center gap-3">
                            <span class="menu-icon-wrapper flex h-5 w-5 shrink-0 items-center justify-center">
                               <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/></svg>
                            </span>
                            <span class="menu-text">Task Konstruksi</span>
                         </span>
                      </a>
                   </nav>
                </details>
                @endif
                
                @if(in_array(Auth::user()->role, ['admin', 'warehouse']))
               <details
                  class="detail-toggle group mt-2 rounded-xl border border-[var(--panel-border)] bg-[var(--panel-bg)] px-2 py-2"
                  data-sidebar-group {{ ($activeMainMenu === 'tasks' && request()->is('tasks/warehouse*')) ? 'open' : '' }}>
                  <summary
                     class="flex cursor-pointer list-none items-center justify-between rounded-xl px-2 py-1.5 text-sm font-semibold text-[var(--panel-text)] transition hover:bg-[var(--sidebar-hover)]">
                     <span class="flex items-center gap-3 truncate font-semibold">
                        <span class="menu-icon-wrapper flex h-5 w-5 shrink-0 items-center justify-center">
                           <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 12.5V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l2-1.14"/><path d="m3.3 7 8.7 5 8.7-5"/><path d="M12 22V12"/><path d="M15 13l3 3 3-3"/><path d="M18 16v5"/></svg>
                        </span>
                        <span class="menu-text">Warehouse</span>
                     </span>
                     <span class="text-[var(--panel-muted)] transition group-open:rotate-180">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none"
                           stroke="currentColor" stroke-width="2">
                           <path stroke-linecap="round" stroke-linejoin="round" d="m9 6 6 6-6 6" />
                        </svg>
                     </span>
                  </summary>
                  <nav class="mt-1.5 space-y-1" data-sidebar-nav>
                     <a href="{{ route('tasks.index', 'warehouse') }}" data-sidebar-item
                        data-sidebar-label="task warehouse"
                        class="relative flex items-center rounded-xl border px-3 py-2 text-sm transition {{ (request()->routeIs('tasks.index') && request('role') === 'warehouse') ? 'active-link border-[var(--sidebar-active-border)] bg-[#f3f4f6] text-[var(--panel-text)] dark:border-[#465066] dark:bg-[#273142] dark:text-white' : 'border-transparent text-[var(--panel-muted)] hover:border-[var(--panel-border)] hover:bg-[var(--sidebar-hover)] hover:text-[var(--panel-text)]' }}">
                        <span class="flex items-center gap-3">
                           <span class="menu-icon-wrapper flex h-5 w-5 shrink-0 items-center justify-center">
                              <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/></svg>
                           </span>
                           <span class="menu-text">Task Warehouse</span>
                        </span>
                     </a>
                     <a href="{{ route('rekon.index') }}" data-sidebar-item
                        data-sidebar-label="dashboard rekon"
                        class="relative flex items-center rounded-xl border px-3 py-2 text-sm transition {{ (request()->routeIs('rekon.index') || $activeMainMenu === 'manage-warehouse') ? 'active-link border-[var(--sidebar-active-border)] bg-[#f3f4f6] text-[var(--panel-text)] dark:border-[#465066] dark:bg-[#273142] dark:text-white' : 'border-transparent text-[var(--panel-muted)] hover:border-[var(--panel-border)] hover:bg-[var(--sidebar-hover)] hover:text-[var(--panel-text)]' }}">
                        <span class="flex items-center gap-3">
                           <span class="menu-icon-wrapper flex h-5 w-5 shrink-0 items-center justify-center">
                              <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/></svg>
                           </span>
                           <span class="menu-text">Dashboard Rekon</span>
                        </span>
                     </a>
                     
                     <a href="{{ route('warehouse.index') }}" data-sidebar-item
                        data-sidebar-label="import data"
                        class="relative flex items-center rounded-xl border px-3 py-2 text-sm transition {{ request()->routeIs('warehouse.index') ? 'active-link border-[var(--sidebar-active-border)] bg-[#f3f4f6] text-[var(--panel-text)] dark:border-[#465066] dark:bg-[#273142] dark:text-white' : 'border-transparent text-[var(--panel-muted)] hover:border-[var(--panel-border)] hover:bg-[var(--sidebar-hover)] hover:text-[var(--panel-text)]' }}">
                        <span class="flex items-center gap-3">
                           <span class="menu-icon-wrapper flex h-5 w-5 shrink-0 items-center justify-center">
                              <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/></svg>
                           </span>
                           <span class="menu-text">Import Data Warehouse</span>
                        </span>
                     </a>
                     <a href="{{ route('warehouse.data-log') }}" data-sidebar-item data-sidebar-label="data log warehouse"
                        class="relative flex items-center rounded-xl border px-3 py-2 text-sm transition border-transparent text-[var(--panel-muted)] hover:border-[var(--panel-border)] hover:bg-[var(--sidebar-hover)] hover:text-[var(--panel-text)]">
                        <span class="flex items-center gap-3">
                           <span class="menu-icon-wrapper flex h-5 w-5 shrink-0 items-center justify-center">
                              <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/></svg>
                           </span>
                           <span class="menu-text">Data Log</span>
                        </span>
                     </a>
                  </nav>
               </details>
               @endif
 
               @if(Auth::user()->role === 'admin')
               <details
                  class="detail-toggle group mt-2 rounded-xl border border-[var(--panel-border)] bg-[var(--panel-bg)] px-2 py-2"
                  data-sidebar-group {{ ($activeMainMenu === 'tasks' && request()->is('tasks/procurement*')) ? 'open' : '' }}>
                  <summary
                     class="flex cursor-pointer list-none items-center justify-between rounded-xl px-2 py-1.5 text-sm font-semibold text-[var(--panel-text)] transition hover:bg-[var(--sidebar-hover)]">
                     <span class="flex items-center gap-3 truncate font-semibold">
                        <span class="menu-icon-wrapper flex h-5 w-5 shrink-0 items-center justify-center">
                           <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12.22 2h-.44a2 2 0 0 0-2 2v1.18a2 2 0 0 1-1 1.73l-.43.25a2 2 0 0 1-2 0l-1.02-.59a2 2 0 0 0-2.73.73l-.22.38a2 2 0 0 0 .73 2.73l1.02.59a2 2 0 0 1 1 1.73v1a2 2 0 0 1-1 1.73l-1.02.6a2 2 0 0 0-.73 2.73l.22.38a2 2 0 0 0 2.73.73l1.02-.6a2 2 0 0 1 2 0l.43.25a2 2 0 0 1 1 1.73V20a2 2 0 0 0 2 2h.44a2 2 0 0 0 2 2h.44a2 2 0 0 0 2-2v-1.18a2 2 0 0 1 1-1.73l.43-.25a2 2 0 0 1 2 0l1.02.59a2 2 0 0 0 2.73-.73l.22-.38a2 2 0 0 0-.73-2.73l-1.02-.59a2 2 0 0 1-1-1.73v-1a2 2 0 0 1 1-1.73l1.02-.6a2 2 0 0 0 .73-2.73l-.22-.38a2 2 0 0 0-2.73-.73l-1.02.6a2 2 0 0 1-2 0l-.43-.25a2 2 0 0 1-1-1.73V4a2 2 0 0 0-2-2z"/><circle cx="12" cy="12" r="3"/></svg>
                        </span>
                        <span class="menu-text">Master Data</span>
                     </span>
                     <span class="text-[var(--panel-muted)] transition group-open:rotate-180">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none"
                           stroke="currentColor" stroke-width="2">
                           <path stroke-linecap="round" stroke-linejoin="round" d="m9 6 6 6-6 6" />
                        </svg>
                     </span>
                  </summary>
                  <nav class="mt-1.5 space-y-1" data-sidebar-nav>
                     @foreach ($masterDataMenus as $resource => $label)
                        <a href="{{ route('master-data.index', $resource) }}" data-sidebar-item
                           data-sidebar-label="{{ strtolower($label) }}"
                           class="relative flex items-center rounded-xl border px-3 py-2 text-sm transition {{ request()->routeIs('master-data.*') && request()->route('resource') === $resource ? 'active-link border-[var(--sidebar-active-border)] bg-[#f3f4f6] text-[var(--panel-text)] dark:border-[#465066] dark:bg-[#273142] dark:text-white' : 'border-transparent text-[var(--panel-muted)] hover:border-[var(--panel-border)] hover:bg-[var(--sidebar-hover)] hover:text-[var(--panel-text)]' }}">
                           <span class="flex items-center gap-3">
                              <span class="menu-icon-wrapper flex h-5 w-5 shrink-0 items-center justify-center">
                                 <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/></svg>
                              </span>
                              <span class="menu-text">{{ $label }}</span>
                           </span>
                        </a>
                     @endforeach
                     <a href="{{ route('khs') }}" data-sidebar-item data-sidebar-label="khs"
                        class="relative flex items-center rounded-xl border px-3 py-2 text-sm transition {{ request()->routeIs('khs*') ? 'active-link border-[var(--sidebar-active-border)] bg-[#f3f4f6] text-[var(--panel-text)] dark:border-[#465066] dark:bg-[#273142] dark:text-white' : 'border-transparent text-[var(--panel-muted)] hover:border-[var(--panel-border)] hover:bg-[var(--sidebar-hover)] hover:text-[var(--panel-text)]' }}">
                        <span class="flex items-center gap-3">
                           <span class="menu-icon-wrapper flex h-5 w-5 shrink-0 items-center justify-center">
                              <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><line x1="10" y1="9" x2="8" y2="9"/></svg>
                           </span>
                           <span class="menu-text">KHS</span>
                        </span>
                     </a>
                  </nav>
               </details>
               @endif
 
               <div class="profile-box mt-2 rounded-lg border border-[var(--panel-border)] bg-[var(--panel-bg)] px-2 py-2">
                  <div class="flex items-center gap-3 px-2">
                     <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-brand-blue text-white text-xs font-bold p-3 shadow-soft">
                        {{ strtoupper(substr(Auth::user()->name, 0, 2)) }}
                     </div>
                     <div class="profile-text min-w-0">
                        <p class="truncate text-sm font-semibold text-[var(--panel-text)]">{{ Auth::user()->name }}</p>
                        <p class="text-[11px] font-medium uppercase tracking-wider text-[var(--panel-muted)]">{{ Auth::user()->role }}</p>
                     </div>
                  </div>
                  <form action="{{ route('logout') }}" method="POST" class="mt-2 text-center">
                     @csrf
                     <button type="submit" class="flex w-full items-center justify-center lg:justify-start gap-3 rounded-xl border border-transparent px-3 py-1.5 text-xs font-medium text-red-500 transition hover:border-red-100 hover:bg-red-50 dark:hover:border-red-900/30 dark:hover:bg-red-900/10">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                           <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0 0 13.5 3h-6a2.25 2.25 0 0 0-2.25 2.25v13.5A2.25 2.25 0 0 0 7.5 21h6a2.25 2.25 0 0 0 2.25-2.25V15M18 12H9m9 0-2.25 2.25M18 12l-2.25-2.25" />
                        </svg>
                        <span class="profile-text">Sign Out</span>
                     </button>
                  </form>
               </div>
            </div>

         </div>
      </aside>

      <div id="main-content" class="min-h-screen lg:ml-[272px]">
         <!-- Mobile Header (Floating) -->
         <header id="header-mobile" class="fixed left-0 right-0 top-0 z-50 lg:hidden pointer-events-none">
            <div class="px-3 pt-3 pointer-events-auto">
               <div class="rounded-xl border border-[var(--panel-border)] bg-[var(--panel-strong)] p-2 md:p-3 shadow-soft">
                  <div class="flex items-center justify-between gap-4">
                     <div class="flex min-w-0 items-center gap-2">
                        <button id="open-sidebar" type="button"
                           class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl border border-[var(--panel-border)] bg-[var(--panel-bg)] text-[var(--panel-text)]">
                           <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none"
                              stroke="currentColor" stroke-width="2">
                              <path stroke-linecap="round" stroke-linejoin="round" d="M4 7h16M4 12h16M4 17h16" />
                           </svg>
                        </button>
                        <div class="min-w-0 flex items-center gap-1.5 h-10 text-xs text-[var(--panel-muted)]">
                           <span class="truncate font-medium text-[var(--panel-text)]">{{ $currentLabel }}</span>
                           @if ($currentTitle !== $currentLabel)
                              <span class="flex items-center text-[var(--panel-border)]">/</span>
                              <span class="truncate font-bold text-brand-primary dark:text-blue-400">{{ $currentTitle }}</span>
                           @endif
                        </div>
                     </div>

                     <div class="flex shrink-0 items-center gap-2">
                        <!-- Notification Bell (Mobile) -->
                        <div class="relative" id="notifications-wrapper-mobile">
                           <button id="notifications-toggle-mobile" type="button"
                              class="relative flex h-10 w-10 items-center justify-center rounded-xl border border-[var(--panel-border)] bg-[var(--panel-bg)] text-[var(--panel-text)] transition">
                              <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                 <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0" />
                              </svg>
                              @if(auth()->user()->unreadNotifications->count() > 0)
                                 <span class="absolute -top-1 -right-1 flex h-4 w-4 items-center justify-center rounded-full bg-red-500 text-[9px] font-bold text-white shadow-sm ring-2 ring-[var(--panel-strong)]">
                                    {{ auth()->user()->unreadNotifications->count() }}
                                 </span>
                              @endif
                           </button>

                           <!-- Notifications Dropdown (Mobile) -->
                           <div id="notifications-dropdown-mobile" class="absolute right-0 mt-3 hidden w-[min(calc(100vw-24px),320px)] origin-top-right overflow-hidden rounded-xl border border-[var(--panel-border)] bg-[var(--panel-strong)] shadow-float">
                              <div class="border-b border-[var(--panel-border)] px-4 py-3">
                                 <div class="flex items-center justify-between">
                                    <h3 class="text-sm font-bold text-[var(--panel-text)]">Notifikasi</h3>
                                 </div>
                              </div>
                              <div class="max-h-[300px] overflow-y-auto">
                                 @forelse(auth()->user()->unreadNotifications->take(5) as $notification)
                                    <form action="{{ route('notifications.read', $notification->id) }}" method="POST">
                                       @csrf
                                       <button type="submit" class="group flex w-full items-start gap-3 border-b border-[var(--panel-border)] px-4 py-4 text-left transition hover:bg-[#f8fafc] dark:hover:bg-slate-800/50">
                                          <div class="mt-1 flex h-8 w-8 shrink-0 items-center justify-center rounded-xl bg-brand-blue/5 text-brand-blue dark:bg-blue-500/10 dark:text-blue-400">
                                             <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" />
                                             </svg>
                                          </div>
                                          <div class="min-w-0 flex-1">
                                             <p class="truncate text-[13px] font-semibold leading-tight text-[var(--panel-text)]">{{ $notification->data['project_name'] }}</p>
                                             <p class="mt-1 line-clamp-2 text-[11px] leading-relaxed text-[var(--panel-muted)]">{{ $notification->data['message'] }}</p>
                                             <p class="mt-2 text-[10px] font-medium text-brand-blue/60 dark:text-blue-400/60">{{ $notification->created_at->diffForHumans() }}</p>
                                          </div>
                                       </button>
                                    </form>
                                 @empty
                                    <div class="flex flex-col items-center justify-center px-4 py-8 text-center text-[var(--panel-muted)]">
                                       <p class="mt-2 text-xs font-medium">Belum ada notifikasi</p>
                                    </div>
                                 @endforelse
                              </div>
                           </div>
                        </div>

                        <button id="theme-toggle-mobile" type="button" aria-label="Toggle theme"
                           class="theme-switch relative flex h-10 w-10 shrink-0 items-center justify-center rounded-xl border border-[var(--panel-border)] bg-[var(--panel-bg)] text-[var(--panel-text)] transition hover:bg-[#f3f4f6] dark:hover:bg-brand-darkPanel focus:outline-none">
                           <!-- Moon Icon (Light Mode) -->
                           <svg xmlns="http://www.w3.org/2000/svg" class="theme-icon-moon h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                              <path stroke-linecap="round" stroke-linejoin="round" d="M21 12.79A9 9 0 1111.21 3c-.05.33-.08.66-.08 1a7 7 0 009.87 6.36z" />
                           </svg>
                           <!-- Sun Icon (Dark Mode) -->
                           <svg xmlns="http://www.w3.org/2000/svg" class="theme-icon-sun h-5 w-5 hidden" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                              <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v2.25M12 18.75V21M3 12h2.25M18.75 12H21M5.636 5.636l1.591 1.591M16.773 16.773l1.591 1.591M5.636 18.364l1.591-1.591M16.773 7.227l1.591-1.591M15.75 12A3.75 3.75 0 1112 8.25 3.75 3.75 0 0115.75 12z" />
                           </svg>
                        </button>
                     </div>
                  </div>
               </div>
            </div>
         </header>

         <!-- Desktop Header (Floating) -->
         <header id="header-desktop"
            class="fixed hidden lg:block pointer-events-none transition-all duration-300">
            <div class="pr-3 pt-3 lg:pl-0 pointer-events-auto">
               <div class="rounded-xl border border-[var(--panel-border)] bg-[var(--panel-strong)] p-3 shadow-soft">
                  <div class="flex items-center justify-between gap-4">
                     <div class="flex min-w-0 items-center gap-4">
                        <button id="sidebar-toggle" type="button"
                           class="flex h-10 w-10 items-center justify-center rounded-xl border border-[var(--panel-border)] bg-[var(--panel-bg)] text-[var(--panel-text)] transition hover:bg-[#f3f4f6] dark:hover:bg-brand-darkPanel">
                           <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none"
                              stroke="currentColor" stroke-width="2">
                              <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25H12" />
                           </svg>
                        </button>
                        <div class="min-w-0 flex h-10 items-center gap-3 text-sm text-[var(--panel-muted)]">
                           <span>Workspace</span>
                           <span>/</span>
                           <span class="truncate font-medium text-[var(--panel-text)] lg:text-[var(--panel-muted)]">{{ $currentLabel }}</span>
                           @if ($currentTitle !== $currentLabel)
                              <span class="text-[var(--panel-muted)]">/</span>
                              <span class="truncate font-bold text-[var(--panel-text)]">{{ $currentTitle }}</span>
                           @endif
                        </div>
                     </div>

                     <div class="flex shrink-0 items-center gap-3">
                        <!-- Notification Bell (Desktop) -->
                        <div class="relative" id="notifications-wrapper-desktop">
                           <button id="notifications-toggle-desktop" type="button"
                              class="relative flex h-10 w-10 items-center justify-center rounded-xl border border-[var(--panel-border)] bg-[var(--panel-bg)] text-[var(--panel-text)] transition hover:bg-[#f3f4f6] dark:hover:bg-brand-darkPanel">
                              <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                 <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0" />
                              </svg>
                              @if(auth()->user()->unreadNotifications->count() > 0)
                                 <span class="absolute -top-1 -right-1 flex h-5 w-5 items-center justify-center rounded-full bg-red-500 text-[10px] font-bold text-white shadow-sm ring-2 ring-[var(--panel-strong)]">
                                    {{ auth()->user()->unreadNotifications->count() }}
                                 </span>
                              @endif
                           </button>
                           <div id="notifications-dropdown-desktop" class="absolute right-0 mt-3 hidden w-80 origin-top-right overflow-hidden rounded-xl border border-[var(--panel-border)] bg-[var(--panel-strong)] shadow-float">
                              <div class="border-b border-[var(--panel-border)] px-4 py-3 text-center">
                                 <h3 class="text-sm font-bold text-[var(--panel-text)]">Notifikasi</h3>
                              </div>
                              <div class="max-h-[360px] overflow-y-auto">
                                 @forelse(auth()->user()->unreadNotifications->take(5) as $notification)
                                    <form action="{{ route('notifications.read', $notification->id) }}" method="POST">
                                       @csrf
                                       <button type="submit" class="group flex w-full items-start gap-3 border-b border-[var(--panel-border)] px-4 py-4 text-left transition hover:bg-[#f8fafc] dark:hover:bg-slate-800/50">
                                          <div class="mt-1 flex h-8 w-8 shrink-0 items-center justify-center rounded-xl bg-brand-blue/5 text-brand-blue dark:bg-blue-500/10 dark:text-blue-400">
                                             <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" />
                                             </svg>
                                          </div>
                                          <div class="min-w-0 flex-1">
                                             <p class="truncate text-[13px] font-semibold text-[var(--panel-text)]">{{ $notification->data['project_name'] }}</p>
                                             <p class="mt-1 line-clamp-2 text-[11px] text-[var(--panel-muted)]">{{ $notification->data['message'] }}</p>
                                             <p class="mt-2 text-[10px] text-brand-blue/60">{{ $notification->created_at->diffForHumans() }}</p>
                                          </div>
                                       </button>
                                    </form>
                                 @empty
                                    <div class="px-4 py-8 text-center text-xs text-[var(--panel-muted)]">Belum ada notifikasi</div>
                                 @endforelse
                              </div>
                           </div>
                        </div>

                        <button id="theme-toggle" type="button" aria-label="Toggle theme"
                           class="theme-switch relative flex h-10 w-10 items-center justify-center rounded-xl border border-[var(--panel-border)] bg-[var(--panel-bg)] text-[var(--panel-text)] transition hover:bg-[#f3f4f6] dark:hover:bg-brand-darkPanel focus:outline-none">
                           <!-- Moon Icon (Light Mode) -->
                           <svg xmlns="http://www.w3.org/2000/svg" class="theme-icon-moon h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                              <path stroke-linecap="round" stroke-linejoin="round" d="M21 12.79A9 9 0 1111.21 3c-.05.33-.08.66-.08 1a7 7 0 009.87 6.36z" />
                           </svg>
                           <!-- Sun Icon (Dark Mode) -->
                           <svg xmlns="http://www.w3.org/2000/svg" class="theme-icon-sun h-5 w-5 hidden" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                              <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v2.25M12 18.75V21M3 12h2.25M18.75 12H21M5.636 5.636l1.591 1.591M16.773 16.773l1.591 1.591M5.636 18.364l1.591-1.591M16.773 7.227l1.591-1.591M15.75 12A3.75 3.75 0 1112 8.25 3.75 3.75 0 0115.75 12z" />
                           </svg>
                        </button>
                     </div>
                  </div>
               </div>
            </div>
         </header>

         <main class="pb-3 pt-[67px] md:pt-[78px] lg:pt-[67px] px-3 lg:pl-0 lg:pr-3">
            <section
               class="mb-3 rounded-xl border border-[var(--panel-border)] bg-[var(--panel-bg)] p-3 shadow-soft">
               <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                  <div class="min-w-0">
                     <p class="text-xs font-semibold uppercase tracking-[0.18em] text-[var(--panel-muted)]">
                        {{ $currentLabel }}</p>
                     <h1 class="mt-2 text-[22px] sm:text-[28px] font-semibold tracking-[-0.04em] text-[var(--panel-text)]">
                        {{ $currentTitle }}</h1>
                     @hasSection('hero_subtitle')
                        @if (trim($__env->yieldContent('hero_subtitle')) !== '')
                           <p class="mt-2 max-w-3xl text-sm leading-6 text-[var(--panel-muted)]">@yield('hero_subtitle')</p>
                        @endif
                     @endif
                  </div>

                  <div class="flex flex-wrap items-center gap-3">
                     @if (trim($__env->yieldContent('hero_actions')) !== '')
                        @yield('hero_actions')
                     @endif
                  </div>
               </div>
            </section>
            @yield('content')
         </main>
      </div>
   </div>

   @if (session('status'))
      <div id="status-toast" class="toast-enter fixed right-4 top-4 z-[75] w-[min(92vw,420px)]">
         <div
            class="overflow-hidden rounded-xl border border-[#d6dfd0] bg-[var(--panel-strong)] p-3 shadow-soft dark:border-[#38513d]">
            <div class="flex items-start gap-4 px-5 py-4">
               <div
                  class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-[#eef8e9] text-[#4f8a42] dark:bg-[#15201a] dark:text-[#bfd7c2]">
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none"
                     stroke="currentColor" stroke-width="2">
                     <path stroke-linecap="round" stroke-linejoin="round" d="m5 13 4 4L19 7" />
                  </svg>
               </div>
               <div class="min-w-0 flex-1">
                  <p class="text-sm font-semibold text-[var(--panel-text)]">Berhasil</p>
                  <p class="mt-1 text-sm leading-6 text-[var(--panel-muted)]">{{ session('status') }}</p>
               </div>
               <button id="status-toast-close" type="button"
                  class="rounded-full p-2 text-[var(--panel-muted)] transition hover:bg-[#f4f5f9] hover:text-[var(--panel-text)]"><svg
                     xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none"
                     stroke="currentColor" stroke-width="2">
                     <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                  </svg></button>
            </div>
            <div class="h-1.5 w-full bg-[#eef8e9] dark:bg-[#1c2a22]">
               <div id="status-toast-bar" class="h-full w-full origin-left bg-[#70c26a] dark:bg-[#5fa85a]"></div>
            </div>
         </div>
      </div>
   @endif

   @if ($errors->any())
      <div id="error-toast" class="toast-enter fixed right-4 top-4 z-[76] w-[min(92vw,460px)]">
         <div
            class="overflow-hidden rounded-xl border border-[#f2c8cc] bg-[var(--panel-strong)] p-3 shadow-soft dark:border-[#5c2d34]">
            <div class="flex items-start gap-4 px-5 py-4">
               <div
                  class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-[#fff4f4] text-[#c65b68] dark:bg-[#2b171b] dark:text-[#f3c7cd]">
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none"
                     stroke="currentColor" stroke-width="2">
                     <path stroke-linecap="round" stroke-linejoin="round"
                        d="M12 9v4m0 4h.01M10.29 3.86l-8.11 14A1 1 0 0 0 3.05 19h17.9a1 1 0 0 0 .87-1.5l-8.11-14a1 1 0 0 0-1.74 0Z" />
                  </svg>
               </div>
               <div class="min-w-0 flex-1">
                  <p class="text-sm font-semibold text-[var(--panel-text)]">Periksa kembali input</p>
                  <ul class="mt-1 space-y-1 text-sm leading-6 text-[var(--panel-muted)]">
                     @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                     @endforeach
                  </ul>
               </div>
               <button id="error-toast-close" type="button"
                  class="rounded-full p-2 text-[var(--panel-muted)] transition hover:bg-[#f4f5f9] hover:text-[var(--panel-text)]"><svg
                     xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none"
                     stroke="currentColor" stroke-width="2">
                     <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                  </svg></button>
            </div>
            <div class="h-1.5 w-full bg-[#fff1f1] dark:bg-[#2b171b]">
               <div id="error-toast-bar" class="h-full w-full origin-left bg-[#d97782]"></div>
            </div>
         </div>
      </div>
   @endif

   <div id="upload-progress-modal" class="upload-modal-backdrop hidden">
      <div class="upload-progress-card">
         <div class="flex items-center gap-4 mb-4">
            <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-blue-50 text-blue-600 dark:bg-blue-900/30 dark:text-blue-400">
               <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
               </svg>
            </div>
            <div class="min-w-0 flex-1">
               <h3 class="text-sm font-bold text-slate-800 dark:text-white truncate" id="upload-file-name">Uploading file...</h3>
               <p class="text-[10px] uppercase font-black tracking-widest text-slate-400 mt-1" id="upload-speed-info">Calculating speed...</p>
            </div>
         </div>

         <div class="relative h-2 w-full overflow-hidden rounded-full bg-slate-100 dark:bg-slate-800">
            <div id="upload-progress-bar" class="absolute inset-y-0 left-0 w-0 bg-blue-600 transition-all duration-300 progress-bar-shimmer"></div>
         </div>

         <div class="mt-4 flex items-center justify-between">
            <span class="text-[10px] font-black text-blue-600 dark:text-blue-400" id="upload-percentage">0%</span>
            <span class="text-[10px] font-bold text-slate-400" id="upload-bytes">0 / 0 MB</span>
         </div>
      </div>
   </div>

   <div id="confirm-modal" class="fixed inset-0 z-[70] hidden items-center justify-center">
      <div id="confirm-backdrop" class="absolute inset-0 bg-slate-950/55 backdrop-blur-[2px]"></div>
      <div
         class="relative mx-4 w-full max-w-md rounded-xl border border-[var(--panel-border)] bg-[var(--panel-strong)] p-3 shadow-soft">
         <div class="flex items-start gap-4">
            <div
               class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-[#fff4f4] text-[#c65b68] dark:bg-[#2b171b] dark:text-[#f3c7cd]">
               <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none"
                  stroke="currentColor" stroke-width="2">
                  <path stroke-linecap="round" stroke-linejoin="round"
                     d="M12 9v4m0 4h.01M10.29 3.86l-8.11 14A1 1 0 0 0 3.05 19h17.9a1 1 0 0 0 .87-1.5l-8.11-14a1 1 0 0 0-1.74 0Z" />
               </svg>
            </div>
            <div class="min-w-0">
               <h3 id="confirm-title" class="text-lg font-semibold text-[var(--panel-text)]">Konfirmasi</h3>
               <p id="confirm-message" class="mt-2 text-sm leading-7 text-[var(--panel-muted)]">Tindakan ini akan
                  dijalankan.</p>
            </div>
         </div>
         <div class="mt-6 flex justify-end gap-3">
            <button id="confirm-cancel" type="button"
               class="rounded-full border border-[var(--panel-border)] px-4 py-2.5 text-sm font-semibold text-[var(--panel-text)] transition hover:border-[var(--sidebar-active-border)]">Batal</button>
            <button id="confirm-accept" type="button"
               class="rounded-full bg-[#c65b68] px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-[#b14b58]">Ya,
               lanjutkan</button>
         </div>
      </div>
   </div>

   <script type="module">
      (function() {
         var root = document.documentElement;
         var sidebar = document.getElementById('sidebar');
         var backdrop = document.getElementById('sidebar-backdrop');
         var openSidebar = document.getElementById('open-sidebar');
         var closeSidebar = document.getElementById('close-sidebar');
         var sidebarToggle = document.getElementById('sidebar-toggle');
         var themeToggles = Array.from(document.querySelectorAll('.theme-switch'));
         var sidebarSearch = document.getElementById('sidebar-search');
         var confirmModal = document.getElementById('confirm-modal');
         var confirmBackdrop = document.getElementById('confirm-backdrop');
         var confirmTitle = document.getElementById('confirm-title');
         var confirmMessage = document.getElementById('confirm-message');
         var confirmCancel = document.getElementById('confirm-cancel');
         var confirmAccept = document.getElementById('confirm-accept');
         var pendingConfirmForm = null;
         var statusToast = document.getElementById('status-toast');
         var statusToastClose = document.getElementById('status-toast-close');
         var statusToastBar = document.getElementById('status-toast-bar');
         var errorToast = document.getElementById('error-toast');
         var errorToastClose = document.getElementById('error-toast-close');
         var errorToastBar = document.getElementById('error-toast-bar');
 
         function initDataTablesDefaults() {
            if (typeof $ === 'undefined' || typeof $.fn.dataTable === 'undefined') return;
            $.fn.dataTable.ext.errMode = 'none';
            $.extend(true, $.fn.dataTable.defaults, {
               scrollX: true,
               autoWidth: false,
               width: '100%',
               responsive: false,
               pageLength: 25,
               searchDelay: 500,
               processing: false, 
               drawCallback: function() {
                  $(this).find('tbody tr.skeleton-row').remove();
               },
               preXhr: function(e, settings) {
                  const api = new $.fn.dataTable.Api(settings);
                  const colCount = api.columns().count();
                  let skeletonRows = '';
                  for (let i = 0; i < 6; i++) {
                     skeletonRows += '<tr class="skeleton-row">';
                     for (let j = 0; j < colCount; j++) {
                        skeletonRows += '<td class="px-4 py-4"><div class="skeleton-item"></div></td>';
                     }
                     skeletonRows += '</tr>';
                  }
                  $(settings.nTable).find('tbody').html(skeletonRows);
               },
               dom: '<"top"lf>rt<"bottom"ip><"clear">',
               language: {
                  search: "Cari:",
                  searchPlaceholder: "Ketik untuk menyaring...",
                  lengthMenu: "Tampilkan _MENU_ data",
                  zeroRecords: "Data tidak ditemukan",
                  info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ data",
                  infoEmpty: "Menampilkan 0 sampai 0 dari 0 data",
                  infoFiltered: "(disaring dari _MAX_ total data)",
                  paginate: {
                     first: "Awal",
                     last: "Akhir",
                     next: '<i class="fas fa-chevron-right"></i>',
                     previous: '<i class="fas fa-chevron-left"></i>'
                  }
               }
            });
         }

         window.initSearchableSelects = function() {
            var Chooser = window.Choices || Choices;
            if (typeof Chooser === 'undefined') return;
            
            document.querySelectorAll('select[data-searchable-select]').forEach(function(select) {
               if (select.dataset.choicesInitialized === 'true') return;
               new Chooser(select, {
                  searchEnabled: true,
                  searchPlaceholderValue: 'Search option',
                  itemSelectText: '',
                  shouldSort: false,
                  allowHTML: false,
                  placeholder: true,
                  noResultsText: 'Data tidak ditemukan',
                  noChoicesText: 'Tidak ada pilihan'
               });
               select.dataset.choicesInitialized = 'true';
            });
         }

         function syncIcons() {
            var isDark = root.classList.contains('dark');
            document.querySelectorAll('.theme-icon-moon').forEach(function(icon) {
               icon.classList.toggle('hidden', isDark);
            });
            document.querySelectorAll('.theme-icon-sun').forEach(function(icon) {
               icon.classList.toggle('hidden', !isDark);
            });
         }

          var notificationsToggleDeskt = document.getElementById('notifications-toggle-desktop');
          var notificationsDropdownDeskt = document.getElementById('notifications-dropdown-desktop');
          var notificationsToggleMobil = document.getElementById('notifications-toggle-mobile');
          var notificationsDropdownMobil = document.getElementById('notifications-dropdown-mobile');

          function setupNotifications(toggle, dropdown) {
             if (toggle && dropdown) {
                toggle.addEventListener('click', function(e) {
                   e.stopPropagation();
                   dropdown.classList.toggle('hidden');
                });
                document.addEventListener('click', function(e) {
                   if (!dropdown.contains(e.target) && !toggle.contains(e.target)) {
                      dropdown.classList.add('hidden');
                   }
                });
             }
          }

          setupNotifications(notificationsToggleDeskt, notificationsDropdownDeskt);
          setupNotifications(notificationsToggleMobil, notificationsDropdownMobil);

         function toggleTheme() {
            var isDark = root.classList.toggle('dark');
            localStorage.setItem('taskgate-theme', isDark ? 'dark' : 'light');
            syncIcons();
         }

         function toggleSidebar() {
            var isCollapsed = root.classList.toggle('sidebar-collapsed');
            localStorage.setItem('taskgate-sidebar-collapsed', isCollapsed);
         }

         function filterSidebarMenus() {
            if (!sidebarSearch) return;
            var keyword = sidebarSearch.value.trim().toLowerCase();
            var groups = document.querySelectorAll('[data-sidebar-group]');

            groups.forEach(function(group) {
               var items = group.querySelectorAll('[data-sidebar-item]');
               var visibleCount = 0;

               items.forEach(function(item) {
                  var label = (item.getAttribute('data-sidebar-label') || item.textContent || '')
                     .toLowerCase();
                  var matched = keyword === '' || label.indexOf(keyword) !== -1;
                  item.classList.toggle('sidebar-search-hidden', !matched);
                  if (matched) visibleCount++;
               });

               group.classList.toggle('sidebar-search-hidden', visibleCount === 0);

               if (group.tagName === 'DETAILS' && keyword !== '' && visibleCount > 0) {
                  group.open = true;
               }
            });
         }

         function openMenu() {
            sidebar.classList.remove('-translate-x-full');
            backdrop.classList.remove('hidden');
         }

         function closeMenu() {
            sidebar.classList.add('-translate-x-full');
            backdrop.classList.add('hidden');
         }

         function openConfirm(form) {
            pendingConfirmForm = form;
            confirmTitle.textContent = form.getAttribute('data-confirm-title') || 'Konfirmasi';
            confirmMessage.textContent = form.getAttribute('data-confirm-message') ||
               'Apakah Anda yakin ingin melanjutkan tindakan ini?';
            confirmModal.classList.remove('hidden');
            confirmModal.classList.add('flex');
         }

         function closeConfirm() {
            pendingConfirmForm = null;
            confirmModal.classList.add('hidden');
            confirmModal.classList.remove('flex');
         }

         function dismissToast() {
            if (!statusToast || statusToast.classList.contains('toast-exit')) return;
            statusToast.classList.remove('toast-enter');
            statusToast.classList.add('toast-exit');
            window.setTimeout(function() {
               if (statusToast) statusToast.remove();
            }, 180);
         }

         function dismissErrorToast() {
            if (!errorToast || errorToast.classList.contains('toast-exit')) return;
            errorToast.classList.remove('toast-enter');
            errorToast.classList.add('toast-exit');
            window.setTimeout(function() {
               if (errorToast) errorToast.remove();
            }, 180);
         }

         initDataTablesDefaults();
         syncIcons();
         window.initSearchableSelects();
         
         // Re-init on DOMContentLoaded to handle any race conditions or dynamic content
         window.addEventListener('DOMContentLoaded', function() {
            window.initSearchableSelects();
         });
         
         filterSidebarMenus();
         themeToggles.forEach(function(toggle) {
            toggle.addEventListener('click', toggleTheme);
         });
         if (sidebarToggle) sidebarToggle.addEventListener('click', toggleSidebar);
         if (sidebarSearch) sidebarSearch.addEventListener('input', filterSidebarMenus);
         if (openSidebar) openSidebar.addEventListener('click', openMenu);
         if (closeSidebar) closeSidebar.addEventListener('click', closeMenu);
         if (backdrop) backdrop.addEventListener('click', closeMenu);

         document.querySelectorAll('[data-confirm-form]').forEach(function(form) {
            form.addEventListener('submit', function(event) {
               event.preventDefault();
               openConfirm(form);
            });
         });

         if (confirmCancel) confirmCancel.addEventListener('click', closeConfirm);
         if (confirmBackdrop) confirmBackdrop.addEventListener('click', closeConfirm);
         if (confirmAccept) confirmAccept.addEventListener('click', function() {
            if (!pendingConfirmForm) return;
            var formToSubmit = pendingConfirmForm;
            closeConfirm();
            formToSubmit.submit();
         });
         if (statusToast && statusToastBar) {
            statusToastBar.animate([{
               transform: 'scaleX(1)'
            }, {
               transform: 'scaleX(0)'
            }], {
               duration: 4200,
               easing: 'linear',
               fill: 'forwards'
            });
            window.setTimeout(dismissToast, 4200);
         }
         if (statusToastClose) statusToastClose.addEventListener('click', dismissToast);
         if (errorToast && errorToastBar) {
            errorToastBar.animate([{
               transform: 'scaleX(1)'
            }, {
               transform: 'scaleX(0)'
            }], {
               duration: 5200,
               easing: 'linear',
               fill: 'forwards'
            });
            window.setTimeout(dismissErrorToast, 5200);
         }
         if (errorToastClose) errorToastClose.addEventListener('click', dismissErrorToast);

         document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape' && confirmModal && !confirmModal.classList.contains('hidden'))
               closeConfirm();
            if (event.key === 'Escape' && errorToast && !errorToast.classList.contains('hidden'))
               dismissErrorToast();
         });

         window.addEventListener('resize', function() {
            if (window.innerWidth >= 1024) {
               backdrop.classList.add('hidden');
               sidebar.classList.remove('-translate-x-full');
            } else {
               sidebar.classList.add('-translate-x-full');
            }
         });

         window.trackUpload = function(xhr, fileName) {
            const modal = document.getElementById('upload-progress-modal');
            const progressBar = document.getElementById('upload-progress-bar');
            const percentageText = document.getElementById('upload-percentage');
            const bytesText = document.getElementById('upload-bytes');
            const speedText = document.getElementById('upload-speed-info');
            const nameText = document.getElementById('upload-file-name');

            if (!modal) return;
            nameText.textContent = fileName || 'Uploading file...';
            modal.classList.remove('hidden');
            modal.classList.add('flex', 'items-center', 'justify-center');

            let startTime = Date.now();

            xhr.upload.addEventListener('progress', function(e) {
               if (e.lengthComputable) {
                  const percent = Math.round((e.loaded / e.total) * 100);
                  progressBar.style.width = percent + '%';
                  percentageText.textContent = percent + '%';
                  
                  const loadedMb = (e.loaded / (1024 * 1024)).toFixed(2);
                  const totalMb = (e.total / (1024 * 1024)).toFixed(2);
                  bytesText.textContent = `${loadedMb} / ${totalMb} MB`;

                  const duration = (Date.now() - startTime) / 1000;
                  if (duration > 0.2) {
                     const speedbps = (e.loaded * 8) / duration;
                     const speedMbps = (speedbps / (1024 * 1024)).toFixed(2);
                     speedText.textContent = `${speedMbps} Mbps • Transferring data...`;
                  }
               }
            });

            xhr.upload.addEventListener('load', function() {
               speedText.textContent = '100% Uploaded • Processing on server...';
               progressBar.classList.add('animate-pulse');
            });

            xhr.addEventListener('load', function() {
               setTimeout(() => {
                  modal.classList.add('hidden');
                  modal.classList.remove('flex', 'items-center', 'justify-center');
                  progressBar.style.width = '0%';
                  progressBar.classList.remove('animate-pulse');
               }, 800);
            });

            xhr.addEventListener('error', function() {
               modal.classList.add('hidden');
               modal.classList.remove('flex', 'items-center', 'justify-center');
            });
         };
      })();
   </script>
   @stack('scripts')
    @include('partials.native-toast')
</body>

</html>


