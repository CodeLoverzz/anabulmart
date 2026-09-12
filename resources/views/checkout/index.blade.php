@extends('layouts.app')

@section('title', 'Checkout Pesanan - AnabulMart Medan')

@section('content')
<div class="max-w-6xl mx-auto px-4 py-8">
    <h1 class="text-2xl font-black text-gray-800 mb-6">Checkout Pesanan</h1>

    @if(session('error'))
        <div class="mb-4 p-4 bg-red-50 border border-red-200 text-red-800 text-xs font-bold rounded-xl flex items-center justify-between">
            <span><i class="fa-solid fa-triangle-exclamation mr-2"></i>{{ session('error') }}</span>
            <button onclick="this.parentElement.remove()" class="text-red-500 hover:text-red-700">&times;</button>
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- FORM PENGIRIMAN -->
        <div class="lg:col-span-2 space-y-6">
            <form action="{{ route('checkout.store') }}" method="POST" id="checkoutForm" class="space-y-6">
                @csrf
                <input type="hidden" name="checkout_type" value="{{ $checkoutType ?? 'cart' }}">

                <div class="bg-white p-6 rounded-2xl border border-gray-200 shadow-sm space-y-4">
                    <h2 class="text-sm font-bold text-gray-800 border-b border-gray-100 pb-3 flex items-center gap-2">
                        <i class="fa-solid fa-user text-amber-500"></i> Informasi Pembeli & Pengiriman
                    </h2>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 mb-1">Nama Lengkap *</label>
                            <input type="text" name="customer_name" required placeholder="Masukkan nama penerima" class="w-full text-xs border border-gray-300 rounded-xl px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-amber-500">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 mb-1">Nomor WhatsApp *</label>
                            <input type="text" name="customer_phone" required placeholder="Contoh: 081234567890" class="w-full text-xs border border-gray-300 rounded-xl px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-amber-500">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1">Alamat Lengkap Pengiriman *</label>
                        <textarea name="shipping_address" rows="3" required placeholder="Nama jalan, nomor rumah, kecamatan, Kota Medan" class="w-full text-xs border border-gray-300 rounded-xl px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-amber-500"></textarea>
                    </div>
                </div>

                <button type="submit" class="w-full py-3.5 bg-amber-500 hover:bg-amber-600 text-white font-bold text-xs rounded-xl shadow-md transition-all flex items-center justify-center gap-2">
                    <i class="fa-solid fa-lock"></i> Buat Pesanan & Bayar Sekarang
                </button>
            </form>
        </div>

        <!-- RINGKASAN HARGA -->
        <div class="bg-white p-6 rounded-2xl border border-gray-200 shadow-sm h-fit space-y-4">
            <h2 class="text-sm font-bold text-gray-800 border-b border-gray-100 pb-3 flex items-center gap-2">
                <i class="fa-solid fa-bag-shopping text-amber-500"></i> Ringkasan Item
            </h2>

            @php
                $items = $itemsToCheckout ?? session()->get('cart', []);
                $calculatedSubtotal = 0;
            @endphp

            <div class="space-y-3 max-h-80 overflow-y-auto pr-1">
                @forelse($items as $item)
                    @php
                        $itemPrice = $item['price'] ?? 0;
                        $itemQty = $item['quantity'] ?? 1;
                        $totalItemPrice = $itemPrice * $itemQty;
                        $calculatedSubtotal += $totalItemPrice;

                        $rawImg = $item['image'] ?? null;
                        $imgUrl = $rawImg ? asset('storage/' . ltrim(str_replace(['public/', 'storage/'], '', $rawImg), '/')) : 'https://placehold.co/100x100?text=No+Foto';
                    @endphp

                    <div class="flex items-center gap-3 p-2 bg-gray-50 rounded-xl border border-gray-100">
                        <div class="w-12 h-12 bg-white rounded-lg overflow-hidden border border-gray-200 flex-shrink-0">
                            <img src="{{ $imgUrl }}" onerror="this.onerror=null; this.src='https://placehold.co/100x100?text=Gagal+Muat';" class="w-full h-full object-cover">
                        </div>
                        <div class="flex-1 min-w-0">
                            <h4 class="text-xs font-bold text-gray-800 truncate">{{ $item['product_name'] ?? 'Produk' }}</h4>
                            <p class="text-[10px] text-amber-600 font-semibold">
                                Variasi: {{ $item['variant_name'] ?? 'Default' }} (x{{ $itemQty }})
                            </p>
                        </div>
                        <div class="text-xs font-black text-emerald-600">
                            Rp {{ number_format($totalItemPrice, 0, ',', '.') }}
                        </div>
                    </div>
                @empty
                    <p class="text-xs text-gray-400 italic text-center py-4">Tidak ada item untuk di-checkout.</p>
                @endforelse
            </div>

            <div class="pt-3 border-t border-gray-100 space-y-2">
                <div class="flex justify-between text-xs text-gray-600 font-semibold">
                    <span>Subtotal Produk:</span>
                    <span class="text-gray-800 font-bold">Rp {{ number_format($subtotal ?? $calculatedSubtotal, 0, ',', '.') }}</span>
                </div>
                <div class="flex justify-between text-xs text-gray-600 font-semibold">
                    <span>Ongkos Kirim:</span>
                    <span class="text-emerald-600 font-bold">Gratis (Kurir Toko Medan)</span>
                </div>
                <div class="pt-2 border-t border-gray-100 flex justify-between text-sm font-black text-gray-800">
                    <span>Total Pembayaran:</span>
                    <span class="text-emerald-600">Rp {{ number_format($subtotal ?? $calculatedSubtotal, 0, ',', '.') }}</span>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection