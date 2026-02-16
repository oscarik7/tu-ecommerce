<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Agregar configuraciones de tienda a la tabla settings existente
        $rows = [
            [
                'key'   => 'phone_whatsapp',
                'value' => '595986150627',
                'type'  => 'string',
            ],
            [
                'key'   => 'phone_display',
                'value' => '+595 986 150627',
                'type'  => 'string',
            ],
            [
                'key'   => 'schedule',
                'value' => json_encode([
                    1 => ['open' => false, 'from' => '13:00', 'to' => '21:00'],
                    2 => ['open' => true,  'from' => '13:00', 'to' => '21:00'],
                    3 => ['open' => true,  'from' => '13:00', 'to' => '21:00'],
                    4 => ['open' => true,  'from' => '13:00', 'to' => '21:00'],
                    5 => ['open' => true,  'from' => '13:00', 'to' => '21:00'],
                    6 => ['open' => true,  'from' => '13:00', 'to' => '21:00'],
                    7 => ['open' => true,  'from' => '13:00', 'to' => '21:00'],
                ]),
                'type'  => 'json',
            ],
        ];

        foreach ($rows as $row) {
            // insertOrIgnore para no fallar si ya existen por alguna razón
            DB::table('settings')->insertOrIgnore(array_merge($row, [
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }
    }

    public function down(): void
    {
        DB::table('settings')->whereIn('key', [
            'phone_whatsapp',
            'phone_display',
            'schedule',
        ])->delete();
    }
};  