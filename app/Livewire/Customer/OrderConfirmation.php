<?php

namespace App\Livewire\Customer;

use App\Models\Order;
use Livewire\Component;

class OrderConfirmation extends Component
{
    public Order $order;

    const COMPANY_WHATSAPP = '595986150627';

    public function mount(int $orderId): void
    {
        if (!auth()->check()) {
            $this->redirect(route('login'));
            return;
        }

        // Solo puede ver su propio pedido
        $this->order = Order::with([
            'items.customizations',
            'paymentMethod',
            'deliveryZone',
        ])
        ->where('user_id', auth()->id())
        ->findOrFail($orderId);
    }

    public function sendWhatsApp(): void
    {
        $this->dispatch('openWhatsApp', url: $this->buildWhatsAppUrl());
    }

    private function buildWhatsAppUrl(): string
    {
        $order = $this->order;
        $pm    = $order->paymentMethod;

        $msg  = "*NUEVO PEDIDO - Taskinho Açaí*\n";
        $msg .= "================================\n\n";
        $msg .= "*PEDIDO:* {$order->order_number}\n";
        $msg .= "*Fecha:* " . $order->created_at->format('d/m/Y H:i') . "\n\n";

        $msg .= "*CLIENTE*\n";
        $msg .= "Nombre: *{$order->customer_name}*\n";
        $msg .= "Tel: {$order->customer_phone}\n\n";

        if ($order->delivery_type === 'delivery') {
            $msg .= "*ENTREGA: DELIVERY*\n";
            $msg .= "Dirección: {$order->customer_address}\n";
            $msg .= "_Costo de envío a confirmar_\n\n";
        } else {
            $msg .= "*ENTREGA: RETIRO EN TIENDA*\n\n";
        }

        $msg .= "*PRODUCTOS*\n";
        foreach ($order->items as $item) {
            $msg .= "• {$item->product_name}";
            if ($item->volume) $msg .= " {$item->volume}ml";
            $msg .= " × {$item->quantity}";
            $msg .= " = " . number_format($item->subtotal, 0, ',', '.') . " Gs\n";

            if ($item->customizations && $item->customizations->count() > 0) {
                foreach ($item->customizations as $c) {
                    $precio = $c->price > 0
                        ? '+' . number_format($c->price, 0, ',', '.') . ' Gs'
                        : 'incluido';
                    $msg .= "  _+ {$c->option_name}: {$precio}_\n";
                }
            }
        }

        $msg .= "\n*Subtotal:* " . number_format($order->subtotal, 0, ',', '.') . " Gs\n";

        if ($order->delivery_type === 'delivery') {
            $msg .= "*Total parcial:* " . number_format($order->total, 0, ',', '.') . " Gs _(+ delivery)_\n\n";
        } else {
            $msg .= "*TOTAL:* " . number_format($order->total, 0, ',', '.') . " Gs\n\n";
        }

        if ($pm) {
            $msg .= "*PAGO:* {$pm->name}\n";
            if ($pm->bank_details) {
                $msg .= "Banco: {$pm->bank_details['bank']}\n";
                $msg .= "Cuenta: {$pm->bank_details['account_number']}\n";
                $msg .= "Titular: {$pm->bank_details['account_holder']}\n";
            }
            if ($pm->instructions) {
                $msg .= "\n_{$pm->instructions}_\n";
            }
        }

        // Factura
        $user = auth()->user();
        if ($user->document) {
            $msg .= "\n*FACTURA REQUERIDA* 🧾\n";
            $tipoDoc = $user->document_type === 'ruc' ? 'RUC' : 'CI';
            $msg .= "{$tipoDoc}: {$user->document}\n";
            if ($user->document_type === 'ruc' && $user->company_name) {
                $msg .= "Razón Social: {$user->company_name}\n";
            }
        }

        if ($order->notes) {
            $msg .= "\n*Notas:* {$order->notes}\n";
        }

        $msg .= "\n_Por favor confirmar recepción_ 🙏";

        return 'https://wa.me/' . self::COMPANY_WHATSAPP . '?text=' . urlencode($msg);
    }

    public function render()
    {
        return view('livewire.customer.order-confirmation')
            ->layout('components.layouts.app');
    }
}