<div class="min-h-screen bg-gray-50 py-4 sm:py-8">
    <div class="max-w-7xl mx-auto px-3 sm:px-6 lg:px-8">
        {{-- Header del carrito --}}
        <div class="mb-4 sm:mb-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-xl sm:text-2xl lg:text-3xl font-bold text-gray-900 flex items-center gap-2">
                        🛒 <span>Mi Carrito</span>
                    </h1>
                    @if($cartItems->count() > 0)
                        <p class="text-xs sm:text-sm text-gray-600 mt-1">
                            {{ $cartItems->count() }} {{ $cartItems->count() == 1 ? 'producto' : 'productos' }}
                        </p>
                    @endif
                </div>

                @if($cartItems->count() > 0)
                    <button wire:click="clearCart"
                        wire:confirm="¿Estás seguro de vaciar el carrito?"
                        class="text-red-600 hover:text-red-800 font-medium text-xs sm:text-sm transition">
                        Vaciar
                    </button>
                @endif
            </div>
        </div>

        {{-- Mensajes --}}
        @if (session()->has('message'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-3 py-2 rounded-lg mb-3 text-sm">
                {{ session('message') }}
            </div>
        @endif

        @if (session()->has('error'))
            <div class="bg-red-100 border border-red-400 text-red-700 px-3 py-2 rounded-lg mb-3 text-sm">
                {{ session('error') }}
            </div>
        @endif

        @if($cartItems->count() > 0)
            <div class="space-y-4">
                {{-- Items del Carrito --}}
                <div class="space-y-3">
                    @foreach($cartItems as $item)
                        <div class="bg-white rounded-lg shadow-sm p-3 sm:p-4">
                            <div class="flex gap-3">
                                {{-- Imagen --}}
                                <div class="w-16 h-16 sm:w-20 sm:h-20 bg-gradient-to-br from-purple-400 to-pink-400 rounded-lg flex items-center justify-center flex-shrink-0">
                                    @if($item->product->image)
                                        <img src="{{ asset('storage/' . $item->product->image) }}"
                                             alt="{{ $item->product->name }}"
                                             class="w-full h-full object-cover rounded-lg">
                                    @else
                                        <span class="text-3xl sm:text-4xl">🍇</span>
                                    @endif
                                </div>

                                {{-- Info del producto --}}
                                <div class="flex-1 min-w-0">
                                    <h3 class="text-sm sm:text-base font-bold text-gray-900 line-clamp-2 mb-1">
                                        {{ $item->product->name }}
                                    </h3>

                                    <div class="flex items-center gap-2 mb-2">
                                        <span class="inline-flex items-center bg-purple-100 text-purple-700 text-xs font-semibold px-2 py-0.5 rounded-full">
                                            {{ $item->variant->volume }} ml
                                        </span>
                                        @php $stockDisp = $item->variant->available_stock; @endphp
                                        @if($stockDisp <= 5)
                                            <span class="text-xs text-orange-600">Solo {{ $stockDisp }}</span>
                                        @endif
                                    </div>

                                    <p class="text-sm sm:text-base font-bold text-purple-600">
                                        {{ number_format($item->variant->price, 0, ',', '.') }} Gs
                                    </p>
                                </div>
                            </div>

                            {{-- Controles y subtotal --}}
                            <div class="flex items-center justify-between mt-3 pt-3 border-t">
                                {{-- Cantidad --}}
                                <div class="flex items-center gap-1 sm:gap-2 bg-gray-100 rounded-lg p-1">
                                    <button wire:click="updateQuantity({{ $item->id }}, {{ $item->quantity - 1 }})"
                                        class="bg-white hover:bg-gray-200 text-gray-700 font-bold w-8 h-8 sm:w-9 sm:h-9 rounded-md transition flex items-center justify-center">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"/>
                                        </svg>
                                    </button>
                                    <span class="font-bold text-base sm:text-lg w-8 sm:w-10 text-center">{{ $item->quantity }}</span>
                                    <button wire:click="updateQuantity({{ $item->id }}, {{ $item->quantity + 1 }})"
                                        @if(!$item->variant->hasStock($item->quantity + 1)) disabled @endif
                                        class="bg-white hover:bg-gray-200 text-gray-700 font-bold w-8 h-8 sm:w-9 sm:h-9 rounded-md transition flex items-center justify-center disabled:opacity-50 disabled:cursor-not-allowed">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                        </svg>
                                    </button>
                                </div>

                                {{-- Subtotal y eliminar --}}
                                <div class="text-right">
                                    <p class="text-xs text-gray-500 mb-0.5">Subtotal</p>
                                    <p class="text-base sm:text-lg font-bold text-gray-900 mb-1">
                                        {{ number_format($item->variant->price * $item->quantity, 0, ',', '.') }} Gs
                                    </p>
                                    <button wire:click="removeItem({{ $item->id }})"
                                        class="text-red-600 hover:text-red-800 text-xs font-medium transition flex items-center gap-1 ml-auto">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                        </svg>
                                        Eliminar
                                    </button>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                {{-- Resumen del Pedido --}}
                <div class="bg-white rounded-lg shadow-sm p-4">
                    <h2 class="text-lg sm:text-xl font-bold text-gray-900 mb-3 flex items-center gap-2">
                        <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                        </svg>
                        Resumen del Pedido
                    </h2>

                    {{-- Desglose --}}
                    <div class="space-y-2 mb-3 pb-3 border-b">
                        <div class="flex justify-between text-sm text-gray-700">
                            <span>Subtotal ({{ $cartItems->sum('quantity') }} productos)</span>
                            <span class="font-semibold">{{ number_format($subtotal, 0, ',', '.') }} Gs</span>
                        </div>
                        <div class="flex justify-between text-sm text-gray-700">
                            <span>Envío</span>
                            <span class="font-semibold text-purple-600">Se calcula en el checkout</span>
                        </div>
                    </div>

                    {{-- Total --}}
                    <div class="bg-purple-50 rounded-lg p-3 mb-4">
                        <div class="flex justify-between items-center">
                            <span class="text-base sm:text-lg font-bold text-gray-900">Subtotal:</span>
                            <span class="text-xl sm:text-2xl font-bold text-purple-600">
                                {{ number_format($subtotal, 0, ',', '.') }} Gs
                            </span>
                        </div>
                        <p class="text-xs text-gray-600 mt-2 text-center">El costo de envío se agregará según tu zona</p>
                    </div>

                    {{-- Botones --}}
                    <div class="space-y-2">
                        <a href="{{ route('checkout') }}"
                            class="block w-full bg-purple-600 hover:bg-purple-700 active:bg-purple-800 text-white text-center font-bold py-3 sm:py-3.5 px-4 rounded-lg transition shadow-md">
                            <span class="flex items-center justify-center gap-2">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                                </svg>
                                Proceder al Pago
                            </span>
                        </a>

                        <a href="{{ route('home') }}"
                            class="block w-full text-center text-purple-600 hover:text-purple-700 font-semibold py-2 transition">
                            <span class="flex items-center justify-center gap-2 text-sm sm:text-base">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                                </svg>
                                Continuar Comprando
                            </span>
                        </a>
                    </div>
                </div>
            </div>
        @else
            {{-- Carrito vacío --}}
            <div class="bg-white rounded-xl shadow-sm p-6 sm:p-12 text-center">
                <div class="max-w-sm mx-auto">
                    <div class="w-24 h-24 sm:w-32 sm:h-32 mx-auto mb-4 sm:mb-6 bg-purple-100 rounded-full flex items-center justify-center">
                        <span class="text-5xl sm:text-6xl">🛒</span>
                    </div>
                    <h2 class="text-xl sm:text-2xl font-bold text-gray-900 mb-2">Tu carrito está vacío</h2>
                    <p class="text-sm sm:text-base text-gray-600 mb-6">¡Descubre nuestros deliciosos productos de açaí!</p>

                    <a href="{{ route('home') }}"
                        class="inline-flex items-center gap-2 bg-purple-600 hover:bg-purple-700 active:bg-purple-800 text-white font-bold py-3 px-6 rounded-lg transition shadow-md">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                        Explorar Productos
                    </a>

                    {{-- Beneficios --}}
                    <div class="grid grid-cols-3 gap-3 mt-8 pt-6 border-t">
                        <div class="text-center">
                            <div class="text-2xl mb-1">🌿</div>
                            <p class="text-xs font-semibold text-gray-700">100% Natural</p>
                        </div>
                        <div class="text-center">
                            <div class="text-2xl mb-1">⚡</div>
                            <p class="text-xs font-semibold text-gray-700">Alta Energía</p>
                        </div>
                        <div class="text-center">
                            <div class="text-2xl mb-1">🇧🇷</div>
                            <p class="text-xs font-semibold text-gray-700">Auténtico</p>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>
