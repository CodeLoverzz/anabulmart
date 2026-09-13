<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class CheckoutController extends Controller
{
    // 1. TAMPILKAN HALAMAN FORM CHECKOUT
    public function index(Request $request)
    {
        $itemsToCheckout = [];
        $checkoutType = $request->query('type', 'cart');

        if ($checkoutType === 'direct') {
            // Langsung Beli dari Detail Produk
            $directItem = session()->get('direct_checkout');
            if (!$directItem) {
                return redirect()->route('catalog.index')->with('error', 'Sesi pembelian telah berakhir.');
            }
            $itemsToCheckout[] = $directItem;
        } else {
            // Checkout dari Keranjang Belanja (Sesuai Centangan)
            $cart = session()->get('cart', []);
            foreach ($cart as $key => $item) {
                if (!isset($item['checked']) || $item['checked'] == true) {
                    $itemsToCheckout[$key] = $item;
                }
            }

            if (empty($itemsToCheckout)) {
                return redirect()->route('cart.index')->with('error', 'Pilih minimal satu produk di keranjang untuk di-checkout.');
            }
        }

        $subtotal = 0;
        $totalWeight = 0;
        foreach ($itemsToCheckout as $item) {
            $subtotal += ($item['price'] * $item['quantity']);
            $totalWeight += (($item['weight_gram'] ?? 1000) * $item['quantity']);
        }
        $totalWeight = max(1, $totalWeight); // API menolak berat 0

        return view('checkout.index', compact('itemsToCheckout', 'subtotal', 'checkoutType', 'totalWeight'));
    }

    // 2. SIMPAN PESANAN PERMANEN KE DATABASE
    public function store(Request $request)
    {
        $request->validate([
            'customer_name'      => 'required|string|max:255',
            'customer_phone'     => 'required|string|max:20',
            'shipping_address'   => 'required|string',
            'destination_id'     => 'required|string',
            'destination_label'  => 'required|string',
            'shipping_courier'   => 'required|string',
            'shipping_service'   => 'required|string',
            'shipping_cost'      => 'required|numeric|min:0',
        ], [
            'destination_id.required'    => 'Silakan pilih wilayah tujuan pengiriman terlebih dahulu.',
            'shipping_courier.required'  => 'Silakan pilih kurir & hitung ongkir terlebih dahulu.',
        ]);

        $checkoutType = $request->input('checkout_type', 'cart');
        $itemsToCheckout = [];

        if ($checkoutType === 'direct') {
            $directItem = session()->get('direct_checkout');
            if (!$directItem) {
                return redirect()->route('catalog.index')->with('error', 'Sesi pembelian telah berakhir.');
            }
            $itemsToCheckout[] = $directItem;
        } else {
            $cart = session()->get('cart', []);
            foreach ($cart as $key => $item) {
                if (!isset($item['checked']) || $item['checked'] == true) {
                    $itemsToCheckout[$key] = $item;
                }
            }
        }

        if (empty($itemsToCheckout)) {
            return redirect()->route('catalog.index')->with('error', 'Tidak ada item yang dapat diproses.');
        }

        // Hitung Subtotal & Total Berat
        $subtotal = 0;
        $totalWeight = 0;
        foreach ($itemsToCheckout as $item) {
            $subtotal += ($item['price'] * $item['quantity']);
            $totalWeight += (($item['weight_gram'] ?? 1000) * $item['quantity']);
        }

        $shippingCost = (float) $request->shipping_cost;
        $totalAmount = $subtotal + $shippingCost;

        // Nomor Order Unik
        $orderNumber = 'ORD-' . date('Ymd') . '-' . strtoupper(Str::random(4));

        // [FIX] Validasi stok SEBELUM transaksi dibuat, supaya stok tidak pernah minus.
        foreach ($itemsToCheckout as $item) {
            $productId = $item['product_id'] ?? null;
            $variantId = $item['variant_id'] ?? null;

            if (!$variantId && $productId) {
                $firstVariant = ProductVariant::where('product_id', $productId)->first();
                $variantId = $firstVariant ? $firstVariant->id : null;
            }

            if (!empty($variantId)) {
                $variant = ProductVariant::find($variantId);
                if (!$variant || $variant->stock < $item['quantity']) {
                    $nama = $variant->variant_name ?? 'produk';
                    return redirect()->back()->with('error', "Stok \"$nama\" tidak mencukupi.")->withInput();
                }
            } elseif (!empty($productId)) {
                $product = Product::find($productId);
                if (!$product || $product->stock < $item['quantity']) {
                    $nama = $product->name ?? 'produk';
                    return redirect()->back()->with('error', "Stok \"$nama\" tidak mencukupi.")->withInput();
                }
            }
        }

        DB::beginTransaction();
        try {
            // A. SIMPAN KE TABEL ORDERS
            $order = Order::create([
                'order_number'       => $orderNumber,
                'customer_name'      => $request->customer_name,
                'customer_whatsapp'  => $request->customer_phone,
                'shipping_address'   => $request->shipping_address,
                'city_id'            => $request->destination_id,        // ID wilayah dari RajaOngkir (kelurahan/kecamatan)
                'destination_label'  => $request->destination_label,     // Nama wilayah lengkap, untuk ditampilkan di admin
                'subtotal'           => $subtotal,
                'shipping_cost'      => $shippingCost,
                'shipping_courier'   => $request->shipping_courier,
                'shipping_service'   => $request->shipping_service,
                'total_weight_gram'  => $totalWeight,
                'total_amount'       => $totalAmount,
                'status'             => 'pending',
                'payment_proof'      => null,
                'alasan_penolakan'   => null,
                'received_proof'     => null,
                'shipping_proof'     => null,
            ]);

            // B. SIMPAN KE TABEL ORDER_ITEMS
            foreach ($itemsToCheckout as $key => $item) {
                $productId = $item['product_id'] ?? null;
                $variantId = $item['variant_id'] ?? null;

                if (!$variantId && $productId) {
                    $firstVariant = ProductVariant::where('product_id', $productId)->first();
                    $variantId = $firstVariant ? $firstVariant->id : null;
                }

                OrderItem::create([
                    'order_id'           => $order->id,
                    'product_variant_id' => $variantId,
                    'price'              => $item['price'],
                    'quantity'           => $item['quantity'],
                    'subtotal'           => $item['price'] * $item['quantity'],
                ]);

                // Kurangi Stok Produk/Variasi
                if (!empty($variantId)) {
                    $variant = ProductVariant::find($variantId);
                    if ($variant) {
                        $variant->decrement('stock', $item['quantity']);
                    }
                } elseif (!empty($productId)) {
                    $product = Product::find($productId);
                    if ($product) {
                        $product->decrement('stock', $item['quantity']);
                    }
                }

                // Bersihkan dari Cart jika dari Keranjang
                if ($checkoutType !== 'direct') {
                    $cart = session()->get('cart', []);
                    if (isset($cart[$key])) {
                        unset($cart[$key]);
                        session()->put('cart', $cart);
                    }
                }
            }

            if ($checkoutType === 'direct') {
                session()->forget('direct_checkout');
            }

            DB::commit();

            return redirect()->route('checkout.qris', ['orderNumber' => $orderNumber])
                             ->with('success', 'Pesanan berhasil dibuat! Silakan selesaikan pembayaran.');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Gagal memproses pesanan: ' . $e->getMessage());
        }
    }

    // 3. TAMPILKAN HALAMAN QRIS PEMBAYARAN
    public function qris($orderNumber)
    {
        $order = Order::with('items')->where('order_number', $orderNumber)->first();

        if (!$order) {
            return redirect()->route('catalog.index')->with('error', 'Data pesanan tidak ditemukan.');
        }

        // Jika pembeli sudah mengupload bukti, arahkan langsung ke Lacak Pesanan
        if (!empty($order->payment_proof)) {
            return redirect()->route('order.status', ['order_number' => $orderNumber])
                             ->with('success', 'Bukti pembayaran Anda sudah dikirim dan sedang diverifikasi admin.');
        }

        return view('checkout.qris', compact('order'));
    }

    // 4. UPLOAD BUKTI PEMBAYARAN (LANGSUNG REDIRECT KE CEK PESANAN)
    public function uploadPayment(Request $request, $orderNumber)
    {
        $request->validate([
            'payment_proof' => 'required|image|mimes:jpeg,png,jpg,webp|max:2048',
        ]);

        $order = Order::where('order_number', $orderNumber)->firstOrFail();

        if ($request->hasFile('payment_proof')) {
            $proofPath = $request->file('payment_proof')->store('payment_proofs', 'public');

            // Update Status ke Database
            $order->update([
                'payment_proof' => $proofPath,
                'status'        => 'waiting_confirmation',
            ]);
        }

        // REDIRECT LANGSUNG KE HALAMAN LACAK STATUS PESANAN
        return redirect()->route('order.status', ['order_number' => $orderNumber])
                         ->with('success', 'Bukti pembayaran berhasil terkirim! Admin akan segera memverifikasi pesanan Anda.');
    }
}