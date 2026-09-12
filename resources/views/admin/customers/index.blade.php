@extends('layouts.admin')

@section('title', 'Data Pelanggan - Admin AnabulMart')
@section('page_title', 'Data Pelanggan Setia')

@section('content')
<div class="flex justify-between items-center mb-6">
    <p class="text-sm text-gray-500">Daftar pelanggan terdata otomatis dari riwayat transaksi tanpa duplikat.</p>
</div>

<div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-left text-sm text-gray-600">
            <thead class="bg-gray-100 text-gray-700 font-semibold uppercase text-xs tracking-wider border-b border-gray-200">
                <tr>
                    <th class="py-3.5 px-5">Nama Pelanggan</th>
                    <th class="py-3.5 px-5">No. WhatsApp / HP</th>
                    <th class="py-3.5 px-5">Alamat Terakhir</th>
                    <th class="py-3.5 px-5 text-center">Total Transaksi</th>
                    <th class="py-3.5 px-5">Akumulasi Belanja</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($customers as $customer)
                <tr class="hover:bg-gray-50/80 transition-all">
                    <td class="py-4 px-5">
                        <span class="font-bold text-gray-800 block">{{ $customer->name }}</span>
                    </td>
                    <td class="py-4 px-5 font-mono text-xs text-amber-600 font-bold">
                        {{ $customer->phone }}
                    </td>
                    <td class="py-4 px-5 text-xs text-gray-500 max-w-xs truncate">
                        {{ $customer->address ?? '-' }}
                    </td>
                    <td class="py-4 px-5 text-center">
                        <span class="px-2.5 py-1 text-xs font-bold rounded-full bg-blue-100 text-blue-800 border border-blue-200">
                            {{ $customer->total_orders }}x Order
                        </span>
                    </td>
                    <td class="py-4 px-5 font-bold text-emerald-600">
                        Rp {{ number_format($customer->total_spent, 0, ',', '.') }}
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="py-8 text-center text-gray-400 italic">
                        Belum ada data pelanggan. Data akan terisi otomatis saat ada transaksi checkout masuk.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection