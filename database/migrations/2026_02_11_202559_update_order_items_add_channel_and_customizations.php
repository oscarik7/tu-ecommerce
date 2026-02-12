<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            // Canal de precio usado en este item
            // 'web' = precio ecommerce, 'pos' = precio tienda, 'delivery_app' = precio app
            $table->string('price_channel')->default('web')->after('price_per_kg');

            // Subtotal de personalizaciones (extras con costo)
            $table->decimal('customizations_subtotal', 10, 2)->default(0)->after('subtotal');

            // El 'subtotal' existente pasa a ser subtotal del item base (sin extras)
            // El total real del item = subtotal + customizations_subtotal
        });
    }

    public function down(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->dropColumn(['price_channel', 'customizations_subtotal']);
        });
    }
};