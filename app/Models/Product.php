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
        'image_hash', // Nuevo campo
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    // Relaciones
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

    // Accessors
    public function getImageUrlAttribute()
    {
        if ($this->image_hash) {
            // URL con hash protegido
            return route('product.image', $this->image_hash);
        }
        
        if ($this->image) {
            // Fallback a la imagen directa
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
}