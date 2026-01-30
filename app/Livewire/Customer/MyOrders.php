<?php

namespace App\Livewire\Customer;

use App\Models\Order;
use App\Models\PaymentMethod;
use Livewire\Component;

class MyOrders extends Component
{
    public $selectedOrder = null;

    // Número de WhatsApp de la empresa
    const COMPANY_WHATSAPP = '595975621886';

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
        $order = Order::with(['items.product.variants', 'deliveryZone', 'paymentMethod'])
            ->findOrFail($orderId);
        
        // Generar mensaje de WhatsApp
        $whatsappUrl = $this->generateWhatsAppMessage($order);
        
        // Abrir WhatsApp en una nueva ventana
        $this->dispatch('openWhatsApp', url: $whatsappUrl);
    }

    /**
     * Generar mensaje detallado de WhatsApp SIN EMOJIS
     */
    private function generateWhatsAppMessage($order)
    {
        $paymentMethod = $order->paymentMethod;
        
        // Construir el mensaje SIN EMOJIS
        $message = "*NUEVO PEDIDO - Taskinho Açaí*\n";
        $message .= "================================\n\n";
        
        // Información del pedido
        $message .= "*DATOS DEL PEDIDO*\n";
        $message .= "Nro Pedido: *{$order->order_number}*\n";
        $message .= "Fecha: " . $order->created_at->format('d/m/Y H:i') . "\n";
        $message .= "Estado: " . $this->getStatusLabel($order->status) . "\n\n";
        
        // Información del cliente
        $message .= "*DATOS DEL CLIENTE*\n";
        $message .= "Nombre: *{$order->customer_name}*\n";
        $message .= "Telefono: {$order->customer_phone}\n\n";
        
        // Tipo de entrega
        $message .= "*TIPO DE ENTREGA*\n";
        if ($order->delivery_type == 'delivery') {
            $message .= "Modalidad: *DELIVERY*\n";
            $message .= "Direccion: {$order->customer_address}\n";
            $message .= "Ciudad: {$order->customer_city}\n";
            
            if ($order->deliveryZone) {
                $message .= "Zona: {$order->deliveryZone->name}\n";
            }
            
            if ($order->delivery_cost == 0) {
                $message .= "_Costo de envio por confirmar segun ubicacion_\n\n";
            } else {
                $message .= "Costo Delivery: " . number_format($order->delivery_cost, 0, ',', '.') . " Gs\n\n";
            }
        } else {
            $message .= "Modalidad: *RETIRO EN TIENDA*\n";
            $message .= "Direccion Tienda: Av. Principal 123, Ciudad del Este\n";
            $message .= "Horario: Lunes a Sabado 9:00 - 20:00\n\n";
        }
        
        // Detalle de productos
        $message .= "*PRODUCTOS*\n";
        $message .= "================================\n\n";
        
        foreach ($order->items as $item) {
            $message .= "*{$item->product_name}*\n";
            $message .= "- Tamaño: {$item->volume}ml\n";
            $message .= "- Cantidad: {$item->quantity}\n";
            $message .= "- Precio Unit: " . number_format($item->price, 0, ',', '.') . " Gs\n";
            $message .= "- Subtotal: *" . number_format($item->subtotal, 0, ',', '.') . " Gs*\n\n";
        }
        
        $message .= "================================\n\n";
        
        // Resumen de costos
        $message .= "*RESUMEN DE COSTOS*\n";
        $message .= "Subtotal: " . number_format($order->subtotal, 0, ',', '.') . " Gs\n";
        
        if ($order->delivery_type == 'delivery') {
            if ($order->delivery_cost == 0) {
                $message .= "Delivery: _A confirmar_\n";
                $message .= "*TOTAL (PARCIAL): " . number_format($order->total, 0, ',', '.') . " Gs*\n\n";
                $message .= "_Total final incluira costo de delivery_\n\n";
            } else {
                $message .= "Delivery: " . number_format($order->delivery_cost, 0, ',', '.') . " Gs\n";
                $message .= "*TOTAL: " . number_format($order->total, 0, ',', '.') . " Gs*\n\n";
            }
        } else {
            $message .= "Delivery: GRATIS\n";
            $message .= "*TOTAL: " . number_format($order->total, 0, ',', '.') . " Gs*\n\n";
        }
        
        // Método de pago
        $message .= "*METODO DE PAGO*\n";
        $message .= "- {$paymentMethod->name}\n";
        if ($paymentMethod->description) {
            $message .= "- {$paymentMethod->description}\n";
        }
        
        // Datos bancarios si existen
        if ($paymentMethod->bank_details) {
            $message .= "\n*DATOS BANCARIOS*\n";
            $message .= "Banco: {$paymentMethod->bank_details['bank']}\n";
            $message .= "Cuenta: {$paymentMethod->bank_details['account_number']}\n";
            $message .= "Titular: {$paymentMethod->bank_details['account_holder']}\n";
        }
        
        // Instrucciones de pago
        if ($paymentMethod->instructions) {
            $message .= "\n*INSTRUCCIONES*\n";
            $message .= "{$paymentMethod->instructions}\n";
        }
        
        // Notas adicionales
        if ($order->notes) {
            $message .= "\n*NOTAS DEL CLIENTE*\n";
            $message .= "{$order->notes}\n";
        }
        
        $message .= "\n================================\n";
        
        if ($order->status == 'pending') {
            $message .= "_Por favor confirmar recepcion del pedido_\n";
            $message .= "_Te contactaremos pronto para coordinar la entrega_\n";
        } else {
            $message .= "_Este es un reenvio del pedido_\n";
            $message .= "_Consulta sobre el estado de tu pedido_\n";
        }
        
        // Generar la URL de WhatsApp
        $whatsappUrl = "https://wa.me/" . self::COMPANY_WHATSAPP . "?text=" . urlencode($message);
        
        return $whatsappUrl;
    }

    /**
     * Obtener etiqueta del estado en español
     */
    private function getStatusLabel($status)
    {
        $labels = [
            'pending' => 'Pendiente',
            'confirmed' => 'Confirmado',
            'preparing' => 'Preparando',
            'ready' => 'Listo',
            'delivering' => 'En camino',
            'delivered' => 'Entregado',
            'cancelled' => 'Cancelado',
        ];
        
        return $labels[$status] ?? $status;
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