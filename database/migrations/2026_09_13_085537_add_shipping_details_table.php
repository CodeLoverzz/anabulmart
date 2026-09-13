<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            if (!Schema::hasColumn('orders', 'destination_label')) {
                $table->string('destination_label')->nullable()->after('city_id');
            }
            if (!Schema::hasColumn('orders', 'shipping_courier')) {
                $table->string('shipping_courier')->nullable()->after('shipping_cost');
            }
            if (!Schema::hasColumn('orders', 'shipping_service')) {
                $table->string('shipping_service')->nullable()->after('shipping_courier');
            }
            if (!Schema::hasColumn('orders', 'total_weight_gram')) {
                $table->integer('total_weight_gram')->nullable()->after('shipping_service');
            }
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['destination_label', 'shipping_courier', 'shipping_service', 'total_weight_gram']);
        });
    }
};