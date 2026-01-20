<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;

class PrintController extends Controller
{
    /**
     * Mostrar vista previa del ticket para imprimir
     */
    public function showTicket($orderId)
    {
        $order = Order::with(['items', 'paymentMethod', 'deliveryZone', 'user'])
            ->findOrFail($orderId);
        
        return view('admin.print-ticket', compact('order'));
    }
}