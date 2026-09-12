<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            if (!Schema::hasColumn('products', 'category_name')) {
                $table->string('category_name')->nullable();
            }
            if (!Schema::hasColumn('products', 'weight_gram')) {
                $table->integer('weight_gram')->default(1000); // Untuk RajaOngkir
            }
            if (!Schema::hasColumn('products', 'min_order')) {
                $table->integer('min_order')->default(1);
            }
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['category_name', 'weight_gram', 'min_order']);
        });
    }
};