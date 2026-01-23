<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Setting extends Model
{
    protected $fillable = [
        'key',
        'value',
        'type', // string, boolean, integer, json
    ];

    /**
     * Obtener un setting por key
     */
    public static function get(string $key, $default = null)
    {
        return Cache::remember("setting_{$key}", 3600, function () use ($key, $default) {
            $setting = self::where('key', $key)->first();
            
            if (!$setting) {
                return $default;
            }

            return self::castValue($setting->value, $setting->type);
        });
    }

    /**
     * Guardar un setting
     */
    public static function set(string $key, $value, string $type = 'string'): void
    {
        self::updateOrCreate(
            ['key' => $key],
            ['value' => is_array($value) ? json_encode($value) : $value, 'type' => $type]
        );

        Cache::forget("setting_{$key}");
    }

    /**
     * Cast del valor según tipo
     */
    private static function castValue($value, $type)
    {
        return match ($type) {
            'boolean' => filter_var($value, FILTER_VALIDATE_BOOLEAN),
            'integer' => (int) $value,
            'json' => json_decode($value, true),
            default => $value,
        };
    }

    /**
     * Verificar si el modo mantenimiento está activo
     */
    public static function isMaintenanceMode(): bool
    {
        return self::get('maintenance_mode', false);
    }

    /**
     * Obtener mensaje de mantenimiento
     */
    public static function getMaintenanceMessage(): string
    {
        return self::get('maintenance_message', '¡Estamos trabajando en algo increíble! Volvemos pronto.');
    }

    /**
     * Obtener fecha estimada de regreso
     */
    public static function getMaintenanceDate(): ?string
    {
        return self::get('maintenance_date', null);
    }
}