@extends('layouts.admin')

@section('title', 'Dashboard Utama - Admin AnabulMart')
@section('page_title', 'Ringkasan Dashboard')

@section('content')
<!-- Ringkasan Kartu Statistik -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
    <div class="bg-white p-6 rounded-xl border border-gray-200 shadow-sm flex items-center justify-between">
        <div>
            <p class="text-sm font-medium text-gray-500">Total Transaksi</p>
            <h3 class="text-3xl font-bold text-gray-800 mt-1">{{ $totalOrders }}</h3>
        </div>
        <div class="w-12 h-12 bg-amber-100 text-amber-600 rounded-lg flex items-center justify-center text-xl">
            <i class="fa-solid fa-receipt"></i>
        </div>
    </div>

    <div class="bg-white p-6 rounded-xl border border-gray-200 shadow-sm flex items-center justify-between">
        <div>
            <p class="text-sm font-medium text-gray-500">Pesanan Pending</p>
            <h3 class="text-3xl font-bold text-amber-600 mt-1">{{ $pendingOrders }}</h3>
        </div>
        <div class="w-12 h-12 bg-amber-50 text-amber-500 rounded-lg flex items-center justify-center text-xl">
            <i class="fa-solid fa-clock"></i>
        </div>
    </div>

    <div class="bg-white p-6 rounded-xl border border-gray-200 shadow-sm flex items-center justify-between">
        <div>
            <p class="text-sm font-medium text-gray-500">Total Produk Active</p>
            <h3 class="text-3xl font-bold text-gray-800 mt-1">{{ $totalProducts }}</h3>
        </div>
        <div class="w-12 h-12 bg-blue-100 text-blue-600 rounded-lg flex items-center justify-center text-xl">
            <i class="fa-solid fa-box"></i>
        </div>
    </div>
</div>

<div class="bg-amber-50 border border-amber-200 p-5 rounded-xl text-amber-800">
    <h4 class="font-bold flex items-center gap-2">
        <i class="fa-solid fa-circle-info"></i> Selamat Datang di Admin Panel AnabulMart
    </h4>
    <p class="text-sm mt-1">Silakan pilih menu di sidebar sebelah kiri untuk mengelola **Data Produk**, verifikasi **Data Transaksi**, data **Pelanggan**, atau melihat **Laporan**.</p>
</div>
@endsection