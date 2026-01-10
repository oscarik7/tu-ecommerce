<div>
    <div class="h-screen flex flex-col bg-gray-100">
        @if (session()->has('message'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4 mx-4 mt-4">
                {{ session('message') }}
            </div>
        @endif

        @if (session()->has('error'))
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4 mx-4 mt-4">
                {{ session('error') }}
            </div>
        @endif

        <div class="flex-1 flex overflow-hidden p-4 gap-4">
            <!-- Panel Izquierdo - Productos -->
            <div class="flex-1 flex flex-col bg-white rounded-xl shadow-lg overflow-hidden">
                <!-- Búsqueda -->
                <div class="p-4 border-b bg-purple-50">
                    <h2 class="text-xl font-bold text-purple-900 mb-3">🛍️ Productos</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                        <input wire:model.live="productSearch" type="text" 
                            placeholder="Buscar productos..."
                            class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                        
                        <select wire:model.live="selectedCategory"
                            class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                            <option value="">Todas las categorías</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}">{{ $category->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <!-- Grid de Productos -->
                <div class="flex-1 overflow-y-auto p-4">
                    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-3">
                        @forelse($products as $product)
                            @if($product->variants->count() > 0)
                                <div class="bg-white border-2 border-gray-200 rounded-xl overflow-hidden hover:border-purple-500 transition-all cursor-pointer">
                                    @if($product->image)
                                        <img src="{{ Storage::url($product->image) }}" 
                                            alt="{{ $product->name }}"
                                            class="w-full h-32 object-cover">
                                    @else
                                        <div class="w-full h-32 bg-gradient-to-br from-purple-200 to-indigo-200 flex items-center justify-center">
                                            <span class="text-4xl">🍹</span>
                                        </div>
                                    @endif
                                    
                                    <div class="p-3">
                                        <h3 class="font-bold text-gray-900 text-sm mb-2 line-clamp-2">{{ $product->name }}</h3>
                                        
                                        <!-- Variantes -->
                                        <div class="space-y-1">
                                            @foreach($product->variants as $variant)
                                                <button wire:click="addToCart({{ $variant->id }})"
                                                    class="w-full bg-purple-600 hover:bg-purple-700 text-white rounded-lg px-3 py-2 text-xs font-bold transition-all flex justify-between items-center disabled:opacity-50 disabled:cursor-not-allowed"
                                                    @if($variant->stock <= 0) disabled @endif>
                                                    <span>{{ $variant->volume }}ml</span>
                                                    <span>{{ number_format($variant->price, 0, ',', '.') }} Gs</span>
                                                </button>
                                                @if($variant->stock > 0 && $variant->stock <= 5)
                                                    <div class="text-xs text-orange-600 text-center">
                                                        ⚠️ Quedan {{ $variant->stock }} unidades
                                                    </div>
                                                @endif
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

            <!-- Panel Derecho - Carrito y Cliente -->
            <div class="w-96 flex flex-col gap-4">
                <!-- Cliente -->
                <div class="bg-white rounded-xl shadow-lg p-4">
                    <h3 class="text-lg font-bold text-gray-900 mb-3">👤 Cliente</h3>
                    
                    @if($selectedCustomer)
                        <div class="bg-purple-50 rounded-lg p-3 mb-3">
                            <div class="flex justify-between items-start">
                                <div>
                                    <div class="font-bold text-gray-900">{{ $selectedCustomer->name }}</div>
                                    <div class="text-sm text-gray-600">{{ $selectedCustomer->email }}</div>
                                    @if($selectedCustomer->phone)
                                        <div class="text-sm text-gray-600">{{ $selectedCustomer->phone }}</div>
                                    @endif
                                </div>
                                <button wire:click="clearCustomer" class="text-red-600 hover:text-red-700">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            </div>
                        </div>
                    @elseif($isGuestSale)
                        <div class="bg-green-50 rounded-lg p-3 mb-3">
                            <div class="flex justify-between items-center">
                                <div class="font-bold text-green-900">🏪 Venta de Mostrador</div>
                                <button wire:click="clearCustomer" class="text-red-600 hover:text-red-700">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            </div>
                        </div>
                    @else
                        <div class="space-y-3">
                            <input wire:model.live="customerSearch" type="text" 
                                placeholder="Buscar cliente..."
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent text-sm">
                            
                            @if(strlen($customerSearch) >= 2 && $customers->count() > 0)
                                <div class="bg-gray-50 rounded-lg p-2 max-h-40 overflow-y-auto border border-gray-200">
                                    @foreach($customers as $customer)
                                        <button wire:click="selectCustomer({{ $customer->id }})"
                                            class="w-full text-left px-3 py-2 hover:bg-purple-100 rounded-lg transition-all mb-1 last:mb-0">
                                            <div class="font-bold text-sm">{{ $customer->name }}</div>
                                            <div class="text-xs text-gray-600">{{ $customer->email }}</div>
                                        </button>
                                    @endforeach
                                </div>
                            @elseif(strlen($customerSearch) >= 2)
                                <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-3 text-sm text-yellow-800">
                                    No se encontraron clientes
                                </div>
                            @endif

                            <button wire:click="setGuestSale"
                                class="w-full bg-green-600 hover:bg-green-700 text-white font-bold py-2 rounded-lg transition-all">
                                🏪 Venta de Mostrador
                            </button>
                        </div>
                    @endif
                </div>

                <!-- Carrito -->
                <div class="flex-1 bg-white rounded-xl shadow-lg flex flex-col overflow-hidden">
                    <div class="p-4 border-b bg-purple-50">
                        <div class="flex justify-between items-center">
                            <h3 class="text-lg font-bold text-purple-900">🛒 Carrito</h3>
                            @if(count($cart) > 0)
                                <button wire:click="clearCart" class="text-red-600 hover:text-red-700 text-sm font-bold">
                                    Vaciar
                                </button>
                            @endif
                        </div>
                        @if(count($cart) > 0)
                            <div class="text-sm text-gray-600 mt-1">
                                {{ count($cart) }} {{ count($cart) === 1 ? 'producto' : 'productos' }}
                            </div>
                        @endif
                    </div>

                    <div class="flex-1 overflow-y-auto p-4">
                        @forelse($cart as $key => $item)
                            <div class="bg-gray-50 rounded-lg p-3 mb-3 border border-gray-200">
                                <div class="flex gap-3">
                                    @if($item['image'])
                                        <img src="{{ Storage::url($item['image']) }}" 
                                            class="w-16 h-16 object-cover rounded-lg">
                                    @else
                                        <div class="w-16 h-16 bg-purple-200 rounded-lg flex items-center justify-center">
                                            <span class="text-2xl">🍹</span>
                                        </div>
                                    @endif
                                    
                                    <div class="flex-1">
                                        <div class="font-bold text-sm text-gray-900">{{ $item['product_name'] }}</div>
                                        <div class="text-xs text-gray-600">{{ $item['volume'] }}ml</div>
                                        <div class="text-sm font-bold text-purple-600">
                                            {{ number_format($item['price'], 0, ',', '.') }} Gs
                                        </div>
                                    </div>

                                    <button wire:click="removeFromCart('{{ $key }}')" 
                                        class="text-red-600 hover:text-red-700">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                    </button>
                                </div>

                                <div class="flex items-center justify-between mt-3">
                                    <div class="flex items-center gap-2">
                                        <button wire:click="updateQuantity('{{ $key }}', 'decrement')"
                                            class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold w-8 h-8 rounded-lg">
                                            -
                                        </button>
                                        <span class="font-bold text-lg w-8 text-center">{{ $item['quantity'] }}</span>
                                        <button wire:click="updateQuantity('{{ $key }}', 'increment')"
                                            class="bg-purple-600 hover:bg-purple-700 text-white font-bold w-8 h-8 rounded-lg">
                                            +
                                        </button>
                                    </div>
                                    <div class="font-black text-lg text-gray-900">
                                        {{ number_format($item['price'] * $item['quantity'], 0, ',', '.') }} Gs
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="text-center py-12 text-gray-500">
                                <div class="text-6xl mb-3">🛒</div>
                                <div class="font-bold">Carrito vacío</div>
                            </div>
                        @endforelse
                    </div>

                    <!-- Total y Pagar -->
                    <div class="p-4 border-t bg-gray-50">
                        <div class="mb-4">
                            <div class="flex justify-between items-center text-xl">
                                <span class="font-bold text-gray-900">TOTAL:</span>
                                <span class="font-black text-purple-600">{{ number_format($cartTotal, 0, ',', '.') }} Gs</span>
                            </div>
                        </div>

                        <button wire:click="openPaymentModal" 
                            @if(count($cart) === 0) disabled @endif
                            class="w-full bg-gradient-to-r from-purple-600 to-indigo-600 hover:from-purple-700 hover:to-indigo-700 text-white font-black py-4 rounded-xl transition-all disabled:opacity-50 disabled:cursor-not-allowed text-lg">
                            💳 PROCESAR PAGO
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de Pago -->
    @if($showPaymentModal)
        <div class="fixed inset-0 bg-black/50 flex items-center justify-center p-4 z-50" wire:loading.class="opacity-50">
            <div class="bg-white rounded-2xl max-w-md w-full p-6">
                <h2 class="text-2xl font-black text-gray-900 mb-6">💳 Método de Pago</h2>

                <div class="space-y-4 mb-6">
                    @if(!$selectedCustomer && !$isGuestSale)
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-2">Nombre Cliente *</label>
                            <input wire:model="customerName" type="text" 
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500">
                            @error('customerName') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-2">Teléfono *</label>
                            <input wire:model="customerPhone" type="text" 
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500">
                            @error('customerPhone') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-2">Email (opcional)</label>
                            <input wire:model="customerEmail" type="email" 
                                placeholder="email@ejemplo.com"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500">
                            @error('customerEmail') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
                        </div>
                    @endif

                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2">Método de Pago *</label>
                        <select wire:model="paymentMethodId" 
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500">
                            <option value="">Seleccione método de pago</option>
                            @foreach($paymentMethods as $method)
                                <option value="{{ $method->id }}">{{ $method->name }}</option>
                            @endforeach
                        </select>
                        @error('paymentMethodId') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
                    </div>

                    <div class="bg-purple-50 rounded-lg p-4">
                        <div class="flex justify-between text-2xl">
                            <span class="font-bold">TOTAL:</span>
                            <span class="font-black text-purple-600">{{ number_format($cartTotal, 0, ',', '.') }} Gs</span>
                        </div>
                    </div>
                </div>

                <div class="flex gap-3">
                    <button type="button" wire:click="closePaymentModal" 
                        class="flex-1 bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-3 rounded-xl transition-all"
                        wire:loading.attr="disabled">
                        Cancelar
                    </button>
                    <button type="button" wire:click="processPayment" 
                        class="flex-1 bg-gradient-to-r from-purple-600 to-indigo-600 hover:from-purple-700 hover:to-indigo-700 text-white font-bold py-3 rounded-xl transition-all disabled:opacity-50"
                        wire:loading.attr="disabled">
                        <span wire:loading.remove wire:target="processPayment">✓ Confirmar Venta</span>
                        <span wire:loading wire:target="processPayment">Procesando...</span>
                    </button>
                </div>
            </div>
        </div>
    @endif

    <!-- Modal de Ticket -->
    @if($showTicketModal && $lastOrder)
        <div class="fixed inset-0 bg-black/50 flex items-center justify-center p-4 z-50">
            <div class="bg-white rounded-2xl max-w-md w-full">
                <!-- Ticket -->
                <div id="ticket-content" class="p-8">
                    <div class="text-center mb-6">
                        <h1 class="text-3xl font-black text-purple-900">🍹 AÇAÍ STORE</h1>
                        <p class="text-sm text-gray-600">Ticket de Venta</p>
                    </div>

                    <div class="border-t-2 border-b-2 border-dashed border-gray-300 py-4 mb-4">
                        <div class="grid grid-cols-2 gap-2 text-sm">
                            <div>
                                <span class="font-bold">Ticket:</span> {{ $lastOrder->order_number }}
                            </div>
                            <div class="text-right">
                                <span class="font-bold">Fecha:</span> {{ $lastOrder->created_at->format('d/m/Y H:i') }}
                            </div>
                        </div>
                    </div>

                    <div class="mb-4">
                        <div class="font-bold text-sm mb-2">CLIENTE:</div>
                        <div class="text-sm">{{ $lastOrder->customer_name }}</div>
                        @if($lastOrder->customer_phone && $lastOrder->customer_phone != '0000000000')
                            <div class="text-sm">{{ $lastOrder->customer_phone }}</div>
                        @endif
                        @if($lastOrder->customer_email && !str_contains($lastOrder->customer_email, 'pos.local'))
                            <div class="text-sm">{{ $lastOrder->customer_email }}</div>
                        @endif
                    </div>

                    <div class="mb-4">
                        <div class="font-bold text-sm mb-2">PRODUCTOS:</div>
                        <table class="w-full text-sm">
                            @foreach($lastOrder->items as $item)
                                <tr class="border-b border-gray-200">
                                    <td class="py-2">
                                        {{ $item->product_name }}
                                        @if($item->volume)
                                            <span class="text-xs text-gray-600">({{ $item->volume }}ml)</span>
                                        @endif
                                    </td>
                                    <td class="text-right">x{{ $item->quantity }}</td>
                                    <td class="text-right font-bold">{{ number_format($item->subtotal, 0, ',', '.') }} Gs</td>
                                </tr>
                            @endforeach
                        </table>
                    </div>

                    <div class="border-t-2 border-gray-300 pt-4 mb-4">
                        <div class="flex justify-between text-lg font-bold">
                            <span>TOTAL:</span>
                            <span class="text-purple-600">{{ number_format($lastOrder->total, 0, ',', '.') }} Gs</span>
                        </div>
                        <div class="flex justify-between text-sm text-gray-600 mt-2">
                            <span>Pago:</span>
                            <span>{{ $lastOrder->paymentMethod->name }}</span>
                        </div>
                    </div>

                    <div class="text-center text-xs text-gray-600 border-t border-dashed border-gray-300 pt-4">
                        <p>¡Gracias por su compra!</p>
                        <p class="mt-2">www.acaistore.com</p>
                    </div>
                </div>

                <!-- Botones -->
                <div class="p-4 bg-gray-50 rounded-b-2xl flex gap-3">
                    <button type="button" wire:click="closeTicketModal" 
                        class="flex-1 bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-3 rounded-xl transition-all">
                        Cerrar
                    </button>
                    <button type="button" onclick="window.print()" 
                        class="flex-1 bg-purple-600 hover:bg-purple-700 text-white font-bold py-3 rounded-xl transition-all">
                        🖨️ Imprimir
                    </button>
                </div>
            </div>
        </div>
    @endif

    {{-- Estilos para impresión --}}
    <style>
        @media print {
            body * {
                visibility: hidden;
            }
            #ticket-content, #ticket-content * {
                visibility: visible;
            }
            #ticket-content {
                position: absolute;
                left: 0;
                top: 0;
                width: 80mm;
                font-size: 12px;
            }
            
            /* Estilos específicos para impresión térmica */
            #ticket-content h1 {
                font-size: 18px;
            }
            
            #ticket-content table {
                font-size: 11px;
            }
            
            /* Ocultar elementos innecesarios */
            @page {
                margin: 0;
            }
        }
    </style>

    {{-- Scripts para notificaciones --}}
    @script
    <script>
        $wire.on('show-notification', (event) => {
            const data = event[0] || event;
            const type = data.type || 'info';
            const message = data.message || 'Operación realizada';
            
            // Aquí puedes usar tu sistema de notificaciones favorito
            // Por ejemplo, con Alpine.js toast o SweetAlert2
            console.log(`[${type.toUpperCase()}] ${message}`);
            
            // Ejemplo simple con alert (reemplaza con tu sistema)
            if (type === 'error') {
                alert('❌ ' + message);
            } else if (type === 'success') {
                alert('✅ ' + message);
            } else {
                alert('ℹ️ ' + message);
            }
        });
    </script>
    @endscript
</div>