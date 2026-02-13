<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'product_id',
        'product_variant_id',
        'product_name',
        'volume',
        'unit_type',                // 'unit' o 'weight'
        'weight',                   // Peso en kg (si es por peso)
        'price_per_kg',             // Precio por kg al momento de la venta
        'price',                    // Precio unitario o precio por kg
        'quantity',                 // Cantidad de unidades o kg (decimal para peso)
        'subtotal',                 // Subtotal total (incluye complementos)
        'customizations_subtotal',  // Subtotal solo de complementos
        'price_channel',            // Canal de precio usado ('pos' o 'delivery_app')
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'subtotal' => 'decimal:2',
        'customizations_subtotal' => 'decimal:2',
        'weight' => 'decimal:3',
        'price_per_kg' => 'decimal:2',
    ];

    // ==========================================
    // RELACIONES
    // ==========================================

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function variant()
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_id');
    }

    /**
     * Relación con las personalizaciones/complementos del item
     */
    public function customizations()
    {
        return $this->hasMany(OrderItemCustomization::class, 'order_item_id');
    }

    // ==========================================
    // ACCESSORS
    // ==========================================

    /**
     * Verificar si es un item por peso
     */
    public function getIsByWeightAttribute(): bool
    {
        return $this->unit_type === 'weight';
    }

    /**
     * Verificar si es un item por unidad
     */
    public function getIsByUnitAttribute(): bool
    {
        return $this->unit_type === 'unit' || empty($this->unit_type);
    }

    /**
     * Obtener descripción del item para tickets/facturas
     */
    public function getItemDescriptionAttribute(): string
    {
        if ($this->is_by_weight) {
            $weightFormatted = number_format($this->weight, 3, ',', '.');
            return "{$this->product_name} ({$weightFormatted} kg)";
        }

        if ($this->volume) {
            return "{$this->product_name} ({$this->volume}ml)";
        }

        return $this->product_name;
    }

    /**
     * Obtener cantidad formateada según tipo
     */
    public function getFormattedQuantityAttribute(): string
    {
        if ($this->is_by_weight) {
            return number_format($this->weight, 3, ',', '.') . ' kg';
        }

        return (int)$this->quantity . ' un.';
    }

    /**
     * Precio unitario formateado
     */
    public function getFormattedPriceAttribute(): string
    {
        if ($this->is_by_weight && $this->price_per_kg) {
            return number_format($this->price_per_kg, 0, ',', '.') . ' Gs/kg';
        }

        return number_format($this->price, 0, ',', '.') . ' Gs';
    }

    /**
     * Subtotal formateado
     */
    public function getFormattedSubtotalAttribute(): string
    {
        return number_format($this->subtotal, 0, ',', '.') . ' Gs';
    }

    // ==========================================
    // MÉTODOS ESTÁTICOS
    // ==========================================

    /**
     * Crear un item por unidad
     */
    public static function createUnitItem(array $data): self
    {
        return self::create([
            'order_id'                => $data['order_id'],
            'product_id'              => $data['product_id'],
            'product_variant_id'      => $data['product_variant_id'] ?? null,
            'product_name'            => $data['product_name'],
            'volume'                  => $data['volume'] ?? null,
            'unit_type'               => 'unit',
            'weight'                  => null,
            'price_per_kg'            => null,
            'price'                   => $data['price'],
            'quantity'                => $data['quantity'],
            'subtotal'                => $data['subtotal'],
            'customizations_subtotal' => $data['customizations_subtotal'] ?? 0,
            'price_channel'           => $data['price_channel'] ?? 'pos',
        ]);
    }

    /**
     * Crear un item por peso
     */
    public static function createWeightItem(array $data): self
    {
        // Si viene subtotal precalculado (ej: cliente pidió por monto), usarlo
        // Si no, calcular desde peso × precio_por_kg
        $subtotal = $data['subtotal'] ?? round($data['price_per_kg'] * $data['weight'], 0);

        return self::create([
            'order_id'                => $data['order_id'],
            'product_id'              => $data['product_id'],
            'product_variant_id'      => null,
            'product_name'            => $data['product_name'],
            'volume'                  => null,
            'unit_type'               => 'weight',
            'weight'                  => $data['weight'],
            'price_per_kg'            => $data['price_per_kg'],
            'price'                   => $data['price_per_kg'],
            'quantity'                => 1,
            'subtotal'                => $subtotal,
            'customizations_subtotal' => 0,
            'price_channel'           => $data['price_channel'] ?? 'pos',
        ]);
    }
}