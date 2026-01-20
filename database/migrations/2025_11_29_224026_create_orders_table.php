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
            
            // User nullable para ventas POS sin cliente
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
            
            $table->foreignId('delivery_zone_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('payment_method_id')->constrained()->onDelete('cascade');
            
            // Tipo de entrega
            $table->enum('delivery_type', ['delivery', 'pickup'])->default('delivery');
            
            // Origen de la venta
            $table->string('source')->default('web'); // 'web' = e-commerce, 'pos' = punto de venta
            
            // Información del cliente (siempre se guarda, aunque user_id sea null)
            $table->string('customer_name'); // Obligatorio - para el ticket
            $table->string('customer_phone')->nullable(); // Opcional en POS
            $table->string('customer_email')->nullable();
            $table->text('customer_address')->nullable();
            $table->string('customer_city')->nullable()->default('Ciudad del Este');
            
            // Coordenadas para delivery
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            
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
            $table->text('payment_proof')->nullable();
            $table->text('notes')->nullable();
            
            // Timestamps de estados
            $table->timestamp('confirmed_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamps();
            
            // Índices para búsquedas comunes
            $table->index('source');
            $table->index('status');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};