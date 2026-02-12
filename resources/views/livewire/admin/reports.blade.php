<div x-data="reportsApp()" x-init="init()" class="space-y-5">

    {{-- ══ HEADER + CONTROLES DE FECHA ══ --}}
    <div class="bg-gradient-to-r from-indigo-600 to-purple-700 rounded-2xl p-5 text-white">
        <div class="flex flex-col lg:flex-row justify-between gap-4">
            <div>
                <h1 class="text-2xl font-black">📊 Reportes</h1>
                <p class="text-indigo-200 text-sm mt-0.5">
                    {{ \Carbon\Carbon::parse($dateFrom)->format('d/m/Y') }}
                    @if($dateFrom !== $dateTo) — {{ \Carbon\Carbon::parse($dateTo)->format('d/m/Y') }} @endif
                </p>
            </div>
            <div class="flex flex-wrap gap-2 items-center">
                {{-- Presets --}}
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
                        class="text-xs px-3 py-1.5 rounded-lg font-bold transition-all
                            {{ $preset === $key ? 'bg-white text-indigo-700' : 'bg-white/20 hover:bg-white/30 text-white' }}">
                        {{ $label }}
                    </button>
                @endforeach
            </div>
        </div>

        {{-- Rango personalizado --}}
        <div class="flex flex-wrap gap-3 mt-4 items-center">
            <input wire:model.live="dateFrom" type="date"
                class="px-3 py-2 rounded-xl border-0 text-gray-800 text-sm font-medium bg-white/90">
            <span class="text-indigo-200 font-bold">→</span>
            <input wire:model.live="dateTo" type="date"
                class="px-3 py-2 rounded-xl border-0 text-gray-800 text-sm font-medium bg-white/90">

            <label class="flex items-center gap-2 cursor-pointer ml-2">
                <input wire:model.live="showComparison" type="checkbox"
                    class="w-4 h-4 rounded text-indigo-600">
                <span class="text-sm text-indigo-200 font-medium">Comparar con período anterior</span>
            </label>

            @if($showComparison && $compareFrom)
                <span class="text-xs bg-white/20 px-3 py-1 rounded-lg text-indigo-100">
                    vs {{ \Carbon\Carbon::parse($compareFrom)->format('d/m') }}
                    — {{ \Carbon\Carbon::parse($compareTo)->format('d/m/Y') }}
                </span>
            @endif
        </div>
    </div>

    {{-- ══ PESTAÑAS ══ --}}
    <div class="flex gap-2 border-b border-gray-200 pb-0">
        @foreach([
            'overview' => '📈 Resumen',
            'sales'    => '💰 Ventas',
            'products' => '🍨 Productos',
            'expenses' => '💸 Egresos',
            'cash'     => '🏦 Caja',
        ] as $tab => $label)
            <button wire:click="$set('activeTab', '{{ $tab }}')"
                class="px-4 py-2.5 font-bold text-sm rounded-t-xl transition-all border-b-2
                    {{ $activeTab === $tab
                        ? 'border-indigo-500 text-indigo-700 bg-indigo-50'
                        : 'border-transparent text-gray-500 hover:text-gray-700 hover:bg-gray-50' }}">
                {{ $label }}
            </button>
        @endforeach
    </div>

    {{-- ══ TARJETAS RESUMEN (siempre visibles) ══ --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        {{-- Ventas totales --}}
        <div class="bg-white rounded-2xl shadow-sm border p-4">
            <div class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">Ventas Totales</div>
            <div class="text-2xl font-black text-gray-900">
                {{ number_format($overview['totalSales'] / 1000, 0) }}k
                <span class="text-sm font-medium text-gray-400">Gs</span>
            </div>
            <div class="text-xs text-gray-500 mt-1">{{ $overview['totalOrders'] }} pedidos</div>
            @if($overview['comparison'])
                @php $d = $overview['comparison']['sales_diff']; @endphp
                <div class="text-xs mt-1 font-bold {{ $d >= 0 ? 'text-green-600' : 'text-red-500' }}">
                    {{ $d >= 0 ? '↑' : '↓' }} {{ abs(round($d, 1)) }}% vs anterior
                </div>
            @endif
        </div>

        {{-- Neto (sin comisiones app) --}}
        <div class="bg-white rounded-2xl shadow-sm border p-4">
            <div class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">Neto Recibido</div>
            <div class="text-2xl font-black text-purple-600">
                {{ number_format($overview['netSales'] / 1000, 0) }}k
                <span class="text-sm font-medium text-purple-300">Gs</span>
            </div>
            @if($overview['totalCommissions'] > 0)
                <div class="text-xs text-orange-500 mt-1">
                    -{{ number_format($overview['totalCommissions'], 0, ',', '.') }} Gs comisiones
                </div>
            @endif
        </div>

        {{-- Egresos --}}
        <div class="bg-white rounded-2xl shadow-sm border p-4">
            <div class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">Egresos</div>
            <div class="text-2xl font-black text-red-500">
                {{ number_format($overview['totalExpenses'] / 1000, 0) }}k
                <span class="text-sm font-medium text-red-300">Gs</span>
            </div>
            @if($overview['comparison'])
                @php $d = $overview['comparison']['expenses_diff']; @endphp
                <div class="text-xs mt-1 font-bold {{ $d <= 0 ? 'text-green-600' : 'text-red-500' }}">
                    {{ $d >= 0 ? '↑' : '↓' }} {{ abs(round($d, 1)) }}% vs anterior
                </div>
            @endif
        </div>

        {{-- Resultado neto --}}
        <div class="bg-white rounded-2xl shadow-sm border p-4">
            <div class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">Resultado</div>
            <div class="text-2xl font-black {{ $overview['netResult'] >= 0 ? 'text-green-600' : 'text-red-600' }}">
                {{ $overview['netResult'] >= 0 ? '' : '-' }}{{ number_format(abs($overview['netResult']) / 1000, 0) }}k
                <span class="text-sm font-medium">Gs</span>
            </div>
            <div class="text-xs text-gray-500 mt-1">
                Ticket promedio: {{ number_format($overview['avgTicket'], 0, ',', '.') }} Gs
            </div>
        </div>
    </div>

    {{-- ══ PESTAÑA RESUMEN / VENTAS ══ --}}
    @if(in_array($activeTab, ['overview', 'sales']) && $sales)

        {{-- Gráfico de ventas por día --}}
        @if(count($sales['salesByDay']) > 0)
            <div class="bg-white rounded-2xl shadow-sm border p-5">
                <h3 class="font-black text-gray-800 mb-4">📈 Ventas por Día</h3>
                <div style="height: 220px">
                    <canvas id="salesChart"></canvas>
                </div>
                <script>
                    window.__salesData = @json($sales['salesByDay']);
                </script>
            </div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">

            {{-- Por canal --}}
            <div class="bg-white rounded-2xl shadow-sm border p-5">
                <h3 class="font-black text-gray-800 mb-4">📡 Por Canal de Venta</h3>
                @if(count($sales['byChannel']) > 0)
                    <div class="grid grid-cols-2 gap-2 mb-4">
                        <div style="height: 180px">
                            <canvas id="channelChart"></canvas>
                        </div>
                        <div class="space-y-2">
                            @foreach($sales['byChannel'] as $ch)
                                <div class="flex justify-between items-center text-sm">
                                    <span class="font-medium text-gray-700">{{ $ch['source'] }}</span>
                                    <div class="text-right">
                                        <div class="font-black text-gray-900">{{ number_format($ch['total'], 0, ',', '.') }} Gs</div>
                                        <div class="text-xs text-gray-400">{{ $ch['orders'] }} pedidos</div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                    <script>window.__channelData = @json($sales['byChannel']);</script>
                @else
                    <p class="text-gray-400 text-sm text-center py-8">Sin datos en este período</p>
                @endif
            </div>

            {{-- Por método de pago --}}
            <div class="bg-white rounded-2xl shadow-sm border p-5">
                <h3 class="font-black text-gray-800 mb-4">💳 Por Método de Pago</h3>
                @if(count($sales['byPayment']) > 0)
                    <div class="grid grid-cols-2 gap-2 mb-4">
                        <div style="height: 180px">
                            <canvas id="paymentChart"></canvas>
                        </div>
                        <div class="space-y-2">
                            @foreach($sales['byPayment'] as $pm)
                                <div class="flex justify-between items-center text-sm">
                                    <span class="font-medium text-gray-700">{{ $pm['name'] }}</span>
                                    <div class="text-right">
                                        <div class="font-black text-gray-900">{{ number_format($pm['total'], 0, ',', '.') }} Gs</div>
                                        <div class="text-xs text-gray-400">{{ $pm['orders'] }} ventas</div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                    <script>window.__paymentData = @json($sales['byPayment']);</script>
                @else
                    <p class="text-gray-400 text-sm text-center py-8">Sin datos en este período</p>
                @endif
            </div>
        </div>

        {{-- Picos por hora --}}
        @if($activeTab === 'sales' && count($sales['byHour']) > 0)
            <div class="bg-white rounded-2xl shadow-sm border p-5">
                <h3 class="font-black text-gray-800 mb-4">⏰ Picos de Venta por Hora</h3>
                <div style="height: 200px">
                    <canvas id="hourChart"></canvas>
                </div>
                <script>window.__hourData = @json($sales['byHour']);</script>
            </div>
        @endif

        {{-- Tabla de ventas por día --}}
        @if($activeTab === 'sales' && count($sales['salesByDay']) > 0)
            <div class="bg-white rounded-2xl shadow-sm border overflow-hidden">
                <div class="px-5 py-3 bg-gray-50 border-b">
                    <h3 class="font-black text-gray-800">📋 Detalle por Día</h3>
                </div>
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 text-xs text-gray-500 uppercase tracking-wider">
                        <tr>
                            <th class="px-5 py-3 text-left">Fecha</th>
                            <th class="px-5 py-3 text-right">Pedidos</th>
                            <th class="px-5 py-3 text-right">Total Gs</th>
                            <th class="px-5 py-3 text-right">Ticket prom.</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($sales['salesByDay'] as $day)
                            <tr class="hover:bg-gray-50">
                                <td class="px-5 py-3 font-medium text-gray-700">{{ $day['date'] }}</td>
                                <td class="px-5 py-3 text-right text-gray-600">{{ $day['orders'] }}</td>
                                <td class="px-5 py-3 text-right font-bold text-gray-900">{{ number_format($day['total'], 0, ',', '.') }}</td>
                                <td class="px-5 py-3 text-right text-gray-500">
                                    {{ $day['orders'] > 0 ? number_format($day['total'] / $day['orders'], 0, ',', '.') : '—' }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="bg-gray-50 font-black border-t-2">
                        <tr>
                            <td class="px-5 py-3 text-gray-700">TOTAL</td>
                            <td class="px-5 py-3 text-right text-gray-700">{{ array_sum(array_column($sales['salesByDay'], 'orders')) }}</td>
                            <td class="px-5 py-3 text-right text-purple-600">{{ number_format(array_sum(array_column($sales['salesByDay'], 'total')), 0, ',', '.') }}</td>
                            <td class="px-5 py-3 text-right text-gray-500">
                                @php $totalO = array_sum(array_column($sales['salesByDay'], 'orders')); $totalV = array_sum(array_column($sales['salesByDay'], 'total')); @endphp
                                {{ $totalO > 0 ? number_format($totalV / $totalO, 0, ',', '.') : '—' }}
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        @endif
    @endif

    {{-- ══ PESTAÑA PRODUCTOS ══ --}}
    @if($activeTab === 'products' && $products)
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">

            {{-- Top productos --}}
            <div class="bg-white rounded-2xl shadow-sm border overflow-hidden">
                <div class="px-5 py-3 bg-gray-50 border-b">
                    <h3 class="font-black text-gray-800">🏆 Top 10 Productos</h3>
                </div>
                @if(count($products['topByQty']) > 0)
                    @php $maxRev = max(array_column($products['topByQty'], 'total_revenue')); @endphp
                    <div class="divide-y divide-gray-100">
                        @foreach($products['topByQty'] as $i => $p)
                            <div class="px-5 py-3 flex items-center gap-3">
                                <span class="text-lg font-black text-gray-300 w-6">{{ $i + 1 }}</span>
                                <div class="flex-1">
                                    <div class="font-bold text-gray-900 text-sm">{{ $p['product_name'] }}</div>
                                    <div class="h-1.5 bg-gray-100 rounded-full mt-1 overflow-hidden">
                                        <div class="h-full bg-gradient-to-r from-purple-400 to-indigo-500 rounded-full"
                                             style="width: {{ $maxRev > 0 ? ($p['total_revenue'] / $maxRev * 100) : 0 }}%"></div>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <div class="font-black text-gray-900 text-sm">{{ number_format($p['total_revenue'], 0, ',', '.') }} Gs</div>
                                    <div class="text-xs text-gray-400">× {{ $p['total_qty'] ?? $p['order_count'] }}</div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-center text-gray-400 py-12">Sin datos en este período</p>
                @endif
            </div>

            {{-- Por categoría --}}
            <div class="bg-white rounded-2xl shadow-sm border p-5">
                <h3 class="font-black text-gray-800 mb-4">📁 Por Categoría</h3>
                @if(count($products['byCategory']) > 0)
                    <div style="height: 220px">
                        <canvas id="categoryChart"></canvas>
                    </div>
                    <script>window.__categoryData = @json($products['byCategory']);</script>
                    <div class="mt-4 space-y-2">
                        @foreach($products['byCategory'] as $cat)
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-600">{{ $cat['category'] }}</span>
                                <span class="font-bold text-gray-900">{{ number_format($cat['total'], 0, ',', '.') }} Gs</span>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-center text-gray-400 py-12">Sin datos en este período</p>
                @endif
            </div>
        </div>
    @endif

    {{-- ══ PESTAÑA EGRESOS ══ --}}
    @if($activeTab === 'expenses' && $expenses)
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 mb-4">
            <div class="bg-white rounded-2xl shadow-sm border p-4 text-center">
                <div class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">Total Egresos</div>
                <div class="text-2xl font-black text-red-500">{{ number_format($expenses['total'], 0, ',', '.') }} Gs</div>
            </div>
            @foreach($expenses['byType'] as $t)
                <div class="bg-white rounded-2xl shadow-sm border p-4">
                    <div class="text-xs font-bold text-gray-400 mb-1">{{ $t['type'] }}</div>
                    <div class="text-xl font-black text-gray-800">{{ number_format($t['total'], 0, ',', '.') }} Gs</div>
                    <div class="text-xs text-gray-400">{{ $t['count'] }} registro(s)</div>
                </div>
            @endforeach
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
            {{-- Gráfico por tipo --}}
            <div class="bg-white rounded-2xl shadow-sm border p-5">
                <h3 class="font-black text-gray-800 mb-4">📊 Distribución por Tipo</h3>
                @if(count($expenses['byType']) > 0)
                    <div style="height: 220px">
                        <canvas id="expenseTypeChart"></canvas>
                    </div>
                    <script>window.__expenseTypeData = @json($expenses['byType']);</script>
                @else
                    <p class="text-center text-gray-400 py-12">Sin egresos en este período</p>
                @endif
            </div>

            {{-- Egresos por día --}}
            <div class="bg-white rounded-2xl shadow-sm border p-5">
                <h3 class="font-black text-gray-800 mb-4">📅 Egresos por Día</h3>
                @if(count($expenses['byDay']) > 0)
                    <div style="height: 220px">
                        <canvas id="expenseDayChart"></canvas>
                    </div>
                    <script>window.__expenseDayData = @json($expenses['byDay']);</script>
                @else
                    <p class="text-center text-gray-400 py-12">Sin egresos en este período</p>
                @endif
            </div>
        </div>

        {{-- Método de pago de egresos --}}
        @if(count($expenses['byMethod']) > 0)
            <div class="bg-white rounded-2xl shadow-sm border p-5">
                <h3 class="font-black text-gray-800 mb-3">💳 Por Método de Pago</h3>
                <div class="grid grid-cols-3 gap-4">
                    @foreach($expenses['byMethod'] as $m)
                        <div class="text-center bg-gray-50 rounded-xl p-4">
                            <div class="text-lg font-black text-gray-800">{{ $m['method'] }}</div>
                            <div class="text-xl font-black text-red-500 mt-1">{{ number_format($m['total'], 0, ',', '.') }} Gs</div>
                            <div class="text-xs text-gray-400">{{ $m['count'] }} registros</div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    @endif

    {{-- ══ PESTAÑA CAJA ══ --}}
    @if($activeTab === 'cash' && $cashData)
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-4">
            <div class="bg-white rounded-2xl shadow-sm border p-4 text-center">
                <div class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">Cajas Cerradas</div>
                <div class="text-2xl font-black text-gray-900">{{ $cashData['registers']->count() }}</div>
            </div>
            <div class="bg-white rounded-2xl shadow-sm border p-4 text-center">
                <div class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">Total Ventas</div>
                <div class="text-2xl font-black text-green-600">{{ number_format($cashData['totalSales'] / 1000, 0) }}k Gs</div>
            </div>
            <div class="bg-white rounded-2xl shadow-sm border p-4 text-center">
                <div class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">Total Egresos</div>
                <div class="text-2xl font-black text-red-500">{{ number_format($cashData['totalExpenses'] / 1000, 0) }}k Gs</div>
            </div>
            <div class="bg-white rounded-2xl shadow-sm border p-4 text-center">
                <div class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">Diferencia prom.</div>
                <div class="text-xl font-black {{ $cashData['avgDiff'] < 5000 ? 'text-green-600' : 'text-yellow-600' }}">
                    {{ number_format($cashData['avgDiff'], 0, ',', '.') }} Gs
                </div>
            </div>
        </div>

        {{-- Arqueos --}}
        <div class="bg-white rounded-2xl shadow-sm border p-4 mb-4">
            <h3 class="font-black text-gray-800 mb-3">🎯 Resultado de Arqueos</h3>
            <div class="grid grid-cols-3 gap-3">
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

        {{-- Tabla de cajas --}}
        @if($cashData['registers']->count() > 0)
            <div class="bg-white rounded-2xl shadow-sm border overflow-hidden">
                <div class="px-5 py-3 bg-gray-50 border-b">
                    <h3 class="font-black text-gray-800">📋 Detalle de Cajas</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50 text-xs text-gray-500 uppercase tracking-wider">
                            <tr>
                                <th class="px-4 py-3 text-left">Fecha</th>
                                <th class="px-4 py-3 text-left">Cajero</th>
                                <th class="px-4 py-3 text-right">Apertura</th>
                                <th class="px-4 py-3 text-right">Ventas</th>
                                <th class="px-4 py-3 text-right">Egresos</th>
                                <th class="px-4 py-3 text-right">Esperado</th>
                                <th class="px-4 py-3 text-right">Contado</th>
                                <th class="px-4 py-3 text-right">Diferencia</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach($cashData['registers'] as $reg)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-4 py-3 text-gray-600 text-xs">
                                        {{ $reg->opened_at->format('d/m/Y') }}<br>
                                        <span class="text-gray-400">{{ $reg->opened_at->format('H:i') }} — {{ $reg->closed_at?->format('H:i') }}</span>
                                    </td>
                                    <td class="px-4 py-3 text-gray-700 font-medium">{{ $reg->opener->name ?? '—' }}</td>
                                    <td class="px-4 py-3 text-right text-gray-600">{{ number_format($reg->opening_amount, 0, ',', '.') }}</td>
                                    <td class="px-4 py-3 text-right font-bold text-green-600">+{{ number_format($reg->total_sales, 0, ',', '.') }}</td>
                                    <td class="px-4 py-3 text-right font-bold text-red-500">-{{ number_format($reg->total_expenses, 0, ',', '.') }}</td>
                                    <td class="px-4 py-3 text-right font-bold text-gray-700">{{ number_format($reg->expected_amount, 0, ',', '.') }}</td>
                                    <td class="px-4 py-3 text-right font-bold text-gray-900">{{ number_format($reg->closing_amount, 0, ',', '.') }}</td>
                                    <td class="px-4 py-3 text-right font-bold
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
            <div class="bg-white rounded-2xl shadow-sm border p-12 text-center text-gray-400">
                <div class="text-4xl mb-2">🏦</div>
                <p class="font-medium">No hay cajas cerradas en este período</p>
            </div>
        @endif
    @endif

    {{-- Chart.js --}}
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.min.js"></script>
    <script>
        const COLORS = ['#6366f1','#8b5cf6','#a78bfa','#c4b5fd','#10b981','#f59e0b','#ef4444','#3b82f6'];

        function makeBarChart(id, labels, values, label = 'Total', color = '#6366f1') {
            const el = document.getElementById(id);
            if (!el) return;
            if (Chart.getChart(el)) Chart.getChart(el).destroy();
            new Chart(el, {
                type: 'bar',
                data: {
                    labels,
                    datasets: [{ label, data: values, backgroundColor: color + 'cc', borderRadius: 6 }]
                },
                options: {
                    responsive: true, maintainAspectRatio: false,
                    plugins: { legend: { display: false } },
                    scales: {
                        y: { ticks: { callback: v => (v/1000).toFixed(0) + 'k', font: { size: 11 } }, grid: { color: '#f3f4f6' } },
                        x: { ticks: { font: { size: 10 } } }
                    }
                }
            });
        }

        function makeDoughnut(id, labels, values) {
            const el = document.getElementById(id);
            if (!el) return;
            if (Chart.getChart(el)) Chart.getChart(el).destroy();
            new Chart(el, {
                type: 'doughnut',
                data: {
                    labels,
                    datasets: [{ data: values, backgroundColor: COLORS, borderWidth: 2, borderColor: '#fff' }]
                },
                options: {
                    responsive: true, maintainAspectRatio: false,
                    plugins: { legend: { display: false } },
                    cutout: '60%'
                }
            });
        }

        function initCharts() {
            if (window.__salesData?.length)
                makeBarChart('salesChart',
                    window.__salesData.map(d => d.date),
                    window.__salesData.map(d => d.total),
                    'Ventas Gs', '#6366f1');

            if (window.__channelData?.length)
                makeDoughnut('channelChart',
                    window.__channelData.map(d => d.source),
                    window.__channelData.map(d => d.total));

            if (window.__paymentData?.length)
                makeDoughnut('paymentChart',
                    window.__paymentData.map(d => d.name),
                    window.__paymentData.map(d => d.total));

            if (window.__hourData?.length)
                makeBarChart('hourChart',
                    window.__hourData.map(d => d.hour),
                    window.__hourData.map(d => d.orders),
                    'Pedidos', '#8b5cf6');

            if (window.__categoryData?.length)
                makeDoughnut('categoryChart',
                    window.__categoryData.map(d => d.category),
                    window.__categoryData.map(d => d.total));

            if (window.__expenseTypeData?.length)
                makeDoughnut('expenseTypeChart',
                    window.__expenseTypeData.map(d => d.type),
                    window.__expenseTypeData.map(d => d.total));

            if (window.__expenseDayData?.length)
                makeBarChart('expenseDayChart',
                    window.__expenseDayData.map(d => d.date),
                    window.__expenseDayData.map(d => d.total),
                    'Egresos Gs', '#ef4444');
        }

        // Inicializar después de cada render de Livewire
        document.addEventListener('DOMContentLoaded', () => setTimeout(initCharts, 100));
        document.addEventListener('livewire:navigated', () => setTimeout(initCharts, 100));
        Livewire.hook('commit', ({ succeed }) => { succeed(() => setTimeout(initCharts, 150)) });
    </script>

</div>
