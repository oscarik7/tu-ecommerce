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
                 :class="{ 'bg-green-500': n.type==='success', 'bg-red-500': n.type==='error', 'bg-blue-500': n.type==='info' }"
                 class="text-white px-4 py-2 rounded-lg shadow-lg font-bold text-sm">
                <span x-text="n.message"></span>
            </div>
        </template>
    </div>

    <div class="space-y-6">

        {{-- ══ ESTADO ACTUAL DE LA CAJA ══ --}}
        @if($openRegister)
            {{-- CAJA ABIERTA --}}
            <div class="bg-gradient-to-r from-green-500 to-emerald-600 rounded-2xl p-6 text-white shadow-xl">
                <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center gap-4">
                    <div>
                        <div class="flex items-center gap-3 mb-2">
                            <span class="text-3xl">🟢</span>
                            <h2 class="text-2xl font-black">Caja Abierta</h2>
                        </div>
                        <p class="text-green-100">
                            Abierta por <strong>{{ $openRegister->opener->name }}</strong>
                            · {{ $openRegister->opened_at->format('d/m/Y H:i') }}
                            · hace {{ $openRegister->duration }}
                        </p>
                        @if($openRegister->opening_notes)
                            <p class="text-green-200 text-sm mt-1">📝 {{ $openRegister->opening_notes }}</p>
                        @endif
                    </div>
                    <div class="flex gap-4 items-center">
                        <div class="text-center bg-white/20 rounded-xl px-5 py-3">
                            <div class="text-2xl font-black">{{ number_format($openRegister->opening_amount, 0, ',', '.') }} Gs</div>
                            <div class="text-xs text-green-100">Monto inicial</div>
                        </div>
                        <button wire:click="openCloseModal"
                            class="bg-red-500 hover:bg-red-600 active:bg-red-700 text-white font-black px-6 py-3 rounded-xl transition-all shadow-lg text-lg">
                            🔒 Cerrar Caja
                        </button>
                    </div>
                </div>

                {{-- Ventas en tiempo real --}}
                @php
                    $liveOrders = $openRegister->orders()->where('status', '!=', 'cancelled')->where('payment_status', 'paid');
                    $liveSales  = $liveOrders->sum('total');
                    $liveCount  = $liveOrders->count();
                    $liveExp    = $openRegister->expenses()->sum('amount');
                @endphp
                <div class="grid grid-cols-2 md:grid-cols-4 gap-3 mt-5">
                    <div class="bg-white/15 rounded-xl p-3 text-center">
                        <div class="text-xl font-black">{{ $liveCount }}</div>
                        <div class="text-xs text-green-100">Ventas</div>
                    </div>
                    <div class="bg-white/15 rounded-xl p-3 text-center">
                        <div class="text-xl font-black">{{ number_format($liveSales, 0, ',', '.') }}</div>
                        <div class="text-xs text-green-100">Gs en ventas</div>
                    </div>
                    <div class="bg-white/15 rounded-xl p-3 text-center">
                        <div class="text-xl font-black text-red-200">{{ number_format($liveExp, 0, ',', '.') }}</div>
                        <div class="text-xs text-green-100">Gs en egresos</div>
                    </div>
                    <div class="bg-white/15 rounded-xl p-3 text-center">
                        <div class="text-xl font-black text-yellow-200">{{ number_format($openRegister->opening_amount + $liveSales - $liveExp, 0, ',', '.') }}</div>
                        <div class="text-xs text-green-100">Efectivo esperado</div>
                    </div>
                </div>
            </div>

        @else
            {{-- CAJA CERRADA --}}
            <div class="bg-gradient-to-r from-gray-700 to-gray-800 rounded-2xl p-6 text-white shadow-xl">
                <div class="flex flex-col lg:flex-row justify-between items-center gap-4">
                    <div class="flex items-center gap-4">
                        <span class="text-5xl">🔴</span>
                        <div>
                            <h2 class="text-2xl font-black">Caja Cerrada</h2>
                            <p class="text-gray-300">No hay ninguna caja abierta en este momento.</p>
                        </div>
                    </div>
                    <button wire:click="openOpenModal"
                        class="bg-green-500 hover:bg-green-600 active:bg-green-700 text-white font-black px-8 py-4 rounded-xl transition-all shadow-lg text-xl">
                        🔓 Abrir Caja
                    </button>
                </div>
            </div>
        @endif

        {{-- ══ STATS DEL MES ══ --}}
        @if($monthStats && $monthStats->total_registers > 0)
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <div class="bg-white rounded-xl shadow p-4 text-center">
                    <div class="text-2xl font-black text-purple-600">{{ $monthStats->total_registers }}</div>
                    <div class="text-sm text-gray-500 mt-1">Cajas este mes</div>
                </div>
                <div class="bg-white rounded-xl shadow p-4 text-center">
                    <div class="text-xl font-black text-green-600">{{ number_format($monthStats->total_sales, 0, ',', '.') }}</div>
                    <div class="text-sm text-gray-500 mt-1">Gs vendidos</div>
                </div>
                <div class="bg-white rounded-xl shadow p-4 text-center">
                    <div class="text-xl font-black text-red-500">{{ number_format($monthStats->total_expenses, 0, ',', '.') }}</div>
                    <div class="text-sm text-gray-500 mt-1">Gs en egresos</div>
                </div>
                <div class="bg-white rounded-xl shadow p-4 text-center">
                    <div class="text-xl font-black {{ $monthStats->avg_difference < 1000 ? 'text-green-600' : 'text-yellow-600' }}">
                        {{ number_format($monthStats->avg_difference, 0, ',', '.') }}
                    </div>
                    <div class="text-sm text-gray-500 mt-1">Diferencia promedio</div>
                </div>
            </div>
        @endif

        {{-- ══ HISTORIAL ══ --}}
        <div class="bg-white rounded-2xl shadow-lg overflow-hidden">
            <div class="px-6 py-4 border-b bg-gray-50 flex justify-between items-center">
                <h3 class="text-lg font-black text-gray-900">📋 Historial de Cajas</h3>
                <span class="text-sm text-gray-500">{{ $history->count() }} registros</span>
            </div>

            @forelse($history as $register)
                <div class="px-6 py-4 border-b last:border-0 hover:bg-gray-50 transition-colors">
                    <div class="flex flex-col md:flex-row justify-between gap-3">

                        {{-- Info básica --}}
                        <div class="flex items-start gap-4">
                            <div class="mt-1">
                                @if($register->status === 'open')
                                    <span class="w-3 h-3 rounded-full bg-green-500 block animate-pulse"></span>
                                @else
                                    <span class="w-3 h-3 rounded-full bg-gray-400 block"></span>
                                @endif
                            </div>
                            <div>
                                <div class="flex items-center gap-2 flex-wrap">
                                    <span class="font-black text-gray-900">
                                        {{ $register->opened_at->format('d/m/Y') }}
                                    </span>
                                    <span class="text-sm text-gray-500">
                                        {{ $register->opened_at->format('H:i') }}
                                        @if($register->closed_at) → {{ $register->closed_at->format('H:i') }} @endif
                                    </span>
                                    <span class="text-xs text-gray-400">({{ $register->duration }})</span>
                                    @if($register->status === 'open')
                                        <span class="bg-green-100 text-green-800 text-xs px-2 py-0.5 rounded-full font-bold">Abierta</span>
                                    @endif
                                </div>
                                <div class="text-sm text-gray-600 mt-1">
                                    👤 {{ $register->opener->name ?? '—' }}
                                    @if($register->closer && $register->closer->id !== $register->opener->id)
                                        → cerrada por {{ $register->closer->name }}
                                    @endif
                                </div>
                                @if($register->closing_notes)
                                    <div class="text-xs text-gray-500 mt-1">📝 {{ $register->closing_notes }}</div>
                                @endif
                            </div>
                        </div>

                        {{-- Cifras --}}
                        <div class="flex gap-6 text-right flex-wrap">
                            <div>
                                <div class="text-xs text-gray-500">Apertura</div>
                                <div class="font-bold text-gray-700">{{ number_format($register->opening_amount, 0, ',', '.') }} Gs</div>
                                @if($register->opening_amount_brl > 0)
                                    <div class="text-xs text-blue-600">{{ number_format($register->opening_amount_brl, 0) }} R$</div>
                                @endif
                            </div>
                            @if($register->status === 'closed')
                                <div>
                                    <div class="text-xs text-gray-500">Ventas</div>
                                    <div class="font-bold text-green-600">+{{ number_format($register->total_sales, 0, ',', '.') }} Gs</div>
                                </div>
                                <div>
                                    <div class="text-xs text-gray-500">Egresos</div>
                                    <div class="font-bold text-red-500">-{{ number_format($register->total_expenses, 0, ',', '.') }} Gs</div>
                                </div>
                                <div>
                                    <div class="text-xs text-gray-500">Cierre</div>
                                    <div class="font-bold text-gray-900">{{ number_format($register->closing_amount, 0, ',', '.') }} Gs</div>
                                    @if($register->closing_amount_brl > 0)
                                        <div class="text-xs text-blue-600">{{ number_format($register->closing_amount_brl, 0) }} R$</div>
                                    @endif
                                </div>
                                <div>
                                    <div class="text-xs text-gray-500">Diferencia</div>
                                    <div class="font-bold {{ $register->difference == 0 ? 'text-green-600' : ($register->difference > 0 ? 'text-blue-600' : 'text-red-600') }}">
                                        {{ $register->difference > 0 ? '+' : '' }}{{ number_format($register->difference, 0, ',', '.') }} Gs
                                        <span class="text-xs">({{ $register->difference_status }})</span>
                                    </div>
                                    @if($register->difference_brl != 0)
                                        <div class="text-xs {{ $register->difference_brl == 0 ? 'text-green-600' : ($register->difference_brl > 0 ? 'text-blue-600' : 'text-red-600') }}">
                                            {{ $register->difference_brl > 0 ? '+' : '' }}{{ number_format($register->difference_brl, 0) }} R$
                                        </div>
                                    @endif
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            @empty
                <div class="px-6 py-12 text-center text-gray-400">
                    <div class="text-5xl mb-3">🏦</div>
                    <div class="font-bold">No hay cajas registradas todavía.</div>
                </div>
            @endforelse

            @if($history->count() >= $historyLimit)
                <div class="px-6 py-4 bg-gray-50 text-center">
                    <button wire:click="loadMore" class="text-purple-600 hover:text-purple-700 font-bold text-sm">
                        Cargar más →
                    </button>
                </div>
            @endif
        </div>
    </div>

    {{-- ══ MODAL APERTURA ══ --}}
    @if($showOpenModal)
        <div x-data="{ show: true }" 
             x-show="show"
             x-transition
             class="fixed inset-0 bg-black/60 backdrop-blur-sm flex items-center justify-center p-4 z-50">
            <div class="bg-white rounded-2xl max-w-md w-full shadow-2xl overflow-hidden" 
                 @click.outside="show = false; $wire.set('showOpenModal', false)">
                <div class="bg-gradient-to-r from-green-500 to-emerald-600 p-5 text-white">
                    <h2 class="text-2xl font-black">🔓 Abrir Caja</h2>
                    <p class="text-green-100 text-sm mt-1">Ingresá el efectivo inicial en caja.</p>
                </div>

                <div class="p-6 space-y-4">
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2">
                            Monto inicial en caja <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <input wire:model="openingAmount" type="number" min="0" step="1000"
                                class="w-full px-4 py-3 text-2xl font-bold text-center border-2 border-gray-300 rounded-xl focus:ring-2 focus:ring-green-500 focus:border-green-500"
                                placeholder="0" autofocus>
                            <span class="absolute right-4 top-1/2 -translate-y-1/2 text-gray-400 font-medium">Gs</span>
                        </div>
                        @error('openingAmount') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror

                        {{-- Atajos rápidos --}}
                        <div class="grid grid-cols-4 gap-2 mt-3">
                            @foreach([50000, 100000, 200000, 500000] as $quick)
                                <button wire:click="$set('openingAmount', {{ $quick }})" type="button"
                                    class="py-2 text-sm font-bold rounded-lg border-2 border-gray-200 hover:border-green-400 hover:bg-green-50 transition-all">
                                    {{ number_format($quick/1000, 0) }}k
                                </button>
                            @endforeach
                        </div>
                    </div>
                    {{-- Monto en reales --}}
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2">
                            Reales en caja (R$) <span class="text-gray-400 text-xs">(opcional)</span>
                        </label>
                        <div class="relative">
                            <input wire:model="openingAmountBrl" type="number" min="0" step="1"
                                class="w-full px-4 py-3 text-xl font-bold text-center border-2 border-gray-300 rounded-xl focus:ring-2 focus:ring-green-500 focus:border-green-500"
                                placeholder="0">
                            <span class="absolute right-4 top-1/2 -translate-y-1/2 text-gray-400 font-medium">R$</span>
                        </div>
                        @error('openingAmountBrl') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        <p class="text-xs text-gray-500 mt-1">💡 Cantidad de reales físicos en el cajón</p>
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2">Notas (opcional)</label>
                        <input wire:model="openingNotes" type="text"
                            class="w-full px-4 py-2 border-2 border-gray-300 rounded-xl focus:ring-2 focus:ring-green-500"
                            placeholder="Ej: Cambio de guardia, fondos del banco...">
                    </div>
                </div>

                <div class="p-4 bg-gray-50 flex gap-3">
                    <button @click="show = false; $wire.set('showOpenModal', false)"
                        class="flex-1 bg-gray-200 hover:bg-gray-300 text-gray-800 font-bold py-3 rounded-xl">Cancelar</button>
                    <button @click="show = false; $wire.confirmOpen()"
                        class="flex-1 bg-green-500 hover:bg-green-600 text-white font-bold py-3 rounded-xl">
                        ✓ Abrir Caja
                    </button>
                </div>
            </div>
        </div>
    @endif

    {{-- ══ MODAL CIERRE ══ --}}
    @if($showCloseModal && !empty($closingSummary))
        <div x-data="{ show: true }"
             x-show="show"
             x-transition
             class="fixed inset-0 bg-black/60 backdrop-blur-sm flex items-center justify-center p-4 z-50">
            <div class="bg-white rounded-2xl max-w-lg w-full shadow-2xl flex flex-col max-h-[90vh]"
                 @click.outside="show = false; $wire.set('showCloseModal', false)">
                <div class="bg-gradient-to-r from-red-500 to-rose-600 p-5 text-white flex-shrink-0">
                    <h2 class="text-2xl font-black">🔒 Cerrar Caja</h2>
                    <p class="text-red-100 text-sm mt-1">Resumen de la jornada · {{ $closingSummary['duration'] }}</p>
                </div>

                <div class="p-6 space-y-5 overflow-y-auto flex-1">

                    {{-- Resumen de ventas --}}
                    <div class="bg-gray-50 rounded-xl p-4 space-y-2">
                        <h4 class="font-black text-gray-700 mb-3">📊 Resumen de la Caja</h4>

                        <div class="flex justify-between text-sm">
                            <span class="text-gray-600">Monto inicial:</span>
                            <span class="font-bold">{{ number_format($closingSummary['opening_amount'], 0, ',', '.') }} Gs</span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-600">Total ventas ({{ $closingSummary['total_orders'] }} pedidos):</span>
                            <span class="font-bold text-green-600">+{{ number_format($closingSummary['total_sales'], 0, ',', '.') }} Gs</span>
                        </div>

                        {{-- Desglose por método --}}
                        @foreach($closingSummary['by_method'] as $methodName => $data)
                            <div class="flex justify-between text-xs text-gray-500 pl-4">
                                <span>↳ {{ $methodName }} ({{ $data['count'] }} ventas):</span>
                                <span>{{ number_format($data['amount'], 0, ',', '.') }} Gs</span>
                            </div>
                        @endforeach

                        <div class="flex justify-between text-sm">
                            <span class="text-gray-600">Egresos en efectivo:</span>
                            <span class="font-bold text-red-500">-{{ number_format($closingSummary['expenses_cash'], 0, ',', '.') }} Gs</span>
                        </div>
                        <div class="border-t pt-2 flex justify-between font-black">
                            <span>Efectivo esperado en caja:</span>
                            <span class="text-purple-600">{{ number_format($closingSummary['expected_cash'], 0, ',', '.') }} Gs</span>
                        </div>
                    </div>

                    {{-- Monto contado --}}
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2">
                            Monto contado físicamente <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <input wire:model.live="closingAmount" type="number" min="0" step="1000"
                                class="w-full px-4 py-3 text-2xl font-bold text-center border-2 border-gray-300 rounded-xl focus:ring-2 focus:ring-red-500 focus:border-red-500">
                            <span class="absolute right-4 top-1/2 -translate-y-1/2 text-gray-400 font-medium">Gs</span>
                        </div>
                        @error('closingAmount') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    {{-- Reales contados --}}
                    @if($closingSummary['opening_brl'] > 0 || $closingSummary['foreign_count'] > 0)
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-2">
                                Reales contados (R$) <span class="text-gray-400 text-xs">(opcional)</span>
                            </label>
                            <div class="relative">
                                <input wire:model.live="closingAmountBrl" type="number" min="0" step="1"
                                    class="w-full px-4 py-3 text-xl font-bold text-center border-2 border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                <span class="absolute right-4 top-1/2 -translate-y-1/2 text-gray-400 font-medium">R$</span>
                            </div>
                            <div class="text-xs text-gray-500 mt-1 flex justify-between">
                                <span>Inicial: {{ number_format($closingSummary['opening_brl'], 0) }} R$</span>
                                <span>Ventas: {{ $closingSummary['foreign_count'] }}</span>
                                <span>Esperado: {{ number_format($closingSummary['expected_brl'], 0) }} R$</span>
                            </div>
                            @error('closingAmountBrl') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                    @endif

                    @php
                        $diff = (float)$this->closingAmount - (float)$closingSummary['expected_cash'];
                        $diffBrl = (float)$this->closingAmountBrl - (float)$closingSummary['expected_brl'];
                    @endphp

                    <div class="rounded-xl p-4 text-center
                        {{ $diff == 0 ? 'bg-green-50 border-2 border-green-300' : ($diff > 0 ? 'bg-blue-50 border-2 border-blue-300' : 'bg-red-50 border-2 border-red-300') }}">
                        <div class="text-xs font-bold mb-1
                            {{ $diff == 0 ? 'text-green-600' : ($diff > 0 ? 'text-blue-600' : 'text-red-600') }}">
                            {{ $diff == 0 ? '✓ Exacto' : ($diff > 0 ? '↑ Sobrante' : '↓ Faltante') }}
                        </div>
                        <div class="text-3xl font-black
                            {{ $diff == 0 ? 'text-green-600' : ($diff > 0 ? 'text-blue-600' : 'text-red-600') }}">
                            {{ $diff > 0 ? '+' : '' }}{{ number_format($diff, 0, ',', '.') }} Gs
                        </div>
                    </div>

                    {{-- Diferencia en reales --}}
                    @if($closingSummary['opening_brl'] > 0 || $closingSummary['foreign_count'] > 0)
                        <div class="rounded-xl p-4 text-center
                            {{ $diffBrl == 0 ? 'bg-blue-50 border-2 border-blue-300' : ($diffBrl > 0 ? 'bg-green-50 border-2 border-green-300' : 'bg-red-50 border-2 border-red-300') }}">
                            <div class="text-xs font-bold mb-1
                                {{ $diffBrl == 0 ? 'text-blue-600' : ($diffBrl > 0 ? 'text-green-600' : 'text-red-600') }}">
                                {{ $diffBrl == 0 ? '✓ Exacto (R$)' : ($diffBrl > 0 ? '↑ Sobrante (R$)' : '↓ Faltante (R$)') }}
                            </div>
                            <div class="text-2xl font-black
                                {{ $diffBrl == 0 ? 'text-blue-600' : ($diffBrl > 0 ? 'text-green-600' : 'text-red-600') }}">
                                {{ $diffBrl > 0 ? '+' : '' }}{{ number_format($diffBrl, 0) }} R$
                            </div>
                        </div>
                    @endif

                    {{-- Notas --}}
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2">Notas del cierre (opcional)</label>
                        <textarea wire:model="closingNotes" rows="2"
                            class="w-full px-4 py-2 border-2 border-gray-300 rounded-xl focus:ring-2 focus:ring-red-500"
                            placeholder="Observaciones, discrepancias, etc."></textarea>
                    </div>
                </div>

                <div class="p-4 bg-gray-50 flex gap-3 flex-shrink-0 border-t">
                    <button @click="show = false; $wire.set('showCloseModal', false)"
                        class="flex-1 bg-gray-200 hover:bg-gray-300 text-gray-800 font-bold py-3 rounded-xl">Cancelar</button>
                    <button @click="show = false; $wire.confirmClose()"
                        class="flex-1 bg-red-500 hover:bg-red-600 text-white font-bold py-3 rounded-xl">
                        🔒 Cerrar Caja
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>