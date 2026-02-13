<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Esta migración corrige la tabla order_item_customizations que ya existe
     * para hacer nullable el customization_option_id (por si se elimina la opción)
     */
    public function up(): void
    {
        Schema::table('order_item_customizations', function (Blueprint $table) {
            // Cambiar la foreign key para permitir null
            $table->dropForeign(['customization_option_id']);
            $table->foreignId('customization_option_id')
                ->nullable()
                ->change()
                ->constrained('customization_options')
                ->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('order_item_customizations', function (Blueprint $table) {
            $table->dropForeign(['customization_option_id']);
            $table->foreignId('customization_option_id')
                ->change()
                ->constrained('customization_options')
                ->onDelete('cascade');
        });
    }
};