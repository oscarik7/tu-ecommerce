<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CartItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'product_id',
        'product_variant_id',
        'quantity',
        'customizations', // ← AGREGADO
    ];

    protected $casts = [
        'quantity'       => 'integer',
        'customizations' => 'array', // ← AGREGADO: convierte JSON ↔ array automáticamente
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function variant()
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_id');
    }

    public function getSubtotalAttribute()
    {
        $extras = collect($this->customizations ?? [])->sum('price');
        return ($this->variant->price + $extras) * $this->quantity;
    }
}