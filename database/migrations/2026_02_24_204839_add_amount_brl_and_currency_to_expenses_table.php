<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('expenses', function (Blueprint $table) {
            // Monto en reales brasileños (cuando el egreso se paga en BRL)
            $table->decimal('amount_brl', 10, 2)->nullable()->default(0)->after('amount');
            // Moneda principal del egreso: 'gs' o 'brl'
            $table->string('currency', 3)->default('gs')->after('amount_brl');
        });
    }

    public function down(): void
    {
        Schema::table('expenses', function (Blueprint $table) {
            $table->dropColumn(['amount_brl', 'currency']);
        });
    }
};