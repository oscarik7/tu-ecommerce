<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('store_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->timestamps();
        });

        // Valores por defecto (los mismos que estaban hardcodeados)
        $defaults = [
            // Teléfono
            ['key' => 'phone_whatsapp',   'value' => '595986150627'],
            ['key' => 'phone_display',    'value' => '+595 986 150627'],

            // Horarios: JSON con días ISO (1=Lun … 7=Dom) y hora apertura/cierre
            // Lunes cerrado, Mar-Dom 13:00-21:00
            ['key' => 'schedule', 'value' => json_encode([
                1 => ['open' => false, 'from' => '13:00', 'to' => '21:00'], // Lunes
                2 => ['open' => true,  'from' => '13:00', 'to' => '21:00'], // Martes
                3 => ['open' => true,  'from' => '13:00', 'to' => '21:00'], // Miércoles
                4 => ['open' => true,  'from' => '13:00', 'to' => '21:00'], // Jueves
                5 => ['open' => true,  'from' => '13:00', 'to' => '21:00'], // Viernes
                6 => ['open' => true,  'from' => '13:00', 'to' => '21:00'], // Sábado
                7 => ['open' => true,  'from' => '13:00', 'to' => '21:00'], // Domingo
            ])],
        ];

        DB::table('store_settings')->insert(
            array_map(fn($row) => array_merge($row, [
                'created_at' => now(),
                'updated_at' => now(),
            ]), $defaults)
        );
    }

    public function down(): void
    {
        Schema::dropIfExists('store_settings');
    }
};