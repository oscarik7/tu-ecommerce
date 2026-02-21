<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class StoreSetting extends Model
{
    // ← apunta a la tabla settings que ya existe
    protected $table = 'settings';

    protected $fillable = ['key', 'value', 'type'];

    // ── Leer un valor (con caché 10 min) ──────────────────
    public static function get(string $key, mixed $default = null): mixed
    {
        return Cache::remember("store_setting:{$key}", 600, function () use ($key, $default) {
            $row = static::where('key', $key)->first();
            return $row ? $row->value : $default;
        });
    }

    // ── Escribir y limpiar caché ───────────────────────────
    public static function set(string $key, mixed $value, string $type = 'string'): void
    {
        static::updateOrCreate(
            ['key' => $key],
            ['value' => $value, 'type' => $type]
        );
        Cache::forget("store_setting:{$key}");
    }

    // ── Horario decodificado ───────────────────────────────
    // Retorna array [1 => ['open'=>bool,'from'=>'HH:MM','to'=>'HH:MM'], ...]
    public static function schedule(): array
    {
        $raw = static::get('schedule');
        if (!$raw) return [];

        $decoded = json_decode($raw, true);

        // Asegurar que las claves sean enteros
        $result = [];
        foreach ($decoded as $day => $config) {
            $result[(int) $day] = $config;
        }
        return $result;
    }

    /**
     * Obtener la cotización BRL actual
     */
    public static function exchangeRateBrl(): float
    {
        return (float) static::get('exchange_rate_brl', 3700);
    }

}