@extends('layouts.admin')

@section('title', 'Data Transaksi - Admin AnabulMart')
@section('page_title', 'Kelola Transaksi & Pesanan')

@section('content')
<div class="flex justify-between items-center mb-6">
    <p class="text-sm text-gray-500">Daftar seluruh pesanan masuk dari pembeli AnabulMart.</p>
</div>

@if(session('success'))
    <div class="mb-4 p-4 bg-emerald-100 border border-emerald-300 text-emerald-800 rounded-lg text-sm font-semibold flex items-center justify-between">
        <span>{{ session('success') }}</span>
        <button onclick="this.parentElement.remove()" class="text-emerald-800 font-bold">&times;</button>
    </div>
@endif

<div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-left text-sm text-gray-600">
            <thead class="bg-gray-100 text-gray-700 font-semibold uppercase text-xs tracking-wider border-b border-gray-200">
                <tr>
                    <th class="py-3.5 px-5">No. Pesanan</th>
                    <th class="py-3.5 px-5">Pelanggan</th>
                    <th class="py-3.5 px-5">Total Bayar</th>
                    <th class="py-3.5 px-5">Bukti Foto Dokumentasi</th>
                    <th class="py-3.5 px-5">Status Pesanan</th>
                    <th class="py-3.5 px-5 text-center">Aksi Lanjutan</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($orders as $order)
                @php
                    $status = strtolower($order->status ?? 'pending');
                    $badgeClass = match($status) {
                        'paid', 'lunas' => 'bg-emerald-100 text-emerald-800 border-emerald-200',
                        'shipped', 'dikirim' => 'bg-blue-100 text-blue-800 border-blue-200',
                        'completed', 'selesai' => 'bg-indigo-100 text-indigo-800 border-indigo-200',
                        'pending', 'menunggu' => 'bg-amber-100 text-amber-800 border-amber-200',
                        'rejected', 'ditolak' => 'bg-red-100 text-red-800 border-red-200',
                        'cancelled', 'batal' => 'bg-gray-100 text-gray-800 border-gray-200',
                        default => 'bg-gray-100 text-gray-800 border-gray-200',
                    };
                @endphp
                <tr class="hover:bg-gray-50/80 transition-all">
                    <!-- NO PESANAN -->
                    <td class="py-4 px-5 font-mono text-amber-600 font-bold">
                        #{{ $order->order_number ?? $order->id }}
                    </td>

                    <!-- PELANGGAN -->
                    <td class="py-4 px-5">
                        <span class="font-bold text-gray-800 block">{{ $order->customer_name ?? 'Pelanggan' }}</span>
                        <span class="text-xs text-gray-400">{{ $order->customer_phone ?? '-' }}</span>
                    </td>

                    <!-- TOTAL BAYAR -->
                    <td class="py-4 px-5 font-bold text-gray-800">
                        Rp {{ number_format($order->total_price ?? $order->total_amount ?? 0, 0, ',', '.') }}
                    </td>

                    <!-- LAMPIRAN BUKTI BANYAK (STRUK, PENGIRIMAN, DITERIMA) -->
                    <td class="py-4 px-5 space-y-1">
                        @if($order->payment_proof ?? $order->proof_image ?? null)
                            <a href="{{ asset('storage/' . ($order->payment_proof ?? $order->proof_image)) }}" target="_blank" class="block text-xs text-blue-600 font-semibold hover:underline">
                                <i class="fa-solid fa-file-invoice"></i> Struk Bayar
                            </a>
                        @endif

                        @if($order->shipping_proof)
                            <a href="{{ asset('storage/' . $order->shipping_proof) }}" target="_blank" class="block text-xs text-amber-600 font-bold hover:underline">
                                <i class="fa-solid fa-truck"></i> Bukti Pengiriman Paket
                            </a>
                        @endif

                        @if($order->received_proof)
                            <a href="{{ asset('storage/' . $order->received_proof) }}" target="_blank" class="block text-xs text-emerald-600 font-bold hover:underline">
                                <i class="fa-solid fa-box-open"></i> Foto Barang Diterima
                            </a>
                        @endif
                    </td>

                    <!-- BADGE STATUS -->
                    <td class="py-4 px-5">
                        <span class="px-2.5 py-1 text-xs font-bold rounded-full border {{ $badgeClass }}">
                            {{ strtoupper($status) }}
                        </span>
                        @if(($status === 'rejected' || $status === 'ditolak') && $order->alasan_penolakan)
                            <span class="block text-[11px] text-red-500 italic mt-1 max-w-xs">
                                Alasan: "{{ $order->alasan_penolakan }}"
                            </span>
                        @endif
                    </td>

                    <!-- AKSI DINAMIS -->
                    <td class="py-4 px-5 text-center">
                        <div class="flex items-center justify-center gap-2">
                            @if($status === 'pending' || $status === 'menunggu')
                                <form action="{{ route('admin.orders.updateStatus', $order->id) }}" method="POST" class="inline">
                                    @csrf
                                    @method('PUT')
                                    <input type="hidden" name="status" value="paid">
                                    <button type="submit" class="text-xs px-2.5 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg font-bold shadow-sm transition-all">
                                        <i class="fa-solid fa-check"></i> Terima Bayar
                                    </button>
                                </form>

                                <button type="button" onclick="openRejectModal('{{ $order->id }}', '{{ $order->order_number ?? $order->id }}')" class="text-xs px-2.5 py-1.5 bg-red-500 hover:bg-red-600 text-white rounded-lg font-bold shadow-sm transition-all">
                                    <i class="fa-solid fa-xmark"></i> Tolak
                                </button>

                            @elseif($status === 'paid' || $status === 'lunas')
                                <!-- POP UP PROSES KIRIM BARANG + UPLOAD FOTO PENGIRIMAN -->
                                <button type="button" onclick="openShippingModal('{{ $order->id }}', '{{ $order->order_number ?? $order->id }}')" class="text-xs px-3 py-1.5 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-bold shadow-sm transition-all flex items-center gap-1">
                                    <i class="fa-solid fa-truck-fast"></i> Kirim Pesanan
                                </button>

                            @elseif($status === 'shipped' || $status === 'dikirim')
                                <!-- POP UP SELESAIKAN PESANAN + UPLOAD FOTO TERIMA -->
                                <button type="button" onclick="openCompleteModal('{{ $order->id }}', '{{ $order->order_number ?? $order->id }}')" class="text-xs px-3 py-1.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg font-bold shadow-sm transition-all flex items-center gap-1">
                                    <i class="fa-solid fa-circle-check"></i> Selesaikan Pesanan
                                </button>

                            @else
                                <form action="{{ route('admin.orders.updateStatus', $order->id) }}" method="POST" class="inline">
                                    @csrf
                                    @method('PUT')
                                    <select name="status" onchange="this.form.submit()" class="text-xs border border-gray-300 rounded-lg px-2 py-1 bg-white focus:outline-none">
                                        <option value="pending" {{ $status == 'pending' ? 'selected' : '' }}>Pending</option>
                                        <option value="paid" {{ $status == 'paid' ? 'selected' : '' }}>Paid</option>
                                        <option value="shipped" {{ $status == 'shipped' ? 'selected' : '' }}>Shipped</option>
                                        <option value="completed" {{ $status == 'completed' ? 'selected' : '' }}>Completed</option>
                                        <option value="rejected" {{ $status == 'rejected' ? 'selected' : '' }}>Rejected</option>
                                    </select>
                                </form>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="py-8 text-center text-gray-400 italic">
                        Belum ada data transaksi masuk.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- ================= MODAL 1: KIRIM PESANAN (SHIPPING) ================= -->
<div id="shippingModal" class="fixed inset-0 z-50 hidden overflow-y-auto bg-gray-900/60 backdrop-blur-sm flex items-center justify-center p-4 transition-all">
    <div class="relative bg-white rounded-2xl max-w-md w-full p-6 shadow-2xl border border-gray-100 transform transition-all scale-95 opacity-0 duration-200" id="shippingModalBox">
        <div class="w-12 h-12 bg-blue-100 text-blue-600 rounded-full flex items-center justify-center mx-auto mb-3 text-xl shadow-inner">
            <i class="fa-solid fa-truck-fast"></i>
        </div>
        <div class="text-center mb-4">
            <h3 class="text-lg font-extrabold text-gray-900 mb-1">Proses Pengiriman Paket</h3>
            <p class="text-xs text-gray-500">Pesanan <span id="shippingOrderNum" class="font-mono font-bold text-blue-600"></span></p>
        </div>
        <form id="shippingForm" method="POST" action="" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            <input type="hidden" name="status" value="shipped">
            
            <div class="mb-5">
                <label class="block text-xs font-bold text-gray-700 mb-1">Foto Resi / Paket Diserahkan (Opsional)</label>
                <input type="file" name="shipping_proof" accept="image/*" class="w-full text-xs text-gray-500 border border-gray-300 rounded-xl p-2">
                <span class="text-[10px] text-gray-400 mt-1 block">Foto resi fisik / foto paket saat diserahkan ke ojol/kurir.</span>
            </div>

            <div class="flex items-center justify-center gap-3">
                <button type="button" onclick="closeShippingModal()" class="w-1/2 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-700 font-semibold rounded-xl text-xs">Batal</button>
                <button type="submit" class="w-1/2 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-xl text-xs shadow-md shadow-blue-200">Proses Kirim</button>
            </div>
        </form>
    </div>
</div>

<!-- ================= MODAL 2: SELESAIKAN PESANAN (COMPLETED) ================= -->
<div id="completeModal" class="fixed inset-0 z-50 hidden overflow-y-auto bg-gray-900/60 backdrop-blur-sm flex items-center justify-center p-4 transition-all">
    <div class="relative bg-white rounded-2xl max-w-md w-full p-6 shadow-2xl border border-gray-100 transform transition-all scale-95 opacity-0 duration-200" id="completeModalBox">
        <div class="w-12 h-12 bg-indigo-100 text-indigo-600 rounded-full flex items-center justify-center mx-auto mb-3 text-xl shadow-inner">
            <i class="fa-solid fa-box-check"></i>
        </div>
        <div class="text-center mb-4">
            <h3 class="text-lg font-extrabold text-gray-900 mb-1">Selesaikan Pesanan</h3>
            <p class="text-xs text-gray-500">Pesanan <span id="completeOrderNum" class="font-mono font-bold text-indigo-600"></span></p>
        </div>
        <form id="completeForm" method="POST" action="" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            <input type="hidden" name="status" value="completed">
            
            <div class="mb-5">
                <label class="block text-xs font-bold text-gray-700 mb-1">Foto Bukti Barang Diterima (Opsional)</label>
                <input type="file" name="received_proof" accept="image/*" class="w-full text-xs text-gray-500 border border-gray-300 rounded-xl p-2">
                <span class="text-[10px] text-gray-400 mt-1 block">Foto penyerahan barang / lokasi tempat tujuan.</span>
            </div>

            <div class="flex items-center justify-center gap-3">
                <button type="button" onclick="closeCompleteModal()" class="w-1/2 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-700 font-semibold rounded-xl text-xs">Batal</button>
                <button type="submit" class="w-1/2 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-xl text-xs shadow-md shadow-indigo-200">Selesaikan Pesanan</button>
            </div>
        </form>
    </div>
</div>

<!-- ================= MODAL 3: REJECT ================= -->
<div id="rejectModal" class="fixed inset-0 z-50 hidden overflow-y-auto bg-gray-900/60 backdrop-blur-sm flex items-center justify-center p-4 transition-all">
    <div class="relative bg-white rounded-2xl max-w-md w-full p-6 shadow-2xl border border-gray-100 transform transition-all scale-95 opacity-0 duration-200" id="rejectModalBox">
        <div class="w-12 h-12 bg-red-100 text-red-600 rounded-full flex items-center justify-center mx-auto mb-3 text-xl shadow-inner">
            <i class="fa-solid fa-circle-xmark"></i>
        </div>
        <div class="text-center mb-4">
            <h3 class="text-lg font-extrabold text-gray-900 mb-1">Tolak Pembayaran Pesanan</h3>
            <p class="text-xs text-gray-500">Pesanan <span id="rejectOrderNum" class="font-mono font-bold text-amber-600"></span></p>
        </div>
        <form id="rejectForm" method="POST" action="">
            @csrf
            @method('PUT')
            <input type="hidden" name="status" value="rejected">
            <div class="mb-5">
                <label class="block text-xs font-bold text-gray-700 mb-1">Alasan Penolakan *</label>
                <textarea name="alasan_penolakan" required rows="3" placeholder="Contoh: Nominal transfer kurang / struk tidak jelas." class="w-full text-xs border border-gray-300 rounded-xl p-3 focus:ring-2 focus:ring-red-500 focus:outline-none"></textarea>
            </div>
            <div class="flex items-center justify-center gap-3">
                <button type="button" onclick="closeRejectModal()" class="w-1/2 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-700 font-semibold rounded-xl text-xs">Batal</button>
                <button type="submit" class="w-1/2 py-2.5 bg-red-600 hover:bg-red-700 text-white font-bold rounded-xl text-xs">Kirim Penolakan</button>
            </div>
        </form>
    </div>
</div>

<script>
    function openShippingModal(orderId, orderNum) {
        const modal = document.getElementById('shippingModal');
        const modalBox = document.getElementById('shippingModalBox');
        document.getElementById('shippingOrderNum').innerText = '#' + orderNum;
        document.getElementById('shippingForm').action = `/admin/order/${orderId}/status`;

        modal.classList.remove('hidden');
        setTimeout(() => {
            modalBox.classList.remove('scale-95', 'opacity-0');
            modalBox.classList.add('scale-100', 'opacity-100');
        }, 10);
    }

    function closeShippingModal() {
        const modal = document.getElementById('shippingModal');
        const modalBox = document.getElementById('shippingModalBox');
        modalBox.classList.remove('scale-100', 'opacity-100');
        modalBox.classList.add('scale-95', 'opacity-0');
        setTimeout(() => modal.classList.add('hidden'), 150);
    }

    function openCompleteModal(orderId, orderNum) {
        const modal = document.getElementById('completeModal');
        const modalBox = document.getElementById('completeModalBox');
        document.getElementById('completeOrderNum').innerText = '#' + orderNum;
        document.getElementById('completeForm').action = `/admin/order/${orderId}/status`;

        modal.classList.remove('hidden');
        setTimeout(() => {
            modalBox.classList.remove('scale-95', 'opacity-0');
            modalBox.classList.add('scale-100', 'opacity-100');
        }, 10);
    }

    function closeCompleteModal() {
        const modal = document.getElementById('completeModal');
        const modalBox = document.getElementById('completeModalBox');
        modalBox.classList.remove('scale-100', 'opacity-100');
        modalBox.classList.add('scale-95', 'opacity-0');
        setTimeout(() => modal.classList.add('hidden'), 150);
    }

    function openRejectModal(orderId, orderNum) {
        const modal = document.getElementById('rejectModal');
        const modalBox = document.getElementById('rejectModalBox');
        document.getElementById('rejectOrderNum').innerText = '#' + orderNum;
        document.getElementById('rejectForm').action = `/admin/order/${orderId}/status`;

        modal.classList.remove('hidden');
        setTimeout(() => {
            modalBox.classList.remove('scale-95', 'opacity-0');
            modalBox.classList.add('scale-100', 'opacity-100');
        }, 10);
    }

    function closeRejectModal() {
        const modal = document.getElementById('rejectModal');
        const modalBox = document.getElementById('rejectModalBox');
        modalBox.classList.remove('scale-100', 'opacity-100');
        modalBox.classList.add('scale-95', 'opacity-0');
        setTimeout(() => modal.classList.add('hidden'), 150);
    }
</script>
@endsection