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
            
            // Columna para la variante (nullable para ventas por peso o pedidos antiguos)
            $table->unsignedBigInteger('product_variant_id')->nullable();
            
            $table->string('product_name'); // Guardamos el nombre por si se elimina el producto
            $table->integer('volume')->nullable(); // Volumen en ml (300, 500, 700, 1000)
            
            // === CAMPOS PARA VENTA POR PESO ===
            // Tipo de unidad: 'unit' (unidades) o 'weight' (por peso/kg)
            $table->enum('unit_type', ['unit', 'weight'])->default('unit');
            
            // Peso en kg (solo si unit_type es 'weight')
            // Ej: 0.345 kg = 345 gramos
            $table->decimal('weight', 10, 3)->nullable();
            
            // Precio por kg al momento de la venta (para referencia histórica)
            $table->decimal('price_per_kg', 10, 2)->nullable();
            // === FIN CAMPOS PESO ===
            
            $table->decimal('price', 10, 2); // Precio unitario o precio por kg
            $table->integer('quantity'); // Cantidad de unidades (para weight siempre es 1)
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