<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            // Precios por kg adicionales por canal
            // price_per_kg existente = precio base (web/ecommerce)
            $table->decimal('price_per_kg_pos', 10, 2)->nullable()->after('price_per_kg');
            $table->decimal('price_per_kg_delivery_app', 10, 2)->nullable()->after('price_per_kg_pos');

            // Tamaño de variante 1500ml está en cup_sizes.
            // Para listar volúmenes disponibles en el futuro
            // (actualmente manejado en product_variants, no se cambia)
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['price_per_kg_pos', 'price_per_kg_delivery_app']);
        });
    }
};