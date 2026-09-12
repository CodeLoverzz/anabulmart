<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use App\Models\Product;
use App\Models\ProductVariant;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Buat Produk 1
        $name1 = 'Whiskas Adult Dry Food 1.2kg - Makanan Kucing Kering';
        $p1 = Product::create([
            'name' => $name1,
            'slug' => Str::slug($name1),
            'category' => 'Makanan Kucing',
            'description' => 'Makanan kucing bernutrisi lengkap dan seimbang untuk kucing dewasa usia 1 tahun ke atas.',
        ]);

        ProductVariant::create([
            'product_id' => $p1->id,
            'variant_name' => 'Ocean Fish 1.2kg',
            'price' => 65000,
            'stock' => 20,
            'image' => null,
        ]);

        ProductVariant::create([
            'product_id' => $p1->id,
            'variant_name' => 'Tuna 1.2kg',
            'price' => 67000,
            'stock' => 15,
            'image' => null,
        ]);

        // 2. Buat Produk 2
        $name2 = 'Bolt Salmon Cats Food 1kg - Makanan Kucing Murah';
        $p2 = Product::create([
            'name' => $name2,
            'slug' => Str::slug($name2),
            'category' => 'Makanan Kucing',
            'description' => 'Bolt Tuna/Salmon diformulasikan untuk memenuhi nutrisi standar profil pakan kucing.',
        ]);

        ProductVariant::create([
            'product_id' => $p2->id,
            'variant_name' => 'Rasa Salmon 1kg',
            'price' => 26000,
            'stock' => 50,
            'image' => null,
        ]);

        // 3. Buat Produk 3
        $name3 = 'Pasir Kucing Gumpal Wangi Bentonite 5.5L';
        $p3 = Product::create([
            'name' => $name3,
            'slug' => Str::slug($name3),
            'category' => 'Perlengkapan',
            'description' => 'Pasir kucing gumpal wangi berkualitas tinggi dengan daya serap maksimal.',
        ]);

        ProductVariant::create([
            'product_id' => $p3->id,
            'variant_name' => 'Aroma Lavender',
            'price' => 35000,
            'stock' => 10,
            'image' => null,
        ]);
    }
}