@extends('layouts.admin')

@section('title', 'Laporan Penjualan - Admin AnabulMart')
@section('page_title', 'Laporan Penjualan & Keuangan')

@section('content')
<!-- CSS KHUSUS CETAK/PDF (Sembunyikan Sidebar, Header Admin, Filter, & Tombol) -->
<style>
    @media print {
        /* Sembunyikan Nav/Sidebar Admin & Elemen Non-Laporan */
        aside, nav, header, .no-print, button, form, a[href] {
            display: none !important;
        }

        /* Buat halaman cetak putih bersih full-width */
        body {
            background-color: #ffffff !important;
            padding: 0 !important;
            margin: 0 !important;
            color: #000000 !important;
        }

        main {
            padding: 0 !important;
            margin: 0 !important;
        }

        .print-container {
            border: none !important;
            box-shadow: none !important;
            padding: 0 !important;
        }

        /* Tampilkan Header Kop Surat Resmi hanya saat Cetak */
        .print-kop {
            display: block !important;
            text-align: center;
            border-bottom: 2px solid #000;
            padding-bottom: 12px;
            margin-bottom: 20px;
        }

        .print-kop h1 {
            font-size: 20pt;
            font-weight: bold;
            text-transform: uppercase;
            margin: 0;
        }

        .print-kop p {
            font-size: 10pt;
            margin: 2px 0;
        }

        /* Styling Cards Ringkasan saat Cetak */
        .print-cards {
            display: flex !important;
            justify-content: space-between !important;
            gap: 10px !important;
            margin-bottom: 20px !important;
        }

        .print-card-item {
            border: 1px solid #ccc !important;
            padding: 10px !important;
            border-radius: 0 !important;
            width: 32% !important;
            text-align: center !important;
        }

        /* [FIX] Paksa SEMUA sudut jadi kotak (0) saat print/PDF - sebelumnya
           class Tailwind seperti rounded-2xl/rounded-xl masih terbawa dari
           tampilan monitor karena tidak di-override di sini. */
        *, *::before, *::after {
            border-radius: 0 !important;
        }

        /* Tabel Cetak Presisi */
        table {
            width: 100% !important;
            border-collapse: collapse !important;
        }

        th, td {
            border: 1px solid #333 !important;
            padding: 8px !important;
            font-size: 9pt !important;
        }

        thead {
            background-color: #f2f2f2 !important;
            -webkit-print-color-adjust: exact;
        }
    }

    /* KOP SURAT SEMBUNYI DI TAMPILAN MONITOR */
    .print-kop {
        display: none;
    }
</style>

<!-- KOP SURAT LAPORAN (HANYA MUNCUL SAAT DI-PRINT/SAVED AS PDF) -->
<div class="print-kop">
    <h1>AnabulMart Petshop Medan</h1>
    <p>Jl. Kasuari No. 13, Kec. Medan Sunggal, Kota Medan | WA: 085175217503</p>
    <p style="font-weight: bold; margin-top: 8px;">
        LAPORAN TRANSAKSI PENJUALAN 
        @if(request('start_date') && request('end_date'))
            PERIODE ({{ \Carbon\Carbon::parse(request('start_date'))->format('d/m/Y') }} - {{ \Carbon\Carbon::parse(request('end_date'))->format('d/m/Y') }})
        @else
            PERIODE SELURUH TRANSAKSI
        @endif
    </p>
</div>

<!-- FORM FILTER PERIODE LAPORAN (TIDAK MUNCUL DI PDF) -->
<div class="no-print bg-white p-5 rounded-2xl border border-gray-200 shadow-sm mb-8">
    <form action="{{ route('admin.reports.index') }}" method="GET" class="flex flex-wrap items-end gap-4">
        <div>
            <label class="block text-xs font-bold text-gray-700 mb-1">Dari Tanggal</label>
            <input type="date" name="start_date" value="{{ request('start_date') }}" class="text-xs border border-gray-300 rounded-xl px-3 py-2 bg-gray-50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-amber-500">
        </div>
        <div>
            <label class="block text-xs font-bold text-gray-700 mb-1">Sampai Tanggal</label>
            <input type="date" name="end_date" value="{{ request('end_date') }}" class="text-xs border border-gray-300 rounded-xl px-3 py-2 bg-gray-50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-amber-500">
        </div>
        <div class="flex items-center gap-2">
            <button type="submit" class="px-4 py-2 bg-amber-500 hover:bg-amber-600 text-white rounded-xl text-xs font-bold transition-all shadow-sm">
                <i class="fa-solid fa-filter"></i> Filter
            </button>
            <a href="{{ route('admin.reports.index') }}" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-600 rounded-xl text-xs font-bold transition-all">
                Reset
            </a>
        </div>
    </form>
</div>

<!-- RINGKASAN CARDS STATISTIK (DIPERTAHANKAN DI PDF) -->
<div class="print-cards grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
    <div class="print-card-item bg-white p-6 rounded-2xl border border-gray-200 shadow-sm flex items-center gap-4">
        <div class="no-print w-14 h-14 bg-emerald-100 text-emerald-600 rounded-2xl flex items-center justify-center text-2xl shadow-inner">
            <i class="fa-solid fa-wallet"></i>
        </div>
        <div>
            <span class="text-xs text-gray-400 block font-medium uppercase tracking-wider">Total Omzet</span>
            <span class="text-2xl font-black text-gray-800">Rp {{ number_format($totalRevenue, 0, ',', '.') }}</span>
        </div>
    </div>

    <div class="print-card-item bg-white p-6 rounded-2xl border border-gray-200 shadow-sm flex items-center gap-4">
        <div class="no-print w-14 h-14 bg-blue-100 text-blue-600 rounded-2xl flex items-center justify-center text-2xl shadow-inner">
            <i class="fa-solid fa-cart-check"></i>
        </div>
        <div>
            <span class="text-xs text-gray-400 block font-medium uppercase tracking-wider">Transaksi Sukses</span>
            <span class="text-2xl font-black text-gray-800">{{ $totalSuccessOrders }} Pesanan</span>
        </div>
    </div>

    <div class="print-card-item bg-white p-6 rounded-2xl border border-gray-200 shadow-sm flex items-center gap-4">
        <div class="no-print w-14 h-14 bg-amber-100 text-amber-600 rounded-2xl flex items-center justify-center text-2xl shadow-inner">
            <i class="fa-solid fa-boxes-stacked"></i>
        </div>
        <div>
            <span class="text-xs text-gray-400 block font-medium uppercase tracking-wider">Produk Terjual</span>
            <span class="text-2xl font-black text-gray-800">{{ $totalItemsSold }} Items</span>
        </div>
    </div>
</div>

<!-- TABEL DETAIL TRANSAKSI LAPORAN -->
<div class="print-container bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
    <div class="no-print p-5 border-b border-gray-100 flex justify-between items-center">
        <h3 class="text-base font-bold text-gray-800">Rincian Transaksi Sukses</h3>
        <button onclick="window.print()" class="px-3.5 py-2 bg-gray-800 hover:bg-gray-900 text-white rounded-xl text-xs font-bold transition-all flex items-center gap-1.5">
            <i class="fa-solid fa-file-pdf"></i> Cetak / Save PDF
        </button>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-left text-sm text-gray-600">
            <thead class="bg-gray-100 text-gray-700 font-semibold uppercase text-xs tracking-wider border-b border-gray-200">
                <tr>
                    <th class="py-3.5 px-5">No. Invoice</th>
                    <th class="py-3.5 px-5">Nama Pelanggan</th>
                    <th class="py-3.5 px-5">Subtotal</th>
                    <th class="py-3.5 px-5">Ongkir</th>
                    <th class="py-3.5 px-5">Total Pendapatan</th>
                    <th class="py-3.5 px-5">Tanggal Selesai</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($completedOrders as $order)
                <tr class="hover:bg-gray-50/80 transition-all">
                    <td class="py-4 px-5 font-mono text-amber-600 font-bold">
                        #{{ $order->order_number ?? $order->id }}
                    </td>
                    <td class="py-4 px-5">
                        <span class="font-bold text-gray-800 block">{{ $order->customer_name }}</span>
                        <span class="text-xs text-gray-400">{{ $order->customer_whatsapp }}</span>
                    </td>
                    <td class="py-4 px-5 text-gray-600">
                        Rp {{ number_format($order->subtotal ?? 0, 0, ',', '.') }}
                    </td>
                    <td class="py-4 px-5 text-gray-600">
                        Rp {{ number_format($order->shipping_cost ?? 0, 0, ',', '.') }}
                    </td>
                    <td class="py-4 px-5 font-bold text-emerald-600">
                        Rp {{ number_format($order->total_amount, 0, ',', '.') }}
                    </td>
                    <td class="py-4 px-5 text-xs text-gray-500">
                        {{ $order->updated_at ? $order->updated_at->format('d/m/Y H:i') : '-' }}
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="py-8 text-center text-gray-400 italic">
                        Belum ada laporan transaksi selesai pada periode ini.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection