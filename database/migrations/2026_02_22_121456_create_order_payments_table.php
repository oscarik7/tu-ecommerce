<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Crear tabla de pagos
        Schema::create('order_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->onDelete('cascade');
            $table->foreignId('payment_method_id')->constrained();
            $table->decimal('amount', 10, 2);
            $table->json('details')->nullable();
            $table->timestamps();
            
            $table->index('order_id');
        });

        // 2. Migrar datos existentes SOLO si hay orders con payment_method_id
        DB::statement('
            INSERT INTO order_payments (order_id, payment_method_id, amount, created_at, updated_at)
            SELECT id, payment_method_id, total, created_at, updated_at
            FROM orders
            WHERE payment_method_id IS NOT NULL
        ');
    }

    public function down(): void
    {
        Schema::dropIfExists('order_payments');
    }
};