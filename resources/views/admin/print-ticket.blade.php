<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Ticket #{{ $order->order_number }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Courier New', monospace;
            font-size: 11px;
            width: 58mm;
            margin: 0 auto;
            line-height: 1.4;
        }
        .ticket { width: 100%; padding: 3mm; }
        .center { text-align: center; }
        .bold { font-weight: bold; }
        .title { font-size: 18px; font-weight: bold; text-align: center; margin-bottom: 2px; }
        .subtitle { font-size: 10px; text-align: center; margin-bottom: 3px; }
        .divider {
            border-top: 1px dashed #333;
            margin: 5px 0;
            height: 0;
        }
        .divider-thick {
            border-top: 2px solid #000;
            margin: 5px 0;
            height: 0;
        }
        .row {
            display: flex;
            justify-content: space-between;
            margin: 2px 0;
            font-size: 11px;
        }
        .row-single { margin: 2px 0; font-size: 11px; }
        .badge {
            text-align: center;
            font-size: 10px;
            margin: 3px 0;
            font-weight: bold;
            padding: 2px 0;
        }
        .section-title {
            font-weight: bold;
            font-size: 12px;
            margin: 5px 0 3px 0;
            text-align: center;
        }
        .product-block {
            margin: 8px 0;
            padding-bottom: 5px;
            border-bottom: 1px dotted #ccc;
        }
        .product-name {
            font-weight: bold;
            font-size: 12px;
            margin-bottom: 2px;
        }
        .product-detail {
            font-size: 10px;
            margin-left: 5px;
            color: #333;
        }
        .product-extra {
            font-size: 9px;
            margin-left: 10px;
            color: #555;
        }
        .total-section {
            margin: 8px 0;
            padding: 5px 0;
            border-top: 2px solid #000;
            border-bottom: 2px solid #000;
        }
        .total-row {
            display: flex;
            justify-content: space-between;
            margin: 3px 0;
            font-size: 11px;
        }
        .total-row.main {
            font-weight: bold;
            font-size: 14px;
            margin: 5px 0;
        }
        .payment-section {
            margin: 8px 0;
            padding: 5px 0;
            background: #f9f9f9;
            border-radius: 3px;
        }
        .payment-row {
            display: flex;
            justify-content: space-between;
            margin: 2px 5px;
            font-size: 11px;
        }
        .payment-subtitle {
            font-size: 9px;
            color: #666;
            margin: 1px 5px 1px 15px;
        }
        .footer {
            text-align: center;
            margin-top: 10px;
            font-size: 10px;
            line-height: 1.5;
        }

        @media screen {
            body {
                background: #f5f5f5;
                padding: 20px;
            }
            .ticket {
                background: white;
                box-shadow: 0 2px 10px rgba(0,0,0,0.1);
                max-width: 58mm;
                border-radius: 5px;
            }
            .buttons {
                text-align: center;
                margin-top: 20px;
            }
            .btn {
                padding: 12px 24px;
                margin: 5px;
                border: none;
                border-radius: 5px;
                cursor: pointer;
                font-size: 14px;
                font-weight: bold;
            }
            .btn-print { background: #10b981; color: white; }
            .btn-print:hover { background: #059669; }
            .btn-close { background: #6b7280; color: white; }
            .btn-close:hover { background: #4b5563; }
        }

        @media print {
            body { width: 58mm; }
            @page { size: 58mm auto; margin: 0; }
            .buttons { display: none; }
        }
    </style>
</head>
<body>
<div class="ticket">

    {{-- HEADER --}}
    <div class="title">Taskinho Açaí</div>
    <div class="subtitle">Ciudad del Este</div>
    <div class="divider-thick"></div>

    @php
        $source = $order->source ?? 'pos';
        $sourceLabel = match($source) {
            'pos'          => 'TICKET DE VENTA',
            'delivery_app' => 'PEDIDO - APP DELIVERY',
            default        => 'TICKET DE PEDIDO',
        };
        $sourceBadge = match($source) {
            'pos'          => '[TIENDA]',
            'delivery_app' => '[' . strtoupper($order->delivery_app_name ?? 'APP') . ']',
            default        => '[WEB]',
        };
    @endphp

    <div class="center bold">{{ $sourceLabel }}</div>
    <div class="badge">{{ $sourceBadge }}</div>
    <div class="divider-thick"></div>

    {{-- INFO PEDIDO --}}
    <div class="row">
        <span>Pedido:</span>
        <span class="bold">#{{ $order->order_number }}</span>
    </div>
    <div class="row">
        <span>Fecha:</span>
        <span>{{ $order->created_at->format('d/m/Y H:i') }}</span>
    </div>

    @if($source === 'delivery_app' && $order->delivery_app_order_id)
        <div class="row">
            <span>Ref App:</span>
            <span>{{ $order->delivery_app_order_id }}</span>
        </div>
    @endif

    @if($order->printedBy)
        <div class="row">
            <span>Cajero:</span>
            <span>{{ $order->printedBy->name }}</span>
        </div>
    @endif

    <div class="divider"></div>

    {{-- CLIENTE --}}
    @php $showCustomer = !empty($order->customer_name) && strtoupper($order->customer_name) !== 'CONSUMIDOR FINAL'; @endphp
    @if($showCustomer)
        <div class="section-title">CLIENTE</div>
        <div class="row-single bold">{{ $order->customer_name }}</div>
        @if($order->customer_phone)
            <div class="row-single">Tel: {{ $order->customer_phone }}</div>
        @endif
        <div class="divider"></div>
    @endif

    {{-- PRODUCTOS --}}
    <div class="section-title">PRODUCTOS</div>
    <div class="divider"></div>

    @foreach($order->items as $item)
        <div class="product-block">
            <div class="product-name">{{ $item->product_name }}</div>

            @if(($item->unit_type ?? 'unit') === 'weight')
                <div class="product-detail">
                    ⚖️ {{ number_format($item->weight, 3, ',', '.') }} kg × {{ number_format($item->price_per_kg, 0, ',', '.') }} Gs/kg
                </div>
            @else
                @if($item->volume)
                    <div class="product-detail">
                        Tamaño: {{ $item->volume >= 1000 ? ($item->volume / 1000).'L' : $item->volume.'ml' }}
                    </div>
                @endif
                <div class="product-detail">
                    Cantidad: {{ $item->quantity }} × {{ number_format($item->price, 0, ',', '.') }} Gs
                </div>

                {{-- Complementos --}}
                @if($item->customizations && $item->customizations->count() > 0)
                    @foreach($item->customizations as $custom)
                        <div class="product-extra">
                            + {{ $custom->option_name }}
                            @if($custom->price > 0)
                                (+{{ number_format($custom->price, 0, ',', '.') }} Gs)
                            @endif
                        </div>
                    @endforeach
                @endif
            @endif

            <div class="row" style="margin-top: 3px;">
                <span class="bold">Subtotal:</span>
                <span class="bold">{{ number_format($item->subtotal, 0, ',', '.') }} Gs</span>
            </div>
        </div>
    @endforeach

    {{-- TOTALES --}}
    <div class="total-section">
        <div class="total-row">
            <span>Subtotal:</span>
            <span>{{ number_format($order->subtotal, 0, ',', '.') }} Gs</span>
        </div>
        @if($order->delivery_cost > 0)
            <div class="total-row">
                <span>Delivery:</span>
                <span>{{ number_format($order->delivery_cost, 0, ',', '.') }} Gs</span>
            </div>
        @endif
        <div class="total-row main">
            <span>TOTAL:</span>
            <span>{{ number_format($order->total, 0, ',', '.') }} Gs</span>
        </div>

        @if($source === 'delivery_app' && $order->delivery_app_commission)
            <div class="divider"></div>
            <div class="total-row" style="font-size: 10px;">
                <span>Comisión {{ $order->delivery_app_name ?? 'App' }}:</span>
                <span>-{{ number_format($order->delivery_app_commission, 0, ',', '.') }} Gs</span>
            </div>
            <div class="total-row bold">
                <span>NETO RECIBIDO:</span>
                <span>{{ number_format($order->total - $order->delivery_app_commission, 0, ',', '.') }} Gs</span>
            </div>
        @endif
    </div>

    {{-- MÉTODO DE PAGO --}}
    @if($order->is_split_payment && $order->payments->count() > 0)
        <div class="section-title">💳 PAGO DIVIDIDO</div>
        <div class="divider"></div>
        <div class="payment-section">
            @foreach($order->payments as $payment)
                <div class="payment-row bold">
                    <span>{{ $payment->paymentMethod->name }}</span>
                    <span>
                        @if(isset($payment->details['original_currency']) && $payment->details['original_currency'] === 'BRL')
                            {{ number_format($payment->details['original_amount'], 2, ',', '.') }} R$
                        @else
                            {{ number_format($payment->amount, 0, ',', '.') }} Gs
                        @endif
                    </span>
                </div>
                @if(isset($payment->details['original_currency']) && $payment->details['original_currency'] === 'BRL')
                    <div class="payment-subtitle">
                        (Equiv: {{ number_format($payment->amount, 0, ',', '.') }} Gs)
                    </div>
                @endif
            @endforeach
        </div>
    @else
        <div class="row">
            <span class="bold">Pago:</span>
            <span>{{ $order->paymentMethod?->name ?? 'N/A' }}</span>
        </div>
    @endif

    <div class="row">
        <span class="bold">Estado:</span>
        <span>{{ $order->payment_status === 'paid' ? 'PAGADO ✓' : 'PENDIENTE' }}</span>
    </div>

    @if($order->notes)
        <div class="divider"></div>
        <div class="row-single bold">NOTAS:</div>
        <div class="row-single">{{ $order->notes }}</div>
    @endif

    <div class="divider-thick"></div>

    {{-- FOOTER --}}
    <div class="footer">
        <div class="bold">¡Gracias por su compra!</div>
        <div style="margin-top: 5px;">Taskinho Açaí</div>
        <div>Ciudad del Este</div>
        <div>+595 986 150 627</div>
    </div>

    <br><br>
</div>

<div class="buttons">
    <button class="btn btn-print" onclick="window.print()">🖨️ Imprimir</button>
    <button class="btn btn-close" onclick="window.close()">✖️ Cerrar</button>
</div>

<script>
    window.onload = function () {
        document.querySelector('.btn-print')?.focus();
    };

    document.addEventListener('keydown', function (e) {
        if ((e.ctrlKey || e.metaKey) && e.key === 'p') {
            e.preventDefault();
            window.print();
        }
        if (e.key === 'Escape') window.close();
    });
</script>
</body>
</html>
