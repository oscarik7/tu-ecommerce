<div class="min-h-screen bg-gray-50">
    {{-- Header con degradado y búsqueda --}}
    <div class="bg-gradient-to-r from-purple-900 to-purple-600 text-white py-12 sm:py-20">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h1 class="text-4xl sm:text-5xl font-extrabold mb-3 sm:mb-4">🍇 Bienvenido a Taskinho Açaí</h1>
            <p class="text-lg sm:text-xl mb-2">El sabor de Brasil ahora en Paraguay 🇧🇷🇵🇾</p>
            <p class="text-sm sm:text-base opacity-90 mb-2">Açaí cremoso, auténtico y delicioso</p>

           {{-- Horario actual dinámico - Con Reloj --}}
            <div class="inline-flex items-center gap-3 bg-white/10 backdrop-blur-xl rounded-2xl px-6 py-3 mb-6 shadow-2xl border border-white/30 hover:border-white/50 transition-all duration-300" wire:poll.60s>
                <div class="relative">
                    <svg class="w-6 h-6 text-white/90 {{ $shopStatus['is_open'] ? 'animate-spin-slow' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <span class="absolute -top-1 -right-1 w-3 h-3 {{ $shopStatus['color'] }} rounded-full {{ $shopStatus['is_open'] ? 'animate-pulse' : '' }} border-2 border-white"></span>
                </div>
                <div class="flex flex-col">
                    <span class="text-sm font-bold leading-tight {{ $shopStatus['is_open'] ? 'text-green-100' : 'text-red-100' }}">
                        {{ $shopStatus['label'] }}
                    </span>
                    <span class="text-xs opacity-80 leading-tight mt-0.5">
                        {{ $shopStatus['hours'] }}
                    </span>
                </div>
            </div>

            <div class="max-w-md mx-auto px-4 sm:px-0 mb-6 sm:mb-8">
                <input wire:model.live="search" type="text" placeholder="Buscar productos..."
                    class="w-full px-4 py-2 sm:py-3 rounded-lg text-gray-900 focus:outline-none focus:ring-2 focus:ring-purple-300 shadow-lg">
            </div>

            @guest
                <div class="flex justify-center gap-4 mb-6">
                    <a href="{{ route('login') }}"
                       class="bg-purple-700 hover:bg-purple-800 text-white font-semibold py-2 px-4 sm:px-6 rounded-lg transition duration-200 shadow-md text-sm sm:text-base">
                        Iniciar Sesión
                    </a>
                    <a href="{{ route('register') }}"
                       class="bg-white hover:bg-purple-100 text-purple-800 font-semibold py-2 px-4 sm:px-6 rounded-lg transition duration-200 shadow-md text-sm sm:text-base">
                        Registrarse
                    </a>
                </div>
            @endguest

            @auth
                <div class="flex justify-center items-center gap-4">
                    <a href="{{ route('cart') }}" class="relative inline-flex items-center bg-white hover:bg-purple-100 text-purple-800 font-semibold py-2 px-4 sm:px-6 rounded-lg transition duration-200 shadow-md text-sm sm:text-base">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path>
                        </svg>
                        Mi Carrito
                        @php
                            $cartCount = auth()->user()->cartItems()->count();
                        @endphp
                        @if($cartCount > 0)
                            <span class="absolute -top-2 -right-2 bg-red-500 text-white text-xs font-bold rounded-full h-6 w-6 flex items-center justify-center">
                                {{ $cartCount }}
                            </span>
                        @endif
                    </a>
                </div>
            @endauth
        </div>
    </div>

    {{-- Mensajes de sesión --}}
    @if (session()->has('message'))
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-4">
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
                <span class="block sm:inline">{{ session('message') }}</span>
            </div>
        </div>
    @endif

    @if (session()->has('error'))
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-4">
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                <span class="block sm:inline">{{ session('error') }}</span>
            </div>
        </div>
    @endif

    {{-- Banner de WhatsApp prominente --}}
    <div class="bg-gradient-to-r from-green-500 to-green-600 text-white py-4">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col sm:flex-row items-center justify-center gap-4">
                <div class="flex items-center gap-3">
                    <svg class="w-8 h-8 flex-shrink-0" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z"/>
                    </svg>
                    <div class="text-left">
                        <p class="font-bold text-lg">¡Haz tu pedido por WhatsApp!</p>
                        <p class="text-sm opacity-90">Respuesta inmediata • Delivery disponible</p>
                    </div>
                </div>
                <a href="https://wa.me/595986150627?text=Hola!%20Quiero%20hacer%20un%20pedido%20de%20Taskinho%20Açaí"
                   target="_blank"
                   class="bg-white text-green-600 hover:bg-green-50 font-bold py-3 px-6 rounded-lg transition shadow-lg whitespace-nowrap">
                    Pedir Ahora
                </a>
            </div>
        </div>
    </div>

    {{-- Sección de Beneficios --}}
    <div class="bg-white py-8 sm:py-12 border-b">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 text-center">
                <div class="flex flex-col items-center">
                    <div class="bg-purple-100 rounded-full p-4 mb-3">
                        <svg class="w-8 h-8 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <h3 class="font-bold text-gray-900 mb-1">100% Natural</h3>
                    <p class="text-sm text-gray-600">Sin conservantes ni aditivos</p>
                </div>
                <div class="flex flex-col items-center">
                    <div class="bg-purple-100 rounded-full p-4 mb-3">
                        <svg class="w-8 h-8 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                        </svg>
                    </div>
                    <h3 class="font-bold text-gray-900 mb-1">Energía Pura</h3>
                    <p class="text-sm text-gray-600">Rico en antioxidantes</p>
                </div>
                <div class="flex flex-col items-center">
                    <div class="bg-purple-100 rounded-full p-4 mb-3">
                        <svg class="w-8 h-8 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <h3 class="font-bold text-gray-900 mb-1">Entrega Rápida</h3>
                    <p class="text-sm text-gray-600">En toda Ciudad del Este</p>
                </div>
                <div class="flex flex-col items-center">
                    <div class="bg-purple-100 rounded-full p-4 mb-3">
                        <svg class="w-8 h-8 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.828 14.828a4 4 0 01-5.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <h3 class="font-bold text-gray-900 mb-1">Sabor Auténtico</h3>
                    <p class="text-sm text-gray-600">Receta brasileña original 🇧🇷</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Productos con categorías --}}
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 sm:py-8">
        <h2 class="text-2xl sm:text-3xl font-bold text-gray-900 mb-6 text-center">Nuestros Productos</h2>

        <div class="flex flex-wrap gap-2 mb-6 sm:mb-8 justify-center">
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
                <div class="bg-white rounded-xl shadow-md overflow-hidden hover:shadow-xl transition-all duration-300 transform hover:-translate-y-1">
                    <div class="h-48 bg-gradient-to-br from-purple-400 to-pink-400 flex items-center justify-center relative overflow-hidden">
                        @if($product->image)
                            <img src="{{ asset('storage/' . $product->image) }}" alt="{{ $product->name }}" class="w-full h-full object-cover">
                        @else
                            <span class="text-8xl">🍇</span>
                        @endif
                        @if($product->activeVariants->where('stock', '<=', 5)->count() > 0)
                            <div class="absolute top-2 right-2 bg-red-500 text-white text-xs font-bold px-2 py-1 rounded">
                                ¡Últimas unidades!
                            </div>
                        @endif
                    </div>

                    <div class="p-4">
                        <h3 class="text-lg font-bold text-gray-900 mb-2">{{ $product->name }}</h3>
                        <p class="text-sm text-gray-600 mb-3">{{ Str::limit($product->description, 60) }}</p>

                        @if($product->ingredients)
                            <div class="mb-3 bg-purple-50 rounded-lg p-2">
                                <p class="text-xs text-gray-700">
                                    <span class="font-semibold">🍓 Ingredientes:</span><br>
                                    {{ Str::limit($product->ingredients, 60) }}
                                </p>
                            </div>
                        @endif

                        @if($product->activeVariants->count() > 0)
                            @php
                                $firstVariant = $product->activeVariants->first();
                            @endphp

                            <div class="flex items-center justify-between mb-3">
                                <div class="flex flex-col">
                                    <span class="text-2xl font-bold text-purple-600">
                                        {{ number_format($firstVariant->price, 0, ',', '.') }} Gs
                                    </span>
                                    @if($product->activeVariants->count() > 1)
                                        <span class="text-xs text-gray-500">Desde</span>
                                    @endif
                                </div>
                            </div>

                            <button
                                wire:click="selectProduct({{ $product->id }})"
                                class="w-full bg-purple-600 hover:bg-purple-700 text-white font-bold py-2 px-4 rounded-lg transition text-sm shadow-md hover:shadow-lg">
                                🛒 Agregar al Carrito
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
                    <span class="text-6xl mb-4 block">🔍</span>
                    <p class="text-gray-500 text-lg">No se encontraron productos.</p>
                </div>
            @endforelse
        </div>
    </div>

    {{-- Modal de selección de variante --}}
    @if($showVariantModal && $selectedProduct)
        <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4"
            wire:click="closeVariantModal">
            <div class="bg-white rounded-xl max-w-md w-full p-6"
                @click.stop>
                <h3 class="text-xl font-bold text-gray-900 mb-4">Selecciona el tamaño</h3>
                <h4 class="text-lg text-gray-700 mb-4">{{ $selectedProduct->name }}</h4>

                <div class="space-y-3 mb-6">
                    @foreach($selectedProduct->activeVariants as $variant)
                        <button
                            wire:click="$set('selectedVariantId', {{ $variant->id }})"
                            type="button"
                            class="w-full p-4 border-2 rounded-lg transition {{ $selectedVariantId == $variant->id ? 'border-purple-600 bg-purple-50' : 'border-gray-300 hover:border-purple-300' }}">
                            <div class="flex justify-between items-center">
                                <div class="text-left">
                                    <div class="font-bold text-gray-900">{{ $variant->volume }}ml</div>
                                    @if($variant->stock <= 5 && $variant->stock > 0)
                                        <div class="text-xs text-orange-500">Solo {{ $variant->stock }} unidades</div>
                                    @elseif($variant->stock <= 0)
                                        <div class="text-xs text-red-500">Sin stock</div>
                                    @endif
                                </div>
                                <div class="text-xl font-bold text-purple-600">
                                    {{ number_format($variant->price, 0, ',', '.') }} Gs
                                </div>
                            </div>
                        </button>
                    @endforeach
                </div>

                <div class="flex gap-3">
                    <button
                        type="button"
                        wire:click="closeVariantModal"
                        class="flex-1 bg-gray-200 hover:bg-gray-300 text-gray-800 font-bold py-3 px-4 rounded-lg transition">
                        Cancelar
                    </button>
                    <button
                        type="button"
                        wire:click="addToCart"
                        {{ (!$selectedVariantId || !$shopStatus['is_open']) ? 'disabled' : '' }}
                        class="flex-1 bg-purple-600 hover:bg-purple-700 text-white font-bold py-3 px-4 rounded-lg transition disabled:bg-gray-400 disabled:cursor-not-allowed">
                        {{ $shopStatus['is_open'] ? 'Agregar' : 'Cerrado' }}
                    </button>
                </div>
            </div>
        </div>
    @endif


    {{-- Botón flotante de WhatsApp --}}
    <a href="https://wa.me/595986150627?text=Hola!%20Quiero%20hacer%20un%20pedido%20de%20Taskinho%20Açaí"
       target="_blank"
       class="fixed bottom-6 right-6 bg-green-500 hover:bg-green-600 text-white rounded-full p-4 shadow-2xl z-50 transition-all duration-300 hover:scale-110 animate-bounce"
       title="Chatea con nosotros por WhatsApp"
       id="whatsapp-float">
        <svg class="w-8 h-8" fill="currentColor" viewBox="0 0 24 24">
            <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z"/>
        </svg>
    </a>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            setTimeout(function() {
                const btn = document.getElementById('whatsapp-float');
                if (btn) btn.classList.remove('animate-bounce');
            }, 3000);
        });
    </script>
</div>
