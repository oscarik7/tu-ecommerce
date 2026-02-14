<div class="space-y-4 p-2 lg:p-0">

    {{-- ══ HEADER ══ --}}
    <div class="bg-gradient-to-r from-indigo-600 to-purple-700 rounded-2xl p-4 lg:p-5 text-white">
        <div class="flex flex-col lg:flex-row justify-between gap-3">
            <div>
                <h1 class="text-xl lg:text-2xl font-black">📊 Reportes</h1>
                <p class="text-indigo-200 text-xs mt-0.5">
                    {{ \Carbon\Carbon::parse($dateFrom)->format('d/m/Y') }}
                    @if($dateFrom !== $dateTo) — {{ \Carbon\Carbon::parse($dateTo)->format('d/m/Y') }} @endif
                </p>
            </div>
            {{-- Presets --}}
            <div class="flex flex-wrap gap-1.5 items-center">
                @foreach([
                    'today'      => 'Hoy',
                    'yesterday'  => 'Ayer',
                    'this_week'  => 'Esta sem.',
                    'last_week'  => 'Sem. ant.',
                    'this_month' => 'Este mes',
                    'last_month' => 'Mes ant.',
                    'this_year'  => 'Este año',
                ] as $key => $label)
                    <button wire:click="applyPreset('{{ $key }}')"
                        class="text-xs px-2.5 py-1.5 rounded-lg font-bold transition-all
                            {{ $preset === $key ? 'bg-white text-indigo-700' : 'bg-white/20 hover:bg-white/30 text-white' }}">
                        {{ $label }}
                    </button>
                @endforeach
            </div>
        </div>

        {{-- Rango personalizado --}}
        <div class="flex flex-wrap gap-2 mt-3 items-center">
            <input wire:model.live="dateFrom" type="date"
                class="px-3 py-2 rounded-xl border-0 text-gray-800 text-sm font-medium bg-white/90 w-full sm:w-auto">
            <span class="text-indigo-200 font-bold hidden sm:block">→</span>
            <input wire:model.live="dateTo" type="date"
                class="px-3 py-2 rounded-xl border-0 text-gray-800 text-sm font-medium bg-white/90 w-full sm:w-auto">
            <label class="flex items-center gap-2 cursor-pointer">
                <input wire:model.live="showComparison" type="checkbox" class="w-4 h-4 rounded text-indigo-600">
                <span class="text-xs text-indigo-200 font-medium">Comparar período anterior</span>
            </label>
            @if($showComparison && $compareFrom)
                <span class="text-xs bg-white/20 px-3 py-1 rounded-lg text-indigo-100">
                    vs {{ \Carbon\Carbon::parse($compareFrom)->format('d/m') }}
                    — {{ \Carbon\Carbon::parse($compareTo)->format('d/m/Y') }}
                </span>
            @endif
        </div>
    </div>

    {{-- ══ PESTAÑAS (scroll horizontal en móvil) ══ --}}
    <div class="flex gap-1 overflow-x-auto pb-1 scrollbar-none">
        @foreach([
            'overview' => '📈 Resumen',
            'sales'    => '💰 Ventas',
            'products' => '🍨 Productos',
            'expenses' => '💸 Egresos',
            'cash'     => '🏦 Caja',
        ] as $tab => $label)
            <button wire:click="$set('activeTab', '{{ $tab }}')"
                class="whitespace-nowrap px-3 py-2 font-bold text-xs lg:text-sm rounded-xl transition-all border-b-2 flex-shrink-0
                    {{ $activeTab === $tab
                        ? 'border-indigo-500 text-indigo-700 bg-indigo-50'
                        : 'border-transparent text-gray-500 hover:text-gray-700 hover:bg-gray-50' }}">
                {{ $label }}
            </button>
        @endforeach
    </div>

    {{-- ══ TARJETAS RESUMEN ══ --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3">
        <div class="bg-white rounded-2xl shadow-sm border p-3 lg:p-4">
            <div class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">Ventas</div>
            <div class="text-xl lg:text-2xl font-black text-gray-900">
                {{ number_format($overview['totalSales'], 0, ',', '.') }}
                <span class="text-xs font-medium text-gray-400">Gs</span>
            </div>
            <div class="text-xs text-gray-500 mt-0.5">{{ $overview['totalOrders'] }} pedidos</div>
            @if($overview['comparison'])
                @php $d = $overview['comparison']['sales_diff']; @endphp
                <div class="text-xs mt-1 font-bold {{ $d >= 0 ? 'text-green-600' : 'text-red-500' }}">
                    {{ $d >= 0 ? '↑' : '↓' }} {{ abs(round($d, 1)) }}% vs ant.
                </div>
            @endif
        </div>

        <div class="bg-white rounded-2xl shadow-sm border p-3 lg:p-4">
            <div class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">Neto</div>
            <div class="text-xl lg:text-2xl font-black text-purple-600">
                {{ number_format($overview['netSales'], 0, ',', '.') }}
                <span class="text-xs font-medium text-purple-300">Gs</span>
            </div>
            @if($overview['totalCommissions'] > 0)
                <div class="text-xs text-orange-500 mt-0.5">
                    -{{ number_format($overview['totalCommissions'], 0, ',', '.') }} comisiones
                </div>
            @endif
        </div>

        <div class="bg-white rounded-2xl shadow-sm border p-3 lg:p-4">
            <div class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">Egresos</div>
            <div class="text-xl lg:text-2xl font-black text-red-500">
                {{ number_format($overview['totalExpenses'], 0, ',', '.') }}
                <span class="text-xs font-medium text-red-300">Gs</span>
            </div>
            @if($overview['comparison'])
                @php $d = $overview['comparison']['expenses_diff']; @endphp
                <div class="text-xs mt-1 font-bold {{ $d <= 0 ? 'text-green-600' : 'text-red-500' }}">
                    {{ $d >= 0 ? '↑' : '↓' }} {{ abs(round($d, 1)) }}% vs ant.
                </div>
            @endif
        </div>

        <div class="bg-white rounded-2xl shadow-sm border p-3 lg:p-4">
            <div class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">Resultado</div>
            <div class="text-xl lg:text-2xl font-black {{ $overview['netResult'] >= 0 ? 'text-green-600' : 'text-red-600' }}">
                {{ $overview['netResult'] >= 0 ? '' : '-' }}{{ number_format(abs($overview['netResult']), 0, ',', '.') }}
                <span class="text-xs font-medium">Gs</span>
            </div>
            <div class="text-xs text-gray-500 mt-0.5">
                Ticket:  {{ number_format($overview['avgTicket'], 0, ',', '.') }} Gs

            </div>
        </div>
    </div>

    {{-- ══ RESUMEN / VENTAS ══ --}}
    @if(in_array($activeTab, ['overview', 'sales']) && $sales)

        @if(count($sales['salesByDay']) > 0)
            <div class="bg-white rounded-2xl shadow-sm border p-4 lg:p-5">
                <h3 class="font-black text-gray-800 mb-3 text-sm lg:text-base">📈 Ventas por Día</h3>
                <div style="height: 200px">
                    <canvas id="salesChart"></canvas>
                </div>
            </div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-3">
            <div class="bg-white rounded-2xl shadow-sm border p-4">
                <h3 class="font-black text-gray-800 mb-3 text-sm">📡 Por Canal</h3>
                @if(count($sales['byChannel']) > 0)
                    <div style="height: 160px" class="mb-3">
                        <canvas id="channelChart"></canvas>
                    </div>
                    <div class="space-y-2">
                        @foreach($sales['byChannel'] as $ch)
                            <div class="flex justify-between items-center text-xs lg:text-sm">
                                <span class="font-medium text-gray-700">{{ $ch['source'] }}</span>
                                <div class="text-right">
                                    <span class="font-black text-gray-900"> {{ number_format($ch['total'], 0, ',', '.') }} Gs</span>
                                    <span class="text-gray-400 ml-1">· {{ $ch['orders'] }} ped.</span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-gray-400 text-sm text-center py-6">Sin datos en este período</p>
                @endif
            </div>

            <div class="bg-white rounded-2xl shadow-sm border p-4">
                <h3 class="font-black text-gray-800 mb-3 text-sm">💳 Por Método de Pago</h3>
                @if(count($sales['byPayment']) > 0)
                    <div style="height: 160px" class="mb-3">
                        <canvas id="paymentChart"></canvas>
                    </div>
                    <div class="space-y-2">
                        @foreach($sales['byPayment'] as $pm)
                            <div class="flex justify-between items-center text-xs lg:text-sm">
                                <span class="font-medium text-gray-700">{{ $pm['name'] }}</span>
                                <div class="text-right">
                                    <span class="font-black text-gray-900">{{ number_format($pm['total'], 0, ',', '.') }} Gs</span>
                                    <span class="text-gray-400 ml-1">· {{ $pm['orders'] }} v.</span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-gray-400 text-sm text-center py-6">Sin datos en este período</p>
                @endif
            </div>
        </div>

        @if($activeTab === 'sales' && count($sales['byHour']) > 0)
            <div class="bg-white rounded-2xl shadow-sm border p-4">
                <h3 class="font-black text-gray-800 mb-3 text-sm">⏰ Picos por Hora</h3>
                <div style="height: 180px">
                    <canvas id="hourChart"></canvas>
                </div>
            </div>
        @endif

        @if($activeTab === 'sales' && count($sales['salesByDay']) > 0)
            <div class="bg-white rounded-2xl shadow-sm border overflow-hidden">
                <div class="px-4 py-3 bg-gray-50 border-b">
                    <h3 class="font-black text-gray-800 text-sm">📋 Detalle por Día</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-xs lg:text-sm">
                        <thead class="bg-gray-50 text-xs text-gray-500 uppercase tracking-wider">
                            <tr>
                                <th class="px-4 py-3 text-left">Fecha</th>
                                <th class="px-4 py-3 text-right">Pedidos</th>
                                <th class="px-4 py-3 text-right">Total Gs</th>
                                <th class="px-4 py-3 text-right hidden sm:table-cell">Ticket prom.</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach($sales['salesByDay'] as $day)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-4 py-2.5 font-medium text-gray-700">{{ $day['date'] }}</td>
                                    <td class="px-4 py-2.5 text-right text-gray-600">{{ $day['orders'] }}</td>
                                    <td class="px-4 py-2.5 text-right font-bold text-gray-900">{{ number_format($day['total'], 0, ',', '.') }}</td>
                                    <td class="px-4 py-2.5 text-right text-gray-500 hidden sm:table-cell">
                                        {{ $day['orders'] > 0 ? number_format($day['total'] / $day['orders'], 0, ',', '.') : '—' }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="bg-gray-50 font-black border-t-2">
                            @php
                                $totalO = array_sum(array_column($sales['salesByDay'], 'orders'));
                                $totalV = array_sum(array_column($sales['salesByDay'], 'total'));
                            @endphp
                            <tr>
                                <td class="px-4 py-3 text-gray-700">TOTAL</td>
                                <td class="px-4 py-3 text-right text-gray-700">{{ $totalO }}</td>
                                <td class="px-4 py-3 text-right text-purple-600">{{ number_format($totalV, 0, ',', '.') }}</td>
                                <td class="px-4 py-3 text-right text-gray-500 hidden sm:table-cell">
                                    {{ $totalO > 0 ? number_format($totalV / $totalO, 0, ',', '.') : '—' }}
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        @endif
    @endif

    {{-- ══ PRODUCTOS ══ --}}
    @if($activeTab === 'products' && $products)
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-3">
            <div class="bg-white rounded-2xl shadow-sm border overflow-hidden">
                <div class="px-4 py-3 bg-gray-50 border-b">
                    <h3 class="font-black text-gray-800 text-sm">🏆 Top 10 Productos</h3>
                </div>
                @if(count($products['topByQty']) > 0)
                    @php $maxRev = max(array_column($products['topByQty'], 'total_revenue')); @endphp
                    <div class="divide-y divide-gray-100">
                        @foreach($products['topByQty'] as $i => $p)
                            <div class="px-4 py-3 flex items-center gap-2">
                                <span class="text-base font-black text-gray-300 w-5 flex-shrink-0">{{ $i + 1 }}</span>
                                <div class="flex-1 min-w-0">
                                    <div class="font-bold text-gray-900 text-xs truncate">{{ $p['product_name'] }}</div>
                                    <div class="h-1.5 bg-gray-100 rounded-full mt-1 overflow-hidden">
                                        <div class="h-full bg-gradient-to-r from-purple-400 to-indigo-500 rounded-full"
                                             style="width: {{ $maxRev > 0 ? ($p['total_revenue'] / $maxRev * 100) : 0 }}%"></div>
                                    </div>
                                </div>
                                <div class="text-right flex-shrink-0">
                                    <div class="font-black text-gray-900 text-xs">{{ number_format($p['total_revenue'] / 1000, 0) }}k Gs</div>
                                    <div class="text-xs text-gray-400">× {{ $p['total_qty'] ?? $p['order_count'] }}</div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-center text-gray-400 py-10 text-sm">Sin datos en este período</p>
                @endif
            </div>

            <div class="bg-white rounded-2xl shadow-sm border p-4">
                <h3 class="font-black text-gray-800 mb-3 text-sm">📁 Por Categoría</h3>
                @if(count($products['byCategory']) > 0)
                    <div style="height: 200px">
                        <canvas id="categoryChart"></canvas>
                    </div>
                    <div class="mt-3 space-y-2">
                        @foreach($products['byCategory'] as $cat)
                            <div class="flex justify-between text-xs lg:text-sm">
                                <span class="text-gray-600">{{ $cat['category'] }}</span>
                                <span class="font-bold text-gray-900">{{ number_format($cat['total'] / 1000, 0) }}k Gs</span>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-center text-gray-400 py-10 text-sm">Sin datos en este período</p>
                @endif
            </div>
        </div>
    @endif

    {{-- ══ EGRESOS ══ --}}
    @if($activeTab === 'expenses' && $expenses)
        <div class="grid grid-cols-2 gap-3 mb-3">
            <div class="bg-white rounded-2xl shadow-sm border p-3 text-center col-span-2">
                <div class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">Total Egresos</div>
                <div class="text-2xl font-black text-red-500">{{ number_format($expenses['total'], 0, ',', '.') }} Gs</div>
            </div>
            @foreach($expenses['byType'] as $t)
                <div class="bg-white rounded-2xl shadow-sm border p-3">
                    <div class="text-xs font-bold text-gray-400 mb-1">{{ $t['type'] }}</div>
                    <div class="text-lg font-black text-gray-800">{{ number_format($t['total'] / 1000, 0) }}k Gs</div>
                    <div class="text-xs text-gray-400">{{ $t['count'] }} registro(s)</div>
                </div>
            @endforeach
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-3">
            <div class="bg-white rounded-2xl shadow-sm border p-4">
                <h3 class="font-black text-gray-800 mb-3 text-sm">📊 Por Tipo</h3>
                @if(count($expenses['byType']) > 0)
                    <div style="height: 200px"><canvas id="expenseTypeChart"></canvas></div>
                @else
                    <p class="text-center text-gray-400 py-10 text-sm">Sin egresos en este período</p>
                @endif
            </div>
            <div class="bg-white rounded-2xl shadow-sm border p-4">
                <h3 class="font-black text-gray-800 mb-3 text-sm">📅 Por Día</h3>
                @if(count($expenses['byDay']) > 0)
                    <div style="height: 200px"><canvas id="expenseDayChart"></canvas></div>
                @else
                    <p class="text-center text-gray-400 py-10 text-sm">Sin egresos en este período</p>
                @endif
            </div>
        </div>

        @if(count($expenses['byMethod']) > 0)
            <div class="bg-white rounded-2xl shadow-sm border p-4">
                <h3 class="font-black text-gray-800 mb-3 text-sm">💳 Por Método de Pago</h3>
                <div class="grid grid-cols-3 gap-3">
                    @foreach($expenses['byMethod'] as $m)
                        <div class="text-center bg-gray-50 rounded-xl p-3">
                            <div class="text-sm font-black text-gray-800">{{ $m['method'] }}</div>
                            <div class="text-base font-black text-red-500 mt-1">{{ number_format($m['total'] / 1000, 0) }}k Gs</div>
                            <div class="text-xs text-gray-400">{{ $m['count'] }} reg.</div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    @endif

    {{-- ══ CAJA ══ --}}
    @if($activeTab === 'cash' && $cashData)
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 mb-3">
            <div class="bg-white rounded-2xl shadow-sm border p-3 text-center">
                <div class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">Cajas</div>
                <div class="text-2xl font-black text-gray-900">{{ $cashData['registers']->count() }}</div>
            </div>
            <div class="bg-white rounded-2xl shadow-sm border p-3 text-center">
                <div class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">Ventas</div>
                <div class="text-xl font-black text-green-600">{{ number_format($cashData['totalSales'] / 1000, 0) }}k Gs</div>
            </div>
            <div class="bg-white rounded-2xl shadow-sm border p-3 text-center">
                <div class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">Egresos</div>
                <div class="text-xl font-black text-red-500">{{ number_format($cashData['totalExpenses'] / 1000, 0) }}k Gs</div>
            </div>
            <div class="bg-white rounded-2xl shadow-sm border p-3 text-center">
                <div class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">Dif. prom.</div>
                <div class="text-lg font-black {{ $cashData['avgDiff'] < 5000 ? 'text-green-600' : 'text-yellow-600' }}">
                    {{ number_format($cashData['avgDiff'], 0, ',', '.') }} Gs
                </div>
            </div>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border p-4 mb-3">
            <h3 class="font-black text-gray-800 mb-3 text-sm">🎯 Resultado de Arqueos</h3>
            <div class="grid grid-cols-3 gap-2">
                <div class="text-center bg-green-50 rounded-xl p-3">
                    <div class="text-2xl font-black text-green-600">{{ $cashData['exactCount'] }}</div>
                    <div class="text-xs text-green-700 font-bold">✓ Exacto</div>
                </div>
                <div class="text-center bg-red-50 rounded-xl p-3">
                    <div class="text-2xl font-black text-red-600">{{ $cashData['shortCount'] }}</div>
                    <div class="text-xs text-red-700 font-bold">↓ Faltante</div>
                </div>
                <div class="text-center bg-blue-50 rounded-xl p-3">
                    <div class="text-2xl font-black text-blue-600">{{ $cashData['overCount'] }}</div>
                    <div class="text-xs text-blue-700 font-bold">↑ Sobrante</div>
                </div>
            </div>
        </div>

        @if($cashData['registers']->count() > 0)
            <div class="bg-white rounded-2xl shadow-sm border overflow-hidden">
                <div class="px-4 py-3 bg-gray-50 border-b">
                    <h3 class="font-black text-gray-800 text-sm">📋 Detalle de Cajas</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-xs">
                        <thead class="bg-gray-50 text-xs text-gray-500 uppercase tracking-wider">
                            <tr>
                                <th class="px-3 py-3 text-left">Fecha</th>
                                <th class="px-3 py-3 text-left">Cajero</th>
                                <th class="px-3 py-3 text-right">Ventas</th>
                                <th class="px-3 py-3 text-right">Egresos</th>
                                <th class="px-3 py-3 text-right">Diferencia</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach($cashData['registers'] as $reg)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-3 py-2.5 text-gray-600">
                                        {{ $reg->opened_at->format('d/m/Y') }}<br>
                                        <span class="text-gray-400">{{ $reg->opened_at->format('H:i') }}</span>
                                    </td>
                                    <td class="px-3 py-2.5 text-gray-700 font-medium">{{ $reg->opener->name ?? '—' }}</td>
                                    <td class="px-3 py-2.5 text-right font-bold text-green-600">+{{ number_format($reg->total_sales / 1000, 0) }}k</td>
                                    <td class="px-3 py-2.5 text-right font-bold text-red-500">-{{ number_format($reg->total_expenses / 1000, 0) }}k</td>
                                    <td class="px-3 py-2.5 text-right font-bold
                                        {{ $reg->difference == 0 ? 'text-green-600' : ($reg->difference > 0 ? 'text-blue-600' : 'text-red-600') }}">
                                        {{ $reg->difference > 0 ? '+' : '' }}{{ number_format($reg->difference, 0, ',', '.') }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @else
            <div class="bg-white rounded-2xl shadow-sm border p-10 text-center text-gray-400">
                <div class="text-4xl mb-2">🏦</div>
                <p class="font-medium text-sm">No hay cajas cerradas en este período</p>
            </div>
        @endif
    @endif
{{-- ══ CHARTS ══ --}}

{{-- 1️⃣ Primero carga Chart.js --}}
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.min.js"></script>

{{-- 2️⃣ Luego tu script (ya puede referenciar Chart) --}}
<script>
    window.__chartData = {};

    @if(in_array($activeTab, ['overview','sales']) && $sales)
        @if(count($sales['salesByDay']) > 0)
        window.__chartData.salesByDay = {!! json_encode($sales['salesByDay']) !!};
        @endif
        @if(count($sales['byChannel']) > 0)
        window.__chartData.byChannel = {!! json_encode($sales['byChannel']) !!};
        @endif
        @if(count($sales['byPayment']) > 0)
        window.__chartData.byPayment = {!! json_encode($sales['byPayment']) !!};
        @endif
        @if($activeTab === 'sales' && count($sales['byHour']) > 0)
        window.__chartData.byHour = {!! json_encode($sales['byHour']) !!};
        @endif
    @endif

    @if($activeTab === 'products' && $products)
        @if(count($products['byCategory']) > 0)
        window.__chartData.byCategory = {!! json_encode($products['byCategory']) !!};
        @endif
    @endif

    @if($activeTab === 'expenses' && $expenses)
        @if(count($expenses['byType']) > 0)
        window.__chartData.byType = {!! json_encode($expenses['byType']) !!};
        @endif
        @if(count($expenses['byDay']) > 0)
        window.__chartData.byDay = {!! json_encode($expenses['byDay']) !!};
        @endif
    @endif

    function initCharts() {
        const C = ['#6366f1','#8b5cf6','#a78bfa','#c4b5fd','#10b981','#f59e0b','#ef4444','#3b82f6'];
        const d = window.__chartData || {};

        function bar(id, labels, values, color) {
            const el = document.getElementById(id);
            if (!el) return;
            const ex = Chart.getChart(el); if (ex) ex.destroy();
            new Chart(el, {
                type: 'bar',
                data: { labels, datasets: [{ data: values, backgroundColor: color + 'bb', borderRadius: 6 }] },
                options: {
                    responsive: true, maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            callbacks: {
                                label: ctx => new Intl.NumberFormat('es-PY').format(ctx.parsed.y) + ' Gs'
                            }
                        }
                    },
                    scales: {
                        y: {
                            ticks: {
                                callback: v => new Intl.NumberFormat('es-PY').format(v) + ' Gs',
                                font: { size: 10 }
                            },
                            grid: { color: '#f3f4f6' }
                        },
                        x: { ticks: { font: { size: 9 }, maxRotation: 45 } }
                    }
                }
            });
        }

        function donut(id, labels, values) {
            const el = document.getElementById(id);
            if (!el) return;
            const ex = Chart.getChart(el); if (ex) ex.destroy();
            new Chart(el, {
                type: 'doughnut',
                data: { labels, datasets: [{ data: values, backgroundColor: C, borderWidth: 2, borderColor: '#fff' }] },
                options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } }, cutout: '60%' }
            });
        }

        if (d.salesByDay)  bar('salesChart',        d.salesByDay.map(x=>x.date),      d.salesByDay.map(x=>x.total),    '#6366f1');
        if (d.byChannel)   donut('channelChart',     d.byChannel.map(x=>x.source),     d.byChannel.map(x=>x.total));
        if (d.byPayment)   donut('paymentChart',     d.byPayment.map(x=>x.name),       d.byPayment.map(x=>x.total));
        if (d.byHour)      bar('hourChart',          d.byHour.map(x=>x.hour),          d.byHour.map(x=>x.orders),       '#8b5cf6');
        if (d.byCategory)  donut('categoryChart',    d.byCategory.map(x=>x.category),  d.byCategory.map(x=>x.total));
        if (d.byType)      donut('expenseTypeChart', d.byType.map(x=>x.type),          d.byType.map(x=>x.total));
        if (d.byDay)       bar('expenseDayChart',    d.byDay.map(x=>x.date),           d.byDay.map(x=>x.total),         '#ef4444');
    }

    // Re-render tras cada commit de Livewire
    document.addEventListener('livewire:init', () => {
        Livewire.hook('commit', ({ succeed }) => {
            succeed(() => setTimeout(initCharts, 100));
        });
    });

    // Primera carga (Chart.js ya está disponible porque va antes)
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', () => setTimeout(initCharts, 100));
    } else {
        setTimeout(initCharts, 100);
    }
</script>
    

</div>