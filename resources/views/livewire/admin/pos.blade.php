<div>
    <div class="h-screen flex flex-col bg-gray-100">
        {{-- Notificaciones --}}
        <div x-data="{ notifications: [] }" 
             x-on:show-notification.window="
                notifications.push($event.detail[0] || $event.detail);
                setTimeout(() => notifications.shift(), 2000);
             "
             class="fixed top-4 right-4 z-50 space-y-2">
            <template x-for="(notif, index) in notifications" :key="index">
                <div x-transition
                     :class="{
                        'bg-green-500': notif.type === 'success',
                        'bg-red-500': notif.type === 'error',
                        'bg-blue-500': notif.type === 'info'
                     }"
                     class="text-white px-4 py-2 rounded-lg shadow-lg font-bold text-sm">
                    <span x-text="notif.message"></span>
                </div>
            </template>
        </div>

        <div class="flex-1 flex overflow-hidden p-4 gap-4">
            <!-- Panel Izquierdo - Productos -->
            <div class="flex-1 flex flex-col bg-white rounded-xl shadow-lg overflow-hidden">
                <!-- Header con búsqueda -->
                <div class="p-4 border-b bg-gradient-to-r from-purple-600 to-indigo-600">
                    <div class="flex items-center justify-between mb-3">
                        <h2 class="text-xl font-bold text-white">🛍️ Productos</h2>
                        <div class="text-white text-sm">
                            {{ now()->format('d/m/Y H:i') }}
                        </div>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                        <input wire:model.live.debounce.300ms="productSearch" type="text" 
                            placeholder="🔍 Buscar productos..."
                            class="px-4 py-2 border-0 rounded-lg focus:ring-2 focus:ring-white/50">
                        
                        <select wire:model.live="selectedCategory"
                            class="px-4 py-2 border-0 rounded-lg focus:ring-2 focus:ring-white/50">
                            <option value="">Todas las categorías</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}">{{ $category->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <!-- Grid de Productos -->
                <div class="flex-1 overflow-y-auto p-4">
                    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-3">
                        @forelse($products as $product)
                            @if($product->variants->count() > 0)
                                <div class="bg-white border-2 border-gray-200 rounded-xl overflow-hidden hover:border-purple-500 hover:shadow-lg transition-all">
                                    @if($product->image)
                                        <img src="{{ Storage::url($product->image) }}" 
                                            alt="{{ $product->name }}"
                                            class="w-full h-28 object-cover">
                                    @else
                                        <div class="w-full h-28 bg-gradient-to-br from-purple-200 to-indigo-200 flex items-center justify-center">
                                            <span class="text-4xl">🍹</span>
                                        </div>
                                    @endif
                                    
                                    <div class="p-2">
                                        <h3 class="font-bold text-gray-900 text-xs mb-2 line-clamp-1">{{ $product->name }}</h3>
                                        
                                        <!-- Variantes como botones -->
                                        <div class="space-y-1">
                                            @foreach($product->variants as $variant)
                                                <button wire:click="addToCart({{ $variant->id }})"
                                                    wire:loading.attr="disabled"
                                                    class="w-full bg-purple-600 hover:bg-purple-700 active:bg-purple-800 text-white rounded-lg px-2 py-1.5 text-xs font-bold transition-all flex justify-between items-center disabled:opacity-50"
                                                    @if($variant->stock <= 0) disabled @endif>
                                                    <span>{{ $variant->volume }}ml</span>
                                                    <span>{{ number_format($variant->price, 0, ',', '.') }}</span>
                                                </button>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            @endif
                        @empty
                            <div class="col-span-full text-center py-12 text-gray-500">
                                <div class="text-6xl mb-3">📦</div>
                                <div class="font-bold">No se encontraron productos</div>
                            </div>
                        @endforelse
                    </div>

                    <div class="mt-4">
                        {{ $products->links() }}
                    </div>
                </div>
            </div>

            <!-- Panel Derecho - Carrito -->
            <div class="w-96 flex flex-col gap-4">
                
                <!-- Tipo de Venta -->
                <div class="bg-white rounded-xl shadow-lg p-3">
                    <div class="flex gap-2 mb-3">
                        <button wire:click="setSaleType('counter')"
                            class="flex-1 py-2 px-3 rounded-lg font-bold text-sm transition-all {{ $saleType === 'counter' ? 'bg-green-600 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300' }}">
                            🏪 Rápida
                        </button>
                        <button wire:click="setSaleType('customer')"
                            class="flex-1 py-2 px-3 rounded-lg font-bold text-sm transition-all {{ $saleType === 'customer' ? 'bg-blue-600 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300' }}">
                            👤 Cliente
                        </button>
                    </div>
                    
                    {{-- Campo de nombre rápido (siempre visible en mostrador) --}}
                    @if($saleType === 'counter')
                        <div class="relative">
                            <input wire:model="customerName" type="text" 
                                placeholder="NOMBRE PARA TICKET (OPCIONAL)"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-green-500 focus:border-green-500 uppercase"
                                style="text-transform: uppercase;"
                                oninput="this.value = this.value.toUpperCase()">
                            @if($customerName)
                                <button wire:click="$set('customerName', '')" 
                                    class="absolute right-2 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
                                    ✕
                                </button>
                            @endif
                        </div>
                        <p class="text-xs text-gray-500 mt-1">Dejar vacío = "CONSUMIDOR FINAL"</p>
                    @endif
                </div>

                <!-- Cliente (Solo si es venta con cliente) -->
                @if($saleType === 'customer')
                    <div class="bg-white rounded-xl shadow-lg p-4" x-data="{ expanded: false }">
                        <div class="flex items-center justify-between mb-3">
                            <h3 class="text-sm font-bold text-gray-900">👤 Cliente (Opcional)</h3>
                            @if($selectedCustomer)
                                <button wire:click="clearCustomer" class="text-red-600 hover:text-red-700 text-xs">
                                    Quitar
                                </button>
                            @endif
                        </div>
                        
                        @if($selectedCustomer)
                            <div class="bg-blue-50 rounded-lg p-3 text-sm">
                                <div class="font-bold text-gray-900">{{ $selectedCustomer->name }}</div>
                                @if($selectedCustomer->phone)
                                    <div class="text-gray-600 text-xs">{{ $selectedCustomer->phone }}</div>
                                @endif
                            </div>
                        @else
                            <div class="space-y-2">
                                <input wire:model.live.debounce.300ms="customerSearch" type="text" 
                                    placeholder="Buscar cliente existente..."
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500">
                                
                                @if(strlen($customerSearch) >= 2 && $customers->count() > 0)
                                    <div class="bg-gray-50 rounded-lg p-2 max-h-32 overflow-y-auto border">
                                        @foreach($customers as $customer)
                                            <button wire:click="selectCustomer({{ $customer->id }})"
                                                class="w-full text-left px-2 py-1.5 hover:bg-blue-100 rounded text-xs">
                                                <div class="font-bold">{{ $customer->name }}</div>
                                                <div class="text-gray-500">{{ $customer->phone }}</div>
                                            </button>
                                        @endforeach
                                    </div>
                                @endif

                                <!-- Datos manuales (colapsable) -->
                                <button @click="expanded = !expanded" 
                                    class="text-xs text-blue-600 hover:text-blue-700 font-semibold">
                                    <span x-text="expanded ? '▼ Ocultar campos' : '▶ Nuevo cliente'"></span>
                                </button>

                                <div x-show="expanded" x-collapse class="space-y-2 pt-2">
                                    <input wire:model="customerName" type="text" 
                                        placeholder="Nombre"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                                    <input wire:model="customerPhone" type="text" 
                                        placeholder="Teléfono"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                                </div>
                            </div>
                        @endif
                    </div>
                @endif

                <!-- Carrito -->
                <div class="flex-1 bg-white rounded-xl shadow-lg flex flex-col overflow-hidden">
                    <div class="p-3 border-b bg-gray-50">
                        <div class="flex justify-between items-center">
                            <h3 class="font-bold text-gray-900">
                                🛒 Carrito 
                                @if(count($cart) > 0)
                                    <span class="bg-purple-600 text-white text-xs px-2 py-0.5 rounded-full ml-1">
                                        {{ collect($cart)->sum('quantity') }}
                                    </span>
                                @endif
                            </h3>
                            @if(count($cart) > 0)
                                <button wire:click="clearCart" 
                                    class="text-red-600 hover:text-red-700 text-xs font-bold">
                                    Vaciar
                                </button>
                            @endif
                        </div>
                    </div>

                    <div class="flex-1 overflow-y-auto p-3">
                        @forelse($cart as $key => $item)
                            <div class="bg-gray-50 rounded-lg p-2 mb-2 border">
                                <div class="flex justify-between items-start mb-2">
                                    <div class="flex-1">
                                        <div class="font-bold text-sm text-gray-900 line-clamp-1">{{ $item['product_name'] }}</div>
                                        <div class="text-xs text-gray-600">
                                            {{ $item['volume'] }}ml × {{ number_format($item['price'], 0, ',', '.') }} Gs
                                        </div>
                                    </div>
                                    <button wire:click="removeFromCart('{{ $key }}')" 
                                        class="text-red-500 hover:text-red-700 ml-2">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                        </svg>
                                    </button>
                                </div>

                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-1">
                                        <button wire:click="updateQuantity('{{ $key }}', 'decrement')"
                                            class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold w-7 h-7 rounded text-sm">
                                            -
                                        </button>
                                        <span class="font-bold w-8 text-center">{{ $item['quantity'] }}</span>
                                        <button wire:click="updateQuantity('{{ $key }}', 'increment')"
                                            class="bg-purple-600 hover:bg-purple-700 text-white font-bold w-7 h-7 rounded text-sm">
                                            +
                                        </button>
                                    </div>
                                    <div class="font-black text-purple-600">
                                        {{ number_format($item['price'] * $item['quantity'], 0, ',', '.') }} Gs
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="text-center py-8 text-gray-400">
                                <div class="text-4xl mb-2">🛒</div>
                                <div class="text-sm">Carrito vacío</div>
                            </div>
                        @endforelse
                    </div>

                    <!-- Total -->
                    <div class="p-4 border-t bg-gradient-to-r from-purple-600 to-indigo-600">
                        <div class="flex justify-between items-center text-white mb-4">
                            <span class="font-bold text-lg">TOTAL:</span>
                            <span class="font-black text-2xl">{{ number_format($cartTotal, 0, ',', '.') }} Gs</span>
                        </div>

                        {{-- Botones de Pago Rápido --}}
                        @if(count($cart) > 0)
                            <div class="grid grid-cols-2 gap-2 mb-3">
                                @foreach($paymentMethods->take(4) as $method)
                                    <button wire:click="quickSale({{ $method->id }})"
                                        wire:loading.attr="disabled"
                                        class="bg-white/20 hover:bg-white/30 active:bg-white/40 text-white font-bold py-3 rounded-lg transition-all text-sm backdrop-blur disabled:opacity-50">
                                        @if(str_contains(strtolower($method->name), 'efectivo'))
                                            💵
                                        @elseif(str_contains(strtolower($method->name), 'tarjeta') || str_contains(strtolower($method->name), 'débito') || str_contains(strtolower($method->name), 'crédito'))
                                            💳
                                        @elseif(str_contains(strtolower($method->name), 'transfer'))
                                            🏦
                                        @elseif(str_contains(strtolower($method->name), 'qr') || str_contains(strtolower($method->name), 'billetera'))
                                            📱
                                        @else
                                            💰
                                        @endif
                                        {{ Str::limit($method->name, 12) }}
                                    </button>
                                @endforeach
                            </div>
                            
                            {{-- Botón para más opciones - SIEMPRE visible --}}
                            <button wire:click="openPaymentModal" 
                                class="w-full bg-white/90 text-purple-600 font-bold py-2 rounded-lg transition-all hover:bg-white text-sm">
                                ➕ Otro método de pago
                            </button>
                        @else
                            <button disabled
                                class="w-full bg-white/50 text-white/70 font-bold py-3 rounded-xl cursor-not-allowed">
                                Agregue productos
                            </button>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de Pago (para más opciones) -->
    @if($showPaymentModal)
        <div class="fixed inset-0 bg-black/60 backdrop-blur-sm flex items-center justify-center p-4 z-50">
            <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-2xl">
                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-xl font-black text-gray-900">💳 Seleccionar Pago</h2>
                    <button wire:click="closePaymentModal" class="text-gray-500 hover:text-gray-700">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <div class="bg-purple-50 rounded-xl p-4 mb-6">
                    <div class="flex justify-between items-center">
                        <span class="font-bold text-gray-700">Total a cobrar:</span>
                        <span class="font-black text-2xl text-purple-600">{{ number_format($cartTotal, 0, ',', '.') }} Gs</span>
                    </div>
                </div>

                <div class="space-y-2 mb-6">
                    @foreach($paymentMethods as $method)
                        <button wire:click="quickSale({{ $method->id }})"
                            wire:loading.attr="disabled"
                            class="w-full bg-gray-100 hover:bg-purple-100 active:bg-purple-200 text-gray-900 font-bold py-4 rounded-xl transition-all flex items-center justify-between px-4 disabled:opacity-50">
                            <span class="flex items-center gap-3">
                                @if(str_contains(strtolower($method->name), 'efectivo'))
                                    <span class="text-2xl">💵</span>
                                @elseif(str_contains(strtolower($method->name), 'tarjeta'))
                                    <span class="text-2xl">💳</span>
                                @elseif(str_contains(strtolower($method->name), 'transfer'))
                                    <span class="text-2xl">🏦</span>
                                @elseif(str_contains(strtolower($method->name), 'qr'))
                                    <span class="text-2xl">📱</span>
                                @else
                                    <span class="text-2xl">💰</span>
                                @endif
                                {{ $method->name }}
                            </span>
                            <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                            </svg>
                        </button>
                    @endforeach
                </div>

                @error('paymentMethodId') 
                    <div class="text-red-600 text-sm mb-4">{{ $message }}</div>
                @enderror

                <button wire:click="closePaymentModal" 
                    class="w-full bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-3 rounded-xl transition-all">
                    Cancelar
                </button>
            </div>
        </div>
    @endif

    <!-- Modal de Confirmación de Venta -->
    @if($showTicketModal && $lastOrder)
        <div class="fixed inset-0 bg-black/60 backdrop-blur-sm flex items-center justify-center p-4 z-50">
            <div class="bg-white rounded-2xl max-w-md w-full shadow-2xl overflow-hidden">
                <!-- Header de éxito -->
                <div class="bg-gradient-to-r from-green-500 to-emerald-500 p-6 text-center">
                    <div class="text-6xl mb-3">✅</div>
                    <h2 class="text-2xl font-black text-white">¡VENTA COMPLETADA!</h2>
                </div>
                
                <!-- Resumen -->
                <div class="p-6">
                    <div class="bg-gray-50 rounded-xl p-4">
                        <div class="flex justify-between items-center mb-2">
                            <span class="text-gray-600">Ticket:</span>
                            <span class="font-black text-lg">{{ $lastOrder->order_number }}</span>
                        </div>
                        <div class="flex justify-between items-center mb-2">
                            <span class="text-gray-600">Cliente:</span>
                            <span class="font-bold">{{ $lastOrder->customer_name }}</span>
                        </div>
                        <div class="flex justify-between items-center mb-2">
                            <span class="text-gray-600">Items:</span>
                            <span class="font-bold">{{ $lastOrder->items->sum('quantity') }} producto(s)</span>
                        </div>
                        <div class="flex justify-between items-center pt-2 border-t">
                            <span class="text-gray-600">Total:</span>
                            <span class="font-black text-2xl text-purple-600">{{ number_format($lastOrder->total, 0, ',', '.') }} Gs</span>
                        </div>
                        <div class="flex justify-between items-center mt-2">
                            <span class="text-gray-600">Pago:</span>
                            <span class="font-bold text-green-600">{{ $lastOrder->paymentMethod->name }}</span>
                        </div>
                    </div>
                </div>

                <!-- Botones -->
                <div class="p-4 bg-gray-50 flex gap-3">
                    <button wire:click="closeTicketModal" 
                        class="flex-1 bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-4 rounded-xl transition-all text-lg">
                        ✓ Listo
                    </button>
                    <button onclick="openPrintTicket({{ $lastOrder->id }})" 
                        class="flex-1 bg-purple-600 hover:bg-purple-700 text-white font-bold py-4 rounded-xl transition-all text-lg">
                        🖨️ Imprimir
                    </button>
                </div>
            </div>
        </div>
    @endif

    <script>
        function openPrintTicket(orderId) {
            const printUrl = `/admin/orders/${orderId}/print`;
            const width = 400;
            const height = 700;
            const left = (screen.width - width) / 2;
            const top = (screen.height - height) / 2;
            
            window.open(
                printUrl, 
                'PrintTicket', 
                `width=${width},height=${height},left=${left},top=${top},scrollbars=yes,resizable=yes`
            );
        }
    </script>
</div>