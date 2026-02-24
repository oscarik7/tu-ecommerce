<div>
    {{-- ══ NOTIFICACIONES ══ --}}
    <div x-data="{ notifications: [] }"
         x-on:show-notification.window="
            const n = $event.detail[0] ?? $event.detail;
            notifications.push(n);
            setTimeout(() => notifications.shift(), 3500);
         "
         class="fixed top-4 right-4 z-50 space-y-2 pointer-events-none">
        <template x-for="(n, i) in notifications" :key="i">
            <div x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0 translate-x-4"
                 x-transition:enter-end="opacity-100 translate-x-0"
                 x-transition:leave="transition ease-in duration-150"
                 x-transition:leave-end="opacity-0"
                 :class="{
                     'bg-emerald-500': n.type === 'success',
                     'bg-red-500':     n.type === 'error',
                     'bg-blue-500':    n.type === 'info',
                     'bg-yellow-500':  n.type === 'warning',
                 }"
                 class="text-white px-4 py-3 rounded-xl shadow-xl font-bold text-sm flex items-center gap-2 pointer-events-auto">
                <span x-text="n.message"></span>
            </div>
        </template>
    </div>

    <div class="space-y-6 max-w-5xl mx-auto">

        {{-- ══════════════════════════════════════════════════════════
             BLOQUE 1: ESTADO ACTUAL DE LA CAJA
        ══════════════════════════════════════════════════════════ --}}

        @if($openRegister)
            {{-- ── CAJA ABIERTA ── --}}
            <div wire:key="register-open-block"
                 class="bg-gradient-to-br from-emerald-500 to-teal-600 rounded-2xl p-6 text-white shadow-xl">

                <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center gap-4 mb-5">
                    <div>
                        <div class="flex items-center gap-3 mb-1">
                            <span class="w-3 h-3 rounded-full bg-white animate-pulse block"></span>
                            <h2 class="text-2xl font-black">Caja Abierta</h2>
                        </div>
                        <p class="text-emerald-100 text-sm">
                            Abierta por <strong>{{ $openRegister->opener->name ?? 'Usuario eliminado' }}</strong>
                            · {{ $openRegister->opened_at->format('d/m/Y H:i') }}
                            · hace <strong>{{ $openRegister->duration }}</strong>
                        </p>
                        @if($openRegister->opening_notes)
                            <p class="text-emerald-200 text-xs mt-1">📝 {{ $openRegister->opening_notes }}</p>
                        @endif
                    </div>
                    <div class="flex gap-3 items-center flex-shrink-0">
                        <div class="text-center bg-white/20 rounded-xl px-4 py-3">
                            <div class="text-xl font-black">{{ number_format($openRegister->opening_amount, 0, ',', '.') }} Gs</div>
                            @if($openRegister->opening_amount_brl > 0)
                                <div class="text-xs font-bold text-emerald-100">+ {{ number_format($openRegister->opening_amount_brl, 2, ',', '.') }} R$</div>
                            @endif
                            <div class="text-xs text-emerald-100 mt-0.5">Monto inicial</div>
                        </div>
                        <button wire:click="openCloseModal"
                                wire:loading.attr="disabled"
                                wire:target="openCloseModal"
                                class="bg-red-500 hover:bg-red-600 active:bg-red-700 text-white font-black px-5 py-3 rounded-xl transition-all shadow-lg disabled:opacity-50">
                            🔒 Cerrar Caja
                        </button>
                    </div>
                </div>

                {{-- ══ Métricas en tiempo real ══
                     FIX: $liveExpTotalBrl es una variable separada, la card BRL
                     es un elemento independiente del grid — NO anidado dentro de otra card.
                --}}
                @php
                    $baseQuery       = $openRegister->orders()->where('status', '!=', 'cancelled')->where('payment_status', 'paid');
                    $liveSales       = (float) $baseQuery->sum('total');
                    $liveCount       = (int)   $baseQuery->count();
                    $liveSplitCount  = (int)   $openRegister->orders()
                                         ->where('status', '!=', 'cancelled')
                                         ->where('payment_status', 'paid')
                                         ->where('is_split_payment', true)
                                         ->count();
                    // Egresos en Gs: solo registros con currency='gs'
                    $liveExpCash     = (float) $openRegister->expenses()
                                         ->where('currency', 'gs')
                                         ->where('payment_method', 'cash')
                                         ->sum('amount');
                    $liveExpTotal    = (float) $openRegister->expenses()
                                         ->where('currency', 'gs')
                                         ->sum('amount');
                    // Egresos en BRL: registros con currency='brl'
                    $liveExpTotalBrl = (float) $openRegister->expenses()
                                         ->where('currency', 'brl')
                                         ->sum('amount_brl');
                    $liveExpected    = (float) $openRegister->opening_amount + $liveSales - $liveExpCash;
                @endphp

                {{-- Grid de métricas: 4 columnas base, 5 si hay egresos BRL --}}
                <div class="{{ $liveExpTotalBrl > 0 ? 'grid-cols-2 md:grid-cols-5' : 'grid-cols-2 md:grid-cols-4' }} grid gap-3">

                    {{-- Card 1: Ventas --}}
                    <div class="bg-white/15 rounded-xl p-3 text-center">
                        <div class="text-2xl font-black">{{ $liveCount }}</div>
                        @if($liveSplitCount > 0)
                            <div class="text-xs text-emerald-200">{{ $liveSplitCount }} divididos</div>
                        @endif
                        <div class="text-xs text-emerald-100 mt-0.5">Ventas</div>
                    </div>

                    {{-- Card 2: Gs vendidos --}}
                    <div class="bg-white/15 rounded-xl p-3 text-center">
                        @if($liveSales >= 1000)
                            <div class="text-lg font-black">{{ number_format($liveSales / 1000, 0) }}k</div>
                        @else
                            <div class="text-lg font-black">{{ number_format($liveSales, 0, ',', '.') }}</div>
                        @endif
                        <div class="text-xs text-emerald-100 mt-0.5">Gs vendidos</div>
                    </div>

                    {{-- Card 3: Egresos Gs --}}
                    <div class="bg-white/15 rounded-xl p-3 text-center">
                        @if($liveExpTotal > 0)
                            <div class="text-lg font-black text-red-200">
                                -{{ $liveExpTotal >= 1000 ? number_format($liveExpTotal / 1000, 0) . 'k' : number_format($liveExpTotal, 0, ',', '.') }}
                            </div>
                            @if($liveExpCash < $liveExpTotal)
                                <div class="text-xs text-emerald-200">
                                    {{ $liveExpCash >= 1000 ? number_format($liveExpCash / 1000, 0) . 'k' : number_format($liveExpCash, 0, ',', '.') }} en efectivo
                                </div>
                            @endif
                        @else
                            <div class="text-lg font-black text-emerald-200">—</div>
                        @endif
                        <div class="text-xs text-emerald-100 mt-0.5">Egresos Gs</div>
                    </div>

                    {{-- Card 4: Egresos R$ (SOLO si hay egresos BRL, card independiente del grid) --}}
                    @if($liveExpTotalBrl > 0)
                        <div class="bg-blue-400/30 rounded-xl p-3 text-center border border-blue-300/30">
                            <div class="text-lg font-black text-blue-200">
                                -R$ {{ number_format($liveExpTotalBrl, 2, ',', '.') }}
                            </div>
                            <div class="text-xs text-emerald-100 mt-0.5">Egresos R$</div>
                        </div>
                    @endif

                    {{-- Card 5 (o 4 si sin BRL): Efectivo esperado Gs --}}
                    <div class="bg-white/15 rounded-xl p-3 text-center">
                        <div class="text-lg font-black text-yellow-200">
                            {{ $liveExpected >= 1000 ? number_format($liveExpected / 1000, 0) . 'k' : number_format($liveExpected, 0, ',', '.') }}
                        </div>
                        <div class="text-xs text-emerald-100 mt-0.5">Efectivo esperado Gs</div>
                    </div>
                </div>
            </div>

        @else
            {{-- ── CAJA CERRADA ── --}}
            <div wire:key="register-closed-block"
                 class="bg-gradient-to-r from-gray-700 to-gray-800 rounded-2xl p-6 text-white shadow-xl">
                <div class="flex flex-col lg:flex-row justify-between items-center gap-4">
                    <div class="flex items-center gap-4">
                        <span class="text-5xl">🔴</span>
                        <div>
                            <h2 class="text-2xl font-black">Caja Cerrada</h2>
                            <p class="text-gray-300 text-sm">No hay ninguna caja abierta en este momento.</p>
                        </div>
                    </div>
                    <button wire:click="openOpenModal"
                            wire:loading.attr="disabled"
                            wire:target="openOpenModal"
                            class="bg-emerald-500 hover:bg-emerald-600 active:bg-emerald-700 text-white font-black px-8 py-4 rounded-xl transition-all shadow-lg text-xl disabled:opacity-50">
                        🔓 Abrir Caja
                    </button>
                </div>
            </div>
        @endif

        {{-- ══════════════════════════════════════════════════════════
             BLOQUE 2: STATS DEL MES
        ══════════════════════════════════════════════════════════ --}}

        @if($monthStats && $monthStats->total_registers > 0)
            @php
                $msNetResult      = (float) ($monthStats->net_result      ?? 0);
                $msTotalDiff      = (float) ($monthStats->total_difference ?? 0);
                $msExactCloses    = (int)   ($monthStats->exact_closes     ?? 0);
                $msTotalRegisters = (int)   ($monthStats->total_registers  ?? 1);
                $precision        = round(($msExactCloses / $msTotalRegisters) * 100);
            @endphp
            <div wire:key="month-stats-block">
                <h3 class="text-sm font-bold text-gray-500 uppercase tracking-wide mb-3 px-1">
                    Resumen del mes · {{ now()->locale('es')->translatedFormat('F Y') }}
                </h3>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">

                    <div class="bg-white rounded-xl shadow-sm p-4 text-center border border-gray-100">
                        <div class="text-2xl font-black text-purple-600">{{ $msTotalRegisters }}</div>
                        <div class="text-xs text-gray-500 mt-1">Cajas cerradas</div>
                    </div>

                    <div class="bg-white rounded-xl shadow-sm p-4 text-center border border-gray-100">
                        <div class="text-xl font-black text-emerald-600">
                            {{ $msNetResult >= 1000 ? number_format($msNetResult / 1000, 0) . 'k' : number_format($msNetResult, 0, ',', '.') }}
                        </div>
                        <div class="text-xs text-gray-500 mt-1">Gs neto (ventas − egresos)</div>
                    </div>

                    <div class="bg-white rounded-xl shadow-sm p-4 text-center border border-gray-100">
                        <div class="text-xl font-black {{ $msTotalDiff == 0 ? 'text-gray-600' : ($msTotalDiff > 0 ? 'text-blue-600' : 'text-red-600') }}">
                            {{ $msTotalDiff > 0 ? '+' : '' }}{{ number_format($msTotalDiff, 0, ',', '.') }} Gs
                        </div>
                        <div class="text-xs text-gray-500 mt-1">Diferencia acumulada</div>
                        <div class="text-xs {{ $msTotalDiff > 0 ? 'text-blue-500' : ($msTotalDiff < 0 ? 'text-red-500' : 'text-gray-400') }}">
                            {{ $msTotalDiff == 0 ? 'Sin diferencia' : ($msTotalDiff > 0 ? '↑ Sobró' : '↓ Faltó') }}
                        </div>
                    </div>

                    <div class="bg-white rounded-xl shadow-sm p-4 text-center border border-gray-100">
                        <div class="text-2xl font-black {{ $precision >= 80 ? 'text-emerald-600' : ($precision >= 50 ? 'text-yellow-600' : 'text-red-600') }}">
                            {{ $precision }}%
                        </div>
                        <div class="text-xs text-gray-500 mt-1">Cierres exactos</div>
                        <div class="text-xs text-gray-400">{{ $msExactCloses }}/{{ $msTotalRegisters }}</div>
                    </div>
                </div>
            </div>
        @endif

        {{-- ══════════════════════════════════════════════════════════
             BLOQUE 3: HISTORIAL
        ══════════════════════════════════════════════════════════ --}}

        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="px-6 py-4 border-b bg-gray-50 flex justify-between items-center">
                <h3 class="text-base font-black text-gray-900">📋 Historial de Cajas</h3>
                <span class="text-sm text-gray-400">{{ $history->count() }} registros</span>
            </div>

            @forelse($history as $register)
                <div wire:key="history-row-{{ $register->id }}"
                     class="px-6 py-4 border-b last:border-0 hover:bg-gray-50/60 transition-colors">
                    <div class="flex flex-col md:flex-row justify-between gap-4">

                        {{-- Info --}}
                        <div class="flex items-start gap-3">
                            <div class="mt-1.5 flex-shrink-0">
                                @if($register->status === 'open')
                                    <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 block animate-pulse"></span>
                                @else
                                    <span class="w-2.5 h-2.5 rounded-full bg-gray-300 block"></span>
                                @endif
                            </div>
                            <div>
                                <div class="flex items-center gap-2 flex-wrap">
                                    <span class="font-black text-gray-900 text-sm">
                                        {{ $register->opened_at->format('d/m/Y') }}
                                    </span>
                                    <span class="text-sm text-gray-500">
                                        {{ $register->opened_at->format('H:i') }}
                                        @if($register->closed_at)
                                            → {{ $register->closed_at->format('H:i') }}
                                        @endif
                                    </span>
                                    <span class="text-xs text-gray-400">({{ $register->duration }})</span>
                                    @if($register->status === 'open')
                                        <span class="bg-emerald-100 text-emerald-700 text-xs px-2 py-0.5 rounded-full font-bold">Abierta</span>
                                    @endif
                                </div>
                                <div class="text-xs text-gray-500 mt-1">
                                    👤 {{ $register->opener->name ?? '—' }}
                                    @if($register->closer && $register->closer->id !== $register->opener?->id)
                                        → cerrada por <span class="font-medium">{{ $register->closer->name }}</span>
                                    @endif
                                </div>
                                @if($register->closing_notes)
                                    <div class="text-xs text-gray-400 mt-1 italic">📝 {{ $register->closing_notes }}</div>
                                @endif
                            </div>
                        </div>

                        {{-- Cifras --}}
                        <div class="flex gap-4 md:gap-6 text-right items-start flex-wrap">
                            <div class="min-w-[70px]">
                                <div class="text-xs text-gray-400 mb-0.5">Apertura</div>
                                <div class="font-bold text-gray-700 text-sm">
                                    {{ number_format($register->opening_amount / 1000, 0) }}k Gs
                                </div>
                                @if($register->opening_amount_brl > 0)
                                    <div class="text-xs text-gray-400">+ {{ number_format($register->opening_amount_brl, 2, ',', '.') }} R$</div>
                                @endif
                            </div>

                            @if($register->status === 'closed')
                                <div class="min-w-[70px]">
                                    <div class="text-xs text-gray-400 mb-0.5">Ventas</div>
                                    <div class="font-bold text-emerald-600 text-sm">
                                        +{{ number_format($register->total_sales / 1000, 0) }}k Gs
                                    </div>
                                    @if($register->total_orders > 0)
                                        <div class="text-xs text-gray-400">{{ $register->total_orders }} pedidos</div>
                                    @endif
                                </div>

                                @if($register->total_expenses > 0)
                                    <div class="min-w-[60px]">
                                        <div class="text-xs text-gray-400 mb-0.5">Egresos</div>
                                        <div class="font-bold text-red-500 text-sm">
                                            -{{ number_format($register->total_expenses / 1000, 0) }}k Gs
                                        </div>
                                    </div>
                                @endif

                                <div class="min-w-[70px]">
                                    <div class="text-xs text-gray-400 mb-0.5">Cierre</div>
                                    <div class="font-bold text-gray-900 text-sm">
                                        {{ number_format($register->closing_amount / 1000, 0) }}k Gs
                                    </div>
                                    @if($register->closing_amount_brl > 0)
                                        <div class="text-xs text-gray-400">{{ number_format($register->closing_amount_brl, 2, ',', '.') }} R$</div>
                                    @endif
                                </div>

                                <div class="min-w-[80px]">
                                    <div class="text-xs text-gray-400 mb-0.5">Diferencia</div>
                                    @php $diff = (float) ($register->difference ?? 0); @endphp
                                    <div class="font-black text-sm
                                        {{ abs($diff) < 1 ? 'text-emerald-600' : ($diff > 0 ? 'text-blue-600' : 'text-red-600') }}">
                                        {{ $diff > 0 ? '+' : '' }}{{ number_format($diff, 0, ',', '.') }} Gs
                                    </div>
                                    <div class="text-xs {{ abs($diff) < 1 ? 'text-emerald-500' : ($diff > 0 ? 'text-blue-500' : 'text-red-500') }}">
                                        {{ $register->difference_status }}
                                    </div>
                                    @if($register->difference_brl !== null && abs((float) $register->difference_brl) > 0.01)
                                        @php $diffBrl = (float) $register->difference_brl; @endphp
                                        <div class="text-xs {{ $diffBrl > 0 ? 'text-blue-500' : 'text-red-500' }} mt-0.5">
                                            {{ $diffBrl > 0 ? '+' : '' }}{{ number_format($diffBrl, 2, ',', '.') }} R$
                                        </div>
                                    @endif
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            @empty
                <div class="px-6 py-14 text-center text-gray-400">
                    <div class="text-5xl mb-3">🏦</div>
                    <div class="font-bold text-gray-500">No hay cajas registradas todavía.</div>
                </div>
            @endforelse

            @if($history->count() >= $historyLimit)
                <div class="px-6 py-4 bg-gray-50 text-center border-t">
                    <button wire:click="loadMore"
                            wire:loading.attr="disabled"
                            wire:target="loadMore"
                            class="text-purple-600 hover:text-purple-700 font-bold text-sm disabled:opacity-50">
                        <span wire:loading.remove wire:target="loadMore">Ver más →</span>
                        <span wire:loading wire:target="loadMore">Cargando...</span>
                    </button>
                </div>
            @endif
        </div>
    </div>

    {{-- ══════════════════════════════════════════════════════════
         MODAL: APERTURA DE CAJA
    ══════════════════════════════════════════════════════════ --}}

    <div wire:key="modal-open-wrapper"
         x-data="{ get show() { return $wire.showOpenModal } }"
         x-show="show"
         x-cloak
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-end="opacity-0"
         x-on:keydown.escape.window="$wire.set('showOpenModal', false)"
         class="fixed inset-0 bg-black/60 backdrop-blur-sm flex items-center justify-center p-4 z-50"
         @click.self="$wire.set('showOpenModal', false)">

        <div class="bg-white rounded-2xl max-w-md w-full shadow-2xl overflow-hidden">

            <div class="bg-gradient-to-r from-emerald-500 to-teal-600 p-5 text-white">
                <h2 class="text-xl font-black">🔓 Abrir Caja</h2>
                <p class="text-emerald-100 text-sm mt-1">Ingresá el efectivo inicial en el cajón.</p>
            </div>

            <div class="p-6 space-y-5">

                {{-- Guaraníes --}}
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-2">
                        Monto inicial en Gs <span class="text-red-500">*</span>
                    </label>
                    <div class="relative">
                        <input wire:model="openingAmount"
                               type="number" min="0" step="1000"
                               class="w-full px-4 py-3 text-2xl font-bold text-center border-2 border-gray-300 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500"
                               placeholder="0">
                        <span class="absolute right-4 top-1/2 -translate-y-1/2 text-gray-400 font-medium">Gs</span>
                    </div>
                    @error('openingAmount')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                    <div class="grid grid-cols-4 gap-2 mt-3">
                        @foreach([50000, 100000, 200000, 500000] as $q)
                            <button wire:click="$set('openingAmount', {{ $q }})" type="button"
                                    class="py-2 text-sm font-bold rounded-lg border-2 border-gray-200 hover:border-emerald-400 hover:bg-emerald-50 transition-all">
                                {{ number_format($q/1000, 0) }}k
                            </button>
                        @endforeach
                    </div>
                </div>

                {{-- Reales --}}
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-2">
                        Reales en cajón
                        <span class="text-gray-400 font-normal text-xs">(opcional)</span>
                    </label>
                    <div class="relative">
                        <input wire:model="openingAmountBrl"
                               type="number" min="0" step="0.01"
                               class="w-full px-4 py-3 text-xl font-bold text-center border-2 border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                               placeholder="0,00">
                        <span class="absolute right-4 top-1/2 -translate-y-1/2 text-gray-400 font-medium">R$</span>
                    </div>
                    @error('openingAmountBrl')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                    <p class="text-xs text-gray-400 mt-1">💡 Cantidad física de reales brasileños en el cajón al abrir</p>
                </div>

                {{-- Notas --}}
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-2">
                        Notas <span class="text-gray-400 font-normal text-xs">(opcional)</span>
                    </label>
                    <input wire:model="openingNotes" type="text"
                           class="w-full px-4 py-2 border-2 border-gray-300 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 text-sm"
                           placeholder="Ej: Cambio de guardia, fondos del banco...">
                </div>
            </div>

            <div class="p-4 bg-gray-50 flex gap-3 border-t">
                <button wire:click="$set('showOpenModal', false)"
                        type="button"
                        class="flex-1 bg-gray-200 hover:bg-gray-300 text-gray-800 font-bold py-3 rounded-xl transition-all">
                    Cancelar
                </button>
                <button wire:click="confirmOpen"
                        wire:loading.attr="disabled"
                        wire:target="confirmOpen"
                        type="button"
                        class="flex-1 bg-emerald-500 hover:bg-emerald-600 text-white font-bold py-3 rounded-xl transition-all disabled:opacity-50">
                    <span wire:loading.remove wire:target="confirmOpen">✓ Abrir Caja</span>
                    <span wire:loading wire:target="confirmOpen">Abriendo...</span>
                </button>
            </div>
        </div>
    </div>

    {{-- ══════════════════════════════════════════════════════════
         MODAL: CIERRE DE CAJA
    ══════════════════════════════════════════════════════════ --}}

    <div wire:key="modal-close-wrapper"
         x-data="{
             get show() { return $wire.showCloseModal && Object.keys($wire.closingSummary).length > 0 },

             get expectedGs()  { return parseFloat($wire.closingSummary?.expected_cash  ?? 0) },
             get expectedBrl() { return parseFloat($wire.closingSummary?.expected_brl   ?? 0) },
             get diffGs()      { return parseFloat($wire.closingAmount    ?? 0) - this.expectedGs  },
             get diffBrl()     { return parseFloat($wire.closingAmountBrl ?? 0) - this.expectedBrl },

             get diffGsLabel() {
                 if (Math.abs(this.diffGs) < 1) return '✓ Exacto · Guaraníes';
                 return (this.diffGs > 0 ? '↑ Sobrante' : '↓ Faltante') + ' · Guaraníes';
             },
             get diffGsClass() {
                 if (Math.abs(this.diffGs) < 1) return 'bg-emerald-50 border-emerald-300 text-emerald-600';
                 return this.diffGs > 0 ? 'bg-blue-50 border-blue-300 text-blue-600' : 'bg-red-50 border-red-300 text-red-600';
             },
             get diffGsHint() {
                 if (Math.abs(this.diffGs) < 1) return '';
                 return this.diffGs > 0
                     ? 'Hay más efectivo del esperado — posible error de conteo o venta no registrada.'
                     : 'Hay menos efectivo del esperado — revisar egresos o posible error de conteo.';
             },

             get diffBrlLabel() {
                 if (Math.abs(this.diffBrl) < 0.02) return '✓ Exacto · Reales';
                 return (this.diffBrl > 0 ? '↑ Sobrante' : '↓ Faltante') + ' · Reales';
             },
             get diffBrlClass() {
                 if (Math.abs(this.diffBrl) < 0.02) return 'bg-blue-50 border-blue-200 text-blue-600';
                 return this.diffBrl > 0 ? 'bg-emerald-50 border-emerald-200 text-emerald-600' : 'bg-red-50 border-red-200 text-red-600';
             },

             fmt(n, decimals = 0) {
                 return new Intl.NumberFormat('es-PY', {
                     minimumFractionDigits: decimals,
                     maximumFractionDigits: decimals,
                 }).format(n);
             }
         }"
         x-show="show"
         x-cloak
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-end="opacity-0"
         x-on:keydown.escape.window="$wire.set('showCloseModal', false)"
         class="fixed inset-0 bg-black/60 backdrop-blur-sm flex items-center justify-center p-4 z-50"
         @click.self="$wire.set('showCloseModal', false)">

        <div class="bg-white rounded-2xl max-w-xl w-full shadow-2xl flex flex-col max-h-[92vh]">

            {{-- Cabecera --}}
            <div class="bg-gradient-to-r from-red-500 to-rose-600 p-5 text-white flex-shrink-0 rounded-t-2xl">
                <h2 class="text-xl font-black">🔒 Cerrar Caja</h2>
                <p class="text-red-100 text-sm mt-1">
                    Jornada de {{ $closingSummary['duration'] ?? '—' }}
                    · {{ now()->format('d/m/Y H:i') }}
                </p>
            </div>

            <div class="overflow-y-auto flex-1 p-6 space-y-5">

                {{-- ── A: RESUMEN DE VENTAS ── --}}
                @if(!empty($closingSummary))
                @php
                    $s = $closingSummary;
                    // FIX: hasBrl se activa también si hay egresos BRL o apertura BRL, no solo ventas BRL
                    $hasBrl = ($s['foreign_sales_brl'] ?? 0) > 0
                           || ($s['opening_brl']       ?? 0) > 0
                           || ($s['expenses_cash_brl'] ?? 0) > 0
                           || ($s['expenses_total_brl'] ?? 0) > 0;
                    $hasApp = ($s['app_sales_count'] ?? 0) > 0;
                @endphp
                <div class="bg-gray-50 rounded-xl p-4 space-y-2.5 border border-gray-100">
                    <h4 class="font-black text-gray-800 text-sm mb-3">📊 Resumen de ventas</h4>

                    <div class="flex justify-between text-sm text-gray-600">
                        <span>Apertura en caja:</span>
                        <span class="font-bold text-gray-800">
                            {{ number_format($s['opening_amount'] ?? 0, 0, ',', '.') }} Gs
                            @if(($s['opening_brl'] ?? 0) > 0)
                                + {{ number_format($s['opening_brl'], 2, ',', '.') }} R$
                            @endif
                        </span>
                    </div>

                    <div class="flex justify-between text-sm text-gray-600">
                        <span>Total ventas ({{ $s['total_orders'] ?? 0 }} pedidos):</span>
                        <span class="font-bold text-emerald-600">+{{ number_format($s['total_sales'] ?? 0, 0, ',', '.') }} Gs</span>
                    </div>

                    {{-- Tabla desglose por método --}}
                    @if(!empty($s['by_method']))
                        <div class="bg-white rounded-lg border border-gray-100 overflow-hidden mt-2">
                            <table class="w-full text-xs">
                                <thead>
                                    <tr class="bg-gray-50 border-b border-gray-100">
                                        <th class="text-left px-3 py-2 text-gray-500 font-bold">Método</th>
                                        <th class="text-right px-3 py-2 text-gray-500 font-bold">Cant.</th>
                                        <th class="text-right px-3 py-2 text-gray-500 font-bold">Monto</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($s['by_method'] as $method)
                                        <tr wire:key="method-row-{{ Str::slug($method['name']) }}"
                                            class="border-b border-gray-50 last:border-0 hover:bg-gray-50">
                                            <td class="px-3 py-2 text-gray-700 font-medium">
                                                {{ $method['name'] }}
                                                @if($method['has_split'] ?? false)
                                                    <span class="text-gray-400 text-[10px] ml-1">(incluye divididos)</span>
                                                @endif
                                            </td>
                                            <td class="px-3 py-2 text-right text-gray-600">{{ $method['count'] }}</td>
                                            <td class="px-3 py-2 text-right font-bold text-gray-800">
                                                @if($method['is_foreign'] ?? false)
                                                    {{ number_format($method['amount_brl'], 2, ',', '.') }} R$
                                                    <div class="text-gray-400 font-normal">(≈ {{ number_format($method['amount_gs'], 0, ',', '.') }} Gs)</div>
                                                @else
                                                    {{ number_format($method['amount_gs'], 0, ',', '.') }} Gs
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif

                    @if($hasApp)
                        <div class="flex justify-between text-sm text-gray-500 pt-1">
                            <span>↳ de los cuales, por App Delivery:</span>
                            <span class="font-medium">
                                {{ $s['app_sales_count'] }} pedidos · {{ number_format($s['app_sales_amount'], 0, ',', '.') }} Gs
                            </span>
                        </div>
                    @endif

                    {{-- ── Egresos (Gs + BRL) ── --}}
                    @if(($s['expenses_cash'] ?? 0) > 0 || ($s['expenses_total'] ?? 0) > 0 || ($s['expenses_cash_brl'] ?? 0) > 0 || ($s['expenses_total_brl'] ?? 0) > 0)
                        <div class="border-t border-gray-200 pt-2 space-y-1">

                            {{-- Egresos en Gs --}}
                            @if(($s['expenses_cash'] ?? 0) > 0)
                                <div class="flex justify-between text-sm text-red-600">
                                    <span>Egresos efectivo Gs (afectan cajón):</span>
                                    <span class="font-bold">-{{ number_format($s['expenses_cash'], 0, ',', '.') }} Gs</span>
                                </div>
                            @endif
                            @if(($s['expenses_transfer'] ?? 0) > 0)
                                <div class="flex justify-between text-sm text-orange-600">
                                    <span>Egresos por transferencia:</span>
                                    <span class="font-bold">-{{ number_format($s['expenses_transfer'], 0, ',', '.') }} Gs</span>
                                </div>
                            @endif

                            {{-- Egresos en BRL --}}
                            @if(($s['expenses_cash_brl'] ?? 0) > 0)
                                <div class="flex justify-between text-sm text-blue-700 font-medium">
                                    <span>🇧🇷 Egresos efectivo R$ (afectan cajón):</span>
                                    <span class="font-bold">-{{ number_format($s['expenses_cash_brl'], 2, ',', '.') }} R$</span>
                                </div>
                            @endif
                            @if(($s['expenses_total_brl'] ?? 0) > ($s['expenses_cash_brl'] ?? 0))
                                @php $brlNonCash = ($s['expenses_total_brl'] ?? 0) - ($s['expenses_cash_brl'] ?? 0); @endphp
                                <div class="flex justify-between text-sm text-blue-500">
                                    <span>🇧🇷 Egresos R$ sin efectivo:</span>
                                    <span class="font-bold">-{{ number_format($brlNonCash, 2, ',', '.') }} R$</span>
                                </div>
                            @endif
                        </div>
                    @endif

                    {{-- Totales esperados --}}
                    <div class="border-t-2 border-gray-200 pt-2 space-y-1">
                        <div class="flex justify-between font-black text-sm">
                            <span>Efectivo esperado en cajón:</span>
                            <span class="text-purple-700">{{ number_format($s['expected_cash'] ?? 0, 0, ',', '.') }} Gs</span>
                        </div>
                        @if($hasBrl)
                            <div class="flex justify-between font-bold text-sm text-blue-700">
                                <span>Reales esperados en cajón:</span>
                                <span>{{ number_format($s['expected_brl'] ?? 0, 2, ',', '.') }} R$</span>
                            </div>
                        @endif
                    </div>
                </div>
                @endif

                {{-- ── B: CONTEO FÍSICO ── --}}
                <div class="space-y-4">
                    <h4 class="font-black text-gray-800 text-sm">💵 Conteo físico del cajón</h4>

                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2">
                            Gs contados físicamente <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <input wire:model="closingAmount"
                                   type="number" min="0" step="1000"
                                   class="w-full px-4 py-3 text-2xl font-bold text-center border-2 border-gray-300 rounded-xl focus:ring-2 focus:ring-red-500 focus:border-red-500">
                            <span class="absolute right-4 top-1/2 -translate-y-1/2 text-gray-400 font-medium">Gs</span>
                        </div>
                        @error('closingAmount')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Campo BRL: se muestra si hay cualquier movimiento en R$ (ventas, apertura o egresos) --}}
                    @if(!empty($closingSummary) && (
                        ($closingSummary['foreign_sales_brl'] ?? 0) > 0
                        || ($closingSummary['opening_brl']       ?? 0) > 0
                        || ($closingSummary['expenses_cash_brl'] ?? 0) > 0
                        || ($closingSummary['expenses_total_brl'] ?? 0) > 0
                    ))
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-2">
                                R$ contados físicamente
                                <span class="text-gray-400 font-normal text-xs">(opcional)</span>
                            </label>
                            <div class="relative">
                                <input wire:model="closingAmountBrl"
                                       type="number" min="0" step="0.01"
                                       class="w-full px-4 py-3 text-xl font-bold text-center border-2 border-blue-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                <span class="absolute right-4 top-1/2 -translate-y-1/2 text-blue-400 font-medium">R$</span>
                            </div>
                            <div class="flex justify-between text-xs text-gray-500 mt-1 px-1">
                                <span>Apertura: {{ number_format($closingSummary['opening_brl'] ?? 0, 2, ',', '.') }} R$</span>
                                <span>Ventas: {{ number_format($closingSummary['foreign_sales_brl'] ?? 0, 2, ',', '.') }} R$</span>
                                @if(($closingSummary['expenses_cash_brl'] ?? 0) > 0)
                                    <span class="text-red-500">Egresos: -{{ number_format($closingSummary['expenses_cash_brl'], 2, ',', '.') }} R$</span>
                                @endif
                                <span class="font-bold text-blue-600">Esperado: {{ number_format($closingSummary['expected_brl'] ?? 0, 2, ',', '.') }} R$</span>
                            </div>
                            @error('closingAmountBrl')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    @endif
                </div>

                {{-- ── C: DIFERENCIAS (Alpine, sin round-trip) ── --}}
                <div class="space-y-3">
                    <h4 class="font-black text-gray-800 text-sm">⚖️ Diferencias</h4>

                    <div :class="diffGsClass + ' rounded-xl p-4 text-center border-2'">
                        <div class="text-xs font-bold mb-1" :class="diffGsClass.split(' ').pop()"
                             x-text="diffGsLabel"></div>
                        <div class="text-3xl font-black" :class="diffGsClass.split(' ').pop()">
                            <span x-text="(diffGs > 0 ? '+' : '') + fmt(diffGs)"></span> Gs
                        </div>
                        <div class="text-xs mt-1" :class="diffGsClass.split(' ').pop()"
                             x-text="diffGsHint" x-show="diffGsHint !== ''"></div>
                    </div>

                    @if(!empty($closingSummary) && (
                        ($closingSummary['foreign_sales_brl'] ?? 0) > 0
                        || ($closingSummary['opening_brl']       ?? 0) > 0
                        || ($closingSummary['expenses_cash_brl'] ?? 0) > 0
                        || ($closingSummary['expenses_total_brl'] ?? 0) > 0
                    ))
                        <div :class="diffBrlClass + ' rounded-xl p-3 text-center border-2'">
                            <div class="text-xs font-bold mb-1" :class="diffBrlClass.split(' ').pop()"
                                 x-text="diffBrlLabel"></div>
                            <div class="text-2xl font-black" :class="diffBrlClass.split(' ').pop()">
                                <span x-text="(diffBrl > 0 ? '+' : '') + fmt(diffBrl, 2)"></span> R$
                            </div>
                        </div>
                    @endif
                </div>

                {{-- ── D: NOTAS ── --}}
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-2">
                        Notas del cierre
                        <span class="text-gray-400 font-normal text-xs">(opcional)</span>
                    </label>
                    <textarea wire:model="closingNotes" rows="2"
                              class="w-full px-4 py-2 border-2 border-gray-300 rounded-xl focus:ring-2 focus:ring-red-500 text-sm"
                              placeholder="Observaciones, discrepancias, incidencias..."></textarea>
                </div>
            </div>

            {{-- Footer --}}
            <div class="p-4 bg-gray-50 flex gap-3 flex-shrink-0 border-t rounded-b-2xl">
                <button wire:click="$set('showCloseModal', false)"
                        type="button"
                        class="flex-1 bg-gray-200 hover:bg-gray-300 text-gray-800 font-bold py-3 rounded-xl transition-all">
                    Cancelar
                </button>
                <button wire:click="confirmClose"
                        wire:loading.attr="disabled"
                        wire:target="confirmClose"
                        type="button"
                        class="flex-1 bg-red-500 hover:bg-red-600 text-white font-bold py-3 rounded-xl transition-all disabled:opacity-50">
                    <span wire:loading.remove wire:target="confirmClose">🔒 Confirmar Cierre</span>
                    <span wire:loading wire:target="confirmClose">Cerrando...</span>
                </button>
            </div>
        </div>
    </div>
</div>