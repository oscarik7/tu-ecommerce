<div class="min-h-screen bg-gray-50 p-4 lg:p-6">

    {{-- ══ HEADER ══════════════════════════════════════════════ --}}
    <div class="mb-5">
        <div class="rounded-2xl p-5 lg:p-6 shadow-sm"
            style="background: linear-gradient(135deg, #6d28d9 0%, #7c3aed 50%, #8b5cf6 100%); border: 1px solid rgba(109,40,217,0.3);">
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                <div>
                    <div class="flex items-center gap-2.5 mb-1">
                        <div class="w-9 h-9 rounded-xl flex items-center justify-center text-lg bg-white/20">📋</div>
                        <h1 class="text-2xl font-bold text-white tracking-tight">Historial de Actividad</h1>
                    </div>
                    <p class="text-purple-100 text-sm ml-11">Todo lo que ocurre en el sistema queda registrado aquí</p>
                </div>
                <div class="flex gap-2.5 ml-11 sm:ml-0">
                    @foreach([['valor' => $stats['today'], 'label' => 'Hoy'], ['valor' => $stats['week'], 'label' => 'Semana'], ['valor' => $stats['total'], 'label' => 'Total']] as $stat)
                        <div class="rounded-xl px-4 py-2.5 text-center min-w-[64px] bg-white/20">
                            <div class="text-xl font-bold text-white leading-none">{{ $stat['valor'] }}</div>
                            <div class="text-xs text-purple-100 mt-0.5">{{ $stat['label'] }}</div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    {{-- ══ FILTROS ══════════════════════════════════════════════ --}}
    <div class="bg-white rounded-2xl p-4 mb-4 shadow-sm border border-gray-100">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
            <div class="sm:col-span-2">
                <div class="relative">
                    <span class="absolute left-3 top-2.5 text-gray-400 text-sm">🔍</span>
                    <input wire:model.live.debounce.300ms="search" type="text"
                        placeholder="Buscar por acción o usuario..."
                        class="w-full pl-9 pr-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-gray-900 placeholder-gray-400 focus:ring-2 focus:ring-purple-500 focus:outline-none focus:bg-white text-sm transition">
                </div>
            </div>
            <div>
                <select wire:model.live="filterLog"
                    class="w-full px-3 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-gray-700 focus:ring-2 focus:ring-purple-500 text-sm">
                    <option value="">📂 Todas las secciones</option>
                    <option value="pedidos">🛒 Pedidos</option>
                    <option value="productos">🍨 Productos</option>
                    <option value="caja">🏦 Caja</option>
                    <option value="egresos">💸 Egresos</option>
                    <option value="empleados">👥 Empleados</option>
                    <option value="inventario">📦 Inventario</option>
                </select>
            </div>
            <div>
                <select wire:model.live="filterDate"
                    class="w-full px-3 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-gray-700 focus:ring-2 focus:ring-purple-500 text-sm">
                    <option value="">📅 Todo el tiempo</option>
                    <option value="today">Hoy</option>
                    <option value="week">Esta semana</option>
                    <option value="month">Este mes</option>
                </select>
            </div>
        </div>
        @if($search || $filterLog || $filterDate)
            <div class="mt-3 flex justify-end">
                <button wire:click="clearFilters"
                    class="text-xs text-purple-600 hover:text-purple-800 flex items-center gap-1.5 transition-colors px-3 py-1.5 rounded-lg hover:bg-purple-50 font-medium">
                    ✕ Limpiar filtros
                </button>
            </div>
        @endif
    </div>

    {{-- ══ HELPERS PHP ══════════════════════════════════════════ --}}
    @php
        $logConfig = [
            'pedidos'    => ['icon' => '🛒', 'bg' => 'bg-blue-100',   'text' => 'text-blue-700'],
            'productos'  => ['icon' => '🍨', 'bg' => 'bg-purple-100', 'text' => 'text-purple-700'],
            'caja'       => ['icon' => '🏦', 'bg' => 'bg-green-100',  'text' => 'text-green-700'],
            'egresos'    => ['icon' => '💸', 'bg' => 'bg-red-100',    'text' => 'text-red-700'],
            'empleados'  => ['icon' => '👥', 'bg' => 'bg-orange-100', 'text' => 'text-orange-700'],
            'inventario' => ['icon' => '📦', 'bg' => 'bg-yellow-100', 'text' => 'text-yellow-700'],
        ];

        // Etiquetas en español para los campos
        $fieldLabels = [
            'status'          => 'Estado',
            'payment_status'  => 'Pago',
            'total'           => 'Total',
            'delivery_type'   => 'Tipo de entrega',
            'customer_name'   => 'Cliente',
            'name'            => 'Nombre',
            'price'           => 'Precio',
            'is_active'       => 'Activo',
            'stock'           => 'Stock',
            'amount'          => 'Monto',
            'description'     => 'Descripción',
            'salary'          => 'Salario',
            'position'        => 'Cargo',
            'opening_amount'  => 'Monto de apertura',
            'closing_amount'  => 'Monto de cierre',
            'notes'           => 'Notas',
            'expense_date'    => 'Fecha del egreso',
            'expense_type'    => 'Tipo de egreso',
            'payment_method'  => 'Método de pago',
            'category_id'     => 'Categoría',
            'phone'           => 'Teléfono',
            'address'         => 'Dirección',
            'email'           => 'Correo',
            'quantity'        => 'Cantidad',
            'discount'        => 'Descuento',
            'opened_at'       => 'Apertura',
            'closed_at'       => 'Cierre',
        ];

        // Campos que son montos en guaraníes
        $moneyFields = ['total', 'price', 'amount', 'salary', 'opening_amount', 'closing_amount', 'discount'];

        // Campos que son fechas
        $dateFields = ['expense_date', 'opened_at', 'closed_at', 'created_at', 'updated_at'];

        // Campos que son booleanos
        $boolFields = ['is_active'];

        // Campos a ocultar en el detalle de creación
        $hiddenFields = ['id', 'created_at', 'updated_at', 'slug', 'image', 'image_hash', 'password', 'remember_token', 'deleted_at'];

        // Traducciones de valores
        $valueTranslations = [
            // Estados de pedido
            'pending'    => 'Pendiente',
            'confirmed'  => 'Confirmado',
            'preparing'  => 'Preparando',
            'ready'      => 'Listo',
            'delivered'  => 'Entregado',
            'cancelled'  => 'Cancelado',
            // Estados de pago
            'paid'       => 'Pagado',
            'failed'     => 'Fallido',
            'refunded'   => 'Reembolsado',
            'unpaid'     => 'Sin pagar',
            // Tipo de entrega
            'pickup'     => 'Retiro en local',
            'delivery'   => 'Delivery',
            // Tipo de egreso
            'insumo'     => 'Insumo',
            'servicio'   => 'Servicio',
            'salario'    => 'Salario',
            'otro'       => 'Otro',
            // Booleanos
            '1'          => 'Sí',
            '0'          => 'No',
            'true'       => 'Sí',
            'false'      => 'No',
        ];

        /**
         * Formatea un valor según el tipo de campo
         */
        $formatValue = function($key, $val) use ($moneyFields, $dateFields, $boolFields, $valueTranslations) {
            if (is_null($val) || $val === '') return '—';

            // Monto en guaraníes
            if (in_array($key, $moneyFields) && is_numeric($val)) {
                return number_format((float)$val, 0, ',', '.') . ' Gs';
            }

            // Fecha/datetime
            if (in_array($key, $dateFields) || (is_string($val) && preg_match('/^\d{4}-\d{2}-\d{2}[T ]/', $val))) {
                try {
                    return \Carbon\Carbon::parse($val)->format('d/m/Y H:i');
                } catch (\Exception $e) {
                    return $val;
                }
            }

            // Booleano
            if (in_array($key, $boolFields)) {
                return $val ? 'Sí' : 'No';
            }

            // Traducción de valores conocidos
            $strVal = (string)$val;
            if (isset($valueTranslations[$strVal])) {
                return $valueTranslations[$strVal];
            }

            return $strVal;
        };
    @endphp

    {{-- ══ LISTA ══════════════════════════════════════════════ --}}
    @forelse($activities as $activity)
        @php
            $cfg = $logConfig[$activity->log_name] ?? ['icon' => '📋', 'bg' => 'bg-gray-100', 'text' => 'text-gray-600'];

            $eventIcon = match(true) {
                str_contains($activity->description, 'creado')     ||
                str_contains($activity->description, 'abierta')    ||
                str_contains($activity->description, 'registrado') ||
                str_contains($activity->description, 'agregado')   => '➕',
                str_contains($activity->description, 'actualizado')||
                str_contains($activity->description, 'editado')    => '✏️',
                str_contains($activity->description, 'eliminado')  ||
                str_contains($activity->description, 'borrado')    => '🗑️',
                default => '📌',
            };

            $props      = $activity->properties;
            $attributes = $props['attributes'] ?? [];
            $old        = $props['old'] ?? [];

            // Cambios (actualización): solo campos que realmente cambiaron
            $changes = [];
            foreach ($attributes as $key => $val) {
                if (in_array($key, $hiddenFields)) continue;
                if (isset($old[$key]) && (string)$old[$key] !== (string)$val) {
                    $changes[] = [
                        'field' => $key,
                        'from'  => $formatValue($key, $old[$key]),
                        'to'    => $formatValue($key, $val),
                    ];
                }
            }

            // Creación: mostrar atributos relevantes (sin límite de 4)
            $isCreated    = empty($old) && !empty($attributes);
            $createdProps = [];
            if ($isCreated) {
                foreach ($attributes as $key => $val) {
                    if (in_array($key, $hiddenFields)) continue;
                    if (is_null($val) || $val === '') continue;
                    $createdProps[] = [
                        'field' => $key,
                        'value' => $formatValue($key, $val),
                    ];
                }
            }
        @endphp

        <div class="bg-white rounded-2xl p-4 mb-3 shadow-sm border border-gray-100 hover:shadow-md hover:border-purple-100 transition-all">
            <div class="flex items-start gap-3">

                {{-- Avatar --}}
                <div class="w-10 h-10 rounded-full flex items-center justify-center flex-shrink-0 text-white font-black text-sm shadow"
                    style="background: linear-gradient(135deg, #7c3aed, #db2777);">
                    {{ $activity->causer ? strtoupper(substr($activity->causer->name, 0, 1)) : '⚙' }}
                </div>

                <div class="flex-1 min-w-0">

                    {{-- Usuario + tiempo relativo --}}
                    <div class="flex items-center justify-between gap-2 mb-1">
                        <span class="text-gray-900 font-semibold text-sm truncate">
                            {{ $activity->causer?->name ?? 'Sistema' }}
                        </span>
                        <span class="text-gray-400 text-xs whitespace-nowrap flex-shrink-0">
                            {{ $activity->created_at->diffForHumans() }}
                        </span>
                    </div>

                    {{-- Acción + sección + ID --}}
                    <div class="flex flex-wrap items-center gap-1.5 mb-2.5">
                        <span class="text-sm">{{ $eventIcon }}</span>
                        <span class="text-gray-800 text-sm font-medium">{{ $activity->description }}</span>
                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-bold {{ $cfg['bg'] }} {{ $cfg['text'] }}">
                            {{ $cfg['icon'] }} {{ ucfirst($activity->log_name ?? 'general') }}
                        </span>
                        @if($activity->subject_id)
                            <span class="text-gray-400 text-xs">· ID #{{ $activity->subject_id }}</span>
                        @endif
                    </div>

                    {{-- Cambios (actualización) --}}
                    @if(count($changes) > 0)
                        <div class="flex flex-wrap gap-2">
                            @foreach($changes as $change)
                                @php $label = $fieldLabels[$change['field']] ?? ucfirst(str_replace('_', ' ', $change['field'])); @endphp
                                <div class="inline-flex items-center gap-1.5 text-xs rounded-xl px-3 py-1.5 bg-gray-50 border border-gray-200">
                                    <span class="text-gray-500 font-semibold">{{ $label }}:</span>
                                    <span class="text-red-500 line-through">{{ $change['from'] }}</span>
                                    <span class="text-gray-400">→</span>
                                    <span class="text-emerald-600 font-bold">{{ $change['to'] }}</span>
                                </div>
                            @endforeach
                        </div>

                    {{-- Atributos de creación --}}
                    @elseif($isCreated && count($createdProps) > 0)
                        <div class="flex flex-wrap gap-2">
                            @foreach($createdProps as $prop)
                                @php $label = $fieldLabels[$prop['field']] ?? ucfirst(str_replace('_', ' ', $prop['field'])); @endphp
                                <div class="inline-flex items-center gap-1.5 text-xs rounded-xl px-3 py-1.5 bg-blue-50 border border-blue-100">
                                    <span class="text-blue-500 font-semibold">{{ $label }}:</span>
                                    <span class="text-blue-700 font-bold">{{ $prop['value'] }}</span>
                                </div>
                            @endforeach
                        </div>
                    @endif

                    {{-- Fecha completa --}}
                    <div class="mt-2.5 text-xs text-gray-400">
                        {{ $activity->created_at->format('d/m/Y \a \l\a\s H:i') }}
                    </div>

                </div>
            </div>
        </div>

    @empty
        <div class="bg-white rounded-2xl p-14 text-center shadow-sm border border-gray-100">
            <div class="text-6xl mb-4">📋</div>
            <div class="text-xl font-bold text-gray-700 mb-2">Sin actividad registrada</div>
            <div class="text-gray-400 text-sm">
                @if($search || $filterLog || $filterDate)
                    Probá con otros filtros
                @else
                    Las acciones aparecerán aquí a medida que se realicen
                @endif
            </div>
        </div>
    @endforelse

    {{-- Paginación --}}
    @if($activities->hasPages())
        <div class="mt-4 bg-white rounded-2xl p-4 shadow-sm border border-gray-100">
            {{ $activities->links() }}
        </div>
    @endif

</div>