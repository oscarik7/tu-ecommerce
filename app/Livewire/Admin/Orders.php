<?php

namespace App\Livewire\Admin;

use App\Models\Order;
use App\Models\ProductVariant;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithPagination;

class Orders extends Component
{
    use WithPagination;

    public $selectedOrder = null;

    // Filtros
    public $filterStatus   = '';
    public $filterSource   = '';   // 'web' | 'pos' | 'delivery_app' | ''
    public $filterDateFrom = '';
    public $filterDateTo   = '';
    public $search         = '';

    // Vista
    public $viewMode = 'active';   // 'active' | 'all'

    const COMPANY_WHATSAPP = '595975621886';

    // ==========================================
    // VISTA
    // ==========================================

    public function mount(): void
    {
        $this->viewMode = 'active';
    }

    public function setViewMode(string $mode): void
    {
        $this->viewMode = $mode;
        $this->resetPage();
    }

    // ==========================================
    // ESTADOS
    // ==========================================

    public function updateStatus(int $orderId, string $status): void
    {
        if ($status === 'cancelled') {
            $this->cancelOrder($orderId);
            return;
        }

        $order  = Order::findOrFail($orderId);
        $update = ['status' => $status];

        if ($status === 'confirmed') $update['confirmed_at'] = now();
        if ($status === 'delivered') $update['delivered_at'] = now();

        $order->update($update);
        session()->flash('message', 'Estado actualizado correctamente.');
    }

    // ==========================================
    // ANULAR
    // ==========================================

    public function cancelOrder(int $orderId, ?string $reason = null): void
    {
        $order = Order::with('items.variant.cupSize')->findOrFail($orderId);

        if ($order->status === 'cancelled') {
            session()->flash('error', 'Esta venta ya está cancelada.');
            return;
        }

        try {
            DB::beginTransaction();

            foreach ($order->items as $item) {
                if ($item->product_variant_id && $item->unit_type !== 'weight') {
                    $variant = ProductVariant::with('cupSize')->find($item->product_variant_id);
                    if ($variant) {
                        // Devolver stock: primero cup_size, luego legacy
                        $variant->incrementStock($item->quantity);
                    }
                }
            }

            $note = '[ANULADA] ' . now()->format('d/m/Y H:i') . ($reason ? ": $reason" : '');
            $order->update([
                'status'         => 'cancelled',
                'payment_status' => 'failed',
                'notes'          => $order->notes ? $order->notes . "\n" . $note : $note,
            ]);

            DB::commit();
            session()->flash('message', 'Venta #' . $order->order_number . ' anulada. Stock devuelto.');

        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Error al anular: ' . $e->getMessage());
        }
    }

    // ==========================================
    // MODAL
    // ==========================================

    public function showOrder(int $orderId): void
    {
        $this->selectedOrder = Order::with([
            'user:id,name,email,phone,document,document_type,company_name',
            'items.customizations',
            'deliveryZone',
            'paymentMethod',
            'cashRegister',
        ])->findOrFail($orderId);
    }

    public function closeModal(): void
    {
        $this->selectedOrder = null;
    }

    // ==========================================
    // IMPRIMIR
    // ==========================================

    public function printTicket(int $orderId): void
    {
        $this->dispatch('openPrintPreview', orderId: $orderId);
    }

    // ==========================================
    // DELIVERY COST
    // ==========================================

    public function updateDeliveryCost(int $orderId, float $cost): void
    {
        $order               = Order::findOrFail($orderId);
        $order->delivery_cost = max(0, $cost);
        $order->total        = $order->subtotal + $order->delivery_cost;
        $order->save();
        session()->flash('message', 'Costo de delivery actualizado.');
    }

    // ==========================================
    // WHATSAPP
    // ==========================================

    public function sendToCustomer(int $orderId): void
    {
        $order = Order::with(['items', 'paymentMethod', 'deliveryZone'])->findOrFail($orderId);

        if (empty($order->customer_phone)) {
            session()->flash('error', 'Este pedido no tiene teléfono de cliente.');
            return;
        }

        $phone = preg_replace('/[^0-9]/', '', $order->customer_phone);
        if (!str_starts_with($phone, '595')) $phone = '595' . $phone;

        $url = 'https://wa.me/' . $phone . '?text=' . urlencode($this->buildCustomerMessage($order));
        $this->dispatch('openWhatsApp', url: $url);
    }

    public function sendToWhatsApp(int $orderId): void
    {
        $order = Order::with(['items', 'deliveryZone', 'paymentMethod'])->findOrFail($orderId);
        $url   = 'https://wa.me/' . self::COMPANY_WHATSAPP . '?text=' . urlencode($this->buildAdminMessage($order));
        $this->dispatch('openWhatsApp', url: $url);
    }

    // ==========================================
    // FILTROS
    // ==========================================

    public function clearFilters(): void
    {
        $this->filterStatus   = '';
        $this->filterSource   = '';
        $this->filterDateFrom = '';
        $this->filterDateTo   = '';
        $this->search         = '';
        $this->resetPage();
    }

    // ==========================================
    // STATS
    // ==========================================

    public function getStatsProperty(): array
    {
        $base = fn() => Order::whereDate('created_at', today())->where('status', '!=', 'cancelled');

        return [
            'today_total'     => $base()->sum('total'),
            'today_count'     => $base()->count(),
            'today_pos'       => $base()->where('source', 'pos')->count(),
            'today_web'       => $base()->where('source', 'web')->count(),
            'today_app'       => $base()->where('source', 'delivery_app')->count(),
            'pending_count'   => Order::where('status', 'pending')->count(),
            'cancelled_today' => Order::whereDate('created_at', today())->where('status', 'cancelled')->count(),
        ];
    }

    // ==========================================
    // MENSAJES WHATSAPP
    // ==========================================

    private function buildCustomerMessage(Order $order): string
    {
        $labels = [
            'pending'   => 'Pendiente',
            'confirmed' => 'Confirmado',
            'preparing' => 'En Preparación',
            'ready'     => 'Listo para Entrega',
            'delivering'=> 'En Camino',
            'delivered' => 'Entregado',
            'cancelled' => 'Cancelado',
        ];

        $msg  = "*ESTADO DE TU PEDIDO - Taskinho Açaí*\n";
        $msg .= "================================\n\n";
        $msg .= "Hola *{$order->customer_name}*!\n\n";
        $msg .= "Tu pedido *#{$order->order_number}* está: *" . ($labels[$order->status] ?? $order->status) . "*\n\n";

        $msg .= match($order->status) {
            'confirmed'  => "Tu pedido fue confirmado! Estamos preparándolo.\n",
            'preparing'  => "Tu pedido está siendo preparado!\n",
            'ready'      => "Tu pedido está listo! " . ($order->delivery_type === 'pickup' ? "Podés pasar a retirarlo." : "Pronto lo enviamos.") . "\n",
            'delivering' => "Tu pedido está en camino!\n",
            'delivered'  => "Tu pedido fue entregado. Gracias!\n",
            'pending'    => "Recibimos tu pedido. Pronto lo confirmamos!\n",
            'cancelled'  => "Tu pedido fue cancelado.\n",
            default      => '',
        };

        $msg .= "\n================================\n";
        $msg .= "Total: " . number_format($order->total, 0, ',', '.') . " Gs\n\n";
        $msg .= "Gracias por tu preferencia!\n";
        $msg .= "Taskinho Açaí - +595 975 621 886\n";

        return $msg;
    }

    private function buildAdminMessage(Order $order): string
    {
        $sourceLabel = match($order->source) {
            'pos'          => 'TIENDA',
            'delivery_app' => strtoupper($order->delivery_app_name ?? 'APP'),
            default        => 'WEB',
        };

        $msg  = "*DETALLES DEL PEDIDO*\n";
        $msg .= "================================\n\n";
        $msg .= "Pedido: *#{$order->order_number}*\n";
        $msg .= "Origen: {$sourceLabel}\n";
        $msg .= "Fecha: " . $order->created_at->format('d/m/Y H:i') . "\n\n";

        $msg .= "*CLIENTE*\n";
        $msg .= "Nombre: {$order->customer_name}\n";
        if ($order->customer_phone) $msg .= "Teléfono: {$order->customer_phone}\n";

        $msg .= "\n*PRODUCTOS*\n";
        foreach ($order->items as $item) {
            if ($item->unit_type === 'weight') {
                $msg .= "- {$item->product_name} " . number_format($item->weight, 3, ',', '.') . " kg\n";
            } else {
                $msg .= "- {$item->quantity}x {$item->product_name}";
                if ($item->volume) $msg .= " ({$item->volume}ml)";
                $msg .= "\n";
            }
        }

        $msg .= "\n*TOTAL: " . number_format($order->total, 0, ',', '.') . " Gs*\n";
        $msg .= "Pago: " . ($order->paymentMethod->name ?? 'N/A') . "\n";

        if ($order->source === 'delivery_app' && $order->delivery_app_commission) {
            $msg .= "Comisión app: " . number_format($order->delivery_app_commission, 0, ',', '.') . " Gs\n";
            $msg .= "Neto: " . number_format($order->total - $order->delivery_app_commission, 0, ',', '.') . " Gs\n";
        }

        return $msg;
    }

    // ==========================================
    // RENDER
    // ==========================================

    public function render()
    {
        $query = Order::with(['user', 'items', 'deliveryZone', 'paymentMethod']);

        if ($this->viewMode === 'active') {
            $query->whereIn('status', ['pending', 'confirmed', 'preparing', 'ready', 'delivering']);
        }

        if ($this->filterStatus) $query->where('status', $this->filterStatus);
        if ($this->filterSource) $query->where('source', $this->filterSource);
        if ($this->filterDateFrom) $query->whereDate('created_at', '>=', $this->filterDateFrom);
        if ($this->filterDateTo)   $query->whereDate('created_at', '<=', $this->filterDateTo);

        if ($this->search) {
            $query->where(fn($q) =>
                $q->where('order_number', 'like', '%' . $this->search . '%')
                  ->orWhere('customer_name', 'like', '%' . $this->search . '%')
                  ->orWhere('customer_phone', 'like', '%' . $this->search . '%')
                  ->orWhere('delivery_app_order_id', 'like', '%' . $this->search . '%')
            );
        }

        $orders = $query->orderBy('created_at', 'desc')->paginate(20);

        return view('livewire.admin.orders', [
            'orders' => $orders,
            'stats'  => $this->stats,
        ])->layout('components.layouts.admin', ['title' => 'Gestión de Pedidos']);
    }
}
