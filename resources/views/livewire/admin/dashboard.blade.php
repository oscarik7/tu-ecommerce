{{-- FIX 9: wire:poll.60000ms refresca datos en POS activo sin recargar la página --}}
<div class="space-y-4 md:space-y-5" wire:poll.60000ms>

    {{-- ══════════════════════════════════════════════════════
         HEADER
         FIX 15: en mobile la fecha y el badge de caja se apilan
    ══════════════════════════════════════════════════════ --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
        <div>
            <h1 class="text-xl md:text-2xl font-black text-gray-900">👋 Dashboard</h1>
            <p class="text-gray-400 text-xs md:text-sm mt-0.5">{{ now()->isoFormat('dddd D [de] MMMM, YYYY') }}</p>
        </div>

        {{-- FIX 6: badge de caja muestra ventas activas de la sesión --}}
        @if($openRegister)
            <a href="{{ route('admin.cash-registers') }}"
               class="inline-flex flex-col items-start sm:items-end gap-0.5 bg-emerald-50 hover:bg-emerald-100 border border-emerald-200 text-emerald-800 font-bold px-3 py-2 rounded-xl text-xs transition-all self-start sm:self-auto">
                <span class="flex items-center gap-1.5">
                    <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse flex-shrink-0"></span>
                    Caja Abierta
                </span>
                @if($registerLiveSales !== null)
                    @php $rs = (float) $registerLiveSales; @endphp
                    <span class="text-emerald-600 font-black">
                        @if($rs >= 1000000)
                            {{ number_format($rs / 1000000, 1) }}M Gs vendidos
                        @elseif($rs >= 1000)
                            {{ number_format($rs / 1000, 0) }}k Gs vendidos
                        @else
                            {{ number_format($rs, 0, ',', '.') }} Gs vendidos
                        @endif
                    </span>
                @endif
            </a>
        @else
            <a href="{{ route('admin.cash-registers') }}"
               class="inline-flex items-center gap-1.5 bg-red-50 hover:bg-red-100 border border-red-200 text-red-700 font-bold px-3 py-2 rounded-xl text-xs transition-all self-start sm:self-auto">
                <span class="w-2 h-2 rounded-full bg-red-500 flex-shrink-0"></span>
                Caja Cerrada
            </a>
        @endif
    </div>

    {{-- ══════════════════════════════════════════════════════
         TARJETAS PRINCIPALES
         FIX 3/10: formato numérico condicional — nunca "0k"
         FIX 7: tendencia hoy vs ayer
         FIX 8: neto real del mes
         FIX 12: tarjeta "Clientes" reemplazada por "Neto Mes"
         FIX 16/20/21: padding y borders consistentes, mobile-safe
    ══════════════════════════════════════════════════════ --}}
    @php
        // Helper de formato inline para evitar repetición
        // Se usa en múltiples tarjetas y filas
        function fmtGs(float $v): string {
            if ($v >= 1000000) return number_format($v / 1000000, 1) . 'M';
            if ($v >= 1000)    return number_format($v / 1000, 0) . 'k';
            return number_format($v, 0, ',', '.');
        }
    @endphp

    <div class="grid grid-cols-2 gap-3 md:gap-4">

        {{-- Ventas hoy --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4 md:p-5">
            <div class="flex items-start justify-between gap-2">
                <div class="min-w-0 flex-1">
                    <p class="text-xs font-bold text-gray-400 uppercase tracking-wider">Ventas Hoy</p>
                    {{-- FIX 3/10: formato condicional, nunca "0k" --}}
                    <p class="text-xl md:text-2xl font-black text-gray-900 mt-1 leading-none">
                        {{ fmtGs($todayRevenue) }}<span class="text-xs font-medium text-gray-400 ml-0.5">Gs</span>
                    </p>
                    {{-- FIX 7: tendencia vs ayer --}}
                    @if($todayTrend !== null)
                        <p class="text-xs mt-1.5 font-bold {{ $todayTrend >= 0 ? 'text-emerald-600' : 'text-red-500' }} leading-none">
                            {{ $todayTrend >= 0 ? '↑' : '↓' }} {{ abs($todayTrend) }}% vs ayer
                        </p>
                    @else
                        <p class="text-xs text-gray-400 mt-1.5 leading-none">{{ $todayOrders }} pedidos hoy</p>
                    @endif
                </div>
                <div class="bg-emerald-100 rounded-xl p-2 md:p-2.5 text-base md:text-xl flex-shrink-0">💰</div>
            </div>
        </div>

        {{-- Ventas del mes --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4 md:p-5">
            <div class="flex items-start justify-between gap-2">
                <div class="min-w-0 flex-1">
                    <p class="text-xs font-bold text-gray-400 uppercase tracking-wider">Mes Actual</p>
                    <p class="text-xl md:text-2xl font-black text-purple-600 mt-1 leading-none">
                        {{ fmtGs($monthRevenue) }}<span class="text-xs font-medium text-purple-300 ml-0.5">Gs</span>
                    </p>
                    {{-- FIX 1: egresos del mes separados Gs/BRL --}}
                    <div class="mt-1.5 space-y-0.5">
                        @if($monthExpensesGs > 0)
                            <p class="text-xs text-red-400 font-semibold leading-none">-{{ fmtGs($monthExpensesGs) }} egresos</p>
                        @endif
                        @if($monthExpensesBrl > 0)
                            <p class="text-xs text-blue-400 font-semibold leading-none">-R$ {{ number_format($monthExpensesBrl, 0, ',', '.') }}</p>
                        @endif
                        @if($monthExpensesGs == 0 && $monthExpensesBrl == 0)
                            <p class="text-xs text-gray-400 leading-none">Sin egresos</p>
                        @endif
                    </div>
                </div>
                <div class="bg-purple-100 rounded-xl p-2 md:p-2.5 text-base md:text-xl flex-shrink-0">📈</div>
            </div>
        </div>

        {{-- Pedidos activos --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4 md:p-5">
            <div class="flex items-start justify-between gap-2">
                <div class="min-w-0 flex-1">
                    <p class="text-xs font-bold text-gray-400 uppercase tracking-wider">Activos</p>
                    {{-- FIX 2: cuenta todos los estados activos, no solo pending --}}
                    <p class="text-xl md:text-2xl font-black {{ $pendingOrders > 0 ? 'text-amber-500' : 'text-gray-900' }} mt-1 leading-none">
                        {{ $pendingOrders }}
                    </p>
                    <p class="text-xs text-gray-400 mt-1.5 leading-none">{{ $todayOrders }} pedidos hoy</p>
                </div>
                <div class="bg-amber-100 rounded-xl p-2 md:p-2.5 text-base md:text-xl flex-shrink-0">⏳</div>
            </div>
        </div>

        {{-- FIX 8/12: Neto del mes reemplaza "Clientes" --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4 md:p-5">
            <div class="flex items-start justify-between gap-2">
                <div class="min-w-0 flex-1">
                    <p class="text-xs font-bold text-gray-400 uppercase tracking-wider">Neto Mes</p>
                    <p class="text-xl md:text-2xl font-black {{ $monthNet >= 0 ? 'text-emerald-600' : 'text-red-500' }} mt-1 leading-none">
                        @if($monthNet < 0)-@endif{{ fmtGs(abs($monthNet)) }}<span class="text-xs font-medium {{ $monthNet >= 0 ? 'text-emerald-300' : 'text-red-300' }} ml-0.5">Gs</span>
                    </p>
                    <p class="text-xs text-gray-400 mt-1.5 leading-none">{{ $totalCustomers }} clientes</p>
                </div>
                <div class="{{ $monthNet >= 0 ? 'bg-emerald-100' : 'bg-red-100' }} rounded-xl p-2 md:p-2.5 text-base md:text-xl flex-shrink-0">
                    {{ $monthNet >= 0 ? '✅' : '⚠️' }}
                </div>
            </div>
        </div>

    </div>

    {{-- ══════════════════════════════════════════════════════
         PEDIDOS RECIENTES + TOP PRODUCTOS
         FIX 11: statusMap incluye 'ready'
         FIX 13/17: layout de fila en mobile no desborda
         FIX 18: números de revenue formateados en k/M
         FIX 4: top productos del mes actual
    ══════════════════════════════════════════════════════ --}}
    <div class="grid grid-cols-1 lg:grid-cols-5 gap-4 md:gap-5">

        {{-- Pedidos recientes (3/5) --}}
        <div class="lg:col-span-3 bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="px-4 md:px-5 py-3.5 md:py-4 border-b border-gray-100 flex items-center justify-between">
                <h3 class="font-black text-gray-900 text-sm md:text-base">🛒 Pedidos Recientes</h3>
                <a href="{{ route('admin.orders') }}"
                   class="text-xs font-bold text-purple-600 hover:text-purple-700 transition-colors">
                    Ver todos →
                </a>
            </div>

            @forelse($recentOrders as $order)
                @php
                    // FIX 11: 'ready' agregado — antes mostraba status raw
                    $statusMap = [
                        'pending'   => ['label' => 'Pendiente',  'class' => 'bg-amber-100 text-amber-700'],
                        'confirmed' => ['label' => 'Confirmado', 'class' => 'bg-blue-100 text-blue-700'],
                        'preparing' => ['label' => 'Preparando', 'class' => 'bg-purple-100 text-purple-700'],
                        'ready'     => ['label' => 'Listo',      'class' => 'bg-teal-100 text-teal-700'],
                        'delivered' => ['label' => 'Entregado',  'class' => 'bg-green-100 text-green-700'],
                        'cancelled' => ['label' => 'Cancelado',  'class' => 'bg-red-100 text-red-600'],
                    ];
                    $st = $statusMap[$order->status] ?? ['label' => ucfirst($order->status), 'class' => 'bg-gray-100 text-gray-600'];
                    $orderTotal = (float) $order->total;
                @endphp
                <div class="px-4 md:px-5 py-3 border-b border-gray-100 last:border-0 hover:bg-gray-50 transition-colors">
                    {{-- FIX 13/17: en mobile la info se distribuye en 2 filas para no cortar --}}
                    <div class="flex items-center gap-2.5">
                        <div class="w-7 h-7 md:w-8 md:h-8 rounded-full bg-purple-100 flex items-center justify-center text-xs font-black text-purple-600 flex-shrink-0">
                            {{ strtoupper(substr($order->customer_name ?? 'C', 0, 1)) }}
                        </div>
                        <div class="flex-1 min-w-0">
                            {{-- Fila 1: número y nombre --}}
                            <div class="flex items-baseline gap-1.5 min-w-0">
                                <span class="text-sm font-black text-gray-900 flex-shrink-0">#{{ $order->order_number }}</span>
                                <span class="text-xs text-gray-400 truncate">{{ $order->customer_name ?? 'Consumidor Final' }}</span>
                            </div>
                            {{-- Fila 2: tiempo y badge --}}
                            <div class="flex items-center gap-1.5 mt-0.5">
                                <span class="text-xs text-gray-400 flex-shrink-0">{{ $order->created_at->diffForHumans() }}</span>
                            </div>
                        </div>
                        {{-- Monto y badge a la derecha --}}
                        <div class="flex flex-col items-end gap-1 flex-shrink-0">
                            <span class="text-sm font-black text-gray-900">
                                {{ fmtGs($orderTotal) }}<span class="text-xs font-normal text-gray-400"> Gs</span>
                            </span>
                            <span class="text-xs font-bold px-2 py-0.5 rounded-full whitespace-nowrap {{ $st['class'] }}">
                                {{ $st['label'] }}
                            </span>
                        </div>
                    </div>
                </div>
            @empty
                <div class="px-5 py-12 text-center text-gray-400">
                    <div class="text-4xl mb-2">🛒</div>
                    <p class="font-medium text-sm">Sin pedidos todavía</p>
                </div>
            @endforelse
        </div>

        {{-- Top productos del mes (2/5) --}}
        <div class="lg:col-span-2 bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="px-4 md:px-5 py-3.5 md:py-4 border-b border-gray-100">
                <h3 class="font-black text-gray-900 text-sm md:text-base">🏆 Top Productos</h3>
                {{-- FIX 4: indica que es del mes actual --}}
                <p class="text-xs text-gray-400 mt-0.5">Por ingresos · {{ now()->isoFormat('MMMM') }}</p>
            </div>

            @forelse($topProducts as $i => $product)
                @php
                    $maxRevenue = (float) ($topProducts->first()?->total_revenue ?? 1);
                    $rev        = (float) $product->total_revenue;
                    $pct        = $maxRevenue > 0 ? ($rev / $maxRevenue * 100) : 0;
                @endphp
                <div class="px-4 md:px-5 py-3 border-b border-gray-100 last:border-0 hover:bg-gray-50 transition-colors">
                    <div class="flex items-center gap-2.5">
                        <span class="text-sm font-black text-gray-200 w-4 text-center flex-shrink-0">{{ $i + 1 }}</span>
                        <div class="flex-1 min-w-0">
                            <div class="text-sm font-bold text-gray-900 truncate">{{ $product->name }}</div>
                            <div class="h-1.5 bg-gray-100 rounded-full mt-1.5 overflow-hidden">
                                <div class="h-full bg-gradient-to-r from-purple-400 to-indigo-500 rounded-full"
                                     style="width: {{ $pct }}%"></div>
                            </div>
                        </div>
                        {{-- FIX 18: formato k/M para no desbordar en columna pequeña --}}
                        <div class="text-right flex-shrink-0 min-w-[52px]">
                            <div class="text-xs font-black text-gray-800 leading-none">
                                {{ fmtGs($rev) }} Gs
                            </div>
                            <div class="text-xs text-gray-400 mt-0.5">×{{ $product->total_qty }}</div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="px-5 py-12 text-center text-gray-400">
                    <div class="text-4xl mb-2">📦</div>
                    <p class="font-medium text-sm">Sin ventas este mes</p>
                </div>
            @endforelse

            @if($topProducts->count() > 0)
                <div class="px-4 md:px-5 py-3 bg-gray-50 border-t border-gray-100">
                    <a href="{{ route('admin.reports') }}"
                       class="text-xs font-bold text-purple-600 hover:text-purple-700 transition-colors">
                        Ver reportes completos →
                    </a>
                </div>
            @endif
        </div>
    </div>

    {{-- ══════════════════════════════════════════════════════
         RESUMEN DE CAJA DEL DÍA
         FIX 5/14: source NULL manejado — ya viene como 'pos' por COALESCE en query
         FIX 19: md:grid-cols-2 para tablet (antes quedaba en 1 col muy ancho)
         FIX 20/21: borders y paddings unificados
    ══════════════════════════════════════════════════════ --}}
    @if($todayByPayment->count() > 0 || $todayBySource->count() > 0)
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 md:gap-5">

        {{-- Por método de pago --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="px-4 md:px-5 py-3.5 md:py-4 border-b border-gray-100 flex items-center gap-2.5">
                <span class="text-lg">💳</span>
                <div>
                    <h3 class="font-black text-gray-900 text-sm md:text-base">Cobros de Hoy</h3>
                    <p class="text-xs text-gray-400">Por método de pago</p>
                </div>
            </div>

            <div class="divide-y divide-gray-100">
                @forelse($todayByPayment as $pm)
                    <div class="px-4 md:px-5 py-3 flex items-center justify-between gap-3 hover:bg-gray-50 transition-colors">
                        <div class="flex items-center gap-2.5 min-w-0">
                            <div class="w-7 h-7 md:w-8 md:h-8 rounded-full bg-purple-100 flex items-center justify-center flex-shrink-0">
                                <span class="text-xs font-black text-purple-600">
                                    {{ strtoupper(substr($pm['name'], 0, 2)) }}
                                </span>
                            </div>
                            <div class="min-w-0">
                                <div class="text-sm font-bold text-gray-900 truncate">{{ $pm['name'] }}</div>
                                <div class="text-xs text-gray-400">{{ $pm['count'] }} {{ $pm['count'] == 1 ? 'venta' : 'ventas' }}</div>
                            </div>
                        </div>
                        <div class="text-right flex-shrink-0">
                            <div class="text-sm font-black text-purple-700">
                                {{ fmtGs((float) $pm['total']) }} Gs
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="px-5 py-8 text-center text-gray-400 text-sm">Sin cobros hoy</div>
                @endforelse
            </div>

            @if($todayByPayment->count() > 1)
                @php $totalPagos = (float) collect($todayByPayment)->sum('total'); @endphp
                <div class="px-4 md:px-5 py-3 bg-purple-50 border-t border-purple-100 flex justify-between items-center">
                    <span class="text-xs md:text-sm font-bold text-purple-800">TOTAL COBRADO</span>
                    <span class="text-sm md:text-base font-black text-purple-800">
                        {{ fmtGs($totalPagos) }} Gs
                    </span>
                </div>
            @endif
        </div>

        {{-- Por canal --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="px-4 md:px-5 py-3.5 md:py-4 border-b border-gray-100 flex items-center gap-2.5">
                <span class="text-lg">📊</span>
                <div>
                    <h3 class="font-black text-gray-900 text-sm md:text-base">Ventas por Canal</h3>
                    <p class="text-xs text-gray-400">Tienda · Web · App — hoy</p>
                </div>
            </div>

            @php
                // FIX 5/14: source null ya viene como 'pos' por COALESCE en la query
                $sourceConfig = [
                    'pos'          => ['label' => 'Tienda (POS)',  'icon' => '🏪', 'bg' => 'bg-green-100',  'text' => 'text-green-800'],
                    'web'          => ['label' => 'Web',           'icon' => '🌐', 'bg' => 'bg-blue-100',   'text' => 'text-blue-800'],
                    'delivery_app' => ['label' => 'Delivery App',  'icon' => '🛵', 'bg' => 'bg-orange-100', 'text' => 'text-orange-800'],
                ];
                $totalCanales = (float) $todayBySource->sum('total');
            @endphp

            <div class="divide-y divide-gray-100">
                @forelse($todayBySource as $src)
                    @php
                        $cfg     = $sourceConfig[$src->source] ?? ['label' => ucfirst($src->source ?? 'Tienda'), 'icon' => '📦', 'bg' => 'bg-gray-100', 'text' => 'text-gray-700'];
                        $srcTot  = (float) $src->total;
                        $pct     = $totalCanales > 0 ? round($srcTot / $totalCanales * 100) : 0;
                    @endphp
                    <div class="px-4 md:px-5 py-3 hover:bg-gray-50 transition-colors">
                        <div class="flex items-center justify-between gap-2 mb-1.5">
                            <div class="flex items-center gap-2 min-w-0">
                                <span class="text-base flex-shrink-0">{{ $cfg['icon'] }}</span>
                                <span class="text-sm font-bold text-gray-900 truncate">{{ $cfg['label'] }}</span>
                                <span class="text-xs {{ $cfg['bg'] }} {{ $cfg['text'] }} px-1.5 py-0.5 rounded-full font-semibold flex-shrink-0">
                                    {{ $src->count }}
                                </span>
                            </div>
                            <span class="text-sm font-black text-gray-800 flex-shrink-0">
                                {{ fmtGs($srcTot) }} Gs
                            </span>
                        </div>
                        <div class="h-1.5 bg-gray-100 rounded-full overflow-hidden">
                            <div class="h-full bg-gradient-to-r from-purple-400 to-indigo-500 rounded-full"
                                 style="width: {{ $pct }}%"></div>
                        </div>
                        <div class="text-xs text-gray-400 mt-0.5 text-right">{{ $pct }}%</div>
                    </div>
                @empty
                    <div class="px-5 py-8 text-center text-gray-400 text-sm">Sin ventas hoy</div>
                @endforelse
            </div>

            @if($totalCanales > 0)
                <div class="px-4 md:px-5 py-3 bg-indigo-50 border-t border-indigo-100 flex justify-between items-center">
                    <span class="text-xs md:text-sm font-bold text-indigo-800">TOTAL DEL DÍA</span>
                    <span class="text-sm md:text-base font-black text-indigo-800">
                        {{ fmtGs($totalCanales) }} Gs
                    </span>
                </div>
            @endif
        </div>

    </div>
    @endif

</div>