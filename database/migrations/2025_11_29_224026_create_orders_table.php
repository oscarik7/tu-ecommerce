<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_number')->unique();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('delivery_zone_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('payment_method_id')->constrained()->onDelete('cascade');
            
            // Información del cliente
            $table->string('customer_name');
            $table->string('customer_phone');
            $table->text('customer_address');
            $table->string('customer_city')->default('Ciudad del Este');
            
            // Montos
            $table->decimal('subtotal', 10, 2);
            $table->decimal('delivery_cost', 10, 2)->default(0);
            $table->decimal('total', 10, 2);
            
            // Estado del pedido
            $table->enum('status', [
                'pending',      // Pendiente
                'confirmed',    // Confirmado
                'preparing',    // Preparando
                'ready',        // Listo para entregar
                'delivering',   // En camino
                'delivered',    // Entregado
                'cancelled'     // Cancelado
            ])->default('pending');
            
            // Información de pago
            $table->enum('payment_status', ['pending', 'paid', 'failed'])->default('pending');
            $table->text('payment_proof')->nullable(); // Ruta del comprobante
            $table->text('notes')->nullable();
            
            $table->timestamp('confirmed_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};