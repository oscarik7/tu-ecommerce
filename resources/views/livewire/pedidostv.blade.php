<div class="min-h-screen bg-gradient-to-br from-gray-900 via-gray-800 to-gray-900 text-white p-4" 
     x-data="{ time: new Date().toLocaleTimeString('es-PY', { hour: '2-digit', minute: '2-digit', second: '2-digit' }) }"
     x-init="setInterval(() => { time = new Date().toLocaleTimeString('es-PY', { hour: '2-digit', minute: '2-digit', second: '2-digit' }) }, 1000)"
     wire:poll.{{ $refreshInterval }}s="loadOrders">
    
    {{-- Header --}}
    <div class="mb-4">
        <div class="flex items-center justify-between mb-3">
            <div>
                <h1 class="text-2xl font-bold bg-gradient-to-r from-blue-400 to-purple-500 bg-clip-text text-transparent">
                    📺 Monitor de Pedidos
                </h1>
                <p class="text-gray-400 text-xs">Actualización en tiempo real</p>
            </div>
            <div class="text-right">
                <div class="text-xl font-mono font-bold text-blue-400" x-text="time"></div>
                <div class="text-xs text-gray-400 mt-1">
                    {{ now()->isoFormat('dddd, D [de] MMMM [de] YYYY') }}
                </div>
            </div>
        </div>

        {{-- Filtros --}}
        <div class="flex gap-2 flex-wrap mb-4">
            <button wire:click="setFilter('pending')" 
                    class="px-3 py-1 text-xs rounded-lg font-semibold transition-all duration-200 {{ $filterStatus === 'pending' ? 'bg-yellow-500 text-black shadow-lg shadow-yellow-500/50' : 'bg-gray-700 text-gray-300 hover:bg-gray-600' }}">
                🕐 Pendientes
            </button>
            <button wire:click="setFilter('confirmed')" 
                    class="px-3 py-1 text-xs rounded-lg font-semibold transition-all duration-200 {{ $filterStatus === 'confirmed' ? 'bg-blue-500 text-white shadow-lg shadow-blue-500/50' : 'bg-gray-700 text-gray-300 hover:bg-gray-600' }}">
                ✅ Confirmados
            </button>
            <button wire:click="setFilter('preparing')" 
                    class="px-3 py-1 text-xs rounded-lg font-semibold transition-all duration-200 {{ $filterStatus === 'preparing' ? 'bg-purple-500 text-white shadow-lg shadow-purple-500/50' : 'bg-gray-700 text-gray-300 hover:bg-gray-600' }}">
                👨‍🍳 Preparando
            </button>
            <button wire:click="setFilter('ready')" 
                    class="px-3 py-1 text-xs rounded-lg font-semibold transition-all duration-200 {{ $filterStatus === 'ready' ? 'bg-green-500 text-white shadow-lg shadow-green-500/50' : 'bg-gray-700 text-gray-300 hover:bg-gray-600' }}">
                📦 Listos
            </button>
            <button wire:click="setFilter('in_delivery')" 
                    class="px-3 py-1 text-xs rounded-lg font-semibold transition-all duration-200 {{ $filterStatus === 'in_delivery' ? 'bg-indigo-500 text-white shadow-lg shadow-indigo-500/50' : 'bg-gray-700 text-gray-300 hover:bg-gray-600' }}">
                🚚 En Camino
            </button>
            <button wire:click="setFilter('')" 
                    class="px-3 py-1 text-xs rounded-lg font-semibold transition-all duration-200 {{ $filterStatus === '' ? 'bg-white text-black shadow-lg shadow-white/50' : 'bg-gray-700 text-gray-300 hover:bg-gray-600' }}">
                🔄 Todos
            </button>
        </div>
    </div>

    {{-- Lista de Pedidos --}}
    @if(count($orders) > 0)
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-3">
            @foreach($orders as $order)
                <div class="bg-gray-800/50 backdrop-blur-sm border border-gray-700 rounded-xl p-3 shadow-lg hover:shadow-purple-500/20 transition-all duration-300 hover:border-purple-500/50">
                    {{-- Header del Pedido --}}
                    <div class="flex items-start justify-between mb-2">
                        <div>
                            {{-- NÚMERO DE PEDIDO GRANDE --}}
                            <h3 class="text-2xl font-bold text-white">
                                #{{ $order['order_number'] }}
                            </h3>
                            <p class="text-gray-400 text-xs">
                                {{ \Carbon\Carbon::parse($order['created_at'])->diffForHumans() }}
                            </p>
                        </div>
                        <span class="px-2 py-1 rounded-full text-xs font-bold {{ $this->getStatusBadgeColor($order['status']) }}">
                            {{ $this->getStatusLabel($order['status']) }}
                        </span>
                    </div>

                    {{-- Info Cliente --}}
                    <div class="bg-gray-900/30 rounded-lg p-2 mb-2">
                        <div class="flex items-center gap-2 mb-1">
                            <span class="text-lg">👤</span>
                            <div>
                                <p class="text-sm font-semibold truncate">{{ $order['customer_name'] }}</p>
                                <p class="text-gray-400 text-xs">📱 {{ $order['customer_phone'] }}</p>
                            </div>
                        </div>
                        
                        @if($order['delivery_type'] === 'delivery')
                            <div class="mt-1 pt-1 border-t border-gray-700">
                                <p class="text-xs text-gray-300 truncate">
                                    <span class="text-sm">📍</span> 
                                    {{ $order['customer_address'] }}
                                </p>
                                @if(isset($order['delivery_zone']['name']))
                                    <p class="text-xs text-blue-400 mt-1">
                                        🗺️ {{ $order['delivery_zone']['name'] }}
                                    </p>
                                @endif
                            </div>
                        @else
                            <div class="mt-1 pt-1 border-t border-gray-700">
                                <p class="text-green-400 font-semibold text-xs">
                                    🏪 Retiro en tienda
                                </p>
                            </div>
                        @endif
                    </div>

                    {{-- Productos --}}
                    <div class="space-y-1 mb-2">
                        <h4 class="text-xs font-semibold text-purple-400 mb-1">📦 Productos:</h4>
                        @foreach($order['items'] as $item)
                            <div class="bg-gray-900/20 rounded p-1">
                                <div class="flex justify-between items-start">
                                    <div class="flex-1">
                                        <div class="flex items-start gap-1">
                                            <span class="text-sm font-bold text-white bg-purple-500/30 px-1 rounded">
                                                {{ $item['quantity'] }}x
                                            </span>
                                            <div class="min-w-0">
                                                {{-- NOMBRE DEL PRODUCTO --}}
                                                <span class="text-sm font-semibold text-white truncate block">
                                                    {{ $item['product_name'] }}
                                                </span>
                                                {{-- MOSTRAR ML SI EXISTEN --}}
                                                @if(isset($item['product']['capacity_ml']) && $item['product']['capacity_ml'])
                                                    <span class="text-xs font-semibold text-blue-300 bg-blue-500/20 px-1 py-0.5 rounded">
                                                        {{ $item['product']['capacity_ml'] }}ml
                                                    </span>
                                                @elseif(isset($item['capacity_ml']) && $item['capacity_ml'])
                                                    <span class="text-xs font-semibold text-blue-300 bg-blue-500/20 px-1 py-0.5 rounded">
                                                        {{ $item['capacity_ml'] }}ml
                                                    </span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                {{-- INGREDIENTES --}}
                                @if(isset($item['ingredients']) && !empty($item['ingredients']))
                                    <div class="mt-1 pt-1 border-t border-gray-700">
                                        <p class="text-xs font-semibold text-yellow-400">🧀</p>
                                        <div class="flex flex-wrap gap-0.5">
                                            @foreach($item['ingredients'] as $ingredient)
                                                <span class="text-xs bg-gray-800/50 px-1 py-0.5 rounded border border-gray-700 truncate max-w-[80px]">
                                                    {{ $ingredient['name'] ?? $ingredient }}
                                                </span>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif
                                
                                {{-- NOTAS DEL PRODUCTO --}}
                                @if(isset($item['notes']) && !empty($item['notes']))
                                    <div class="mt-1 pt-1 border-t border-gray-700">
                                        <p class="text-xs text-yellow-300 truncate">
                                            📝 {{ Str::limit($item['notes'], 30) }}
                                        </p>
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>

                    {{-- Totales y Método de Pago --}}
                    <div class="pt-2 border-t border-gray-700">
                        <div class="text-right">
                            <p class="text-lg font-bold text-green-400">
                                {{ number_format($order['total'], 0, ',', '.') }} Gs.
                            </p>
                            <p class="text-gray-400 text-xs">
                                💳 {{ $order['payment_method']['name'] ?? 'N/A' }}
                            </p>
                        </div>
                    </div>
                    
                    {{-- Notas generales del pedido --}}
                    @if(!empty($order['notes']))
                        <div class="mt-2 bg-yellow-500/10 border border-yellow-500/30 rounded p-1">
                            <p class="text-xs text-yellow-300 truncate">
                                📝 {{ Str::limit($order['notes'], 40) }}
                            </p>
                        </div>
                    @endif
                </div>
            @endforeach
        </div>
    @else
        <div class="text-center py-10">
            <div class="text-4xl mb-2">📭</div>
            <h3 class="text-lg font-bold text-gray-400 mb-1">No hay pedidos</h3>
            <p class="text-gray-500 text-sm">Los pedidos aparecerán aquí en tiempo real</p>
        </div>
    @endif

    {{-- Indicador de actualización --}}
    <div class="fixed bottom-4 right-4 bg-gray-800 border border-gray-700 rounded-full px-3 py-1 shadow-lg">
        <div class="flex items-center gap-1">
            <div class="w-2 h-2 bg-green-500 rounded-full animate-pulse"></div>
            <span class="text-xs text-gray-300">Actualiza cada {{ $refreshInterval }}s</span>
        </div>
    </div>
</div>