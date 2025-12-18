<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            // Añadimos la columna 'volume' después de 'price', permitiendo nulos.
            $table->integer('volume')->nullable()->after('price')->comment('Capacidad en ml');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('volume');
        });
    }
};