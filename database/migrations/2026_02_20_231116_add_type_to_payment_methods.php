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
            if (!Schema::hasColumn('cash_registers', 'opening_amount_brl')) {
                $table->decimal('opening_amount_brl', 10, 2)->default(0)->after('opening_amount');
            }
            if (!Schema::hasColumn('cash_registers', 'closing_amount_brl')) {
                $table->decimal('closing_amount_brl', 10, 2)->nullable()->after('closing_amount');
            }
            if (!Schema::hasColumn('cash_registers', 'expected_amount_brl')) {
                $table->decimal('expected_amount_brl', 10, 2)->nullable()->after('expected_amount');
            }
            if (!Schema::hasColumn('cash_registers', 'difference_brl')) {
                $table->decimal('difference_brl', 10, 2)->nullable()->after('difference');
            }
            if (!Schema::hasColumn('cash_registers', 'total_sales_foreign')) {
                $table->decimal('total_sales_foreign', 10, 2)->nullable()->after('total_sales_transfer');
            }
        });

        // 2. Verificar que no exista ya el método antes de crearlo
        $existingMethod = DB::table('payment_methods')
            ->where('name', 'Efectivo (R$)')
            ->first();

        if (!$existingMethod) {
            DB::table('payment_methods')->insert([
                'name'        => 'Efectivo (R$)',
                'type'        => 'foreign_currency',
                'is_active'   => true,
                'description' => 'Pago en efectivo con reales brasileños',
                'created_at'  => now(),
                'updated_at'  => now(),
            ]);
        }

        // 3. Verificar que no exista ya el setting antes de crearlo
        $existingSetting = DB::table('settings')
            ->where('key', 'exchange_rate_brl')
            ->first();

        if (!$existingSetting) {
            DB::table('settings')->insert([
                'key'   => 'exchange_rate_brl',
                'value' => '3700',
                'type'  => 'integer',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        Schema::table('cash_registers', function (Blueprint $table) {
            $columns = [
                'opening_amount_brl',
                'closing_amount_brl',
                'expected_amount_brl',
                'difference_brl',
                'total_sales_foreign',
            ];
            
            foreach ($columns as $column) {
                if (Schema::hasColumn('cash_registers', $column)) {
                    $table->dropColumn($column);
                }
            }
        });

        DB::table('payment_methods')
            ->where('name', 'Efectivo (R$)')
            ->delete();

        DB::table('settings')
            ->where('key', 'exchange_rate_brl')
            ->delete();
    }
};