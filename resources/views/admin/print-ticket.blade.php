<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Ticket #{{ $order->order_number }}</title>
    <style>
        * { margin: 0; padding: 0; }
        body { font-family: monospace; font-size: 12px; width: 48mm; margin: 0 auto; line-height: 1.3; }
        .ticket { width: 100%; padding: 2mm; }
        .center  { text-align: center; }
        .bold    { font-weight: bold; }
        .right   { text-align: right; }
        .title   { font-size: 16px; font-weight: bold; text-align: center; margin-bottom: 3px; }
        .subtitle { font-size: 11px; text-align: center; margin-bottom: 2px; }
        .divider { text-align: center; margin: 4px 0; }
        .row      { width: 100%; margin: 2px 0; }
        .row-flex { display: table; width: 100%; margin: 2px 0; }
        .row-flex .left  { display: table-cell; text-align: left; }
        .row-flex .right { display: table-cell; text-align: right; }
        .badge   { text-align: center; font-size: 10px; margin: 3px 0; font-weight: bold; }
        .product-name   { font-weight: bold; margin-top: 5px; }
        .product-detail { padding-left: 8px; }
        .product-weight { padding-left: 8px; font-style: italic; }
        .product-extra  { padding-left: 12px; font-size: 10px; line-height: 1.2; }
        .total-box { border-top: 1px dashed #000; border-bottom: 1px dashed #000; padding: 5px 0; margin: 5px 0; }
        .total-line { font-size: 14px; font-weight: bold; }
        .app-net { font-size: 11px; }
        .footer  { text-align: center; margin-top: 10px; font-size: 11px; }

        @media screen {
            body { background: #f0f0f0; padding: 20px; }
            .ticket { background: white; box-shadow: 0 2px 10px rgba(0,0,0,0.1); max-width: 58mm; }
            .buttons { text-align: center; margin-top: 20px; }
            .btn { padding: 10px 20px; margin: 5px; border: none; border-radius: 5px; cursor: pointer; font-size: 14px; }
            .btn-print { background: #10b981; color: white; }
            .btn-close { background: #6b7280; color: white; }
        }
        @media print {
            body { width: 48mm; }
            @page { size: 58mm auto; margin: 0; }
            .buttons { display: none; }
        }
    </style>
</head>
<body>
<div class="ticket">

    <div class="title">Taskinho Açaí</div>
    <div class="subtitle">Ciudad del Este</div>
    <div class="divider">=============================</div>

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
    <div class="divider">=============================</div>

    <div class="row">Pedido: #{{ $order->order_number }}</div>
    <div class="row">Fecha: {{ $order->created_at->format('d/m/Y H:i') }}</div>

    @if($source === 'delivery_app' && $order->delivery_app_order_id)
        <div class="row">Ref App: {{ $order->delivery_app_order_id }}</div>
    @endif
    @if($source === 'web')
        <div class="row">Estado: {{ strtoupper($order->status) }}</div>
    @endif
    @if($order->printedBy)
        <div class="row">Cajero: {{ $order->printedBy->name }}</div>
    @endif

    <div class="divider">---------------------------</div>

    @php $showCustomer = !empty($order->customer_name) && strtoupper($order->customer_name) !== 'CONSUMIDOR FINAL'; @endphp
    @if($showCustomer)
        <div class="row bold">CLIENTE</div>
        <div class="row">{{ $order->customer_name }}</div>
        @if($order->customer_phone)
            <div class="row">Tel: {{ $order->customer_phone }}</div>
        @endif
        <div class="divider">---------------------------</div>
    @endif

    @if($source === 'web')
        <div class="row bold">ENTREGA</div>
        @if($order->delivery_type === 'delivery')
            <div class="row">Tipo: DELIVERY</div>
            @if($order->customer_address)
                <div class="row">Dir: {{ $order->customer_address }}</div>
            @endif
            @if($order->deliveryZone)
                <div class="row">Zona: {{ $order->deliveryZone->name }}</div>
            @endif
        @else
            <div class="row">Tipo: RETIRO EN TIENDA</div>
        @endif
        <div class="divider">---------------------------</div>
    @endif

    <div class="row bold">PRODUCTOS</div>
    <div class="divider">=============================</div>

    @foreach($order->items as $item)
        <div class="product-name">{{ $item->product_name }}</div>

        @if(($item->unit_type ?? 'unit') === 'weight')
            <div class="product-weight">
                {{ number_format($item->weight, 3, ',', '.') }} kg
                x {{ number_format($item->price_per_kg, 0, ',', '.') }} Gs/kg
            </div>
            <div class="product-detail bold">
                = {{ number_format($item->subtotal, 0, ',', '.') }} Gs
            </div>
        @else
            @if($item->volume)
                <div class="product-detail">
                    {{ $item->volume >= 1000 ? ($item->volume / 1000).'L' : $item->volume.'ml' }}
                </div>
            @endif

            <div class="product-detail">
                {{ $item->quantity }} x {{ number_format($item->price, 0, ',', '.') }} Gs
            </div>

            {{-- Complementos --}}
            @if($item->customizations && $item->customizations->count() > 0)
                @foreach($item->customizations as $custom)
                    <div class="product-extra">
                        @if($custom->price > 0)
                            + {{ $custom->option_name }} +{{ number_format($custom->price, 0, ',', '.') }} Gs
                        @else
                            + {{ $custom->option_name }}
                        @endif
                    </div>
                @endforeach
            @endif

            {{--
                IMPORTANTE: $item->subtotal ya fue guardado como (base + extras) × qty.
                NO sumar customizations_subtotal — eso causaba el doble conteo que mostraba 40.000
                cuando debería ser 40.000 y el subtotal de la orden 40.000 también.
            --}}
            <div class="product-detail bold">
                = {{ number_format($item->subtotal, 0, ',', '.') }} Gs
            </div>
        @endif

        <div class="divider">---------------------------</div>
    @endforeach

    <div class="total-box">
        <div class="row-flex">
            <span class="left">Subtotal:</span>
            <span class="right">{{ number_format($order->subtotal, 0, ',', '.') }} Gs</span>
        </div>
        @if($order->delivery_cost > 0)
            <div class="row-flex">
                <span class="left">Delivery:</span>
                <span class="right">{{ number_format($order->delivery_cost, 0, ',', '.') }} Gs</span>
            </div>
        @endif
        <div class="row-flex total-line">
            <span class="left">TOTAL:</span>
            <span class="right">{{ number_format($order->total, 0, ',', '.') }} Gs</span>
        </div>
        @if($source === 'delivery_app' && $order->delivery_app_commission)
            <div class="divider">- - - - - - - - - - - - -</div>
            <div class="row-flex app-net">
                <span class="left">Comision {{ $order->delivery_app_name ?? 'App' }}:</span>
                <span class="right">-{{ number_format($order->delivery_app_commission, 0, ',', '.') }} Gs</span>
            </div>
            <div class="row-flex app-net bold">
                <span class="left">NETO RECIBIDO:</span>
                <span class="right">{{ number_format($order->total - $order->delivery_app_commission, 0, ',', '.') }} Gs</span>
            </div>
        @endif
    </div>

    <div class="row">Pago: {{ $order->paymentMethod->name ?? 'N/A' }}</div>
    <div class="row">Estado: {{ $order->payment_status === 'paid' ? 'PAGADO' : 'PENDIENTE' }}</div>

    @if($order->notes)
        <div class="divider">---------------------------</div>
        <div class="row bold">NOTAS:</div>
        <div class="row">{{ $order->notes }}</div>
    @endif

    <div class="divider">---------------------------</div>

    <div class="footer">
        <div>¡Gracias por su compra!</div>
        <div class="bold">Taskinho Açaí</div>
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
    window.onload = function () { document.querySelector('.btn-print')?.focus(); };
    document.addEventListener('keydown', function (e) {
        if ((e.ctrlKey || e.metaKey) && e.key === 'p') { e.preventDefault(); window.print(); }
        if (e.key === 'Escape') window.close();
    });
</script>
</body>
</html>
