<div class="min-h-screen bg-gradient-to-br from-purple-900 via-purple-800 to-indigo-900 p-4">
    <!-- Header -->
    <div class="mb-4">
        <div class="flex justify-between items-center bg-white/10 backdrop-blur-md rounded-xl p-4 shadow-xl">
            <div>
                <h1 class="text-2xl font-bold text-white mb-1">🍹 Pedidos en Tiempo Real</h1>
                <p class="text-purple-200 text-sm">Actualización automática cada 30 segundos</p>
            </div>
            <div class="text-right">
                <div class="text-3xl font-bold text-white">{{ $orders->count() }}</div>
                <div class="text-purple-200 text-sm">Pedidos Activos</div>
            </div>
        </div>
    </div>

    <!-- Filtros (opcional, puede ocultarse para TV) -->
    <div class="bg-white/10 backdrop-blur-md rounded-xl p-3 mb-4 shadow-xl">
        <div class="flex gap-2">
            <button wire:click="$set('filterStatus', '')" 
                class="px-4 py-2 rounded-lg font-bold text-sm transition-all {{ $filterStatus == '' ? 'bg-white text-purple-900' : 'bg-white/20 text-white hover:bg-white/30' }}">
                Todos
            </button>
            <button wire:click="$set('filterStatus', 'pending')" 
                class="px-4 py-2 rounded-lg font-bold text-sm transition-all {{ $filterStatus == 'pending' ? 'bg-yellow-400 text-yellow-900' : 'bg-white/20 text-white hover:bg-white/30' }}">
                Pendientes
            </button>
            <button wire:click="$set('filterStatus', 'preparing')" 
                class="px-4 py-2 rounded-lg font-bold text-sm transition-all {{ $filterStatus == 'preparing' ? 'bg-purple-400 text-purple-900' : 'bg-white/20 text-white hover:bg-white/30' }}">
                En Preparación
            </button>
            <button wire:click="$set('filterStatus', 'ready')" 
                class="px-4 py-2 rounded-lg font-bold text-sm transition-all {{ $filterStatus == 'ready' ? 'bg-green-400 text-green-900' : 'bg-white/20 text-white hover:bg-white/30' }}">
                Listos
            </button>
        </div>
    </div>

    <!-- Grid de Cards -->
    <div class="grid grid-cols-1 lg:grid-cols-3 xl:grid-cols-4 gap-3">
        @forelse($orders as $order)
            <div class="bg-white rounded-2xl shadow-xl overflow-hidden transform hover:scale-105 transition-all duration-300
                {{ $order->status == 'pending' ? 'ring-2 ring-yellow-400' : '' }}
                {{ $order->status == 'confirmed' ? 'ring-2 ring-blue-400' : '' }}
                {{ $order->status == 'preparing' ? 'ring-2 ring-purple-400 animate-pulse' : '' }}
                {{ $order->status == 'ready' ? 'ring-2 ring-green-400' : '' }}">
                
                <!-- Header del Card -->
                <div class="p-3
                    {{ $order->status == 'pending' ? 'bg-gradient-to-r from-yellow-400 to-yellow-500' : '' }}
                    {{ $order->status == 'confirmed' ? 'bg-gradient-to-r from-blue-400 to-blue-500' : '' }}
                    {{ $order->status == 'preparing' ? 'bg-gradient-to-r from-purple-500 to-purple-600' : '' }}
                    {{ $order->status == 'ready' ? 'bg-gradient-to-r from-green-500 to-green-600' : '' }}
                    {{ $order->status == 'delivering' ? 'bg-gradient-to-r from-orange-400 to-orange-500' : '' }}">
                    
                    <div class="flex justify-between items-start mb-2">
                        <div>
                            <div class="text-white/80 text-xs font-semibold mb-1">PEDIDO</div>
                            <div class="text-2xl font-black text-white">#{{ $order->order_number }}</div>
                        </div>
                        <div class="text-right">
                            <div class="text-white/80 text-xs font-semibold mb-1">HORA</div>
                            <div class="text-lg font-bold text-white">{{ $order->created_at->format('H:i') }}</div>
                        </div>
                    </div>

                    <!-- Tiempo transcurrido -->
                    <div class="bg-white/20 backdrop-blur-sm rounded-lg px-3 py-1 text-white font-bold text-sm">
                        ⏱️ Hace {{ $order->created_at->diffForHumans(null, true) }}
                    </div>
                </div>

                <!-- Cuerpo del Card -->
                <div class="p-3">
                    <!-- Cliente -->
                    <div class="mb-3">
                        <div class="flex items-center mb-2">
                            <span class="text-2xl mr-2">👤</span>
                            <div>
                                <div class="text-base font-bold text-gray-900">{{ $order->customer_name }}</div>
                                <div class="text-sm text-gray-600">📱 {{ $order->customer_phone }}</div>
                            </div>
                        </div>
                    </div>

                    <!-- Tipo de entrega -->
                    <div class="mb-3 bg-gray-100 rounded-xl p-2">
                        <div class="text-lg font-bold text-center">
                            @if($order->delivery_type == 'delivery')
                                <span class="text-blue-600">🚚 DELIVERY</span>
                                @if($order->deliveryZone)
                                    <div class="text-xs text-gray-600 mt-1">{{ $order->deliveryZone->name }}</div>
                                @endif
                            @else
                                <span class="text-green-600">🏪 RETIRO</span>
                            @endif
                        </div>
                    </div>

                    <!-- Productos -->
                    <div class="mb-3">
                        <div class="text-xs font-bold text-gray-700 mb-2">📦 PRODUCTOS ({{ $order->items->count() }})</div>
                        <div class="space-y-1 max-h-32 overflow-y-auto">
                            @foreach($order->items as $item)
                                <div class="bg-purple-50 rounded-lg p-2 flex justify-between items-center">
                                    <div class="flex-1">
                                        <div class="font-bold text-sm text-gray-900">{{ $item->product_name }}</div>
                                        @if($item->variant)
                                            <div class="text-xs text-gray-600">{{ $item->variant->volume }} ml</div>
                                        @endif
                                    </div>
                                    <div class="text-right">
                                        <div class="text-lg font-black text-purple-600">x{{ $item->quantity }}</div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <!-- Total -->
                    <div class="bg-gradient-to-r from-purple-600 to-indigo-600 rounded-xl p-3 mb-2">
                        <div class="flex justify-between items-center">
                            <span class="text-white text-sm font-bold">TOTAL</span>
                            <span class="text-white text-xl font-black">{{ number_format($order->total, 0, ',', '.') }} Gs</span>
                        </div>
                    </div>

                    <!-- Método de pago -->
                    <div class="text-center mb-2">
                        <span class="inline-block bg-gray-200 rounded-full px-3 py-1 text-xs font-bold text-gray-800">
                            💳 {{ $order->paymentMethod->name }}
                        </span>
                    </div>

                    @if($order->notes)
                        <div class="bg-yellow-100 border border-yellow-400 rounded-lg p-2 mb-2">
                            <div class="text-xs font-bold text-yellow-900 mb-1">📝 NOTAS:</div>
                            <div class="text-xs text-gray-800">{{ $order->notes }}</div>
                        </div>
                    @endif

                    <!-- Estado -->
                    <div class="text-center mb-2">
                        <select wire:change="updateStatus({{ $order->id }}, $event.target.value)" 
                            class="w-full text-sm font-black rounded-xl px-3 py-2 border-2 cursor-pointer focus:ring-2 focus:ring-purple-500 transition-all
                            {{ $order->status == 'pending' ? 'bg-yellow-100 border-yellow-400 text-yellow-900' : '' }}
                            {{ $order->status == 'confirmed' ? 'bg-blue-100 border-blue-400 text-blue-900' : '' }}
                            {{ $order->status == 'preparing' ? 'bg-purple-100 border-purple-400 text-purple-900' : '' }}
                            {{ $order->status == 'ready' ? 'bg-green-100 border-green-400 text-green-900' : '' }}
                            {{ $order->status == 'delivering' ? 'bg-orange-100 border-orange-400 text-orange-900' : '' }}
                            {{ $order->status == 'delivered' ? 'bg-teal-100 border-teal-400 text-teal-900' : '' }}">
                            <option value="pending" {{ $order->status == 'pending' ? 'selected' : '' }}>⏳ PENDIENTE</option>
                            <option value="confirmed" {{ $order->status == 'confirmed' ? 'selected' : '' }}>✅ CONFIRMADO</option>
                            <option value="preparing" {{ $order->status == 'preparing' ? 'selected' : '' }}>👨‍🍳 PREPARANDO</option>
                            <option value="ready" {{ $order->status == 'ready' ? 'selected' : '' }}>✨ LISTO</option>
                            <option value="delivering" {{ $order->status == 'delivering' ? 'selected' : '' }}>🚚 EN CAMINO</option>
                            <option value="delivered" {{ $order->status == 'delivered' ? 'selected' : '' }}>🎉 ENTREGADO</option>
                        </select>
                    </div>

                    <!-- Acciones -->
                    <div class="flex gap-2">
                        <button wire:click="showOrder({{ $order->id }})" 
                            class="flex-1 bg-purple-600 hover:bg-purple-700 text-white font-bold py-2 rounded-lg transition-all text-sm">
                            👁️ Ver
                        </button>
                        <button wire:click="sendToWhatsApp({{ $order->id }})" 
                            class="flex-1 bg-green-600 hover:bg-green-700 text-white font-bold py-2 rounded-lg transition-all text-sm">
                            📱 WhatsApp
                        </button>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-span-full">
                <div class="bg-white rounded-2xl shadow-xl p-8 text-center">
                    <div class="text-5xl mb-4">😴</div>
                    <div class="text-2xl font-bold text-gray-600">No hay pedidos activos</div>
                    <div class="text-base text-gray-400 mt-2">Los nuevos pedidos aparecerán aquí automáticamente</div>
                </div>
            </div>
        @endforelse
    </div>

    <!-- Modal de Detalles (más grande para TV) -->
    @if($selectedOrder)
        <div class="fixed inset-0 bg-black/80 backdrop-blur-sm flex items-center justify-center p-8 z-50" wire:click="closeModal">
            <div class="bg-white rounded-3xl max-w-6xl w-full max-h-[90vh] overflow-y-auto shadow-2xl" wire:click.stop>
                <div class="p-8">
                    <div class="flex justify-between items-start mb-8">
                        <h2 class="text-5xl font-black text-gray-900">Pedido #{{ $selectedOrder->order_number }}</h2>
                        <button wire:click="closeModal" class="text-gray-500 hover:text-gray-700">
                            <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                        <!-- Información del Cliente -->
                        <div class="bg-gradient-to-br from-purple-50 to-indigo-50 rounded-2xl p-6">
                            <h3 class="font-black text-2xl mb-4 text-purple-900">👤 Cliente</h3>
                            <div class="space-y-3 text-lg">
                                <p><span class="font-bold">Nombre:</span> {{ $selectedOrder->customer_name }}</p>
                                <p><span class="font-bold">Teléfono:</span> {{ $selectedOrder->customer_phone }}</p>
                                <p><span class="font-bold">Email:</span> {{ $selectedOrder->user->email }}</p>
                            </div>
                        </div>

                        <!-- Información del Pedido -->
                        <div class="bg-gradient-to-br from-blue-50 to-cyan-50 rounded-2xl p-6">
                            <h3 class="font-black text-2xl mb-4 text-blue-900">📋 Pedido</h3>
                            <div class="space-y-3 text-lg">
                                <p><span class="font-bold">Fecha:</span> {{ $selectedOrder->created_at->format('d/m/Y H:i') }}</p>
                                <p><span class="font-bold">Estado:</span> {{ ucfirst($selectedOrder->status) }}</p>
                                <p><span class="font-bold">Pago:</span> {{ $selectedOrder->paymentMethod->name }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Entrega -->
                    <div class="bg-gradient-to-br from-green-50 to-emerald-50 rounded-2xl p-6 mb-6">
                        <h3 class="font-black text-2xl mb-4 text-green-900">🚚 Entrega</h3>
                        <div class="space-y-3 text-lg">
                            <p><span class="font-bold">Tipo:</span> {{ $selectedOrder->delivery_type == 'delivery' ? 'Delivery' : 'Retiro en Tienda' }}</p>
                            
                            @if($selectedOrder->delivery_type == 'delivery')
                                <p><span class="font-bold">Dirección:</span> {{ $selectedOrder->customer_address }}</p>
                                <p><span class="font-bold">Ciudad:</span> {{ $selectedOrder->customer_city }}</p>
                                @if($selectedOrder->deliveryZone)
                                    <p><span class="font-bold">Zona:</span> {{ $selectedOrder->deliveryZone->name }}</p>
                                    <p><span class="font-bold">Costo delivery:</span> {{ number_format($selectedOrder->delivery_cost, 0, ',', '.') }} Gs</p>
                                @endif
                                @if($selectedOrder->latitude && $selectedOrder->longitude)
                                    <a href="https://www.google.com/maps?q={{ $selectedOrder->latitude }},{{ $selectedOrder->longitude }}" 
                                       target="_blank" 
                                       class="inline-block bg-purple-600 hover:bg-purple-700 text-white font-bold px-6 py-3 rounded-xl mt-2 transition-all">
                                        📍 Ver ubicación en Google Maps
                                    </a>
                                @endif
                            @endif
                        </div>
                    </div>

                    <!-- Productos -->
                    <div class="mb-6">
                        <h3 class="font-black text-2xl mb-4 text-gray-900">🛍️ Productos</h3>
                        <div class="bg-gray-50 rounded-2xl overflow-hidden">
                            <table class="min-w-full">
                                <thead class="bg-purple-600 text-white">
                                    <tr>
                                        <th class="px-6 py-4 text-left text-lg font-bold">Producto</th>
                                        <th class="px-6 py-4 text-left text-lg font-bold">Precio</th>
                                        <th class="px-6 py-4 text-left text-lg font-bold">Cantidad</th>
                                        <th class="px-6 py-4 text-left text-lg font-bold">Subtotal</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200">
                                    @foreach($selectedOrder->items as $item)
                                        <tr class="hover:bg-purple-50">
                                            <td class="px-6 py-4 text-lg font-semibold">{{ $item->product_name }}</td>
                                            <td class="px-6 py-4 text-lg">{{ number_format($item->price, 0, ',', '.') }} Gs</td>
                                            <td class="px-6 py-4 text-lg font-bold">{{ $item->quantity }}</td>
                                            <td class="px-6 py-4 text-lg font-black text-purple-600">{{ number_format($item->subtotal, 0, ',', '.') }} Gs</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Totales -->
                    <div class="bg-gradient-to-r from-purple-600 to-indigo-600 rounded-2xl p-6">
                        <div class="space-y-3 text-white">
                            <div class="flex justify-between text-xl">
                                <span>Subtotal:</span>
                                <span class="font-bold">{{ number_format($selectedOrder->subtotal, 0, ',', '.') }} Gs</span>
                            </div>
                            @if($selectedOrder->delivery_cost > 0)
                                <div class="flex justify-between text-xl">
                                    <span>Delivery:</span>
                                    <span class="font-bold">{{ number_format($selectedOrder->delivery_cost, 0, ',', '.') }} Gs</span>
                                </div>
                            @endif
                            <div class="flex justify-between text-3xl border-t-2 border-white/30 pt-3">
                                <span class="font-black">TOTAL:</span>
                                <span class="font-black">{{ number_format($selectedOrder->total, 0, ',', '.') }} Gs</span>
                            </div>
                        </div>
                    </div>

                    @if($selectedOrder->notes)
                        <div class="mt-6 bg-yellow-100 border-4 border-yellow-400 rounded-2xl p-6">
                            <h3 class="font-black text-xl mb-3 text-yellow-900">📝 Notas del Cliente:</h3>
                            <p class="text-lg text-gray-800">{{ $selectedOrder->notes }}</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    @endif
</div>

<script>
    // Auto-refresh cada 30 segundos
    setInterval(() => {
        @this.call('$refresh');
    }, 30000);
</script>