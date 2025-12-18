<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cart_items', function (Blueprint $table) {
            // 1. ELIMINAR las llaves foráneas primero para liberar el índice
            $table->dropForeign(['user_id']);
            $table->dropForeign(['product_id']);

            // 2. AHORA sí podemos borrar el índice único sin errores
            $table->dropUnique(['user_id', 'product_id']);

            // 3. AGREGAR la nueva columna
            $table->foreignId('product_variant_id')->nullable()->after('product_id')->constrained()->onDelete('cascade');

            // 4. RESTAURAR las llaves foráneas de las columnas originales
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');

            // 5. CREAR el nuevo índice único (incluyendo la variante)
            $table->unique(['user_id', 'product_id', 'product_variant_id'], 'cart_items_user_prod_var_unique');
        });
    }

    public function down(): void
    {
        Schema::table('cart_items', function (Blueprint $table) {
            // Seguimos la misma lógica inversa para el rollback
            $table->dropUnique('cart_items_user_prod_var_unique');
            $table->dropForeign(['product_variant_id']);
            $table->dropColumn('product_variant_id');
            
            // Restaurar el estado original
            $table->unique(['user_id', 'product_id']);
        });
    }
};