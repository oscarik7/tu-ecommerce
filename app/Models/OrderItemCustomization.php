<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderItemCustomization extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_item_id',
        'customization_option_id',
        'quantity',
        'price',
        'option_name',  // nombre guardado para historial
    ];

    protected $casts = [
        'quantity' => 'integer',
        'price'    => 'decimal:2',
    ];

    public $timestamps = false; // No necesita created_at/updated_at

    // ==========================================
    // RELACIONES
    // ==========================================

    public function orderItem()
    {
        return $this->belongsTo(OrderItem::class, 'order_item_id');
    }

    public function customizationOption()
    {
        return $this->belongsTo(CustomizationOption::class, 'customization_option_id');
    }

    // ==========================================
    // ACCESSORS
    // ==========================================

    /**
     * Subtotal de esta personalización (precio × cantidad)
     */
    public function getSubtotalAttribute(): float
    {
        return $this->price * $this->quantity;
    }

    /**
     * Subtotal formateado
     */
    public function getFormattedSubtotalAttribute(): string
    {
        return number_format($this->subtotal, 0, ',', '.') . ' Gs';
    }
}