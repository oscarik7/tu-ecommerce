<?php

namespace App\Livewire\Customer;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\PaymentMethod;
use Livewire\Component;
use Illuminate\Support\Str;

class Checkout extends Component
{
    public $customer_name;
    public $customer_phone;
    public $customer_address;
    public $delivery_type = 'delivery';
    public $payment_method_id;
    public $notes;
    public $latitude;
    public $longitude;

    // Número de WhatsApp de la empresa
    const COMPANY_WHATSAPP = '595975621886';

    public function mount()
    {
        // Verificar que el usuario esté autenticado
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        // Verificar que el carrito no esté vacío
        if (auth()->user()->cartItems()->count() === 0) {
            return redirect()->route('cart');
        }

        $user = auth()->user();
        $this->customer_name = $user->name;
        $this->customer_phone = $user->phone;
        $this->customer_address = $user->address ?? '';
    }

    protected function rules()
    {
        return [
            'customer_name' => 'required|string|max:255',
            'customer_phone' => 'required|string',
            'customer_address' => $this->delivery_type == 'delivery' ? 'required|string' : 'nullable',
            'delivery_type' => 'required|in:delivery,pickup',
            'payment_method_id' => 'required|exists:payment_methods,id',
            'notes' => 'nullable|string',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
        ];
    }

    public function placeOrder()
    {
        $this->validate();

        $cartItems = auth()->user()->cartItems()->with(['product', 'variant'])->get();

        if ($cartItems->isEmpty()) {
            session()->flash('error', 'Tu carrito está vacío.');
            return redirect()->route('cart');
        }

        // Verificar stock de todas las variantes
        foreach ($cartItems as $item) {
            if ($item->quantity > $item->variant->stock) {
                session()->flash('error', "No hay suficiente stock de {$item->product->name} ({$item->variant->volume}ml)");
                return redirect()->route('cart');
            }
        }

        $subtotal = $cartItems->sum(function ($item) {
            return $item->variant->price * $item->quantity;
        });

        // El costo de delivery será determinado por la tienda
        $deliveryCost = 0;

        $total = $subtotal + $deliveryCost;

        // Crear orden
        $order = Order::create([
            'order_number' => 'ORD-' . strtoupper(Str::random(8)),
            'user_id' => auth()->id(),
            'delivery_zone_id' => null,
            'payment_method_id' => $this->payment_method_id,
            'delivery_type' => $this->delivery_type,
            'customer_name' => $this->customer_name,
            'customer_phone' => $this->customer_phone,
            'customer_address' => $this->customer_address,
            'customer_city' => 'Ciudad del Este',
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'subtotal' => $subtotal,
            'delivery_cost' => $deliveryCost,
            'total' => $total,
            'status' => 'pending',
            'payment_status' => 'pending',
            'notes' => $this->notes,
        ]);

        // Crear items de la orden y reducir stock
        foreach ($cartItems as $item) {
            OrderItem::create([
                'order_id' => $order->id,
                'product_id' => $item->product_id,
                'product_variant_id' => $item->variant->id,
                'product_name' => $item->product->name,
                'volume' => $item->variant->volume,
                'price' => $item->variant->price,
                'quantity' => $item->quantity,
                'subtotal' => $item->variant->price * $item->quantity,
            ]);

            // Reducir stock de la variante
            $item->variant->decrement('stock', $item->quantity);
        }

        // Generar URL de WhatsApp
        $whatsappUrl = $this->generateWhatsAppMessage($order, $cartItems);

        // Limpiar carrito
        auth()->user()->cartItems()->delete();

        // Disparar evento para abrir WhatsApp INMEDIATAMENTE
        $this->dispatch('openWhatsAppNow', url: $whatsappUrl);

        session()->flash('order_created', $order->id);
        session()->flash('success', '¡Pedido confirmado! Se abrirá WhatsApp para enviar tu pedido.');
        
        // NO redirigir, quedarse en la misma página para que se abra WhatsApp
        return;
    }

    /**
     * Generar mensaje detallado de WhatsApp SIN EMOJIS
     */
    private function generateWhatsAppMessage($order, $cartItems)
    {
        $paymentMethod = PaymentMethod::find($this->payment_method_id);
        
        // Construir el mensaje SIN EMOJIS
        $message = "*NUEVO PEDIDO - ACAI STORE*\n";
        $message .= "================================\n\n";
        
        // Información del pedido
        $message .= "*DATOS DEL PEDIDO*\n";
        $message .= "Nro Pedido: *{$order->order_number}*\n";
        $message .= "Fecha: " . $order->created_at->format('d/m/Y H:i') . "\n";
        $message .= "Estado: Pendiente\n\n";
        
        // Información del cliente
        $message .= "*DATOS DEL CLIENTE*\n";
        $message .= "Nombre: *{$order->customer_name}*\n";
        $message .= "Telefono: {$order->customer_phone}\n\n";
        
        // Tipo de entrega
        $message .= "*TIPO DE ENTREGA*\n";
        if ($order->delivery_type == 'delivery') {
            $message .= "Modalidad: *DELIVERY*\n";
            $message .= "Direccion: {$order->customer_address}\n";
            $message .= "Ciudad: {$order->customer_city}\n";
            $message .= "_Costo de envio por confirmar segun ubicacion_\n\n";
        } else {
            $message .= "Modalidad: *RETIRO EN TIENDA*\n";
            $message .= "Direccion Tienda: Av. Principal 123, Ciudad del Este\n";
            $message .= "Horario: Lunes a Sabado 9:00 - 20:00\n\n";
        }
        
        // Detalle de productos
        $message .= "*PRODUCTOS*\n";
        $message .= "================================\n\n";
        
        foreach ($cartItems as $item) {
            $itemTotal = $item->variant->price * $item->quantity;
            $message .= "*{$item->product->name}*\n";
            $message .= "- Tamano: {$item->variant->volume}ml\n";
            $message .= "- Cantidad: {$item->quantity}\n";
            $message .= "- Precio Unit: " . number_format($item->variant->price, 0, ',', '.') . " Gs\n";
            $message .= "- Subtotal: *" . number_format($itemTotal, 0, ',', '.') . " Gs*\n\n";
        }
        
        $message .= "================================\n\n";
        
        // Resumen de costos
        $message .= "*RESUMEN DE COSTOS*\n";
        $message .= "Subtotal: " . number_format($order->subtotal, 0, ',', '.') . " Gs\n";
        
        if ($order->delivery_type == 'delivery') {
            $message .= "Delivery: _A confirmar_\n";
            $message .= "*TOTAL (PARCIAL): " . number_format($order->total, 0, ',', '.') . " Gs*\n\n";
            $message .= "_Total final incluira costo de delivery_\n\n";
        } else {
            $message .= "Delivery: GRATIS\n";
            $message .= "*TOTAL: " . number_format($order->total, 0, ',', '.') . " Gs*\n\n";
        }
        
        // Método de pago
        $message .= "*METODO DE PAGO*\n";
        $message .= "- {$paymentMethod->name}\n";
        if ($paymentMethod->description) {
            $message .= "- {$paymentMethod->description}\n";
        }
        
        // Datos bancarios si existen
        if ($paymentMethod->bank_details) {
            $message .= "\n*DATOS BANCARIOS*\n";
            $message .= "Banco: {$paymentMethod->bank_details['bank']}\n";
            $message .= "Cuenta: {$paymentMethod->bank_details['account_number']}\n";
            $message .= "Titular: {$paymentMethod->bank_details['account_holder']}\n";
        }
        
        // Instrucciones de pago
        if ($paymentMethod->instructions) {
            $message .= "\n*INSTRUCCIONES*\n";
            $message .= "{$paymentMethod->instructions}\n";
        }
        
        // Notas adicionales
        if ($order->notes) {
            $message .= "\n*NOTAS DEL CLIENTE*\n";
            $message .= "{$order->notes}\n";
        }
        
        $message .= "\n================================\n";
        $message .= "_Por favor confirmar recepcion del pedido_\n";
        $message .= "_Te contactaremos pronto para coordinar la entrega_\n";
        
        // Generar la URL de WhatsApp
        $whatsappUrl = "https://wa.me/" . self::COMPANY_WHATSAPP . "?text=" . urlencode($message);
        
        return $whatsappUrl;
    }

    public function render()
    {
        $cartItems = auth()->user()->cartItems()->with(['product', 'variant'])->get();
        
        $subtotal = $cartItems->sum(function ($item) {
            return $item->variant->price * $item->quantity;
        });

        $paymentMethods = PaymentMethod::where('is_active', true)->get();

        // El costo de delivery será determinado por la tienda
        $deliveryCost = 0;
        $total = $subtotal + $deliveryCost;

        return view('livewire.customer.checkout', [
            'cartItems' => $cartItems,
            'subtotal' => $subtotal,
            'paymentMethods' => $paymentMethods,
            'deliveryCost' => $deliveryCost,
            'total' => $total,
        ])->layout('components.layouts.app');
    }
}