<div class="min-h-screen bg-gray-50">
    {{-- Header con degradado y búsqueda --}}
    <div class="bg-gradient-to-r from-purple-900 to-purple-600 text-white py-12 sm:py-20">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h1 class="text-4xl sm:text-5xl font-extrabold mb-3 sm:mb-4">🍇 Bienvenido a Taskinho Açaí</h1>
            <p class="text-lg sm:text-xl mb-2">El sabor de Brasil ahora en Ciudad del Este</p>
            <p class="text-sm sm:text-base opacity-90 mb-2">Açaí cremoso, auténtico y delicioso</p>

            {{-- Horario actual dinámico --}}
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
                <input wire:model.live.debounce.300ms="search" type="text" placeholder="Buscar productos..."
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
                        @php $cartCount = auth()->user()->cartItems()->count(); @endphp
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
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative flex items-center justify-between" role="alert">
                <span>{{ session('message') }}</span>
                <button onclick="this.parentElement.remove()" class="text-green-700 hover:text-green-900">
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/></svg>
                </button>
            </div>
        </div>
    @endif

    @if (session()->has('error'))
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-4">
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative flex items-center justify-between" role="alert">
                <span>{{ session('error') }}</span>
                <button onclick="this.parentElement.remove()" class="text-red-700 hover:text-red-900">
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/></svg>
                </button>
            </div>
        </div>
    @endif

    {{-- Banner WhatsApp --}}
    <div class="bg-gradient-to-r from-green-500 to-green-600 text-white py-4">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col sm:flex-row items-center justify-center gap-4">
                <div class="flex items-center gap-3">
                    <svg class="w-8 h-8 flex-shrink-0" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z"/></svg>
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

    {{-- Productos --}}
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 sm:py-8">
        <h2 class="text-2xl sm:text-3xl font-bold text-gray-900 mb-6 text-center">Nuestros Productos</h2>

        {{-- Filtros de Categorías --}}
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

        {{-- Grid de Productos --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
            @forelse($products as $product)
                <div class="bg-white rounded-2xl shadow-lg overflow-hidden hover:shadow-2xl transition-all duration-300 transform hover:-translate-y-2 flex flex-col">

                    {{-- Imagen --}}
                    <div class="relative h-64 overflow-hidden bg-white flex items-center justify-center p-4 border-b border-gray-100">
                        @if($product->image_url)
                            <img src="{{ $product->image_url }}"
                                alt="{{ $product->name }}"
                                class="max-w-full max-h-full object-contain drop-shadow-lg"
                                loading="lazy">
                        @else
                            <div class="w-full h-full bg-gradient-to-br from-purple-400 to-pink-400 flex items-center justify-center rounded-lg">
                                <span class="text-6xl">🍇</span>
                            </div>
                        @endif
                        @if($product->category)
                            <div class="absolute top-3 left-3 z-10">
                                <span class="bg-purple-600 text-white text-xs font-bold px-3 py-1 rounded-full shadow-lg">
                                    {{ $product->category->name }}
                                </span>
                            </div>
                        @endif
                    </div>

                    {{-- Contenido --}}
                    <div class="p-5 flex-1 flex flex-col">
                        <h3 class="text-xl font-bold text-gray-900 mb-2 line-clamp-2 min-h-[3.5rem]">
                            {{ $product->name }}
                        </h3>

                        @if($product->description)
                            <p class="text-sm text-gray-600 mb-3 line-clamp-2">
                                {{ Str::limit($product->description, 80) }}
                            </p>
                        @endif

                        @if($product->ingredients)
                            <div class="mb-3 bg-purple-50 rounded-lg p-2.5">
                                <p class="text-xs text-gray-700 line-clamp-2">
                                    <span class="font-semibold text-purple-700">🍓 Ingredientes:</span><br>
                                    {{ $product->ingredients }}
                                </p>
                            </div>
                        @endif

                        <div class="flex-1"></div>

                        @if($product->activeVariants->count() > 0)
                            @php $firstVariant = $product->activeVariants->first(); @endphp

                            {{-- Badges de tamaños con stock --}}
                            <div class="flex flex-wrap gap-1 mb-3">
                                @foreach($product->activeVariants as $v)
                                    @php $sinStock = $v->available_stock <= 0; @endphp
                                    <span class="text-xs px-2 py-1 rounded-full font-semibold
                                        {{ $sinStock ? 'bg-gray-100 text-gray-400 line-through' : 'bg-purple-100 text-purple-700' }}">
                                        {{ $v->volume }}ml
                                    </span>
                                @endforeach
                            </div>

                            {{-- Precio --}}
                            <div class="mb-4">
                                <div class="flex items-baseline gap-2">
                                    <span class="text-3xl font-black text-purple-600">
                                        {{ number_format($firstVariant->price, 0, ',', '.') }}
                                    </span>
                                    <span class="text-lg font-bold text-gray-500">Gs</span>
                                </div>
                                @if($product->activeVariants->count() > 1)
                                    <span class="text-xs text-gray-500">Desde • {{ $product->activeVariants->count() }} tamaños</span>
                                @endif
                            </div>

                            {{-- Botón --}}
                            <button
                                wire:click="selectProduct({{ $product->id }})"
                                class="w-full bg-gradient-to-r from-purple-600 to-pink-600 hover:from-purple-700 hover:to-pink-700 text-white font-bold py-3 px-4 rounded-xl transition-all transform hover:scale-105 shadow-lg hover:shadow-xl flex items-center justify-center gap-2">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
                                </svg>
                                Agregar al Carrito
                            </button>
                        @else
                            <div class="text-center py-4 bg-red-50 rounded-lg border border-red-200">
                                <span class="text-sm text-red-600 font-semibold">⚠️ Sin stock disponible</span>
                            </div>
                        @endif
                    </div>
                </div>
            @empty
                <div class="col-span-full text-center py-16 bg-white rounded-xl shadow-md">
                    <span class="text-6xl mb-4 block">🔍</span>
                    <p class="text-gray-600 text-xl font-semibold mb-2">No se encontraron productos</p>
                    <p class="text-gray-400">
                        @if($search || $selectedCategory)
                            Intenta con otra búsqueda o categoría
                        @else
                            Pronto tendremos productos disponibles
                        @endif
                    </p>
                    @if($search || $selectedCategory)
                        <button wire:click="clearFilters" class="mt-4 bg-purple-600 hover:bg-purple-700 text-white font-bold py-2 px-6 rounded-lg transition">
                            Limpiar Filtros
                        </button>
                    @endif
                </div>
            @endforelse
        </div>
    </div>

    {{-- ══ MODAL PASO 1: TAMAÑO ══ --}}
    @if($showVariantModal && $selectedProduct)
        <div class="fixed inset-0 bg-black/60 flex items-center justify-center z-50 p-4"
            wire:click="closeVariantModal">
            <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-2xl" @click.stop>

                <div class="flex justify-between items-start mb-5">
                    <div>
                        <p class="text-xs text-gray-400 font-semibold uppercase tracking-wider mb-0.5">Paso 1 de 2</p>
                        <h3 class="text-2xl font-bold text-gray-900">Seleccioná el tamaño</h3>
                        <p class="text-base text-gray-600 mt-0.5">{{ $selectedProduct->name }}</p>
                    </div>
                    <button wire:click="closeVariantModal" class="text-gray-400 hover:text-gray-600 p-1">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                <div class="space-y-2 mb-6">
                    @foreach($selectedProduct->activeVariants as $variant)
                        @php
                            $stockDisp  = $variant->available_stock;
                            $sinStock   = $stockDisp <= 0;
                            $isSelected = !$sinStock && $selectedVariantId == $variant->id;
                        @endphp
                        <button
                            @if(!$sinStock) wire:click="$set('selectedVariantId', {{ $variant->id }})" @endif
                            type="button"
                            @disabled($sinStock)
                            class="w-full p-4 border-2 rounded-xl transition-all
                                {{ $sinStock
                                    ? 'border-gray-200 bg-gray-50 opacity-50 cursor-not-allowed'
                                    : ($isSelected
                                        ? 'border-purple-600 bg-purple-50 shadow-md'
                                        : 'border-gray-200 hover:border-purple-400 cursor-pointer') }}">
                            <div class="flex justify-between items-center">
                                <div class="text-left">
                                    <div class="font-bold text-lg {{ $sinStock ? 'text-gray-400' : 'text-gray-900' }}">
                                        {{ $variant->volume }}ml
                                    </div>
                                    @if($sinStock)
                                        <div class="text-xs text-red-400 font-semibold mt-0.5">Sin stock</div>
                                    @elseif($stockDisp <= 5)
                                        <div class="text-xs text-orange-500 font-semibold mt-0.5">Solo {{ $stockDisp }} disponibles</div>
                                    @endif
                                </div>
                                <div class="text-right">
                                    <div class="text-2xl font-black {{ $sinStock ? 'text-gray-400' : 'text-purple-600' }}">
                                        {{ number_format($variant->price, 0, ',', '.') }}
                                    </div>
                                    <div class="text-sm text-gray-500">Gs</div>
                                </div>
                            </div>
                        </button>
                    @endforeach
                </div>

                <div class="flex gap-3">
                    <button type="button" wire:click="closeVariantModal"
                        class="flex-1 bg-gray-200 hover:bg-gray-300 text-gray-800 font-bold py-3 rounded-xl transition">
                        Cancelar
                    </button>
                    <button type="button" wire:click="confirmVariant"
                        @disabled(!$selectedVariantId)
                        class="flex-1 bg-purple-600 hover:bg-purple-700 text-white font-bold py-3 rounded-xl transition disabled:bg-gray-300 disabled:cursor-not-allowed shadow-lg">
                        {{ $shopStatus['is_open'] ? 'Continuar →' : 'Local Cerrado' }}
                    </button>
                </div>
            </div>
        </div>
    @endif

    {{-- ══ MODAL PASO 2: COMPLEMENTOS ══ --}}
    @if($showCustomizationsModal && $selectedProduct && $customizationGroups->count())
        <div class="fixed inset-0 bg-black/60 backdrop-blur-sm flex items-center justify-center z-50 p-4"
             wire:click="closeCustomizationsModal">
            <div class="bg-white rounded-2xl w-full max-w-lg shadow-2xl flex flex-col max-h-[90vh]" @click.stop>

                {{-- Header --}}
                <div class="bg-gradient-to-r from-purple-600 to-pink-600 px-5 py-4 text-white flex-shrink-0 rounded-t-2xl">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-purple-200 text-xs font-semibold uppercase tracking-wider">Paso 2 de 2</p>
                            <h3 class="text-xl font-black">Elegí tus complementos</h3>
                            <p class="text-purple-100 text-sm mt-0.5">{{ $selectedProduct->name }}</p>
                        </div>
                        <button wire:click="closeCustomizationsModal"
                            class="text-white/70 hover:text-white p-1.5 rounded-xl hover:bg-white/20 transition-all">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>
                </div>

                {{-- Grupos --}}
                <div class="overflow-y-auto flex-1 p-5 space-y-6">
                    @foreach($customizationGroups as $group)
                        <div>
                            <div class="flex items-center justify-between mb-3">
                                <div>
                                    <h4 class="font-black text-gray-900 text-base">
                                        {{ $group->name }}
                                        @if($group->required)
                                            <span class="text-red-500 text-xs ml-1">*obligatorio</span>
                                        @endif
                                    </h4>
                                    @if($group->description)
                                        <p class="text-xs text-gray-400 mt-0.5">{{ $group->description }}</p>
                                    @endif
                                </div>
                                @if($group->max_selections)
                                    <span class="text-xs text-gray-400 bg-gray-100 px-2 py-1 rounded-full flex-shrink-0 ml-2">
                                        Máx {{ $group->max_selections }}
                                    </span>
                                @endif
                            </div>

                            <div class="space-y-2">
                                @foreach($group->activeOptions as $option)
                                    @php
                                        $isSelected = in_array($option->id, $selectedCustomizations[$group->id] ?? []);
                                    @endphp
                                    <button type="button"
                                        wire:click="toggleCustomization({{ $group->id }}, {{ $option->id }}, {{ $group->multiple ? 'true' : 'false' }})"
                                        class="w-full flex items-center justify-between px-4 py-3 rounded-xl border-2 transition-all
                                            {{ $isSelected
                                                ? 'border-purple-500 bg-purple-50'
                                                : 'border-gray-200 hover:border-purple-300 hover:bg-gray-50' }}">
                                        <div class="flex items-center gap-3">
                                            <div class="w-5 h-5 rounded-{{ $group->multiple ? 'md' : 'full' }} border-2 flex items-center justify-center flex-shrink-0 transition-all
                                                {{ $isSelected ? 'border-purple-500 bg-purple-500' : 'border-gray-300' }}">
                                                @if($isSelected)
                                                    <svg class="w-3 h-3 text-white" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"/>
                                                    </svg>
                                                @endif
                                            </div>
                                            <span class="font-semibold text-gray-800 text-sm">{{ $option->name }}</span>
                                        </div>
                                        @if($option->price > 0)
                                            <span class="text-sm font-black text-orange-500 flex-shrink-0">
                                                +{{ number_format($option->price, 0, ',', '.') }} Gs
                                            </span>
                                        @else
                                            <span class="text-xs text-green-600 font-bold bg-green-50 px-2 py-0.5 rounded-full flex-shrink-0">
                                                Incluido
                                            </span>
                                        @endif
                                    </button>
                                @endforeach
                            </div>

                            @php $nSelec = count($selectedCustomizations[$group->id] ?? []); @endphp
                            @if($nSelec > 0)
                                <p class="text-xs text-purple-600 font-semibold mt-2 text-right">
                                    {{ $nSelec }} seleccionado{{ $nSelec > 1 ? 's' : '' }}
                                </p>
                            @endif
                        </div>

                        @if(!$loop->last)
                            <div class="border-t border-dashed border-gray-200"></div>
                        @endif
                    @endforeach
                </div>

                {{-- Footer con resumen y botones --}}
                <div class="px-5 py-4 bg-gray-50 border-t rounded-b-2xl flex-shrink-0">
                    @php
                        $extrasTotal = 0;
                        foreach($customizationGroups as $grp) {
                            foreach($grp->activeOptions as $opt) {
                                if(in_array($opt->id, $selectedCustomizations[$grp->id] ?? [])) {
                                    $extrasTotal += (float) $opt->price;
                                }
                            }
                        }
                    @endphp
                    @if($extrasTotal > 0)
                        <div class="flex items-center justify-between mb-3 px-1">
                            <span class="text-sm text-gray-600 font-medium">Extras seleccionados:</span>
                            <span class="text-base font-black text-orange-500">
                                +{{ number_format($extrasTotal, 0, ',', '.') }} Gs
                            </span>
                        </div>
                    @endif
                    <div class="flex gap-3">
                        <button wire:click="closeCustomizationsModal"
                            class="flex-1 bg-gray-200 hover:bg-gray-300 text-gray-700 font-bold py-3 rounded-xl text-sm transition-all">
                            Cancelar
                        </button>
                        <button wire:click="confirmCustomizations" wire:loading.attr="disabled"
                            class="flex-1 bg-gradient-to-r from-purple-600 to-pink-600 hover:from-purple-700 hover:to-pink-700 text-white font-black py-3 rounded-xl text-sm transition-all shadow-lg disabled:opacity-50 flex items-center justify-center gap-2">
                            <span wire:loading.remove wire:target="confirmCustomizations">
                                🛒 Agregar al Carrito
                            </span>
                            <span wire:loading wire:target="confirmCustomizations">Agregando...</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- Botón flotante WhatsApp --}}
    <a href="https://wa.me/595986150627?text=Hola!%20Quiero%20hacer%20un%20pedido%20de%20Taskinho%20Açaí"
       target="_blank"
       class="fixed bottom-6 right-6 bg-green-500 hover:bg-green-600 text-white rounded-full p-4 shadow-2xl z-50 transition-all duration-300 hover:scale-110 animate-bounce"
       id="whatsapp-float">
        <svg class="w-8 h-8" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z"/></svg>
    </a>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            setTimeout(function() {
                const btn = document.getElementById('whatsapp-float');
                if (btn) btn.classList.remove('animate-bounce');
            }, 3000);
        });
    </script>

    <style>
        @keyframes spin-slow { from { transform: rotate(0deg); } to { transform: rotate(360deg); } }
        .animate-spin-slow { animation: spin-slow 3s linear infinite; }
    </style>
</div>