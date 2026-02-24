<div class="min-h-screen bg-gray-50 p-4 lg:p-6">

    {{-- ══════════════════════════════════════════════════════════
         HEADER
    ══════════════════════════════════════════════════════════ --}}
    <div class="mb-5">
        <div class="rounded-2xl p-5 lg:p-6 shadow-sm"
             style="background: linear-gradient(135deg, #6d28d9 0%, #7c3aed 50%, #8b5cf6 100%); border: 1px solid rgba(109,40,217,0.3);">
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                <div>
                    <div class="flex items-center gap-2.5 mb-1">
                        <div class="w-9 h-9 rounded-xl flex items-center justify-center text-lg bg-white/20">🔍</div>
                        <h1 class="text-2xl font-bold text-white tracking-tight">Auditoría del Sistema</h1>
                    </div>
                    <p class="text-purple-100 text-sm ml-11">Registro completo de acciones realizadas por cada usuario</p>
                </div>

                {{-- Stats contextuales: tienen sentido para el dueño --}}
                <div class="flex gap-2.5 ml-11 sm:ml-0">
                    <div class="rounded-xl px-4 py-2.5 text-center min-w-[72px] bg-white/20">
                        <div class="text-xl font-bold text-white leading-none">{{ $stats['today'] }}</div>
                        <div class="text-xs text-purple-100 mt-0.5">Eventos hoy</div>
                    </div>
                    <div class="rounded-xl px-4 py-2.5 text-center min-w-[72px] bg-white/20">
                        <div class="text-xl font-bold text-white leading-none">{{ $stats['users_today'] }}</div>
                        <div class="text-xs text-purple-100 mt-0.5">Usuarios activos</div>
                    </div>
                    <div class="rounded-xl px-4 py-2.5 text-center min-w-[72px]
                        {{ $stats['critical'] > 0 ? 'bg-red-500/80' : 'bg-white/20' }}">
                        <div class="text-xl font-bold text-white leading-none">{{ $stats['critical'] }}</div>
                        <div class="text-xs text-purple-100 mt-0.5">Alertas 7 días</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ══════════════════════════════════════════════════════════
         FILTROS
    ══════════════════════════════════════════════════════════ --}}
    <div class="bg-white rounded-2xl p-4 mb-4 shadow-sm border border-gray-100">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">

            {{-- Búsqueda --}}
            <div class="sm:col-span-2">
                <div class="relative">
                    <span class="absolute left-3 top-2.5 text-gray-400 text-sm">🔍</span>
                    <input wire:model.live.debounce.400ms="search"
                           type="text"
                           placeholder="Buscar por usuario, acción o valor..."
                           class="w-full pl-9 pr-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-gray-900 placeholder-gray-400 focus:ring-2 focus:ring-purple-500 focus:outline-none focus:bg-white text-sm transition">
                </div>
            </div>

            {{-- Sección --}}
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

            {{-- Tipo de evento --}}
            <div>
                <select wire:model.live="filterEvent"
                        class="w-full px-3 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-gray-700 focus:ring-2 focus:ring-purple-500 text-sm">
                    <option value="">⚡ Todos los eventos</option>
                    <option value="created">➕ Creaciones</option>
                    <option value="updated">✏️ Modificaciones</option>
                    <option value="deleted">🗑️ Eliminaciones</option>
                </select>
            </div>

            {{-- Usuario --}}
            <div>
                <select wire:model.live="filterUser"
                        class="w-full px-3 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-gray-700 focus:ring-2 focus:ring-purple-500 text-sm">
                    <option value="">👤 Todos los usuarios</option>
                    @foreach($users as $user)
                        <option value="{{ $user->id }}">{{ $user->name }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Período preset --}}
            <div>
                <select wire:model.live="filterDate"
                        class="w-full px-3 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-gray-700 focus:ring-2 focus:ring-purple-500 text-sm">
                    <option value="">📅 Todo el tiempo</option>
                    <option value="today">Hoy</option>
                    <option value="yesterday">Ayer</option>
                    <option value="week">Esta semana</option>
                    <option value="month">Este mes</option>
                </select>
            </div>

            {{-- Rango de fechas personalizado --}}
            <div class="flex gap-2 items-center">
                <input wire:model.live="dateFrom"
                       type="date"
                       class="flex-1 px-3 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-gray-700 focus:ring-2 focus:ring-purple-500 text-sm"
                       placeholder="Desde">
                <span class="text-gray-400 text-xs flex-shrink-0">al</span>
                <input wire:model.live="dateTo"
                       type="date"
                       class="flex-1 px-3 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-gray-700 focus:ring-2 focus:ring-purple-500 text-sm"
                       placeholder="Hasta">
            </div>

            {{-- Registros por página --}}
            <div>
                <select wire:model.live="perPage"
                        class="w-full px-3 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-gray-700 focus:ring-2 focus:ring-purple-500 text-sm">
                    <option value="25">25 por página</option>
                    <option value="30" selected>30 por página</option>
                    <option value="50">50 por página</option>
                    <option value="100">100 por página</option>
                </select>
            </div>
        </div>

        {{-- Limpiar filtros --}}
        @if($hasFilters)
            <div class="mt-3 flex justify-between items-center">
                <p class="text-xs text-gray-500">
                    Mostrando resultados filtrados · {{ $activities->total() }} eventos encontrados
                </p>
                <button wire:click="clearFilters"
                        class="text-xs text-purple-600 hover:text-purple-800 flex items-center gap-1.5 transition-colors px-3 py-1.5 rounded-lg hover:bg-purple-50 font-medium">
                    ✕ Limpiar todos los filtros
                </button>
            </div>
        @endif
    </div>

    {{-- ══════════════════════════════════════════════════════════
         HELPERS PHP (solo presentación, lógica está en el componente)
    ══════════════════════════════════════════════════════════ --}}
    @php
        $logConfig = [
            'pedidos'    => ['icon' => '🛒', 'bg' => 'bg-blue-100',   'text' => 'text-blue-700',   'ring' => 'ring-blue-200'],
            'productos'  => ['icon' => '🍨', 'bg' => 'bg-purple-100', 'text' => 'text-purple-700', 'ring' => 'ring-purple-200'],
            'caja'       => ['icon' => '🏦', 'bg' => 'bg-emerald-100','text' => 'text-emerald-700','ring' => 'ring-emerald-200'],
            'egresos'    => ['icon' => '💸', 'bg' => 'bg-red-100',    'text' => 'text-red-700',    'ring' => 'ring-red-200'],
            'empleados'  => ['icon' => '👥', 'bg' => 'bg-orange-100', 'text' => 'text-orange-700', 'ring' => 'ring-orange-200'],
            'inventario' => ['icon' => '📦', 'bg' => 'bg-yellow-100', 'text' => 'text-yellow-700', 'ring' => 'ring-yellow-200'],
        ];

        $criticalityConfig = [
            'high'   => ['border' => 'border-l-4 border-l-red-400',    'bg' => 'bg-red-50/40'],
            'medium' => ['border' => 'border-l-4 border-l-yellow-400', 'bg' => 'bg-yellow-50/40'],
            'low'    => ['border' => '',                                 'bg' => ''],
        ];
    @endphp

    {{-- ══════════════════════════════════════════════════════════
         LISTA DE EVENTOS
    ══════════════════════════════════════════════════════════ --}}
    <div class="space-y-2">
        @forelse($activities as $activity)
            @php
                $cfg          = $logConfig[$activity->log_name] ?? ['icon' => '📋', 'bg' => 'bg-gray-100', 'text' => 'text-gray-600', 'ring' => 'ring-gray-200'];
                $crit         = $this->criticality($activity);
                $critCfg      = $criticalityConfig[$crit];

                $desc         = mb_strtolower($activity->description);
                $eventIcon    = match(true) {
                    str_contains($desc, 'eliminado') || str_contains($desc, 'borrado')    => '🗑️',
                    str_contains($desc, 'cancelado')                                       => '⛔',
                    str_contains($desc, 'cread') || str_contains($desc, 'registrado') ||
                        str_contains($desc, 'abierta') || str_contains($desc, 'agregado') => '➕',
                    str_contains($desc, 'actualizado') || str_contains($desc, 'editado') ||
                        str_contains($desc, 'cerrada') || str_contains($desc, 'cerrado')  => '✏️',
                    default                                                                => '📌',
                };

                $props        = $activity->properties->toArray();
                $attributes   = $props['attributes'] ?? [];
                $old          = $props['old'] ?? [];
                $isUpdate     = !empty($old);
                $isCreation   = empty($old) && !empty($attributes);

                $changes      = $isUpdate   ? $this->extractChanges($activity)     : [];
                $createdProps = $isCreation ? $this->extractCreatedProps($activity) : [];

                $subjectLabel = $this->subjectLabel($activity);
                $subjectType  = $activity->subject_type
                    ? $this->subjectTypeName($activity->subject_type)
                    : null;

                $isBySystem   = empty($activity->causer);
                $causer       = $activity->causer;
                $causerInitial = $isBySystem ? '⚙' : strtoupper(substr($causer->name, 0, 1));
            @endphp

            <div class="bg-white rounded-2xl p-4 shadow-sm border border-gray-100 hover:shadow-md hover:border-purple-100 transition-all {{ $critCfg['border'] }} {{ $critCfg['bg'] }}">
                <div class="flex items-start gap-3">

                    {{-- Avatar --}}
                    @if($isBySystem)
                        <div class="w-10 h-10 rounded-full flex items-center justify-center flex-shrink-0 bg-gray-200 text-gray-500 text-lg">
                            ⚙
                        </div>
                    @else
                        <div class="w-10 h-10 rounded-full flex items-center justify-center flex-shrink-0 text-white font-black text-sm shadow-sm flex-shrink-0"
                             style="background: linear-gradient(135deg, #7c3aed, #db2777);">
                            {{ $causerInitial }}
                        </div>
                    @endif

                    <div class="flex-1 min-w-0">

                        {{-- Fila 1: usuario + tiempo --}}
                        <div class="flex items-center justify-between gap-2 mb-1">
                            <span class="text-gray-900 font-semibold text-sm truncate">
                                {{ $isBySystem ? 'Sistema automático' : $causer->name }}
                            </span>
                            <div class="flex items-center gap-2 flex-shrink-0">
                                {{-- Badge de criticidad --}}
                                @if($crit === 'high')
                                    <span class="text-xs font-bold text-red-600 bg-red-100 px-2 py-0.5 rounded-full">⚠ Crítico</span>
                                @elseif($crit === 'medium')
                                    <span class="text-xs font-bold text-yellow-700 bg-yellow-100 px-2 py-0.5 rounded-full">! Atención</span>
                                @endif
                                <span class="text-gray-400 text-xs">
                                    {{ $activity->created_at->diffForHumans() }}
                                </span>
                            </div>
                        </div>

                        {{-- Fila 2: descripción de la acción --}}
                        <div class="flex flex-wrap items-center gap-1.5 mb-2">
                            <span class="text-sm leading-none">{{ $eventIcon }}</span>
                            <span class="text-gray-800 text-sm font-medium">{{ $activity->description }}</span>

                            {{-- Qué entidad fue afectada --}}
                            @if($subjectType || $subjectLabel)
                                <span class="text-gray-400 text-xs">·</span>
                                @if($subjectType)
                                    <span class="text-gray-500 text-xs">{{ $subjectType }}</span>
                                @endif
                                @if($subjectLabel)
                                    <span class="text-gray-700 text-xs font-bold">{{ $subjectLabel }}</span>
                                @endif
                            @endif

                            {{-- Badge de sección --}}
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-bold {{ $cfg['bg'] }} {{ $cfg['text'] }}">
                                {{ $cfg['icon'] }} {{ ucfirst($activity->log_name ?? 'general') }}
                            </span>
                        </div>

                        {{-- Fila 3: detalle de cambios o atributos de creación --}}
                        @if(count($changes) > 0)
                            {{-- MODIFICACIÓN: mostrar qué cambió --}}
                            <div class="flex flex-wrap gap-1.5 mt-1">
                                @foreach($changes as $change)
                                    <div class="inline-flex items-center gap-1.5 text-xs rounded-xl px-3 py-1.5 bg-gray-50 border border-gray-200 max-w-full">
                                        <span class="text-gray-500 font-semibold flex-shrink-0">{{ $change['label'] }}:</span>
                                        <span class="text-red-500 line-through truncate max-w-[100px]" title="{{ $change['from'] }}">{{ $change['from'] }}</span>
                                        <span class="text-gray-400 flex-shrink-0">→</span>
                                        <span class="text-emerald-600 font-bold truncate max-w-[100px]" title="{{ $change['to'] }}">{{ $change['to'] }}</span>
                                    </div>
                                @endforeach
                            </div>

                        @elseif($isCreation && count($createdProps) > 0)
                            {{-- CREACIÓN: mostrar los atributos relevantes --}}
                            <div class="flex flex-wrap gap-1.5 mt-1">
                                @foreach($createdProps as $prop)
                                    <div class="inline-flex items-center gap-1.5 text-xs rounded-xl px-3 py-1.5 bg-blue-50 border border-blue-100">
                                        <span class="text-blue-500 font-semibold">{{ $prop['label'] }}:</span>
                                        <span class="text-blue-700 font-bold">{{ $prop['value'] }}</span>
                                    </div>
                                @endforeach
                            </div>

                        @elseif(!$isCreation && !$isUpdate && $activity->description)
                            {{-- EVENTO SIN PROPIEDADES (login, etc.) --}}
                            <div class="text-xs text-gray-400 italic mt-1">Sin detalles adicionales</div>
                        @endif

                        {{-- Fecha y hora exacta --}}
                        <div class="mt-2 text-xs text-gray-400">
                            {{ $activity->created_at->format('d/m/Y') }}
                            <span class="font-medium">{{ $activity->created_at->format('H:i:s') }}</span>
                        </div>

                    </div>
                </div>
            </div>

        @empty
            <div class="bg-white rounded-2xl p-14 text-center shadow-sm border border-gray-100">
                <div class="text-6xl mb-4">🔍</div>
                <div class="text-xl font-bold text-gray-700 mb-2">Sin actividad registrada</div>
                <div class="text-gray-400 text-sm">
                    @if($hasFilters)
                        No se encontraron eventos con los filtros actuales.
                        <button wire:click="clearFilters" class="text-purple-600 hover:underline font-medium ml-1">
                            Limpiar filtros
                        </button>
                    @else
                        Las acciones del sistema aparecerán aquí a medida que se realicen.
                    @endif
                </div>
            </div>
        @endforelse
    </div>

    {{-- Paginación --}}
    @if($activities->hasPages())
        <div class="mt-4 bg-white rounded-2xl p-4 shadow-sm border border-gray-100">
            {{ $activities->links() }}
        </div>
    @endif

</div>