<div class="min-h-screen bg-gray-50">
    <div class="bg-gradient-to-r from-purple-900 to-purple-600 text-white py-12 sm:py-20">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h1 class="text-4xl sm:text-5xl font-extrabold mb-3 sm:mb-4">🍇 Bienvenido a Taskinho Açaí</h1>
            <p class="text-lg sm:text-xl mb-6 sm:mb-8">Los mejores bowls de açaí de Ciudad del Este</p>

            <div class="max-w-md mx-auto px-4 sm:px-0 mb-6 sm:mb-8">
                <input wire:model.live="search" type="text" placeholder="Buscar productos..."
                    class="w-full px-4 py-2 sm:py-3 rounded-lg text-gray-900 focus:outline-none focus:ring-2 focus:ring-purple-300">
            </div>

            @guest
                <div class="flex justify-center gap-4">
                    <a href="{{ route('login') }}" 
                       class="bg-purple-700 hover:bg-purple-800 text-white font-semibold py-2 px-4 rounded-lg transition duration-200 shadow-md text-sm sm:text-base">
                        Iniciar Sesión
                    </a>
                    <a href="{{ route('register') }}" 
                       class="bg-white hover:bg-purple-100 text-purple-800 font-semibold py-2 px-4 rounded-lg transition duration-200 shadow-md text-sm sm:text-base">
                        Registrarse
                    </a>
                </div>
            @endguest
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
                <div class="bg-white rounded-xl shadow-md overflow-hidden hover:shadow-xl transition" 
                     x-data="{ 
                         selectedVariantId: {{ $product->activeVariants->first()?->id ?? 'null' }},
                         variants: {{ $product->activeVariants->toJson() }},
                         get selectedVariant() {
                             return this.variants.find(v => v.id === this.selectedVariantId) || this.variants[0] || null;
                         }
                     }">
                    <div class="h-48 bg-gradient-to-br from-purple-400 to-pink-400 flex items-center justify-center">
                        @if($product->image)
                            <img src="{{ asset('storage/' . $product->image) }}" alt="{{ $product->name }}" class="w-full h-full object-cover">
                        @else
                            <span class="text-8xl">🍇</span>
                        @endif
                    </div>

                    <div class="p-4">
                        <h3 class="text-lg font-bold text-gray-900 mb-2">{{ $product->name }}</h3>

                        <p class="text-sm text-gray-600 mb-3">{{ Str::limit($product->description, 60) }}</p>

                        @if($product->ingredients)
                            <p class="text-xs text-gray-500 mb-3">
                                <span class="font-semibold">Ingredientes:</span> {{ Str::limit($product->ingredients, 50) }}
                            </p>
                        @endif

                        @if($product->activeVariants->count() > 0)
                            <!-- Selector de Tamaños -->
                            <div class="mb-3">
                                <label class="block text-xs font-semibold text-gray-700 mb-2">Tamaño:</label>
                                <div class="grid grid-cols-2 gap-2">
                                    @foreach($product->activeVariants as $variant)
                                        <button 
                                            @click="selectedVariantId = {{ $variant->id }}"
                                            :class="selectedVariantId === {{ $variant->id }} ? 'border-purple-600 bg-purple-50 text-purple-700' : 'border-gray-300 text-gray-700 hover:border-purple-300'"
                                            @if($variant->stock <= 0) disabled @endif
                                            class="px-3 py-2 border-2 rounded-lg text-sm font-semibold transition disabled:opacity-50 disabled:cursor-not-allowed">
                                            {{ $variant->volume }}ml
                                            @if($variant->stock <= 0)
                                                <span class="block text-xs text-red-500">Sin stock</span>
                                            @endif
                                        </button>
                                    @endforeach
                                </div>
                            </div>

                            <!-- Precio Dinámico -->
                            <div class="flex items-center justify-between mb-3">
                                <div class="flex flex-col">
                                    <span class="text-2xl font-bold text-purple-600" x-text="selectedVariant ? new Intl.NumberFormat('es-PY').format(selectedVariant.price) + ' Gs' : 'N/A'">
                                    </span>
                                    <span class="text-xs text-gray-500" x-show="selectedVariant && selectedVariant.stock > 0">
                                        Stock: <span x-text="selectedVariant.stock"></span> unidades
                                    </span>
                                    <span class="text-xs text-red-500" x-show="selectedVariant && selectedVariant.stock <= 0">
                                        Sin stock
                                    </span>
                                </div>
                            </div>

                            <!-- Botón Agregar -->
                            <button 
                                @click="$wire.addToCart(selectedVariantId)"
                                :disabled="!selectedVariant || selectedVariant.stock <= 0"
                                class="w-full bg-purple-600 hover:bg-purple-700 text-white font-bold py-2 px-4 rounded-lg transition disabled:bg-gray-400 disabled:cursor-not-allowed text-sm">
                                <span x-show="selectedVariant && selectedVariant.stock > 0">Agregar al Carrito</span>
                                <span x-show="!selectedVariant || selectedVariant.stock <= 0">Sin Stock</span>
                            </button>
                        @else
                            <div class="text-center py-4">
                                <span class="text-sm text-red-500">Sin variantes disponibles</span>
                            </div>
                        @endif
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