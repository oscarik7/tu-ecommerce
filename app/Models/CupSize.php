<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CupSize extends Model
{
    use HasFactory;

    protected $fillable = [
        'volume_ml',
        'name',
        'stock',
        'stock_min',
        'is_active',
    ];

    protected $casts = [
        'stock' => 'integer',
        'stock_min' => 'integer',
        'is_active' => 'boolean',
    ];

    // ==========================================
    // RELACIONES
    // ==========================================

    public function variants()
    {
        return $this->hasMany(ProductVariant::class);
    }

    // ==========================================
    // SCOPES
    // ==========================================

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeLowStock($query)
    {
        return $query->whereColumn('stock', '<=', 'stock_min');
    }

    // ==========================================
    // ACCESSORS
    // ==========================================

    public function getIsLowStockAttribute(): bool
    {
        return $this->stock <= $this->stock_min;
    }

    public function getFormattedStockAttribute(): string
    {
        return number_format($this->stock) . ' vasitos';
    }

    // ==========================================
    // MÉTODOS
    // ==========================================

    /**
     * Decrementar stock de vasitos.
     * Lanza excepción si no hay stock suficiente.
     */
    public function decrementStock(int $quantity = 1): void
    {
        if ($this->stock < $quantity) {
            throw new \Exception("Stock insuficiente de vasitos {$this->name}. Disponible: {$this->stock}");
        }
        $this->decrement('stock', $quantity);
    }

    /**
     * Incrementar stock (devolución o reposición)
     */
    public function incrementStock(int $quantity = 1): void
    {
        $this->increment('stock', $quantity);
    }

    /**
     * Verificar si hay stock para una cantidad
     */
    public function hasStock(int $quantity = 1): bool
    {
        return $this->stock >= $quantity;
    }

    /**
     * Obtener el cup_size por volumen en ml
     */
    public static function findByVolume(int $volumeMl): ?self
    {
        return self::where('volume_ml', $volumeMl)->first();
    }
}