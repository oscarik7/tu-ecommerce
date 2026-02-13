<?php

namespace App\Livewire\Customer;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderItemCustomization;
use App\Models\PaymentMethod;
use Livewire\Component;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class Checkout extends Component
{
    public string $customer_name    = '';
    public string $customer_phone   = '';
    public string $customer_address = '';
    public string $delivery_type    = 'delivery';
    public        $payment_method_id = '';
    public string $notes            = '';
    public        $latitude         = null;
    public        $longitude        = null;

    const COMPANY_WHATSAPP = '595986150627';

    // ==========================================
    // LIFECYCLE
    // ==========================================

    public function mount(): void
    {
        if (!auth()->check()) {
            $this->redirect(route('login'));
            return;
        }

        if (auth()->user()->cartItems()->count() === 0) {
            $this->redirect(route('cart'));
            return;
        }

        $user = auth()->user();
        $this->customer_name    = $user->name ?? '';
        $this->customer_phone   = $user->phone ?? '';
        $this->customer_address = $user->address ?? '';
    }

    // ==========================================
    // VALIDACIÓN
    // ==========================================

    protected function rules(): array
    {
        return [
            'customer_name'     => 'required|string|max:255',
            'customer_phone'    => 'required|string',
            'customer_address'  => $this->delivery_type === 'delivery' ? 'required|string' : 'nullable',
            'delivery_type'     => 'required|in:delivery,pickup',
            'payment_method_id' => 'required|exists:payment_methods,id',
            'notes'             => 'nullable|string',
            'latitude'          => 'nullable|numeric',
            'longitude'         => 'nullable|numeric',
        ];
    }

    // ==========================================
    // HELPER: precio total de un item (base + extras) × cantidad
    // ==========================================

    private function calcItemTotal($item): float
    {
        $extras = collect($item->customizations ?? [])->sum('price');
        return ($item->variant->price + $extras) * $item->quantity;
    }

    // ==========================================
    // PROCESAR PEDIDO
    // ==========================================

    public function placeOrder(): void
    {
        $this->validate();

        $cartItems = auth()->user()
            ->cartItems()
            ->with(['product', 'variant.cupSize'])
            ->get();

        if ($cartItems->isEmpty()) {
            session()->flash('error', 'Tu carrito está vacío.');
            $this->redirect(route('cart'));
            return;
        }

        // Verificar stock
        foreach ($cartItems as $item) {
            if (!$item->variant->hasStock($item->quantity)) {
                session()->flash('error',
                    "Stock insuficiente para {$item->product->name} ({$item->variant->volume}ml). Revisá tu carrito."
                );
                $this->redirect(route('cart'));
                return;
            }
        }

        // ✅ Incluye precio base + extras de complementos
        $subtotal = $cartItems->sum(fn($item) => $this->calcItemTotal($item));
        $total    = $subtotal;

        try {
            DB::beginTransaction();

            $order = Order::create([
                'order_number'      => 'WEB-' . date('Ymd') . '-' . strtoupper(Str::random(5)),
                'user_id'           => auth()->id(),
                'delivery_zone_id'  => null,
                'payment_method_id' => $this->payment_method_id,
                'delivery_type'     => $this->delivery_type,
                'customer_name'     => mb_strtoupper($this->customer_name),
                'customer_phone'    => $this->customer_phone,
                'customer_address'  => $this->customer_address ?: null,
                'customer_city'     => 'Ciudad del Este',
                'latitude'          => $this->latitude ?: null,
                'longitude'         => $this->longitude ?: null,
                'subtotal'          => $subtotal,
                'delivery_cost'     => 0,
                'total'             => $total,
                'status'            => 'pending',
                'payment_status'    => 'pending',
                'source'            => 'web',
                'notes'             => $this->notes ?: null,
            ]);

            foreach ($cartItems as $item) {
                $customizations      = $item->customizations ?? [];
                $extrasUnit          = collect($customizations)->sum('price');
                $itemSubtotal        = ($item->variant->price + $extrasUnit) * $item->quantity;
                $customizationsExtra = $extrasUnit * $item->quantity;

                $orderItem = OrderItem::create([
                    'order_id'                => $order->id,
                    'product_id'              => $item->product_id,
                    'product_variant_id'      => $item->variant->id,
                    'product_name'            => $item->product->name,
                    'volume'                  => $item->variant->volume,
                    'price'                   => $item->variant->price,
                    'quantity'                => $item->quantity,
                    'subtotal'                => $itemSubtotal,
                    'customizations_subtotal' => $customizationsExtra,
                    'price_channel'           => 'web',
                ]);

                // Detalle de cada complemento
                foreach ($customizations as $c) {
                    if (class_exists(OrderItemCustomization::class)) {
                        OrderItemCustomization::create([
                            'order_item_id'           => $orderItem->id,
                            'customization_option_id' => $c['option_id'] ?? null,
                            'quantity'                => $item->quantity,
                            'price'                   => $c['price'],
                            'option_name'             => $c['name'],
                        ]);
                    }
                }

                $item->variant->decrementStock($item->quantity);
            }

            auth()->user()->cartItems()->delete();

            DB::commit();

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error placeOrder: ' . $e->getMessage());
            session()->flash('error', 'Error al procesar el pedido. Por favor intentá nuevamente.');
            return;
        }

        $whatsappUrl = $this->buildWhatsAppUrl($order, $cartItems);
        $this->dispatch('openWhatsAppNow', url: $whatsappUrl);
        session()->flash('order_created', $order->id);
    }

    // ==========================================
    // WHATSAPP — incluye desglose de complementos
    // ==========================================

    private function buildWhatsAppUrl(Order $order, $cartItems): string
    {
        $pm = PaymentMethod::find($this->payment_method_id);

        $msg  = "*NUEVO PEDIDO - Taskinho Açaí*\n";
        $msg .= "================================\n\n";
        $msg .= "*PEDIDO:* {$order->order_number}\n";
        $msg .= "*Fecha:* " . $order->created_at->format('d/m/Y H:i') . "\n\n";

        $msg .= "*CLIENTE*\n";
        $msg .= "Nombre: *{$order->customer_name}*\n";
        $msg .= "Tel: {$order->customer_phone}\n\n";

        if ($this->delivery_type === 'delivery') {
            $msg .= "*ENTREGA: DELIVERY*\n";
            $msg .= "Dirección: {$order->customer_address}\n";
            $msg .= "_Costo de envío a confirmar_\n\n";
        } else {
            $msg .= "*ENTREGA: RETIRO EN TIENDA*\n\n";
        }

        $msg .= "*PRODUCTOS*\n";
        foreach ($cartItems as $item) {
            $customizations = $item->customizations ?? [];
            $extrasUnit     = collect($customizations)->sum('price');
            $itemTotal      = ($item->variant->price + $extrasUnit) * $item->quantity;

            $msg .= "• {$item->product->name} {$item->variant->volume}ml × {$item->quantity}";
            $msg .= " = " . number_format($itemTotal, 0, ',', '.') . " Gs\n";

            if (!empty($customizations)) {
                $msg .= "  _Base: " . number_format($item->variant->price, 0, ',', '.') . " Gs_\n";
                foreach ($customizations as $c) {
                    $precioTexto = $c['price'] > 0
                        ? '+' . number_format($c['price'], 0, ',', '.') . ' Gs'
                        : 'incluido';
                    $msg .= "  _+ {$c['name']}: {$precioTexto}_\n";
                }
            }
        }

        $msg .= "\n*Subtotal:* " . number_format($order->subtotal, 0, ',', '.') . " Gs\n";

        if ($this->delivery_type === 'delivery') {
            $msg .= "*Total parcial:* " . number_format($order->total, 0, ',', '.') . " Gs _(+ delivery)_\n\n";
        } else {
            $msg .= "*TOTAL:* " . number_format($order->total, 0, ',', '.') . " Gs\n\n";
        }

        $msg .= "*PAGO:* {$pm->name}\n";

        if ($pm->bank_details) {
            $msg .= "Banco: {$pm->bank_details['bank']}\n";
            $msg .= "Cuenta: {$pm->bank_details['account_number']}\n";
            $msg .= "Titular: {$pm->bank_details['account_holder']}\n";
        }

        if ($pm->instructions) {
            $msg .= "\n_{$pm->instructions}_\n";
        }

        if ($order->notes) {
            $msg .= "\n*Notas:* {$order->notes}\n";
        }

        $msg .= "\n_Por favor confirmar recepción_ 🙏";

        return 'https://wa.me/' . self::COMPANY_WHATSAPP . '?text=' . urlencode($msg);
    }

    // ==========================================
    // RENDER
    // ==========================================

    public function render()
    {
        $cartItems = auth()->user()
            ->cartItems()
            ->with(['product', 'variant.cupSize'])
            ->get();

        // ✅ subtotal incluye base + extras de complementos
        $subtotal = $cartItems->sum(fn($item) => $this->calcItemTotal($item));

        $paymentMethods = PaymentMethod::where('is_active', true)->get();

        return view('livewire.customer.checkout', [
            'cartItems'      => $cartItems,
            'subtotal'       => $subtotal,
            'paymentMethods' => $paymentMethods,
            'deliveryCost'   => 0,
            'total'          => $subtotal,
        ])->layout('components.layouts.app');
    }
}