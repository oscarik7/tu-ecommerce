<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cart_items', function (Blueprint $table) {
            $table->foreignId('product_variant_id')->nullable()->after('product_id')->constrained()->onDelete('cascade');
            
            // Eliminar el unique anterior y crear uno nuevo que incluya la variante
            $table->dropUnique(['user_id', 'product_id']);
            $table->unique(['user_id', 'product_id', 'product_variant_id']);
        });
    }

    public function down(): void
    {
        Schema::table('cart_items', function (Blueprint $table) {
            $table->dropUnique(['user_id', 'product_id', 'product_variant_id']);
            $table->dropForeign(['product_variant_id']);
            $table->dropColumn('product_variant_id');
            $table->unique(['user_id', 'product_id']);
        });
    }
};