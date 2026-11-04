<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Sajian Marinasi - Admin Management')</title>

    <!-- Google Fonts: Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                        mono: ['ui-monospace', 'SFMono-Regular', 'Menlo', 'Monaco', 'Consolas', 'monospace'],
                    },
                    colors: {
                        zinc: {
                            50: '#fafafa',
                            100: '#f4f4f5',
                            200: '#e4e4e7',
                            300: '#d4d4d8',
                            400: '#a1a1aa',
                            500: '#71717a',
                            600: '#52525b',
                            700: '#3f3f46',
                            800: '#27272a',
                            900: '#18181b',
                            950: '#09090b',
                        }
                    },
                    boxShadow: {
                        '2xs': '0 1px 2px 0 rgba(0, 0, 0, 0.03)',
                    }
                }
            }
        }
    </script>

    <!-- Chart.js CDN -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <style>
        body { font-family: 'Inter', sans-serif; }
        [x-cloak] { display: none !important; }
        .tabular-nums { font-variant-numeric: tabular-nums; }
        @media print {
            .no-print { display: none !important; }
            .print-full { width: 100% !important; margin: 0 !important; padding: 0 !important; }
            body { background: #ffffff !important; }
            aside { display: none !important; }
            main { padding-left: 0 !important; padding-top: 0 !important; }
        }
    </style>
    @stack('styles')
</head>
<body class="bg-zinc-50 text-zinc-900 font-sans antialiased min-h-screen flex flex-col">

    <!-- FIXED LEFT SIDEBAR (w-60, bg-zinc-950) -->
    <aside class="no-print w-60 fixed inset-y-0 left-0 bg-zinc-950 border-r border-zinc-800 flex flex-col z-40 text-zinc-300">
        <!-- Brand Header -->
        <div class="h-14 px-5 flex items-center justify-between border-b border-zinc-800">
            <a href="{{ route('dashboard') }}" class="flex items-center space-x-2.5">
                <div class="w-7 h-7 rounded-md bg-zinc-800 border border-zinc-700 flex items-center justify-center text-amber-500 font-bold text-xs tracking-wider">
                    SM
                </div>
                <div>
                    <h1 class="text-xs font-semibold text-white tracking-tight leading-none">Sajian Marinasi</h1>
                    <span class="text-[10px] text-zinc-400 block mt-1">Admin Management</span>
                </div>
            </a>
        </div>

        <!-- Navigation Menu -->
        <nav class="flex-1 overflow-y-auto px-3 py-4 space-y-1">
            <div class="px-2 pb-2 text-[10px] font-medium uppercase tracking-wider text-zinc-400">
                Menu Operasional
            </div>

            <!-- 1. Dashboard Analitik -->
            <a href="{{ route('dashboard') }}" 
               class="flex items-center space-x-2.5 px-2.5 py-2 rounded-md text-xs transition-colors {{ request()->routeIs('dashboard') ? 'bg-zinc-900 text-white font-medium border-l-2 border-amber-500' : 'text-zinc-400 hover:text-zinc-100 hover:bg-zinc-900' }}">
                <svg class="w-4 h-4 {{ request()->routeIs('dashboard') ? 'text-amber-500' : 'text-zinc-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                </svg>
                <span>Dashboard</span>
            </a>

            <!-- 2. Stok & Produk -->
            <a href="{{ route('products.index') }}" 
               class="flex items-center space-x-2.5 px-2.5 py-2 rounded-md text-xs transition-colors {{ request()->routeIs('products.*') ? 'bg-zinc-900 text-white font-medium border-l-2 border-amber-500' : 'text-zinc-400 hover:text-zinc-100 hover:bg-zinc-900' }}">
                <svg class="w-4 h-4 {{ request()->routeIs('products.*') ? 'text-amber-500' : 'text-zinc-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                </svg>
                <span>Stok & Produk</span>
            </a>

            <!-- 3. Transaksi Customer -->
            <a href="{{ route('orders.index') }}" 
               class="flex items-center space-x-2.5 px-2.5 py-2 rounded-md text-xs transition-colors {{ request()->routeIs('orders.*') ? 'bg-zinc-900 text-white font-medium border-l-2 border-amber-500' : 'text-zinc-400 hover:text-zinc-100 hover:bg-zinc-900' }}">
                <svg class="w-4 h-4 {{ request()->routeIs('orders.*') ? 'text-amber-500' : 'text-zinc-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                </svg>
                <span>Transaksi Penjualan</span>
            </a>

            <!-- 4. Pengeluaran Belanja -->
            <a href="{{ route('expenses.index') }}" 
               class="flex items-center space-x-2.5 px-2.5 py-2 rounded-md text-xs transition-colors {{ request()->routeIs('expenses.*') ? 'bg-zinc-900 text-white font-medium border-l-2 border-amber-500' : 'text-zinc-400 hover:text-zinc-100 hover:bg-zinc-900' }}">
                <svg class="w-4 h-4 {{ request()->routeIs('expenses.*') ? 'text-amber-500' : 'text-zinc-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2z" />
                </svg>
                <span>Pengeluaran Belanja</span>
            </a>

            <!-- 5. Laporan & Omzet -->
            <a href="{{ route('reports.index') }}" 
               class="flex items-center space-x-2.5 px-2.5 py-2 rounded-md text-xs transition-colors {{ request()->routeIs('reports.*') ? 'bg-zinc-900 text-white font-medium border-l-2 border-amber-500' : 'text-zinc-400 hover:text-zinc-100 hover:bg-zinc-900' }}">
                <svg class="w-4 h-4 {{ request()->routeIs('reports.*') ? 'text-amber-500' : 'text-zinc-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                <span>Laporan & Omzet</span>
            </a>
        </nav>

        <!-- Sidebar Footer Action -->
        <div class="p-3 border-t border-zinc-800 space-y-2">
            <a href="{{ route('orders.create') }}" class="w-full py-2 px-3 bg-zinc-900 hover:bg-zinc-800 text-white text-xs font-medium rounded-md border border-zinc-700 flex items-center justify-center space-x-1.5 transition-colors">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                <span>+ Kasir / Order Baru</span>
            </a>
            <div class="flex items-center justify-between text-[11px] text-zinc-400 px-1">
                <span>Database</span>
                <span class="inline-flex items-center text-emerald-400">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 mr-1.5"></span>
                    Tersambung
                </span>
            </div>
        </div>
    </aside>

    <!-- MAIN CONTENT CONTAINER (Pushed right by w-60) -->
    <div class="pl-60 flex flex-col flex-1 min-h-screen">

        <!-- Top Header Bar -->
        <header class="no-print h-14 bg-white border-b border-zinc-200 sticky top-0 z-30 px-6 flex items-center justify-between">
            <div class="flex items-center space-x-3">
                <h1 class="text-sm font-semibold text-zinc-900">
                    @yield('header_title', 'Dashboard')
                </h1>
            </div>

            <div class="flex items-center space-x-3">
                <div class="text-xs text-zinc-400 tabular-nums hidden sm:block">
                    {{ now()->translatedFormat('l, d F Y') }}
                </div>
                <div class="h-4 w-px bg-zinc-200 hidden sm:block"></div>
                <div class="flex items-center space-x-2">
                    <div class="w-6 h-6 rounded-md bg-zinc-100 border border-zinc-200 text-zinc-700 font-medium text-[11px] flex items-center justify-center">
                        SM
                    </div>
                    <span class="text-xs font-medium text-zinc-800">Admin</span>
                </div>
            </div>
        </header>

        <!-- Flash Messages & Errors -->
        <div class="no-print px-6 pt-5">
            @if(session('success'))
                <div x-data="{ show: true }" x-show="show" class="mb-4 px-4 py-3 rounded-lg bg-emerald-50 border border-emerald-200 text-emerald-800 flex items-center justify-between text-xs">
                    <div class="flex items-center space-x-2.5">
                        <svg class="w-4 h-4 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        <span class="font-medium">{{ session('success') }}</span>
                    </div>
                    <button @click="show = false" class="text-emerald-600 hover:text-emerald-800 p-0.5">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                    </button>
                </div>
            @endif

            @if(session('error'))
                <div x-data="{ show: true }" x-show="show" class="mb-4 px-4 py-3 rounded-lg bg-rose-50 border border-rose-200 text-rose-800 flex items-center justify-between text-xs">
                    <div class="flex items-center space-x-2.5">
                        <svg class="w-4 h-4 text-rose-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                        <span class="font-medium">{{ session('error') }}</span>
                    </div>
                    <button @click="show = false" class="text-rose-600 hover:text-rose-800 p-0.5">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                    </button>
                </div>
            @endif

            @if(session('info'))
                <div x-data="{ show: true }" x-show="show" class="mb-4 px-4 py-3 rounded-lg bg-zinc-100 border border-zinc-200 text-zinc-800 flex items-center justify-between text-xs">
                    <div class="flex items-center space-x-2.5">
                        <svg class="w-4 h-4 text-zinc-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <span class="font-medium">{{ session('info') }}</span>
                    </div>
                    <button @click="show = false" class="text-zinc-600 hover:text-zinc-800 p-0.5">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                    </button>
                </div>
            @endif

            @if($errors->any())
                <div x-data="{ show: true }" x-show="show" class="mb-4 px-4 py-3 rounded-lg bg-amber-50 border border-amber-200 text-amber-900 text-xs">
                    <div class="flex items-start justify-between">
                        <div>
                            <span class="font-semibold block mb-1">Periksa kembali data masukan Anda:</span>
                            <ul class="list-disc list-inside space-y-0.5 text-amber-800">
                                @foreach($errors->all() as $err)
                                    <li>{{ $err }}</li>
                                @endforeach
                            </ul>
                        </div>
                        <button @click="show = false" class="text-amber-700 hover:text-amber-900 p-0.5">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                        </button>
                    </div>
                </div>
            @endif
        </div>

        <!-- Main Page Content -->
        <main class="flex-1 px-6 py-5 w-full">
            @yield('content')
        </main>

        <!-- Footer -->
        <footer class="no-print bg-white border-t border-zinc-200 mt-auto py-3 px-6 text-center text-xs text-zinc-400">
            &copy; {{ date('Y') }} Sajian Marinasi — Admin Management System.
        </footer>
    </div>

    @stack('scripts')
</body>
</html>
