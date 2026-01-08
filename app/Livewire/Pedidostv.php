<?php

namespace App\Livewire;

use App\Models\Order;
use Livewire\Component;
use Livewire\Attributes\On;

class Pedidostv extends Component
{
    public $orders = [];
    public $filterStatus = 'pending';
    public $refreshInterval = 120; // segundos

    public function mount()
    {
        $this->loadOrders();
    }

    public function loadOrders()
    {
        $this->orders = Order::with(['items.product', 'deliveryZone', 'paymentMethod'])
            ->when($this->filterStatus, function ($query) {
                $query->where('status', $this->filterStatus);
            })
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get()
            ->toArray();
    }

    #[On('orderUpdated')]
    public function refreshOrders()
    {
        $this->loadOrders();
    }

    public function setFilter($status)
    {
        $this->filterStatus = $status;
        $this->loadOrders();
    }

    public function getStatusBadgeColor($status)
    {
        return match($status) {
            'pending' => 'bg-yellow-500',
            'confirmed' => 'bg-blue-500',
            'preparing' => 'bg-purple-500',
            'ready' => 'bg-green-500',
            'in_delivery' => 'bg-indigo-500',
            'delivered' => 'bg-gray-500',
            'cancelled' => 'bg-red-500',
            default => 'bg-gray-400',
        };
    }

    public function getStatusLabel($status)
    {
        return match($status) {
            'pending' => 'Pendiente',
            'confirmed' => 'Confirmado',
            'preparing' => 'Preparando',
            'ready' => 'Listo',
            'in_delivery' => 'En Camino',
            'delivered' => 'Entregado',
            'cancelled' => 'Cancelado',
            default => $status,
        };
    }

    public function render()
    {
        return view('livewire.pedidostv')->layout('components.layouts.provider');
    }
}