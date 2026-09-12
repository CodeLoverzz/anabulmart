<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Admin - AnabulMart')</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gray-100 font-sans antialiased">

    <div class="flex h-screen overflow-hidden">
        <!-- SIDEBAR ADMIN -->
        <aside class="w-64 bg-slate-900 text-white flex flex-col justify-between shadow-lg">
            <div>
                <!-- Logo & Brand -->
                <div class="p-5 border-b border-slate-800 flex items-center space-x-3">
                    <i class="fa-solid fa-paw text-amber-500 text-2xl"></i>
                    <div>
                        <h1 class="font-bold text-lg leading-none tracking-wide">[ADMIN]</h1>
                        <span class="text-xs text-slate-400">ANABULMART</span>
                    </div>
                </div>

                <!-- Navigasi Sidebar -->
                <nav class="mt-4 px-3 space-y-1">
                    <p class="px-3 text-[10px] font-semibold text-slate-400 uppercase tracking-wider mb-2">MENU UTAMA</p>
                    
                    <!-- 1. Dashboard Ringkasan -->
                    <a href="{{ route('admin.dashboard') }}" 
                       class="flex items-center px-3 py-2.5 text-sm font-medium rounded-lg transition-all {{ request()->routeIs('admin.dashboard') ? 'bg-amber-500 text-white shadow' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                        <i class="fa-solid fa-chart-pie w-6 text-center"></i>
                        <span>Dashboard</span>
                    </a>

                    <!-- 2. Data Produk -->
                    <a href="{{ route('admin.products.index') }}" 
                       class="flex items-center px-3 py-2.5 text-sm font-medium rounded-lg transition-all {{ request()->routeIs('admin.products.*') ? 'bg-amber-500 text-white shadow' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                        <i class="fa-solid fa-box w-6 text-center"></i>
                        <span>Data Produk</span>
                    </a>

                    <!-- 3. Data Transaksi -->
                    <a href="{{ route('admin.orders.index') }}" 
                       class="flex items-center px-3 py-2.5 text-sm font-medium rounded-lg transition-all {{ request()->routeIs('admin.orders.*') ? 'bg-amber-500 text-white shadow' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                        <i class="fa-solid fa-receipt w-6 text-center"></i>
                        <span>Data Transaksi</span>
                    </a>

                    <!-- 4. Pelanggan -->
                    <a href="{{ route('admin.customers.index') }}" 
                       class="flex items-center px-3 py-2.5 text-sm font-medium rounded-lg transition-all {{ request()->routeIs('admin.customers.*') ? 'bg-amber-500 text-white shadow' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                        <i class="fa-solid fa-users w-6 text-center"></i>
                        <span>Pelanggan</span>
                    </a>

                    <!-- 5. Laporan -->
                    <a href="{{ route('admin.reports.index') }}" 
                       class="flex items-center px-3 py-2.5 text-sm font-medium rounded-lg transition-all {{ request()->routeIs('admin.reports.*') ? 'bg-amber-500 text-white shadow' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                        <i class="fa-solid fa-chart-line w-6 text-center"></i>
                        <span>Laporan</span>
                    </a>
                </nav>
            </div>

            <!-- Profile & Logout -->
            <div class="p-4 border-t border-slate-800">
                <div class="flex items-center space-x-3 mb-3">
                    <div class="w-9 h-9 rounded-full bg-amber-500 flex items-center justify-center text-slate-900 font-bold">
                        <i class="fa-solid fa-user"></i>
                    </div>
                    <div>
                        <p class="text-sm font-semibold text-white">Admin Toko</p>
                        <p class="text-xs text-slate-400">AnabulMart Medan</p>
                    </div>
                </div>
                <form action="{{ route('admin.logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="w-full py-2 px-3 text-xs font-semibold text-red-400 border border-red-500/30 rounded-lg hover:bg-red-500/10 transition-all text-left flex items-center justify-center gap-2">
                        <i class="fa-solid fa-right-from-bracket"></i> Logout Sesi
                    </button>
                </form>
            </div>
        </aside>

        <!-- KONTEN UTAMA DENGAN HEADER ATAS -->
        <div class="flex-1 flex flex-col overflow-y-auto">
            <!-- Header Top Bar (Tanpa Badge WA) -->
            <header class="bg-white border-b border-gray-200 px-8 py-4 flex items-center justify-between shadow-sm">
                <h2 class="text-xl font-bold text-gray-800">@yield('page_title', 'Dashboard')</h2>
                <div class="text-xs text-gray-500 font-medium">
                    <i class="fa-regular fa-calendar-days mr-1"></i> {{ \Carbon\Carbon::now()->translatedFormat('d F Y') }}
                </div>
            </header>

            <!-- Main Content Area -->
            <main class="p-8">
                @if(session('success'))
                    <div class="mb-6 bg-emerald-50 border-l-4 border-emerald-500 p-4 text-emerald-800 text-sm font-medium rounded shadow-sm">
                        {{ session('success') }}
                    </div>
                @endif

                @yield('content')
            </main>
        </div>
    </div>

</body>
</html>