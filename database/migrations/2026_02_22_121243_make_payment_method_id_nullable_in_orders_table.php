<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Obtener el nombre real de la foreign key
        $foreignKeys = DB::select("
            SELECT CONSTRAINT_NAME
            FROM information_schema.KEY_COLUMN_USAGE
            WHERE TABLE_SCHEMA = DATABASE()
            AND TABLE_NAME = 'orders'
            AND COLUMN_NAME = 'payment_method_id'
            AND REFERENCED_TABLE_NAME IS NOT NULL
        ");

        if (!empty($foreignKeys)) {
            $fkName = $foreignKeys[0]->CONSTRAINT_NAME;

            Schema::table('orders', function (Blueprint $table) use ($fkName) {
                // Usar el nombre real de la FK
                DB::statement("ALTER TABLE orders DROP FOREIGN KEY `{$fkName}`");
            });
        }

        Schema::table('orders', function (Blueprint $table) {
            // Hacer nullable
            $table->unsignedBigInteger('payment_method_id')->nullable()->change();

            // Re-agregar foreign key con onDelete('set null')
            $table->foreign('payment_method_id')
                  ->references('id')
                  ->on('payment_methods')
                  ->onDelete('set null');

            // Agregar is_split_payment solo si no existe
            if (!Schema::hasColumn('orders', 'is_split_payment')) {
                $table->boolean('is_split_payment')->default(false)->after('payment_method_id');
            }
        });
    }

    public function down(): void
    {
        // Obtener el nombre de la FK nuevamente
        $foreignKeys = DB::select("
            SELECT CONSTRAINT_NAME
            FROM information_schema.KEY_COLUMN_USAGE
            WHERE TABLE_SCHEMA = DATABASE()
            AND TABLE_NAME = 'orders'
            AND COLUMN_NAME = 'payment_method_id'
            AND REFERENCED_TABLE_NAME IS NOT NULL
        ");

        if (!empty($foreignKeys)) {
            $fkName = $foreignKeys[0]->CONSTRAINT_NAME;
            DB::statement("ALTER TABLE orders DROP FOREIGN KEY `{$fkName}`");
        }

        Schema::table('orders', function (Blueprint $table) {
            if (Schema::hasColumn('orders', 'is_split_payment')) {
                $table->dropColumn('is_split_payment');
            }
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->unsignedBigInteger('payment_method_id')->nullable(false)->change();
            $table->foreign('payment_method_id')->references('id')->on('payment_methods');
        });
    }
};
