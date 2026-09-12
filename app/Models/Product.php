<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'category',
        'category_name',
        'description',   // [FIX] sebelumnya tidak ada di sini, jadi deskripsi produk tidak pernah tersimpan
        'price',
        'stock',
        'min_order',
        'weight_gram',
        'main_image',    // pastikan controller menulis ke key ini, BUKAN 'image'
    ];

    public function variants()
    {
        return $this->hasMany(ProductVariant::class);
    }
}
