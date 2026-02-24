<div>
    {{-- ══════════════════════════════════════════════════════════════════════ --}}
    {{-- NOTIFICACIONES                                                        --}}
    {{-- ══════════════════════════════════════════════════════════════════════ --}}
    <div x-data="{ notifications: [] }"
         x-on:show-notification.window="
             let n = $event.detail[0] || $event.detail;
             notifications.push(n);
             setTimeout(() => notifications.shift(), 2500);
         "
         class="fixed top-4 right-4 z-50 space-y-2 pointer-events-none">
        <template x-for="(notif, index) in notifications" :key="index">
            <div x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0 translate-x-4"
                 x-transition:enter-end="opacity-100 translate-x-0"
                 x-transition:leave="transition ease-in duration-150"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 :class="{
                    'bg-green-500': notif.type === 'success',
                    'bg-red-500':   notif.type === 'error',
                    'bg-blue-500':  notif.type === 'info'
                 }"
                 class="text-white px-4 py-2 rounded-lg shadow-lg font-bold text-sm min-w-[220px]">
                <span x-text="notif.message"></span>
            </div>
        </template>
    </div>

    {{-- ══════════════════════════════════════════════════════════════════════ --}}
    {{-- LAYOUT PRINCIPAL                                                      --}}
    {{-- ══════════════════════════════════════════════════════════════════════ --}}
    <div class="h-screen flex flex-col bg-gray-100">
        <div class="flex-1 flex overflow-hidden p-4 gap-4">

            {{-- ════════════════════════════════════════════════════ --}}
            {{-- PANEL IZQUIERDO — PRODUCTOS                          --}}
            {{-- ════════════════════════════════════════════════════ --}}
            <div class="flex-1 flex flex-col bg-white rounded-xl shadow-lg overflow-hidden">

                {{-- Header --}}
                <div class="p-4 border-b bg-gradient-to-r from-purple-600 to-indigo-600">
                    <div class="flex items-center justify-between mb-3">
                        <h2 class="text-xl font-bold text-white">🛍️ Productos</h2>
                        <div class="flex items-center gap-3">
                            @if($priceChannel === 'delivery_app')
                                <span class="text-xs bg-orange-400 text-white px-2 py-1 rounded-full font-bold">🛵 Precios App</span>
                            @else
                                <span class="text-xs bg-white/20 text-white px-2 py-1 rounded-full">🏪 Precios POS</span>
                            @endif
                            <div class="text-white text-sm">{{ now()->format('d/m/Y H:i') }}</div>
                        </div>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                        <input wire:model.live.debounce.300ms="productSearch"
                               type="text"
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
                                $kgPrice = match($priceChannel) {
                                    'delivery_app' => $product->price_per_kg_delivery_app ?: $product->price_per_kg,
                                    default        => $product->price_per_kg_pos ?: $product->price_per_kg,
                                };
                            @endphp
                            <div wire:key="product-{{ $product->id }}"
                                 class="bg-white border-2 border-gray-200 rounded-xl overflow-hidden hover:border-purple-500 hover:shadow-lg transition-all">

                                {{-- Imagen --}}
                                <div class="relative">
                                    @if($product->image)
                                        <img src="{{ Storage::url($product->image) }}"
                                             alt="{{ $product->name }}"
                                             class="w-full h-28 object-cover">
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
                                            @if($product->variants->count() > 0)
                                                <div class="flex items-center gap-1 py-0.5">
                                                    <div class="flex-1 border-t border-gray-300"></div>
                                                    <span class="text-[10px] text-gray-400">o por vaso</span>
                                                    <div class="flex-1 border-t border-gray-300"></div>
                                                </div>
                                                <div class="grid grid-cols-2 gap-1">
                                                    @foreach($product->variants as $variant)
                                                        @php $varPrice = $variant->getPriceForChannel($priceChannel); @endphp
                                                        <button wire:click="addToCart({{ $variant->id }})"
                                                                wire:key="variant-{{ $variant->id }}"
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
                                                        $varPrice = $variant->getPriceForChannel($priceChannel);
                                                        $cupStock = $variant->available_stock;
                                                    @endphp
                                                    <button wire:click="addToCart({{ $variant->id }})"
                                                            wire:key="variant-{{ $variant->id }}"
                                                            class="w-full bg-purple-600 hover:bg-purple-700 active:bg-purple-800 text-white rounded-lg px-2 py-1.5 text-xs font-bold transition-all flex justify-between items-center {{ $cupStock <= 0 ? 'opacity-40 cursor-not-allowed' : '' }}"
                                                            @if($cupStock <= 0) disabled @endif>
                                                        <span>{{ $variant->volume >= 1000 ? ($variant->volume/1000).'L' : $variant->volume.'ml' }}</span>
                                                        <span class="flex items-center gap-1">
                                                            {{ number_format($varPrice, 0, ',', '.') }}
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

            {{-- ════════════════════════════════════════════════════ --}}
            {{-- PANEL DERECHO — CARRITO                              --}}
            {{-- ════════════════════════════════════════════════════ --}}
            <div class="w-96 flex flex-col gap-3">

                {{-- Tipo de venta --}}
                <div class="bg-white rounded-xl shadow-lg p-2">
                    <div class="flex gap-1 mb-2">
                        <button wire:click="setSaleType('counter')"
                                class="flex-1 py-1.5 px-2 rounded-lg font-bold text-xs transition-all {{ $saleType === 'counter' ? 'bg-green-600 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300' }}">
                            🏪 Rápida
                        </button>
                        <button wire:click="setSaleType('customer')"
                                class="flex-1 py-1.5 px-2 rounded-lg font-bold text-xs transition-all {{ $saleType === 'customer' ? 'bg-blue-600 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300' }}">
                            👤 Cliente
                        </button>
                        <button wire:click="setSaleType('delivery_app')"
                                class="flex-1 py-1.5 px-2 rounded-lg font-bold text-xs transition-all {{ $saleType === 'delivery_app' ? 'bg-orange-500 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300' }}">
                            🛵 App
                        </button>
                    </div>

                    {{-- Nombre en venta rápida --}}
                    @if($saleType === 'counter')
                        <input wire:model="customerName"
                               type="text"
                               placeholder="NOMBRE PARA TICKET"
                               class="w-full px-2 py-1.5 border border-gray-300 rounded-lg text-xs focus:ring-2 focus:ring-green-500 uppercase"
                               oninput="this.value = this.value.toUpperCase()">
                    @endif

                    {{-- Búsqueda de cliente --}}
                    @if($saleType === 'customer')
                        <div class="relative">
                            @if($selectedCustomer)
                                <div class="flex items-center justify-between bg-blue-50 rounded-lg px-2 py-1.5">
                                    <div>
                                        <div class="text-xs font-bold text-blue-800">{{ $selectedCustomer->name }}</div>
                                        <div class="text-[10px] text-blue-600">{{ $selectedCustomer->phone }}</div>
                                    </div>
                                    <button wire:click="clearCustomer" class="text-red-400 hover:text-red-600">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                        </svg>
                                    </button>
                                </div>
                            @else
                                <input wire:model.live.debounce.300ms="customerSearch"
                                       type="text"
                                       placeholder="Buscar cliente..."
                                       class="w-full px-2 py-1.5 border border-gray-300 rounded-lg text-xs focus:ring-2 focus:ring-blue-500">
                                @if($customers->count() > 0)
                                    <div class="absolute z-10 w-full bg-white rounded-lg shadow-xl border mt-1">
                                        @foreach($customers as $customer)
                                            <button wire:click="selectCustomer({{ $customer->id }})"
                                                    class="w-full text-left px-3 py-2 hover:bg-blue-50 text-xs border-b last:border-0">
                                                <div class="font-bold">{{ $customer->name }}</div>
                                                <div class="text-gray-500">{{ $customer->phone }}</div>
                                            </button>
                                        @endforeach
                                    </div>
                                @endif
                            @endif
                        </div>
                    @endif

                    {{-- Delivery App --}}
                    @if($saleType === 'delivery_app')
                        <div class="space-y-1.5">
                            <div class="bg-orange-50 border border-orange-200 rounded px-2 py-1 text-[10px] text-orange-700 font-medium">
                                🛵 Precios App activos
                            </div>
                            <select wire:model="deliveryAppName"
                                    class="w-full px-2 py-1.5 border border-gray-300 rounded text-xs">
                                <option value="Pedidos Ya">Pedidos Ya</option>
                                <option value="Rappi">Rappi</option>
                                <option value="Otro">Otro</option>
                            </select>
                        </div>
                    @endif
                </div>

                {{-- Carrito --}}
                <div class="flex-1 bg-white rounded-xl shadow-lg flex flex-col overflow-hidden">

                    {{-- Header carrito --}}
                    <div class="p-2 border-b bg-gray-50 flex-shrink-0">
                        <div class="flex justify-between items-center">
                            <h3 class="font-bold text-gray-900 text-sm">
                                🛒 Carrito
                                @if(count($cart) > 0)
                                    <span class="bg-purple-600 text-white text-xs px-1.5 py-0.5 rounded-full ml-1">{{ count($cart) }}</span>
                                @endif
                            </h3>
                            @if(count($cart) > 0)
                                <button wire:click="clearCart" class="text-red-600 hover:text-red-700 text-xs font-bold">Vaciar</button>
                            @endif
                        </div>
                    </div>

                    {{-- Items --}}
                    <div class="flex-1 overflow-y-auto p-2">
                        @forelse($cart as $key => $item)
                            @php
                                $isWeight  = ($item['type'] ?? 'unit') === 'weight';
                                $extras    = $item['extras'] ?? 0;
                                $unitTotal = $isWeight
                                    ? $item['price']
                                    : ($item['price'] + $extras) * $item['quantity'];
                            @endphp
                            <div wire:key="cart-{{ $key }}"
                                 class="bg-gray-50 rounded-lg p-2 mb-1.5 border {{ $isWeight ? 'border-orange-200' : 'border-gray-200' }}">

                                <div class="flex justify-between items-start mb-1">
                                    <div class="flex-1 min-w-0">
                                        <div class="font-bold text-xs text-gray-900 line-clamp-1">{{ $item['product_name'] }}</div>
                                        <div class="text-[10px] text-gray-500">
                                            @if($isWeight)
                                                <span class="text-orange-600">⚖️ {{ number_format($item['weight'], 3, ',', '.') }} kg</span>
                                            @else
                                                {{ $item['volume'] >= 1000 ? ($item['volume']/1000).'L' : $item['volume'].'ml' }}
                                                @if($extras > 0)
                                                    <span class="text-orange-500 ml-1">+{{ number_format($extras, 0, ',', '.') }} extras</span>
                                                @endif
                                            @endif
                                        </div>
                                        {{-- Complementos seleccionados --}}
                                        @if(!$isWeight && !empty($item['customizations']))
                                            <div class="mt-0.5 flex flex-wrap gap-0.5">
                                                @foreach($item['customizations'] as $c)
                                                    <span class="text-[9px] bg-purple-100 text-purple-700 px-1 py-0.5 rounded">{{ $c['name'] }}</span>
                                                @endforeach
                                            </div>
                                        @endif
                                    </div>
                                    <button wire:click="removeFromCart('{{ $key }}')"
                                            class="text-red-400 hover:text-red-600 ml-1 flex-shrink-0">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                        </svg>
                                    </button>
                                </div>

                                <div class="flex items-center justify-between">
                                    @if(!$isWeight)
                                        <div class="flex items-center gap-1">
                                            <button wire:click="updateQuantity('{{ $key }}', 'decrement')"
                                                    class="bg-gray-200 hover:bg-gray-300 text-gray-800 font-bold w-5 h-5 rounded text-xs leading-none">−</button>
                                            <span class="font-bold w-4 text-center text-xs">{{ $item['quantity'] }}</span>
                                            <button wire:click="updateQuantity('{{ $key }}', 'increment')"
                                                    class="bg-purple-600 hover:bg-purple-700 text-white font-bold w-5 h-5 rounded text-xs leading-none">+</button>
                                        </div>
                                    @else
                                        <div class="text-[10px] text-orange-600 font-semibold">⚖️</div>
                                    @endif
                                    <div class="font-black text-xs {{ $isWeight ? 'text-orange-600' : 'text-purple-700' }}">
                                        {{ number_format($unitTotal, 0, ',', '.') }} Gs
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="text-center py-6 text-gray-400">
                                <div class="text-3xl mb-2">🛒</div>
                                <div class="text-xs">Carrito vacío</div>
                            </div>
                        @endforelse
                    </div>

                    {{-- Footer: Pago Dividido + Total + Botones --}}
                    <div class="flex-shrink-0 bg-gradient-to-br from-violet-600 to-purple-600 p-3 text-white">

                        {{-- Toggle Pago Dividido --}}
                        @if(count($cart) > 0)
                            <button wire:click="toggleSplitPayment"
                                    type="button"
                                    class="w-full mb-2 py-2 px-3 rounded-lg text-xs font-bold transition-all
                                        {{ $useSplitPayment
                                            ? 'bg-gradient-to-r from-yellow-400 to-orange-400 text-slate-900'
                                            : 'bg-white/20 hover:bg-white/30 text-white' }}">
                                {{ $useSplitPayment ? '✓ Pago Dividido activo' : '💳 Pago Dividido' }}
                            </button>
                        @endif

                        {{-- Panel de pago dividido --}}
                        @if($useSplitPayment && count($cart) > 0)
                            <div class="bg-white/10 backdrop-blur-sm rounded-lg p-2 mb-2 border border-white/20">
                                <div class="flex justify-between text-xs mb-1">
                                    <span class="text-white/90">Pagado:</span>
                                    <span class="font-bold">{{ number_format($totalSplitPaid, 0, ',', '.') }} Gs</span>
                                </div>
                                <div class="flex justify-between text-xs mb-2">
                                    <span class="text-white/90">Falta:</span>
                                    <span class="font-bold {{ $remainingAmount > 0 ? 'text-yellow-300' : 'text-emerald-300' }}">
                                        {{ number_format($remainingAmount, 0, ',', '.') }} Gs
                                    </span>
                                </div>

                                {{-- Barra de progreso --}}
                                <div class="w-full bg-white/20 rounded-full h-1.5 overflow-hidden mb-2">
                                    <div class="bg-gradient-to-r from-emerald-400 to-teal-400 h-full transition-all"
                                         style="width: {{ $cartTotal > 0 ? min(100, ($totalSplitPaid / $cartTotal) * 100) : 0 }}%"></div>
                                </div>

                                {{-- Lista de pagos agregados --}}
                                @if(count($splitPayments) > 0)
                                    <div class="space-y-1 max-h-24 overflow-y-auto">
                                        @foreach($splitPayments as $index => $payment)
                                            @php $method = $paymentMethods->firstWhere('id', $payment['method_id']); @endphp
                                            <div wire:key="split-{{ $index }}"
                                                 class="flex justify-between items-center bg-white/10 rounded px-2 py-1 text-xs border border-white/10">
                                                <span class="font-medium truncate flex-1">{{ $method?->name ?? 'Método' }}</span>
                                                <div class="flex items-center gap-1.5">
                                                    <span class="font-bold text-[11px]">
                                                        @if($payment['currency'] === 'BRL')
                                                            {{ number_format($payment['amount'], 2, ',', '.') }} R$
                                                        @else
                                                            {{ number_format($payment['amount_gs'] / 1000, 0) }}k
                                                        @endif
                                                    </span>
                                                    <button wire:click="removeSplitPayment({{ $index }})"
                                                            class="text-rose-300 hover:text-rose-100">
                                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                                        </svg>
                                                    </button>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        @endif

                        {{-- Total --}}
                        <div class="flex justify-between items-baseline mb-2">
                            <span class="font-bold text-sm">TOTAL</span>
                            <span class="font-black text-3xl">{{ number_format($cartTotal / 1000, 0) }}k</span>
                        </div>

                        @if(count($cart) > 0)
                            {{-- Botones de pago rápido --}}
                            <div class="grid grid-cols-2 gap-1.5 mb-2">
                                @foreach($paymentMethods->take(4) as $method)
                                    @php
                                        $nameLower = mb_strtolower($method->name);
                                        // Icono según tipo/nombre
                                        $icon = match(true) {
                                            str_contains($nameLower, 'real') || str_contains($nameLower, 'brl') => '🇧🇷',
                                            str_contains($nameLower, 'efectivo') || str_contains($nameLower, 'cash') => '💵',
                                            str_contains($nameLower, 'tarjeta') || str_contains($nameLower, 'débito') || str_contains($nameLower, 'crédito') => '💳',
                                            str_contains($nameLower, 'transfer') => '🏦',
                                            str_contains($nameLower, 'qr') || str_contains($nameLower, 'pago móvil') => '📱',
                                            $method->type === 'cash' => '💵',
                                            $method->type === 'foreign_currency' => '🇧🇷',
                                            default => '💰',
                                        };
                                        // Etiqueta corta inteligente: quitar palabras genéricas y abreviar
                                        $label = $method->name;
                                        $label = preg_replace('/\b(en|de|con|pago|método)\b/i', '', $label);
                                        $label = trim(preg_replace('/\s+/', ' ', $label));
                                        // Si sigue siendo largo, tomar las iniciales de cada palabra (máx 2 palabras)
                                        $words = explode(' ', $label);
                                        if (mb_strlen($label) > 10 && count($words) >= 2) {
                                            // Mostrar primera palabra completa si es corta, sino abreviar
                                            $label = mb_strlen($words[0]) <= 8
                                                ? $words[0] . ' ' . mb_strtoupper(mb_substr($words[1], 0, 1)) . '.'
                                                : mb_substr($words[0], 0, 8) . '.';
                                        }
                                    @endphp
                                    <button wire:click="quickSale({{ $method->id }})"
                                            wire:loading.attr="disabled"
                                            title="{{ $method->name }}"
                                            class="bg-white/20 hover:bg-white/30 active:bg-white/40 text-white font-bold py-2 px-1 rounded-lg transition-all disabled:opacity-50 border border-white/10 flex flex-col items-center justify-center gap-0.5 min-h-[52px]">
                                        <span class="text-base leading-none">{{ $useSplitPayment ? '➕' : $icon }}</span>
                                        <span class="text-[11px] leading-tight text-center w-full px-1 truncate">{{ $label }}</span>
                                    </button>
                                @endforeach
                            </div>

                            {{-- Confirmar venta dividida (solo cuando cubrió el total) --}}
                            @if($useSplitPayment && $remainingAmount <= 0)
                                <button wire:click="processSplitPayment"
                                        wire:loading.attr="disabled"
                                        class="w-full bg-gradient-to-r from-emerald-500 to-teal-500 hover:from-emerald-600 hover:to-teal-600 text-white font-bold py-3 rounded-lg mb-1.5 shadow-lg text-sm disabled:opacity-50">
                                    <span wire:loading.remove>✓ Confirmar Venta</span>
                                    <span wire:loading>Procesando...</span>
                                </button>
                            @endif

                            {{-- Más métodos de pago --}}
                            <button wire:click="openPaymentModal"
                                    class="w-full bg-white text-violet-600 font-bold py-2.5 rounded-lg hover:shadow-xl transition-all text-xs">
                                Más métodos de pago
                            </button>
                        @else
                            <button disabled
                                    class="w-full bg-white/20 text-white/50 font-bold py-2.5 rounded-lg cursor-not-allowed text-xs">
                                Agregá productos al carrito
                            </button>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ══════════════════════════════════════════════════════════════════════ --}}
    {{-- MODAL: VENTA POR PESO                                                 --}}
    {{-- ══════════════════════════════════════════════════════════════════════ --}}
    @if($showWeightModal && $selectedWeightProduct)
        <div class="fixed inset-0 bg-black/60 backdrop-blur-sm flex items-center justify-center p-4 z-50"
             x-on:keydown.escape.window="$wire.closeWeightModal()">
            <div class="bg-white rounded-2xl max-w-md w-full shadow-2xl overflow-hidden">

                @php
                    $kgPriceModal = match($priceChannel) {
                        'delivery_app' => $selectedWeightProduct->price_per_kg_delivery_app ?: $selectedWeightProduct->price_per_kg,
                        default        => $selectedWeightProduct->price_per_kg_pos ?: $selectedWeightProduct->price_per_kg,
                    };
                @endphp

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
                    <div class="mt-2 bg-white/20 rounded-lg px-3 py-2 inline-block">
                        <span class="font-bold text-lg">{{ number_format($kgPriceModal, 0, ',', '.') }} Gs/kg</span>
                        <span class="text-xs text-orange-200 ml-1">· Precio {{ $priceChannel === 'delivery_app' ? 'App' : 'POS' }}</span>
                    </div>
                </div>

                <div class="p-5" x-data="{ mode: @entangle('weightInputMode') }">

                    {{-- Toggle modo --}}
                    <div class="flex rounded-xl bg-gray-100 p-1 mb-5">
                        <button @click="mode = 'amount'; $wire.setWeightInputMode('amount')"
                                :class="mode === 'amount' ? 'bg-white shadow-md text-orange-600' : 'text-gray-600'"
                                class="flex-1 py-2.5 px-4 rounded-lg font-bold text-sm transition-all">
                            💰 Por Monto (Gs)
                        </button>
                        <button @click="mode = 'weight'; $wire.setWeightInputMode('weight')"
                                :class="mode === 'weight' ? 'bg-white shadow-md text-orange-600' : 'text-gray-600'"
                                class="flex-1 py-2.5 px-4 rounded-lg font-bold text-sm transition-all">
                            ⚖️ Por Peso (kg)
                        </button>
                    </div>

                    {{-- Modo: por monto --}}
                    <div x-show="mode === 'amount'" x-cloak>
                        <label class="block text-sm font-medium text-gray-700 mb-2">¿Cuánto quiere llevar? (Guaraníes)</label>
                        <div class="relative mb-3">
                            <input wire:model.live.debounce.300ms="amountInput"
                                   type="text"
                                   inputmode="numeric"
                                   placeholder="Ej: 30000"
                                   class="w-full px-4 py-4 text-2xl font-bold text-center border-2 border-gray-300 rounded-xl focus:ring-2 focus:ring-orange-500 focus:border-orange-500"
                                   autofocus>
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
                        @if($amountInput && (float) str_replace(['.', ','], ['', '.'], $amountInput) > 0)
                            @php
                                $inputAmt = (float) str_replace(['.', ','], ['', '.'], $amountInput);
                                $calcKg   = $kgPriceModal > 0 ? $inputAmt / $kgPriceModal : 0;
                            @endphp
                            <div class="bg-green-50 rounded-xl p-4 border-2 border-green-300 text-center">
                                <p class="text-sm text-green-700 mb-1">Esto equivale a:</p>
                                <p class="text-3xl font-black text-green-600">{{ number_format($calcKg, 3, ',', '.') }} kg</p>
                                <p class="text-sm text-green-600 mt-1">({{ number_format($calcKg * 1000, 0, ',', '.') }} gramos)</p>
                            </div>
                        @endif
                    </div>

                    {{-- Modo: por peso --}}
                    <div x-show="mode === 'weight'" x-cloak>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Ingrese el peso en kilogramos:</label>
                        <div class="relative mb-3">
                            <input wire:model.live.debounce.300ms="weightInput"
                                   type="text"
                                   inputmode="decimal"
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
                        @if($weightInput && (float) str_replace(',', '.', $weightInput) > 0)
                            @php $calcAmt = $kgPriceModal * (float) str_replace(',', '.', $weightInput); @endphp
                            <div class="bg-green-50 rounded-xl p-4 border-2 border-green-300 text-center">
                                <p class="text-sm text-green-700 mb-1">Total a cobrar:</p>
                                <p class="text-3xl font-black text-green-600">{{ number_format($calcAmt, 0, ',', '.') }} Gs</p>
                            </div>
                        @endif
                    </div>
                </div>

                <div class="p-4 bg-gray-50 flex gap-3">
                    <button wire:click="closeWeightModal"
                            class="flex-1 bg-gray-200 hover:bg-gray-300 text-gray-800 font-bold py-3 rounded-xl transition-all">
                        Cancelar
                    </button>
                    <button wire:click="addWeightToCart"
                            wire:loading.attr="disabled"
                            wire:target="addWeightToCart"
                            class="flex-1 bg-orange-500 hover:bg-orange-600 text-white font-bold py-3 rounded-xl transition-all disabled:opacity-50">
                        <span wire:loading.remove wire:target="addWeightToCart">✓ Agregar</span>
                        <span wire:loading wire:target="addWeightToCart">Agregando...</span>
                    </button>
                </div>
            </div>
        </div>
    @endif

    {{-- ══════════════════════════════════════════════════════════════════════ --}}
    {{-- MODAL: LISTA DE MÉTODOS DE PAGO                                       --}}
    {{-- ══════════════════════════════════════════════════════════════════════ --}}
    @if($showPaymentModal)
        <div class="fixed inset-0 bg-black/60 backdrop-blur-sm flex items-center justify-center p-4 z-50"
             x-on:keydown.escape.window="$wire.closePaymentModal()">
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
                        <span class="font-bold text-gray-700">
                            {{ $useSplitPayment ? 'Falta por pagar:' : 'Total a cobrar:' }}
                        </span>
                        <span class="font-black text-2xl text-purple-600">
                            {{ number_format($useSplitPayment ? $remainingAmount : $cartTotal, 0, ',', '.') }} Gs
                        </span>
                    </div>
                </div>

                <div class="space-y-2 mb-6">
                    @foreach($paymentMethods as $method)
                        @php
                            $nameLower = mb_strtolower($method->name);
                            $modalIcon = match(true) {
                                str_contains($nameLower, 'real') || str_contains($nameLower, 'brl') || $method->type === 'foreign_currency' => '🇧🇷',
                                str_contains($nameLower, 'efectivo') || str_contains($nameLower, 'cash') || ($method->type === 'cash' && !str_contains($nameLower, 'real')) => '💵',
                                str_contains($nameLower, 'tarjeta') || str_contains($nameLower, 'débito') || str_contains($nameLower, 'crédito') => '💳',
                                str_contains($nameLower, 'transfer') => '🏦',
                                str_contains($nameLower, 'qr') || str_contains($nameLower, 'pago móvil') => '📱',
                                default => '💰',
                            };
                        @endphp
                        <button wire:click="quickSale({{ $method->id }})"
                                wire:loading.attr="disabled"
                                class="w-full bg-gray-100 hover:bg-purple-100 active:bg-purple-200 text-gray-900 font-bold py-4 rounded-xl transition-all flex items-center justify-between px-4 disabled:opacity-50">
                            <span class="flex items-center gap-3">
                                <span class="text-2xl">{{ $modalIcon }}</span>
                                {{ $method->name }}
                            </span>
                            <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
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

    {{-- ══════════════════════════════════════════════════════════════════════ --}}
    {{-- MODAL: CALCULADORA DE VUELTO (efectivo / BRL)                         --}}
    {{-- ══════════════════════════════════════════════════════════════════════ --}}
    @if($showChangeCalculator)
        @php
            $isBrl        = $changeCalculatorCurrency === 'BRL';
            $amountToShow = $useSplitPayment ? $remainingAmount : $cartTotal;
            $headerColor  = $isBrl
                ? 'bg-gradient-to-r from-blue-500 to-indigo-500'
                : 'bg-gradient-to-r from-green-500 to-emerald-500';
            $ringColor    = $isBrl ? 'focus:ring-blue-500' : 'focus:ring-green-500';
            $btnColor     = $isBrl
                ? 'bg-blue-500 hover:bg-blue-600'
                : 'bg-green-500 hover:bg-green-600';
        @endphp
        <div class="fixed inset-0 bg-black/50 flex items-center justify-center p-4 z-[60]"
             x-on:keydown.escape.window="$wire.closeChangeCalculator()">
            <div class="bg-white rounded-2xl w-full max-w-lg shadow-2xl overflow-hidden max-h-[95vh] flex flex-col">

                {{-- Header --}}
                <div class="flex-shrink-0 p-4 {{ $headerColor }} text-white">
                    <div class="flex justify-between items-center mb-3">
                        <div>
                            <h2 class="text-xl font-black">{{ $isBrl ? '💵 Reales Brasileños' : '💵 Efectivo' }}</h2>
                            <p class="text-xs opacity-80">
                                {{ $useSplitPayment ? 'Pago parcial — ' : '' }}Calculadora de vuelto
                            </p>
                        </div>
                        <button wire:click="closeChangeCalculator" class="text-white/80 hover:text-white">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>

                    <div class="bg-white/20 backdrop-blur-sm rounded-lg p-3">
                        <div class="text-xs opacity-80">{{ $useSplitPayment ? 'Falta por pagar' : 'Total a cobrar' }}</div>
                        <div class="text-2xl font-black">{{ number_format($amountToShow, 0, ',', '.') }} Gs</div>
                        @if($isBrl)
                            <div class="text-sm opacity-75 mt-1">
                                ≈ {{ number_format($amountToShow / $calculatedChange['rate'], 2, ',', '.') }} R$
                            </div>
                        @endif
                    </div>

                    @if($isBrl)
                        <div class="mt-2 text-center">
                            <span class="text-xs bg-white/20 px-2 py-1 rounded-full">
                                💱 1 R$ = {{ number_format($calculatedChange['rate'], 0, ',', '.') }} Gs
                            </span>
                        </div>
                    @endif
                </div>

                {{-- Body --}}
                <div class="flex-1 overflow-y-auto p-4 space-y-3">

                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2">
                            Cliente paga: <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <input wire:model.live="amountReceived"
                                   type="text"
                                   inputmode="numeric"
                                   placeholder="0"
                                   class="w-full px-4 py-3 text-2xl font-bold text-center border-2 border-gray-300 rounded-xl focus:ring-2 {{ $ringColor }}"
                                   autofocus>
                            <span class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 font-bold">
                                {{ $isBrl ? 'R$' : 'Gs' }}
                            </span>
                        </div>
                    </div>

                    {{-- Atajos rápidos --}}
                    <div class="grid grid-cols-4 gap-2">
                        @if($isBrl)
                            @foreach([5, 10, 20, 50] as $quick)
                                <button wire:click="$set('amountReceived', '{{ $quick }}')" type="button"
                                        class="py-2 text-sm font-bold rounded-lg border-2 border-gray-200 hover:border-blue-400 hover:bg-blue-50 transition-all">
                                    {{ $quick }} R$
                                </button>
                            @endforeach
                        @else
                            @foreach([10000, 20000, 50000, 100000] as $quick)
                                <button wire:click="$set('amountReceived', '{{ $quick }}')" type="button"
                                        class="py-2 text-sm font-bold rounded-lg border-2 border-gray-200 hover:border-green-400 hover:bg-green-50 transition-all">
                                    {{ number_format($quick / 1000, 0) }}k
                                </button>
                            @endforeach
                        @endif
                    </div>

                    {{-- Resultado en tiempo real --}}
                    @if($amountReceived && (float) str_replace(['.', ','], ['', '.'], $amountReceived) > 0)
                        <div class="bg-gray-50 rounded-xl p-3 space-y-2">

                            @if($isBrl)
                                <div class="flex justify-between text-sm">
                                    <span class="text-gray-600">Equivale a:</span>
                                    <span class="font-bold text-blue-600">{{ number_format($calculatedChange['receivedInGs'], 0, ',', '.') }} Gs</span>
                                </div>
                            @endif

                            @if($useSplitPayment)
                                {{-- Panel dividido --}}
                                @php
                                    $amountToAdd  = $isBrl
                                        ? round((float) str_replace(['.', ','], ['', '.'], $amountReceived) * $calculatedChange['rate'])
                                        : (float) str_replace(['.', ','], ['', '.'], $amountReceived);
                                    $newRemaining = max(0, $remainingAmount - $amountToAdd);
                                @endphp
                                <div class="bg-blue-50 border-2 border-blue-300 rounded-lg p-3">
                                    <div class="flex justify-between items-center mb-2">
                                        <span class="text-sm text-blue-700 font-semibold">Este pago:</span>
                                        <span class="text-xl font-black text-blue-600">
                                            {{ number_format(min($amountToAdd, $remainingAmount), 0, ',', '.') }} Gs
                                        </span>
                                    </div>
                                    @if($newRemaining > 0)
                                        <div class="flex justify-between items-center pt-2 border-t border-blue-200">
                                            <span class="text-sm text-blue-600">Quedará:</span>
                                            <span class="text-lg font-bold text-orange-600">
                                                {{ number_format($newRemaining, 0, ',', '.') }} Gs
                                            </span>
                                        </div>
                                    @else
                                        <div class="bg-green-100 border border-green-300 rounded-lg p-2 mt-2 text-center">
                                            <span class="text-green-700 text-sm font-bold">✓ Total completo</span>
                                        </div>
                                    @endif
                                </div>
                                @if($amountToAdd > $remainingAmount + 50)
                                    <div class="bg-orange-50 border border-orange-200 rounded-lg p-2 text-center">
                                        <span class="text-orange-600 text-xs font-bold">
                                            ⚠️ Excede por {{ number_format($amountToAdd - $remainingAmount, 0, ',', '.') }} Gs — se ajustará automáticamente
                                        </span>
                                    </div>
                                @endif

                            @else
                                {{-- Panel pago único --}}
                                @php
                                    $changeGs      = $calculatedChange['changeInGs'];
                                    $withinTolerance = $changeGs >= -50 && $changeGs < 0; // falta poco → aceptar
                                    $displayChange = $withinTolerance ? 0 : $changeGs;    // mostrar 0 si es dentro de tolerancia
                                    $changeColor   = $displayChange > 0 ? 'text-green-600' : ($displayChange < 0 ? 'text-red-600' : 'text-gray-600');
                                @endphp
                                <div class="flex justify-between items-center border-t pt-2">
                                    <span class="font-bold text-gray-700">Vuelto:</span>
                                    <div class="text-right">
                                        <div class="text-xl font-black {{ $changeColor }}">
                                            {{ number_format($displayChange, 0, ',', '.') }} Gs
                                        </div>
                                        @if($isBrl && $displayChange > 0)
                                            <div class="text-xs text-gray-500 mt-1">
                                                ≈ {{ number_format($calculatedChange['changeInBrl'], 2, ',', '.') }} R$
                                            </div>
                                        @endif
                                    </div>
                                </div>
                                @if($withinTolerance)
                                    {{-- Diferencia por redondeo de cotización — aceptar --}}
                                    <div class="bg-green-50 border border-green-200 rounded-lg p-2 text-center">
                                        <span class="text-green-700 text-xs font-bold">
                                            ✓ Diferencia de {{ number_format(abs($changeGs), 0, ',', '.') }} Gs por redondeo — se acepta
                                        </span>
                                    </div>
                                @elseif($changeGs < -50)
                                    <div class="bg-red-50 border border-red-200 rounded-lg p-2 text-center">
                                        <span class="text-red-600 text-sm font-bold">
                                            ⚠️ Falta {{ number_format(abs($changeGs), 0, ',', '.') }} Gs
                                        </span>
                                    </div>
                                @endif
                            @endif
                        </div>
                    @endif
                </div>

                {{-- Footer --}}
                <div class="flex-shrink-0 p-3 bg-gray-50 flex gap-2 border-t">
                    <button wire:click="closeChangeCalculator"
                            class="flex-1 bg-gray-200 hover:bg-gray-300 text-gray-800 font-bold py-3 rounded-xl transition-all">
                        Cancelar
                    </button>
                    <button wire:click="confirmPaymentWithChange"
                            wire:loading.attr="disabled"
                            wire:target="confirmPaymentWithChange"
                            class="flex-1 {{ $btnColor }} text-white font-bold py-3 rounded-xl transition-all disabled:opacity-50">
                        <span wire:loading.remove wire:target="confirmPaymentWithChange">✓ Confirmar</span>
                        <span wire:loading wire:target="confirmPaymentWithChange">...</span>
                    </button>
                </div>
            </div>
        </div>
    @endif

    {{-- ══════════════════════════════════════════════════════════════════════ --}}
    {{-- MODAL: MONTO PARA TARJETA / TRANSFERENCIA (pago dividido)             --}}
    {{-- ══════════════════════════════════════════════════════════════════════ --}}
    @if($showSplitAmountModal && $pendingSplitMethodId)
        @php $pendingMethod = $paymentMethods->firstWhere('id', $pendingSplitMethodId); @endphp
        <div class="fixed inset-0 bg-black/60 backdrop-blur-sm flex items-center justify-center p-4 z-[60]"
             x-on:keydown.escape.window="$wire.set('showSplitAmountModal', false)">
            <div class="bg-white rounded-3xl max-w-md w-full shadow-2xl overflow-hidden">

                <div class="p-6 bg-gradient-to-r from-violet-600 to-purple-600 text-white">
                    <div class="flex justify-between items-start mb-4">
                        <div>
                            <h2 class="text-2xl font-black">{{ $pendingMethod?->name }}</h2>
                            <p class="text-sm mt-1 opacity-90">Ingrese el monto a pagar</p>
                        </div>
                        <button wire:click="$set('showSplitAmountModal', false)" class="text-white/80 hover:text-white">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>
                    <div class="bg-white/20 backdrop-blur-sm rounded-xl p-4">
                        <div class="text-xs opacity-90 mb-1">Falta por pagar</div>
                        <div class="text-3xl font-black">{{ number_format($remainingAmount, 0, ',', '.') }} Gs</div>
                    </div>
                </div>

                <div class="p-6">
                    <label class="block text-sm font-bold text-slate-700 mb-2">
                        Monto a pagar: <span class="text-rose-500">*</span>
                    </label>
                    <div class="relative mb-4">
                        <input wire:model.live="amountReceived"
                               type="text"
                               inputmode="numeric"
                               placeholder="0"
                               class="w-full px-4 py-4 text-3xl font-bold text-center border-2 border-slate-200 rounded-xl focus:ring-4 focus:ring-violet-500/20 focus:border-violet-500"
                               autofocus>
                        <span class="absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 font-bold text-lg">Gs</span>
                    </div>

                    <div class="grid grid-cols-4 gap-2 mb-4">
                        @foreach([10000, 20000, 50000, 100000] as $quick)
                            <button wire:click="$set('amountReceived', '{{ $quick }}')" type="button"
                                    class="py-2 text-sm font-bold rounded-lg border-2 border-slate-200 hover:border-violet-400 hover:bg-violet-50 transition-all">
                                {{ number_format($quick / 1000, 0) }}k
                            </button>
                        @endforeach
                    </div>

                    @if($amountReceived && (float) str_replace(['.', ','], ['', '.'], $amountReceived) > 0)
                        @php $inputAmt = (float) str_replace(['.', ','], ['', '.'], $amountReceived); @endphp
                        @if($inputAmt > $remainingAmount + 50)
                            <div class="bg-rose-50 border border-rose-200 rounded-lg p-3 text-center text-rose-600 text-sm font-bold mb-4">
                                ⚠️ El monto excede lo que falta
                            </div>
                        @else
                            <div class="bg-emerald-50 border border-emerald-200 rounded-lg p-3 text-center mb-4">
                                <div class="text-sm text-emerald-700 font-semibold">Quedará por pagar:</div>
                                <div class="text-2xl font-black text-emerald-600">
                                    {{ number_format(max(0, $remainingAmount - $inputAmt), 0, ',', '.') }} Gs
                                </div>
                            </div>
                        @endif
                    @endif
                </div>

                <div class="p-4 bg-slate-50 flex gap-3 border-t">
                    <button wire:click="$set('showSplitAmountModal', false)"
                            class="flex-1 bg-slate-200 hover:bg-slate-300 text-slate-800 font-bold py-3 rounded-xl transition-all">
                        Cancelar
                    </button>
                    <button wire:click="confirmSplitAmount"
                            wire:loading.attr="disabled"
                            wire:target="confirmSplitAmount"
                            class="flex-1 bg-gradient-to-r from-violet-600 to-purple-600 hover:from-violet-700 hover:to-purple-700 text-white font-bold py-3 rounded-xl transition-all disabled:opacity-50 shadow-lg">
                        <span wire:loading.remove wire:target="confirmSplitAmount">✓ Agregar Pago</span>
                        <span wire:loading wire:target="confirmSplitAmount">Agregando...</span>
                    </button>
                </div>
            </div>
        </div>
    @endif

    {{-- ══════════════════════════════════════════════════════════════════════ --}}
    {{-- MODAL: VUELTO CONFIRMADO (muestra cuánto devolver)                    --}}
    {{-- ══════════════════════════════════════════════════════════════════════ --}}
    @if($showChangeModal && !empty($changeSnapshot))
        <div class="fixed inset-0 bg-black/60 backdrop-blur-sm flex items-center justify-center p-4 z-50">
            <div class="bg-white rounded-2xl max-w-md w-full shadow-2xl overflow-hidden">

                <div class="bg-gradient-to-r from-green-500 to-emerald-500 p-6 text-center">
                    <div class="text-6xl mb-3">💵</div>
                    <h2 class="text-2xl font-black text-white">Dar Vuelto</h2>
                    <p class="text-green-100 text-sm mt-1">Entregue el cambio al cliente</p>
                </div>

                <div class="p-6">
                    <div class="bg-gray-50 rounded-xl p-4 mb-4 space-y-2">
                        <div class="flex justify-between items-center">
                            <span class="text-gray-600">Total venta:</span>
                            <span class="font-bold text-lg">{{ number_format($changeSnapshot['base'] ?? 0, 0, ',', '.') }} Gs</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-gray-600">Recibido:</span>
                            <span class="font-bold text-lg">
                                @if(($changeSnapshot['rate'] ?? 1) > 1)
                                    {{ number_format($changeSnapshot['received'] ?? 0, 2, ',', '.') }} R$
                                    <span class="text-sm text-gray-500">({{ number_format($changeSnapshot['receivedInGs'] ?? 0, 0, ',', '.') }} Gs)</span>
                                @else
                                    {{ number_format($changeSnapshot['received'] ?? 0, 0, ',', '.') }} Gs
                                @endif
                            </span>
                        </div>
                        @if(($changeSnapshot['rate'] ?? 1) > 1)
                            <div class="flex justify-between items-center text-xs text-gray-400">
                                <span>Cotización:</span>
                                <span>1 R$ = {{ number_format($changeSnapshot['rate'], 0, ',', '.') }} Gs</span>
                            </div>
                        @endif
                    </div>

                    <div class="bg-gradient-to-br from-green-50 to-emerald-50 border-2 border-green-300 rounded-xl p-6 text-center">
                        <div class="text-sm text-green-700 font-semibold mb-2">VUELTO A ENTREGAR</div>
                        <div class="text-5xl font-black text-green-600 mb-2">
                            {{ number_format($changeSnapshot['changeInGs'] ?? 0, 0, ',', '.') }}
                        </div>
                        <div class="text-lg font-bold text-green-700">Guaraníes</div>

                        @if(($changeSnapshot['changeInBrl'] ?? 0) > 0)
                            <div class="mt-3 pt-3 border-t border-green-200">
                                <div class="text-xs text-green-600 mb-1">O equivalente en Reales:</div>
                                <div class="text-2xl font-black text-blue-600">
                                    {{ number_format($changeSnapshot['changeInBrl'], 2, ',', '.') }} R$
                                </div>
                            </div>
                        @endif
                    </div>
                </div>

                <div class="p-4 bg-gray-50">
                    <button wire:click="closeChangeModal"
                            class="w-full bg-green-500 hover:bg-green-600 text-white font-bold py-4 rounded-xl transition-all text-lg">
                        ✓ Vuelto entregado — Ver Ticket
                    </button>
                </div>
            </div>
        </div>
    @endif

    {{-- ══════════════════════════════════════════════════════════════════════ --}}
    {{-- MODAL: TICKET DE VENTA                                                --}}
    {{-- ══════════════════════════════════════════════════════════════════════ --}}
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
                                <span class="font-bold text-green-600">
                                    {{ number_format($lastOrder->total - $lastOrder->delivery_app_commission, 0, ',', '.') }} Gs
                                </span>
                            </div>
                        @endif

                        {{-- Ítems --}}
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

                        {{-- Total y método --}}
                        <div class="flex justify-between items-center pt-3 mt-3 border-t border-gray-300">
                            <span class="font-bold text-gray-600">Total:</span>
                            <span class="font-black text-2xl text-purple-600">{{ number_format($lastOrder->total, 0, ',', '.') }} Gs</span>
                        </div>
                        <div class="flex justify-between items-center mt-2">
                            <span class="text-gray-600">Pago:</span>
                            @if($lastOrder->is_split_payment)
                                <span class="font-bold text-purple-600">Pago Dividido</span>
                            @else
                                <span class="font-bold text-green-600">{{ $lastOrder->paymentMethod?->name ?? 'N/A' }}</span>
                            @endif
                        </div>

                        {{-- Detalle pagos divididos --}}
                        @if($lastOrder->is_split_payment && $lastOrder->payments->count() > 0)
                            <div class="mt-3 pt-3 border-t border-gray-200">
                                <div class="text-xs text-gray-500 font-semibold mb-2">Detalle de pagos:</div>
                                <div class="space-y-1">
                                    @foreach($lastOrder->payments as $payment)
                                        <div class="flex justify-between text-sm">
                                            <span class="text-gray-600">{{ $payment->paymentMethod->name }}</span>
                                            <span class="font-medium text-gray-800">
                                                @if(isset($payment->details['original_currency']) && $payment->details['original_currency'] === 'BRL')
                                                    {{ number_format($payment->details['original_amount'], 2, ',', '.') }} R$
                                                    <span class="text-xs text-gray-400">({{ number_format($payment->amount, 0, ',', '.') }} Gs)</span>
                                                @else
                                                    {{ number_format($payment->amount, 0, ',', '.') }} Gs
                                                @endif
                                            </span>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </div>
                </div>

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

    {{-- ══════════════════════════════════════════════════════════════════════ --}}
    {{-- MODAL: COMPLEMENTOS POS                                               --}}
    {{-- ══════════════════════════════════════════════════════════════════════ --}}
    @if($showCustomizationsModal && $pendingVariantId)
        @php $pendingVariant = \App\Models\ProductVariant::with('product')->find($pendingVariantId); @endphp
        <div class="fixed inset-0 bg-black/60 backdrop-blur-sm flex items-center justify-center p-4 z-50"
             x-on:keydown.escape.window="$wire.closeCustomizationsModalPOS()">
            <div class="bg-white rounded-2xl w-full max-w-lg shadow-2xl flex flex-col max-h-[90vh]">

                {{-- Header --}}
                <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 flex-shrink-0">
                    <div>
                        <h2 class="text-lg font-black text-gray-900">🍓 Complementos</h2>
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
                        <div wire:key="group-{{ $group['id'] }}">
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
                                        $isMultiple = $group['is_multiple'] ?? false;
                                        $optImgUrl  = \App\Models\CustomizationOption::find($option['id'])?->image_url;
                                    @endphp
                                    <button wire:key="opt-{{ $option['id'] }}"
                                            type="button"
                                            wire:click="toggleCustomization({{ $group['id'] }}, {{ $option['id'] }}, {{ $isMultiple ? 'true' : 'false' }})"
                                            class="flex items-center gap-2 p-2.5 rounded-xl border-2 transition-all duration-150 text-left
                                                {{ $isSelected
                                                    ? 'border-purple-500 bg-purple-50'
                                                    : 'border-gray-200 hover:border-gray-300 bg-white' }}">

                                        @if($optImgUrl)
                                            <div class="w-10 h-10 rounded-lg overflow-hidden flex-shrink-0 border border-gray-100">
                                                <img src="{{ $optImgUrl }}" alt="{{ $option['name'] }}" class="w-full h-full object-cover">
                                            </div>
                                        @endif

                                        <div class="w-4 h-4 rounded-{{ $isMultiple ? 'sm' : 'full' }} border-2 flex items-center justify-center flex-shrink-0 transition-all
                                            {{ $isSelected ? 'border-purple-500 bg-purple-500' : 'border-gray-300' }}">
                                            @if($isSelected)
                                                <svg class="w-2.5 h-2.5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
                                                </svg>
                                            @endif
                                        </div>

                                        <span class="text-sm font-semibold text-gray-800 truncate flex-1 min-w-0">{{ $option['name'] }}</span>

                                        <span class="text-xs font-bold flex-shrink-0 ml-auto {{ $option['price'] > 0 ? 'text-orange-500' : 'text-green-500' }}">
                                            {{ $option['price'] > 0 ? '+' . number_format($option['price'], 0, ',', '.') : 'gratis' }}
                                        </span>
                                    </button>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>

                {{-- Footer con resumen --}}
                <div class="flex-shrink-0 px-6 py-4 border-t border-gray-100 bg-gray-50 rounded-b-2xl">
                    @php
                        $extrasPreview = 0;
                        foreach ($posCustomizationGroups as $grp) {
                            foreach ($grp['options'] as $opt) {
                                if (in_array($opt['id'], $selectedCustomizations[$grp['id']] ?? [])) {
                                    $extrasPreview += $opt['price'];
                                }
                            }
                        }
                        $basePrice    = $pendingVariant?->getPriceForChannel($priceChannel) ?? 0;
                        $totalPreview = $basePrice + $extrasPreview;
                    @endphp

                    <div class="flex items-center justify-between mb-3">
                        <div class="text-xs text-gray-500 space-y-0.5">
                            <div>Base: <span class="font-semibold text-gray-700">{{ number_format($basePrice, 0, ',', '.') }} Gs</span></div>
                            @if($extrasPreview > 0)
                                <div>Extras: <span class="font-semibold text-orange-500">+{{ number_format($extrasPreview, 0, ',', '.') }} Gs</span></div>
                            @endif
                        </div>
                        <div class="text-right">
                            <div class="text-xs text-gray-400">Total unitario</div>
                            <div class="text-2xl font-black text-purple-700">
                                {{ number_format($totalPreview, 0, ',', '.') }}
                                <span class="text-sm font-normal text-gray-400">Gs</span>
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <button wire:click="closeCustomizationsModalPOS"
                                class="py-3 rounded-xl bg-gray-200 hover:bg-gray-300 text-gray-700 font-bold text-sm transition-colors">
                            Cancelar
                        </button>
                        <button wire:click="confirmCustomizationsPOS"
                                wire:loading.attr="disabled"
                                wire:target="confirmCustomizationsPOS"
                                class="py-3 rounded-xl bg-gradient-to-r from-purple-600 to-pink-500 hover:from-purple-700 hover:to-pink-600 text-white font-bold text-sm transition-all shadow-md disabled:opacity-60">
                            <span wire:loading.remove wire:target="confirmCustomizationsPOS">✓ Agregar al carrito</span>
                            <span wire:loading wire:target="confirmCustomizationsPOS">Agregando...</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- ══════════════════════════════════════════════════════════════════════ --}}
    {{-- SCRIPTS                                                               --}}
    {{-- ══════════════════════════════════════════════════════════════════════ --}}
    <script>
        function openPrintTicket(orderId) {
            const w = 400, h = 700;
            window.open(
                `/admin/orders/${orderId}/print`,
                'PrintTicket',
                `width=${w},height=${h},left=${(screen.width-w)/2},top=${(screen.height-h)/2},scrollbars=yes,resizable=yes`
            );
        }
    </script>
</div>