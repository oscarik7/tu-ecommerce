<div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <h1 class="text-3xl font-bold text-gray-900 mb-8">🛒 Mi Carrito</h1>

        @if (session()->has('message'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                {{ session('message') }}
            </div>
        @endif

        @if($cartItems->count() > 0)
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <!-- Items del Carrito -->
                <div class="lg:col-span-2 space-y-4">
                    @foreach($cartItems as $item)
                        <div class="bg-white rounded-lg shadow-md p-4 flex items-center gap-4">
                            <div class="w-24 h-24 bg-gradient-to-br from-purple-400 to-pink-400 rounded-lg flex items-center justify-center flex-shrink-0">
                                @if($item->product->image)
                                    <img src="{{ asset('storage/' . $item->product->image) }}" alt="{{ $item->product->name }}" class="w-full h-full object-cover rounded-lg">
                                @else
                                    <span class="text-4xl">🍇</span>
                                @endif
                            </div>

                            <div class="flex-1">
                                <h3 class="text-lg font-bold text-gray-900">{{ $item->product->name }}</h3>
                                <p class="text-sm text-gray-600">{{ $item->product->description }}</p>
                                <p class="text-lg font-bold text-purple-600 mt-2">
                                    {{ number_format($item->product->price, 0, ',', '.') }} Gs
                                </p>
                            </div>

                            <div class="flex items-center gap-2">
                                <button wire:click="updateQuantity({{ $item->id }}, {{ $item->quantity - 1 }})" 
                                    class="bg-gray-200 hover:bg-gray-300 text-gray-700 font-bold py-1 px-3 rounded">
                                    -
                                </button>
                                <span class="font-bold text-lg w-12 text-center">{{ $item->quantity }}</span>
                                <button wire:click="updateQuantity({{ $item->id }}, {{ $item->quantity + 1 }})" 
                                    class="bg-gray-200 hover:bg-gray-300 text-gray-700 font-bold py-1 px-3 rounded">
                                    +
                                </button>
                            </div>

                            <div class="text-right">
                                <p class="text-lg font-bold text-gray-900">
                                    {{ number_format($item->product->price * $item->quantity, 0, ',', '.') }} Gs
                                </p>
                                <button wire:click="removeItem({{ $item->id }})" 
                                    class="text-red-600 hover:text-red-800 text-sm mt-2">
                                    Eliminar
                                </button>
                            </div>
                        </div>
                    @endforeach

                    <button wire:click="clearCart" 
                        wire:confirm="¿Estás seguro de vaciar el carrito?"
                        class="text-red-600 hover:text-red-800 font-medium">
                        Vaciar Carrito
                    </button>
                </div>

                <!-- Resumen -->
                <div class="lg:col-span-1">
                    <div class="bg-white rounded-lg shadow-md p-6 sticky top-4">
                        <h2 class="text-xl font-bold text-gray-900 mb-4">Resumen del Pedido</h2>
                        
                        <div class="space-y-2 mb-4">
                            <div class="flex justify-between text-gray-700">
                                <span>Subtotal:</span>
                                <span class="font-bold">{{ number_format($subtotal, 0, ',', '.') }} Gs</span>
                            </div>
                        </div>

                        <div class="border-t pt-4 mb-6">
                            <div class="flex justify-between text-xl font-bold text-gray-900">
                                <span>Total:</span>
                                <span class="text-purple-600">{{ number_format($subtotal, 0, ',', '.') }} Gs</span>
                            </div>
                        </div>

                        <a href="{{ route('checkout') }}" 
                            class="block w-full bg-purple-600 hover:bg-purple-700 text-white text-center font-bold py-3 px-4 rounded-lg transition">
                            Proceder al Checkout
                        </a>

                        <a href="{{ route('home') }}" 
                            class="block w-full text-center text-purple-600 hover:text-purple-700 font-medium mt-4">
                            Continuar Comprando
                        </a>
                    </div>
                </div>
            </div>
        @else
            <div class="bg-white rounded-lg shadow-md p-12 text-center">
                <span class="text-8xl">🛒</span>
                <h2 class="text-2xl font-bold text-gray-900 mt-4 mb-2">Tu carrito está vacío</h2>
                <p class="text-gray-600 mb-6">Agrega algunos productos deliciosos de açaí</p>
                <a href="{{ route('home') }}" 
                    class="inline-block bg-purple-600 hover:bg-purple-700 text-white font-bold py-3 px-6 rounded-lg transition">
                    Ver Productos
                </a>
            </div>
        @endif
    </div>
</div>