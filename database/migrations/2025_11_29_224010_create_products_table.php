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
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            // ❌ NO DEBE TENER ESTAS LÍNEAS:
            // $table->decimal('price', 10, 2);
            // $table->integer('volume')->nullable();
            // $table->integer('stock')->default(0);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};