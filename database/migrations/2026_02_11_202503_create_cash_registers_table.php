<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cash_registers', function (Blueprint $table) {
            $table->id();

            // Usuario que abre/cierra la caja (cajero)
            $table->foreignId('opened_by')->constrained('users')->onDelete('cascade');
            $table->foreignId('closed_by')->nullable()->constrained('users')->onDelete('set null');

            // Empleado asociado (opcional, para vincular turnos)
            $table->foreignId('employee_id')->nullable()->constrained()->onDelete('set null');

            // Montos
            $table->decimal('opening_amount', 10, 2);           // Monto con el que se abre la caja
            $table->decimal('closing_amount', 10, 2)->nullable(); // Monto contado al cerrar
            $table->decimal('expected_amount', 10, 2)->nullable(); // Calculado: apertura + ventas efectivo - gastos efectivo
            $table->decimal('difference', 10, 2)->nullable();     // closing - expected (negativo = falta, positivo = sobra)

            // Resumen de ventas (calculado al cerrar)
            $table->decimal('total_sales', 10, 2)->default(0);
            $table->decimal('total_sales_cash', 10, 2)->default(0);    // Solo efectivo
            $table->decimal('total_sales_card', 10, 2)->default(0);    // Tarjeta
            $table->decimal('total_sales_transfer', 10, 2)->default(0); // Transferencia/QR
            $table->decimal('total_expenses', 10, 2)->default(0);      // Egresos registrados
            $table->integer('total_orders')->default(0);               // Cantidad de ventas

            $table->timestamp('opened_at');
            $table->timestamp('closed_at')->nullable();

            $table->text('opening_notes')->nullable();
            $table->text('closing_notes')->nullable();

            $table->enum('status', ['open', 'closed'])->default('open');

            $table->timestamps();

            // Solo puede haber una caja abierta por usuario a la vez
            $table->index(['opened_by', 'status']);
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cash_registers');
    }
};