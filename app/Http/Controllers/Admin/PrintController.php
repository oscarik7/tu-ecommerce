<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;

class PrintController extends Controller
{
    public function showTicket(int $orderId)
    {
        $order = Order::with([
            'items.customizations',  
            'paymentMethod',
            'deliveryZone',
            'user',
            'printedBy',       // quien imprimió
            'cashRegister',    // caja donde se registró
        ])->findOrFail($orderId);

        // Registrar quién imprimió y cuándo (solo si no fue impreso antes)
        if (!$order->printed_by && auth()->check()) {
            $order->update(['printed_by' => auth()->id()]);
        }

        return view('admin.print-ticket', compact('order'));
    }
}