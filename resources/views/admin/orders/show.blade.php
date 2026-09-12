@extends('layouts.admin')

@section('title', 'Detail Pesanan #' . $order->order_number)

@section('content')
<div class="max-w-5xl mx-auto px-4 py-6 space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-xl font-black text-gray-800">Detail Pesanan #{{ $order->order_number }}</h1>
            <p class="text-xs text-gray-500">Tanggal Transaksi: {{ $order->created_at->format('d M Y, H:i') }} WIB</p>
        </div>
        <a href="{{ route('admin.orders.index') }}" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold text-xs rounded-xl transition-all">
            &larr; Kembali ke Daftar Transaksi
        </a>
    </div>

    @if(session('success'))
        <div class="p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-bold rounded-xl flex items-center justify-between">
            <span><i class="fa-solid fa-circle-check mr-2 text-emerald-600"></i>{{ session('success') }}</span>
            <button onclick="this.parentElement.remove()" class="text-emerald-500 hover:text-emerald-700">&times;</button>
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- FORM KONTROL UTAMA ADMIN -->
        <div class="lg:col-span-1 space-y-6">
            <div class="bg-white p-5 rounded-2xl border border-gray-200 shadow-sm space-y-4">
                <h3 class="text-xs font-bold text-gray-800 uppercase tracking-wider border-b border-gray-100 pb-2 flex items-center gap-2">
                    <i class="fa-solid fa-sliders text-amber-500"></i> Update Status Pesanan
                </h3>

                <form action="{{ route('admin.orders.updateStatus', $order->id) }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                    @csrf
                    @method('PUT')

                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1">Status Transaksi *</label>
                        <select name="status" class="w-full text-xs border border-gray-300 rounded-xl px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-amber-500 bg-gray-50 font-bold text-gray-800">
                            <option value="pending" {{ $order->status == 'pending' ? 'selected' : '' }}>Pending (Menunggu Pembayaran)</option>
                            <option value="waiting_confirmation" {{ $order->status == 'waiting_confirmation' ? 'selected' : '' }}>Waiting Confirmation (Verifikasi Pembayaran)</option>
                            <option value="shipped" {{ in_array($order->status, ['shipped', 'dikirim']) ? 'selected' : '' }}>Shipped (Sedang Dikirim)</option>
                            <option value="delivered" {{ in_array($order->status, ['delivered', 'selesai', 'completed']) ? 'selected' : '' }}>Delivered / Selesai</option>
                            <option value="rejected" {{ in_array($order->status, ['rejected', 'ditolak']) ? 'selected' : '' }}>Ditolak / Rejected</option>
                        </select>
                    </div>

                    <!-- UPLOAD FOTO PENGIRIMAN (SHIPPED) -->
                    <div class="p-3 bg-purple-50 rounded-xl border border-purple-200 space-y-2">
                        <label class="block text-xs font-bold text-purple-900">
                            <i class="fa-solid fa-camera text-purple-600 mr-1"></i> Upload Foto Pengiriman / Paket
                        </label>
                        <input type="file" name="shipping_proof" accept="image/*" class="w-full text-xs text-gray-500 border border-purple-200 rounded-lg p-1.5 bg-white">
                        <span class="text-[10px] text-purple-700 block">*Pilih foto bukti pengiriman kurir</span>
                    </div>

                    <!-- UPLOAD FOTO PENERIMAAN (DELIVERED) -->
                    <div class="p-3 bg-emerald-50 rounded-xl border border-emerald-200 space-y-2">
                        <label class="block text-xs font-bold text-emerald-900">
                            <i class="fa-solid fa-circle-check text-emerald-600 mr-1"></i> Upload Foto Penerimaan
                        </label>
                        <input type="file" name="received_proof" accept="image/*" class="w-full text-xs text-gray-500 border border-emerald-200 rounded-lg p-1.5 bg-white">
                        <span class="text-[10px] text-emerald-700 block">*Pilih foto bukti penerimaan paket</span>
                    </div>

                    <!-- ALASAN PENOLAKAN -->
                    <div class="p-3 bg-red-50 rounded-xl border border-red-200 space-y-2">
                        <label class="block text-xs font-bold text-red-900">Alasan Penolakan (Jika Ditolak)</label>
                        <textarea name="alasan_penolakan" rows="2" placeholder="Tuliskan alasan penolakan..." class="w-full text-xs border border-red-200 rounded-lg p-2 bg-white">{{ $order->alasan_penolakan }}</textarea>
                    </div>

                    <button type="submit" class="w-full py-3 bg-amber-500 hover:bg-amber-600 text-white font-bold text-xs rounded-xl shadow-md transition-all">
                        Simpan Perubahan Status
                    </button>
                </form>
            </div>
        </div>

        <!-- RINCIAN DOKUMEN DAN BUKTI -->
        <div class="lg:col-span-2 space-y-6">
            <div class="bg-white p-5 rounded-2xl border border-gray-200 shadow-sm text-xs space-y-2">
                <h3 class="font-bold text-gray-800 border-b border-gray-100 pb-2">Informasi Pembeli</h3>
                <div class="flex justify-between border-b border-gray-100 pb-1.5">
                    <span class="text-gray-500">Nama Pelanggan:</span>
                    <span class="font-bold text-gray-800">{{ $order->customer_name }}</span>
                </div>
                <div class="flex justify-between border-b border-gray-100 pb-1.5">
                    <span class="text-gray-500">WhatsApp:</span>
                    <span class="font-bold text-emerald-600">{{ $order->customer_whatsapp }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-500">Alamat Pengiriman:</span>
                    <span class="font-bold text-gray-800 text-right max-w-xs">{{ $order->shipping_address }}</span>
                </div>
            </div>

            <!-- PREVIEW GAMBAR ADMIN STREAM VIA MEDIA ROUTE (BEBAS 403 FORBIDDEN) -->
            <div class="bg-white p-5 rounded-2xl border border-gray-200 shadow-sm space-y-4 text-xs">
                <h3 class="font-bold text-gray-800 border-b border-gray-100 pb-2">Pratinjau Foto Transaksi</h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-3 text-center">
                    
                    <!-- 1. FOTO STRUK BAYAR -->
                    <div class="p-2 border border-gray-200 rounded-xl bg-gray-50 space-y-2">
                        <span class="font-bold block text-gray-700">Bukti Bayar Pembeli</span>
                        @if(!empty($order->payment_proof))
                            @php
                                // PERBAIKAN: Hanya bersihkan prefix 'public/', 'storage/', dan backslash '\\'
                                $cleanPayment = ltrim(str_replace(['public/', 'storage/', '\\'], ['', '', '/'], $order->payment_proof), '/');
                                $paymentUrl = route('media.show', ['path' => $cleanPayment]);
                            @endphp
                            <a href="{{ $paymentUrl }}" target="_blank" class="block group">
                                <img src="{{ $paymentUrl }}" alt="Bukti Pembayaran" class="w-full h-36 object-cover rounded-lg border group-hover:opacity-90 transition-opacity">
                                <span class="text-[10px] text-blue-600 font-bold hover:underline block mt-1"><i class="fa-solid fa-up-right-from-square"></i> Buka Foto</span>
                            </a>
                        @else
                            <span class="text-[10px] text-gray-400 italic block py-12">Belum Ada</span>
                        @endif
                    </div>

                    <!-- 2. FOTO SHIPPED (PENGIRIMAN) -->
                    <div class="p-2 border border-purple-200 rounded-xl bg-purple-50/50 space-y-2">
                        <span class="font-bold block text-purple-900">Foto Pengiriman</span>
                        @if(!empty($order->shipping_proof))
                            @php
                                $cleanShip = ltrim(str_replace(['public/', 'storage/', '\\'], ['', '', '/'], $order->shipping_proof), '/');
                                $shipUrl = route('media.show', ['path' => $cleanShip]);
                            @endphp
                            <a href="{{ $shipUrl }}" target="_blank" class="block group">
                                <img src="{{ $shipUrl }}" alt="Foto Pengiriman" class="w-full h-36 object-cover rounded-lg border border-purple-200 group-hover:opacity-90 transition-opacity">
                                <span class="text-[10px] text-purple-600 font-bold hover:underline block mt-1"><i class="fa-solid fa-up-right-from-square"></i> Buka Foto</span>
                            </a>
                        @else
                            <span class="text-[10px] text-purple-400 italic block py-12">Belum Ada</span>
                        @endif
                    </div>

                    <!-- 3. FOTO PENERIMAAN -->
                    <div class="p-2 border border-emerald-200 rounded-xl bg-emerald-50/50 space-y-2">
                        <span class="font-bold block text-emerald-900">Foto Penerimaan</span>
                        @if(!empty($order->received_proof))
                            @php
                                $cleanRec = ltrim(str_replace(['public/', 'storage/', '\\'], ['', '', '/'], $order->received_proof), '/');
                                $recUrl = route('media.show', ['path' => $cleanRec]);
                            @endphp
                            <a href="{{ $recUrl }}" target="_blank" class="block group">
                                <img src="{{ $recUrl }}" alt="Foto Penerimaan" class="w-full h-36 object-cover rounded-lg border border-emerald-200 group-hover:opacity-90 transition-opacity">
                                <span class="text-[10px] text-emerald-600 font-bold hover:underline block mt-1"><i class="fa-solid fa-up-right-from-square"></i> Buka Foto</span>
                            </a>
                        @else
                            <span class="text-[10px] text-emerald-400 italic block py-12">Belum Ada</span>
                        @endif
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>
@endsection