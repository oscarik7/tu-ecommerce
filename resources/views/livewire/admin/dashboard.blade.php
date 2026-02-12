<div class="space-y-6">

    {{-- ══ HEADER ══ --}}
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-black text-gray-900">👋 Dashboard</h1>
            <p class="text-gray-500 text-sm mt-0.5">{{ now()->isoFormat('dddd D [de] MMMM, YYYY') }}</p>
        </div>
        @if($openRegister)
            <a href="{{ route('admin.cash-registers') }}"
               class="flex items-center gap-2 bg-green-100 hover:bg-green-200 text-green-800 font-bold px-4 py-2 rounded-xl text-sm transition-all">
                <span class="w-2 h-2 rounded-full bg-green-500 animate-pulse"></span>
                Caja Abierta
            </a>
        @else
            <a href="{{ route('admin.cash-registers') }}"
               class="flex items-center gap-2 bg-red-100 hover:bg-red-200 text-red-700 font-bold px-4 py-2 rounded-xl text-sm transition-all">
                <span class="w-2 h-2 rounded-full bg-red-500"></span>
                Caja Cerrada
            </a>
        @endif
    </div>

    {{-- ══ TARJETAS PRINCIPALES ══ --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">

        {{-- Ventas hoy --}}
        <div class="bg-white rounded-2xl shadow-sm border p-5">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-xs font-bold text-gray-400 uppercase tracking-wider">Ventas Hoy</p>
                    <p class="text-2xl font-black text-gray-900 mt-1">
                        {{ number_format($todayRevenue / 1000, 0) }}k
                        <span class="text-sm font-medium text-gray-400">Gs</span>
                    </p>
                </div>
                <div class="bg-green-100 rounded-xl p-2.5 text-xl">💰</div>
            </div>
        </div>

        {{-- Ventas del mes --}}
        <div class="bg-white rounded-2xl shadow-sm border p-5">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-xs font-bold text-gray-400 uppercase tracking-wider">Ventas del Mes</p>
                    <p class="text-2xl font-black text-purple-600 mt-1">
                        {{ number_format($monthRevenue / 1000, 0) }}k
                        <span class="text-sm font-medium text-purple-300">Gs</span>
                    </p>
                    @if($monthExpenses > 0)
                        <p class="text-xs text-red-400 mt-1">-{{ number_format($monthExpenses / 1000, 0) }}k egresos</p>
                    @endif
                </div>
                <div class="bg-purple-100 rounded-xl p-2.5 text-xl">📈</div>
            </div>
        </div>

        {{-- Pedidos pendientes --}}
        <div class="bg-white rounded-2xl shadow-sm border p-5">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-xs font-bold text-gray-400 uppercase tracking-wider">Pendientes</p>
                    <p class="text-2xl font-black {{ $pendingOrders > 0 ? 'text-amber-500' : 'text-gray-900' }} mt-1">
                        {{ $pendingOrders }}
                    </p>
                    <p class="text-xs text-gray-400 mt-1">de {{ $totalOrders }} totales</p>
                </div>
                <div class="bg-amber-100 rounded-xl p-2.5 text-xl">⏳</div>
            </div>
        </div>

        {{-- Clientes --}}
        <div class="bg-white rounded-2xl shadow-sm border p-5">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-xs font-bold text-gray-400 uppercase tracking-wider">Clientes</p>
                    <p class="text-2xl font-black text-blue-600 mt-1">{{ $totalCustomers }}</p>
                </div>
                <div class="bg-blue-100 rounded-xl p-2.5 text-xl">👥</div>
            </div>
        </div>
    </div>

    {{-- ══ CONTENIDO PRINCIPAL ══ --}}
    <div class="grid grid-cols-1 lg:grid-cols-5 gap-5">

        {{-- Pedidos recientes (3/5) --}}
        <div class="lg:col-span-3 bg-white rounded-2xl shadow-sm border overflow-hidden">
            <div class="px-5 py-4 border-b flex items-center justify-between">
                <h3 class="font-black text-gray-900">🛒 Pedidos Recientes</h3>
                <a href="{{ route('admin.orders') }}"
                   class="text-xs font-bold text-purple-600 hover:text-purple-700 transition-colors">
                    Ver todos →
                </a>
            </div>

            @forelse($recentOrders as $order)
                @php
                    $statusMap = [
                        'pending'   => ['label' => 'Pendiente',  'class' => 'bg-amber-100 text-amber-700'],
                        'confirmed' => ['label' => 'Confirmado', 'class' => 'bg-blue-100 text-blue-700'],
                        'preparing' => ['label' => 'Preparando', 'class' => 'bg-purple-100 text-purple-700'],
                        'delivered' => ['label' => 'Entregado',  'class' => 'bg-green-100 text-green-700'],
                        'cancelled' => ['label' => 'Cancelado',  'class' => 'bg-red-100 text-red-600'],
                    ];
                    $st = $statusMap[$order->status] ?? ['label' => $order->status, 'class' => 'bg-gray-100 text-gray-600'];
                @endphp
                <div class="px-5 py-3.5 border-b last:border-0 hover:bg-gray-50 transition-colors">
                    <div class="flex items-center justify-between gap-3">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-full bg-purple-100 flex items-center justify-center text-xs font-black text-purple-600 flex-shrink-0">
                                {{ strtoupper(substr($order->customer_name ?? 'C', 0, 1)) }}
                            </div>
                            <div>
                                <div class="text-sm font-bold text-gray-900">#{{ $order->order_number }}</div>
                                <div class="text-xs text-gray-400">
                                    {{ $order->customer_name ?? 'Consumidor Final' }}
                                    · {{ $order->created_at->diffForHumans() }}
                                </div>
                            </div>
                        </div>
                        <div class="flex items-center gap-2 flex-shrink-0">
                            <span class="text-sm font-black text-gray-900">
                                {{ number_format($order->total, 0, ',', '.') }} Gs
                            </span>
                            <span class="text-xs font-bold px-2 py-1 rounded-full {{ $st['class'] }}">
                                {{ $st['label'] }}
                            </span>
                        </div>
                    </div>
                </div>
            @empty
                <div class="px-5 py-12 text-center text-gray-400">
                    <div class="text-4xl mb-2">🛒</div>
                    <p class="font-medium">Sin pedidos todavía</p>
                </div>
            @endforelse
        </div>

        {{-- Top productos (2/5) --}}
        <div class="lg:col-span-2 bg-white rounded-2xl shadow-sm border overflow-hidden">
            <div class="px-5 py-4 border-b">
                <h3 class="font-black text-gray-900">🏆 Top Productos</h3>
                <p class="text-xs text-gray-400 mt-0.5">Por ingresos generados</p>
            </div>

            @forelse($topProducts as $i => $product)
                @php
                    $maxRevenue = $topProducts->first()?->total_revenue ?? 1;
                    $pct = $maxRevenue > 0 ? ($product->total_revenue / $maxRevenue * 100) : 0;
                @endphp
                <div class="px-5 py-3.5 border-b last:border-0 hover:bg-gray-50 transition-colors">
                    <div class="flex items-center gap-3">
                        <span class="text-base font-black text-gray-200 w-5 text-center flex-shrink-0">
                            {{ $i + 1 }}
                        </span>
                        <div class="flex-1 min-w-0">
                            <div class="text-sm font-bold text-gray-900 truncate">{{ $product->name }}</div>
                            <div class="h-1.5 bg-gray-100 rounded-full mt-1.5 overflow-hidden">
                                <div class="h-full bg-gradient-to-r from-purple-400 to-indigo-500 rounded-full transition-all"
                                     style="width: {{ $pct }}%"></div>
                            </div>
                        </div>
                        <div class="text-right flex-shrink-0">
                            <div class="text-xs font-black text-gray-800">
                                {{ number_format($product->total_revenue, 0, ',', '.') }} Gs
                            </div>
                            <div class="text-xs text-gray-400">×{{ $product->total_qty }}</div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="px-5 py-12 text-center text-gray-400">
                    <div class="text-4xl mb-2">📦</div>
                    <p class="font-medium">Sin ventas registradas</p>
                </div>
            @endforelse

            @if($topProducts->count() > 0)
                <div class="px-5 py-3 bg-gray-50 border-t">
                    <a href="{{ route('admin.reports') }}"
                       class="text-xs font-bold text-purple-600 hover:text-purple-700 transition-colors">
                        Ver reportes completos →
                    </a>
                </div>
            @endif
        </div>
    </div>

</div>
