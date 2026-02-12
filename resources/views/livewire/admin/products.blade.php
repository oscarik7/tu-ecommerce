<div>
    {{-- Mensajes Flash --}}
    @if (session()->has('message'))
        <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 4000)"
            class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4 flex items-center justify-between">
            <span>{{ session('message') }}</span>
            <button @click="show = false" class="text-green-700 hover:text-green-900">
                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
                </svg>
            </button>
        </div>
    @endif

    @if (session()->has('error'))
        <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)"
            class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4 flex items-center justify-between">
            <span>{{ session('error') }}</span>
            <button @click="show = false" class="text-red-700 hover:text-red-900">
                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
                </svg>
            </button>
        </div>
    @endif

    {{-- Header con Filtros --}}
    <div class="mb-6">
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
            <div class="flex flex-col sm:flex-row gap-3 flex-1">
                <div class="flex-1">
                    <input wire:model.live.debounce.300ms="search"
                        type="text"
                        placeholder="Buscar productos..."
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                </div>

                <div class="sm:w-48">
                    <select wire:model.live="filterCategory"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                        <option value="">Todas las categorías</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}">{{ $category->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="sm:w-40">
                    <select wire:model.live="filterSaleType"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                        <option value="">Todos los tipos</option>
                        <option value="unit">Por unidad</option>
                        <option value="weight">Por peso</option>
                        <option value="both">Ambos</option>
                    </select>
                </div>
            </div>

            <button wire:click="create"
                class="bg-purple-600 hover:bg-purple-700 text-white px-6 py-2 rounded-lg transition font-medium flex items-center justify-center gap-2 whitespace-nowrap">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Nuevo Producto
            </button>
        </div>
    </div>

    {{-- Tabla de Productos --}}
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Producto</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Categoría</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tipo</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Precios (Web / POS / App)</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Estado</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($products as $product)
                        <tr class="hover:bg-gray-50 transition">
                            {{-- Producto --}}
                            <td class="px-6 py-4">
                                <div class="flex items-center">
                                    <div class="h-12 w-12 flex-shrink-0">
                                        @if($product->image_url)
                                            <img src="{{ $product->image_url }}" alt="{{ $product->name }}"
                                                class="h-12 w-12 rounded-lg object-cover border border-gray-200">
                                        @else
                                            <div class="h-12 w-12 rounded-lg bg-gradient-to-br from-purple-400 to-pink-400 flex items-center justify-center">
                                                <span class="text-2xl">🍇</span>
                                            </div>
                                        @endif
                                    </div>
                                    <div class="ml-4">
                                        <div class="text-sm font-medium text-gray-900">{{ $product->name }}</div>
                                        @if($product->description)
                                            <div class="text-xs text-gray-500 mt-1">{{ Str::limit($product->description, 50) }}</div>
                                        @endif
                                    </div>
                                </div>
                            </td>

                            {{-- Categoría --}}
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                                    {{ $product->category->name }}
                                </span>
                            </td>

                            {{-- Tipo de venta --}}
                            <td class="px-6 py-4 whitespace-nowrap">
                                @php
                                    $badges = [
                                        'unit'   => ['bg' => 'bg-blue-100',   'text' => 'text-blue-800',   'label' => 'Unidad', 'icon' => '📦'],
                                        'weight' => ['bg' => 'bg-orange-100', 'text' => 'text-orange-800', 'label' => 'Peso',   'icon' => '⚖️'],
                                        'both'   => ['bg' => 'bg-purple-100', 'text' => 'text-purple-800', 'label' => 'Ambos',  'icon' => '🔄'],
                                    ];
                                    $badge = $badges[$product->sale_type ?? 'unit'] ?? $badges['unit'];
                                @endphp
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-medium {{ $badge['bg'] }} {{ $badge['text'] }}">
                                    {{ $badge['icon'] }} {{ $badge['label'] }}
                                </span>
                            </td>

                            {{-- Precios por canal --}}
                            <td class="px-6 py-4">
                                @if(in_array($product->sale_type, ['weight', 'both']) && $product->price_per_kg)
                                    <div class="text-xs text-gray-500 mb-1 font-semibold">Por kg:</div>
                                    <div class="flex flex-wrap gap-1 mb-2">
                                        <span class="inline-flex items-center gap-1 text-xs bg-blue-50 text-blue-700 px-2 py-0.5 rounded-full font-medium">
                                            🌐 {{ number_format($product->price_per_kg, 0, ',', '.') }}
                                        </span>
                                        @if($product->price_per_kg_pos)
                                            <span class="inline-flex items-center gap-1 text-xs bg-green-50 text-green-700 px-2 py-0.5 rounded-full font-medium">
                                                🏪 {{ number_format($product->price_per_kg_pos, 0, ',', '.') }}
                                            </span>
                                        @endif
                                        @if($product->price_per_kg_delivery_app)
                                            <span class="inline-flex items-center gap-1 text-xs bg-orange-50 text-orange-700 px-2 py-0.5 rounded-full font-medium">
                                                🛵 {{ number_format($product->price_per_kg_delivery_app, 0, ',', '.') }}
                                            </span>
                                        @endif
                                    </div>
                                @endif

                                @if(in_array($product->sale_type, ['unit', 'both']))
                                    @php $activeVariants = $product->variants->where('is_active', true); @endphp
                                    @if($activeVariants->count() > 0)
                                        <div class="text-xs text-gray-500 mb-1 font-semibold">Por variante:</div>
                                        <div class="flex flex-wrap gap-1">
                                            @foreach($activeVariants->take(3) as $variant)
                                                <div class="text-xs bg-purple-50 text-purple-700 px-2 py-0.5 rounded-full">
                                                    {{ $variant->volume >= 1000 ? ($variant->volume/1000).'L' : $variant->volume.'ml' }}:
                                                    <span class="font-bold">{{ number_format($variant->price, 0, ',', '.') }}</span>
                                                    @if($variant->price_pos)
                                                        <span class="text-green-600"> / {{ number_format($variant->price_pos, 0, ',', '.') }}</span>
                                                    @endif
                                                </div>
                                            @endforeach
                                            @if($activeVariants->count() > 3)
                                                <span class="text-xs text-gray-400">+{{ $activeVariants->count() - 3 }} más</span>
                                            @endif
                                        </div>
                                    @else
                                        <span class="text-xs text-gray-400 italic">Sin variantes</span>
                                    @endif
                                @endif
                            </td>

                            {{-- Estado --}}
                            <td class="px-6 py-4 whitespace-nowrap">
                                <button wire:click="toggleActive({{ $product->id }})"
                                    class="px-3 py-1 rounded-full text-xs font-semibold transition {{ $product->is_active ? 'bg-green-100 text-green-800 hover:bg-green-200' : 'bg-red-100 text-red-800 hover:bg-red-200' }}">
                                    {{ $product->is_active ? 'Activo' : 'Inactivo' }}
                                </button>
                            </td>

                            {{-- Acciones --}}
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                <div class="flex items-center gap-3">
                                    <button wire:click="edit({{ $product->id }})"
                                        class="text-purple-600 hover:text-purple-900 font-medium transition flex items-center gap-1">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                        </svg>
                                        Editar
                                    </button>

                                    @php
                                        $hasSales = \App\Models\OrderItem::where('product_id', $product->id)->exists();
                                        $inCarts  = \App\Models\CartItem::where('product_id', $product->id)->exists();
                                    @endphp

                                    @if($hasSales || $inCarts)
                                        @if($product->is_active)
                                            <button wire:click="deactivateProduct({{ $product->id }})"
                                                wire:confirm="¿Desactivar este producto?"
                                                class="text-orange-600 hover:text-orange-900 font-medium transition flex items-center gap-1">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/>
                                                </svg>
                                                Desactivar
                                            </button>
                                        @else
                                            <span class="text-gray-400 text-xs">Con ventas</span>
                                        @endif
                                    @else
                                        <button wire:click="delete({{ $product->id }})"
                                            wire:confirm="¿Eliminar este producto? Esta acción no se puede deshacer."
                                            class="text-red-600 hover:text-red-900 font-medium transition flex items-center gap-1">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                            </svg>
                                            Eliminar
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center">
                                <div class="flex flex-col items-center justify-center text-gray-500">
                                    <svg class="w-16 h-16 mb-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
                                    </svg>
                                    <p class="text-lg font-medium">No se encontraron productos</p>
                                    <p class="text-sm mt-1">Ajustá los filtros o creá un nuevo producto</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Paginación --}}
    <div class="mt-4">
        {{ $products->links() }}
    </div>

    {{-- ====================== MODAL ====================== --}}
    @if($showModal)
        <div class="fixed inset-0 bg-black bg-opacity-50 flex items-start justify-center p-4 z-50 overflow-y-auto"
            wire:click="closeModal">
            <div class="bg-white rounded-xl max-w-5xl w-full my-8 shadow-2xl" wire:click.stop>

                {{-- Header --}}
                <div class="p-6 border-b border-gray-200 bg-gradient-to-r from-purple-600 to-pink-600 rounded-t-xl">
                    <div class="flex justify-between items-center">
                        <div>
                            <h2 class="text-2xl font-bold text-white">
                                {{ $editMode ? '✏️ Editar Producto' : '✨ Nuevo Producto' }}
                            </h2>
                            <p class="text-purple-100 text-sm mt-1">
                                {{ $editMode ? 'Actualizá la información del producto' : 'Completá los datos para crear un nuevo producto' }}
                            </p>
                        </div>
                        <button wire:click="closeModal" class="text-white hover:text-purple-200 transition">
                            <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>
                </div>

                <form wire:submit.prevent="save">
                    <div class="p-6 space-y-6 max-h-[calc(90vh-180px)] overflow-y-auto">

                        {{-- ── 1. INFO GENERAL ── --}}
                        <div class="bg-gradient-to-br from-purple-50 to-pink-50 rounded-xl p-5 space-y-4 border border-purple-100">
                            <h3 class="font-bold text-lg text-gray-900 flex items-center gap-2">
                                <span class="bg-purple-600 text-white rounded-full w-7 h-7 flex items-center justify-center text-sm font-bold">1</span>
                                Información General
                            </h3>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div class="md:col-span-2">
                                    <label class="block text-sm font-medium text-gray-700 mb-1">
                                        Nombre <span class="text-red-500">*</span>
                                    </label>
                                    <input wire:model="name" type="text" required
                                        placeholder="Ej: Açaí Bowl Energía"
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                                    @error('name') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">
                                        Categoría <span class="text-red-500">*</span>
                                    </label>
                                    <select wire:model="category_id" required
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                                        <option value="">Seleccioná una categoría</option>
                                        @foreach($categories as $category)
                                            <option value="{{ $category->id }}">{{ $category->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('category_id') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                                </div>

                                <div class="flex items-center">
                                    <label class="flex items-center cursor-pointer gap-3">
                                        <input wire:model="is_active" type="checkbox"
                                            class="h-5 w-5 text-purple-600 focus:ring-purple-500 border-gray-300 rounded">
                                        <span class="text-sm font-medium text-gray-700">Producto activo y visible</span>
                                    </label>
                                </div>

                                <div class="md:col-span-2">
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Descripción</label>
                                    <textarea wire:model="description" rows="2"
                                        placeholder="Describe el producto..."
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent"></textarea>
                                </div>

                                <div class="md:col-span-2">
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Ingredientes</label>
                                    <textarea wire:model="ingredients" rows="2"
                                        placeholder="Açaí, granola, banana, miel..."
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent"></textarea>
                                </div>
                            </div>
                        </div>

                        {{-- ── 2. TIPO DE VENTA ── --}}
                        <div class="bg-gradient-to-br from-amber-50 to-orange-50 rounded-xl p-5 border border-amber-100">
                            <h3 class="font-bold text-lg text-gray-900 flex items-center gap-2 mb-3">
                                <span class="bg-amber-500 text-white rounded-full w-7 h-7 flex items-center justify-center text-sm font-bold">2</span>
                                Tipo de Venta
                            </h3>

                            <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                                <label class="relative cursor-pointer">
                                    <input type="radio" wire:model.live="sale_type" value="unit" class="peer sr-only">
                                    <div class="p-4 border-2 rounded-xl transition-all peer-checked:border-blue-500 peer-checked:bg-blue-50 border-gray-200 hover:border-blue-300">
                                        <div class="flex items-center gap-2 mb-1">
                                            <span class="text-2xl">📦</span>
                                            <span class="font-bold text-gray-900">Por Unidad</span>
                                        </div>
                                        <p class="text-xs text-gray-500">Vasos con tamaño fijo</p>
                                        <div class="mt-2 flex gap-1 flex-wrap">
                                            <span class="text-xs bg-blue-100 text-blue-700 px-2 py-0.5 rounded-full">🌐 Web</span>
                                            <span class="text-xs bg-green-100 text-green-700 px-2 py-0.5 rounded-full">🏪 POS</span>
                                            <span class="text-xs bg-orange-100 text-orange-700 px-2 py-0.5 rounded-full">🛵 App</span>
                                        </div>
                                    </div>
                                </label>

                                <label class="relative cursor-pointer">
                                    <input type="radio" wire:model.live="sale_type" value="weight" class="peer sr-only">
                                    <div class="p-4 border-2 rounded-xl transition-all peer-checked:border-orange-500 peer-checked:bg-orange-50 border-gray-200 hover:border-orange-300">
                                        <div class="flex items-center gap-2 mb-1">
                                            <span class="text-2xl">⚖️</span>
                                            <span class="font-bold text-gray-900">Por Peso</span>
                                        </div>
                                        <p class="text-xs text-gray-500">Precio por kilogramo</p>
                                        <div class="mt-2">
                                            <span class="text-xs bg-orange-100 text-orange-700 px-2 py-0.5 rounded-full">⚠️ Solo POS</span>
                                        </div>
                                    </div>
                                </label>

                                <label class="relative cursor-pointer">
                                    <input type="radio" wire:model.live="sale_type" value="both" class="peer sr-only">
                                    <div class="p-4 border-2 rounded-xl transition-all peer-checked:border-purple-500 peer-checked:bg-purple-50 border-gray-200 hover:border-purple-300">
                                        <div class="flex items-center gap-2 mb-1">
                                            <span class="text-2xl">🔄</span>
                                            <span class="font-bold text-gray-900">Ambos</span>
                                        </div>
                                        <p class="text-xs text-gray-500">Unidades + por peso</p>
                                        <div class="mt-2 flex gap-1 flex-wrap">
                                            <span class="text-xs bg-blue-100 text-blue-700 px-2 py-0.5 rounded-full">Unidad: todo</span>
                                            <span class="text-xs bg-orange-100 text-orange-700 px-2 py-0.5 rounded-full">Peso: POS</span>
                                        </div>
                                    </div>
                                </label>
                            </div>
                        </div>

                        {{-- ── 3. PRECIOS POR KG (si aplica) ── --}}
                        @if(in_array($sale_type, ['weight', 'both']))
                            <div class="bg-gradient-to-br from-orange-50 to-red-50 rounded-xl p-5 border-2 border-orange-200">
                                <h3 class="font-bold text-lg text-gray-900 flex items-center gap-2 mb-1">
                                    <span class="bg-orange-500 text-white rounded-full w-7 h-7 flex items-center justify-center text-sm font-bold">3</span>
                                    Precios por Kilogramo
                                    <span class="text-xs bg-orange-100 text-orange-700 px-2 py-1 rounded-full font-normal ml-1">Solo POS</span>
                                </h3>
                                <p class="text-sm text-gray-500 mb-4 ml-9">
                                    El precio web es obligatorio. POS y Pedidos Ya son opcionales — si los dejás vacíos, se usa el precio web.
                                </p>

                                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                    {{-- Precio web/base --}}
                                    <div>
                                        <label class="block text-sm font-bold text-blue-700 mb-1">
                                            🌐 Web / Base <span class="text-red-500">*</span>
                                        </label>
                                        <div class="relative">
                                            <input wire:model.live="price_per_kg" type="number" step="1000" min="1"
                                                placeholder="87000"
                                                class="w-full px-4 py-3 text-lg font-bold border-2 border-blue-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                            <span class="absolute right-3 top-3.5 text-gray-500 text-sm font-medium">Gs/kg</span>
                                        </div>
                                        @error('price_per_kg') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                                    </div>

                                    {{-- Precio POS --}}
                                    <div>
                                        <label class="block text-sm font-bold text-green-700 mb-1">
                                            🏪 Tienda (POS)
                                        </label>
                                        <div class="relative">
                                            <input wire:model="price_per_kg_pos" type="number" step="1000" min="1"
                                                placeholder="igual al web"
                                                class="w-full px-4 py-3 text-lg font-bold border-2 border-green-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
                                            <span class="absolute right-3 top-3.5 text-gray-500 text-sm font-medium">Gs/kg</span>
                                        </div>
                                        @error('price_per_kg_pos') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                                    </div>

                                    {{-- Precio Pedidos Ya --}}
                                    <div>
                                        <label class="block text-sm font-bold text-orange-700 mb-1">
                                            🛵 Pedidos Ya / App
                                        </label>
                                        <div class="relative">
                                            <input wire:model="price_per_kg_delivery_app" type="number" step="1000" min="1"
                                                placeholder="igual al web"
                                                class="w-full px-4 py-3 text-lg font-bold border-2 border-orange-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500">
                                            <span class="absolute right-3 top-3.5 text-gray-500 text-sm font-medium">Gs/kg</span>
                                        </div>
                                        @error('price_per_kg_delivery_app') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                                    </div>
                                </div>

                                {{-- Preview --}}
                                @if($price_per_kg && $price_per_kg > 0)
                                    <div class="mt-4 bg-white rounded-lg p-4 border border-orange-200">
                                        <p class="text-sm font-medium text-gray-700 mb-3">Precio web por gramos:</p>
                                        <div class="grid grid-cols-4 gap-2">
                                            @foreach([250, 500, 750, 1000] as $grams)
                                                <div class="bg-orange-50 rounded-lg p-2 text-center">
                                                    <div class="text-xs text-gray-500">{{ $grams }}g</div>
                                                    <div class="font-bold text-orange-800 text-sm">
                                                        {{ number_format($price_per_kg * ($grams / 1000), 0, ',', '.') }} Gs
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif
                            </div>
                        @endif

                        {{-- ── 4. IMAGEN ── --}}
                        <div class="bg-blue-50 rounded-xl p-5 border border-blue-100">
                            <h3 class="font-bold text-lg text-gray-900 flex items-center gap-2 mb-4">
                                <span class="bg-blue-500 text-white rounded-full w-7 h-7 flex items-center justify-center text-sm font-bold">
                                    {{ in_array($sale_type, ['weight', 'both']) ? '4' : '3' }}
                                </span>
                                Imagen del Producto
                            </h3>

                            <input wire:model="image" type="file" accept="image/*"
                                class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-purple-50 file:text-purple-700 hover:file:bg-purple-100 transition">
                            @error('image') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                            <p class="text-xs text-gray-500 mt-1">JPG, PNG o WebP. Máximo 5MB. Se convierte automáticamente a WebP.</p>

                            <div class="flex items-start gap-4 mt-4">
                                @if ($image)
                                    <div>
                                        <p class="text-sm font-medium text-gray-700 mb-2">Nueva imagen:</p>
                                        <img src="{{ $image->temporaryUrl() }}" alt="Preview"
                                            class="h-40 w-40 object-cover rounded-lg border-2 border-purple-300 shadow">
                                    </div>
                                @endif

                                @if($editMode && $currentImage && !$image)
                                    <div>
                                        <p class="text-sm font-medium text-gray-700 mb-2">Imagen actual:</p>
                                        <div class="relative inline-block">
                                            <img src="{{ Storage::disk('public')->url($currentImage) }}" alt="Current"
                                                class="h-40 w-40 object-cover rounded-lg border-2 border-gray-300 shadow">
                                            <button type="button" wire:click="removeImage"
                                                wire:confirm="¿Eliminar esta imagen?"
                                                class="absolute top-2 right-2 bg-red-500 hover:bg-red-600 text-white rounded-full p-1.5 shadow-lg transition">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                                </svg>
                                            </button>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>

                        {{-- ── 5. VARIANTES DE TAMAÑO (si aplica) ── --}}
                        @if(in_array($sale_type, ['unit', 'both']))
                            <div class="bg-gradient-to-br from-green-50 to-teal-50 rounded-xl p-5 border border-green-100">
                                <h3 class="font-bold text-lg text-gray-900 flex items-center gap-2 mb-1">
                                    <span class="bg-green-600 text-white rounded-full w-7 h-7 flex items-center justify-center text-sm font-bold">
                                        {{ in_array($sale_type, ['both']) ? '5' : '4' }}
                                    </span>
                                    Variantes de Tamaño
                                </h3>
                                <p class="text-sm text-gray-500 mb-4 ml-9">
                                    Completá el <strong>precio web</strong> para activar una variante. POS y Pedidos Ya son opcionales.
                                    Si los dejás vacíos, se usa el precio web para esos canales.
                                </p>

                                {{-- Leyenda de colores --}}
                                <div class="flex flex-wrap gap-3 mb-4 ml-9">
                                    <span class="flex items-center gap-1 text-xs font-medium">
                                        <span class="w-3 h-3 rounded-full bg-blue-400 inline-block"></span>
                                        🌐 Web/Ecommerce
                                    </span>
                                    <span class="flex items-center gap-1 text-xs font-medium">
                                        <span class="w-3 h-3 rounded-full bg-green-400 inline-block"></span>
                                        🏪 Tienda (POS)
                                    </span>
                                    <span class="flex items-center gap-1 text-xs font-medium">
                                        <span class="w-3 h-3 rounded-full bg-orange-400 inline-block"></span>
                                        🛵 Pedidos Ya
                                    </span>
                                    <span class="flex items-center gap-1 text-xs font-medium">
                                        <span class="w-3 h-3 rounded-full bg-purple-400 inline-block"></span>
                                        📦 Stock vasitos
                                    </span>
                                </div>

                                <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                                    @foreach($variants as $index => $variant)
                                        @php
                                            $hasPrice = !empty($variant['price']) && $variant['price'] > 0;
                                            $isNew    = $variant['volume'] == 1500;
                                            $volLabel = $variant['volume'] >= 1000
                                                ? ($variant['volume'] / 1000) . ' Litro' . ($variant['volume'] > 1000 ? 's' : '')
                                                : $variant['volume'] . 'ml';
                                            // Stock del cup_size
                                            $cupStock = $variant['cup_stock'] ?? $cupSizes->firstWhere('volume_ml', $variant['volume'])?->stock ?? 0;
                                        @endphp
                                        <div class="bg-white rounded-xl p-4 border-2 transition-all
                                            {{ $hasPrice ? 'border-green-300 shadow-sm' : 'border-gray-200' }}">

                                            {{-- Header de la variante --}}
                                            <div class="flex items-center justify-between mb-3">
                                                <div class="flex items-center gap-2">
                                                    <span class="bg-purple-600 text-white font-bold px-3 py-1 rounded-full text-sm">
                                                        {{ $volLabel }}
                                                    </span>
                                                    @if($isNew)
                                                        <span class="text-xs bg-yellow-100 text-yellow-800 px-2 py-0.5 rounded-full font-medium">
                                                            ✨ Nuevo
                                                        </span>
                                                    @endif
                                                    {{-- Stock de vasitos --}}
                                                    <span class="text-xs px-2 py-0.5 rounded-full font-medium
                                                        {{ $cupStock <= 10 ? 'bg-red-100 text-red-700' : 'bg-purple-100 text-purple-700' }}">
                                                        📦 {{ $cupStock }} vasitos
                                                    </span>
                                                </div>
                                                <label class="flex items-center gap-2 cursor-pointer">
                                                    <input wire:model="variants.{{ $index }}.is_active"
                                                        type="checkbox"
                                                        class="h-4 w-4 text-green-600 focus:ring-green-500 border-gray-300 rounded">
                                                    <span class="text-sm text-gray-600">Activa</span>
                                                </label>
                                            </div>

                                            {{-- Tres precios en columna --}}
                                            <div class="space-y-2">
                                                {{-- Precio Web --}}
                                                <div>
                                                    <label class="block text-xs font-bold text-blue-700 mb-1">🌐 Web (obligatorio)</label>
                                                    <div class="relative">
                                                        <input wire:model="variants.{{ $index }}.price"
                                                            type="number" step="1000" min="0"
                                                            placeholder="35000"
                                                            class="w-full px-3 py-2 border-2 border-blue-200 rounded-lg focus:ring-2 focus:ring-blue-400 focus:border-blue-400 text-sm font-medium">
                                                        <span class="absolute right-2 top-2 text-xs text-gray-400">Gs</span>
                                                    </div>
                                                    @error("variants.{$index}.price")
                                                        <span class="text-red-500 text-xs">{{ $message }}</span>
                                                    @enderror
                                                </div>

                                                {{-- Precio POS --}}
                                                <div>
                                                    <label class="block text-xs font-bold text-green-700 mb-1">
                                                        🏪 Tienda (POS)
                                                        @if(!empty($variant['price']) && $variant['price'] > 0 && empty($variant['price_pos']))
                                                            <span class="text-gray-400 font-normal">= usa precio web</span>
                                                        @endif
                                                    </label>
                                                    <div class="relative">
                                                        <input wire:model="variants.{{ $index }}.price_pos"
                                                            type="number" step="1000" min="0"
                                                            placeholder="{{ !empty($variant['price']) ? number_format($variant['price'], 0) : 'igual al web' }}"
                                                            class="w-full px-3 py-2 border-2 border-green-200 rounded-lg focus:ring-2 focus:ring-green-400 focus:border-green-400 text-sm font-medium">
                                                        <span class="absolute right-2 top-2 text-xs text-gray-400">Gs</span>
                                                    </div>
                                                </div>

                                                {{-- Precio Pedidos Ya --}}
                                                <div>
                                                    <label class="block text-xs font-bold text-orange-700 mb-1">
                                                        🛵 Pedidos Ya
                                                        @if(!empty($variant['price']) && $variant['price'] > 0 && empty($variant['price_delivery_app']))
                                                            <span class="text-gray-400 font-normal">= usa precio web</span>
                                                        </span>
                                                        @endif
                                                    </label>
                                                    <div class="relative">
                                                        <input wire:model="variants.{{ $index }}.price_delivery_app"
                                                            type="number" step="1000" min="0"
                                                            placeholder="{{ !empty($variant['price']) ? number_format($variant['price'], 0) : 'igual al web' }}"
                                                            class="w-full px-3 py-2 border-2 border-orange-200 rounded-lg focus:ring-2 focus:ring-orange-400 focus:border-orange-400 text-sm font-medium">
                                                        <span class="absolute right-2 top-2 text-xs text-gray-400">Gs</span>
                                                    </div>
                                                </div>
                                            </div>

                                            {{-- Resumen de precios si tiene precio web --}}
                                            @if($hasPrice)
                                                <div class="mt-3 pt-3 border-t border-gray-100 grid grid-cols-3 gap-1 text-center">
                                                    <div class="bg-blue-50 rounded-lg p-1.5">
                                                        <div class="text-xs text-blue-500">Web</div>
                                                        <div class="text-xs font-bold text-blue-800">
                                                            {{ number_format($variant['price'], 0, ',', '.') }}
                                                        </div>
                                                    </div>
                                                    <div class="bg-green-50 rounded-lg p-1.5">
                                                        <div class="text-xs text-green-500">POS</div>
                                                        <div class="text-xs font-bold text-green-800">
                                                            {{ !empty($variant['price_pos']) ? number_format($variant['price_pos'], 0, ',', '.') : '= web' }}
                                                        </div>
                                                    </div>
                                                    <div class="bg-orange-50 rounded-lg p-1.5">
                                                        <div class="text-xs text-orange-500">App</div>
                                                        <div class="text-xs font-bold text-orange-800">
                                                            {{ !empty($variant['price_delivery_app']) ? number_format($variant['price_delivery_app'], 0, ',', '.') : '= web' }}
                                                        </div>
                                                    </div>
                                                </div>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>

                                {{-- Aviso stock vasitos --}}
                                @php
                                    $lowStock = $cupSizes->filter(fn($cs) => $cs->is_low_stock);
                                @endphp
                                @if($lowStock->count() > 0)
                                    <div class="mt-4 bg-red-50 border border-red-200 rounded-lg p-3 flex items-start gap-2">
                                        <span class="text-xl">⚠️</span>
                                        <div>
                                            <p class="text-sm font-bold text-red-700">Stock bajo de vasitos:</p>
                                            <p class="text-xs text-red-600">
                                                {{ $lowStock->map(fn($cs) => "{$cs->name}: {$cs->stock} und.")->implode(' · ') }}
                                            </p>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        @endif

                    </div>{{-- fin scroll --}}

                    {{-- Footer --}}
                    <div class="p-6 border-t border-gray-200 bg-gray-50 rounded-b-xl flex justify-end gap-3">
                        <button type="button" wire:click="closeModal"
                            class="px-6 py-2 border-2 border-gray-300 rounded-lg text-gray-700 hover:bg-gray-100 transition font-medium">
                            Cancelar
                        </button>
                        <button type="submit"
                            class="px-8 py-2 bg-purple-600 hover:bg-purple-700 text-white rounded-lg transition font-bold shadow-lg flex items-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                @if($editMode)
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                                @else
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                @endif
                            </svg>
                            {{ $editMode ? 'Actualizar Producto' : 'Crear Producto' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    {{-- Loading --}}
    <div wire:loading.delay class="fixed inset-0 bg-black bg-opacity-30 flex items-center justify-center z-50">
        <div class="bg-white rounded-xl p-6 shadow-xl flex items-center gap-3">
            <svg class="animate-spin h-6 w-6 text-purple-600" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"/>
            </svg>
            <span class="text-gray-700 font-medium">Procesando...</span>
        </div>
    </div>
</div>