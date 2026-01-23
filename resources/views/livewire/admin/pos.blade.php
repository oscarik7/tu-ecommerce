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
                            @php
                                $productSaleType = $product->sale_type ?? 'unit';
                                $canSellByUnit = in_array($productSaleType, ['unit', 'both']);
                                $canSellByWeight = in_array($productSaleType, ['weight', 'both']);
                            @endphp
                            <div class="bg-white border-2 border-gray-200 rounded-xl overflow-hidden hover:border-purple-500 hover:shadow-lg transition-all">
                                {{-- Imagen del producto --}}
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
                                    
                                    {{-- Badge de tipo de venta --}}
                                    @if($productSaleType === 'weight')
                                        <span class="absolute top-1 right-1 bg-orange-500 text-white text-[10px] px-1.5 py-0.5 rounded-full font-bold">
                                            ⚖️ Kg
                                        </span>
                                    @elseif($productSaleType === 'both')
                                        <span class="absolute top-1 right-1 bg-purple-500 text-white text-[10px] px-1.5 py-0.5 rounded-full font-bold">
                                            🔄 Ambos
                                        </span>
                                    @endif
                                </div>
                                
                                <div class="p-2">
                                    <h3 class="font-bold text-gray-900 text-xs mb-2 line-clamp-1">{{ $product->name }}</h3>
                                    
                                    {{-- ====================================== --}}
                                    {{-- CASO 1: Solo por PESO --}}
                                    {{-- ====================================== --}}
                                    @if($productSaleType === 'weight')
                                        <div class="space-y-1">
                                            <button wire:click="openWeightModal({{ $product->id }})"
                                                wire:loading.attr="disabled"
                                                class="w-full bg-orange-500 hover:bg-orange-600 active:bg-orange-700 text-white rounded-lg px-2 py-2 text-xs font-bold transition-all disabled:opacity-50">
                                                <div class="flex justify-between items-center">
                                                    <span>⚖️ Por Kg</span>
                                                    <span>{{ number_format($product->price_per_kg, 0, ',', '.') }}</span>
                                                </div>
                                            </button>
                                        </div>
                                    
                                    {{-- ====================================== --}}
                                    {{-- CASO 2: AMBOS (Peso + Unidades) --}}
                                    {{-- ====================================== --}}
                                    @elseif($productSaleType === 'both')
                                        <div class="space-y-1">
                                            {{-- Botón de venta por peso --}}
                                            <button wire:click="openWeightModal({{ $product->id }})"
                                                wire:loading.attr="disabled"
                                                class="w-full bg-orange-500 hover:bg-orange-600 active:bg-orange-700 text-white rounded-lg px-2 py-1.5 text-xs font-bold transition-all disabled:opacity-50">
                                                <div class="flex justify-between items-center">
                                                    <span>⚖️ Por Kg</span>
                                                    <span>{{ number_format($product->price_per_kg, 0, ',', '.') }}</span>
                                                </div>
                                            </button>
                                            
                                            {{-- Separador --}}
                                            <div class="flex items-center gap-1 py-0.5">
                                                <div class="flex-1 border-t border-gray-300"></div>
                                                <span class="text-[10px] text-gray-400">o por unidad</span>
                                                <div class="flex-1 border-t border-gray-300"></div>
                                            </div>
                                            
                                            {{-- Variantes por unidad --}}
                                            @if($product->variants->count() > 0)
                                                <div class="grid grid-cols-2 gap-1">
                                                    @foreach($product->variants as $variant)
                                                        <button wire:click="addToCart({{ $variant->id }})"
                                                            wire:loading.attr="disabled"
                                                            class="bg-purple-600 hover:bg-purple-700 active:bg-purple-800 text-white rounded px-1 py-1 text-[10px] font-bold transition-all disabled:opacity-50 {{ $variant->stock <= 0 ? 'opacity-50 cursor-not-allowed' : '' }}"
                                                            @if($variant->stock <= 0) disabled @endif>
                                                            <div>{{ $variant->volume }}ml</div>
                                                            <div class="text-purple-200">{{ number_format($variant->price / 1000, 0) }}k</div>
                                                        </button>
                                                    @endforeach
                                                </div>
                                            @endif
                                        </div>
                                    
                                    {{-- ====================================== --}}
                                    {{-- CASO 3: Solo por UNIDAD (default) --}}
                                    {{-- ====================================== --}}
                                    @else
                                        @if($product->variants->count() > 0)
                                            <div class="space-y-1">
                                                @foreach($product->variants as $variant)
                                                    <button wire:click="addToCart({{ $variant->id }})"
                                                        wire:loading.attr="disabled"
                                                        class="w-full bg-purple-600 hover:bg-purple-700 active:bg-purple-800 text-white rounded-lg px-2 py-1.5 text-xs font-bold transition-all flex justify-between items-center disabled:opacity-50 {{ $variant->stock <= 0 ? 'opacity-50 cursor-not-allowed' : '' }}"
                                                        @if($variant->stock <= 0) disabled @endif>
                                                        <span>{{ $variant->volume }}ml</span>
                                                        <span>{{ number_format($variant->price, 0, ',', '.') }}</span>
                                                    </button>
                                                @endforeach
                                            </div>
                                        @else
                                            <div class="text-xs text-gray-400 text-center py-2">
                                                Sin stock
                                            </div>
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
                                        {{ count($cart) }}
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
                            <div class="bg-gray-50 rounded-lg p-2 mb-2 border {{ ($item['type'] ?? 'unit') === 'weight' ? 'border-orange-200' : 'border-gray-200' }}">
                                <div class="flex justify-between items-start mb-2">
                                    <div class="flex-1">
                                        <div class="font-bold text-sm text-gray-900 line-clamp-1">{{ $item['product_name'] }}</div>
                                        <div class="text-xs text-gray-600">
                                            @if(($item['type'] ?? 'unit') === 'weight')
                                                {{-- Mostrar peso --}}
                                                <span class="text-orange-600">⚖️ {{ number_format($item['weight'], 3, ',', '.') }} kg</span>
                                                <span class="text-gray-400">×</span>
                                                <span>{{ number_format($item['price_per_kg'], 0, ',', '.') }} Gs/kg</span>
                                            @else
                                                {{-- Mostrar volumen --}}
                                                {{ $item['volume'] }}ml × {{ number_format($item['price'], 0, ',', '.') }} Gs
                                            @endif
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
                                    @if(($item['type'] ?? 'unit') === 'weight')
                                        {{-- Productos por peso no tienen control de cantidad --}}
                                        <div class="text-xs text-orange-600 font-semibold flex items-center gap-1">
                                            <span>⚖️</span> Por peso
                                        </div>
                                    @else
                                        {{-- Control de cantidad para productos unitarios --}}
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
                                    @endif
                                    <div class="font-black {{ ($item['type'] ?? 'unit') === 'weight' ? 'text-orange-600' : 'text-purple-600' }}">
                                        @if(($item['type'] ?? 'unit') === 'weight')
                                            {{ number_format($item['price'], 0, ',', '.') }} Gs
                                        @else
                                            {{ number_format($item['price'] * $item['quantity'], 0, ',', '.') }} Gs
                                        @endif
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
                            
                            {{-- Botón para más opciones --}}
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

    {{-- ============================================ --}}
    {{-- MODAL PARA INGRESAR PESO --}}
    {{-- ============================================ --}}
    @if($showWeightModal && $selectedWeightProduct)
        <div class="fixed inset-0 bg-black/60 backdrop-blur-sm flex items-center justify-center p-4 z-50"
             x-data="{ mode: 'amount', amount: '', weight: '' }"
             x-on:keydown.escape.window="$wire.closeWeightModal()">
            <div class="bg-white rounded-2xl max-w-md w-full shadow-2xl overflow-hidden">
                {{-- Header --}}
                <div class="bg-gradient-to-r from-orange-500 to-amber-500 p-4 text-white">
                    <div class="flex justify-between items-start">
                        <div>
                            <h2 class="text-xl font-black">⚖️ Venta por Peso</h2>
                            <p class="text-orange-100 text-sm">{{ $selectedWeightProduct->name }}</p>
                        </div>
                        <button wire:click="closeWeightModal" class="text-white/80 hover:text-white">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                    <div class="mt-2 bg-white/20 rounded-lg px-3 py-2 inline-block">
                        <span class="font-bold text-lg">{{ number_format($selectedWeightProduct->price_per_kg, 0, ',', '.') }} Gs/kg</span>
                    </div>
                </div>

                <div class="p-5">
                    {{-- Selector de modo --}}
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

                    {{-- INPUT POR MONTO --}}
                    <div x-show="mode === 'amount'" x-cloak>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            ¿Cuánto quiere llevar? (Guaraníes)
                        </label>
                        <div class="relative mb-3">
                            <input 
                                wire:model.live.debounce.300ms="amountInput"
                                type="text" 
                                inputmode="numeric"
                                placeholder="Ej: 30000"
                                class="w-full px-4 py-4 text-2xl font-bold text-center border-2 border-gray-300 rounded-xl focus:ring-2 focus:ring-orange-500 focus:border-orange-500"
                                autofocus>
                            <span class="absolute right-4 top-1/2 -translate-y-1/2 text-gray-400 font-medium">Gs</span>
                        </div>

                        {{-- Botones rápidos de monto --}}
                        <div class="grid grid-cols-4 gap-2 mb-4">
                            @foreach([10000, 20000, 30000, 50000] as $quickAmount)
                                <button wire:click="$set('amountInput', '{{ $quickAmount }}')"
                                    type="button"
                                    class="py-2 px-2 text-sm font-bold rounded-lg border-2 border-gray-200 hover:border-orange-400 hover:bg-orange-50 transition-all">
                                    {{ number_format($quickAmount / 1000, 0) }}k
                                </button>
                            @endforeach
                        </div>

                        {{-- Preview del peso calculado --}}
                        @if($amountInput && floatval(str_replace(['.', ','], ['', '.'], $amountInput)) > 0)
                            @php
                                $inputAmount = floatval(str_replace(['.', ','], ['', '.'], $amountInput));
                                $calculatedWeight = $inputAmount / $selectedWeightProduct->price_per_kg;
                            @endphp
                            <div class="bg-gradient-to-r from-green-50 to-emerald-50 rounded-xl p-4 border-2 border-green-300">
                                <div class="text-center">
                                    <p class="text-sm text-green-700 mb-1">Esto equivale a:</p>
                                    <p class="text-3xl font-black text-green-600">
                                        {{ number_format($calculatedWeight, 3, ',', '.') }} kg
                                    </p>
                                    <p class="text-sm text-green-600 mt-1">
                                        ({{ number_format($calculatedWeight * 1000, 0, ',', '.') }} gramos)
                                    </p>
                                </div>
                            </div>
                        @endif
                    </div>

                    {{-- INPUT POR PESO --}}
                    <div x-show="mode === 'weight'" x-cloak>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Ingrese el peso en kilogramos:
                        </label>
                        <div class="relative mb-3">
                            <input 
                                wire:model.live.debounce.300ms="weightInput"
                                type="text" 
                                inputmode="decimal"
                                placeholder="Ej: 0.500"
                                class="w-full px-4 py-4 text-2xl font-bold text-center border-2 border-gray-300 rounded-xl focus:ring-2 focus:ring-orange-500 focus:border-orange-500">
                            <span class="absolute right-4 top-1/2 -translate-y-1/2 text-gray-400 font-medium">kg</span>
                        </div>

                        {{-- Botones rápidos de peso --}}
                        <div class="grid grid-cols-4 gap-2 mb-4">
                            @foreach([0.25, 0.5, 0.75, 1] as $quickWeight)
                                <button wire:click="$set('weightInput', '{{ $quickWeight }}')"
                                    type="button"
                                    class="py-2 px-2 text-sm font-bold rounded-lg border-2 border-gray-200 hover:border-orange-400 hover:bg-orange-50 transition-all">
                                    {{ $quickWeight < 1 ? ($quickWeight * 1000) . 'g' : $quickWeight . 'kg' }}
                                </button>
                            @endforeach
                        </div>

                        {{-- Preview del monto calculado --}}
                        @if($weightInput && floatval(str_replace(',', '.', $weightInput)) > 0)
                            @php
                                $inputWeight = floatval(str_replace(',', '.', $weightInput));
                                $calculatedAmount = $selectedWeightProduct->price_per_kg * $inputWeight;
                            @endphp
                            <div class="bg-gradient-to-r from-green-50 to-emerald-50 rounded-xl p-4 border-2 border-green-300">
                                <div class="text-center">
                                    <p class="text-sm text-green-700 mb-1">Total a cobrar:</p>
                                    <p class="text-3xl font-black text-green-600">
                                        {{ number_format($calculatedAmount, 0, ',', '.') }} Gs
                                    </p>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Footer --}}
                <div class="p-4 bg-gray-50 flex gap-3">
                    <button wire:click="closeWeightModal" 
                        class="flex-1 bg-gray-200 hover:bg-gray-300 text-gray-800 font-bold py-3 rounded-xl transition-all">
                        Cancelar
                    </button>
                    <button wire:click="addWeightToCart"
                        wire:loading.attr="disabled"
                        class="flex-1 bg-orange-500 hover:bg-orange-600 text-white font-bold py-3 rounded-xl transition-all disabled:opacity-50">
                        <span wire:loading.remove wire:target="addWeightToCart">✓ Agregar</span>
                        <span wire:loading wire:target="addWeightToCart">Agregando...</span>
                    </button>
                </div>
            </div>
        </div>
    @endif

    {{-- ============================================ --}}
    {{-- MODAL DE PAGO --}}
    {{-- ============================================ --}}
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

    {{-- ============================================ --}}
    {{-- MODAL DE CONFIRMACIÓN DE VENTA --}}
    {{-- ============================================ --}}
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
                            <span class="font-bold">{{ $lastOrder->items->count() }} producto(s)</span>
                        </div>
                        
                        {{-- Detalle de items --}}
                        <div class="border-t border-gray-200 mt-3 pt-3 space-y-1">
                            @foreach($lastOrder->items as $item)
                                <div class="flex justify-between text-sm">
                                    <span class="text-gray-600">
                                        {{ $item->product_name }}
                                        @if($item->unit_type === 'weight')
                                            <span class="text-orange-600">({{ number_format($item->weight, 3, ',', '.') }} kg)</span>
                                        @elseif($item->volume)
                                            ({{ $item->volume }}ml × {{ $item->quantity }})
                                        @endif
                                    </span>
                                    <span class="font-medium">{{ number_format($item->subtotal, 0, ',', '.') }}</span>
                                </div>
                            @endforeach
                        </div>
                        
                        <div class="flex justify-between items-center pt-3 mt-3 border-t border-gray-300">
                            <span class="text-gray-600 font-bold">Total:</span>
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