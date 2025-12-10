<?php

namespace App\Livewire\Customer;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\DeliveryZone;
use App\Models\PaymentMethod;
use Livewire\Component;
use Illuminate\Support\Str;

class Checkout extends Component
{
    public $customer_name;
    public $customer_phone;
    public $customer_address;
    public $delivery_type = 'delivery';
    public $delivery_zone_id;
    public $payment_method_id;
    public $notes;
    public $latitude;
    public $longitude;

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
            'delivery_zone_id' => $this->delivery_type == 'delivery' ? 'required|exists:delivery_zones,id' : 'nullable',
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

        $deliveryCost = 0;
        if ($this->delivery_type == 'delivery' && $this->delivery_zone_id) {
            $zone = DeliveryZone::find($this->delivery_zone_id);
            $deliveryCost = $zone ? $zone->delivery_cost : 0;
        }

        $total = $subtotal + $deliveryCost;

        // Crear orden
        $order = Order::create([
            'order_number' => 'ORD-' . strtoupper(Str::random(8)),
            'user_id' => auth()->id(),
            'delivery_zone_id' => $this->delivery_type == 'delivery' ? $this->delivery_zone_id : null,
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

        // Limpiar carrito
        auth()->user()->cartItems()->delete();

        session()->flash('order_created', $order->id);
        return redirect()->route('my-orders');
    }

    public function render()
    {
        $cartItems = auth()->user()->cartItems()->with(['product', 'variant'])->get();
        
        $subtotal = $cartItems->sum(function ($item) {
            return $item->variant->price * $item->quantity;
        });

        $deliveryZones = DeliveryZone::where('is_active', true)->get();
        $paymentMethods = PaymentMethod::where('is_active', true)->get();

        $deliveryCost = 0;
        if ($this->delivery_type == 'delivery' && $this->delivery_zone_id) {
            $zone = DeliveryZone::find($this->delivery_zone_id);
            $deliveryCost = $zone ? $zone->delivery_cost : 0;
        }

        $total = $subtotal + $deliveryCost;

        return view('livewire.customer.checkout', [
            'cartItems' => $cartItems,
            'subtotal' => $subtotal,
            'deliveryZones' => $deliveryZones,
            'paymentMethods' => $paymentMethods,
            'deliveryCost' => $deliveryCost,
            'total' => $total,
        ])->layout('components.layouts.app');
    }
}