<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Order extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'order_number',
        'user_id',
        'delivery_zone_id',
        'payment_method_id',
        'cash_register_id',
        'printed_by',
        'delivery_type',
        'source',
        // Delivery app
        'delivery_app_name',
        'delivery_app_order_id',
        'delivery_app_commission',
        // Cliente
        'customer_name',
        'customer_phone',
        'customer_email',
        'customer_address',
        'customer_city',
        'latitude',
        'longitude',
        // Montos
        'subtotal',
        'delivery_cost',
        'total',
        // Estado
        'status',
        'payment_status',
        'payment_proof',
        'notes',
        // Facturación
        'needs_invoice',
        'invoice_document',
        'invoice_company',
        // Timestamps
        'confirmed_at',
        'delivered_at',
    ];

    protected $casts = [
        'subtotal'                 => 'decimal:2',
        'delivery_cost'            => 'decimal:2',
        'total'                    => 'decimal:2',
        'delivery_app_commission'  => 'decimal:2',
        'latitude'                 => 'decimal:7',
        'longitude'                => 'decimal:7',
        'needs_invoice'            => 'boolean',
        'confirmed_at'             => 'datetime',
        'delivered_at'             => 'datetime',
    ];

    // ==========================================
    // CONSTANTES DE CANAL
    // ==========================================
    // source values:
    // 'web'          = ecommerce web
    // 'pos'          = tienda física POS
    // 'delivery_app' = Pedidos Ya, ingresado manualmente
    // 'manual'       = venta manual con precio especial

    // ==========================================
    // RELACIONES
    // ==========================================

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

    public function cashRegister()
    {
        return $this->belongsTo(CashRegister::class);
    }

    public function printedBy()
    {
        return $this->belongsTo(User::class, 'printed_by');
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    // ==========================================
    // SCOPES
    // ==========================================

    public function scopeFromWeb($query)
    {
        return $query->where('source', 'web');
    }

    public function scopeFromPos($query)
    {
        return $query->where('source', 'pos');
    }

    public function scopeFromDeliveryApp($query)
    {
        return $query->where('source', 'delivery_app');
    }

    public function scopeActive($query)
    {
        return $query->whereIn('status', ['pending', 'confirmed', 'preparing', 'ready', 'delivering']);
    }

    public function scopeToday($query)
    {
        return $query->whereDate('created_at', today());
    }

    // ==========================================
    // ACCESSORS
    // ==========================================

    public function getSourceLabelAttribute(): string
    {
        return match($this->source) {
            'web'          => '🌐 Web',
            'pos'          => '🏪 Tienda',
            'delivery_app' => '🛵 ' . ($this->delivery_app_name ?? 'App Delivery'),
            'manual'       => '📝 Manual',
            default        => $this->source,
        };
    }

    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            'pending'   => 'Pendiente',
            'confirmed' => 'Confirmado',
            'preparing' => 'Preparando',
            'ready'     => 'Listo',
            'delivering'=> 'En Camino',
            'delivered' => 'Entregado',
            'cancelled' => 'Cancelado',
            default     => $this->status,
        };
    }

    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            'pending'    => 'yellow',
            'confirmed'  => 'blue',
            'preparing'  => 'indigo',
            'ready'      => 'purple',
            'delivering' => 'orange',
            'delivered'  => 'green',
            'cancelled'  => 'red',
            default      => 'gray',
        };
    }

    public function getIsFromDeliveryAppAttribute(): bool
    {
        return $this->source === 'delivery_app';
    }

    /**
     * Total real del cliente (incluyendo comisión de la app si aplica)
     */
    public function getAppNetAmountAttribute(): float
    {
        if (!$this->is_from_delivery_app || !$this->delivery_app_commission) {
            return $this->total;
        }
        return $this->total - $this->delivery_app_commission;
    }

    // ==========================================
    // WHATSAPP (mantiene funcionalidad existente)
    // ==========================================

    public function getWhatsAppMessage(): string
    {
        $message = "🛍️ *NUEVO PEDIDO #{$this->order_number}*\n\n";
        $message .= "👤 *Cliente:* {$this->customer_name}\n";

        if ($this->customer_phone) {
            $message .= "📞 *Teléfono:* {$this->customer_phone}\n";
        }

        if ($this->is_from_delivery_app) {
            $message .= "🛵 *Canal:* {$this->delivery_app_name}\n";
            if ($this->delivery_app_order_id) {
                $message .= "🔢 *ID App:* {$this->delivery_app_order_id}\n";
            }
        }

        $message .= "\n📦 *Productos:*\n";
        foreach ($this->items as $item) {
            $message .= "• {$item->item_description} x{$item->formatted_quantity} - {$item->formatted_subtotal}\n";
        }

        $message .= "\n💰 *Subtotal:* " . number_format($this->subtotal, 0, ',', '.') . " Gs\n";

        if ($this->delivery_type === 'delivery') {
            $message .= "🚚 *Delivery:* " . number_format($this->delivery_cost, 0, ',', '.') . " Gs\n";
        }

        $message .= "💵 *TOTAL: " . number_format($this->total, 0, ',', '.') . " Gs*\n";
        $message .= "💳 *Pago:* {$this->paymentMethod->name}\n";

        if ($this->notes) {
            $message .= "\n📝 *Notas:* {$this->notes}\n";
        }

        return urlencode($message);
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['status', 'payment_status', 'total', 'delivery_type', 'customer_name'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->useLogName('pedidos')
            ->setDescriptionForEvent(fn(string $event) => match($event) {
                'created' => 'Pedido creado',
                'updated' => 'Pedido actualizado',
                'deleted' => 'Pedido eliminado',
                default   => $event,
            });
    }
}
