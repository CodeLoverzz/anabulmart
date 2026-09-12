<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Models\Product;
use App\Models\Order;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\OrderStatusController;
use App\Http\Controllers\MediaController;
use App\Http\Controllers\Admin\OrderController as AdminOrderController;
use App\Http\Controllers\Admin\ProductController as AdminProductController;
use App\Http\Controllers\Admin\AuthController as AdminAuthController; // [FIX] sebelumnya tidak pernah di-import/dipakai

/*
|--------------------------------------------------------------------------
| Web Routes - AnabulMart Medan
|--------------------------------------------------------------------------
*/

// =========================================================================
// 1. PUBLIC / KATALOG & PEMBELI
// =========================================================================

Route::get('/', function () {
    return redirect()->route('catalog.index');
});

// [HAPUS] Rute /login lama (mengarah ke view 'auth.login' yang tidak pernah ada,
// hanya dirujuk oleh welcome.blade.php yang juga tidak pernah ditampilkan).
// Login yang dipakai aplikasi ini adalah /admin/login.
// Halaman Katalog / Dashboard Utama Pembeli
Route::get('/catalog', function () {
    $products = Product::with('variants')->latest()->get();
    return view('catalog.index', compact('products'));
})->name('catalog.index');

// Detail Produk Pembeli
Route::get('/catalog/{id}', function ($id) {
    $product = Product::with('variants')->findOrFail($id);
    return view('catalog.show', compact('product'));
})->name('catalog.show');

// KERANJANG BELANJA / CART
Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
Route::post('/cart/add/{id?}', [CartController::class, 'add'])->name('cart.add');
Route::post('/cart/update/{id?}', [CartController::class, 'update'])->name('cart.update');
Route::delete('/cart/remove/{id?}', [CartController::class, 'remove'])->name('cart.remove');
Route::post('/cart/remove/{id?}', [CartController::class, 'remove']);

// Process Checkout & Payment
Route::get('/checkout', [CheckoutController::class, 'index'])->name('checkout.index');
Route::post('/checkout', [CheckoutController::class, 'store'])->name('checkout.store');
Route::get('/checkout/qris/{orderNumber}', [CheckoutController::class, 'qris'])->name('checkout.qris');
Route::post('/checkout/upload/{orderNumber}', [CheckoutController::class, 'uploadPayment'])->name('checkout.upload');

// Lacak Status Pesanan Pembeli
Route::get('/cek-pesanan', [OrderStatusController::class, 'index'])->name('order.status');

// STREAM MEDIA GAMBAR BYPASS FORBIDDEN 403 XAMPP
Route::get('/media/{path}', [MediaController::class, 'show'])->where('path', '.*')->name('media.show');


// =========================================================================
// 2. LOGIN ADMIN (DI LUAR MIDDLEWARE AUTH, SUPAYA BISA DIAKSES SEBELUM LOGIN)
// =========================================================================
// [FIX] Rute ini sebelumnya tidak pernah didaftarkan sama sekali, sehingga
// halaman login admin tidak bisa diakses dan admin.login route tidak ada.

Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('/login', [AdminAuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AdminAuthController::class, 'login'])->name('login.process'); // [FIX] nama rute ini WAJIB ada karena resources/views/admin/login.blade.php memanggil route('admin.login.process')
});


// =========================================================================
// 3. ROUTE PANEL ADMIN (WAJIB LOGIN)
// =========================================================================
// [FIX] Ditambahkan middleware 'auth' pada seluruh grup ini. Sebelumnya
// SIAPA SAJA bisa membuka /admin/products, /admin/orders, dll tanpa login.

Route::prefix('admin')->name('admin.')->middleware('auth')->group(function () {

    // Helper Fungsi Deteksi Kolom Total Harga
    $getAmountColumn = function () {
        if (Schema::hasColumn('orders', 'total_amount')) return 'total_amount';
        if (Schema::hasColumn('orders', 'grand_total')) return 'grand_total';
        if (Schema::hasColumn('orders', 'total')) return 'total';
        if (Schema::hasColumn('orders', 'total_price')) return 'total_price';
        return 'total_amount';
    };

    // DASHBOARD ADMIN
    Route::get('/dashboard', function () use ($getAmountColumn) {
        $amountCol     = $getAmountColumn();
        $totalOrders   = Order::count();
        $totalProducts = Product::count();
        $totalRevenue  = Order::whereIn('status', ['delivered', 'selesai', 'completed'])->sum($amountCol) ?? 0;
        $recentOrders  = Order::latest()->take(5)->get();
        $pendingOrders = Order::whereIn('status', ['pending', 'waiting_confirmation'])->count();

        $data = compact('totalOrders', 'totalProducts', 'totalRevenue', 'recentOrders', 'pendingOrders');

        if (view()->exists('admin.dashboard')) return view('admin.dashboard', $data);
        if (view()->exists('admin.dashboard.index')) return view('admin.dashboard.index', $data);
        return redirect()->route('admin.orders.index');
    })->name('dashboard');

    // Logout Admin
    // [FIX] Sebelumnya cuma redirect tanpa benar-benar logout (session admin tetap aktif).
    Route::post('/logout', function () {
        Auth::logout();
        request()->session()->invalidate();
        request()->session()->regenerateToken();
        return redirect()->route('admin.login');
    })->name('logout');

    // 1. DATA PRODUK (ADMIN)
    Route::get('/products', [AdminProductController::class, 'index'])->name('products.index');
    Route::get('/product', [AdminProductController::class, 'index'])->name('product.index');

    Route::get('/products/create', [AdminProductController::class, 'create'])->name('products.create');
    Route::get('/product/create', [AdminProductController::class, 'create'])->name('product.create');

    Route::post('/products', [AdminProductController::class, 'store'])->name('products.store');
    Route::post('/product', [AdminProductController::class, 'store'])->name('product.store');

    Route::get('/products/{id}/edit', [AdminProductController::class, 'edit'])->name('products.edit');
    Route::get('/product/{id}/edit', [AdminProductController::class, 'edit'])->name('product.edit');

    Route::put('/products/{id}', [AdminProductController::class, 'update'])->name('products.update');
    Route::put('/product/{id}', [AdminProductController::class, 'update'])->name('product.update');

    Route::delete('/products/{id}', [AdminProductController::class, 'destroy'])->name('products.destroy');
    Route::delete('/product/{id}', [AdminProductController::class, 'destroy'])->name('product.destroy');

    // 2. DATA TRANSAKSI / ORDERS (ADMIN)
    Route::get('/orders', [AdminOrderController::class, 'index'])->name('orders.index');
    Route::get('/orders/{id}', [AdminOrderController::class, 'show'])->name('orders.show');
    Route::put('/orders/{id}/status', [AdminOrderController::class, 'updateStatus'])->name('orders.updateStatus');
    Route::post('/orders/{id}/status', [AdminOrderController::class, 'updateStatus']);
    Route::put('/order/{id}/status', [AdminOrderController::class, 'updateStatus']);
    Route::post('/order/{id}/status', [AdminOrderController::class, 'updateStatus']);

    // 3. DATA PELANGGAN / CUSTOMERS (ADMIN)
    Route::get('/customers', function () {
        $customers = Order::select('customer_name', 'customer_whatsapp', 'shipping_address')
            ->selectRaw('COUNT(id) as total_orders')
            ->selectRaw('MAX(created_at) as last_order_date')
            ->groupBy('customer_name', 'customer_whatsapp', 'shipping_address')
            ->latest('last_order_date')
            ->paginate(15);

        if (view()->exists('admin.customers.index')) {
            return view('admin.customers.index', compact('customers'));
        } elseif (view()->exists('admin.customers')) {
            return view('admin.customers', compact('customers'));
        }
        return redirect()->route('admin.orders.index');
    })->name('customers.index');

    // 4. LAPORAN / REPORTS (ADMIN)
    Route::get('/reports', function () use ($getAmountColumn) {
        $amountCol = $getAmountColumn();

        $totalRevenue       = Order::whereIn('status', ['delivered', 'selesai', 'completed'])->sum($amountCol) ?? 0;
        $totalOrders        = Order::count();
        $completedOrders    = Order::whereIn('status', ['delivered', 'selesai', 'completed'])->latest()->get();

        $completedOrdersCount = $completedOrders->count();
        $totalSuccessOrders   = $completedOrdersCount;
        $pendingOrders        = Order::whereIn('status', ['pending', 'waiting_confirmation'])->count();
        $canceledOrders       = Order::whereIn('status', ['rejected', 'ditolak', 'canceled'])->count();

        $totalItemsSold = 0;
        if (Schema::hasTable('order_items')) {
            $totalItemsSold = DB::table('order_items')->sum('quantity') ?? 0;
        } elseif (Schema::hasColumn('orders', 'total_items')) {
            $totalItemsSold = Order::sum('total_items') ?? 0;
        } else {
            $totalItemsSold = $totalOrders;
        }

        $monthlyReport = Order::select(
                DB::raw('YEAR(created_at) as year'),
                DB::raw('MONTH(created_at) as month'),
                DB::raw('COUNT(id) as total_orders'),
                DB::raw("SUM($amountCol) as total_revenue")
            )
            ->groupBy('year', 'month')
            ->orderBy('year', 'desc')
            ->orderBy('month', 'desc')
            ->get();

        $monthlyRevenue = $monthlyReport;
        $salesData      = $monthlyReport;
        $topProducts    = Product::latest()->take(5)->get();
        $orders         = Order::latest()->paginate(15);

        $data = compact(
            'totalRevenue', 'totalOrders', 'completedOrders', 'completedOrdersCount',
            'totalSuccessOrders', 'pendingOrders', 'canceledOrders', 'totalItemsSold',
            'monthlyReport', 'monthlyRevenue', 'salesData', 'topProducts', 'orders'
        );

        if (view()->exists('admin.reports.index')) {
            return view('admin.reports.index', $data);
        } elseif (view()->exists('admin.reports')) {
            return view('admin.reports', $data);
        }
        return redirect()->route('admin.orders.index');
    })->name('reports.index');

    // 5. KATEGORI & PENGATURAN (ADMIN)
    Route::get('/categories', function () {
        if (view()->exists('admin.categories.index')) return view('admin.categories.index');
        return redirect()->route('admin.products.index');
    })->name('categories.index');

    Route::get('/settings', function () {
        if (view()->exists('admin.settings.index')) return view('admin.settings.index');
        return redirect()->route('admin.orders.index');
    })->name('settings.index');

});

// Global Fallback Logout
Route::post('/logout', function () {
    return redirect()->route('catalog.index');
})->name('logout');