<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payment_methods', function (Blueprint $table) {
            // JSON con los tipos de entrega permitidos: ['delivery', 'pickup']
            // null = sin restricción (compatible con todos)
            $table->json('allowed_delivery_types')->nullable()->after('is_active');
        });

        // Métodos existentes → sin restricción (null = todos permitidos)
        DB::table('payment_methods')->update(['allowed_delivery_types' => null]);
    }

    public function down(): void
    {
        Schema::table('payment_methods', function (Blueprint $table) {
            $table->dropColumn('allowed_delivery_types');
        });
    }
};