<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('product_variants', function (Blueprint $table) {
            $table->boolean('visible_web')->default(true)->after('is_active');
            $table->boolean('visible_pos')->default(true)->after('visible_web');
            $table->boolean('visible_app')->default(true)->after('visible_pos');
        });
    }

    public function down(): void
    {
        Schema::table('product_variants', function (Blueprint $table) {
            $table->dropColumn(['visible_web', 'visible_pos', 'visible_app']);
        });
    }
};
