<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentMethod extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'type',
        'description',
        'instructions',
        'bank_details',
        'is_active',
        'allowed_delivery_types',
    ];

    protected $casts = [
        'bank_details'           => 'array',
        'is_active'              => 'boolean',
        'allowed_delivery_types' => 'array',
    ];

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    /**
     * Verifica si este método está disponible para un tipo de entrega dado.
     * null = sin restricción (disponible para todos)
     */
    public function isAvailableFor(string $deliveryType): bool
    {
        if (empty($this->allowed_delivery_types)) {
            return true;
        }

        return in_array($deliveryType, $this->allowed_delivery_types);
    }

    /**
     * Scope para filtrar por tipo de entrega.
     */
    public function scopeAvailableFor($query, string $deliveryType)
    {
        return $query->where(function ($q) use ($deliveryType) {
            $q->whereNull('allowed_delivery_types')
              ->orWhereJsonContains('allowed_delivery_types', $deliveryType);
        });
    }
}