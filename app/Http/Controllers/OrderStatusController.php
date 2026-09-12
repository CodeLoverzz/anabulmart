<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;

class OrderStatusController extends Controller
{
    public function index(Request $request)
    {
        $order = null;
        $search = $request->query('order_number');

        if (!empty($search)) {
            // Memuat relasi items dan variant.product
            $order = Order::with(['items.variant.product'])
                ->where('order_number', trim($search))
                ->first();
        }

        return view('order.status', compact('order', 'search'));
    }
}