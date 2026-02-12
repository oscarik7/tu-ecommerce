<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cart_items', function (Blueprint $table) {
            // Array JSON con las opciones seleccionadas por el cliente
            // Formato: [{"option_id": 1, "name": "Granola", "price": 0}, ...]
            $table->json('customizations')->nullable()->after('quantity');
        });
    }

    public function down(): void
    {
        Schema::table('cart_items', function (Blueprint $table) {
            $table->dropColumn('customizations');
        });
    }
};
