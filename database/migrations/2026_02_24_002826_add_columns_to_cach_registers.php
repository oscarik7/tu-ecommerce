<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cash_registers', function (Blueprint $table) {
            // Monto real en R$ recibido durante la jornada (antes solo se guardaba el equivalente en Gs)
            $table->decimal('total_sales_foreign_brl', 10, 2)->default(0)->after('total_sales_foreign')
                  ->comment('Monto total en R$ reales recibido (no equivalente en Gs)');

            // Total de ventas con pago dividido (para historial y análisis)
            $table->decimal('total_sales_split', 14, 2)->default(0)->after('total_sales_foreign_brl')
                  ->comment('Total en Gs de órdenes con pago dividido');

            // Cantidad de órdenes con pago dividido
            $table->unsignedInteger('total_orders_split')->default(0)->after('total_orders')
                  ->comment('Cantidad de órdenes con is_split_payment = true');

            // Egresos solo en efectivo (para el cálculo correcto del expected_amount)
            $table->decimal('total_expenses_cash', 14, 2)->default(0)->after('total_expenses')
                  ->comment('Egresos pagados en efectivo (los únicos que afectan el cajón Gs)');
        });
    }

    public function down(): void
    {
        Schema::table('cash_registers', function (Blueprint $table) {
            $table->dropColumn([
                'total_sales_foreign_brl',
                'total_sales_split',
                'total_orders_split',
                'total_expenses_cash',
            ]);
        });
    }
};