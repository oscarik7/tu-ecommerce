<div class="min-h-screen bg-gradient-to-br from-purple-900 via-purple-800 to-indigo-900 p-6">

    {{-- ══ HEADER + STATS ══ --}}
    <div class="mb-6">
        <div class="bg-white/10 backdrop-blur-md rounded-xl p-6 shadow-xl">
            <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center gap-4">
                <div>
                    <h1 class="text-3xl font-bold text-white mb-2">📋 Gestión de Pedidos</h1>
                    <p class="text-purple-200">Tienda · Web · Delivery App</p>
                </div>

                <div class="flex flex-wrap gap-3">
                    <div class="bg-white/20 rounded-lg px-4 py-3 text-center min-w-[90px]">
                        <div class="text-2xl font-bold text-white">{{ number_format($stats['today_total'], 0, ',', '.') }}</div>
                        <div class="text-xs text-purple-200">Gs Hoy</div>
                    </div>
                    <div class="bg-green-500/30 rounded-lg px-4 py-3 text-center min-w-[70px]">
                        <div class="text-2xl font-bold text-white">{{ $stats['today_pos'] }}</div>
                        <div class="text-xs text-green-200">🏪 Tienda</div>
                    </div>
                    <div class="bg-blue-500/30 rounded-lg px-4 py-3 text-center min-w-[70px]">
                        <div class="text-2xl font-bold text-white">{{ $stats['today_web'] }}</div>
                        <div class="text-xs text-blue-200">🌐 Web</div>
                    </div>
                    @if($stats['today_app'] > 0)
                        <div class="bg-orange-500/30 rounded-lg px-4 py-3 text-center min-w-[70px]">
                            <div class="text-2xl font-bold text-white">{{ $stats['today_app'] }}</div>
                            <div class="text-xs text-orange-200">🛵 App</div>
                        </div>
                    @endif
                    @if($stats['pending_count'] > 0)
                        <div class="bg-yellow-500/30 rounded-lg px-4 py-3 text-center min-w-[70px] animate-pulse">
                            <div class="text-2xl font-bold text-white">{{ $stats['pending_count'] }}</div>
                            <div class="text-xs text-yellow-200">⏳ Pendientes</div>
                        </div>
                    @endif
                    @if($stats['cancelled_today'] > 0)
                        <div class="bg-red-500/30 rounded-lg px-4 py-3 text-center min-w-[70px]">
                            <div class="text-2xl font-bold text-white">{{ $stats['cancelled_today'] }}</div>
                            <div class="text-xs text-red-200">❌ Anuladas</div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- ══ MENSAJES FLASH ══ --}}
    @if(session()->has('message'))
        <div class="bg-green-500 text-white px-6 py-4 rounded-xl mb-6 shadow-xl flex items-center gap-2">
            <span class="text-xl">✓</span> <span class="font-semibold">{{ session('message') }}</span>
        </div>
    @endif
    @if(session()->has('error'))
        <div class="bg-red-500 text-white px-6 py-4 rounded-xl mb-6 shadow-xl flex items-center gap-2">
            <span class="text-xl">✕</span> <span class="font-semibold">{{ session('error') }}</span>
        </div>
    @endif

    {{-- ══ TABS + FILTROS ══ --}}
    <div class="bg-white/10 backdrop-blur-md rounded-xl p-4 mb-4 shadow-xl">
        <div class="flex flex-wrap gap-2 mb-4">
            <button wire:click="setViewMode('active')"
                class="px-4 py-2 rounded-lg font-bold text-sm transition-all {{ $viewMode === 'active' ? 'bg-white text-purple-900' : 'bg-white/20 text-white hover:bg-white/30' }}">
                📌 Pedidos Activos
            </button>
            <button wire:click="setViewMode('all')"
                class="px-4 py-2 rounded-lg font-bold text-sm transition-all {{ $viewMode === 'all' ? 'bg-white text-purple-900' : 'bg-white/20 text-white hover:bg-white/30' }}">
                📚 Historial Completo
            </button>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-6 gap-3">
            <div class="lg:col-span-2">
                <input wire:model.live.debounce.300ms="search" type="text"
                    placeholder="🔍 Número, cliente, teléfono o ref. app..."
                    class="w-full px-4 py-2 bg-white rounded-lg text-gray-900 placeholder-gray-500 focus:ring-2 focus:ring-purple-500 focus:outline-none text-sm">
            </div>
            <div>
                <select wire:model.live="filterSource"
                    class="w-full px-4 py-2 bg-white rounded-lg text-gray-900 focus:ring-2 focus:ring-purple-500 text-sm">
                    <option value="">📦 Todos los orígenes</option>
                    <option value="pos">🏪 Tienda (POS)</option>
                    <option value="web">🌐 Web (E-commerce)</option>
                    <option value="delivery_app">🛵 Delivery App</option>
                </select>
            </div>
            <div>
                <select wire:model.live="filterStatus"
                    class="w-full px-4 py-2 bg-white rounded-lg text-gray-900 focus:ring-2 focus:ring-purple-500 text-sm">
                    <option value="">📊 Todos los estados</option>
                    <option value="pending">⏳ Pendiente</option>
                    <option value="confirmed">✅ Confirmado</option>
                    <option value="preparing">👨‍🍳 Preparando</option>
                    <option value="ready">✨ Listo</option>
                    <option value="delivering">🚚 En Camino</option>
                    <option value="delivered">🎉 Entregado</option>
                    <option value="cancelled">❌ Cancelado</option>
                </select>
            </div>
            <div>
                <input wire:model.live="filterDateFrom" type="date"
                    class="w-full px-4 py-2 bg-white rounded-lg text-gray-900 focus:ring-2 focus:ring-purple-500 text-sm">
            </div>
            <div>
                <input wire:model.live="filterDateTo" type="date"
                    class="w-full px-4 py-2 bg-white rounded-lg text-gray-900 focus:ring-2 focus:ring-purple-500 text-sm">
            </div>
        </div>

        @if($search || $filterStatus || $filterSource || $filterDateFrom || $filterDateTo)
            <div class="mt-3">
                <button wire:click="clearFilters" class="text-white/70 hover:text-white text-sm font-semibold">
                    ✕ Limpiar filtros
                </button>
            </div>
        @endif
    </div>

    {{-- ══ TABLA ══ --}}
    <div class="bg-white rounded-xl shadow-2xl overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gradient-to-r from-purple-600 to-indigo-600">
                    <tr>
                        <th class="px-4 py-4 text-left text-xs font-bold text-white uppercase tracking-wider">Pedido</th>
                        <th class="px-4 py-4 text-left text-xs font-bold text-white uppercase tracking-wider">Origen</th>
                        <th class="px-4 py-4 text-left text-xs font-bold text-white uppercase tracking-wider">Cliente</th>
                        <th class="px-4 py-4 text-left text-xs font-bold text-white uppercase tracking-wider">Entrega</th>
                        <th class="px-4 py-4 text-left text-xs font-bold text-white uppercase tracking-wider">Productos</th>
                        <th class="px-4 py-4 text-left text-xs font-bold text-white uppercase tracking-wider">Total</th>
                        <th class="px-4 py-4 text-left text-xs font-bold text-white uppercase tracking-wider">Estado</th>
                        <th class="px-4 py-4 text-left text-xs font-bold text-white uppercase tracking-wider">Acciones</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($orders as $order)
                        @php $source = $order->source ?? 'pos'; @endphp
                        <tr class="hover:bg-purple-50 transition-colors {{ $order->status === 'pending' ? 'bg-yellow-50' : '' }}">

                            {{-- Pedido --}}
                            <td class="px-4 py-4 whitespace-nowrap">
                                <div class="text-sm font-bold text-gray-900">#{{ $order->order_number }}</div>
                                <div class="text-xs text-gray-500">{{ $order->created_at->format('d/m/Y H:i') }}</div>
                                <div class="text-xs text-gray-400">{{ $order->created_at->diffForHumans() }}</div>
                            </td>

                            {{-- Origen --}}
                            <td class="px-4 py-4 whitespace-nowrap">
                                @if($source === 'pos')
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-green-100 text-green-800">
                                        🏪 Tienda
                                    </span>
                                @elseif($source === 'delivery_app')
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-orange-100 text-orange-800">
                                        🛵 {{ $order->delivery_app_name ?? 'App' }}
                                    </span>
                                    @if($order->delivery_app_order_id)
                                        <div class="text-xs text-gray-400 mt-1">Ref: {{ $order->delivery_app_order_id }}</div>
                                    @endif
                                @else
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-blue-100 text-blue-800">
                                        🌐 Web
                                    </span>
                                @endif
                            </td>

                            {{-- Cliente --}}
                            <td class="px-4 py-4">
                                <div class="text-sm font-semibold text-gray-900">
                                    {{ $order->customer_name }}
                                    @if(strtoupper($order->customer_name) === 'CONSUMIDOR FINAL')
                                        <span class="text-gray-400 text-xs font-normal">(anónimo)</span>
                                    @endif
                                </div>
                                @if($order->customer_phone)
                                    <div class="text-xs text-gray-600">{{ $order->customer_phone }}</div>
                                @endif
                                @if($order->user_id)
                                    <div class="text-xs text-purple-600">👤 Registrado</div>
                                @endif
                            </td>

                            {{-- Entrega --}}
                            <td class="px-4 py-4">
                                @if($source === 'pos')
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-bold bg-gray-100 text-gray-600">
                                        🏪 En tienda
                                    </span>
                                @elseif($source === 'delivery_app')
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-bold bg-orange-100 text-orange-700">
                                        🛵 App delivery
                                    </span>
                                @elseif($order->delivery_type === 'delivery')
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-bold bg-blue-100 text-blue-800">
                                        🚚 Delivery
                                    </span>
                                    @if($order->deliveryZone)
                                        <div class="text-xs text-gray-600 mt-1">{{ $order->deliveryZone->name }}</div>
                                    @endif
                                @else
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-bold bg-green-100 text-green-800">
                                        🏪 Retiro
                                    </span>
                                @endif
                            </td>

                            {{-- Productos --}}
                            <td class="px-4 py-4">
                                @php
                                    // Items por peso cuentan como 1 unidad visual
                                    $itemCount = $order->items->sum(fn($i) => $i->unit_type === 'weight' ? 1 : $i->quantity);
                                @endphp
                                <div class="text-sm font-semibold text-gray-900">{{ $itemCount }} item(s)</div>
                                <div class="text-xs text-gray-600">
                                    @foreach($order->items->take(2) as $item)
                                        @if($item->unit_type === 'weight')
                                            {{ Str::limit($item->product_name, 15) }} (⚖️ {{ number_format($item->weight, 2, ',', '.') }}kg)<br>
                                        @else
                                            {{ $item->quantity }}× {{ Str::limit($item->product_name, 15) }}<br>
                                        @endif
                                    @endforeach
                                    @if($order->items->count() > 2)
                                        <span class="text-purple-600 font-semibold">+{{ $order->items->count() - 2 }} más</span>
                                    @endif
                                </div>
                            </td>

                            {{-- Total --}}
                            <td class="px-4 py-4 whitespace-nowrap">
                                <div class="text-lg font-black text-purple-600">
                                    {{ number_format($order->total, 0, ',', '.') }} Gs
                                </div>
                                @if($source === 'delivery_app' && $order->delivery_app_commission)
                                    <div class="text-xs text-orange-600 font-semibold">
                                        -{{ number_format($order->delivery_app_commission, 0, ',', '.') }} comisión
                                    </div>
                                    <div class="text-xs text-green-700 font-semibold">
                                        = {{ number_format($order->total - $order->delivery_app_commission, 0, ',', '.') }} neto
                                    </div>
                                @elseif($order->delivery_cost > 0)
                                    <div class="text-xs text-gray-500">
                                        (inc. {{ number_format($order->delivery_cost, 0, ',', '.') }} delivery)
                                    </div>
                                @endif
                                <div class="text-xs text-gray-500">{{ $order->paymentMethod->name ?? 'N/A' }}</div>
                            </td>

                            {{-- Estado --}}
                            <td class="px-4 py-4">
                                @if(in_array($order->status, ['delivered', 'cancelled']))
                                    <span class="inline-flex items-center px-3 py-1.5 rounded-lg text-xs font-bold
                                        {{ $order->status === 'delivered' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                        {{ $order->status === 'delivered' ? '🎉 Entregado' : '❌ Cancelado' }}
                                    </span>
                                @else
                                    <select wire:change="updateStatus({{ $order->id }}, $event.target.value)"
                                        class="text-xs font-bold rounded-lg px-3 py-2 border-2 cursor-pointer focus:ring-2 focus:ring-purple-500
                                        {{ $order->status === 'pending'    ? 'bg-yellow-100 border-yellow-400 text-yellow-900' : '' }}
                                        {{ $order->status === 'confirmed'  ? 'bg-blue-100 border-blue-400 text-blue-900' : '' }}
                                        {{ $order->status === 'preparing'  ? 'bg-purple-100 border-purple-400 text-purple-900' : '' }}
                                        {{ $order->status === 'ready'      ? 'bg-green-100 border-green-400 text-green-900' : '' }}
                                        {{ $order->status === 'delivering' ? 'bg-orange-100 border-orange-400 text-orange-900' : '' }}">
                                        <option value="pending"    {{ $order->status === 'pending' ? 'selected' : '' }}>⏳ Pendiente</option>
                                        <option value="confirmed"  {{ $order->status === 'confirmed' ? 'selected' : '' }}>✅ Confirmado</option>
                                        <option value="preparing"  {{ $order->status === 'preparing' ? 'selected' : '' }}>👨‍🍳 Preparando</option>
                                        <option value="ready"      {{ $order->status === 'ready' ? 'selected' : '' }}>✨ Listo</option>
                                        <option value="delivering" {{ $order->status === 'delivering' ? 'selected' : '' }}>🚚 En Camino</option>
                                        <option value="delivered"  {{ $order->status === 'delivered' ? 'selected' : '' }}>🎉 Entregado</option>
                                        <option value="cancelled"  {{ $order->status === 'cancelled' ? 'selected' : '' }}>❌ Cancelado</option>
                                    </select>
                                @endif

                                @if($order->notes)
                                    <div class="mt-2 text-xs bg-yellow-100 border border-yellow-400 rounded px-2 py-1 text-yellow-900">
                                        📝 Tiene notas
                                    </div>
                                @endif
                            </td>

                            {{-- Acciones --}}
                            <td class="px-4 py-4 whitespace-nowrap">
                                <div class="flex gap-2">
                                    <button wire:click="showOrder({{ $order->id }})"
                                        class="bg-purple-600 hover:bg-purple-700 text-white font-bold px-3 py-2 rounded-lg transition-all" title="Ver detalles">
                                        👁️
                                    </button>
                                    <button wire:click="printTicket({{ $order->id }})"
                                        class="bg-blue-600 hover:bg-blue-700 text-white font-bold px-3 py-2 rounded-lg transition-all" title="Imprimir ticket">
                                        🖨️
                                    </button>
                                    @if($order->customer_phone)
                                        <button wire:click="sendToCustomer({{ $order->id }})"
                                            class="bg-green-600 hover:bg-green-700 text-white font-bold px-3 py-2 rounded-lg transition-all" title="WhatsApp al cliente">
                                            📱
                                        </button>
                                    @endif
                                    @if($order->status !== 'cancelled')
                                        <button onclick="confirmCancel({{ $order->id }}, '{{ $order->order_number }}')"
                                            class="bg-red-600 hover:bg-red-700 text-white font-bold px-3 py-2 rounded-lg transition-all" title="Anular venta">
                                            ❌
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-6 py-12 text-center">
                                <div class="text-6xl mb-4">{{ $viewMode === 'active' ? '😴' : '📭' }}</div>
                                <div class="text-2xl font-bold text-gray-600">
                                    {{ $viewMode === 'active' ? 'No hay pedidos activos' : 'No se encontraron pedidos' }}
                                </div>
                                <div class="text-gray-400 mt-2">
                                    {{ ($search || $filterStatus || $filterSource || $filterDateFrom || $filterDateTo) ? 'Intentá con otros filtros' : 'Los nuevos pedidos aparecerán aquí' }}
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($orders->hasPages())
            <div class="px-6 py-4 bg-gray-50 border-t">
                {{ $orders->links() }}
            </div>
        @endif
    </div>

    {{-- ══ MODAL DETALLE ══ --}}
    @if($selectedOrder)
        @php $selSource = $selectedOrder->source ?? 'pos'; @endphp
        <div class="fixed inset-0 bg-black/80 backdrop-blur-sm flex items-center justify-center p-4 z-50" wire:click="closeModal">
            <div class="bg-white rounded-3xl max-w-4xl w-full max-h-[90vh] overflow-y-auto shadow-2xl" wire:click.stop>
                <div class="p-8">

                    {{-- Header --}}
                    <div class="flex justify-between items-start mb-6">
                        <div>
                            <h2 class="text-3xl font-black text-gray-900">Pedido #{{ $selectedOrder->order_number }}</h2>
                            <div class="flex gap-2 mt-2 flex-wrap">
                                @if($selSource === 'pos')
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-bold bg-green-100 text-green-800">🏪 Venta en Tienda</span>
                                @elseif($selSource === 'delivery_app')
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-bold bg-orange-100 text-orange-800">
                                        🛵 {{ $selectedOrder->delivery_app_name ?? 'Delivery App' }}
                                    </span>
                                    @if($selectedOrder->delivery_app_order_id)
                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm bg-gray-100 text-gray-600">
                                            Ref: {{ $selectedOrder->delivery_app_order_id }}
                                        </span>
                                    @endif
                                @else
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-bold bg-blue-100 text-blue-800">🌐 Venta Web</span>
                                @endif
                            </div>
                        </div>
                        <button wire:click="closeModal" class="text-gray-500 hover:text-gray-700">
                            <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                        {{-- Cliente --}}
                        <div class="bg-gradient-to-br from-purple-50 to-indigo-50 rounded-xl p-6">
                            <h3 class="font-black text-xl mb-4 text-purple-900">👤 Cliente</h3>
                            <div class="space-y-2">
                                <p><span class="font-bold">Nombre:</span> {{ $selectedOrder->customer_name }}</p>
                                @if($selectedOrder->customer_phone)
                                    <p><span class="font-bold">Teléfono:</span> {{ $selectedOrder->customer_phone }}</p>
                                @endif
                                @if($selectedOrder->user)
                                    <p><span class="font-bold">Email:</span> {{ $selectedOrder->user->email }}</p>
                                    <p class="text-sm text-purple-600">✓ Cliente registrado</p>
                                @else
                                    <p class="text-sm text-gray-500">Cliente no registrado</p>
                                @endif
                            </div>
                        </div>

                        {{-- Info del pedido --}}
                        <div class="bg-gradient-to-br from-blue-50 to-cyan-50 rounded-xl p-6">
                            <h3 class="font-black text-xl mb-4 text-blue-900">📋 Información</h3>
                            <div class="space-y-2">
                                <p><span class="font-bold">Fecha:</span> {{ $selectedOrder->created_at->format('d/m/Y H:i') }}</p>
                                <p><span class="font-bold">Estado:</span> {{ ucfirst($selectedOrder->status) }}</p>
                                <p><span class="font-bold">Pago:</span> {{ $selectedOrder->paymentMethod->name ?? 'N/A' }}</p>
                                <p>
                                    <span class="font-bold">Estado Pago:</span>
                                    <span class="{{ $selectedOrder->payment_status === 'paid' ? 'text-green-600' : 'text-yellow-600' }}">
                                        {{ $selectedOrder->payment_status === 'paid' ? '✓ Pagado' : '⏳ Pendiente' }}
                                    </span>
                                </p>
                                @if($selectedOrder->cashRegister)
                                    <p><span class="font-bold">Caja:</span> {{ $selectedOrder->cashRegister->name ?? '#' . $selectedOrder->cashRegister->id }}</p>
                                @endif
                            </div>
                        </div>
                    </div>

                    {{-- Info de Delivery App --}}
                    @if($selSource === 'delivery_app')
                        <div class="bg-orange-50 border-2 border-orange-200 rounded-xl p-6 mb-6">
                            <h3 class="font-black text-xl mb-4 text-orange-900">🛵 Datos de la App</h3>
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <p class="text-sm text-gray-600">App:</p>
                                    <p class="font-bold">{{ $selectedOrder->delivery_app_name ?? '—' }}</p>
                                </div>
                                @if($selectedOrder->delivery_app_order_id)
                                    <div>
                                        <p class="text-sm text-gray-600">Referencia:</p>
                                        <p class="font-bold">{{ $selectedOrder->delivery_app_order_id }}</p>
                                    </div>
                                @endif
                                @if($selectedOrder->delivery_app_commission)
                                    <div>
                                        <p class="text-sm text-gray-600">Comisión cobrada:</p>
                                        <p class="font-bold text-red-600">-{{ number_format($selectedOrder->delivery_app_commission, 0, ',', '.') }} Gs</p>
                                    </div>
                                    <div>
                                        <p class="text-sm text-gray-600">Neto para el negocio:</p>
                                        <p class="font-bold text-green-700">{{ number_format($selectedOrder->total - $selectedOrder->delivery_app_commission, 0, ',', '.') }} Gs</p>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endif

                    {{-- Entrega (solo web delivery) --}}
                    @if($selSource === 'web' && $selectedOrder->delivery_type === 'delivery')
                        <div class="bg-gradient-to-br from-green-50 to-emerald-50 rounded-xl p-6 mb-6">
                            <h3 class="font-black text-xl mb-4 text-green-900">🚚 Entrega</h3>
                            <div class="space-y-3">
                                <p><span class="font-bold">Dirección:</span> {{ $selectedOrder->customer_address }}</p>
                                <p><span class="font-bold">Ciudad:</span> {{ $selectedOrder->customer_city }}</p>
                                @if($selectedOrder->deliveryZone)
                                    <p><span class="font-bold">Zona:</span> {{ $selectedOrder->deliveryZone->name }}</p>
                                @endif
                                <div class="flex items-center gap-3 bg-white rounded-lg p-3 border-2 border-green-300">
                                    <label class="font-bold text-gray-700">Costo Delivery:</label>
                                    <div class="flex items-center gap-2">
                                        <input type="number"
                                            id="delivery-cost-{{ $selectedOrder->id }}"
                                            value="{{ $selectedOrder->delivery_cost }}"
                                            class="px-3 py-2 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 w-32 font-bold text-right"
                                            min="0" step="1000">
                                        <span class="text-gray-600 font-semibold">Gs</span>
                                        <button onclick="updateDeliveryCost({{ $selectedOrder->id }})"
                                            class="bg-green-600 hover:bg-green-700 text-white font-bold px-4 py-2 rounded-lg transition-all">
                                            💾 Guardar
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif

                    {{-- Productos --}}
                    <div class="mb-6">
                        <h3 class="font-black text-xl mb-4">📦 Productos</h3>
                        <div class="bg-gray-50 rounded-xl overflow-hidden">
                            <table class="min-w-full">
                                <thead class="bg-purple-600 text-white">
                                    <tr>
                                        <th class="px-4 py-3 text-left">Producto</th>
                                        <th class="px-4 py-3 text-left">Detalle</th>
                                        <th class="px-4 py-3 text-right">Subtotal</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200">
                                    @foreach($selectedOrder->items as $item)
                                        <tr>
                                            <td class="px-4 py-3 font-semibold">{{ $item->product_name }}</td>
                                            <td class="px-4 py-3 text-sm text-gray-600">
                                                @if($item->unit_type === 'weight')
                                                    ⚖️ {{ number_format($item->weight, 3, ',', '.') }} kg
                                                    × {{ number_format($item->price_per_kg, 0, ',', '.') }} Gs/kg
                                                @else
                                                    @if($item->volume)
                                                        {{ $item->volume >= 1000 ? ($item->volume/1000).'L' : $item->volume.'ml' }} ·
                                                    @endif
                                                    {{ $item->quantity }} × {{ number_format($item->price, 0, ',', '.') }} Gs
                                                @endif
                                            </td>
                                            <td class="px-4 py-3 font-black text-purple-600 text-right">
                                                {{ number_format($item->subtotal, 0, ',', '.') }} Gs
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>

                    {{-- Totales --}}
                    <div class="bg-gradient-to-r from-purple-600 to-indigo-600 rounded-xl p-6">
                        <div class="space-y-2 text-white">
                            <div class="flex justify-between text-lg">
                                <span>Subtotal:</span>
                                <span>{{ number_format($selectedOrder->subtotal, 0, ',', '.') }} Gs</span>
                            </div>
                            @if($selectedOrder->delivery_cost > 0)
                                <div class="flex justify-between text-lg">
                                    <span>Delivery:</span>
                                    <span>{{ number_format($selectedOrder->delivery_cost, 0, ',', '.') }} Gs</span>
                                </div>
                            @endif
                            <div class="border-t-2 border-white/30 pt-2"></div>
                            <div class="flex justify-between text-2xl font-black">
                                <span>TOTAL:</span>
                                <span>{{ number_format($selectedOrder->total, 0, ',', '.') }} Gs</span>
                            </div>
                            @if($selSource === 'delivery_app' && $selectedOrder->delivery_app_commission)
                                <div class="border-t border-white/30 pt-2 mt-2 space-y-1 text-sm">
                                    <div class="flex justify-between opacity-80">
                                        <span>Comisión {{ $selectedOrder->delivery_app_name }}:</span>
                                        <span>-{{ number_format($selectedOrder->delivery_app_commission, 0, ',', '.') }} Gs</span>
                                    </div>
                                    <div class="flex justify-between font-bold text-green-300">
                                        <span>NETO RECIBIDO:</span>
                                        <span>{{ number_format($selectedOrder->total - $selectedOrder->delivery_app_commission, 0, ',', '.') }} Gs</span>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>

                    @if($selectedOrder->notes)
                        <div class="mt-6 bg-yellow-100 border-4 border-yellow-400 rounded-xl p-4">
                            <h3 class="font-black text-lg mb-2 text-yellow-900">📝 Notas:</h3>
                            <p class="text-gray-800 whitespace-pre-line">{{ $selectedOrder->notes }}</p>
                        </div>
                    @endif

                    {{-- Acciones --}}
                    <div class="mt-6 flex gap-3 flex-wrap">
                        <button wire:click="printTicket({{ $selectedOrder->id }})"
                            class="flex-1 bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 rounded-xl transition-all min-w-[120px]">
                            🖨️ Imprimir Ticket
                        </button>
                        @if($selectedOrder->customer_phone)
                            <button wire:click="sendToCustomer({{ $selectedOrder->id }})"
                                class="flex-1 bg-green-600 hover:bg-green-700 text-white font-bold py-3 rounded-xl transition-all min-w-[120px]">
                                📱 WhatsApp Cliente
                            </button>
                        @endif
                        @if($selectedOrder->status !== 'cancelled')
                            <button onclick="confirmCancel({{ $selectedOrder->id }}, '{{ $selectedOrder->order_number }}')"
                                class="bg-red-600 hover:bg-red-700 text-white font-bold py-3 px-5 rounded-xl transition-all">
                                ❌ Anular
                            </button>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>

<script>
    document.addEventListener('livewire:initialized', () => {
        Livewire.on('openWhatsApp', (event) => {
            window.open(event.url, '_blank');
        });
        Livewire.on('openPrintPreview', (event) => {
            const w = 400, h = 700;
            window.open(
                `/admin/orders/${event.orderId}/print`,
                'PrintTicket',
                `width=${w},height=${h},left=${(screen.width-w)/2},top=${(screen.height-h)/2},scrollbars=yes,resizable=yes`
            );
        });
    });

    function confirmCancel(orderId, orderNumber) {
        if (confirm(`¿Anular la venta #${orderNumber}?\n\nEsto cancelará el pedido y devolverá el stock.\nNo se puede deshacer.`)) {
            @this.call('cancelOrder', orderId);
        }
    }

    function updateDeliveryCost(orderId) {
        const input   = document.getElementById(`delivery-cost-${orderId}`);
        const newCost = parseFloat(input.value) || 0;
        if (newCost < 0) { alert('El costo no puede ser negativo'); return; }
        if (confirm(`¿Actualizar delivery a ${new Intl.NumberFormat('es-PY').format(newCost)} Gs?`)) {
            @this.call('updateDeliveryCost', orderId, newCost);
        }
    }
</script>
