<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Datos de facturación
            $table->string('document')->nullable()->after('phone');       // CI o RUC
            $table->enum('document_type', ['ci', 'ruc'])->default('ci')->after('document');
            $table->string('company_name')->nullable()->after('document_type'); // Para RUC
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['document', 'document_type', 'company_name']);
        });
    }
};