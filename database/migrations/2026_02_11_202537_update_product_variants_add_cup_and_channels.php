<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Agregar cup_size_id a product_variants
        //    El campo 'volume' y 'stock' se mantienen por compatibilidad,
        //    cup_size_id es la referencia al inventario compartido real.
        Schema::table('product_variants', function (Blueprint $table) {
            $table->unsignedBigInteger('cup_size_id')->nullable()->after('product_id');
            $table->foreign('cup_size_id')->references('id')->on('cup_sizes')->onDelete('set null');

            // Precios por canal de venta (nullable = usa el precio base)
            // El precio base existente ('price') será el precio de ecommerce/web
            $table->decimal('price_pos', 10, 2)->nullable()->after('price');          // Tienda física
            $table->decimal('price_delivery_app', 10, 2)->nullable()->after('price_pos'); // Pedidos Ya, etc.
            // 'price' existente = precio web/ecommerce (ya existe en la tabla)
        });

        // 2. Poblar cup_size_id basado en el campo 'volume' existente
        DB::statement("
            UPDATE product_variants pv
            SET cup_size_id = (
                SELECT cs.id FROM cup_sizes cs WHERE cs.volume_ml = pv.volume LIMIT 1
            )
            WHERE pv.volume IS NOT NULL
        ");

        // 3. Agregar cup_size_id al producto también para venta por peso
        //    Los productos 'weight' se sirven en vasitos también,
        //    solo que el precio es por kg, no por variante.
        // (No se necesita cambio extra aquí, ya maneja price_per_kg en products)
    }

    public function down(): void
    {
        Schema::table('product_variants', function (Blueprint $table) {
            $table->dropForeign(['cup_size_id']);
            $table->dropColumn(['cup_size_id', 'price_pos', 'price_delivery_app']);
        });
    }
};