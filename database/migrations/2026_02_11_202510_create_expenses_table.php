<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('expenses', function (Blueprint $table) {
            $table->id();

            // Caja a la que pertenece este gasto
            $table->foreignId('cash_register_id')->nullable()->constrained()->onDelete('set null');

            // Empleado relacionado (para pagos de salario)
            $table->foreignId('employee_id')->nullable()->constrained()->onDelete('set null');

            // Usuario que registró el gasto
            $table->foreignId('registered_by')->constrained('users')->onDelete('cascade');

            // Tipo de gasto
            $table->enum('type', [
                'salary',       // Pago de salario a empleado
                'purchase',     // Compra de insumos/materia prima
                'inventory',    // Compra de stock (vasitos, ingredientes, etc.)
                'operational',  // Gastos operacionales (alquiler, luz, etc.)
                'other',        // Otros gastos
            ]);

            $table->string('description');
            $table->decimal('amount', 10, 2);

            // Método de pago del gasto
            $table->enum('payment_method', ['cash', 'transfer', 'card'])->default('cash');

            // Comprobante (foto del recibo/factura)
            $table->string('receipt_image')->nullable();

            // Período para pagos de salario (ej: "Enero 2025")
            $table->string('period')->nullable();

            $table->timestamp('expense_date');
            $table->text('notes')->nullable();

            $table->timestamps();

            $table->index('type');
            $table->index('expense_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('expenses');
    }
};