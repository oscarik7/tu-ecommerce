<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class ProductVariant extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'product_id',
        'cup_size_id',
        'volume',
        'price',              // Precio web/ecommerce (base)
        'price_pos',          // Precio tienda física
        'price_delivery_app', // Precio Pedidos Ya / apps
        'stock',              // LEGACY - ya no se usa para control real de stock
        'is_active',
        'visible_web',   // ← NUEVO
        'visible_pos',   // ← NUEVO
        'visible_app',
    ];

    protected $casts = [
        'price'              => 'decimal:2',
        'price_pos'          => 'decimal:2',
        'price_delivery_app' => 'decimal:2',
        'volume'             => 'integer',
        'stock'              => 'integer',
        'is_active'          => 'boolean',
        'visible_web'  => 'boolean',  // ← NUEVO
        'visible_pos'  => 'boolean',  // ← NUEVO
        'visible_app'  => 'boolean',  // ← NUEVO
    ];

    // ==========================================
    // RELACIONES
    // ==========================================

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function cupSize()
    {
        return $this->belongsTo(CupSize::class);
    }

    public function cartItems()
    {
        return $this->hasMany(CartItem::class);
    }

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }

    // ==========================================
    // ACCESSORS
    // ==========================================

    public function getFormattedVolumeAttribute(): string
    {
        if ($this->volume >= 1000) {
            $liters = $this->volume / 1000;
            return ($liters == intval($liters))
                ? intval($liters) . ' litro' . ($liters > 1 ? 's' : '')
                : number_format($liters, 1, ',', '.') . ' litros';
        }
        return $this->volume . 'ml';
    }

    /**
     * Obtener el precio según el canal de venta
     *
     * @param string $channel 'web' | 'pos' | 'delivery_app'
     */
    public function getPriceForChannel(string $channel): float
    {
        return match($channel) {
            'pos'          => $this->price_pos ?? $this->price,
            'delivery_app' => $this->price_delivery_app ?? $this->price,
            default        => $this->price, // 'web' y cualquier otro
        };
    }

    /**
     * Verificar si tiene precio específico para un canal
     */
    public function hasPriceForChannel(string $channel): bool
    {
        return match($channel) {
            'pos'          => $this->price_pos !== null,
            'delivery_app' => $this->price_delivery_app !== null,
            default        => true,
        };
    }

    // ==========================================
    // STOCK (delegado a CupSize)
    // ==========================================

    /**
     * Verificar si hay stock disponible (usa cup_size si está vinculado)
     */
    public function hasStock(int $quantity = 1): bool
    {
        if ($this->cup_size_id && $this->cupSize) {
            return $this->cupSize->hasStock($quantity);
        }
        // Fallback al stock legacy
        return $this->stock >= $quantity;
    }

    /**
     * Decrementar stock (usa cup_size si está vinculado)
     */
    public function decrementStock(int $quantity = 1): void
    {
        if ($this->cup_size_id && $this->cupSize) {
            $this->cupSize->decrementStock($quantity);
        } else {
            // Fallback al stock legacy
            $this->decrement('stock', $quantity);
        }
    }

    /**
     * Incrementar stock (para devoluciones/anulaciones)
     */
    public function incrementStock(int $quantity = 1): void
    {
        if ($this->cup_size_id && $this->cupSize) {
            $this->cupSize->incrementStock($quantity);
        } else {
            $this->increment('stock', $quantity);
        }
    }

    /**
     * Stock disponible actual
     */
    public function getAvailableStockAttribute(): int
    {
        if ($this->cup_size_id && $this->cupSize) {
            return $this->cupSize->stock;
        }
        return $this->stock;
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['stock', 'price', 'is_active'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->useLogName('inventario')
            ->setDescriptionForEvent(fn(string $event) => match($event) {
                'created' => 'Variante creada',
                'updated' => 'Stock/precio actualizado',
                'deleted' => 'Variante eliminada',
                default   => $event,
            });
    }
}
