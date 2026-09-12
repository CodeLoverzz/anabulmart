<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Http\Request;

class CartController extends Controller
{
    // Tampilkan Halaman Keranjang
    public function index()
    {
        $cart = session()->get('cart', []);
        return view('cart.index', compact('cart'));
    }

    // Tambah Produk / Beli Langsung dari Detail Produk
    public function add(Request $request, $id)
    {
        $product = Product::with('variants')->findOrFail($id);
        $variantId = $request->input('variant_id');
        $quantity = (int) $request->input('quantity', 1);
        $action = $request->input('action'); // 'add_to_cart' ATAU 'checkout'

        $variant = null;
        if ($variantId) {
            $variant = ProductVariant::find($variantId);
        } else {
            $variant = $product->variants->first();
        }

        // Ambil path gambar yang valid
        $rawImage = $product->main_image ?? ($variant->variant_image ?? ($variant->image ?? null));
        $cleanImage = $rawImage ? ltrim(str_replace(['public/', 'storage/'], '', $rawImage), '/') : null;

        $price = ($variant && $variant->price > 0) ? $variant->price : ($product->price ?? 0);
        $variantName = $variant ? $variant->variant_name : 'Default';

        // 1. Jika Aksinya 'checkout' (Beli Sekarang)
        if ($action === 'checkout') {
            session()->put('direct_checkout', [
                'product_id'   => $product->id,
                'product_name' => $product->name,
                'variant_id'   => $variant ? $variant->id : null,
                'variant_name' => $variantName,
                'price'        => $price,
                'quantity'     => $quantity,
                'image'        => $cleanImage,
                'weight_gram'  => $product->weight_gram ?? 1000,
            ]);

            return redirect()->route('checkout.index', ['type' => 'direct']);
        }

        // 2. Jika Aksinya 'add_to_cart' (Masukkan Keranjang)
        $cart = session()->get('cart', []);
        $cartKey = $product->id . '-' . ($variant ? $variant->id : '0');

        if (isset($cart[$cartKey])) {
            $cart[$cartKey]['quantity'] += $quantity;
        } else {
            $cart[$cartKey] = [
                'product_id'   => $product->id,
                'product_name' => $product->name,
                'variant_id'   => $variant ? $variant->id : null,
                'variant_name' => $variantName,
                'price'        => $price,
                'quantity'     => $quantity,
                'image'        => $cleanImage,
                'weight_gram'  => $product->weight_gram ?? 1000,
                'checked'      => true, // Default tercentang
            ];
        }

        session()->put('cart', $cart);

        return redirect()->back()->with('success', 'Produk berhasil ditambahkan ke keranjang!');
    }

    // Update Quantity atau Checkbox di Keranjang
    public function update(Request $request)
    {
        $cart = session()->get('cart', []);
        $key = $request->input('key');

        if (isset($cart[$key])) {
            if ($request->has('quantity')) {
                $cart[$key]['quantity'] = max(1, (int) $request->input('quantity'));
            }
            if ($request->has('checked')) {
                $cart[$key]['checked'] = (bool) $request->input('checked');
            }
            session()->put('cart', $cart);
        }

        return redirect()->route('cart.index');
    }

    // Hapus Item dari Keranjang
    public function remove(Request $request)
    {
        $cart = session()->get('cart', []);
        $key = $request->input('key');

        if (isset($cart[$key])) {
            unset($cart[$key]);
            session()->put('cart', $cart);
        }

        return redirect()->route('cart.index')->with('success', 'Item berhasil dihapus dari keranjang.');
    }
}