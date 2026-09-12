@extends('layouts.app')

@section('title', 'Pembayaran QRIS - AnabulMart Medan')

@section('content')
<div class="max-w-2xl mx-auto px-4 py-8">
    @php
        $orderNumber     = $order->order_number ?? '-';
        $customerName    = $order->customer_name ?? '-';
        $customerPhone   = $order->customer_whatsapp ?? '-';
        $shippingAddress = $order->shipping_address ?? '-';
        $totalPrice      = $order->total_amount ?? ($order->subtotal ?? 0);
        $itemsCount      = $order->items ? $order->items->count() : 0;
    @endphp

    @if(session('success'))
        <div class="mb-4 p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-bold rounded-xl flex items-center justify-between">
            <span><i class="fa-solid fa-circle-check mr-2"></i>{{ session('success') }}</span>
            <button onclick="this.parentElement.remove()" class="text-emerald-500 hover:text-emerald-700">&times;</button>
        </div>
    @endif

    <div class="bg-white p-6 sm:p-8 rounded-3xl border border-gray-200 shadow-sm space-y-6 text-center">
        <!-- HEADER -->
        <div>
            <div class="w-12 h-12 bg-amber-100 text-amber-600 rounded-2xl flex items-center justify-center mx-auto mb-3 text-xl font-bold">
                <i class="fa-solid fa-qrcode"></i>
            </div>
            <h1 class="text-xl font-black text-gray-800">Pembayaran QRIS AnabulMart</h1>
            <p class="text-xs text-gray-500 mt-1">Scan kode QRIS di bawah ini menggunakan e-wallet / Mobile Banking Anda</p>
        </div>

        <!-- NOMOR PESANAN & TOTAL HARGA AKURAT -->
        <div class="p-4 bg-gray-50 rounded-2xl border border-gray-100 space-y-1">
            <span class="text-[10px] uppercase font-bold tracking-wider text-gray-400">Nomor Pesanan</span>
            <div class="text-sm font-mono font-bold text-amber-600">{{ $orderNumber }}</div>
            <div class="text-2xl font-black text-emerald-600 pt-2 border-t border-gray-200/60 mt-2">
                Rp {{ number_format($totalPrice, 0, ',', '.') }}
            </div>
        </div>

        <!-- GAMBAR QRIS -->
        <div class="p-4 bg-white rounded-2xl border-2 border-amber-400 inline-block shadow-inner max-w-xs mx-auto">
            <img src="https://api.qrserver.com/v1/create-qr-code/?size=250x250&data=AnabulMart-{{ $orderNumber }}-{{ $totalPrice }}" 
                 alt="QRIS Pembayaran" 
                 class="w-48 h-48 mx-auto rounded-lg object-contain">
            <span class="block text-[10px] text-gray-400 font-bold mt-2">NMSO: ID1029384756201 (AnabulMart Medan)</span>
        </div>

        <!-- RINGKASAN DATA PENGIRIMAN -->
        <div class="text-left bg-gray-50 p-4 rounded-2xl border border-gray-100 text-xs space-y-2">
            <div class="flex justify-between border-b border-gray-200/60 pb-1.5">
                <span class="text-gray-500 font-semibold">Nama Penerima:</span>
                <span class="font-bold text-gray-800">{{ $customerName }} ({{ $customerPhone }})</span>
            </div>
            <div class="flex justify-between border-b border-gray-200/60 pb-1.5">
                <span class="text-gray-500 font-semibold">Alamat Tujuan:</span>
                <span class="font-bold text-gray-800 truncate max-w-[200px]">{{ $shippingAddress }}</span>
            </div>
            <div class="flex justify-between">
                <span class="text-gray-500 font-semibold">Total Item:</span>
                <span class="font-bold text-gray-800">{{ $itemsCount }} Produk</span>
            </div>
        </div>

        <!-- FORM UPLOAD BUKTI PEMBAYARAN -->
        <div class="pt-4 border-t border-gray-100 text-left">
            <h3 class="text-xs font-bold text-gray-800 uppercase tracking-wider mb-2 flex items-center gap-2">
                <i class="fa-solid fa-file-invoice-dollar text-amber-500"></i> Upload Bukti Transfer / Pembayaran
            </h3>
            
            <form action="{{ route('checkout.upload', $orderNumber) }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                @csrf
                <div>
                    <input type="file" name="payment_proof" id="paymentProofInput" required accept="image/*" class="w-full text-xs text-gray-500 border border-gray-300 rounded-xl p-2 bg-gray-50 focus:outline-none">
                    <span class="text-[10px] text-gray-400 block mt-1">*Format gambar: JPG, PNG, WEBP (Maksimal 2MB)</span>
                </div>

                <button type="submit" class="w-full py-3.5 bg-amber-500 hover:bg-amber-600 text-white font-bold text-xs rounded-xl shadow-md transition-all flex items-center justify-center gap-2">
                    <i class="fa-solid fa-paper-plane"></i> Kirim Bukti Pembayaran
                </button>
            </form>
        </div>

        <!-- TOMBOL AKSI -->
        <div class="pt-2 flex flex-col sm:flex-row gap-3">
            <a href="{{ route('catalog.index') }}" class="flex-1 py-3 bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold text-xs rounded-xl transition-all">
                Kembali Belanja
            </a>
            <a href="{{ route('order.status') }}?order_number={{ $orderNumber }}" class="flex-1 py-3 bg-slate-800 hover:bg-slate-900 text-white font-bold text-xs rounded-xl transition-all shadow-md">
                Lacak Status Pesanan
            </a>
        </div>
    </div>
</div>
@endsection