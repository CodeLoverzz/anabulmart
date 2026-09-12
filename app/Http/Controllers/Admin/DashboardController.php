<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Order;
use App\Models\Product;
use App\Models\Customer;

class DashboardController extends Controller
{
    // 1. Tampilkan Form Login Admin
    public function loginForm()
    {
        if (Auth::check()) {
            return redirect()->route('admin.dashboard');
        }
        
        if (view()->exists('admin.login')) {
            return view('admin.login');
        }
        
        return view('login');
    }

    // 2. Eksekusi Proses Login Admin
    public function loginProcess(Request $request)
    {
        $credentials = $request->validate([
            'email'    => 'required|email',
            'password' => 'required',
        ]);

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();
            return redirect()->intended(route('admin.dashboard'));
        }

        return back()->withErrors([
            'email' => 'Email atau password salah.',
        ])->onlyInput('email');
    }

    // 3. Halaman Utama Dashboard Admin
    public function index()
    {
        $totalOrders   = Order::count();
        $totalProducts = Product::count();
        $totalCustomers = Customer::count();
        
        // Menghitung pesanan yang butuh verifikasi/proses (Pending)
        $pendingOrders = Order::whereIn('status', ['pending', 'menunggu_pembayaran', 'menunggu_konfirmasi'])->count();
        
        // Total Pendapatan
        $totalRevenue  = Order::whereIn('status', ['paid', 'shipped', 'completed', 'lunas', 'dikirim', 'selesai'])->sum('total_amount');
        
        // 5 Pesanan Terbaru
        $recentOrders  = Order::latest()->take(5)->get();

        return view('admin.dashboard', compact(
            'totalOrders', 
            'totalProducts', 
            'totalCustomers',
            'pendingOrders', 
            'totalRevenue', 
            'recentOrders'
        ));
    }

    // 4. Halaman Data Transaksi (Orders)
    public function ordersIndex()
    {
        $orders = Order::latest()->get();
        return view('admin.orders.index', compact('orders'));
    }

    // 5. Update Status Pesanan
    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|string',
        ]);

        $order = Order::findOrFail($id);
        $order->status = $request->status;
        $order->save();

        return redirect()->back()->with('success', 'Status pesanan berhasil diperbarui!');
    }

    // 6. Logout Admin
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('admin.login');
    }
}