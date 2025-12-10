<div class="min-h-screen bg-gray-50">
    <div class="bg-gradient-to-r from-purple-900 to-purple-600 text-white py-12 sm:py-20">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h1 class="text-4xl sm:text-5xl font-extrabold mb-3 sm:mb-4"> Bienvenido a Taskinho Açaí</h1>
            <p class="text-lg sm:text-xl mb-6 sm:mb-8">Los mejores bowls de açaí de Ciudad del Este</p>

            <div class="max-w-md mx-auto px-4 sm:px-0 mb-6 sm:mb-8">
                <input wire:model.live="search" type="text" placeholder="Buscar productos..."
                    class="w-full px-4 py-2 sm:py-3 rounded-lg text-gray-900 focus:outline-none focus:ring-2 focus:ring-purple-300">
            </div>

            <div class="flex justify-center gap-4">
                <a href="#" 
                   class="bg-purple-700 hover:bg-purple-800 text-white font-semibold py-2 px-4 rounded-lg transition duration-200 shadow-md text-sm sm:text-base">
                    Iniciar Sesión
                </a>
                <a href="#" 
                   class="bg-white hover:bg-purple-100 text-purple-800 font-semibold py-2 px-4 rounded-lg transition duration-200 shadow-md text-sm sm:text-base">
                    Registrarse
                </a>
            </div>
            </div>
    </div>
    @if (session()->has('message'))
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-4">
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative">
                {{ session('message') }}
            </div>
        </div>
    @endif

    @if (session()->has('error'))
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-4">
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative">
                {{ session('error') }}
            </div>
        </div>
    @endif
    
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 sm:py-8">
        <div class="flex flex-wrap gap-2 mb-6 sm:mb-8">
            <button wire:click="$set('selectedCategory', null)"
                class="px-3 py-1.5 sm:px-4 sm:py-2 rounded-lg transition text-sm sm:text-base {{ !$selectedCategory ? 'bg-purple-600 text-white shadow-md' : 'bg-white text-gray-700 shadow-sm hover:bg-gray-100' }}">
                Todas
            </button>
            @foreach($categories as $category)
                <button wire:click="$set('selectedCategory', {{ $category->id }})"
                    class="px-3 py-1.5 sm:px-4 sm:py-2 rounded-lg transition text-sm sm:text-base {{ $selectedCategory == $category->id ? 'bg-purple-600 text-white shadow-md' : 'bg-white text-gray-700 shadow-sm hover:bg-gray-100' }}">
                    {{ $category->name }}
                </button>
            @endforeach
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
            @forelse($products as $product)
                <div class="bg-white rounded-xl shadow-md overflow-hidden hover:shadow-xl transition">
                    <div class="h-48 bg-gradient-to-br from-purple-400 to-pink-400 flex items-center justify-center">
                        @if($product->image)
                            <img src="{{ asset('storage/' . $product->image) }}" alt="{{ $product->name }}" class="w-full h-full object-cover">
                        @else
                            <span class="text-8xl">🍇</span>
                        @endif
                    </div>

                    <div class="p-4">
                        <h3 class="text-lg font-bold text-gray-900 mb-2">{{ $product->name }}</h3>

                        @if($product->volume)
                            <span class="inline-block bg-purple-100 text-purple-700 text-xs font-semibold px-2 py-0.5 rounded-full mb-2">
                                {{ $product->volume }} ml
                            </span>
                        @endif

                        <p class="text-sm text-gray-600 mb-3">{{ Str::limit($product->description, 60) }}</p>

                        @if($product->ingredients)
                            <p class="text-xs text-gray-500 mb-3">
                                <span class="font-semibold">Ingredientes:</span> {{ Str::limit($product->ingredients, 50) }}
                            </p>
                        @endif

                        <div class="flex items-center justify-between mb-3">
                            <span class="text-xl sm:text-2xl font-bold text-purple-600">
                                {{ number_format($product->price, 0, ',', '.') }} Gs
                            </span>
                            <span class="text-sm text-gray-500">
                                Stock: {{ $product->stock }}
                            </span>
                        </div>

                        <button wire:click="addToCart({{ $product->id }})"
                            @if($product->stock <= 0) disabled @endif
                            class="w-full bg-purple-600 hover:bg-purple-700 text-white font-bold py-2 px-4 rounded-lg transition disabled:bg-gray-400 disabled:cursor-not-allowed text-sm">
                            @if($product->stock > 0)
                                Agregar al Carrito
                            @else
                                Sin Stock
                            @endif
                        </button>
                    </div>
                </div>
            @empty
                <div class="col-span-full text-center py-12">
                    <p class="text-gray-500 text-lg">No se encontraron productos.</p>
                </div>
            @endforelse
        </div>
    </div>
    
    <hr class="border-t border-gray-200 mt-8">

    <footer class="bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 sm:py-10">
            <div class="text-center text-xs sm:text-sm text-gray-500">
                &copy; {{ date('Y') }} **Taskinho Açaí**. Todos los derechos reservados. Desarrollado por Devparaguay.
            </div>
        </div>
    </footer>
</div>