<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Agregar columnas para reales en cash_registers
        Schema::table('cash_registers', function (Blueprint $table) {
            $table->decimal('opening_amount_brl', 10, 2)->default(0)->after('opening_amount');
            $table->decimal('closing_amount_brl', 10, 2)->nullable()->after('closing_amount');
            $table->decimal('expected_amount_brl', 10, 2)->nullable()->after('expected_amount');
            $table->decimal('difference_brl', 10, 2)->nullable()->after('difference');
            $table->decimal('total_sales_foreign', 10, 2)->nullable()->after('total_sales_transfer');
        });

        // 2. Agregar método de pago para reales
        DB::table('payment_methods')->insert([
            'name'        => 'Efectivo (R$)',
            'type'        => 'foreign_currency',
            'is_active'   => true,
            'description' => 'Pago en efectivo con reales brasileños',
            'created_at'  => now(),
            'updated_at'  => now(),
        ]);

        // 3. Agregar cotización BRL a settings
        DB::table('settings')->insert([
            'key'   => 'exchange_rate_brl',
            'value' => '3700',
            'type'  => 'integer',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        Schema::table('cash_registers', function (Blueprint $table) {
            $table->dropColumn([
                'opening_amount_brl',
                'closing_amount_brl',
                'expected_amount_brl',
                'difference_brl',
                'total_sales_foreign',
            ]);
        });

        DB::table('payment_methods')
            ->where('name', 'Efectivo (R$)')
            ->delete();

        DB::table('settings')
            ->where('key', 'exchange_rate_brl')
            ->delete();
    }
};