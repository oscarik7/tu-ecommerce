<?php

namespace App\Livewire\Customer;

use App\Models\Order;
use Livewire\Component;

class MyOrders extends Component
{
    public $selectedOrder = null;

    public function showOrder($orderId)
    {
        $this->selectedOrder = Order::with(['items.product', 'deliveryZone', 'paymentMethod'])
            ->where('user_id', auth()->id())
            ->findOrFail($orderId);
    }

    public function closeModal()
    {
        $this->selectedOrder = null;
    }

    public function sendToWhatsApp($orderId)
    {
        $order = Order::with(['items.product', 'deliveryZone', 'paymentMethod'])
            ->findOrFail($orderId);
        
        // Número de WhatsApp del admin (configurable)
        $adminPhone = '595981000000'; // Cambia esto por el número real
        
        $message = $order->getWhatsAppMessage();
        $whatsappUrl = "https://wa.me/{$adminPhone}?text={$message}";
        
        return redirect()->away($whatsappUrl);
    }

    public function render()
    {
        $orders = Order::where('user_id', auth()->id())
            ->with(['items', 'deliveryZone', 'paymentMethod'])
            ->orderBy('created_at', 'desc')
            ->get();

        return view('livewire.customer.my-orders', [
            'orders' => $orders,
        ])->layout('components.layouts.app');
    }
}