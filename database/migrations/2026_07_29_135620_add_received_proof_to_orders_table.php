<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            // Ubah tipe kolom status jadi string agar muat nilai completed, shipped, dll.
            $table->string('status', 50)->default('pending')->change();

            // Tambahkan kolom bukti foto barang diterima jika belum ada
            if (!Schema::hasColumn('orders', 'received_proof')) {
                $table->string('received_proof')->nullable()->after('alasan_penolakan');
            }
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('received_proof');
        });
    }
};