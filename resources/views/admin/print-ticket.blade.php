<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ticket #{{ $order->order_number }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Courier New', monospace;
            background: #f3f4f6;
            padding: 20px;
            display: flex;
            justify-content: center;
            align-items: flex-start;
            min-height: 100vh;
        }

        .preview-container {
            background: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            max-width: 400px;
        }

        .ticket {
            width: 300px;
            background: white;
            border: 2px dashed #ccc;
            padding: 10px;
            font-size: 12px;
            line-height: 1.4;
            margin: 0 auto;
        }

        .center {
            text-align: center;
        }

        .bold {
            font-weight: bold;
        }

        .large {
            font-size: 18px;
            font-weight: bold;
        }

        .line {
            border-bottom: 1px dashed #000;
            margin: 8px 0;
        }

        .double-line {
            border-bottom: 2px solid #000;
            margin: 8px 0;
        }

        .row {
            display: flex;
            justify-content: space-between;
            margin: 3px 0;
        }

        .product {
            margin: 8px 0;
        }

        .indent {
            padding-left: 15px;
        }

        .total-section {
            background: #f9fafb;
            padding: 8px;
            margin: 10px 0;
            border-radius: 5px;
        }

        .source-badge {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 4px;
            font-size: 10px;
            font-weight: bold;
            margin-top: 4px;
        }

        .source-pos {
            background: #d1fae5;
            color: #065f46;
        }

        .source-web {
            background: #dbeafe;
            color: #1e40af;
        }

        .buttons {
            display: flex;
            gap: 10px;
            margin-top: 20px;
            justify-content: center;
        }

        .btn {
            padding: 12px 24px;
            border: none;
            border-radius: 8px;
            font-weight: bold;
            cursor: pointer;
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s;
        }

        .btn-print {
            background: #10b981;
            color: white;
        }

        .btn-print:hover {
            background: #059669;
        }

        .btn-close {
            background: #6b7280;
            color: white;
        }

        .btn-close:hover {
            background: #4b5563;
        }

        @media print {
            body {
                background: white;
                padding: 0;
            }

            .preview-container {
                box-shadow: none;
                padding: 0;
                max-width: 80mm;
            }

            .ticket {
                border: none;
                width: 80mm;
                font-size: 11px;
            }

            .buttons {
                display: none;
            }
        }

        @page {
            size: 58mm auto;
            margin: 0;
        }
    </style>
</head>
<body>
    <div class="preview-container">
        <div class="ticket">
            <!-- Header -->
            <div class="center large">Açaí Store</div>
            <div class="center">================================</div>
            <div class="center bold">
                @if($order->source === 'pos')
                    TICKET DE VENTA
                @else
                    TICKET DE PEDIDO
                @endif
            </div>
            <div class="center">
                <span class="source-badge {{ $order->source === 'pos' ? 'source-pos' : 'source-web' }}">
                    {{ $order->source === 'pos' ? '🏪 TIENDA' : '🌐 WEB' }}
                </span>
            </div>
            <div class="center">================================</div>
            <br>

            <!-- Información del Pedido -->
            <div class="bold">Pedido: #{{ $order->order_number }}</div>
            <div>Fecha: {{ $order->created_at->format('d/m/Y H:i') }}</div>
            @if($order->source !== 'pos')
                <div>Estado: {{ strtoupper($order->status) }}</div>
            @endif
            <div class="line"></div>
            <br>

            <!-- Cliente -->
            @if($order->customer_name !== 'Consumidor Final')
                <div class="bold">CLIENTE</div>
                <div>{{ $order->customer_name }}</div>
                @if($order->customer_phone)
                    <div>Tel: {{ $order->customer_phone }}</div>
                @endif
                <div class="line"></div>
                <br>
            @endif

            <!-- Tipo de Entrega (solo para pedidos web) -->
            @if($order->source !== 'pos')
                <div class="bold">ENTREGA</div>
                @if($order->delivery_type == 'delivery')
                    <div>Tipo: DELIVERY</div>
                    @if($order->customer_address)
                        <div>Dir: {{ $order->customer_address }}</div>
                    @endif
                    @if($order->deliveryZone)
                        <div>Zona: {{ $order->deliveryZone->name }}</div>
                    @endif
                @else
                    <div>Tipo: RETIRO EN TIENDA</div>
                @endif
                <div class="line"></div>
                <br>
            @endif

            <!-- Productos -->
            <div class="bold">PRODUCTOS</div>
            <div class="double-line"></div>
            
            @foreach($order->items as $item)
                <div class="product">
                    <div class="bold">{{ $item->product_name }}</div>
                    @if($item->volume)
                        <div class="indent">{{ $item->volume }}ml</div>
                    @endif
                    <div class="indent">{{ $item->quantity }} x {{ number_format($item->price, 0, ',', '.') }} Gs</div>
                    <div class="indent">= {{ number_format($item->subtotal, 0, ',', '.') }} Gs</div>
                </div>
                <div class="line"></div>
            @endforeach
            
            <br>

            <!-- Totales -->
            <div class="total-section">
                <div class="row">
                    <span>Subtotal:</span>
                    <span>{{ number_format($order->subtotal, 0, ',', '.') }} Gs</span>
                </div>
                
                @if($order->delivery_cost > 0)
                    <div class="row">
                        <span>Delivery:</span>
                        <span>{{ number_format($order->delivery_cost, 0, ',', '.') }} Gs</span>
                    </div>
                @endif
                
                <div class="double-line"></div>
                
                <div class="row bold large">
                    <span>TOTAL:</span>
                    <span>{{ number_format($order->total, 0, ',', '.') }} Gs</span>
                </div>
            </div>

            <!-- Método de Pago -->
            <div class="bold">Pago: {{ $order->paymentMethod->name ?? 'N/A' }}</div>
            <div>Estado: {{ $order->payment_status === 'paid' ? 'PAGADO' : 'PENDIENTE' }}</div>
            
            @if($order->notes)
                <br>
                <div class="line"></div>
                <div class="bold">NOTAS:</div>
                <div>{{ $order->notes }}</div>
            @endif

            <div class="line"></div>
            <br>

            <!-- Footer -->
            <div class="center">
                <div>¡Gracias por su compra!</div>
                <div class="bold">Açaí Store</div>
                <div>Ciudad del Este</div>
                <div>+595 975 621 886</div>
            </div>
            
            <br><br>
        </div>

        <!-- Botones de Acción -->
        <div class="buttons">
            <button class="btn btn-print" onclick="window.print()">
                🖨️ Imprimir
            </button>
            <button class="btn btn-close" onclick="window.close()">
                ✖️ Cerrar
            </button>
        </div>
    </div>

    <script>
        window.onload = function() {
            document.querySelector('.btn-print').focus();
        };

        document.addEventListener('keydown', function(e) {
            if ((e.ctrlKey || e.metaKey) && e.key === 'p') {
                e.preventDefault();
                window.print();
            }
            if (e.key === 'Escape') {
                window.close();
            }
        });
    </script>
</body>
</html>