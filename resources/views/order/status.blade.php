@extends('layouts.app')

@section('title', 'Lacak Status Pesanan - AnabulMart Medan')

@section('content')
<div class="max-w-3xl mx-auto px-4 py-8">
    @if(session('success'))
        <div class="mb-6 p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-bold rounded-2xl flex items-center justify-between shadow-sm">
            <span><i class="fa-solid fa-circle-check mr-2 text-emerald-600 text-sm"></i>{{ session('success') }}</span>
            <button onclick="this.parentElement.remove()" class="text-emerald-500 hover:text-emerald-700 font-black">&times;</button>
        </div>
    @endif

    <div class="bg-white p-6 sm:p-8 rounded-3xl border border-gray-200 shadow-sm space-y-6">
        <div class="text-center">
            <h1 class="text-xl font-black text-gray-800">Lacak Status Pesanan</h1>
            <p class="text-xs text-gray-500 mt-1">Masukkan Nomor Invoice/Pesanan Anda (Contoh: ORD-20260730-XXXX)</p>
        </div>

        <form action="{{ route('order.status') }}" method="GET" class="flex gap-2 max-w-md mx-auto">
            <input type="text" name="order_number" value="{{ $search ?? '' }}" required placeholder="Contoh: ORD-20260730-BEVA" class="flex-1 text-xs border border-gray-300 rounded-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-amber-500 font-mono">
            <button type="submit" class="px-5 py-3 bg-amber-500 hover:bg-amber-600 text-white font-bold text-xs rounded-xl shadow-md transition-all">
                Cari Pesanan
            </button>
        </form>

        @if(!empty($search))
            @if($order)
                @php
                    $st = strtolower($order->status);

                    $shippingProofUrl = null;
                    if (!empty($order->shipping_proof)) {
                        $cleanPath = ltrim(str_replace(['public/', 'storage/'], '', $order->shipping_proof), '/');
                        $shippingProofUrl = route('media.show', ['path' => $cleanPath]);
                    }

                    $receivedProofUrl = null;
                    if (!empty($order->received_proof)) {
                        $cleanPathRec = ltrim(str_replace(['public/', 'storage/'], '', $order->received_proof), '/');
                        $receivedProofUrl = route('media.show', ['path' => $cleanPathRec]);
                    }
                @endphp

                <div class="mt-8 border-t border-gray-100 pt-6 space-y-6">
                    <div class="p-4 rounded-2xl border flex items-center justify-between
                        @if(in_array($st, ['rejected', 'ditolak', 'canceled', 'batal'])) bg-red-50 border-red-200 text-red-800
                        @elseif(in_array($st, ['pending', 'menunggu pembayaran'])) bg-amber-50 border-amber-200 text-amber-800
                        @elseif(in_array($st, ['waiting_confirmation', 'menunggu konfirmasi'])) bg-blue-50 border-blue-200 text-blue-800
                        @elseif(in_array($st, ['shipped', 'dikirim'])) bg-purple-50 border-purple-200 text-purple-800
                        @else bg-emerald-50 border-emerald-200 text-emerald-800 @endif">

                        <div>
                            <span class="text-[10px] uppercase font-bold tracking-wider opacity-75">Status Pesanan</span>
                            <h3 class="text-sm font-black capitalize">
                                @if(in_array($st, ['rejected', 'ditolak']))
                                    <i class="fa-solid fa-circle-xmark mr-1"></i> Pesanan Ditolak
                                @elseif($st === 'pending')
                                    <i class="fa-solid fa-clock mr-1"></i> Menunggu Pembayaran
                                @elseif(in_array($st, ['waiting_confirmation', 'menunggu konfirmasi']))
                                    <i class="fa-solid fa-spinner mr-1 animate-spin"></i> Menunggu Konfirmasi Admin
                                @elseif(in_array($st, ['shipped', 'dikirim']))
                                    <i class="fa-solid fa-truck-fast mr-1"></i> Pesanan Sedang Dikirim
                                @elseif(in_array($st, ['delivered', 'selesai', 'completed']))
                                    <i class="fa-solid fa-box-open mr-1"></i> Pesanan Selesai / Diterima
                                @else
                                    <i class="fa-solid fa-circle-check mr-1"></i> {{ ucfirst($order->status) }}
                                @endif
                            </h3>
                        </div>

                        <div class="text-right">
                            <span class="text-[10px] uppercase font-bold tracking-wider opacity-75">Nomor Order</span>
                            <div class="text-xs font-mono font-bold">{{ $order->order_number }}</div>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        @if($shippingProofUrl)
                            <div class="p-4 bg-purple-50/70 border border-purple-200 rounded-2xl space-y-3">
                                <div class="flex items-center justify-between">
                                    <h4 class="text-xs font-black text-purple-900 flex items-center gap-1.5">
                                        <i class="fa-solid fa-truck-ramp-box text-purple-600"></i> Foto Bukti Pengiriman
                                    </h4>
                                    <span class="text-[9px] font-bold bg-purple-200 text-purple-800 px-2 py-0.5 rounded-full">Kurir Toko</span>
                                </div>

                                <div class="overflow-hidden rounded-xl border border-purple-200 shadow-sm bg-white">
                                    <img src="{{ $shippingProofUrl }}"
                                         alt="Bukti Pengiriman Paket"
                                         class="w-full h-48 object-cover hover:scale-105 transition-transform duration-300">
                                    <div class="p-2 bg-white text-center border-t border-purple-100">
                                        <a href="{{ $shippingProofUrl }}" target="_blank" class="text-[10px] font-bold text-purple-700 hover:underline">
                                            <i class="fa-solid fa-up-right-from-square"></i> Buka Foto Ukuran Penuh
                                        </a>
                                    </div>
                                </div>
                            </div>
                        @elseif(in_array($st, ['shipped', 'dikirim']))
                            <div class="p-4 bg-purple-50 border border-purple-200 rounded-2xl text-xs text-purple-900 flex items-center gap-3">
                                <i class="fa-solid fa-truck-fast text-xl text-purple-600"></i>
                                <div>
                                    <div class="font-bold">Paket Sedang Dikirim</div>
                                    <p class="text-[10px] text-purple-700">Foto bukti pengiriman belum diunggah oleh admin.</p>
                                </div>
                            </div>
                        @endif

                        @if($receivedProofUrl)
                            <div class="p-4 bg-emerald-50/70 border border-emerald-200 rounded-2xl space-y-3">
                                <div class="flex items-center justify-between">
                                    <h4 class="text-xs font-black text-emerald-900 flex items-center gap-1.5">
                                        <i class="fa-solid fa-circle-check text-emerald-600"></i> Foto Bukti Penerimaan
                                    </h4>
                                    <span class="text-[9px] font-bold bg-emerald-200 text-emerald-800 px-2 py-0.5 rounded-full">Selesai</span>
                                </div>

                                <div class="overflow-hidden rounded-xl border border-emerald-200 shadow-sm bg-white">
                                    <img src="{{ $receivedProofUrl }}"
                                         alt="Bukti Pesanan Diterima"
                                         class="w-full h-48 object-cover hover:scale-105 transition-transform duration-300">
                                    <div class="p-2 bg-white text-center border-t border-emerald-100">
                                        <a href="{{ $receivedProofUrl }}" target="_blank" class="text-[10px] font-bold text-emerald-700 hover:underline">
                                            <i class="fa-solid fa-up-right-from-square"></i> Buka Foto Ukuran Penuh
                                        </a>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>

                    @if(in_array($st, ['rejected', 'ditolak']) || !empty($order->alasan_penolakan))
                        <div class="p-4 bg-red-100/70 border border-red-300 rounded-2xl text-xs text-red-900 space-y-1">
                            <div class="font-bold flex items-center gap-1.5">
                                <i class="fa-solid fa-triangle-exclamation text-red-600"></i> Alasan Penolakan dari Admin:
                            </div>
                            <p class="text-xs italic pl-5">{{ $order->alasan_penolakan ?? 'Bukti pembayaran tidak sesuai atau tidak valid.' }}</p>
                        </div>
                    @endif

                    <div class="bg-gray-50 p-4 rounded-2xl border border-gray-100 text-xs space-y-2">
                        <div class="flex justify-between border-b border-gray-200/60 pb-1.5">
                            <span class="text-gray-500 font-semibold">Nama Penerima:</span>
                            <span class="font-bold text-gray-800">{{ $order->customer_name }} ({{ $order->customer_whatsapp }})</span>
                        </div>
                        <div class="flex justify-between border-b border-gray-200/60 pb-1.5">
                            <span class="text-gray-500 font-semibold">Alamat Tujuan:</span>
                            <span class="font-bold text-gray-800">{{ $order->shipping_address }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-500 font-semibold">Total Pembayaran:</span>
                            <span class="font-black text-emerald-600">Rp {{ number_format($order->total_amount ?? $order->subtotal, 0, ',', '.') }}</span>
                        </div>
                    </div>

                    <div>
                        <h4 class="text-xs font-bold text-gray-700 uppercase tracking-wider mb-3">Item Pesanan</h4>
                        <div class="space-y-2">
                            @foreach($order->items as $item)
                                <div class="flex items-center justify-between p-3 bg-gray-50 rounded-xl border border-gray-100 text-xs">
                                    <div>
                                        <div class="font-bold text-gray-800">
                                            {{ $item->variant->product->name ?? 'Produk AnabulMart' }}
                                        </div>
                                        <div class="text-[10px] text-amber-600 font-semibold">
                                            Variasi: {{ $item->variant->variant_name ?? 'Default' }} (x{{ $item->quantity }})
                                        </div>
                                    </div>
                                    <div class="font-black text-gray-700">
                                        Rp {{ number_format($item->subtotal, 0, ',', '.') }}
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @else
                <div class="mt-8 p-6 bg-red-50 border border-red-200 text-red-800 text-xs font-bold rounded-2xl text-center space-y-2">
                    <i class="fa-solid fa-circle-xmark text-2xl text-red-500"></i>
                    <div>Nomor Pesanan <span class="font-mono text-red-900 font-black">"{{ $search }}"</span> tidak ditemukan.</div>
                </div>
            @endif
        @endif
    </div>
</div>
@endsection