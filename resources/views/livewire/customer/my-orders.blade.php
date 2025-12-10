<div class="min-h-screen bg-gray-50 py-6 sm:py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <h1 class="text-2xl sm:text-3xl font-bold text-gray-900 mb-6 sm:mb-8">📦 Mis Pedidos</h1>

        @if(session()->has('order_created'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-6">
                <p class="font-bold">¡Pedido realizado con éxito! 🎉</p>
                <p class="text-sm">Tu pedido ha sido registrado. Pronto recibirás confirmación.</p>
            </div>
        @endif

        @if($orders->count() > 0)
            <div class="space-y-4 sm:space-y-6">
                @foreach($orders as $order)
                    <div class="bg-white rounded-lg shadow-lg p-4 sm:p-6 transition hover:shadow-xl">
                        
                        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-4 border-b pb-4">
                            <div class="mb-3 sm:mb-0">
                                <h3 class="text-xl font-bold text-gray-900 truncate">Pedido #{{ $order->order_number }}</h3>
                                <p class="text-sm text-gray-600">{{ $order->created_at->format('d/m/Y H:i') }}</p>
                            </div>
                            
                            <div class="flex flex-wrap items-center gap-2 sm:gap-3">
                                @php
                                    $statusColors = [
                                        'pending' => 'bg-yellow-100 text-yellow-800',
                                        'confirmed' => 'bg-blue-100 text-blue-800',
                                        'preparing' => 'bg-purple-100 text-purple-800',
                                        'ready' => 'bg-indigo-100 text-indigo-800',
                                        'delivering' => 'bg-orange-100 text-orange-800',
                                        'delivered' => 'bg-green-100 text-green-800',
                                        'cancelled' => 'bg-red-100 text-red-800',
                                    ];
                                    
                                    $statusLabels = [
                                        'pending' => 'Pendiente',
                                        'confirmed' => 'Confirmado',
                                        'preparing' => 'Preparando',
                                        'ready' => 'Listo',
                                        'delivering' => 'En camino',
                                        'delivered' => 'Entregado',
                                        'cancelled' => 'Cancelado',
                                    ];
                                @endphp
                                
                                <span class="px-2 py-0.5 sm:px-3 sm:py-1 rounded-full text-xs sm:text-sm font-semibold {{ $statusColors[$order->status] ?? 'bg-gray-100 text-gray-800' }}">
                                    {{ $statusLabels[$order->status] ?? $order->status }}
                                </span>

                                <button wire:click="showOrder({{ $order->id }})" 
                                    class="text-purple-600 hover:text-purple-700 font-medium text-sm sm:text-base">
                                    Ver Detalles
                                </button>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 md:grid-cols-3 gap-3 sm:gap-4 text-xs sm:text-sm pt-2">
                            <div class="truncate">
                                <span class="font-semibold text-gray-700">Tipo:</span>
                                <span class="ml-1 sm:ml-2">{{ $order->delivery_type == 'delivery' ? '🚚 Delivery' : '🏪 Retiro en Tienda' }}</span>
                            </div>
                            
                            <div class="truncate">
                                <span class="font-semibold text-gray-700">Productos:</span>
                                <span class="ml-1 sm:ml-2">{{ $order->items->count() }} items</span>
                            </div>
                            
                            <div class="col-span-2 md:col-span-1 truncate">
                                <span class="font-semibold text-gray-700">Total:</span>
                                <span class="ml-1 sm:ml-2 font-bold text-purple-600 text-base sm:text-lg">{{ number_format($order->total, 0, ',', '.') }} Gs</span>
                            </div>
                        </div>

                        @if($order->status == 'pending')
                            <div class="mt-4 pt-4 border-t border-gray-100">
                                <button wire:click="sendToWhatsApp({{ $order->id }})" 
                                    class="w-full sm:w-auto bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded-lg transition flex items-center justify-center sm:justify-start gap-2 text-sm sm:text-base">
                                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                        <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
                                    </svg>
                                    Enviar por WhatsApp
                                </button>
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
        @else
            <div class="bg-white rounded-lg shadow-md p-8 sm:p-12 text-center">
                <span class="text-6xl sm:text-8xl">📦</span>
                <h2 class="text-xl sm:text-2xl font-bold text-gray-900 mt-4 mb-2">No tienes pedidos aún</h2>
                <p class="text-gray-600 mb-6">Realiza tu primer pedido de açaí</p>
                <a href="{{ route('home') }}" 
                    class="inline-block bg-purple-600 hover:bg-purple-700 text-white font-bold py-3 px-6 rounded-lg transition text-sm sm:text-base">
                    Ver Productos
                </a>
            </div>
        @endif
    </div>

    @if($selectedOrder)
        <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50" wire:click="closeModal">
            <div class="bg-white rounded-lg max-w-lg sm:max-w-2xl w-full max-h-[95vh] overflow-y-auto" wire:click.stop>
                <div class="p-4 sm:p-6">
                    <div class="flex justify-between items-start mb-4 sm:mb-6">
                        <h2 class="text-xl sm:text-2xl font-bold text-gray-900">Detalles del Pedido</h2>
                        <button wire:click="closeModal" class="text-gray-500 hover:text-gray-700">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>

                    <div class="space-y-4 sm:space-y-6">
                        <div>
                            <h3 class="font-bold text-base sm:text-lg mb-2">Información General</h3>
                            <div class="bg-gray-50 rounded-lg p-3 sm:p-4 space-y-2 text-xs sm:text-sm">
                                <p><span class="font-semibold">Número:</span> #{{ $selectedOrder->order_number }}</p>
                                <p><span class="font-semibold">Fecha:</span> {{ $selectedOrder->created_at->format('d/m/Y H:i') }}</p>
                                <p><span class="font-semibold">Cliente:</span> {{ $selectedOrder->customer_name }}</p>
                                <p><span class="font-semibold">Teléfono:</span> {{ $selectedOrder->customer_phone }}</p>
                            </div>
                        </div>

                        <div>
                            <h3 class="font-bold text-base sm:text-lg mb-2">Productos</h3>
                            <div class="bg-gray-50 rounded-lg p-3 sm:p-4 space-y-2">
                                @foreach($selectedOrder->items as $item)
                                    <div class="flex justify-between text-xs sm:text-sm">
                                        <span>{{ $item->quantity }}x {{ $item->product_name }}</span>
                                        <span class="font-bold">{{ number_format($item->subtotal, 0, ',', '.') }} Gs</span>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <div>
                            <h3 class="font-bold text-base sm:text-lg mb-2">Entrega</h3>
                            <div class="bg-gray-50 rounded-lg p-3 sm:p-4 space-y-2 text-xs sm:text-sm">
                                <p><span class="font-semibold">Tipo:</span> {{ $selectedOrder->delivery_type == 'delivery' ? 'Delivery' : 'Retiro en Tienda' }}</p>
                                
                                @if($selectedOrder->delivery_type == 'delivery')
                                    <p><span class="font-semibold">Dirección:</span> {{ $selectedOrder->customer_address }}</p>
                                    @if($selectedOrder->deliveryZone)
                                        <p><span class="font-semibold">Zona:</span> {{ $selectedOrder->deliveryZone->name }}</p>
                                    @endif
                                    @if($selectedOrder->latitude && $selectedOrder->longitude)
                                        <a href="http://maps.google.com/maps?q={{ $selectedOrder->latitude }},{{ $selectedOrder->longitude }}" 
                                            target="_blank" 
                                            class="text-purple-600 hover:text-purple-700 block mt-1">
                                            📍 Ver en Google Maps
                                        </a>
                                    @endif
                                @endif
                            </div>
                        </div>

                        <div>
                            <h3 class="font-bold text-base sm:text-lg mb-2">Pago</h3>
                            <div class="bg-gray-50 rounded-lg p-3 sm:p-4 space-y-2 text-xs sm:text-sm">
                                <p><span class="font-semibold">Método:</span> {{ $selectedOrder->paymentMethod->name }}</p>
                                <p><span class="font-semibold">Subtotal:</span> {{ number_format($selectedOrder->subtotal, 0, ',', '.') }} Gs</p>
                                @if($selectedOrder->delivery_cost > 0)
                                    <p><span class="font-semibold">Delivery:</span> {{ number_format($selectedOrder->delivery_cost, 0, ',', '.') }} Gs</p>
                                @endif
                                <p class="text-base sm:text-lg"><span class="font-semibold">Total:</span> <span class="text-purple-600 font-bold">{{ number_format($selectedOrder->total, 0, ',', '.') }} Gs</span></p>
                            </div>
                        </div>

                        @if($selectedOrder->notes)
                            <div>
                                <h3 class="font-bold text-base sm:text-lg mb-2">Notas</h3>
                                <div class="bg-gray-50 rounded-lg p-3 sm:p-4 text-xs sm:text-sm">
                                    {{ $selectedOrder->notes }}
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>