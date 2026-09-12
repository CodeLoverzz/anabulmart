@extends('layouts.app')

@section('title', 'Keranjang Belanja - AnabulMart Medan')

@section('content')
<div class="max-w-6xl mx-auto px-4 py-8">
    <h1 class="text-2xl font-black text-gray-800 mb-6">Keranjang Belanja</h1>

    @if(session('success'))
        <div class="mb-4 p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-bold rounded-xl flex items-center justify-between">
            <span><i class="fa-solid fa-circle-check mr-2"></i>{{ session('success') }}</span>
            <button onclick="this.parentElement.remove()" class="text-emerald-500 hover:text-emerald-700">&times;</button>
        </div>
    @endif

    @if(session('error'))
        <div class="mb-4 p-4 bg-red-50 border border-red-200 text-red-800 text-xs font-bold rounded-xl flex items-center justify-between">
            <span><i class="fa-solid fa-triangle-exclamation mr-2"></i>{{ session('error') }}</span>
            <button onclick="this.parentElement.remove()" class="text-red-500 hover:text-red-700">&times;</button>
        </div>
    @endif

    @if(!empty($cart) && count($cart) > 0)
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- LIST ITEM KERANJANG -->
            <div class="lg:col-span-2 space-y-4">
                @php $grandTotal = 0; @endphp
                @foreach($cart as $key => $item)
                    @php
                        $itemTotal = $item['price'] * $item['quantity'];
                        $isChecked = isset($item['checked']) ? $item['checked'] : true;
                        if($isChecked) { $grandTotal += $itemTotal; }

                        $imgUrl = !empty($item['image']) 
                            ? asset('storage/' . $item['image']) 
                            : 'https://placehold.co/100x100?text=No+Foto';
                    @endphp

                    <div class="bg-white p-4 rounded-2xl border border-gray-200 shadow-sm flex items-center gap-4">
                        <!-- CHECKBOX PILIH ITEM -->
                        <form action="{{ route('cart.update') }}" method="POST">
                            @csrf
                            <input type="hidden" name="key" value="{{ $key }}">
                            <input type="hidden" name="checked" value="{{ $isChecked ? 0 : 1 }}">
                            <input type="checkbox" onchange="this.form.submit()" {{ $isChecked ? 'checked' : '' }} class="w-4 h-4 text-amber-500 rounded focus:ring-amber-400 cursor-pointer">
                        </form>

                        <!-- GAMBAR PRODUK -->
                        <div class="w-16 h-16 bg-gray-50 rounded-xl overflow-hidden border border-gray-100 flex-shrink-0">
                            <img src="{{ $imgUrl }}" onerror="this.onerror=null; this.src='https://placehold.co/100x100?text=Gagal+Muat';" class="w-full h-full object-cover">
                        </div>

                        <!-- DETAIL ITEM -->
                        <div class="flex-1">
                            <h3 class="text-xs font-bold text-gray-800 line-clamp-1">{{ $item['product_name'] }}</h3>
                            <span class="text-[10px] text-amber-600 font-semibold block mb-1">Variasi: {{ $item['variant_name'] }}</span>
                            <div class="text-xs font-black text-emerald-600">Rp {{ number_format($item['price'], 0, ',', '.') }}</div>
                        </div>

                        <!-- UBAH QTY & HAPUS -->
                        <div class="flex items-center gap-3">
                            <form action="{{ route('cart.update') }}" method="POST" class="flex items-center border border-gray-200 rounded-lg overflow-hidden bg-white">
                                @csrf
                                <input type="hidden" name="key" value="{{ $key }}">
                                <input type="hidden" name="quantity" value="{{ max(1, $item['quantity'] - 1) }}">
                                <button type="submit" class="px-2 py-1 bg-gray-50 text-xs font-bold text-gray-600 hover:bg-gray-100">-</button>
                            </form>

                            <span class="text-xs font-bold text-gray-800 w-6 text-center">{{ $item['quantity'] }}</span>

                            <form action="{{ route('cart.update') }}" method="POST" class="flex items-center border border-gray-200 rounded-lg overflow-hidden bg-white">
                                @csrf
                                <input type="hidden" name="key" value="{{ $key }}">
                                <input type="hidden" name="quantity" value="{{ $item['quantity'] + 1 }}">
                                <button type="submit" class="px-2 py-1 bg-gray-50 text-xs font-bold text-gray-600 hover:bg-gray-100">+</button>
                            </form>

                            <!-- TOMBOL HAPUS -->
                            <form action="{{ route('cart.remove') }}" method="POST" class="ml-2">
                                @csrf
                                <input type="hidden" name="key" value="{{ $key }}">
                                <button type="submit" class="text-red-500 hover:text-red-700 text-xs p-1">
                                    <i class="fa-solid fa-trash"></i>
                                </button>
                            </form>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- RINGKASAN BELANJA -->
            <div class="bg-white p-6 rounded-2xl border border-gray-200 shadow-sm h-fit space-y-4">
                <h2 class="text-sm font-bold text-gray-800 border-b border-gray-100 pb-3">Ringkasan Belanja</h2>
                
                <div class="flex justify-between text-xs text-gray-600 font-semibold">
                    <span>Total Produk Tercentang:</span>
                    <span class="text-emerald-600 font-black">Rp {{ number_format($grandTotal, 0, ',', '.') }}</span>
                </div>

                <a href="{{ route('checkout.index', ['type' => 'cart']) }}" class="w-full py-3 bg-amber-500 hover:bg-amber-600 text-white font-bold text-xs rounded-xl transition-all shadow-md flex items-center justify-center gap-2 block text-center">
                    Lanjut Checkout Barang Tercentang <i class="fa-solid fa-arrow-right text-[10px]"></i>
                </a>
            </div>
        </div>
    @else
        <div class="bg-white p-12 rounded-3xl border border-gray-200 text-center space-y-3">
            <i class="fa-solid fa-cart-shopping text-4xl text-gray-300"></i>
            <h3 class="text-sm font-bold text-gray-700">Keranjang Belanja Masih Kosong</h3>
            <p class="text-xs text-gray-400">Yuk cari produk impian anabulmu sekarang!</p>
            <a href="{{ route('catalog.index') }}" class="inline-block px-5 py-2.5 bg-amber-500 text-white font-bold text-xs rounded-xl hover:bg-amber-600 transition-all">
                Mulai Belanja
            </a>
        </div>
    @endif
</div>
@endsection