<?php

namespace App\Livewire\Customer;

use App\Models\Order;
use Livewire\Component;

class MyOrders extends Component
{
    // Solo ID — nunca el modelo completo como propiedad pública
    public ?int $selectedOrderId = null;

    const COMPANY_WHATSAPP = '595986150627';

    // ==========================================
    // MODAL DETALLE
    // ==========================================

    public function showOrder(int $orderId): void
    {
        // Verificar que pertenece al usuario antes de asignar
        $exists = Order::where('id', $orderId)
            ->where('user_id', auth()->id())
            ->exists();

        if ($exists) {
            $this->selectedOrderId = $orderId;
        }
    }

    public function closeModal(): void
    {
        $this->selectedOrderId = null;
    }

    // ==========================================
    // WHATSAPP
    // ==========================================

    public function sendToWhatsApp(int $orderId): void
    {
        $order = Order::with(['items', 'deliveryZone', 'paymentMethod'])
            ->where('user_id', auth()->id())
            ->findOrFail($orderId);

        $url = $this->buildWhatsAppUrl($order);
        $this->dispatch('openWhatsApp', url: $url);
    }

    private function buildWhatsAppUrl(Order $order): string
    {
        $pm = $order->paymentMethod;

        $msg  = "*PEDIDO - Taskinho Açaí*\n";
        $msg .= "================================\n\n";
        $msg .= "*Nro:* {$order->order_number}\n";
        $msg .= "*Fecha:* " . $order->created_at->format('d/m/Y H:i') . "\n";
        $msg .= "*Estado:* " . $this->statusLabel($order->status) . "\n\n";

        $msg .= "*CLIENTE*\n";
        $msg .= "Nombre: *{$order->customer_name}*\n";
        $msg .= "Tel: {$order->customer_phone}\n\n";

        if ($order->delivery_type === 'delivery') {
            $msg .= "*ENTREGA: DELIVERY*\n";
            $msg .= "Dirección: {$order->customer_address}\n";
            if ($order->deliveryZone) $msg .= "Zona: {$order->deliveryZone->name}\n";
            $msg .= $order->delivery_cost > 0
                ? "Costo: " . number_format($order->delivery_cost, 0, ',', '.') . " Gs\n\n"
                : "_Costo a confirmar_\n\n";
        } else {
            $msg .= "*ENTREGA: RETIRO EN TIENDA*\n\n";
        }

        $msg .= "*PRODUCTOS*\n";
        foreach ($order->items as $item) {
            $msg .= "• {$item->product_name} {$item->volume}ml × {$item->quantity}";
            $msg .= " = " . number_format($item->subtotal, 0, ',', '.') . " Gs\n";
        }

        $msg .= "\n*Subtotal:* " . number_format($order->subtotal, 0, ',', '.') . " Gs\n";
        $msg .= "*TOTAL:* " . number_format($order->total, 0, ',', '.') . " Gs\n\n";

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

        if ($order->notes) {
            $msg .= "\n*Notas:* {$order->notes}\n";
        }

        $suffix = $order->status === 'pending'
            ? "_Por favor confirmar recepción del pedido_"
            : "_Consulta sobre estado del pedido_";
        $msg .= "\n{$suffix}";

        return 'https://wa.me/' . self::COMPANY_WHATSAPP . '?text=' . urlencode($msg);
    }

    private function statusLabel(string $status): string
    {
        return [
            'pending'    => 'Pendiente',
            'confirmed'  => 'Confirmado',
            'preparing'  => 'Preparando',
            'ready'      => 'Listo',
            'delivering' => 'En camino',
            'delivered'  => 'Entregado',
            'cancelled'  => 'Cancelado',
        ][$status] ?? $status;
    }

    // ==========================================
    // RENDER
    // ==========================================

    public function render()
    {
        $orders = Order::where('user_id', auth()->id())
            ->with(['items', 'deliveryZone', 'paymentMethod'])
            ->orderByDesc('created_at')
            ->get();

        // Resolver modelo fresco desde el ID para el modal
        $selectedOrder = $this->selectedOrderId
            ? Order::with(['items', 'deliveryZone', 'paymentMethod'])
                ->where('user_id', auth()->id())
                ->find($this->selectedOrderId)
            : null;

        return view('livewire.customer.my-orders', [
            'orders'        => $orders,
            'selectedOrder' => $selectedOrder,
        ])->layout('components.layouts.app');
    }
}
