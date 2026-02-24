<div>
    {{-- Notificaciones --}}
    <div x-data="{ notifications: [] }"
         x-on:show-notification.window="
            notifications.push($event.detail[0] || $event.detail);
            setTimeout(() => notifications.shift(), 3000);
         "
         class="fixed top-4 right-4 z-50 space-y-2">
        <template x-for="(n, i) in notifications" :key="i">
            <div x-transition
                 :class="{ 'bg-green-500': n.type==='success', 'bg-red-500': n.type==='error' }"
                 class="text-white px-4 py-2 rounded-lg shadow-lg font-bold text-sm">
                <span x-text="n.message"></span>
            </div>
        </template>
    </div>

    <div class="space-y-6">

        {{-- ══ HEADER ══ --}}
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <h1 class="text-2xl font-black text-gray-900">💸 Egresos</h1>
                @if($openRegister)
                    <p class="text-sm text-green-600 font-semibold mt-1">
                        🟢 Caja abierta · los nuevos egresos se registran en esta caja
                    </p>
                @else
                    <p class="text-sm text-amber-600 font-semibold mt-1">
                        🟡 Sin caja abierta · los egresos se registrarán sin caja
                    </p>
                @endif
            </div>
            <button wire:click="openModal"
                class="bg-red-500 hover:bg-red-600 active:bg-red-700 text-white font-black px-6 py-3 rounded-xl transition-all shadow-md">
                + Registrar Egreso
            </button>
        </div>

        {{-- ══ STATS ══
             BUG 5 FIX: Agregada card de total BRL cuando hay egresos en R$.
        ══ --}}
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <div class="bg-white rounded-xl shadow p-4 text-center col-span-2 md:col-span-1">
                <div class="text-2xl font-black text-red-500">{{ number_format($stats['total'], 0, ',', '.') }}</div>
                <div class="text-sm text-gray-500 mt-1">Gs total egresos</div>
                <div class="text-xs text-gray-400">{{ $stats['count'] }} registros</div>
            </div>
            <div class="bg-white rounded-xl shadow p-4 text-center">
                <div class="text-lg font-black text-green-600">{{ number_format($stats['purchase'], 0, ',', '.') }}</div>
                <div class="text-xs text-gray-500 mt-1">🛒 Insumos</div>
            </div>
            <div class="bg-white rounded-xl shadow p-4 text-center">
                <div class="text-lg font-black text-orange-500">{{ number_format($stats['operational'], 0, ',', '.') }}</div>
                <div class="text-xs text-gray-500 mt-1">🔧 Operacional</div>
            </div>
            {{-- BUG 5 FIX: Card BRL visible siempre, no solo cuando hay datos --}}
            <div class="bg-blue-50 rounded-xl shadow p-4 text-center border border-blue-200">
                <div class="text-lg font-black text-blue-600">
                    R$ {{ number_format($stats['total_brl'], 2, ',', '.') }}
                </div>
                <div class="text-xs text-blue-500 mt-1">🇧🇷 Total en Reales</div>
                @if($stats['count_brl'] > 0)
                    <div class="text-xs text-blue-400">{{ $stats['count_brl'] }} registros</div>
                @endif
            </div>
        </div>

        {{-- ══ FILTROS ══
             BUG 4 FIX: Agregado select de moneda que existía en el backend pero no en la vista.
        ══ --}}
        <div class="bg-white rounded-xl shadow p-4">
            <div class="grid grid-cols-1 md:grid-cols-5 gap-3">
                <input wire:model.live.debounce.300ms="search" type="text"
                    placeholder="🔍 Buscar descripción..."
                    class="px-4 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-red-400">

                <select wire:model.live="filterType"
                    class="px-4 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-red-400">
                    <option value="">Todos los tipos</option>
                    @foreach($types as $key => $typeInfo)
                        <option value="{{ $key }}">{{ $typeInfo['label'] }}</option>
                    @endforeach
                </select>

                {{-- BUG 4 FIX: Selector de moneda ahora visible --}}
                <select wire:model.live="filterCurrency"
                    class="px-4 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-red-400">
                    <option value="">Todas las monedas</option>
                    <option value="gs">🪙 Solo Guaraníes</option>
                    <option value="brl">🇧🇷 Solo Reales</option>
                </select>

                <input wire:model.live="filterDateFrom" type="date"
                    class="px-4 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-red-400">

                <input wire:model.live="filterDateTo" type="date"
                    class="px-4 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-red-400">
            </div>
            <div class="flex gap-4 mt-3 text-sm">
                <button wire:click="showAllDates" class="text-purple-600 hover:text-purple-700 font-semibold">
                    Ver todos los períodos
                </button>
                @if($filterType || $filterCurrency || $search)
                    <button wire:click="clearFilters" class="text-gray-500 hover:text-gray-700 font-semibold">
                        ✕ Limpiar filtros
                    </button>
                @endif
            </div>
        </div>

        {{-- ══ TABLA ══ --}}
        <div class="bg-white rounded-xl shadow-lg overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gradient-to-r from-red-500 to-rose-600">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-bold text-white uppercase">Fecha</th>
                            <th class="px-4 py-3 text-left text-xs font-bold text-white uppercase">Tipo</th>
                            <th class="px-4 py-3 text-left text-xs font-bold text-white uppercase">Descripción</th>
                            <th class="px-4 py-3 text-left text-xs font-bold text-white uppercase">Pago</th>
                            <th class="px-4 py-3 text-right text-xs font-bold text-white uppercase">Monto</th>
                            <th class="px-4 py-3 text-left text-xs font-bold text-white uppercase">Caja</th>
                            <th class="px-4 py-3 text-left text-xs font-bold text-white uppercase">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($expenses as $expense)
                            @php
                                $typeInfo = $types[$expense->type] ?? ['label' => $expense->type, 'color' => 'gray'];
                                $colors   = [
                                    'orange' => 'bg-orange-100 text-orange-800',
                                    'green'  => 'bg-green-100 text-green-800',
                                    'yellow' => 'bg-yellow-100 text-yellow-800',
                                    'blue'   => 'bg-blue-100 text-blue-800',
                                    'gray'   => 'bg-gray-100 text-gray-800',
                                ];
                                $badgeCls = $colors[$typeInfo['color']] ?? $colors['gray'];
                                $isBrl    = ($expense->currency ?? 'gs') === 'brl';
                            @endphp
                            {{-- BUG 3 FIX: fila con tinte azul si es BRL --}}
                            <tr class="hover:bg-gray-50 transition-colors {{ $isBrl ? 'bg-blue-50/40' : '' }}">
                                <td class="px-4 py-3 whitespace-nowrap">
                                    <div class="text-sm font-semibold text-gray-900">
                                        {{ $expense->expense_date->format('d/m/Y') }}
                                    </div>
                                    <div class="text-xs text-gray-400">{{ $expense->expense_date->format('H:i') }}</div>
                                </td>
                                <td class="px-4 py-3">
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-bold {{ $badgeCls }}">
                                        {{ $typeInfo['label'] }}
                                    </span>
                                </td>
                                <td class="px-4 py-3">
                                    <div class="text-sm text-gray-900">{{ $expense->description }}</div>
                                    @if($expense->notes)
                                        <div class="text-xs text-gray-400 mt-0.5">{{ $expense->notes }}</div>
                                    @endif
                                    @if($expense->registeredBy)
                                        <div class="text-xs text-purple-500 mt-0.5">por {{ $expense->registeredBy->name }}</div>
                                    @endif
                                </td>
                                <td class="px-4 py-3">
                                    <span class="text-sm {{ $expense->payment_method === 'cash' ? 'text-green-700' : 'text-blue-700' }}">
                                        @if($expense->payment_method === 'cash') 💵 Efectivo
                                        @elseif($expense->payment_method === 'card') 💳 Tarjeta
                                        @else 🏦 Transfer
                                        @endif
                                    </span>
                                </td>
                                {{-- BUG 3 FIX: mostrar monto según currency, no siempre en Gs --}}
                                <td class="px-4 py-3 text-right">
                                    @if($isBrl)
                                        <span class="text-lg font-black text-blue-600">
                                            R$ {{ number_format((float) $expense->amount_brl, 2, ',', '.') }}
                                        </span>
                                        <div class="text-xs text-blue-400">🇧🇷 Reales</div>
                                    @else
                                        <span class="text-lg font-black text-red-500">
                                            {{ number_format((float) $expense->amount, 0, ',', '.') }} Gs
                                        </span>
                                    @endif
                                </td>
                                <td class="px-4 py-3">
                                    @if($expense->cashRegister)
                                        <span class="text-xs {{ $expense->cashRegister->status === 'open' ? 'text-green-600' : 'text-gray-500' }}">
                                            {{ $expense->cashRegister->status === 'open' ? '🟢' : '⚫' }}
                                            {{ $expense->cashRegister->opened_at->format('d/m H:i') }}
                                        </span>
                                    @else
                                        <span class="text-xs text-gray-400">Sin caja</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3">
                                    <div class="flex gap-2">
                                        <button wire:click="editExpense({{ $expense->id }})"
                                            class="bg-blue-100 hover:bg-blue-200 text-blue-700 font-bold px-3 py-1.5 rounded-lg text-xs transition-all">
                                            ✏️
                                        </button>
                                        @if(!($expense->cashRegister?->status === 'closed'))
                                            <button
                                                onclick="if(confirm('¿Eliminar este egreso?')) @this.call('delete', {{ $expense->id }})"
                                                class="bg-red-100 hover:bg-red-200 text-red-700 font-bold px-3 py-1.5 rounded-lg text-xs transition-all">
                                                🗑️
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-6 py-12 text-center text-gray-400">
                                    <div class="text-5xl mb-3">💸</div>
                                    <div class="font-bold">No hay egresos en el período seleccionado.</div>
                                    <button wire:click="showAllDates" class="mt-2 text-purple-600 text-sm hover:underline">
                                        Ver todos los períodos
                                    </button>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($expenses->hasPages())
                <div class="px-6 py-4 bg-gray-50 border-t">
                    {{ $expenses->links() }}
                </div>
            @endif
        </div>
    </div>

    {{-- ══ MODAL NUEVO / EDITAR EGRESO ══

         BUG 1 FIX: Agregado selector de moneda (Gs / R$) con inputs dinámicos.
         BUG 2 FIX: wire:model.live en paymentMethod y currency para que el
                    estilo condicional de PHP se actualice al hacer click.
                    Sin .live, Livewire no re-renderiza y los radio buttons
                    quedan visualmente bloqueados en el valor inicial.
    ══ --}}
    @if($showModal)
        <div class="fixed inset-0 bg-black/60 backdrop-blur-sm flex items-center justify-center p-4 z-50"
             x-on:keydown.escape.window="$wire.closeModal()">
            <div class="bg-white rounded-2xl w-full max-w-md shadow-2xl overflow-hidden flex flex-col max-h-[90vh]">

                {{-- Header fijo --}}
                <div class="bg-gradient-to-r from-red-500 to-rose-600 px-5 py-4 text-white flex-shrink-0">
                    <h2 class="text-lg font-black">
                        {{ $editingId ? '✏️ Editar Egreso' : '💸 Registrar Egreso' }}
                    </h2>
                    @if($openRegister)
                        <p class="text-red-100 text-xs mt-0.5">Se registrará en la caja abierta</p>
                    @else
                        <p class="text-yellow-200 text-xs mt-0.5">⚠️ Sin caja abierta</p>
                    @endif
                </div>

                {{-- Cuerpo con scroll --}}
                <div class="overflow-y-auto flex-1 p-5 space-y-4">

                    {{-- Tipo --}}
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Tipo de egreso</label>
                        <div class="grid grid-cols-2 gap-2">
                            @foreach($types as $key => $typeData)
                                <label class="flex items-center gap-2 px-3 py-2.5 rounded-xl border-2 cursor-pointer transition-all
                                    {{ $type === $key
                                        ? 'border-red-400 bg-red-50 text-red-700'
                                        : 'border-gray-200 text-gray-600 hover:border-red-200 hover:bg-red-50/40' }}">
                                    <input wire:model.live="type" type="radio" value="{{ $key }}" class="sr-only">
                                    <span class="text-sm font-semibold leading-tight">{{ $typeData['label'] }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>

                    {{-- Descripción + sugerencias --}}
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">
                            Descripción <span class="text-red-500">*</span>
                        </label>
                        <input wire:model="description" type="text"
                            class="w-full px-3 py-2.5 border-2 border-gray-300 rounded-xl focus:ring-2 focus:ring-red-400 focus:border-red-400 text-sm"
                            placeholder="¿En qué se gastó?">
                        @error('description') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror

                        @if($type && array_key_exists($type, $types) && !empty($types[$type]['examples']))
                            <div class="flex flex-wrap gap-1.5 mt-2">
                                @foreach($types[$type]['examples'] as $example)
                                    <button wire:click="$set('description', '{{ $example }}')" type="button"
                                        class="text-xs px-2.5 py-1 bg-gray-100 hover:bg-red-100 hover:text-red-700 rounded-lg transition-all border border-gray-200 font-medium">
                                        {{ $example }}
                                    </button>
                                @endforeach
                            </div>
                        @endif
                    </div>

                    {{-- ══ MONEDA + MONTO ══
                         BUG 1 FIX COMPLETO: Selector de moneda con inputs dinámicos.
                         - wire:model.live="currency" → Livewire re-renderiza al cambiar
                           para mostrar el input correcto y ocultar el otro.
                         - updatedCurrency() en el Livewire resetea el monto opuesto.
                    ══ --}}
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">
                            Moneda y Monto <span class="text-red-500">*</span>
                        </label>

                        {{-- Selector de moneda --}}
                        <div class="grid grid-cols-2 gap-2 mb-3">
                            <label class="flex items-center justify-center gap-2 py-2.5 rounded-xl border-2 cursor-pointer transition-all font-bold text-sm
                                {{ $currency === 'gs'
                                    ? 'border-red-400 bg-red-50 text-red-700'
                                    : 'border-gray-200 text-gray-500 hover:border-gray-300' }}">
                                <input wire:model.live="currency" type="radio" value="gs" class="sr-only">
                                🪙 Guaraníes
                            </label>
                            <label class="flex items-center justify-center gap-2 py-2.5 rounded-xl border-2 cursor-pointer transition-all font-bold text-sm
                                {{ $currency === 'brl'
                                    ? 'border-blue-400 bg-blue-50 text-blue-700'
                                    : 'border-gray-200 text-gray-500 hover:border-gray-300' }}">
                                <input wire:model.live="currency" type="radio" value="brl" class="sr-only">
                                🇧🇷 Reales (R$)
                            </label>
                        </div>

                        {{-- Input Guaraníes --}}
                        @if($currency === 'gs')
                            <div class="relative">
                                <input wire:model="amount" type="number" min="1" step="1000"
                                    class="w-full px-4 py-3 text-2xl font-black text-center border-2 border-gray-300 rounded-xl focus:ring-2 focus:ring-red-400 focus:border-red-400"
                                    placeholder="0">
                                <span class="absolute right-4 top-1/2 -translate-y-1/2 text-gray-400 font-semibold text-sm">Gs</span>
                            </div>
                            @error('amount') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                            <div class="grid grid-cols-4 gap-1.5 mt-2">
                                @foreach([50000, 100000, 200000, 500000] as $quickAmount)
                                    <button wire:click="$set('amount', {{ $quickAmount }})" type="button"
                                        class="text-xs py-1.5 font-bold rounded-lg border-2 border-gray-200 hover:border-red-300 hover:bg-red-50 transition-all text-gray-700">
                                        {{ number_format($quickAmount / 1000, 0) }}k
                                    </button>
                                @endforeach
                            </div>
                        @endif

                        {{-- Input Reales --}}
                        @if($currency === 'brl')
                            <div class="relative">
                                <input wire:model="amountBrl" type="number" min="0.01" step="0.01"
                                    class="w-full px-4 py-3 text-2xl font-black text-center border-2 border-blue-300 rounded-xl focus:ring-2 focus:ring-blue-400 focus:border-blue-400"
                                    placeholder="0,00">
                                <span class="absolute right-4 top-1/2 -translate-y-1/2 text-blue-400 font-semibold text-sm">R$</span>
                            </div>
                            @error('amountBrl') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                            <div class="grid grid-cols-4 gap-1.5 mt-2">
                                @foreach([20, 50, 100, 200] as $quickBrl)
                                    <button wire:click="$set('amountBrl', {{ $quickBrl }})" type="button"
                                        class="text-xs py-1.5 font-bold rounded-lg border-2 border-blue-200 hover:border-blue-400 hover:bg-blue-50 transition-all text-blue-700">
                                        R$ {{ $quickBrl }}
                                    </button>
                                @endforeach
                            </div>
                            <p class="text-xs text-blue-500 mt-2">
                                💡 Este egreso se descontará del cajón de reales al cerrar la caja.
                            </p>
                        @endif
                    </div>

                    {{-- ══ MÉTODO DE PAGO ══
                         BUG 2 FIX: wire:model.live en lugar de wire:model.
                         Sin .live, Livewire no re-renderiza al cambiar el radio,
                         por lo que el estilo condicional de PHP nunca se actualiza
                         y el botón activo visualmente queda fijo en "Efectivo".
                    ══ --}}
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Pagado con</label>
                        <div class="grid grid-cols-3 gap-2">
                            @foreach(['cash' => '💵 Efectivo', 'card' => '💳 Tarjeta', 'transfer' => '🏦 Transfer'] as $key => $label)
                                <label class="flex items-center justify-center p-2.5 rounded-xl border-2 cursor-pointer transition-all text-xs font-bold
                                    {{ $paymentMethod === $key
                                        ? 'border-red-400 bg-red-50 text-red-700'
                                        : 'border-gray-200 text-gray-600 hover:border-red-200' }}">
                                    {{-- BUG 2 FIX: .live agregado --}}
                                    <input wire:model.live="paymentMethod" type="radio" value="{{ $key }}" class="sr-only">
                                    {{ $label }}
                                </label>
                            @endforeach
                        </div>
                    </div>

                    {{-- Notas opcionales --}}
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Notas (opcional)</label>
                        <input wire:model="notes" type="text"
                            class="w-full px-3 py-2.5 border-2 border-gray-300 rounded-xl focus:ring-2 focus:ring-red-400 text-sm"
                            placeholder="Observaciones adicionales...">
                    </div>
                </div>

                {{-- Footer fijo --}}
                <div class="px-5 py-4 bg-gray-50 border-t flex gap-3 flex-shrink-0">
                    <button wire:click="closeModal"
                        class="flex-1 bg-gray-200 hover:bg-gray-300 text-gray-700 font-bold py-2.5 rounded-xl transition-all text-sm">
                        Cancelar
                    </button>
                    <button wire:click="save" wire:loading.attr="disabled"
                        class="flex-1 bg-red-500 hover:bg-red-600 text-white font-bold py-2.5 rounded-xl transition-all disabled:opacity-50 text-sm">
                        <span wire:loading.remove wire:target="save">✓ {{ $editingId ? 'Actualizar' : 'Registrar' }}</span>
                        <span wire:loading wire:target="save">Guardando...</span>
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>