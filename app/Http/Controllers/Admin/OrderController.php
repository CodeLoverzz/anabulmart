<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class OrderController extends Controller
{
    // TAMPILKAN DAFTAR TRANSAKSI ADMIN
    public function index(Request $request)
    {
        $query = Order::with('items')->latest();

        if ($request->has('status') && $request->status != '') {
            $query->where('status', $request->status);
        }

        $orders = $query->paginate(10);

        return view('admin.orders.index', compact('orders'));
    }

    // DETAIL TRANSAKSI
    public function show($id)
    {
        $order = Order::with(['items.variant.product'])->findOrFail($id);
        return view('admin.orders.show', compact('order'));
    }

    // UPDATE STATUS DAN SIMPAN FOTO BUKTI PENGIRIMAN / PENERIMAAN
    public function updateStatus(Request $request, $id)
    {
        $order = Order::findOrFail($id);

        $request->validate([
            'status'           => 'required|string',
            'shipping_proof'   => 'nullable|image|mimes:jpeg,png,jpg,webp|max:5048',
            'received_proof'   => 'nullable|image|mimes:jpeg,png,jpg,webp|max:5048',
            'alasan_penolakan' => 'nullable|string',
        ]);

        $dataToUpdate = [
            'status' => $request->status,
        ];

        // 1. Simpan foto pengiriman jika admin mengunggah file baru
        if ($request->hasFile('shipping_proof')) {
            if ($order->shipping_proof && Storage::disk('public')->exists($order->shipping_proof)) {
                Storage::disk('public')->delete($order->shipping_proof);
            }
            $pathShipping = $request->file('shipping_proof')->store('shipping_proofs', 'public');
            $dataToUpdate['shipping_proof'] = $pathShipping;
        }

        // 2. Simpan foto penerimaan jika admin mengunggah file baru
        if ($request->hasFile('received_proof')) {
            if ($order->received_proof && Storage::disk('public')->exists($order->received_proof)) {
                Storage::disk('public')->delete($order->received_proof);
            }
            $pathReceived = $request->file('received_proof')->store('received_proofs', 'public');
            $dataToUpdate['received_proof'] = $pathReceived;
        }

        // 3. Catat alasan penolakan jika status rejected
        if (in_array($request->status, ['rejected', 'ditolak'])) {
            $dataToUpdate['alasan_penolakan'] = $request->alasan_penolakan ?? 'Bukti pembayaran tidak sesuai.';
        }

        // Update database dengan data baru
        $order->update($dataToUpdate);

        return redirect()->back()->with('success', 'Status pesanan & foto bukti berhasil diperbarui!');
    }
}