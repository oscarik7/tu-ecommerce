<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payment_methods', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Ej: Transferencia Bancaria
            $table->string('type'); // bank_transfer, cash, etc
            $table->text('description')->nullable();
            $table->text('instructions')->nullable(); // Instrucciones para el pago
            $table->json('bank_details')->nullable(); // Datos bancarios en JSON
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_methods');
    }
};