<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cup_sizes', function (Blueprint $table) {
            $table->id();
            $table->integer('volume_ml')->unique(); // 300, 400, 500, 700, 1000, 1500
            $table->string('name');                 // "300ml", "Litro y Medio", etc.
            $table->integer('stock')->default(0);   // Stock COMPARTIDO entre todos los productos
            $table->integer('stock_min')->default(10); // Alerta de stock mínimo
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Insertar los tamaños base
        DB::table('cup_sizes')->insert([
            ['volume_ml' => 300,  'name' => '300ml',          'stock' => 0, 'stock_min' => 10, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['volume_ml' => 400,  'name' => '400ml',          'stock' => 0, 'stock_min' => 10, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['volume_ml' => 500,  'name' => '500ml',          'stock' => 0, 'stock_min' => 10, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['volume_ml' => 700,  'name' => '700ml',          'stock' => 0, 'stock_min' => 10, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['volume_ml' => 1000, 'name' => '1 Litro',        'stock' => 0, 'stock_min' => 5,  'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['volume_ml' => 1500, 'name' => 'Litro y Medio',  'stock' => 0, 'stock_min' => 5,  'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('cup_sizes');
    }
};