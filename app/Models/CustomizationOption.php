<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class CustomizationOption extends Model
{
    use HasFactory;

    protected $fillable = [
        'customization_group_id',
        'name',
        'image',
        'price',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'price'      => 'decimal:2',
        'is_active'  => 'boolean',
        'sort_order' => 'integer',
    ];

    public function group()
    {
        return $this->belongsTo(CustomizationGroup::class, 'customization_group_id');
    }

    public function orderItemCustomizations()
    {
        return $this->hasMany(OrderItemCustomization::class);
    }

    public function getIsExtraAttribute(): bool
    {
        return $this->price > 0;
    }

    public function getFormattedPriceAttribute(): string
    {
        if ($this->price == 0) return 'Incluido';
        return '+' . number_format($this->price, 0, ',', '.') . ' Gs';
    }

    /**
     * URL pública de la imagen o null si no tiene.
     */
    public function getImageUrlAttribute(): ?string
    {
        if (!$this->image) return null;
        return Storage::url($this->image);
    }
}