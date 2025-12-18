<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'category_id',
        'name',
        'slug',
        'description',
        'ingredients',
        'image',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

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

    // Obtener el precio mínimo de las variantes
    public function getMinPriceAttribute()
    {
        return $this->variants()->min('price') ?? 0;
    }

    // Obtener el precio máximo de las variantes
    public function getMaxPriceAttribute()
    {
        return $this->variants()->max('price') ?? 0;
    }

    // Verificar si hay stock disponible en alguna variante
    public function hasStock()
    {
        return $this->variants()->where('stock', '>', 0)->exists();
    }
}