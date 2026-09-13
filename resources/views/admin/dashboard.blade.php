@extends('layouts.admin')

@section('title', 'Dashboard Utama - Admin AnabulMart')
@section('page_title', 'Ringkasan Dashboard')

@section('content')

<!-- ================= KARTU RINGKASAN ================= -->
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-5 mb-8">
    <div class="bg-white p-5 rounded-xl border border-gray-200 shadow-sm">
        <div class="w-10 h-10 bg-amber-100 text-amber-600 rounded-lg flex items-center justify-center text-lg mb-3">
            <i class="fa-solid fa-receipt"></i>
        </div>
        <p class="text-xs font-medium text-gray-500">Total Transaksi</p>
        <h3 class="text-2xl font-bold text-gray-800 mt-0.5">{{ $totalOrders }}</h3>
    </div>

    <div class="bg-white p-5 rounded-xl border border-gray-200 shadow-sm">
        <div class="w-10 h-10 bg-emerald-100 text-emerald-600 rounded-lg flex items-center justify-center text-lg mb-3">
            <i class="fa-solid fa-sack-dollar"></i>
        </div>
        <p class="text-xs font-medium text-gray-500">Total Omzet</p>
        <h3 class="text-xl font-bold text-gray-800 mt-0.5">Rp {{ number_format($totalRevenue, 0, ',', '.') }}</h3>
    </div>

    <div class="bg-white p-5 rounded-xl border border-gray-200 shadow-sm">
        <div class="w-10 h-10 bg-amber-50 text-amber-500 rounded-lg flex items-center justify-center text-lg mb-3">
            <i class="fa-solid fa-clock"></i>
        </div>
        <p class="text-xs font-medium text-gray-500">Pesanan Pending</p>
        <h3 class="text-2xl font-bold text-amber-600 mt-0.5">{{ $pendingOrders }}</h3>
    </div>

    <div class="bg-white p-5 rounded-xl border border-gray-200 shadow-sm">
        <div class="w-10 h-10 bg-blue-100 text-blue-600 rounded-lg flex items-center justify-center text-lg mb-3">
            <i class="fa-solid fa-box"></i>
        </div>
        <p class="text-xs font-medium text-gray-500">Total Produk</p>
        <h3 class="text-2xl font-bold text-gray-800 mt-0.5">{{ $totalProducts }}</h3>
    </div>

    <div class="bg-white p-5 rounded-xl border border-gray-200 shadow-sm">
        <div class="w-10 h-10 bg-pink-100 text-pink-600 rounded-lg flex items-center justify-center text-lg mb-3">
            <i class="fa-solid fa-users"></i>
        </div>
        <p class="text-xs font-medium text-gray-500">Total Pelanggan</p>
        <h3 class="text-2xl font-bold text-gray-800 mt-0.5">{{ $totalCustomers }}</h3>
    </div>
</div>

<!-- ================= BARIS 1: OMZET HARIAN + STATUS PESANAN ================= -->
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
    <div class="lg:col-span-2 bg-white p-6 rounded-2xl border border-gray-200 shadow-sm">
        <h3 class="text-sm font-bold text-gray-800 mb-1">Omzet 7 Hari Terakhir</h3>
        <p class="text-xs text-gray-400 mb-4">Total pendapatan dari transaksi yang sudah selesai, per hari.</p>
        <div class="h-64">
            <canvas id="salesChart"></canvas>
        </div>
    </div>

    <div class="bg-white p-6 rounded-2xl border border-gray-200 shadow-sm">
        <h3 class="text-sm font-bold text-gray-800 mb-1">Status Pesanan</h3>
        <p class="text-xs text-gray-400 mb-4">Distribusi seluruh pesanan berdasarkan status saat ini.</p>
        <div class="h-64 flex items-center justify-center">
            <canvas id="statusChart"></canvas>
        </div>
    </div>
</div>

<!-- ================= BARIS 2: PRODUK TERLARIS + KATEGORI ================= -->
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
    <div class="lg:col-span-2 bg-white p-6 rounded-2xl border border-gray-200 shadow-sm">
        <h3 class="text-sm font-bold text-gray-800 mb-1">5 Produk Terlaris</h3>
        <p class="text-xs text-gray-400 mb-4">Berdasarkan jumlah unit terjual dari seluruh riwayat transaksi.</p>
        <div class="h-64">
            <canvas id="topProductChart"></canvas>
        </div>
    </div>

    <div class="bg-white p-6 rounded-2xl border border-gray-200 shadow-sm">
        <h3 class="text-sm font-bold text-gray-800 mb-1">Omzet per Kategori</h3>
        <p class="text-xs text-gray-400 mb-4">Kategori produk mana yang paling menyumbang omzet.</p>
        <div class="h-64 flex items-center justify-center">
            <canvas id="categoryChart"></canvas>
        </div>
    </div>
</div>

<!-- ================= BARIS 3: TOP PELANGGAN + PESANAN TERBARU ================= -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
    <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">
        <div class="p-5 border-b border-gray-100">
            <h3 class="text-sm font-bold text-gray-800">Top 5 Pelanggan</h3>
            <p class="text-xs text-gray-400">Berdasarkan akumulasi total belanja.</p>
        </div>
        <div class="divide-y divide-gray-100">
            @forelse($topCustomers as $i => $c)
                <div class="flex items-center justify-between px-5 py-3">
                    <div class="flex items-center gap-3">
                        <span class="w-7 h-7 rounded-full bg-amber-100 text-amber-700 text-xs font-bold flex items-center justify-center">{{ $i + 1 }}</span>
                        <div>
                            <p class="text-xs font-bold text-gray-800">{{ $c->name ?: 'Tanpa Nama' }}</p>
                            <p class="text-[10px] text-gray-400">{{ $c->phone }} &bull; {{ $c->total_orders }}x order</p>
                        </div>
                    </div>
                    <span class="text-xs font-black text-emerald-600">Rp {{ number_format($c->total_spent, 0, ',', '.') }}</span>
                </div>
            @empty
                <p class="text-xs text-gray-400 italic text-center py-8">Belum ada data pelanggan.</p>
            @endforelse
        </div>
    </div>

    <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">
        <div class="p-5 border-b border-gray-100 flex justify-between items-center">
            <h3 class="text-sm font-bold text-gray-800">Pesanan Terbaru</h3>
            <a href="{{ route('admin.orders.index') }}" class="text-[11px] font-bold text-amber-600 hover:underline">Lihat semua</a>
        </div>
        <div class="divide-y divide-gray-100">
            @forelse($recentOrders as $order)
                <div class="flex items-center justify-between px-5 py-3">
                    <div>
                        <p class="text-xs font-bold text-gray-800">#{{ $order->order_number ?? $order->id }}</p>
                        <p class="text-[10px] text-gray-400">{{ $order->customer_name }}</p>
                    </div>
                    <div class="text-right">
                        <p class="text-xs font-black text-gray-800">Rp {{ number_format($order->total_amount, 0, ',', '.') }}</p>
                        <span class="text-[10px] text-gray-400">{{ ucwords(str_replace('_',' ', $order->status)) }}</span>
                    </div>
                </div>
            @empty
                <p class="text-xs text-gray-400 italic text-center py-8">Belum ada transaksi masuk.</p>
            @endforelse
        </div>
    </div>
</div>

<div class="bg-amber-50 border border-amber-200 p-5 rounded-xl text-amber-800">
    <h4 class="font-bold flex items-center gap-2 text-sm">
        <i class="fa-solid fa-circle-info"></i> Selamat Datang di Admin Panel AnabulMart
    </h4>
    <p class="text-xs mt-1">Silakan pilih menu di sidebar sebelah kiri untuk mengelola Data Produk, verifikasi Data Transaksi, data Pelanggan, atau melihat Laporan.</p>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4"></script>
<script>
(function () {
    const salesLabels     = @json($salesChartLabels);
    const salesData       = @json($salesChartData);
    const topProductLabels = @json($topProductLabels);
    const topProductData   = @json($topProductData);
    const statusLabels    = @json($statusLabels);
    const statusData      = @json($statusData);
    const categoryLabels  = @json($categoryLabels);
    const categoryData    = @json($categoryData);

    const palette = ['#f59e0b', '#10b981', '#3b82f6', '#ec4899', '#8b5cf6', '#ef4444', '#14b8a6', '#f97316'];

    // ---- BAR: Omzet 7 hari ----
    new Chart(document.getElementById('salesChart'), {
        type: 'bar',
        data: {
            labels: salesLabels,
            datasets: [{
                label: 'Omzet',
                data: salesData,
                backgroundColor: '#f59e0b',
                borderRadius: 6,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                y: {
                    ticks: {
                        callback: (v) => 'Rp ' + (v >= 1000000 ? (v/1000000).toFixed(1)+'jt' : v >= 1000 ? (v/1000)+'rb' : v)
                    }
                }
            }
        }
    });

    // ---- BAR: Produk Terlaris ----
    new Chart(document.getElementById('topProductChart'), {
        type: 'bar',
        data: {
            labels: topProductLabels.length ? topProductLabels : ['Belum ada data'],
            datasets: [{
                label: 'Unit Terjual',
                data: topProductData.length ? topProductData : [0],
                backgroundColor: '#3b82f6',
                borderRadius: 6,
            }]
        },
        options: {
            indexAxis: 'y',
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
        }
    });

    // ---- PIE: Status Pesanan ----
    new Chart(document.getElementById('statusChart'), {
        type: 'pie',
        data: {
            labels: statusLabels.length ? statusLabels : ['Belum ada data'],
            datasets: [{
                data: statusData.length ? statusData : [1],
                backgroundColor: palette,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { position: 'bottom', labels: { boxWidth: 10, font: { size: 10 } } } },
        }
    });

    // ---- PIE: Kategori Produk ----
    new Chart(document.getElementById('categoryChart'), {
        type: 'pie',
        data: {
            labels: categoryLabels.length ? categoryLabels : ['Belum ada data'],
            datasets: [{
                data: categoryData.length ? categoryData : [1],
                backgroundColor: palette,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { position: 'bottom', labels: { boxWidth: 10, font: { size: 10 } } } },
        }
    });
})();
</script>
@endsection