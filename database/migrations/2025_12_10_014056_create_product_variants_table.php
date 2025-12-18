<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_variants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->integer('volume'); // 300, 500, 700, 1000 ml
            $table->decimal('price', 10, 2); // Precio específico para este volumen
            $table->integer('stock')->default(0); // Stock específico por variante
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            // Un producto no puede tener volúmenes duplicados
            $table->unique(['product_id', 'volume']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_variants');
    }
};