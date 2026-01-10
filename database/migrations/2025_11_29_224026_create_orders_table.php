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
            
            // Tipo de entrega
            $table->enum('delivery_type', ['delivery', 'pickup'])->default('delivery');
            
            // Información del cliente
            $table->string('customer_name');
            $table->string('customer_phone');
            $table->string('customer_email')->nullable(); // AGREGADO - Puede ser null
            $table->text('customer_address')->nullable(); // CAMBIADO A NULLABLE para ventas en tienda
            $table->string('customer_city')->nullable()->default('Ciudad del Este'); // CAMBIADO A NULLABLE
            
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
            $table->enum('payment_status', ['pending', 'paid', 'failed'])->default('pending'); // YA EXISTE
            $table->text('payment_proof')->nullable(); // Ruta del comprobante
            $table->text('notes')->nullable();
            
            // Timestamps de estados
            $table->timestamp('confirmed_at')->nullable(); // YA EXISTE
            $table->timestamp('delivered_at')->nullable(); // YA EXISTE
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};