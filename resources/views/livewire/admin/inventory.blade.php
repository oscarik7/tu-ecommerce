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

    {{-- ── TARJETAS RESUMEN ── --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-xl shadow p-4 flex items-center gap-3">
            <div class="bg-purple-100 rounded-full p-3 text-2xl">🥤</div>
            <div>
                <p class="text-xs text-gray-500 font-medium">Tipos de vasito</p>
                <p class="text-2xl font-bold text-gray-900">{{ $stats['total_types'] }}</p>
            </div>
        </div>
        <div class="bg-white rounded-xl shadow p-4 flex items-center gap-3">
            <div class="bg-blue-100 rounded-full p-3 text-2xl">📦</div>
            <div>
                <p class="text-xs text-gray-500 font-medium">Total en stock</p>
                <p class="text-2xl font-bold text-gray-900">{{ number_format($stats['total_vasitos']) }}</p>
            </div>
        </div>
        <div class="bg-white rounded-xl shadow p-4 flex items-center gap-3">
            <div class="bg-yellow-100 rounded-full p-3 text-2xl">⚠️</div>
            <div>
                <p class="text-xs text-gray-500 font-medium">Stock bajo</p>
                <p class="text-2xl font-bold {{ $stats['low_stock'] > 0 ? 'text-yellow-600' : 'text-gray-900' }}">
                    {{ $stats['low_stock'] }}
                </p>
            </div>
        </div>
        <div class="bg-white rounded-xl shadow p-4 flex items-center gap-3">
            <div class="bg-red-100 rounded-full p-3 text-2xl">🚫</div>
            <div>
                <p class="text-xs text-gray-500 font-medium">Sin stock</p>
                <p class="text-2xl font-bold {{ $stats['out_of_stock'] > 0 ? 'text-red-600' : 'text-gray-900' }}">
                    {{ $stats['out_of_stock'] }}
                </p>
            </div>
        </div>
    </div>

    {{-- ── GRID DE VASITOS ── --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 mb-8">
        @foreach($cupSizes as $cup)
            @php
                $pct      = $cup->stock_min > 0 ? min(100, ($cup->stock / ($cup->stock_min * 5)) * 100) : 100;
                $barColor = $cup->stock === 0 ? 'bg-red-500' : ($cup->is_low_stock ? 'bg-yellow-400' : 'bg-green-500');
                $cardBorder = $cup->stock === 0 ? 'border-red-300 bg-red-50' : ($cup->is_low_stock ? 'border-yellow-300 bg-yellow-50' : 'border-gray-200 bg-white');
            @endphp
            <div class="rounded-xl border-2 {{ $cardBorder }} shadow-sm hover:shadow-md transition p-5">

                {{-- Header --}}
                <div class="flex items-start justify-between mb-3">
                    <div>
                        <h3 class="text-xl font-bold text-gray-900">{{ $cup->name }}</h3>
                        <p class="text-xs text-gray-500">{{ number_format($cup->volume_ml) }}ml</p>
                    </div>
                    <div class="flex items-center gap-2">
                        {{-- Badge estado --}}
                        @if($cup->stock === 0)
                            <span class="text-xs bg-red-100 text-red-700 px-2 py-1 rounded-full font-bold">Sin stock</span>
                        @elseif($cup->is_low_stock)
                            <span class="text-xs bg-yellow-100 text-yellow-700 px-2 py-1 rounded-full font-bold">Stock bajo</span>
                        @else
                            <span class="text-xs bg-green-100 text-green-700 px-2 py-1 rounded-full font-bold">OK</span>
                        @endif

                        {{-- Toggle activo --}}
                        <button wire:click="toggleActive({{ $cup->id }})"
                            class="text-xs px-2 py-1 rounded-full transition {{ $cup->is_active ? 'bg-gray-100 text-gray-600 hover:bg-gray-200' : 'bg-orange-100 text-orange-600 hover:bg-orange-200' }}">
                            {{ $cup->is_active ? '✓ Activo' : '✗ Inactivo' }}
                        </button>
                    </div>
                </div>

                {{-- Stock actual --}}
                <div class="text-center my-4">
                    <span class="text-5xl font-black text-gray-900">{{ $cup->stock }}</span>
                    <span class="text-lg text-gray-500 ml-1">vasitos</span>
                </div>

                {{-- Barra de progreso --}}
                <div class="mb-3">
                    <div class="flex justify-between text-xs text-gray-500 mb-1">
                        <span>0</span>
                        <span>Mínimo: {{ $cup->stock_min }}</span>
                    </div>
                    <div class="w-full bg-gray-200 rounded-full h-3 overflow-hidden">
                        <div class="{{ $barColor }} h-3 rounded-full transition-all duration-500"
                            style="width: {{ $pct }}%"></div>
                    </div>
                </div>

                {{-- Stock mínimo editable --}}
                <div class="flex items-center gap-2 mb-4 text-sm">
                    <span class="text-gray-500 text-xs">Alerta en:</span>
                    <input type="number" min="0" value="{{ $cup->stock_min }}"
                        wire:change="updateMinStock({{ $cup->id }}, $event.target.value)"
                        class="w-20 px-2 py-1 border border-gray-300 rounded-lg text-xs text-center focus:ring-2 focus:ring-purple-400">
                    <span class="text-xs text-gray-400">unidades</span>
                </div>

                {{-- Botones de acción --}}
                <div class="grid grid-cols-3 gap-2">
                    <button wire:click="openAdjust({{ $cup->id }}, 'add')"
                        class="flex flex-col items-center gap-1 bg-green-500 hover:bg-green-600 text-white px-3 py-2 rounded-lg transition text-xs font-bold">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        Entrada
                    </button>

                    <button wire:click="openAdjust({{ $cup->id }}, 'subtract')"
                        {{ $cup->stock === 0 ? 'disabled' : '' }}
                        class="flex flex-col items-center gap-1 bg-red-500 hover:bg-red-600 text-white px-3 py-2 rounded-lg transition text-xs font-bold disabled:opacity-50 disabled:cursor-not-allowed">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"/>
                        </svg>
                        Salida
                    </button>

                    <button wire:click="openAdjust({{ $cup->id }}, 'set')"
                        class="flex flex-col items-center gap-1 bg-blue-500 hover:bg-blue-600 text-white px-3 py-2 rounded-lg transition text-xs font-bold">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                        </svg>
                        Ajustar
                    </button>
                </div>
            </div>
        @endforeach
    </div>

    {{-- ── HISTORIAL DE COMPRAS ── --}}
    <div class="bg-white rounded-xl shadow overflow-hidden">
        <div class="p-4 border-b border-gray-200 flex items-center justify-between">
            <h3 class="font-bold text-gray-900 flex items-center gap-2">
                <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                </svg>
                Últimas compras de vasitos
            </h3>
            <span class="text-xs text-gray-500">Últimas 20 entradas</span>
        </div>

        @if($recentExpenses->count() > 0)
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 text-sm">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Fecha</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Descripción</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Registrado por</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Monto</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($recentExpenses as $expense)
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-3 whitespace-nowrap text-gray-600">
                                    {{ $expense->expense_date->format('d/m/Y H:i') }}
                                </td>
                                <td class="px-4 py-3 text-gray-800">
                                    {{ $expense->description }}
                                    @if($expense->notes)
                                        <p class="text-xs text-gray-400">{{ $expense->notes }}</p>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-gray-600">
                                    {{ $expense->registeredBy?->name ?? '—' }}
                                </td>
                                <td class="px-4 py-3 text-right font-bold text-gray-900 whitespace-nowrap">
                                    {{ number_format($expense->amount, 0, ',', '.') }} Gs
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="p-8 text-center text-gray-400">
                <span class="text-4xl block mb-2">📋</span>
                <p>No hay compras de vasitos registradas aún.</p>
            </div>
        @endif
    </div>

    {{-- ══════════════════ MODAL DE AJUSTE ══════════════════ --}}
    @if($showAdjustModal && $selectedCupSize)
        <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50"
            wire:click="closeAdjust">
            <div class="bg-white rounded-2xl max-w-md w-full shadow-2xl" wire:click.stop>

                {{-- Header --}}
                @php
                    $modalColors = [
                        'add'      => ['bg' => 'from-green-500 to-teal-600',  'label' => '📥 Entrada de Stock'],
                        'subtract' => ['bg' => 'from-red-500 to-rose-600',    'label' => '📤 Salida de Stock'],
                        'set'      => ['bg' => 'from-blue-500 to-indigo-600', 'label' => '✏️ Ajuste de Stock'],
                    ];
                    $mc = $modalColors[$adjustType];
                @endphp
                <div class="p-5 bg-gradient-to-r {{ $mc['bg'] }} rounded-t-2xl">
                    <div class="flex justify-between items-center">
                        <div>
                            <h2 class="text-xl font-bold text-white">{{ $mc['label'] }}</h2>
                            <p class="text-white/80 text-sm mt-0.5">
                                Vasito {{ $selectedCupSize->name }} · Stock actual:
                                <strong>{{ $selectedCupSize->stock }}</strong> unidades
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

                    {{-- Tipo de ajuste (pestañas) --}}
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
                            @if($adjustType === 'set')
                                Nuevo stock total
                            @elseif($adjustType === 'add')
                                Cantidad a agregar
                            @else
                                Cantidad a descontar
                            @endif
                            <span class="text-red-500">*</span>
                        </label>
                        <input wire:model.live="adjustQty" type="number" min="1" autofocus
                            placeholder="{{ $adjustType === 'set' ? 'Ej: 50' : 'Ej: 24' }}"
                            class="w-full px-4 py-3 text-2xl font-bold text-center border-2 border-gray-300 rounded-xl focus:ring-2 focus:ring-purple-400 focus:border-purple-400">
                        @error('adjustQty') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror

                        {{-- Preview del resultado --}}
                        @if($adjustQty && $adjustQty > 0)
                            @php
                                $preview = match($adjustType) {
                                    'add'      => $selectedCupSize->stock + (int)$adjustQty,
                                    'subtract' => max(0, $selectedCupSize->stock - (int)$adjustQty),
                                    'set'      => (int)$adjustQty,
                                };
                                $previewColor = $preview === 0 ? 'text-red-600' : ($preview <= $selectedCupSize->stock_min ? 'text-yellow-600' : 'text-green-700');
                            @endphp
                            <div class="mt-2 text-center text-sm text-gray-500">
                                {{ $selectedCupSize->stock }} →
                                <span class="font-bold text-lg {{ $previewColor }}">{{ $preview }}</span>
                                vasitos
                            </div>
                        @endif
                    </div>

                    {{-- Motivo --}}
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-1">
                            Motivo <span class="text-red-500">*</span>
                        </label>
                        <select wire:model="adjustReason"
                            class="w-full px-4 py-2 border-2 border-gray-300 rounded-xl focus:ring-2 focus:ring-purple-400 focus:border-purple-400 mb-2">
                            <option value="">— Seleccioná un motivo —</option>
                            @foreach($adjustType === 'add' ? $reasonsAdd : $reasonsSubtract as $reason)
                                <option value="{{ $reason }}">{{ $reason }}</option>
                            @endforeach
                        </select>
                        {{-- Campo libre si elige "Otro" --}}
                        @if($adjustReason === 'Otro' || (!in_array($adjustReason, $adjustType === 'add' ? $reasonsAdd : $reasonsSubtract) && $adjustReason))
                            <input wire:model="adjustReason" type="text"
                                placeholder="Describí el motivo..."
                                class="w-full px-4 py-2 border-2 border-gray-300 rounded-xl focus:ring-2 focus:ring-purple-400 focus:border-purple-400">
                        @endif
                        @error('adjustReason') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    {{-- Registrar costo (solo en entradas) --}}
                    @if($adjustType === 'add')
                        <div class="bg-gray-50 rounded-xl p-4 border border-gray-200">
                            <label class="flex items-center gap-3 cursor-pointer mb-3">
                                <input wire:model.live="registerExpense" type="checkbox"
                                    class="h-5 w-5 text-purple-600 border-gray-300 rounded">
                                <div>
                                    <span class="text-sm font-bold text-gray-700">Registrar como gasto</span>
                                    <p class="text-xs text-gray-500">Se agregará a los gastos de inventario</p>
                                </div>
                            </label>

                            @if($registerExpense)
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">
                                        Costo de la compra <span class="text-red-500">*</span>
                                    </label>
                                    <div class="relative">
                                        <input wire:model="adjustCost" type="number" step="1000" min="1"
                                            placeholder="150000"
                                            class="w-full px-4 py-2 border-2 border-green-300 rounded-xl focus:ring-2 focus:ring-green-400">
                                        <span class="absolute right-3 top-2.5 text-sm text-gray-500 font-medium">Gs</span>
                                    </div>
                                    @error('adjustCost') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                                </div>
                            @endif
                        </div>
                    @endif

                </div>

                {{-- Footer --}}
                <div class="px-6 pb-6 flex gap-3">
                    <button type="button" wire:click="closeAdjust"
                        class="flex-1 py-3 border-2 border-gray-300 rounded-xl text-gray-700 hover:bg-gray-50 transition font-medium">
                        Cancelar
                    </button>
                    <button wire:click="saveAdjust"
                        class="flex-1 py-3 font-bold text-white rounded-xl transition
                            {{ $adjustType === 'add' ? 'bg-green-500 hover:bg-green-600' :
                               ($adjustType === 'subtract' ? 'bg-red-500 hover:bg-red-600' : 'bg-blue-500 hover:bg-blue-600') }}">
                        @if($adjustType === 'add')      Confirmar Entrada
                        @elseif($adjustType === 'subtract') Confirmar Salida
                        @else                               Guardar Ajuste
                        @endif
                    </button>
                </div>
            </div>
        </div>
    @endif

    {{-- Loading --}}
    <div wire:loading.delay class="fixed inset-0 bg-black bg-opacity-30 flex items-center justify-center z-50">
        <div class="bg-white rounded-xl p-5 shadow-xl flex items-center gap-3">
            <svg class="animate-spin h-6 w-6 text-purple-600" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"/>
            </svg>
            <span class="text-gray-700 font-medium">Procesando...</span>
        </div>
    </div>
</div>