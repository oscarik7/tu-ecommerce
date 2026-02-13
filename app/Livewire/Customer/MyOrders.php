<?php

namespace App\Livewire\Customer;

use App\Models\Order;
use Livewire\Component;

class MyOrders extends Component
{
    // Vista activa: 'orders' | 'account'
    public string $activeTab = 'orders';

    // Modal pedido
    public ?int $selectedOrderId = null;

    // Edición de perfil
    public bool   $editingProfile = false;
    public string $editName       = '';
    public string $editPhone      = '';
    public string $editAddress    = '';
    public string $editDocType    = 'ci';
    public string $editDoc        = '';
    public string $editCompany    = '';
    public string $profileSuccess = '';

    const COMPANY_WHATSAPP = '595986150627';

    // ==========================================
    // LIFECYCLE
    // ==========================================

    public function mount(): void
    {
        if (!auth()->check()) {
            $this->redirect(route('login'));
        }

        // Tab desde query string: /my-orders?tab=account
        $this->activeTab = request('tab', 'orders');
    }

    // ==========================================
    // TABS
    // ==========================================

    public function setTab(string $tab): void
    {
        $this->activeTab = $tab;
        $this->editingProfile = false;
        $this->profileSuccess  = '';
    }

    // ==========================================
    // MODAL PEDIDO
    // ==========================================

    public function showOrder(int $orderId): void
    {
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
    // PERFIL — edición
    // ==========================================

    public function startEditProfile(): void
    {
        $user = auth()->user();
        $this->editName     = $user->name;
        $user->phone        ?? '';
        $this->editPhone    = $user->phone ?? '';
        $this->editAddress  = $user->address ?? '';
        $this->editDocType  = $user->document_type ?? 'ci';
        $this->editDoc      = $user->document ?? '';
        $this->editCompany  = $user->company_name ?? '';
        $this->editingProfile = true;
        $this->profileSuccess  = '';
    }

    public function cancelEditProfile(): void
    {
        $this->editingProfile = false;
        $this->resetValidation();
    }

    public function saveProfile(): void
    {
        $this->validate([
            'editName'    => 'required|string|max:255',
            'editPhone'   => 'nullable|string|max:30',
            'editAddress' => 'nullable|string|max:500',
            'editDocType' => 'required|in:ci,ruc',
            'editDoc'     => 'nullable|string|max:20',
            'editCompany' => 'nullable|string|max:255',
        ], [
            'editName.required' => 'El nombre es obligatorio.',
            'editDoc.max'       => 'El documento no puede superar 20 caracteres.',
        ]);

        auth()->user()->update([
            'name'          => $this->editName,
            'phone'         => $this->editPhone ?: null,
            'address'       => $this->editAddress ?: null,
            'document_type' => $this->editDocType,
            'document'      => $this->editDoc ?: null,
            'company_name'  => ($this->editDocType === 'ruc' && $this->editCompany)
                                ? $this->editCompany
                                : null,
        ]);

        $this->editingProfile = false;
        $this->profileSuccess  = '¡Perfil actualizado correctamente!';
    }

    // ==========================================
    // WHATSAPP
    // ==========================================

    public function sendToWhatsApp(int $orderId): void
    {
        $order = Order::with(['items.customizations', 'deliveryZone', 'paymentMethod'])
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
            ->with(['items.customizations', 'deliveryZone', 'paymentMethod'])
            ->orderByDesc('created_at')
            ->get();

        $selectedOrder = $this->selectedOrderId
            ? Order::with(['items.customizations', 'deliveryZone', 'paymentMethod'])
                ->where('user_id', auth()->id())
                ->find($this->selectedOrderId)
            : null;

        return view('livewire.customer.my-orders', [
            'orders'        => $orders,
            'selectedOrder' => $selectedOrder,
            'user'          => auth()->user(),
        ])->layout('components.layouts.app');
    }
}
