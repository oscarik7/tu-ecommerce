<div>
    @if (session()->has('message'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
            {{ session('message') }}
        </div>
    @endif

    <!-- Filtros -->
    <div class="bg-white rounded-lg shadow p-4 mb-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <input wire:model.live="search" type="text" placeholder="Buscar por número, nombre o teléfono..." 
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
            </div>
            
            <div>
                <select wire:model.live="filterStatus" 
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                    <option value="">Todos los estados</option>
                    <option value="pending">Pendiente</option>
                    <option value="confirmed">Confirmado</option>
                    <option value="preparing">Preparando</option>
                    <option value="ready">Listo</option>
                    <option value="delivering">En camino</option>
                    <option value="delivered">Entregado</option>
                    <option value="cancelled">Cancelado</option>
                </select>
            </div>
        </div>
    </div>

    <!-- Tabla de Pedidos -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Pedido</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Cliente</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tipo</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Estado</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fecha</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($orders as $order)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-gray-900">#{{ $order->order_number }}</div>
                            <div class="text-xs text-gray-500">{{ $order->items->count() }} items</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-gray-900">{{ $order->customer_name }}</div>
                            <div class="text-xs text-gray-500">{{ $order->customer_phone }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="text-sm text-gray-900">
                                {{ $order->delivery_type == 'delivery' ? '🚚 Delivery' : '🏪 Retiro' }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-bold text-gray-900">{{ number_format($order->total, 0, ',', '.') }} Gs</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <select wire:change="updateStatus({{ $order->id }}, $event.target.value)" 
                                class="text-xs rounded-full px-3 py-1 font-semibold border-0 focus:ring-2 focus:ring-purple-500
                                {{ $order->status == 'pending' ? 'bg-yellow-100 text-yellow-800' : '' }}
                                {{ $order->status == 'confirmed' ? 'bg-blue-100 text-blue-800' : '' }}
                                {{ $order->status == 'preparing' ? 'bg-purple-100 text-purple-800' : '' }}
                                {{ $order->status == 'ready' ? 'bg-indigo-100 text-indigo-800' : '' }}
                                {{ $order->status == 'delivering' ? 'bg-orange-100 text-orange-800' : '' }}
                                {{ $order->status == 'delivered' ? 'bg-green-100 text-green-800' : '' }}
                                {{ $order->status == 'cancelled' ? 'bg-red-100 text-red-800' : '' }}">
                                <option value="pending" {{ $order->status == 'pending' ? 'selected' : '' }}>Pendiente</option>
                                <option value="confirmed" {{ $order->status == 'confirmed' ? 'selected' : '' }}>Confirmado</option>
                                <option value="preparing" {{ $order->status == 'preparing' ? 'selected' : '' }}>Preparando</option>
                                <option value="ready" {{ $order->status == 'ready' ? 'selected' : '' }}>Listo</option>
                                <option value="delivering" {{ $order->status == 'delivering' ? 'selected' : '' }}>En camino</option>
                                <option value="delivered" {{ $order->status == 'delivered' ? 'selected' : '' }}>Entregado</option>
                                <option value="cancelled" {{ $order->status == 'cancelled' ? 'selected' : '' }}>Cancelado</option>
                            </select>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ $order->created_at->format('d/m/Y H:i') }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                            <button wire:click="showOrder({{ $order->id }})" 
                                class="text-purple-600 hover:text-purple-900 font-medium mr-3">
                                Ver
                            </button>
                            <button wire:click="sendToWhatsApp({{ $order->id }})" 
                                class="text-green-600 hover:text-green-900 font-medium">
                                WhatsApp
                            </button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-6 py-12 text-center text-gray-500">
                            No se encontraron pedidos.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Paginación -->
    <div class="mt-4">
        {{ $orders->links() }}
    </div>

    <!-- Modal de Detalles -->
    @if($selectedOrder)
        <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50" wire:click="closeModal">
            <div class="bg-white rounded-lg max-w-3xl w-full max-h-[90vh] overflow-y-auto" wire:click.stop>
                <div class="p-6">
                    <div class="flex justify-between items-start mb-6">
                        <h2 class="text-2xl font-bold text-gray-900">Detalles del Pedido #{{ $selectedOrder->order_number }}</h2>
                        <button wire:click="closeModal" class="text-gray-500 hover:text-gray-700">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Información del Cliente -->
                        <div class="bg-gray-50 rounded-lg p-4">
                            <h3 class="font-bold text-lg mb-3">Información del Cliente</h3>
                            <div class="space-y-2 text-sm">
                                <p><span class="font-semibold">Nombre:</span> {{ $selectedOrder->customer_name }}</p>
                                <p><span class="font-semibold">Teléfono:</span> {{ $selectedOrder->customer_phone }}</p>
                                <p><span class="font-semibold">Email:</span> {{ $selectedOrder->user->email }}</p>
                            </div>
                        </div>

                        <!-- Información del Pedido -->
                        <div class="bg-gray-50 rounded-lg p-4">
                            <h3 class="font-bold text-lg mb-3">Información del Pedido</h3>
                            <div class="space-y-2 text-sm">
                                <p><span class="font-semibold">Fecha:</span> {{ $selectedOrder->created_at->format('d/m/Y H:i') }}</p>
                                <p><span class="font-semibold">Estado:</span> {{ ucfirst($selectedOrder->status) }}</p>
                                <p><span class="font-semibold">Pago:</span> {{ $selectedOrder->paymentMethod->name }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Entrega -->
                    <div class="bg-gray-50 rounded-lg p-4 mt-4">
                        <h3 class="font-bold text-lg mb-3">Información de Entrega</h3>
                        <div class="space-y-2 text-sm">
                            <p><span class="font-semibold">Tipo:</span> {{ $selectedOrder->delivery_type == 'delivery' ? 'Delivery' : 'Retiro en Tienda' }}</p>
                            
                            @if($selectedOrder->delivery_type == 'delivery')
                                <p><span class="font-semibold">Dirección:</span> {{ $selectedOrder->customer_address }}</p>
                                <p><span class="font-semibold">Ciudad:</span> {{ $selectedOrder->customer_city }}</p>
                                @if($selectedOrder->deliveryZone)
                                    <p><span class="font-semibold">Zona:</span> {{ $selectedOrder->deliveryZone->name }}</p>
                                    <p><span class="font-semibold">Costo delivery:</span> {{ number_format($selectedOrder->delivery_cost, 0, ',', '.') }} Gs</p>
                                @endif
                                @if($selectedOrder->latitude && $selectedOrder->longitude)
                                    <a href="https://www.google.com/maps?q={{ $selectedOrder->latitude }},{{ $selectedOrder->longitude }}" 
                                       target="_blank" 
                                       class="inline-block text-purple-600 hover:text-purple-700 font-medium">
                                        📍 Ver ubicación en Google Maps
                                    </a>
                                @endif
                            @endif
                        </div>
                    </div>

                    <!-- Productos -->
                    <div class="mt-4">
                        <h3 class="font-bold text-lg mb-3">Productos</h3>
                        <div class="bg-gray-50 rounded-lg overflow-hidden">
                            <table class="min-w-full">
                                <thead class="bg-gray-200">
                                    <tr>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-700">Producto</th>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-700">Precio</th>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-700">Cantidad</th>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-700">Subtotal</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200">
                                    @foreach($selectedOrder->items as $item)
                                        <tr>
                                            <td class="px-4 py-2 text-sm">{{ $item->product_name }}</td>
                                            <td class="px-4 py-2 text-sm">{{ number_format($item->price, 0, ',', '.') }} Gs</td>
                                            <td class="px-4 py-2 text-sm">{{ $item->quantity }}</td>
                                            <td class="px-4 py-2 text-sm font-bold">{{ number_format($item->subtotal, 0, ',', '.') }} Gs</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Totales -->
                    <div class="mt-4 bg-gray-50 rounded-lg p-4">
                        <div class="space-y-2">
                            <div class="flex justify-between text-sm">
                                <span>Subtotal:</span>
                                <span class="font-bold">{{ number_format($selectedOrder->subtotal, 0, ',', '.') }} Gs</span>
                            </div>
                            @if($selectedOrder->delivery_cost > 0)
                                <div class="flex justify-between text-sm">
                                    <span>Delivery:</span>
                                    <span class="font-bold">{{ number_format($selectedOrder->delivery_cost, 0, ',', '.') }} Gs</span>
                                </div>
                            @endif
                            <div class="flex justify-between text-lg border-t pt-2">
                                <span class="font-bold">Total:</span>
                                <span class="font-bold text-purple-600">{{ number_format($selectedOrder->total, 0, ',', '.') }} Gs</span>
                            </div>
                        </div>
                    </div>

                    @if($selectedOrder->notes)
                        <div class="mt-4 bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                            <h3 class="font-bold text-sm mb-2">📝 Notas del Cliente:</h3>
                            <p class="text-sm text-gray-700">{{ $selectedOrder->notes }}</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    @endif
</div>