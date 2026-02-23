<div>
    {{-- Flash messages --}}
    @if(session()->has('message'))
        <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 4000)"
            class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg mb-4 flex items-center justify-between">
            <span>{{ session('message') }}</span>
            <button @click="show = false"><svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/></svg></button>
        </div>
    @endif

    @if(session()->has('error'))
        <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)"
            class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg mb-4 flex items-center justify-between">
            <span>{{ session('error') }}</span>
            <button @click="show = false"><svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/></svg></button>
        </div>
    @endif

    {{-- Estadísticas --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-gradient-to-br from-purple-500 to-indigo-600 rounded-xl shadow-lg p-4 text-white">
            <div class="flex items-center gap-3">
                <div class="bg-white/20 rounded-full p-3 text-2xl">📦</div>
                <div>
                    <p class="text-xs text-purple-100 font-medium">Total Ítems</p>
                    <p class="text-3xl font-black">{{ $stats['total_items'] }}</p>
                </div>
            </div>
        </div>

        <div class="bg-gradient-to-br from-blue-500 to-cyan-600 rounded-xl shadow-lg p-4 text-white">
            <div class="flex items-center gap-3">
                <div class="bg-white/20 rounded-full p-3 text-2xl">🏪</div>
                <div>
                    <p class="text-xs text-blue-100 font-medium">Unidades Total</p>
                    <p class="text-3xl font-black">{{ number_format($stats['total_units']) }}</p>
                </div>
            </div>
        </div>

        <div class="bg-gradient-to-br from-yellow-500 to-orange-600 rounded-xl shadow-lg p-4 text-white">
            <div class="flex items-center gap-3">
                <div class="bg-white/20 rounded-full p-3 text-2xl">⚠️</div>
                <div>
                    <p class="text-xs text-yellow-100 font-medium">Stock Bajo</p>
                    <p class="text-3xl font-black {{ $stats['low_stock'] > 0 ? 'animate-pulse' : '' }}">
                        {{ $stats['low_stock'] }}
                    </p>
                </div>
            </div>
        </div>

        <div class="bg-gradient-to-br from-red-500 to-rose-600 rounded-xl shadow-lg p-4 text-white">
            <div class="flex items-center gap-3">
                <div class="bg-white/20 rounded-full p-3 text-2xl">🚫</div>
                <div>
                    <p class="text-xs text-red-100 font-medium">Sin Stock</p>
                    <p class="text-3xl font-black {{ $stats['out_of_stock'] > 0 ? 'animate-pulse' : '' }}">
                        {{ $stats['out_of_stock'] }}
                    </p>
                </div>
            </div>
        </div>
    </div>

    {{-- Filtros --}}
    <div class="bg-white rounded-xl shadow-lg p-4 mb-6">
        <div class="grid grid-cols-1 md:grid-cols-5 gap-3">
            <div class="md:col-span-2">
                <input wire:model.live.debounce.300ms="search" type="text"
                    placeholder="🔍 Buscar productos o vasitos..."
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500">
            </div>

            <select wire:model.live="filterType"
                class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500">
                <option value="">Todos los tipos</option>
                <option value="cups">🥤 Solo Vasitos</option>
                <option value="simple_products">🍹 Solo Productos</option>
            </select>

            <select wire:model.live="filterCategory"
                class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500">
                <option value="">Todas las categorías</option>
                @foreach($categories as $category)
                    <option value="{{ $category->id }}">{{ $category->name }}</option>
                @endforeach
            </select>

            <select wire:model.live="filterStock"
                class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500">
                <option value="">Todo el stock</option>
                <option value="low">⚠️ Stock bajo</option>
                <option value="out">🚫 Sin stock</option>
            </select>
        </div>
    </div>

    {{-- Tabla de Inventario --}}
    <div class="bg-white rounded-xl shadow-lg overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gradient-to-r from-purple-600 to-indigo-600">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-bold text-white uppercase tracking-wider">Producto</th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-white uppercase tracking-wider">Categoría</th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-white uppercase tracking-wider">Tamaño</th>
                        <th class="px-6 py-4 text-center text-xs font-bold text-white uppercase tracking-wider">Stock</th>
                        <th class="px-6 py-4 text-center text-xs font-bold text-white uppercase tracking-wider">Stock Mín.</th>
                        <th class="px-6 py-4 text-center text-xs font-bold text-white uppercase tracking-wider">Estado</th>
                        <th class="px-6 py-4 text-center text-xs font-bold text-white uppercase tracking-wider">Acciones</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-100">
                    @forelse($items as $item)
                        @php
                            $rowBg = $item['stock'] === 0
                                ? 'bg-red-50'
                                : ($item['is_low'] ? 'bg-yellow-50' : 'bg-white');
                        @endphp
                        <tr class="{{ $rowBg }} hover:bg-purple-50 transition">
                            {{-- Producto --}}
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="flex-shrink-0">
                                        @if($item['type'] === 'cup')
                                            <div class="w-10 h-10 bg-gradient-to-br from-purple-400 to-indigo-500 rounded-full flex items-center justify-center">
                                                <span class="text-xl">🥤</span>
                                            </div>
                                        @else
                                            <div class="w-10 h-10 bg-gradient-to-br from-blue-400 to-cyan-500 rounded-full flex items-center justify-center">
                                                <span class="text-xl">🍹</span>
                                            </div>
                                        @endif
                                    </div>
                                    <div>
                                        <div class="text-sm font-bold text-gray-900">{{ $item['name'] }}</div>
                                        @if($item['type'] === 'cup')
                                            <span class="text-xs bg-purple-100 text-purple-700 px-2 py-0.5 rounded-full font-medium">Vasito</span>
                                        @else
                                            <span class="text-xs bg-blue-100 text-blue-700 px-2 py-0.5 rounded-full font-medium">Producto</span>
                                        @endif
                                    </div>
                                </div>
                            </td>

                            {{-- Categoría --}}
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                    {{ $item['category'] }}
                                </span>
                            </td>

                            {{-- Tamaño --}}
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="text-sm font-medium text-gray-900">{{ $item['volume'] }}</span>
                            </td>

                            {{-- Stock --}}
                            <td class="px-6 py-4 text-center">
                                <div class="flex flex-col items-center">
                                    <span class="text-2xl font-black {{ $item['stock'] === 0 ? 'text-red-600' : ($item['is_low'] ? 'text-yellow-600' : 'text-green-600') }}">
                                        {{ $item['stock'] }}
                                    </span>
                                    <span class="text-xs text-gray-500">unidades</span>
                                </div>
                            </td>

                            {{-- Stock Mínimo --}}
                            <td class="px-6 py-4 text-center">
                                <input type="number" min="0" value="{{ $item['stock_min'] }}"
                                    wire:change="updateMinStock('{{ $item['type'] }}', {{ $item['id'] }}, $event.target.value)"
                                    class="w-20 px-2 py-1 border border-gray-300 rounded text-sm text-center focus:ring-2 focus:ring-purple-400">
                            </td>

                            {{-- Estado --}}
                            <td class="px-6 py-4 text-center">
                                @if($item['stock'] === 0)
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-red-100 text-red-800">
                                        🚫 Sin stock
                                    </span>
                                @elseif($item['is_low'])
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-yellow-100 text-yellow-800">
                                        ⚠️ Stock bajo
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-green-100 text-green-800">
                                        ✓ OK
                                    </span>
                                @endif
                            </td>

                            {{-- Acciones --}}
                            <td class="px-6 py-4">
                                <div class="flex items-center justify-center gap-2">
                                    <button wire:click="openAdjust('{{ $item['type'] }}', {{ $item['id'] }}, 'add')"
                                        class="bg-green-500 hover:bg-green-600 text-white px-3 py-1.5 rounded-lg transition text-xs font-bold flex items-center gap-1">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                        </svg>
                                        Entrada
                                    </button>

                                    <button wire:click="openAdjust('{{ $item['type'] }}', {{ $item['id'] }}, 'subtract')"
                                        {{ $item['stock'] === 0 ? 'disabled' : '' }}
                                        class="bg-red-500 hover:bg-red-600 text-white px-3 py-1.5 rounded-lg transition text-xs font-bold flex items-center gap-1 disabled:opacity-50 disabled:cursor-not-allowed">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"/>
                                        </svg>
                                        Salida
                                    </button>

                                    <button wire:click="openAdjust('{{ $item['type'] }}', {{ $item['id'] }}, 'set')"
                                        class="bg-blue-500 hover:bg-blue-600 text-white px-3 py-1.5 rounded-lg transition text-xs font-bold flex items-center gap-1">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                        </svg>
                                        Ajustar
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-12 text-center">
                                <div class="flex flex-col items-center justify-center text-gray-500">
                                    <svg class="w-16 h-16 mb-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
                                    </svg>
                                    <p class="text-lg font-medium">No se encontraron ítems</p>
                                    <p class="text-sm mt-1">Ajustá los filtros de búsqueda</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Paginación manual --}}
    @if($total > $perPage)
        <div class="mt-4 flex justify-between items-center">
            <div class="text-sm text-gray-600">
                Mostrando {{ min($perPage, $total) }} de {{ $total }} ítems
            </div>
            <div class="flex gap-2">
                @for($i = 1; $i <= ceil($total / $perPage); $i++)
                    <a href="?page={{ $i }}"
                        class="px-4 py-2 rounded-lg {{ $currentPage == $i ? 'bg-purple-600 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300' }} transition">
                        {{ $i }}
                    </a>
                @endfor
            </div>
        </div>
    @endif

    {{-- Modal de Ajuste --}}
    @if($showAdjustModal)
        <div class="fixed inset-0 bg-black/50 flex items-center justify-center p-4 z-50" wire:click="closeAdjust">
            <div class="bg-white rounded-2xl max-w-md w-full shadow-2xl" wire:click.stop>

                @php
                    $modalColors = [
                        'add'      => ['bg' => 'from-green-500 to-teal-600',  'label' => '📥 Entrada'],
                        'subtract' => ['bg' => 'from-red-500 to-rose-600',    'label' => '📤 Salida'],
                        'set'      => ['bg' => 'from-blue-500 to-indigo-600', 'label' => '✏️ Ajuste'],
                    ];
                    $mc = $modalColors[$adjustType];
                @endphp

                <div class="p-5 bg-gradient-to-r {{ $mc['bg'] }} rounded-t-2xl">
                    <div class="flex justify-between items-center">
                        <div>
                            <h2 class="text-xl font-bold text-white">{{ $mc['label'] }} de Stock</h2>
                            <p class="text-white/80 text-sm mt-0.5">
                                {{ $adjustItemName }} · Stock actual: <strong>{{ $adjustCurrentStock }}</strong>
                            </p>
                        </div>
                        <button wire:click="closeAdjust" class="text-white/80 hover:text-white">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>
                </div>

                <div class="p-6 space-y-4">
                    {{-- Pestañas --}}
                    <div class="flex rounded-lg overflow-hidden border border-gray-200">
                        <button type="button" wire:click="$set('adjustType', 'add')"
                            class="flex-1 py-2 text-sm font-medium transition {{ $adjustType === 'add' ? 'bg-green-500 text-white' : 'bg-white text-gray-600 hover:bg-gray-50' }}">
                            📥 Entrada
                        </button>
                        <button type="button" wire:click="$set('adjustType', 'subtract')"
                            class="flex-1 py-2 text-sm font-medium transition {{ $adjustType === 'subtract' ? 'bg-red-500 text-white' : 'bg-white text-gray-600 hover:bg-gray-50' }}">
                            📤 Salida
                        </button>
                        <button type="button" wire:click="$set('adjustType', 'set')"
                            class="flex-1 py-2 text-sm font-medium transition {{ $adjustType === 'set' ? 'bg-blue-500 text-white' : 'bg-white text-gray-600 hover:bg-gray-50' }}">
                            ✏️ Ajustar
                        </button>
                    </div>

                    {{-- Cantidad --}}
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-1">
                            @if($adjustType === 'set') Nuevo stock total
                            @elseif($adjustType === 'add') Cantidad a agregar
                            @else Cantidad a descontar
                            @endif
                            <span class="text-red-500">*</span>
                        </label>
                        <input wire:model.live="adjustQty" type="number" min="1" autofocus
                            class="w-full px-4 py-3 text-2xl font-bold text-center border-2 border-gray-300 rounded-xl focus:ring-2 focus:ring-purple-400">
                        @error('adjustQty') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror

                        @if($adjustQty && $adjustQty > 0)
                            @php
                                $preview = match($adjustType) {
                                    'add'      => $adjustCurrentStock + (int)$adjustQty,
                                    'subtract' => max(0, $adjustCurrentStock - (int)$adjustQty),
                                    'set'      => (int)$adjustQty,
                                };
                            @endphp
                            <div class="mt-2 text-center text-sm text-gray-500">
                                {{ $adjustCurrentStock }} → <span class="font-bold text-lg text-purple-600">{{ $preview }}</span> unidades
                            </div>
                        @endif
                    </div>

                    {{-- Motivo --}}
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-1">
                            Motivo <span class="text-red-500">*</span>
                        </label>
                        <select wire:model="adjustReason"
                            class="w-full px-4 py-2 border-2 border-gray-300 rounded-xl focus:ring-2 focus:ring-purple-400 mb-2">
                            <option value="">— Seleccioná un motivo —</option>
                            @foreach($reasons as $reason)
                                <option value="{{ $reason }}">{{ $reason }}</option>
                            @endforeach
                        </select>
                        @if($adjustReason === 'Otro')
                            <input wire:model="adjustReason" type="text" placeholder="Describí el motivo..."
                                class="w-full px-4 py-2 border-2 border-gray-300 rounded-xl focus:ring-2 focus:ring-purple-400">
                        @endif
                        @error('adjustReason') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                    </div>
                </div>

                <div class="px-6 pb-6 flex gap-3">
                    <button type="button" wire:click="closeAdjust"
                        class="flex-1 py-3 border-2 border-gray-300 rounded-xl text-gray-700 hover:bg-gray-50 transition font-medium">
                        Cancelar
                    </button>
                    <button wire:click="saveAdjust"
                        class="flex-1 py-3 font-bold text-white rounded-xl transition
                            {{ $adjustType === 'add' ? 'bg-green-500 hover:bg-green-600' :
                               ($adjustType === 'subtract' ? 'bg-red-500 hover:bg-red-600' : 'bg-blue-500 hover:bg-blue-600') }}">
                        Confirmar
                    </button>
                </div>
            </div>
        </div>
    @endif

    {{-- Loading --}}
    <div wire:loading.delay class="fixed inset-0 bg-black/30 flex items-center justify-center z-50">
        <div class="bg-white rounded-xl p-5 shadow-xl flex items-center gap-3">
            <svg class="animate-spin h-6 w-6 text-purple-600" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"/>
            </svg>
            <span class="text-gray-700 font-medium">Procesando...</span>
        </div>
    </div>
</div>
