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

            {{-- ══════════ PANEL IZQUIERDO — PRODUCTOS ══════════ --}}
            <div class="flex-1 flex flex-col bg-white rounded-xl shadow-lg overflow-hidden">

                {{-- Header --}}
                <div class="p-4 border-b bg-gradient-to-r from-purple-600 to-indigo-600">
                    <div class="flex items-center justify-between mb-3">
                        <h2 class="text-xl font-bold text-white">🛍️ Productos</h2>
                        <div class="flex items-center gap-3">
                            {{-- Indicador de canal de precio activo --}}
                            @if($priceChannel === 'delivery_app')
                                <span class="text-xs bg-orange-400 text-white px-2 py-1 rounded-full font-bold">🛵 Precios App</span>
                            @else
                                <span class="text-xs bg-white/20 text-white px-2 py-1 rounded-full">🏪 Precios POS</span>
                            @endif
                            <div class="text-white text-sm">{{ now()->format('d/m/Y H:i') }}</div>
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

                {{-- Grid de Productos --}}
                <div class="flex-1 overflow-y-auto p-4">
                    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-3">
                        @forelse($products as $product)
                            @php
                                $productSaleType = $product->sale_type ?? 'unit';
                                // Precio por kg según canal
                                $kgPrice = match($priceChannel) {
                                    'delivery_app' => $product->price_per_kg_delivery_app ?: $product->price_per_kg,
                                    default        => $product->price_per_kg_pos ?: $product->price_per_kg,
                                };
                            @endphp
                            <div class="bg-white border-2 border-gray-200 rounded-xl overflow-hidden hover:border-purple-500 hover:shadow-lg transition-all">
                                <div class="relative">
                                    @if($product->image)
                                        <img src="{{ Storage::url($product->image) }}" alt="{{ $product->name }}" class="w-full h-28 object-cover">
                                    @else
                                        <div class="w-full h-28 bg-gradient-to-br from-purple-200 to-indigo-200 flex items-center justify-center">
                                            <span class="text-4xl">🍹</span>
                                        </div>
                                    @endif
                                    @if($productSaleType === 'weight')
                                        <span class="absolute top-1 right-1 bg-orange-500 text-white text-[10px] px-1.5 py-0.5 rounded-full font-bold">⚖️ Kg</span>
                                    @elseif($productSaleType === 'both')
                                        <span class="absolute top-1 right-1 bg-purple-500 text-white text-[10px] px-1.5 py-0.5 rounded-full font-bold">🔄</span>
                                    @endif
                                </div>

                                <div class="p-2">
                                    <h3 class="font-bold text-gray-900 text-xs mb-2 line-clamp-1">{{ $product->name }}</h3>

                                    {{-- Solo peso --}}
                                    @if($productSaleType === 'weight')
                                        <button wire:click="openWeightModal({{ $product->id }})"
                                            class="w-full bg-orange-500 hover:bg-orange-600 text-white rounded-lg px-2 py-2 text-xs font-bold transition-all">
                                            <div class="flex justify-between items-center">
                                                <span>⚖️ Por Kg</span>
                                                <span>{{ number_format($kgPrice, 0, ',', '.') }}</span>
                                            </div>
                                        </button>

                                    {{-- Ambos --}}
                                    @elseif($productSaleType === 'both')
                                        <div class="space-y-1">
                                            <button wire:click="openWeightModal({{ $product->id }})"
                                                class="w-full bg-orange-500 hover:bg-orange-600 text-white rounded-lg px-2 py-1.5 text-xs font-bold transition-all">
                                                <div class="flex justify-between items-center">
                                                    <span>⚖️ Por Kg</span>
                                                    <span>{{ number_format($kgPrice, 0, ',', '.') }}</span>
                                                </div>
                                            </button>
                                            <div class="flex items-center gap-1 py-0.5">
                                                <div class="flex-1 border-t border-gray-300"></div>
                                                <span class="text-[10px] text-gray-400">o por vaso</span>
                                                <div class="flex-1 border-t border-gray-300"></div>
                                            </div>
                                            @if($product->variants->count() > 0)
                                                <div class="grid grid-cols-2 gap-1">
                                                    @foreach($product->variants as $variant)
                                                        @php $varPrice = $variant->getPriceForChannel($priceChannel); @endphp
                                                        <button wire:click="addToCart({{ $variant->id }})"
                                                            class="bg-purple-600 hover:bg-purple-700 text-white rounded px-1 py-1 text-[10px] font-bold transition-all">
                                                            <div>{{ $variant->volume >= 1000 ? ($variant->volume/1000).'L' : $variant->volume.'ml' }}</div>
                                                            <div class="text-purple-200">{{ number_format($varPrice / 1000, 0) }}k</div>
                                                        </button>
                                                    @endforeach
                                                </div>
                                            @endif
                                        </div>

                                    {{-- Solo unidad --}}
                                    @else
                                        @if($product->variants->count() > 0)
                                            <div class="space-y-1">
                                                @foreach($product->variants as $variant)
                                                    @php
                                                        $varPrice   = $variant->getPriceForChannel($priceChannel);
                                                        $cupStock   = $variant->available_stock;
                                                    @endphp
                                                    <button wire:click="addToCart({{ $variant->id }})"
                                                        class="w-full bg-purple-600 hover:bg-purple-700 active:bg-purple-800 text-white rounded-lg px-2 py-1.5 text-xs font-bold transition-all flex justify-between items-center
                                                            {{ $cupStock <= 0 ? 'opacity-40 cursor-not-allowed' : '' }}"
                                                        @if($cupStock <= 0) disabled @endif>
                                                        <span>{{ $variant->volume >= 1000 ? ($variant->volume/1000).'L' : $variant->volume.'ml' }}</span>
                                                        <span class="flex items-center gap-1">
                                                            {{ number_format($varPrice, 0, ',', '.') }}
                                                            {{-- Badge de stock bajo --}}
                                                            @if($cupStock > 0 && $cupStock <= 5)
                                                                <span class="text-yellow-300 text-[9px]">⚠️{{ $cupStock }}</span>
                                                            @endif
                                                        </span>
                                                    </button>
                                                @endforeach
                                            </div>
                                        @else
                                            <div class="text-xs text-gray-400 text-center py-2">Sin stock</div>
                                        @endif
                                    @endif
                                </div>
                            </div>
                        @empty
                            <div class="col-span-full text-center py-12 text-gray-500">
                                <div class="text-6xl mb-3">📦</div>
                                <div class="font-bold">No se encontraron productos</div>
                            </div>
                        @endforelse
                    </div>
                    <div class="mt-4">{{ $products->links() }}</div>
                </div>
            </div>

            {{-- ══════════ PANEL DERECHO — CARRITO ══════════ --}}
            <div class="w-96 flex flex-col gap-4">

                {{-- Tipo de venta --}}
                <div class="bg-white rounded-xl shadow-lg p-3">
                    <div class="flex gap-2 mb-3">
                        <button wire:click="setSaleType('counter')"
                            class="flex-1 py-2 px-2 rounded-lg font-bold text-xs transition-all {{ $saleType === 'counter' ? 'bg-green-600 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300' }}">
                            🏪 Rápida
                        </button>
                        <button wire:click="setSaleType('customer')"
                            class="flex-1 py-2 px-2 rounded-lg font-bold text-xs transition-all {{ $saleType === 'customer' ? 'bg-blue-600 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300' }}">
                            👤 Cliente
                        </button>
                        <button wire:click="setSaleType('delivery_app')"
                            class="flex-1 py-2 px-2 rounded-lg font-bold text-xs transition-all {{ $saleType === 'delivery_app' ? 'bg-orange-500 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300' }}">
                            🛵 App
                        </button>
                    </div>

                    {{-- Nombre en venta rápida --}}
                    @if($saleType === 'counter')
                        <div class="relative">
                            <input wire:model="customerName" type="text"
                                placeholder="NOMBRE PARA TICKET (OPCIONAL)"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-green-500 uppercase"
                                oninput="this.value = this.value.toUpperCase()">
                            @if($customerName)
                                <button wire:click="$set('customerName', '')" class="absolute right-2 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">✕</button>
                            @endif
                        </div>
                        <p class="text-xs text-gray-500 mt-1">Vacío = "CONSUMIDOR FINAL"</p>
                    @endif

                    {{-- Datos de Pedidos Ya --}}
                    @if($saleType === 'delivery_app')
                        <div class="space-y-2">
                            <div class="bg-orange-50 border border-orange-200 rounded-lg px-3 py-2 text-xs text-orange-700 font-medium flex items-center gap-2">
                                🛵 Precios Pedidos Ya activos
                            </div>
                            <select wire:model="deliveryAppName"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-orange-400">
                                <option value="Pedidos Ya">Pedidos Ya</option>
                                <option value="Rappi">Rappi</option>
                                <option value="Otro">Otro</option>
                            </select>
                            <input wire:model="deliveryAppOrderId" type="text"
                                placeholder="N° de pedido (opcional)"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-orange-400">
                            <div class="relative">
                                <input wire:model="deliveryAppCommission" type="number" step="1000"
                                    placeholder="Comisión de la app (Gs)"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-orange-400">
                                <span class="absolute right-3 top-2 text-xs text-gray-400">Gs</span>
                            </div>
                        </div>
                    @endif
                </div>

                {{-- Cliente (solo en modo customer) --}}
                @if($saleType === 'customer')
                    <div class="bg-white rounded-xl shadow-lg p-4" x-data="{ expanded: false }">
                        <div class="flex items-center justify-between mb-3">
                            <h3 class="text-sm font-bold text-gray-900">👤 Cliente</h3>
                            @if($selectedCustomer)
                                <button wire:click="clearCustomer" class="text-red-600 text-xs">Quitar</button>
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
                                    placeholder="Buscar cliente..."
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
                                <button @click="expanded = !expanded" class="text-xs text-blue-600 hover:text-blue-700 font-semibold">
                                    <span x-text="expanded ? '▼ Ocultar' : '▶ Nuevo cliente'"></span>
                                </button>
                                <div x-show="expanded" x-collapse class="space-y-2 pt-2">
                                    <input wire:model="customerName" type="text" placeholder="Nombre"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                                    <input wire:model="customerPhone" type="text" placeholder="Teléfono"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                                </div>
                            </div>
                        @endif
                    </div>
                @endif

                {{-- Carrito --}}
                <div class="flex-1 bg-white rounded-xl shadow-lg flex flex-col overflow-hidden">
                    <div class="p-3 border-b bg-gray-50">
                        <div class="flex justify-between items-center">
                            <h3 class="font-bold text-gray-900">
                                🛒 Carrito
                                @if(count($cart) > 0)
                                    <span class="bg-purple-600 text-white text-xs px-2 py-0.5 rounded-full ml-1">{{ count($cart) }}</span>
                                @endif
                            </h3>
                            @if(count($cart) > 0)
                                <button wire:click="clearCart" class="text-red-600 hover:text-red-700 text-xs font-bold">Vaciar</button>
                            @endif
                        </div>
                    </div>

                    <div class="flex-1 overflow-y-auto p-3">
                        @forelse($cart as $key => $item)
                            <div class="bg-gray-50 rounded-lg p-2 mb-2 border {{ ($item['type'] ?? 'unit') === 'weight' ? 'border-orange-200' : 'border-gray-200' }}">
                                <div class="flex justify-between items-start mb-2">
                                    @php
                                        $isWeight  = ($item['type'] ?? 'unit') === 'weight';
                                        $extras    = $item['extras'] ?? 0;
                                        $unitTotal = $isWeight ? $item['price'] : ($item['price'] + $extras) * $item['quantity'];
                                    @endphp
                                    <div class="flex-1 min-w-0">
                                        <div class="font-bold text-sm text-gray-900 line-clamp-1">{{ $item['product_name'] }}</div>
                                        <div class="text-xs text-gray-500">
                                            @if($isWeight)
                                                <span class="text-orange-600">⚖️ {{ number_format($item['weight'], 3, ',', '.') }} kg</span>
                                                × {{ number_format($item['price_per_kg'], 0, ',', '.') }} Gs/kg
                                            @else
                                                {{ $item['volume'] >= 1000 ? ($item['volume']/1000).'L' : $item['volume'].'ml' }}
                                                · base {{ number_format($item['price'], 0, ',', '.') }} Gs
                                            @endif
                                        </div>
                                        {{-- Complementos del ítem --}}
                                        @if(!empty($item['customizations']))
                                            <div class="mt-1 space-y-0.5">
                                                @foreach($item['customizations'] as $c)
                                                    <div class="flex items-center gap-1 text-[10px]">
                                                        <span class="text-purple-400">+</span>
                                                        <span class="text-gray-600">{{ $c['name'] }}</span>
                                                        @if($c['price'] > 0)
                                                            <span class="text-orange-500 font-semibold ml-auto">+{{ number_format($c['price'], 0, ',', '.') }}</span>
                                                        @else
                                                            <span class="text-green-500 ml-auto">gratis</span>
                                                        @endif
                                                    </div>
                                                @endforeach
                                            </div>
                                        @endif
                                    </div>
                                    <button wire:click="removeFromCart('{{ $key }}')" class="text-red-400 hover:text-red-600 ml-2 flex-shrink-0">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                        </svg>
                                    </button>
                                </div>
                                <div class="flex items-center justify-between mt-1.5">
                                    @if($isWeight)
                                        <div class="text-xs text-orange-600 font-semibold">⚖️ Por peso</div>
                                    @else
                                        <div class="flex items-center gap-1">
                                            <button wire:click="updateQuantity('{{ $key }}', 'decrement')"
                                                class="bg-gray-200 hover:bg-gray-300 text-gray-800 font-bold w-6 h-6 rounded text-sm leading-none">−</button>
                                            <span class="font-bold w-6 text-center text-sm">{{ $item['quantity'] }}</span>
                                            <button wire:click="updateQuantity('{{ $key }}', 'increment')"
                                                class="bg-purple-600 hover:bg-purple-700 text-white font-bold w-6 h-6 rounded text-sm leading-none">+</button>
                                        </div>
                                    @endif
                                    <div class="font-black text-sm {{ $isWeight ? 'text-orange-600' : 'text-purple-700' }}">
                                        {{ number_format($unitTotal, 0, ',', '.') }} Gs
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

                    {{-- Total y botones de pago --}}
                    <div class="p-4 border-t bg-gradient-to-r from-purple-600 to-indigo-600">
                        <div class="flex justify-between items-center text-white mb-4">
                            <span class="font-bold text-lg">TOTAL:</span>
                            <span class="font-black text-2xl">{{ number_format($cartTotal, 0, ',', '.') }} Gs</span>
                        </div>

                        @if(count($cart) > 0)
                            <div class="grid grid-cols-2 gap-2 mb-3">
                                @foreach($paymentMethods->take(4) as $method)
                                    <button wire:click="quickSale({{ $method->id }})"
                                        wire:loading.attr="disabled"
                                        class="bg-white/20 hover:bg-white/30 active:bg-white/40 text-white font-bold py-3 rounded-lg transition-all text-sm backdrop-blur disabled:opacity-50">
                                        @if(str_contains(strtolower($method->name), 'efectivo')) 💵
                                        @elseif(str_contains(strtolower($method->name), 'tarjeta') || str_contains(strtolower($method->name), 'débito')) 💳
                                        @elseif(str_contains(strtolower($method->name), 'transfer')) 🏦
                                        @elseif(str_contains(strtolower($method->name), 'qr') || str_contains(strtolower($method->name), 'billetera')) 📱
                                        @else 💰
                                        @endif
                                        {{ Str::limit($method->name, 12) }}
                                    </button>
                                @endforeach
                            </div>
                            <button wire:click="openPaymentModal"
                                class="w-full bg-white/90 text-purple-600 font-bold py-2 rounded-lg transition-all hover:bg-white text-sm">
                                ➕ Otro método de pago
                            </button>
                        @else
                            <button disabled class="w-full bg-white/50 text-white/70 font-bold py-3 rounded-xl cursor-not-allowed">
                                Agregue productos
                            </button>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ══════════ MODAL PESO ══════════ --}}
    @if($showWeightModal && $selectedWeightProduct)
        <div class="fixed inset-0 bg-black/60 backdrop-blur-sm flex items-center justify-center p-4 z-50"
             x-data="{ mode: 'amount', amount: '', weight: '' }"
             x-on:keydown.escape.window="$wire.closeWeightModal()">
            <div class="bg-white rounded-2xl max-w-md w-full shadow-2xl overflow-hidden">
                <div class="bg-gradient-to-r from-orange-500 to-amber-500 p-4 text-white">
                    <div class="flex justify-between items-start">
                        <div>
                            <h2 class="text-xl font-black">⚖️ Venta por Peso</h2>
                            <p class="text-orange-100 text-sm">{{ $selectedWeightProduct->name }}</p>
                        </div>
                        <button wire:click="closeWeightModal" class="text-white/80 hover:text-white">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>
                    @php
                        $kgPriceModal = match($priceChannel) {
                            'delivery_app' => $selectedWeightProduct->price_per_kg_delivery_app ?: $selectedWeightProduct->price_per_kg,
                            default        => $selectedWeightProduct->price_per_kg_pos ?: $selectedWeightProduct->price_per_kg,
                        };
                    @endphp
                    <div class="mt-2 bg-white/20 rounded-lg px-3 py-2 inline-block">
                        <span class="font-bold text-lg">{{ number_format($kgPriceModal, 0, ',', '.') }} Gs/kg</span>
                        @if($priceChannel === 'delivery_app')
                            <span class="text-xs text-orange-200 ml-1">· Precio App</span>
                        @else
                            <span class="text-xs text-orange-200 ml-1">· Precio POS</span>
                        @endif
                    </div>
                </div>

                <div class="p-5">
                    <div class="flex rounded-xl bg-gray-100 p-1 mb-5">
                        <button @click="mode = 'amount'; weight = ''"
                            :class="mode === 'amount' ? 'bg-white shadow-md text-orange-600' : 'text-gray-600'"
                            class="flex-1 py-2.5 px-4 rounded-lg font-bold text-sm transition-all">
                            💰 Por Monto (Gs)
                        </button>
                        <button @click="mode = 'weight'; amount = ''"
                            :class="mode === 'weight' ? 'bg-white shadow-md text-orange-600' : 'text-gray-600'"
                            class="flex-1 py-2.5 px-4 rounded-lg font-bold text-sm transition-all">
                            ⚖️ Por Peso (kg)
                        </button>
                    </div>

                    <div x-show="mode === 'amount'" x-cloak>
                        <label class="block text-sm font-medium text-gray-700 mb-2">¿Cuánto quiere llevar? (Guaraníes)</label>
                        <div class="relative mb-3">
                            <input wire:model.live.debounce.300ms="amountInput" type="text" inputmode="numeric"
                                placeholder="Ej: 30000"
                                class="w-full px-4 py-4 text-2xl font-bold text-center border-2 border-gray-300 rounded-xl focus:ring-2 focus:ring-orange-500 focus:border-orange-500" autofocus>
                            <span class="absolute right-4 top-1/2 -translate-y-1/2 text-gray-400 font-medium">Gs</span>
                        </div>
                        <div class="grid grid-cols-4 gap-2 mb-4">
                            @foreach([10000, 20000, 30000, 50000] as $quickAmount)
                                <button wire:click="$set('amountInput', '{{ $quickAmount }}')" type="button"
                                    class="py-2 px-2 text-sm font-bold rounded-lg border-2 border-gray-200 hover:border-orange-400 hover:bg-orange-50 transition-all">
                                    {{ number_format($quickAmount / 1000, 0) }}k
                                </button>
                            @endforeach
                        </div>
                        @if($amountInput && floatval(str_replace(['.', ','], ['', '.'], $amountInput)) > 0)
                            @php
                                $inputAmt  = floatval(str_replace(['.', ','], ['', '.'], $amountInput));
                                $calcKg    = $kgPriceModal > 0 ? $inputAmt / $kgPriceModal : 0;
                            @endphp
                            <div class="bg-green-50 rounded-xl p-4 border-2 border-green-300 text-center">
                                <p class="text-sm text-green-700 mb-1">Esto equivale a:</p>
                                <p class="text-3xl font-black text-green-600">{{ number_format($calcKg, 3, ',', '.') }} kg</p>
                                <p class="text-sm text-green-600 mt-1">({{ number_format($calcKg * 1000, 0, ',', '.') }} gramos)</p>
                            </div>
                        @endif
                    </div>

                    <div x-show="mode === 'weight'" x-cloak>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Ingrese el peso en kilogramos:</label>
                        <div class="relative mb-3">
                            <input wire:model.live.debounce.300ms="weightInput" type="text" inputmode="decimal"
                                placeholder="Ej: 0.500"
                                class="w-full px-4 py-4 text-2xl font-bold text-center border-2 border-gray-300 rounded-xl focus:ring-2 focus:ring-orange-500 focus:border-orange-500">
                            <span class="absolute right-4 top-1/2 -translate-y-1/2 text-gray-400 font-medium">kg</span>
                        </div>
                        <div class="grid grid-cols-4 gap-2 mb-4">
                            @foreach([0.25, 0.5, 0.75, 1] as $quickWeight)
                                <button wire:click="$set('weightInput', '{{ $quickWeight }}')" type="button"
                                    class="py-2 px-2 text-sm font-bold rounded-lg border-2 border-gray-200 hover:border-orange-400 hover:bg-orange-50 transition-all">
                                    {{ $quickWeight < 1 ? ($quickWeight * 1000).'g' : $quickWeight.'kg' }}
                                </button>
                            @endforeach
                        </div>
                        @if($weightInput && floatval(str_replace(',', '.', $weightInput)) > 0)
                            @php $calcAmt = $kgPriceModal * floatval(str_replace(',', '.', $weightInput)); @endphp
                            <div class="bg-green-50 rounded-xl p-4 border-2 border-green-300 text-center">
                                <p class="text-sm text-green-700 mb-1">Total a cobrar:</p>
                                <p class="text-3xl font-black text-green-600">{{ number_format($calcAmt, 0, ',', '.') }} Gs</p>
                            </div>
                        @endif
                    </div>
                </div>

                <div class="p-4 bg-gray-50 flex gap-3">
                    <button wire:click="closeWeightModal"
                        class="flex-1 bg-gray-200 hover:bg-gray-300 text-gray-800 font-bold py-3 rounded-xl transition-all">Cancelar</button>
                    <button wire:click="addWeightToCart" wire:loading.attr="disabled"
                        class="flex-1 bg-orange-500 hover:bg-orange-600 text-white font-bold py-3 rounded-xl transition-all disabled:opacity-50">
                        <span wire:loading.remove wire:target="addWeightToCart">✓ Agregar</span>
                        <span wire:loading wire:target="addWeightToCart">Agregando...</span>
                    </button>
                </div>
            </div>
        </div>
    @endif

    {{-- ══════════ MODAL PAGO ══════════ --}}
    @if($showPaymentModal)
        <div class="fixed inset-0 bg-black/60 backdrop-blur-sm flex items-center justify-center p-4 z-50">
            <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-2xl">
                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-xl font-black text-gray-900">💳 Seleccionar Pago</h2>
                    <button wire:click="closePaymentModal" class="text-gray-500 hover:text-gray-700">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
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
                        <button wire:click="quickSale({{ $method->id }})" wire:loading.attr="disabled"
                            class="w-full bg-gray-100 hover:bg-purple-100 active:bg-purple-200 text-gray-900 font-bold py-4 rounded-xl transition-all flex items-center justify-between px-4 disabled:opacity-50">
                            <span class="flex items-center gap-3">
                                @if(str_contains(strtolower($method->name), 'efectivo')) <span class="text-2xl">💵</span>
                                @elseif(str_contains(strtolower($method->name), 'tarjeta')) <span class="text-2xl">💳</span>
                                @elseif(str_contains(strtolower($method->name), 'transfer')) <span class="text-2xl">🏦</span>
                                @elseif(str_contains(strtolower($method->name), 'qr')) <span class="text-2xl">📱</span>
                                @else <span class="text-2xl">💰</span>
                                @endif
                                {{ $method->name }}
                            </span>
                            <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                            </svg>
                        </button>
                    @endforeach
                </div>
                @error('paymentMethodId') <div class="text-red-600 text-sm mb-4">{{ $message }}</div> @enderror
                <button wire:click="closePaymentModal" class="w-full bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-3 rounded-xl transition-all">Cancelar</button>
            </div>
        </div>
    @endif

    {{-- ══════════ MODAL TICKET ══════════ --}}
    @if($showTicketModal && $lastOrder)
        <div class="fixed inset-0 bg-black/60 backdrop-blur-sm flex items-center justify-center p-4 z-50">
            <div class="bg-white rounded-2xl max-w-md w-full shadow-2xl overflow-hidden">
                <div class="bg-gradient-to-r from-green-500 to-emerald-500 p-6 text-center">
                    <div class="text-6xl mb-3">✅</div>
                    <h2 class="text-2xl font-black text-white">¡VENTA COMPLETADA!</h2>
                    @if($lastOrder->source === 'delivery_app')
                        <p class="text-green-100 text-sm mt-1">🛵 {{ $lastOrder->delivery_app_name }}</p>
                    @endif
                </div>
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
                        @if($lastOrder->delivery_app_commission)
                            <div class="flex justify-between items-center mb-2">
                                <span class="text-gray-600">Comisión app:</span>
                                <span class="font-bold text-orange-600">{{ number_format($lastOrder->delivery_app_commission, 0, ',', '.') }} Gs</span>
                            </div>
                            <div class="flex justify-between items-center mb-2">
                                <span class="text-gray-600">Neto recibido:</span>
                                <span class="font-bold text-green-600">{{ number_format($lastOrder->total - $lastOrder->delivery_app_commission, 0, ',', '.') }} Gs</span>
                            </div>
                        @endif
                        <div class="border-t border-gray-200 mt-3 pt-3 space-y-1">
                            @foreach($lastOrder->items as $item)
                                <div class="flex justify-between text-sm">
                                    <span class="text-gray-600">
                                        {{ $item->product_name }}
                                        @if($item->unit_type === 'weight')
                                            <span class="text-orange-600">({{ number_format($item->weight, 3, ',', '.') }} kg)</span>
                                        @elseif($item->volume)
                                            ({{ $item->volume >= 1000 ? ($item->volume/1000).'L' : $item->volume.'ml' }} × {{ $item->quantity }})
                                        @endif
                                    </span>
                                    <span class="font-medium">{{ number_format($item->subtotal, 0, ',', '.') }}</span>
                                </div>
                            @endforeach
                        </div>
                        <div class="flex justify-between items-center pt-3 mt-3 border-t border-gray-300">
                            <span class="font-bold text-gray-600">Total:</span>
                            <span class="font-black text-2xl text-purple-600">{{ number_format($lastOrder->total, 0, ',', '.') }} Gs</span>
                        </div>
                        <div class="flex justify-between items-center mt-2">
                            <span class="text-gray-600">Pago:</span>
                            <span class="font-bold text-green-600">{{ $lastOrder->paymentMethod->name }}</span>
                        </div>
                    </div>
                </div>
                <div class="p-4 bg-gray-50 flex gap-3">
                    <button wire:click="closeTicketModal"
                        class="flex-1 bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-4 rounded-xl transition-all text-lg">✓ Listo</button>
                    <button onclick="openPrintTicket({{ $lastOrder->id }})"
                        class="flex-1 bg-purple-600 hover:bg-purple-700 text-white font-bold py-4 rounded-xl transition-all text-lg">🖨️ Imprimir</button>
                </div>
            </div>
        </div>
    @endif

    {{-- ══════════ MODAL CALCULADORA DE VUELTO ══════════ --}}
    @if($showChangeCalculator)
        <div class="fixed inset-0 bg-black/60 backdrop-blur-sm flex items-center justify-center p-4 z-50">
            <div class="bg-white rounded-2xl max-w-md w-full shadow-2xl overflow-hidden">
                
                {{-- Header --}}
                <div class="p-5 {{ $changeCalculatorCurrency === 'BRL' ? 'bg-gradient-to-r from-blue-500 to-indigo-500' : 'bg-gradient-to-r from-green-500 to-emerald-500' }} text-white">
                    <div class="flex justify-between items-start mb-2">
                        <div>
                            <h2 class="text-2xl font-black">
                                {{ $changeCalculatorCurrency === 'BRL' ? '💵 Pago en Reales' : '💵 Pago en Efectivo' }}
                            </h2>
                            <p class="text-sm mt-1 {{ $changeCalculatorCurrency === 'BRL' ? 'text-blue-100' : 'text-green-100' }}">
                                Calculadora de vuelto
                            </p>
                        </div>
                        <button wire:click="closeChangeCalculator" class="text-white/80 hover:text-white">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>
                    
                    {{-- Total a cobrar --}}
                    <div class="bg-white/20 rounded-xl p-3 mt-3">
                        <div class="text-xs {{ $changeCalculatorCurrency === 'BRL' ? 'text-blue-100' : 'text-green-100' }}">Total a cobrar</div>
                        <div class="text-3xl font-black">{{ number_format($cartTotal, 0, ',', '.') }} Gs</div>
                    </div>

                    {{-- Cotización (solo BRL) --}}
                    @if($changeCalculatorCurrency === 'BRL')
                        <div class="mt-2 text-center">
                            <span class="text-xs bg-white/20 px-3 py-1 rounded-full">
                                💱 Cotización: 1 R$ = {{ number_format($calculatedChange['rate'], 0, ',', '.') }} Gs
                            </span>
                        </div>
                    @endif
                </div>

                {{-- Body --}}
                <div class="p-6 space-y-4">
                    
                    {{-- Campo de monto recibido --}}
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2">
                            Cliente paga: <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <input wire:model.live="amountReceived" 
                                type="text" 
                                inputmode="numeric"
                                placeholder="0"
                                class="w-full px-4 py-4 text-3xl font-bold text-center border-2 border-gray-300 rounded-xl focus:ring-2 {{ $changeCalculatorCurrency === 'BRL' ? 'focus:ring-blue-500' : 'focus:ring-green-500' }}"
                                autofocus>
                            <span class="absolute right-4 top-1/2 -translate-y-1/2 text-gray-400 font-bold text-lg">
                                {{ $changeCalculatorCurrency === 'BRL' ? 'R$' : 'Gs' }}
                            </span>
                        </div>
                    </div>

                    {{-- Atajos rápidos --}}
                    <div class="grid grid-cols-4 gap-2">
                        @if($changeCalculatorCurrency === 'BRL')
                            {{-- Atajos en reales --}}
                            @foreach([5, 10, 20, 50] as $quick)
                                <button wire:click="$set('amountReceived', '{{ $quick }}')" type="button"
                                    class="py-2 text-sm font-bold rounded-lg border-2 border-gray-200 hover:border-blue-400 hover:bg-blue-50 transition-all">
                                    {{ $quick }} R$
                                </button>
                            @endforeach
                        @else
                            {{-- Atajos en guaraníes --}}
                            @foreach([10000, 20000, 50000, 100000] as $quick)
                                <button wire:click="$set('amountReceived', '{{ $quick }}')" type="button"
                                    class="py-2 text-sm font-bold rounded-lg border-2 border-gray-200 hover:border-green-400 hover:bg-green-50 transition-all">
                                    {{ number_format($quick / 1000, 0) }}k
                                </button>
                            @endforeach
                        @endif
                    </div>

                    {{-- Cálculos en tiempo real --}}
                    @if($amountReceived && floatval(str_replace(['.', ','], ['', '.'], $amountReceived)) > 0)
                        <div class="bg-gray-50 rounded-xl p-4 space-y-2">
                            
                            {{-- Equivalente en Gs (solo BRL) --}}
                            @if($changeCalculatorCurrency === 'BRL')
                                <div class="flex justify-between text-sm">
                                    <span class="text-gray-600">Equivale a:</span>
                                    <span class="font-bold text-blue-600">{{ number_format($calculatedChange['receivedInGs'], 0, ',', '.') }} Gs</span>
                                </div>
                            @endif

                            {{-- Vuelto en Gs --}}
                            <div class="flex justify-between items-center border-t pt-2">
                                <span class="font-bold text-gray-700">Vuelto:</span>
                                <div class="text-right">
                                    <div class="text-2xl font-black {{ $calculatedChange['changeInGs'] >= 0 ? 'text-green-600' : 'text-red-600' }}">
                                        {{ number_format($calculatedChange['changeInGs'], 0, ',', '.') }} Gs
                                    </div>
                                    
                                    {{-- Vuelto en R$ (solo BRL y si hay vuelto) --}}
                                    @if($changeCalculatorCurrency === 'BRL' && $calculatedChange['changeInGs'] > 0)
                                        <div class="text-xs text-gray-500 mt-1">
                                            (≈ {{ number_format($calculatedChange['changeInBrl'], 2, ',', '.') }} R$)
                                        </div>
                                    @endif
                                </div>
                            </div>

                            {{-- Advertencia si falta --}}
                            @if($calculatedChange['changeInGs'] < 0)
                                <div class="bg-red-50 border border-red-200 rounded-lg p-2 text-center">
                                    <span class="text-red-600 text-sm font-bold">⚠️ Falta {{ number_format(abs($calculatedChange['changeInGs']), 0, ',', '.') }} Gs</span>
                                </div>
                            @endif
                        </div>
                    @endif
                </div>

                {{-- Footer --}}
                <div class="p-4 bg-gray-50 flex gap-3 border-t">
                    <button wire:click="closeChangeCalculator"
                        class="flex-1 bg-gray-200 hover:bg-gray-300 text-gray-800 font-bold py-3 rounded-xl transition-all">
                        Cancelar
                    </button>
                    <button wire:click="confirmPaymentWithChange" 
                        wire:loading.attr="disabled"
                        class="flex-1 {{ $changeCalculatorCurrency === 'BRL' ? 'bg-blue-500 hover:bg-blue-600' : 'bg-green-500 hover:bg-green-600' }} text-white font-bold py-3 rounded-xl transition-all disabled:opacity-50">
                        <span wire:loading.remove wire:target="confirmPaymentWithChange">✓ Confirmar Venta</span>
                        <span wire:loading wire:target="confirmPaymentWithChange">Procesando...</span>
                    </button>
                </div>
            </div>
        </div>
    @endif

    <script>
        function openPrintTicket(orderId) {
            const w = 400, h = 700;
            window.open(`/admin/orders/${orderId}/print`, 'PrintTicket',
                `width=${w},height=${h},left=${(screen.width-w)/2},top=${(screen.height-h)/2},scrollbars=yes,resizable=yes`);
        }
    </script>

    {{-- ══════════ MODAL COMPLEMENTOS POS ══════════ --}}
    @if($showCustomizationsModal && $pendingVariantId)
        @php
            $pendingVariant = \App\Models\ProductVariant::with('product')->find($pendingVariantId);
        @endphp
        <div class="fixed inset-0 bg-black/60 backdrop-blur-sm flex items-center justify-center p-4 z-50">
            <div class="bg-white rounded-2xl w-full max-w-lg shadow-2xl flex flex-col max-h-[90vh]">

                {{-- Header --}}
                <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 flex-shrink-0">
                    <div>
                        <h2 class="text-lg font-black text-gray-900">
                            🍓 Complementos
                        </h2>
                        @if($pendingVariant)
                            <p class="text-sm text-purple-600 font-semibold mt-0.5">
                                {{ $pendingVariant->product->name }} — {{ $pendingVariant->volume }}ml
                            </p>
                        @endif
                    </div>
                    <button wire:click="closeCustomizationsModalPOS"
                        class="w-8 h-8 rounded-full bg-gray-100 hover:bg-gray-200 flex items-center justify-center transition-colors">
                        <svg class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                {{-- Grupos --}}
                <div class="flex-1 overflow-y-auto px-6 py-4 space-y-5">
                    @foreach($posCustomizationGroups as $group)
                        <div>
                            <div class="flex items-center gap-2 mb-3">
                                <h3 class="font-bold text-gray-800 text-sm">{{ $group['name'] }}</h3>
                                @if($group['required'] ?? false)
                                    <span class="text-[10px] bg-red-100 text-red-600 font-bold px-2 py-0.5 rounded-full">Requerido</span>
                                @else
                                    <span class="text-[10px] bg-gray-100 text-gray-500 px-2 py-0.5 rounded-full">Opcional</span>
                                @endif
                                @if(($group['max_selections'] ?? null) > 1)
                                    <span class="text-[10px] text-gray-400">máx. {{ $group['max_selections'] }}</span>
                                @endif
                            </div>

                            <div class="grid grid-cols-2 gap-2">
                                @foreach($group['options'] as $option)
                                    @php
                                        $isSelected = in_array($option['id'], $selectedCustomizations[$group['id']] ?? []);
                                        // USAR is_multiple del array del grupo
                                        $isMultiple = ($group['is_multiple'] ?? false);
                                    @endphp
                                    
                                    {{-- MODAL COMPLEMENTOS POS — botón CON foto --}}
                                    @php
                                        $optImgUrl = \App\Models\CustomizationOption::find($option['id'])?->image_url;
                                    @endphp
                                    <button type="button"
                                        wire:click="toggleCustomization({{ $group['id'] }}, {{ $option['id'] }}, {{ $isMultiple ? 'true' : 'false' }})"
                                        class="flex items-center gap-2 p-2.5 rounded-xl border-2 transition-all duration-150 text-left
                                            {{ $isSelected
                                                ? 'border-purple-500 bg-purple-50'
                                                : 'border-gray-200 hover:border-gray-300 bg-white' }}">

                                        {{-- Foto (si tiene) --}}
                                        @if($optImgUrl)
                                            <div class="w-10 h-10 rounded-lg overflow-hidden flex-shrink-0 border border-gray-100">
                                                <img src="{{ $optImgUrl }}" alt="{{ $option['name'] }}"
                                                    class="w-full h-full object-cover">
                                            </div>
                                        @endif

                                        {{-- Checkbox / Radio --}}
                                        <div class="w-4 h-4 rounded-{{ $isMultiple ? 'sm' : 'full' }} border-2 flex items-center justify-center flex-shrink-0 transition-all
                                            {{ $isSelected ? 'border-purple-500 bg-purple-500' : 'border-gray-300' }}">
                                            @if($isSelected)
                                                <svg class="w-2.5 h-2.5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
                                                </svg>
                                            @endif
                                        </div>

                                        <span class="text-sm font-semibold text-gray-800 truncate flex-1 min-w-0">{{ $option['name'] }}</span>

                                        @if($option['price'] > 0)
                                            <span class="text-xs font-bold text-orange-500 flex-shrink-0 ml-auto">
                                                +{{ number_format($option['price'], 0, ',', '.') }}
                                            </span>
                                        @else
                                            <span class="text-xs text-green-500 flex-shrink-0 ml-auto">gratis</span>
                                        @endif
                                    </button>

                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>

                {{-- Footer con resumen y botón confirmar --}}
                <div class="flex-shrink-0 px-6 py-4 border-t border-gray-100 bg-gray-50 rounded-b-2xl">
                    @php
                        $extrasPreview = 0;
                        foreach($posCustomizationGroups as $grp) {
                            foreach($grp['options'] as $opt) {
                                if(in_array($opt['id'], $selectedCustomizations[$grp['id']] ?? [])) {
                                    $extrasPreview += $opt['price'];
                                }
                            }
                        }
                        $basePrice   = $pendingVariant?->getPriceForChannel($priceChannel) ?? 0;
                        $totalPreview = $basePrice + $extrasPreview;
                    @endphp

                    <div class="flex items-center justify-between mb-3">
                        <div class="text-xs text-gray-500 space-y-0.5">
                            <div>Base: <span class="font-semibold text-gray-700">{{ number_format($basePrice, 0, ',', '.') }} Gs</span></div>
                            @if($extrasPreview > 0)
                                <div>Complementos: <span class="font-semibold text-orange-500">+{{ number_format($extrasPreview, 0, ',', '.') }} Gs</span></div>
                            @endif
                        </div>
                        <div class="text-right">
                            <div class="text-xs text-gray-400">Total unitario</div>
                            <div class="text-2xl font-black text-purple-700">{{ number_format($totalPreview, 0, ',', '.') }} <span class="text-sm font-normal text-gray-400">Gs</span></div>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <button wire:click="closeCustomizationsModalPOS"
                            class="py-3 rounded-xl bg-gray-200 hover:bg-gray-300 text-gray-700 font-bold text-sm transition-colors">
                            Cancelar
                        </button>
                        <button wire:click="confirmCustomizationsPOS"
                            wire:loading.attr="disabled"
                            class="py-3 rounded-xl bg-gradient-to-r from-purple-600 to-pink-500 hover:from-purple-700 hover:to-pink-600 text-white font-bold text-sm transition-all shadow-md disabled:opacity-60">
                            <span wire:loading.remove wire:target="confirmCustomizationsPOS">✓ Agregar al carrito</span>
                            <span wire:loading wire:target="confirmCustomizationsPOS">Agregando...</span>
                        </button>
                    </div>
                </div>

            </div>
        </div>
    @endif
</div>