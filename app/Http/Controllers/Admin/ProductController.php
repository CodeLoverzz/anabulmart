<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class ProductController extends Controller
{
    // 1. TAMPILKAN SEMUA PRODUK
    public function index()
    {
        $products = Product::with(['variants'])->latest()->paginate(10);
        return view('admin.products.index', compact('products'));
    }

    // 2. FORM TAMBAH PRODUK
    public function create()
    {
        return view('admin.products.create');
    }

    // 3. SIMPAN PRODUK BARU
    public function store(Request $request)
    {
        $request->validate([
            'name'        => 'required|string|max:255',
            'description' => 'nullable|string',
            'price'       => 'nullable|numeric',
            'stock'       => 'nullable|integer',
            'weight_gram' => 'nullable|integer',
            'image'       => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
        ]);

        DB::beginTransaction();
        try {
            $hasVariants = $request->has('variants') && is_array($request->variants) && count($request->variants) > 0;

            // Handle Foto Utama
            $imagePath = null;
            if ($request->hasFile('image')) {
                $imagePath = $request->file('image')->store('products', 'public');
            }

            // Hitung Harga Utama & Total Stok
            $mainPrice  = floatval($request->price ?? 0);
            $totalStock = 0;

            if ($hasVariants) {
                foreach ($request->variants as $v) {
                    if (!empty($v['name'])) {
                        $totalStock += intval($v['stock'] ?? 0);
                    }
                }
                if ($mainPrice <= 0 && isset($request->variants[0]['price'])) {
                    $mainPrice = floatval($request->variants[0]['price']);
                }
            } else {
                $totalStock = intval($request->stock ?? 0);
            }

            // Ambil Kategori dari Form
            $categoryName = $request->category_name ?? $request->category ?? 'Umum';

            // SKU utama HANYA dipakai untuk menyusun SKU varian di bawah.
            // [FIX] Tabel "products" tidak punya kolom "sku", jadi jangan dikirim ke Product::create().
            $productSku = 'ANB-' . strtoupper(Str::random(6));

            // Create Data Produk Utama
            $product = Product::create([
                'name'          => $request->name,
                'slug'          => Str::slug($request->name),
                'category'      => $categoryName,
                'category_name' => $categoryName,
                'description'   => $request->description,   // [FIX] sekarang benar-benar tersimpan (lihat Product::$fillable)
                'price'         => $mainPrice,
                'stock'         => $totalStock,
                'weight_gram'   => intval($request->weight_gram ?? 1000),
                'main_image'    => $imagePath,               // [FIX] sebelumnya 'image' (key salah, kolom aslinya main_image)
            ]);

            // Create Data Variasi
            if ($hasVariants) {
                foreach ($request->variants as $index => $vData) {
                    if (empty($vData['name'])) continue;

                    $vImg = null;
                    if ($request->hasFile("variants.{$index}.image")) {
                        $vImg = $request->file("variants.{$index}.image")->store('products/variants', 'public');
                    }

                    ProductVariant::create([
                        'product_id'    => $product->id,
                        'variant_name'  => $vData['name'],
                        'sku'           => $productSku . '-V-' . ($index + 1),
                        'price'         => floatval(!empty($vData['price']) ? $vData['price'] : $mainPrice),
                        'stock'         => intval($vData['stock'] ?? 0),
                        'variant_image' => $vImg,
                    ]);
                }
            }

            DB::commit();
            return redirect()->route('admin.products.index')->with('success', 'Produk berhasil ditambahkan!');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal simpan ke database: ' . $e->getMessage())->withInput();
        }
    }

    // 4. FORM EDIT PRODUK
    public function edit(int $id)
    {
        $product = Product::with('variants')->findOrFail($id);
        return view('admin.products.edit', compact('product'));
    }

    // 5. UPDATE PRODUK
    public function update(Request $request, int $id)
    {
        $product = Product::findOrFail($id);

        $request->validate([
            'name'        => 'required|string|max:255',
            'description' => 'nullable|string',
            'price'       => 'nullable|numeric',
            'stock'       => 'nullable|integer',
            'weight_gram' => 'nullable|integer',
            'image'       => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
        ]);

        DB::beginTransaction();
        try {
            $hasVariants = $request->has('variants') && count($request->variants) > 0;

            // Foto Utama Baru
            $imagePath = $product->main_image; // [FIX] sebelumnya $product->image (atribut ini tidak pernah ada, selalu null)
            if ($request->hasFile('image')) {
                if ($product->main_image && Storage::disk('public')->exists($product->main_image)) {
                    Storage::disk('public')->delete($product->main_image);
                }
                $imagePath = $request->file('image')->store('products', 'public');
            }

            $mainPrice  = floatval($request->price ?? $product->price);
            $totalStock = 0;
            $productSku = 'ANB-' . strtoupper(Str::random(6)); // dipakai hanya untuk penomoran SKU varian baru

            if ($hasVariants) {
                $inputVariantIds = [];

                foreach ($request->variants as $index => $vData) {
                    if (empty($vData['name'])) continue;

                    $vStock = intval($vData['stock'] ?? 0);
                    $vPrice = floatval(!empty($vData['price']) ? $vData['price'] : $mainPrice);
                    $totalStock += $vStock;

                    $vImg = null;
                    if ($request->hasFile("variants.{$index}.image")) {
                        $vImg = $request->file("variants.{$index}.image")->store('products/variants', 'public');
                    }

                    if (!empty($vData['id'])) {
                        // UPDATE VARIASI LAMA
                        $variant = ProductVariant::find($vData['id']);
                        if ($variant) {
                            $updateData = [
                                'variant_name' => $vData['name'],
                                'price'        => $vPrice,
                                'stock'        => $vStock,
                            ];
                            if ($vImg) {
                                if ($variant->variant_image && Storage::disk('public')->exists($variant->variant_image)) {
                                    Storage::disk('public')->delete($variant->variant_image);
                                }
                                $updateData['variant_image'] = $vImg;
                            }
                            $variant->update($updateData);
                            $inputVariantIds[] = $variant->id;
                        }
                    } else {
                        // BUAT VARIASI BARU
                        $newVariant = ProductVariant::create([
                            'product_id'    => $product->id,
                            'variant_name'  => $vData['name'],
                            'sku'           => $productSku . '-V-' . (ProductVariant::where('product_id', $product->id)->count() + 1),
                            'price'         => $vPrice,
                            'stock'         => $vStock,
                            'variant_image' => $vImg,
                        ]);
                        $inputVariantIds[] = $newVariant->id;
                    }
                }

                // Hapus variasi yang dihapus dari form saat edit
                ProductVariant::where('product_id', $product->id)
                    ->whereNotIn('id', $inputVariantIds)
                    ->delete();

            } else {
                // TANPA VARIASI
                $totalStock = intval($request->stock ?? 0);
                ProductVariant::where('product_id', $product->id)->delete();
            }

            // Ambil Kategori dari Form (Update)
            $categoryName = $request->category_name ?? $request->category ?? $product->category ?? 'Umum';

            // Update data produk utama
            $product->update([
                'name'          => $request->name,
                'slug'          => Str::slug($request->name),
                'category'      => $categoryName,
                'category_name' => $categoryName,
                'description'   => $request->description,   // [FIX] sekarang tersimpan
                'price'         => $mainPrice,
                'stock'         => $totalStock,
                'weight_gram'   => intval($request->weight_gram ?? $product->weight_gram ?? 1000),
                'main_image'    => $imagePath,               // [FIX] sebelumnya 'image'
            ]);

            DB::commit();
            return redirect()->route('admin.products.index')->with('success', 'Produk & stok berhasil diperbarui!');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal memperbarui produk: ' . $e->getMessage())->withInput();
        }
    }

    // 6. HAPUS PRODUK (DENGAN PENGAMANAN FOREIGN KEY)
    public function destroy(int $id)
    {
        $product = Product::with('variants')->findOrFail($id);

        DB::beginTransaction();
        try {
            // 1. Hapus foto utama produk jika ada
            if ($product->main_image && Storage::disk('public')->exists($product->main_image)) {
                Storage::disk('public')->delete($product->main_image);
            }

            // 2. Hapus foto variasi & data variasi terkait
            foreach ($product->variants as $variant) {
                if ($variant->variant_image && Storage::disk('public')->exists($variant->variant_image)) {
                    Storage::disk('public')->delete($variant->variant_image);
                }

                // Amankan order_items agar tidak kena error foreign key (1451)
                DB::table('order_items')->where('product_variant_id', $variant->id)->update(['product_variant_id' => null]);

                $variant->delete();
            }

            // 3. Hapus produk utama
            $product->delete();

            DB::commit();
            return redirect()->route('admin.products.index')->with('success', 'Produk berhasil dihapus!');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal menghapus produk: ' . $e->getMessage());
        }
    }
}
