<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->text('ingredients')->nullable();
            $table->string('image')->nullable();
            $table->string('image_hash', 64)->nullable();
            $table->boolean('is_active')->default(true);
            
            // === CAMPOS PARA TIPO DE VENTA ===
            // 'unit' = solo por unidad (variantes) - disponible en Web + POS
            // 'weight' = solo por peso/kg - disponible SOLO en POS
            // 'both' = ambos modos disponibles
            $table->enum('sale_type', ['unit', 'weight', 'both'])->default('unit');
            
            // Precio por kilogramo (solo aplica si sale_type es 'weight' o 'both')
            $table->decimal('price_per_kg', 10, 2)->nullable();
            // === FIN CAMPOS TIPO VENTA ===
            
            $table->timestamps();
            
            // Índices
            $table->index('sale_type');
            $table->index('image_hash');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};