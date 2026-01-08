<?php

namespace App\Livewire\Admin;

use App\Models\Order;
use Livewire\Component;
use Livewire\WithPagination;

class Orders extends Component
{
    use WithPagination;

    public $selectedOrder = null;
    public $filterStatus = '';
    public $search = '';

    public function updateStatus($orderId, $status)
    {
        $order = Order::findOrFail($orderId);
        $order->update(['status' => $status]);
        
        if ($status === 'confirmed') {
            $order->update(['confirmed_at' => now()]);
        } elseif ($status === 'delivered') {
            $order->update(['delivered_at' => now()]);
        }
        
        session()->flash('message', 'Estado del pedido actualizado.');
    }

    public function showOrder($orderId)
    {
        $this->selectedOrder = Order::with(['user', 'items.product', 'deliveryZone', 'paymentMethod'])
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
        
        $adminPhone = '595975621886'; // Cambia esto
        $message = $order->getWhatsAppMessage();
        $whatsappUrl = "https://wa.me/{$adminPhone}?text={$message}";
        
        return redirect()->away($whatsappUrl);
    }

    public function render()
    {
        $orders = Order::with(['user', 'items', 'deliveryZone', 'paymentMethod'])
            ->when($this->filterStatus, function ($query) {
                $query->where('status', $this->filterStatus);
            })
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('order_number', 'like', '%' . $this->search . '%')
                      ->orWhere('customer_name', 'like', '%' . $this->search . '%')
                      ->orWhere('customer_phone', 'like', '%' . $this->search . '%');
                });
            })
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('livewire.admin.orders', [
            'orders' => $orders,
        ])->layout('components.layouts.admin', ['title' => 'Gestión de Pedidos']);
    }
}