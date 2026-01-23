<div>
    {{-- Mensajes Flash --}}
    @if (session()->has('message'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4 flex items-center justify-between">
            <span>{{ session('message') }}</span>
            <button onclick="this.parentElement.remove()" class="text-green-700 hover:text-green-900">
                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
                </svg>
            </button>
        </div>
    @endif

    @if (session()->has('error'))
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4 flex items-center justify-between">
            <span>{{ session('error') }}</span>
            <button onclick="this.parentElement.remove()" class="text-red-700 hover:text-red-900">
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
                        placeholder="Buscar productos por nombre..." 
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

                {{-- 🆕 Filtro por tipo de venta --}}
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
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Producto
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Categoría
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Tipo Venta
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Precios
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Estado
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Acciones
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($products as $product)
                        <tr class="hover:bg-gray-50 transition">
                            <td class="px-6 py-4">
                                <div class="flex items-center">
                                    <div class="h-12 w-12 flex-shrink-0">
                                        @if($product->image_url)
                                            <img src="{{ $product->image_url }}" 
                                                alt="{{ $product->name }}" 
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
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                                    {{ $product->category->name }}
                                </span>
                            </td>
                            {{-- 🆕 Columna Tipo de Venta --}}
                            <td class="px-6 py-4 whitespace-nowrap">
                                @php
                                    $saleType = $product->sale_type ?? 'unit';
                                    $badges = [
                                        'unit' => ['bg' => 'bg-blue-100', 'text' => 'text-blue-800', 'label' => 'Por unidad', 'icon' => '📦'],
                                        'weight' => ['bg' => 'bg-orange-100', 'text' => 'text-orange-800', 'label' => 'Por peso', 'icon' => '⚖️'],
                                        'both' => ['bg' => 'bg-purple-100', 'text' => 'text-purple-800', 'label' => 'Ambos', 'icon' => '🔄'],
                                    ];
                                    $badge = $badges[$saleType] ?? $badges['unit'];
                                @endphp
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-medium {{ $badge['bg'] }} {{ $badge['text'] }}">
                                    <span>{{ $badge['icon'] }}</span>
                                    {{ $badge['label'] }}
                                </span>
                                @if($saleType === 'weight')
                                    <div class="text-xs text-orange-600 mt-1 font-medium">Solo POS</div>
                                @endif
                            </td>
                            {{-- 🆕 Columna Precios actualizada --}}
                            <td class="px-6 py-4">
                                <div class="space-y-1">
                                    {{-- Precio por kg si aplica --}}
                                    @if(in_array($product->sale_type ?? 'unit', ['weight', 'both']) && $product->price_per_kg)
                                        <div class="flex items-center gap-1">
                                            <span class="text-orange-600 font-bold text-sm">
                                                {{ number_format($product->price_per_kg, 0, ',', '.') }} Gs/kg
                                            </span>
                                            <span class="text-xs text-orange-500">⚖️</span>
                                        </div>
                                    @endif
                                    
                                    {{-- Variantes si aplica --}}
                                    @if(in_array($product->sale_type ?? 'unit', ['unit', 'both']))
                                        @if($product->variants->count() > 0)
                                            <div class="flex flex-wrap gap-1">
                                                @foreach($product->variants->where('is_active', true)->take(3) as $variant)
                                                    <span class="inline-flex items-center text-xs font-medium text-purple-700 bg-purple-50 px-1.5 py-0.5 rounded">
                                                        {{ $variant->volume }}ml: {{ number_format($variant->price, 0, ',', '.') }}
                                                    </span>
                                                @endforeach
                                                @if($product->variants->where('is_active', true)->count() > 3)
                                                    <span class="text-xs text-gray-400">+{{ $product->variants->where('is_active', true)->count() - 3 }}</span>
                                                @endif
                                            </div>
                                        @else
                                            <span class="text-xs text-gray-400 italic">Sin variantes</span>
                                        @endif
                                    @endif
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <button wire:click="toggleActive({{ $product->id }})" 
                                    class="px-3 py-1 rounded-full text-xs font-semibold transition {{ $product->is_active ? 'bg-green-100 text-green-800 hover:bg-green-200' : 'bg-red-100 text-red-800 hover:bg-red-200' }}">
                                    {{ $product->is_active ? 'Activo' : 'Inactivo' }}
                                </button>
                            </td>
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
                                        $inCarts = \App\Models\CartItem::where('product_id', $product->id)->exists();
                                    @endphp
                                    
                                    @if($hasSales || $inCarts)
                                        @if($product->is_active)
                                            <button 
                                                wire:click="deactivateProduct({{ $product->id }})"
                                                wire:confirm="Este producto tiene {{ $hasSales ? 'ventas asociadas' : 'items en carritos de clientes' }}. ¿Deseas desactivarlo?"
                                                class="text-orange-600 hover:text-orange-900 font-medium transition flex items-center gap-1"
                                                title="Desactivar producto">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/>
                                                </svg>
                                                Desactivar
                                            </button>
                                        @else
                                            <span class="text-gray-400 text-xs flex items-center gap-1">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                                </svg>
                                                Con ventas
                                            </span>
                                        @endif
                                    @else
                                        <button 
                                            wire:click="delete({{ $product->id }})" 
                                            wire:confirm="¿Estás seguro de eliminar este producto? Esta acción no se puede deshacer."
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
                                    <p class="text-sm mt-1">Intenta ajustar los filtros o crea un nuevo producto</p>
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

    {{-- Modal de Crear/Editar --}}
    @if($showModal)
        <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50 overflow-y-auto" 
            wire:click="closeModal">
            <div class="bg-white rounded-lg max-w-4xl w-full my-8" wire:click.stop>
                {{-- Header del Modal --}}
                <div class="p-6 border-b border-gray-200">
                    <div class="flex justify-between items-start">
                        <div>
                            <h2 class="text-2xl font-bold text-gray-900">
                                {{ $editMode ? 'Editar Producto' : 'Nuevo Producto' }}
                            </h2>
                            <p class="text-sm text-gray-600 mt-1">
                                {{ $editMode ? 'Actualiza la información del producto' : 'Completa los datos para crear un nuevo producto' }}
                            </p>
                        </div>
                        <button wire:click="closeModal" 
                            class="text-gray-400 hover:text-gray-600 transition">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>
                </div>

                {{-- Contenido del Modal --}}
                <form wire:submit.prevent="save">
                    <div class="p-6 space-y-6 max-h-[calc(90vh-180px)] overflow-y-auto">
                        
                        {{-- Información General --}}
                        <div class="bg-gradient-to-br from-purple-50 to-pink-50 rounded-lg p-5 space-y-4">
                            <div class="flex items-center gap-2 mb-2">
                                <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                <h3 class="font-bold text-lg text-gray-900">Información General</h3>
                            </div>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div class="md:col-span-2">
                                    <label class="block text-sm font-medium text-gray-700 mb-1">
                                        Nombre del Producto <span class="text-red-500">*</span>
                                    </label>
                                    <input wire:model="name" 
                                        type="text" 
                                        required
                                        placeholder="Ej: Bowl Açaí Energía"
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                                    @error('name') 
                                        <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> 
                                    @enderror
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">
                                        Categoría <span class="text-red-500">*</span>
                                    </label>
                                    <select wire:model="category_id" 
                                        required
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                                        <option value="">Selecciona una categoría</option>
                                        @foreach($categories as $category)
                                            <option value="{{ $category->id }}">{{ $category->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('category_id') 
                                        <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> 
                                    @enderror
                                </div>

                                <div class="flex items-center">
                                    <label class="flex items-center cursor-pointer">
                                        <input wire:model="is_active" 
                                            type="checkbox" 
                                            class="h-5 w-5 text-purple-600 focus:ring-purple-500 border-gray-300 rounded">
                                        <span class="ml-3 text-sm font-medium text-gray-700">
                                            Producto activo y visible
                                        </span>
                                    </label>
                                </div>

                                <div class="md:col-span-2">
                                    <label class="block text-sm font-medium text-gray-700 mb-1">
                                        Descripción
                                    </label>
                                    <textarea wire:model="description" 
                                        rows="3"
                                        placeholder="Describe el producto, sus beneficios y características..."
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent"></textarea>
                                    @error('description') 
                                        <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> 
                                    @enderror
                                </div>

                                <div class="md:col-span-2">
                                    <label class="block text-sm font-medium text-gray-700 mb-1">
                                        Ingredientes
                                    </label>
                                    <textarea wire:model="ingredients" 
                                        rows="2"
                                        placeholder="Ej: Açaí orgánico, granola casera, banana, miel pura, fresas"
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent"></textarea>
                                    @error('ingredients') 
                                        <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> 
                                    @enderror
                                </div>
                            </div>
                        </div>

                        {{-- 🆕 TIPO DE VENTA --}}
                        <div class="bg-gradient-to-br from-amber-50 to-orange-50 rounded-lg p-5 space-y-4">
                            <div class="flex items-center gap-2 mb-2">
                                <span class="text-xl">🏷️</span>
                                <h3 class="font-bold text-lg text-gray-900">Tipo de Venta</h3>
                            </div>
                            <p class="text-sm text-gray-600 mb-4">
                                Selecciona cómo se venderá este producto. Los productos <strong>por peso</strong> solo estarán disponibles en el <strong>POS (tienda física)</strong>.
                            </p>

                            <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                                {{-- Opción: Por Unidad --}}
                                <label class="relative cursor-pointer">
                                    <input type="radio" wire:model.live="sale_type" value="unit" class="peer sr-only">
                                    <div class="p-4 border-2 rounded-xl transition-all peer-checked:border-blue-500 peer-checked:bg-blue-50 border-gray-200 hover:border-blue-300">
                                        <div class="flex items-center gap-3 mb-2">
                                            <span class="text-2xl">📦</span>
                                            <span class="font-bold text-gray-900">Por Unidad</span>
                                        </div>
                                        <p class="text-xs text-gray-600">Vasos de 300ml, 400ml, 500ml, etc.</p>
                                        <div class="mt-2 flex gap-1">
                                            <span class="text-xs bg-blue-100 text-blue-700 px-2 py-0.5 rounded-full">Web</span>
                                            <span class="text-xs bg-green-100 text-green-700 px-2 py-0.5 rounded-full">POS</span>
                                        </div>
                                    </div>
                                </label>

                                {{-- Opción: Por Peso --}}
                                <label class="relative cursor-pointer">
                                    <input type="radio" wire:model.live="sale_type" value="weight" class="peer sr-only">
                                    <div class="p-4 border-2 rounded-xl transition-all peer-checked:border-orange-500 peer-checked:bg-orange-50 border-gray-200 hover:border-orange-300">
                                        <div class="flex items-center gap-3 mb-2">
                                            <span class="text-2xl">⚖️</span>
                                            <span class="font-bold text-gray-900">Por Peso</span>
                                        </div>
                                        <p class="text-xs text-gray-600">Se vende por kilogramo (Gs/kg)</p>
                                        <div class="mt-2">
                                            <span class="text-xs bg-orange-100 text-orange-700 px-2 py-0.5 rounded-full font-medium">⚠️ Solo POS</span>
                                        </div>
                                    </div>
                                </label>

                                {{-- Opción: Ambos --}}
                                <label class="relative cursor-pointer">
                                    <input type="radio" wire:model.live="sale_type" value="both" class="peer sr-only">
                                    <div class="p-4 border-2 rounded-xl transition-all peer-checked:border-purple-500 peer-checked:bg-purple-50 border-gray-200 hover:border-purple-300">
                                        <div class="flex items-center gap-3 mb-2">
                                            <span class="text-2xl">🔄</span>
                                            <span class="font-bold text-gray-900">Ambos</span>
                                        </div>
                                        <p class="text-xs text-gray-600">Unidades + venta por peso</p>
                                        <div class="mt-2 flex gap-1">
                                            <span class="text-xs bg-blue-100 text-blue-700 px-2 py-0.5 rounded-full">Unidad: Web+POS</span>
                                            <span class="text-xs bg-orange-100 text-orange-700 px-2 py-0.5 rounded-full">Peso: POS</span>
                                        </div>
                                    </div>
                                </label>
                            </div>
                            @error('sale_type') 
                                <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> 
                            @enderror
                        </div>

                        {{-- 🆕 PRECIO POR KILO (solo si sale_type es weight o both) --}}
                        @if(in_array($sale_type, ['weight', 'both']))
                            <div class="bg-gradient-to-br from-orange-50 to-amber-50 rounded-lg p-5 space-y-4 border-2 border-orange-200">
                                <div class="flex items-center gap-2 mb-2">
                                    <span class="text-xl">⚖️</span>
                                    <h3 class="font-bold text-lg text-gray-900">Precio por Kilogramo</h3>
                                    <span class="text-xs bg-orange-100 text-orange-700 px-2 py-1 rounded-full font-medium ml-2">Solo disponible en POS</span>
                                </div>
                                
                                <div class="max-w-sm">
                                    <label class="block text-sm font-medium text-gray-700 mb-1">
                                        Precio por Kg <span class="text-red-500">*</span>
                                    </label>
                                    <div class="relative">
                                        <input wire:model="price_per_kg" 
                                            type="number" 
                                            step="1" 
                                            min="1"
                                            placeholder="87000"
                                            class="w-full px-4 py-3 text-lg font-bold border-2 border-orange-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500">
                                        <span class="absolute right-4 top-3 text-gray-500 font-medium">Gs/kg</span>
                                    </div>
                                    @error('price_per_kg') 
                                        <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> 
                                    @enderror
                                </div>

                                {{-- Preview de precios --}}
                                @if($price_per_kg && $price_per_kg > 0)
                                    <div class="bg-white rounded-lg p-4 border border-orange-200">
                                        <p class="text-sm font-medium text-gray-700 mb-2">Vista previa de precios:</p>
                                        <div class="grid grid-cols-2 md:grid-cols-4 gap-3 text-sm">
                                            @foreach([250, 500, 750, 1000] as $grams)
                                                <div class="bg-orange-50 rounded-lg p-2 text-center">
                                                    <div class="font-bold text-orange-800">{{ $grams }}g</div>
                                                    <div class="text-orange-600">{{ number_format($price_per_kg * ($grams/1000), 0, ',', '.') }} Gs</div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif
                            </div>
                        @endif

                        {{-- Imagen del Producto --}}
                        <div class="bg-blue-50 rounded-lg p-5 space-y-4">
                            <div class="flex items-center gap-2 mb-2">
                                <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                                <h3 class="font-bold text-lg text-gray-900">Imagen del Producto</h3>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Subir Imagen (se convertirá a WebP)
                                </label>
                                <input wire:model="image" 
                                    type="file" 
                                    accept="image/*"
                                    class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-purple-50 file:text-purple-700 hover:file:bg-purple-100 transition">
                                @error('image') 
                                    <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> 
                                @enderror
                                <p class="text-xs text-gray-500 mt-1">Formatos: JPG, PNG, WebP. Máximo 5MB.</p>
                            </div>

                            <div class="flex items-start gap-4">
                                @if ($image)
                                    <div class="flex-1">
                                        <p class="text-sm font-medium text-gray-700 mb-2">Vista previa:</p>
                                        <img src="{{ $image->temporaryUrl() }}" 
                                            alt="Preview" 
                                            class="h-48 w-48 object-cover rounded-lg border-2 border-purple-300 shadow-md">
                                    </div>
                                @endif
                                
                                @if($editMode && $currentImage && !$image)
                                    <div>
                                        <p class="text-sm font-medium text-gray-700 mb-2">Imagen actual:</p>
                                        <div class="relative inline-block">
                                            <img src="{{ Storage::disk('public')->url($currentImage) }}" 
                                                alt="Current" 
                                                class="h-40 w-40 object-cover rounded-lg border-2 border-gray-300 shadow-md">
                                            <button type="button" 
                                                wire:click="removeImage"
                                                wire:confirm="¿Estás seguro de eliminar esta imagen?"
                                                class="absolute top-2 right-2 bg-red-500 hover:bg-red-600 text-white rounded-full p-2 shadow-lg transition">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                </svg>
                                            </button>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>

                        {{-- Variantes de Tamaño (solo si sale_type es unit o both) --}}
                        @if(in_array($sale_type, ['unit', 'both']))
                            <div class="bg-gradient-to-br from-green-50 to-teal-50 rounded-lg p-5 space-y-4">
                                <div class="flex items-center gap-2 mb-2">
                                    <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z"/>
                                    </svg>
                                    <h3 class="font-bold text-lg text-gray-900">Variantes de Tamaño</h3>
                                    <span class="text-xs bg-blue-100 text-blue-700 px-2 py-1 rounded-full ml-2">Disponible en Web y POS</span>
                                </div>
                                <p class="text-sm text-gray-600">
                                    Configura los diferentes tamaños disponibles. 
                                    <span class="font-semibold">Debe haber al menos una variante con precio.</span>
                                </p>
                                
                                <div class="grid grid-cols-1 lg:grid-cols-2 xl:grid-cols-3 gap-4">
                                    @foreach($variants as $index => $variant)
                                        <div class="bg-white rounded-lg p-4 border-2 {{ !empty($variant['price']) && $variant['price'] > 0 ? 'border-green-300' : 'border-gray-200' }} shadow-sm hover:shadow-md transition">
                                            <div class="flex items-center justify-between mb-3">
                                                <h4 class="font-bold text-gray-900 text-lg flex items-center gap-2">
                                                    <span class="bg-purple-100 text-purple-700 px-3 py-1 rounded-full text-sm">
                                                        {{ $variant['volume'] }} ml
                                                    </span>
                                                </h4>
                                                <label class="flex items-center cursor-pointer">
                                                    <input wire:model="variants.{{ $index }}.is_active" 
                                                        type="checkbox" 
                                                        class="h-4 w-4 text-green-600 focus:ring-green-500 border-gray-300 rounded">
                                                    <span class="ml-2 text-sm font-medium text-gray-700">Activa</span>
                                                </label>
                                            </div>
                                            
                                            <div class="space-y-3">
                                                <div>
                                                    <label class="block text-sm font-medium text-gray-700 mb-1">
                                                        Precio (Gs)
                                                    </label>
                                                    <div class="relative">
                                                        <input wire:model="variants.{{ $index }}.price" 
                                                            type="number" 
                                                            step="1000" 
                                                            min="0"
                                                            placeholder="35000"
                                                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent">
                                                        <span class="absolute right-3 top-2 text-gray-500 text-sm">Gs</span>
                                                    </div>
                                                </div>
                                                
                                                <div>
                                                    <label class="block text-sm font-medium text-gray-700 mb-1">
                                                        Stock
                                                    </label>
                                                    <input wire:model="variants.{{ $index }}.stock" 
                                                        type="number" 
                                                        min="0"
                                                        placeholder="0"
                                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent">
                                                </div>
                                            </div>

                                            @if(!empty($variant['price']) && $variant['price'] > 0)
                                                <div class="mt-3 pt-3 border-t border-gray-200">
                                                    <div class="flex justify-between text-xs">
                                                        <span class="text-gray-600">Precio:</span>
                                                        <span class="font-bold text-green-700">{{ number_format($variant['price'], 0, ',', '.') }} Gs</span>
                                                    </div>
                                                </div>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </div>

                    {{-- Footer del Modal --}}
                    <div class="p-6 border-t border-gray-200 bg-gray-50 rounded-b-lg">
                        <div class="flex justify-end gap-3">
                            <button type="button" 
                                wire:click="closeModal"
                                class="px-6 py-2 border-2 border-gray-300 rounded-lg text-gray-700 hover:bg-gray-100 transition font-medium">
                                Cancelar
                            </button>
                            <button type="submit"
                                class="px-6 py-2 bg-purple-600 hover:bg-purple-700 text-white rounded-lg transition font-medium shadow-lg flex items-center gap-2">
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
                    </div>
                </form>
            </div>
        </div>
    @endif

    {{-- Loading Indicator --}}
    <div wire:loading class="fixed inset-0 bg-black bg-opacity-30 flex items-center justify-center z-50">
        <div class="bg-white rounded-lg p-6 shadow-xl">
            <div class="flex items-center gap-3">
                <svg class="animate-spin h-6 w-6 text-purple-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <span class="text-gray-700 font-medium">Procesando...</span>
            </div>
        </div>
    </div>
</div>