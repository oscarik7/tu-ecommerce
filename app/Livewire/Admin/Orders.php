<?php

namespace App\Livewire\Admin;

use App\Models\Order;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithPagination;

class Orders extends Component
{
    use WithPagination;

    public $selectedOrder = null;
    
    // Filtros
    public $filterStatus = '';
    public $filterSource = '';      // 'web', 'pos', '' (todos)
    public $filterDateFrom = '';
    public $filterDateTo = '';
    public $search = '';
    
    // Vista: 'active' (pedidos activos) o 'all' (historial completo)
    public $viewMode = 'active';

    // Número de WhatsApp de la empresa
    const COMPANY_WHATSAPP = '595975621886';

    public function mount()
    {
        // Por defecto mostrar pedidos activos
        $this->viewMode = 'active';
    }

    /**
     * Cambiar modo de vista
     */
    public function setViewMode($mode)
    {
        $this->viewMode = $mode;
        $this->resetPage();
    }

    public function updateStatus($orderId, $status)
    {
        $order = Order::findOrFail($orderId);
        
        // Si se está cancelando, usar el método específico
        if ($status === 'cancelled') {
            $this->cancelOrder($orderId);
            return;
        }
        
        $order->update(['status' => $status]);
        
        if ($status === 'confirmed') {
            $order->update(['confirmed_at' => now()]);
        } elseif ($status === 'delivered') {
            $order->update(['delivered_at' => now()]);
        }
        
        session()->flash('message', 'Estado actualizado correctamente.');
    }

    /**
     * Anular/Cancelar una venta
     * Devuelve el stock de los productos
     */
    public function cancelOrder($orderId, $reason = null)
    {
        $order = Order::with('items')->findOrFail($orderId);
        
        // Verificar que no esté ya cancelada
        if ($order->status === 'cancelled') {
            session()->flash('error', 'Esta venta ya está cancelada.');
            return;
        }
        
        try {
            DB::beginTransaction();
            
            // Devolver stock de cada item
            foreach ($order->items as $item) {
                if ($item->product_variant_id) {
                    $variant = \App\Models\ProductVariant::find($item->product_variant_id);
                    if ($variant) {
                        $variant->increment('stock', $item->quantity);
                    }
                }
            }
            
            // Actualizar estado de la orden
            $order->update([
                'status' => 'cancelled',
                'payment_status' => 'failed',
                'notes' => $order->notes 
                    ? $order->notes . "\n[ANULADA] " . now()->format('d/m/Y H:i') . ($reason ? ": $reason" : '')
                    : "[ANULADA] " . now()->format('d/m/Y H:i') . ($reason ? ": $reason" : ''),
            ]);
            
            DB::commit();
            
            session()->flash('message', 'Venta #' . $order->order_number . ' anulada. Stock devuelto.');
            
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Error al anular la venta: ' . $e->getMessage());
        }
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

    /**
     * Abrir vista previa del ticket para imprimir
     */
    public function printTicket($orderId)
    {
        $this->dispatch('openPrintPreview', orderId: $orderId);
    }

    /**
     * Actualizar el costo de delivery
     */
    public function updateDeliveryCost($orderId, $cost)
    {
        $order = Order::findOrFail($orderId);
        
        $cost = floatval($cost);
        $order->delivery_cost = $cost;
        $order->total = $order->subtotal + $cost;
        $order->save();
        
        session()->flash('message', 'Costo de delivery actualizado correctamente.');
    }

    /**
     * Enviar detalles del pedido al CLIENTE por WhatsApp
     */
    public function sendToCustomer($orderId)
    {
        $order = Order::with(['items', 'paymentMethod', 'deliveryZone'])->findOrFail($orderId);
        
        // Verificar que tenga teléfono
        if (empty($order->customer_phone)) {
            session()->flash('error', 'Este pedido no tiene teléfono de cliente.');
            return;
        }
        
        $customerPhone = preg_replace('/[^0-9]/', '', $order->customer_phone);
        if (substr($customerPhone, 0, 3) !== '595') {
            $customerPhone = '595' . $customerPhone;
        }
        
        $message = $this->generateCustomerWhatsAppMessage($order);
        $whatsappUrl = "https://wa.me/{$customerPhone}?text=" . urlencode($message);
        
        $this->dispatch('openWhatsApp', url: $whatsappUrl);
    }

    /**
     * Enviar a WhatsApp de la empresa
     */
    public function sendToWhatsApp($orderId)
    {
        $order = Order::with(['items.product', 'deliveryZone', 'paymentMethod'])
            ->findOrFail($orderId);
        
        $message = $this->generateAdminWhatsAppMessage($order);
        $whatsappUrl = "https://wa.me/" . self::COMPANY_WHATSAPP . "?text=" . urlencode($message);
        
        $this->dispatch('openWhatsApp', url: $whatsappUrl);
    }

    /**
     * Limpiar todos los filtros
     */
    public function clearFilters()
    {
        $this->filterStatus = '';
        $this->filterSource = '';
        $this->filterDateFrom = '';
        $this->filterDateTo = '';
        $this->search = '';
        $this->resetPage();
    }

    /**
     * Generar mensaje de WhatsApp para el cliente
     */
    private function generateCustomerWhatsAppMessage($order)
    {
        $statusMessages = [
            'pending' => 'Pendiente',
            'confirmed' => 'Confirmado',
            'preparing' => 'En Preparación',
            'ready' => 'Listo para Entrega',
            'delivering' => 'En Camino',
            'delivered' => 'Entregado',
            'cancelled' => 'Cancelado'
        ];

        $message = "*ESTADO DE TU PEDIDO - Taskinho Açaí*\n";
        $message .= "================================\n\n";
        $message .= "Hola *{$order->customer_name}*!\n\n";
        $message .= "Estado actual de tu pedido *#{$order->order_number}*:\n";
        $message .= "*{$statusMessages[$order->status]}*\n\n";
        
        switch ($order->status) {
            case 'confirmed':
                $message .= "Tu pedido ha sido confirmado!\n";
                $message .= "Estamos preparando tu pedido con mucho cuidado.\n";
                break;
            case 'preparing':
                $message .= "Tu pedido está siendo preparado!\n";
                break;
            case 'ready':
                $message .= "Tu pedido está listo!\n";
                if ($order->delivery_type == 'pickup') {
                    $message .= "Puedes pasar a retirar tu pedido.\n";
                } else {
                    $message .= "Pronto será enviado a tu domicilio.\n";
                }
                break;
            case 'delivering':
                $message .= "Tu pedido está en camino!\n";
                break;
            case 'delivered':
                $message .= "Tu pedido ha sido entregado!\n";
                $message .= "Gracias por tu compra!\n";
                break;
            case 'pending':
                $message .= "Hemos recibido tu pedido.\n";
                $message .= "Pronto lo confirmaremos!\n";
                break;
            case 'cancelled':
                $message .= "Tu pedido ha sido cancelado.\n";
                break;
        }
        
        $message .= "\n================================\n";
        $message .= "Total: " . number_format($order->total, 0, ',', '.') . " Gs\n";
        $message .= "\nGracias por tu preferencia!\n";
        $message .= "Taskinho Açaí - +595 975 621 886\n";
        
        return $message;
    }

    /**
     * Generar mensaje de WhatsApp para el admin
     */
    private function generateAdminWhatsAppMessage($order)
    {
        $message = "*DETALLES DEL PEDIDO*\n";
        $message .= "================================\n\n";
        $message .= "Pedido: *#{$order->order_number}*\n";
        $message .= "Origen: " . ($order->source === 'pos' ? 'TIENDA' : 'WEB') . "\n";
        $message .= "Fecha: " . $order->created_at->format('d/m/Y H:i') . "\n\n";
        
        $message .= "*CLIENTE*\n";
        $message .= "Nombre: {$order->customer_name}\n";
        if ($order->customer_phone) {
            $message .= "Teléfono: {$order->customer_phone}\n";
        }
        
        $message .= "\n*PRODUCTOS*\n";
        foreach ($order->items as $item) {
            $message .= "- {$item->quantity}x {$item->product_name}";
            if ($item->volume) {
                $message .= " ({$item->volume}ml)";
            }
            $message .= "\n";
        }
        
        $message .= "\n*TOTAL: " . number_format($order->total, 0, ',', '.') . " Gs*\n";
        $message .= "Pago: {$order->paymentMethod->name}\n";
        
        return $message;
    }

    /**
     * Obtener estadísticas rápidas
     * Excluye ventas canceladas
     */
    public function getStatsProperty()
    {
        // Base query: hoy y NO canceladas
        $baseQuery = Order::whereDate('created_at', today())
            ->where('status', '!=', 'cancelled');
        
        return [
            'today_total' => (clone $baseQuery)->sum('total'),
            'today_count' => (clone $baseQuery)->count(),
            'today_pos' => (clone $baseQuery)->where('source', 'pos')->count(),
            'today_web' => (clone $baseQuery)->where('source', 'web')->count(),
            'pending_count' => Order::where('status', 'pending')->count(),
            'cancelled_today' => Order::whereDate('created_at', today())
                ->where('status', 'cancelled')->count(),
        ];
    }

    public function render()
    {
        $query = Order::with(['user', 'items', 'deliveryZone', 'paymentMethod']);
        
        // Filtro por modo de vista
        if ($this->viewMode === 'active') {
            // Solo pedidos activos (no entregados ni cancelados)
            $query->whereIn('status', ['pending', 'confirmed', 'preparing', 'ready', 'delivering']);
        }
        
        // Filtro por estado específico
        if ($this->filterStatus) {
            $query->where('status', $this->filterStatus);
        }
        
        // Filtro por origen (web/pos)
        if ($this->filterSource) {
            $query->where('source', $this->filterSource);
        }
        
        // Filtro por fecha desde
        if ($this->filterDateFrom) {
            $query->whereDate('created_at', '>=', $this->filterDateFrom);
        }
        
        // Filtro por fecha hasta
        if ($this->filterDateTo) {
            $query->whereDate('created_at', '<=', $this->filterDateTo);
        }
        
        // Búsqueda
        if ($this->search) {
            $query->where(function ($q) {
                $q->where('order_number', 'like', '%' . $this->search . '%')
                  ->orWhere('customer_name', 'like', '%' . $this->search . '%')
                  ->orWhere('customer_phone', 'like', '%' . $this->search . '%');
            });
        }
        
        $orders = $query->orderBy('created_at', 'desc')->paginate(20);

        return view('livewire.admin.orders', [
            'orders' => $orders,
            'stats' => $this->stats,
        ])->layout('components.layouts.admin', ['title' => 'Gestión de Pedidos']);
    }
}