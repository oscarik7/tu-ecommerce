<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cart_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            
            // Agregar product_variant_id
            $table->unsignedBigInteger('product_variant_id')->nullable();
            
            $table->integer('quantity')->default(1);
            $table->timestamps();
        });
        
        // Agregar foreign key y unique después
        Schema::table('cart_items', function (Blueprint $table) {
            $table->foreign('product_variant_id')
                ->references('id')
                ->on('product_variants')
                ->onDelete('cascade');
                
            // Un usuario no puede tener la misma variante duplicada en el carrito
            $table->unique(['user_id', 'product_id', 'product_variant_id'], 'cart_items_user_prod_var_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cart_items');
    }
};