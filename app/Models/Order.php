<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_number',
        'user_id',
        'delivery_zone_id',
        'payment_method_id',
        'delivery_type',
        'customer_name',
        'customer_phone',
        'customer_email',      // AGREGADO
        'customer_address',
        'customer_city',
        'latitude',
        'longitude',
        'subtotal',
        'delivery_cost',
        'total',
        'status',
        'payment_status',      // AGREGADO
        'payment_proof',
        'notes',
        'confirmed_at',        // AGREGADO
        'delivered_at',        // AGREGADO
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'delivery_cost' => 'decimal:2',
        'total' => 'decimal:2',
        'latitude' => 'decimal:7',
        'longitude' => 'decimal:7',
        'confirmed_at' => 'datetime',
        'delivered_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function deliveryZone()
    {
        return $this->belongsTo(DeliveryZone::class);
    }

    public function paymentMethod()
    {
        return $this->belongsTo(PaymentMethod::class);
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function getWhatsAppMessage()
    {
        $message = "🛍️ *NUEVO PEDIDO #{$this->order_number}*\n\n";
        $message .= "👤 *Cliente:* {$this->customer_name}\n";
        $message .= "📞 *Teléfono:* {$this->customer_phone}\n\n";
        
        $message .= "📦 *Productos:*\n";
        foreach ($this->items as $item) {
            $volumeText = $item->volume ? " ({$item->volume}ml)" : "";
            $message .= "• {$item->quantity}x {$item->product_name}{$volumeText} - " . number_format($item->subtotal, 0, ',', '.') . " Gs.\n";
        }
        
        $message .= "\n💰 *Subtotal:* " . number_format($this->subtotal, 0, ',', '.') . " Gs.\n";
        
        if ($this->delivery_type === 'delivery') {
            $message .= "🚚 *Tipo:* Delivery\n";
            $message .= "📍 *Dirección:* {$this->customer_address}\n";
            $message .= "🏙️ *Ciudad:* {$this->customer_city}\n";
            if ($this->deliveryZone) {
                $message .= "📌 *Zona:* {$this->deliveryZone->name}\n";
            }
            $message .= "🛵 *Costo delivery:* " . number_format($this->delivery_cost, 0, ',', '.') . " Gs.\n";
            
            if ($this->latitude && $this->longitude) {
                $message .= "\n📍 *Ubicación en Google Maps:*\n";
                $message .= "https://www.google.com/maps?q={$this->latitude},{$this->longitude}\n";
            }
        } else {
            $message .= "🏪 *Tipo:* Retiro en tienda\n";
        }
        
        $message .= "\n💵 *Total:* " . number_format($this->total, 0, ',', '.') . " Gs.\n";
        $message .= "💳 *Método de pago:* {$this->paymentMethod->name}\n";
        
        if ($this->notes) {
            $message .= "\n📝 *Notas:* {$this->notes}\n";
        }
        
        return urlencode($message);
    }
}