<div class="min-h-screen bg-gradient-to-br from-gray-900 via-gray-800 to-gray-900 text-white p-6" 
     x-data="{ time: new Date().toLocaleTimeString('es-PY', { hour: '2-digit', minute: '2-digit', second: '2-digit' }) }"
     x-init="setInterval(() => { time = new Date().toLocaleTimeString('es-PY', { hour: '2-digit', minute: '2-digit', second: '2-digit' }) }, 1000)"
     wire:poll.{{ $refreshInterval }}s="loadOrders">
    
    {{-- Header --}}
    <div class="mb-8">
        <div class="flex items-center justify-between mb-6">
            <div>
                <h1 class="text-5xl font-bold bg-gradient-to-r from-blue-400 to-purple-500 bg-clip-text text-transparent">
                    📺 Monitor de Pedidos
                </h1>
                <p class="text-gray-400 mt-2 text-lg">Actualización en tiempo real</p>
            </div>
            <div class="text-right">
                <div class="text-4xl font-mono font-bold text-blue-400" x-text="time"></div>
                <div class="text-sm text-gray-400 mt-1">
                    {{ now()->isoFormat('dddd, D [de] MMMM [de] YYYY') }}
                </div>
            </div>
        </div>

        {{-- Filtros --}}
        <div class="flex gap-3 flex-wrap">
            <button wire:click="setFilter('pending')" 
                    class="px-6 py-3 rounded-lg font-semibold transition-all duration-200 {{ $filterStatus === 'pending' ? 'bg-yellow-500 text-black shadow-lg shadow-yellow-500/50' : 'bg-gray-700 text-gray-300 hover:bg-gray-600' }}">
                🕐 Pendientes
            </button>
            <button wire:click="setFilter('confirmed')" 
                    class="px-6 py-3 rounded-lg font-semibold transition-all duration-200 {{ $filterStatus === 'confirmed' ? 'bg-blue-500 text-white shadow-lg shadow-blue-500/50' : 'bg-gray-700 text-gray-300 hover:bg-gray-600' }}">
                ✅ Confirmados
            </button>
            <button wire:click="setFilter('preparing')" 
                    class="px-6 py-3 rounded-lg font-semibold transition-all duration-200 {{ $filterStatus === 'preparing' ? 'bg-purple-500 text-white shadow-lg shadow-purple-500/50' : 'bg-gray-700 text-gray-300 hover:bg-gray-600' }}">
                👨‍🍳 Preparando
            </button>
            <button wire:click="setFilter('ready')" 
                    class="px-6 py-3 rounded-lg font-semibold transition-all duration-200 {{ $filterStatus === 'ready' ? 'bg-green-500 text-white shadow-lg shadow-green-500/50' : 'bg-gray-700 text-gray-300 hover:bg-gray-600' }}">
                📦 Listos
            </button>
            <button wire:click="setFilter('in_delivery')" 
                    class="px-6 py-3 rounded-lg font-semibold transition-all duration-200 {{ $filterStatus === 'in_delivery' ? 'bg-indigo-500 text-white shadow-lg shadow-indigo-500/50' : 'bg-gray-700 text-gray-300 hover:bg-gray-600' }}">
                🚚 En Camino
            </button>
            <button wire:click="setFilter('')" 
                    class="px-6 py-3 rounded-lg font-semibold transition-all duration-200 {{ $filterStatus === '' ? 'bg-white text-black shadow-lg shadow-white/50' : 'bg-gray-700 text-gray-300 hover:bg-gray-600' }}">
                🔄 Todos
            </button>
        </div>
    </div>

    {{-- Lista de Pedidos --}}
    @if(count($orders) > 0)
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            @foreach($orders as $order)
                <div class="bg-gray-800/50 backdrop-blur-sm border-2 border-gray-700 rounded-2xl p-6 shadow-2xl hover:shadow-purple-500/20 transition-all duration-300 hover:border-purple-500/50">
                    {{-- Header del Pedido --}}
                    <div class="flex items-start justify-between mb-4">
                        <div>
                            <h3 class="text-3xl font-bold text-white">
                                #{{ $order['order_number'] }}
                            </h3>
                            <p class="text-gray-400 text-sm">
                                {{ \Carbon\Carbon::parse($order['created_at'])->diffForHumans() }}
                            </p>
                        </div>
                        <span class="px-4 py-2 rounded-full text-sm font-bold {{ $this->getStatusBadgeColor($order['status']) }} shadow-lg">
                            {{ $this->getStatusLabel($order['status']) }}
                        </span>
                    </div>

                    {{-- Info Cliente --}}
                    <div class="bg-gray-900/50 rounded-xl p-4 mb-4">
                        <div class="flex items-center gap-3 mb-2">
                            <span class="text-2xl">👤</span>
                            <div>
                                <p class="text-xl font-semibold">{{ $order['customer_name'] }}</p>
                                <p class="text-gray-400">📱 {{ $order['customer_phone'] }}</p>
                            </div>
                        </div>
                        
                        @if($order['delivery_type'] === 'delivery')
                            <div class="mt-3 pt-3 border-t border-gray-700">
                                <p class="text-sm text-gray-300">
                                    <span class="text-lg">📍</span> 
                                    {{ $order['customer_address'] }}, {{ $order['customer_city'] }}
                                </p>
                                @if(isset($order['delivery_zone']['name']))
                                    <p class="text-sm text-blue-400 mt-1">
                                        🗺️ Zona: {{ $order['delivery_zone']['name'] }}
                                    </p>
                                @endif
                            </div>
                        @else
                            <div class="mt-3 pt-3 border-t border-gray-700">
                                <p class="text-green-400 font-semibold">
                                    🏪 Retiro en tienda
                                </p>
                            </div>
                        @endif
                    </div>

                    {{-- Productos --}}
                    <div class="space-y-2 mb-4">
                        <h4 class="text-lg font-semibold text-purple-400 mb-2">📦 Productos:</h4>
                        @foreach($order['items'] as $item)
                            <div class="flex justify-between items-center bg-gray-900/30 rounded-lg p-3">
                                <div class="flex-1">
                                    <span class="text-white font-medium">
                                        {{ $item['quantity'] }}x {{ $item['product_name'] }}
                                    </span>
                                </div>

                            </div>
                        @endforeach
                    </div>

                   
                    {{-- Notas --}}
                    @if(!empty($order['notes']))
                        <div class="mt-4 bg-yellow-500/10 border border-yellow-500/30 rounded-lg p-3">
                            <p class="text-sm text-yellow-300">
                                <span class="font-semibold">📝 Nota:</span> {{ $order['notes'] }}
                            </p>
                        </div>
                    @endif
                </div>
            @endforeach
        </div>
    @else
        <div class="text-center py-20">
            <div class="text-8xl mb-4">📭</div>
            <h3 class="text-3xl font-bold text-gray-400 mb-2">No hay pedidos</h3>
            <p class="text-gray-500">Los pedidos aparecerán aquí en tiempo real</p>
        </div>
    @endif

    {{-- Indicador de actualización --}}
    <div class="fixed bottom-6 right-6 bg-gray-800 border border-gray-700 rounded-full px-6 py-3 shadow-2xl">
        <div class="flex items-center gap-3">
            <div class="w-3 h-3 bg-green-500 rounded-full animate-pulse"></div>
            <span class="text-sm text-gray-300">Actualización automática cada {{ $refreshInterval }}s</span>
        </div>
    </div>
</div>