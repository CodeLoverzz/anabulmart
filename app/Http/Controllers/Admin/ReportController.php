<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\OrderItem;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        // Query Dasar: Ambil pesanan yang sudah berhasil/lunas/selesai
        $query = Order::whereIn('status', ['paid', 'shipped', 'completed', 'lunas', 'dikirim', 'selesai']);

        // Filter Berdasarkan Tanggal
        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('created_at', [$request->start_date . ' 00:00:00', $request->end_date . ' 23:59:59']);
        } elseif ($request->filled('filter_type')) {
            if ($request->filter_type === 'today') {
                $query->whereDate('created_at', now()->today());
            } elseif ($request->filter_type === 'this_month') {
                $query->whereMonth('created_at', now()->month)->whereYear('created_at', now()->year);
            }
        }

        $completedOrders = $query->latest()->get();

        // Hitung Ringkasan Angka
        $totalRevenue = $completedOrders->sum('total_amount');
        $totalSuccessOrders = $completedOrders->count();
        
        // Hitung Total Item Produk Terjual
        $orderIds = $completedOrders->pluck('id');
        $totalItemsSold = OrderItem::whereIn('order_id', $orderIds)->sum('quantity');

        return view('admin.reports.index', compact(
            'completedOrders',
            'totalRevenue',
            'totalSuccessOrders',
            'totalItemsSold'
        ));
    }
}