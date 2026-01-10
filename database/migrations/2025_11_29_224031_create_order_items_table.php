<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->onDelete('cascade');
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            
            // Columna para la variante (nullable porque puede no existir en pedidos antiguos)
            $table->unsignedBigInteger('product_variant_id')->nullable();
            
            $table->string('product_name'); // Guardamos el nombre por si se elimina el producto
            $table->integer('volume')->nullable(); // Volumen en ml (500, 1000, etc)
            $table->decimal('price', 10, 2); // Precio al momento de la compra
            $table->integer('quantity');
            $table->decimal('subtotal', 10, 2);
            $table->timestamps();
        });
        
        // Agregar la foreign key DESPUÉS de crear la tabla
        Schema::table('order_items', function (Blueprint $table) {
            $table->foreign('product_variant_id')
                ->references('id')
                ->on('product_variants')
                ->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_items');
    }
};