<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductVariant extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'sku',            // <-- Tambahkan kolom ini
        'variant_name',
        'price',
        'stock',
        'variant_image',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}