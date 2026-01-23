<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'category_id',
        'description',
        'ingredients',
        'is_active',
        'image',
        'image_hash',
        'sale_type',      // 'unit', 'weight', 'both'
        'price_per_kg',   // Precio por kilo (para ventas por peso)
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'price_per_kg' => 'decimal:2',
    ];

    // ==========================================
    // RELACIONES
    // ==========================================

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function variants()
    {
        return $this->hasMany(ProductVariant::class);
    }

    public function activeVariants()
    {
        return $this->hasMany(ProductVariant::class)->where('is_active', true);
    }

    // ==========================================
    // SCOPES
    // ==========================================

    /**
     * Productos disponibles para e-commerce (solo unitarios o ambos)
     * NO incluye productos que son solo por peso
     */
    public function scopeForEcommerce($query)
    {
        return $query->where('is_active', true)
                     ->whereIn('sale_type', ['unit', 'both'])
                     ->whereHas('activeVariants');
    }

    /**
     * Productos disponibles para POS (todos los tipos)
     */
    public function scopeForPos($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Solo productos vendidos por peso
     */
    public function scopeByWeight($query)
    {
        return $query->whereIn('sale_type', ['weight', 'both']);
    }

    /**
     * Solo productos vendidos por unidad
     */
    public function scopeByUnit($query)
    {
        return $query->whereIn('sale_type', ['unit', 'both']);
    }

    // ==========================================
    // ACCESSORS
    // ==========================================

    public function getImageUrlAttribute()
    {
        if ($this->image_hash) {
            return route('product.image', $this->image_hash);
        }
        
        if ($this->image) {
            return Storage::disk('public')->url($this->image);
        }
        
        return null;
    }

    public function getMinPriceAttribute()
    {
        return $this->activeVariants()->min('price') ?? 0;
    }

    public function getMaxPriceAttribute()
    {
        return $this->activeVariants()->max('price') ?? 0;
    }

    /**
     * Verificar si el producto se puede vender por unidad
     */
    public function getCanSellByUnitAttribute(): bool
    {
        return in_array($this->sale_type, ['unit', 'both']);
    }

    /**
     * Verificar si el producto se puede vender por peso
     */
    public function getCanSellByWeightAttribute(): bool
    {
        return in_array($this->sale_type, ['weight', 'both']);
    }

    /**
     * Verificar si está disponible para e-commerce
     */
    public function getIsAvailableForEcommerceAttribute(): bool
    {
        return $this->is_active 
            && $this->can_sell_by_unit 
            && $this->activeVariants()->exists();
    }

    /**
     * Precio por kilo formateado
     */
    public function getFormattedPricePerKgAttribute(): string
    {
        if (!$this->price_per_kg) {
            return '';
        }
        
        return number_format($this->price_per_kg, 0, ',', '.') . ' Gs/kg';
    }

    // ==========================================
    // MÉTODOS
    // ==========================================

    /**
     * Calcular precio para una cantidad de peso
     * 
     * @param float $weightInKg Peso en kilogramos
     * @return float Precio total
     */
    public function calculateWeightPrice(float $weightInKg): float
    {
        if (!$this->can_sell_by_weight || !$this->price_per_kg) {
            return 0;
        }

        return round($this->price_per_kg * $weightInKg, 0);
    }

    /**
     * Obtener etiqueta del tipo de venta
     */
    public function getSaleTypeLabel(): string
    {
        return match($this->sale_type) {
            'unit' => 'Por unidad',
            'weight' => 'Por peso (kg)',
            'both' => 'Unidad y peso',
            default => 'Desconocido',
        };
    }
}