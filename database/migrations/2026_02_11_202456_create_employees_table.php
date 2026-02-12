<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employees', function (Blueprint $table) {
            $table->id();

            // Vinculado opcionalmente a un usuario del sistema
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');

            $table->string('name');
            $table->string('document')->nullable();       // CI
            $table->string('phone')->nullable();
            $table->string('position');                   // Cajero, Preparador, Repartidor, etc.
            $table->text('address')->nullable();

            // Salario
            $table->decimal('salary', 10, 2)->default(0);
            $table->enum('salary_type', ['fixed', 'hourly', 'commission'])->default('fixed');
            // fixed = salario mensual fijo
            // hourly = por hora
            // commission = porcentaje de ventas

            $table->date('hire_date');
            $table->date('termination_date')->nullable();

            $table->boolean('is_active')->default(true);
            $table->text('notes')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employees');
    }
};