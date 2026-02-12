<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomizationGroup extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'required',
        'multiple',
        'max_selections',
        'min_selections',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'required'       => 'boolean',
        'multiple'       => 'boolean',
        'is_active'      => 'boolean',
        'max_selections' => 'integer',
        'min_selections' => 'integer',
        'sort_order'     => 'integer',
    ];

    public function options()
    {
        return $this->hasMany(CustomizationOption::class)
                    ->orderBy('sort_order');
    }

    public function activeOptions()
    {
        return $this->hasMany(CustomizationOption::class)
                    ->where('is_active', true)
                    ->orderBy('sort_order');
    }

    public function products()
    {
        return $this->belongsToMany(Product::class, 'product_customization_groups')
                    ->withPivot('sort_order');
    }
}


class CustomizationOption extends Model
{
    use HasFactory;

    protected $fillable = [
        'customization_group_id',
        'name',
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
}


class OrderItemCustomization extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_item_id',
        'customization_option_id',
        'quantity',
        'price',
        'option_name',
    ];

    protected $casts = [
        'price'    => 'decimal:2',
        'quantity' => 'integer',
    ];

    public function orderItem()
    {
        return $this->belongsTo(OrderItem::class);
    }

    public function option()
    {
        return $this->belongsTo(CustomizationOption::class, 'customization_option_id');
    }

    public function getSubtotalAttribute(): float
    {
        return $this->price * $this->quantity;
    }

    public function getFormattedSubtotalAttribute(): string
    {
        if ($this->subtotal == 0) return 'Incluido';
        return number_format($this->subtotal, 0, ',', '.') . ' Gs';
    }
}