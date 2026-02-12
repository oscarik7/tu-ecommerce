<div>

    {{-- ══ ALERTAS DE SALARIOS PENDIENTES ══ --}}
    @if($alerts->count() > 0)
        <div class="mb-6 bg-amber-50 border-2 border-amber-300 rounded-2xl p-4">
            <div class="flex items-center gap-2 mb-3">
                <span class="text-xl">⚠️</span>
                <h3 class="font-black text-amber-800">
                    {{ $alerts->count() }} empleado(s) con salario pendiente
                </h3>
            </div>
            <div class="flex flex-wrap gap-2">
                @foreach($alerts as $alertEmp)
                    <div class="flex items-center gap-2 bg-white border border-amber-200 rounded-xl px-3 py-2">
                        <div>
                            <span class="font-bold text-sm text-gray-800">{{ $alertEmp->name }}</span>
                            <span class="text-xs text-amber-600 ml-1">
                                · {{ $alertEmp->months_pending }} mes(es) sin pago
                            </span>
                        </div>
                        <button wire:click="openPayModal({{ $alertEmp->id }})"
                            class="text-xs bg-amber-500 hover:bg-amber-600 text-white px-2 py-1 rounded-lg font-bold transition-all">
                            Pagar
                        </button>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    {{-- ══ VISTA DETALLE ══ --}}
    @if($view === 'detail' && $selectedEmployee)
        @php $emp = $selectedEmployee; @endphp

        <div class="mb-4">
            <button wire:click="backToList"
                class="flex items-center gap-2 text-sm text-gray-500 hover:text-gray-800 transition-all font-medium">
                ← Volver a la lista
            </button>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

            {{-- Perfil --}}
            <div class="bg-white rounded-2xl shadow-sm border overflow-hidden">
                <div class="bg-gradient-to-br from-purple-500 to-indigo-600 p-6 text-white text-center">
                    <div class="w-20 h-20 rounded-full bg-white/20 flex items-center justify-center mx-auto mb-3 text-4xl">
                        👤
                    </div>
                    <h2 class="text-xl font-black">{{ $emp->name }}</h2>
                    <p class="text-purple-200 text-sm">{{ $emp->position }}</p>
                    @if($emp->is_active)
                        <span class="inline-block mt-2 text-xs bg-green-400 text-green-900 font-bold px-3 py-1 rounded-full">Activo</span>
                    @else
                        <span class="inline-block mt-2 text-xs bg-red-400 text-white font-bold px-3 py-1 rounded-full">Inactivo</span>
                    @endif
                </div>
                <div class="p-4 space-y-3">
                    @if($emp->phone)
                        <div class="flex items-center gap-2 text-sm">
                            <span class="text-gray-400">📞</span>
                            <span class="text-gray-700">{{ $emp->phone }}</span>
                        </div>
                    @endif
                    @if($emp->document)
                        <div class="flex items-center gap-2 text-sm">
                            <span class="text-gray-400">🪪</span>
                            <span class="text-gray-700">{{ $emp->document }}</span>
                        </div>
                    @endif
                    @if($emp->address)
                        <div class="flex items-center gap-2 text-sm">
                            <span class="text-gray-400">📍</span>
                            <span class="text-gray-700">{{ $emp->address }}</span>
                        </div>
                    @endif
                    <div class="flex items-center gap-2 text-sm">
                        <span class="text-gray-400">📅</span>
                        <span class="text-gray-700">Desde {{ $emp->hire_date?->format('d/m/Y') }}</span>
                    </div>
                    @if($emp->user)
                        <div class="flex items-center gap-2 text-sm">
                            <span class="text-gray-400">🔐</span>
                            <span class="text-gray-700">Usuario: {{ $emp->user->name }}</span>
                        </div>
                    @endif
                    <div class="pt-2 border-t">
                        <div class="text-xs text-gray-400 mb-1">Salario</div>
                        <div class="font-black text-lg text-purple-600">
                            {{ number_format($emp->salary, 0, ',', '.') }} Gs
                        </div>
                        <div class="text-xs text-gray-500">{{ $salaryTypes[$emp->salary_type]['label'] ?? $emp->salary_type }}</div>
                    </div>
                    @if($emp->notes)
                        <div class="text-xs text-gray-500 bg-gray-50 rounded-lg p-2">{{ $emp->notes }}</div>
                    @endif
                </div>
                <div class="p-4 border-t flex gap-2">
                    <button wire:click="openModal({{ $emp->id }})"
                        class="flex-1 bg-purple-100 hover:bg-purple-200 text-purple-700 font-bold py-2 rounded-xl text-sm transition-all">
                        ✏️ Editar
                    </button>
                    <button wire:click="openPayModal({{ $emp->id }})"
                        class="flex-1 bg-green-500 hover:bg-green-600 text-white font-bold py-2 rounded-xl text-sm transition-all">
                        💰 Pagar
                    </button>
                </div>
            </div>

            {{-- Historial de pagos --}}
            <div class="lg:col-span-2 bg-white rounded-2xl shadow-sm border overflow-hidden">
                <div class="p-4 border-b flex items-center justify-between">
                    <h3 class="font-black text-gray-800">📋 Historial de Pagos</h3>
                    @php
                        $salaryExpenses = $emp->expenses->where('type', 'salary');
                        $totalPaid = $salaryExpenses->sum('amount');
                    @endphp
                    <span class="text-sm font-bold text-purple-600">
                        Total pagado: {{ number_format($totalPaid, 0, ',', '.') }} Gs
                    </span>
                </div>

                @if($salaryExpenses->count() === 0)
                    <div class="p-12 text-center text-gray-400">
                        <div class="text-4xl mb-2">💸</div>
                        <p class="font-medium">Sin pagos registrados</p>
                    </div>
                @else
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead class="bg-gray-50 text-xs text-gray-500 uppercase tracking-wider">
                                <tr>
                                    <th class="px-4 py-3 text-left">Fecha</th>
                                    <th class="px-4 py-3 text-left">Período</th>
                                    <th class="px-4 py-3 text-left">Método</th>
                                    <th class="px-4 py-3 text-right">Monto</th>
                                    <th class="px-4 py-3 text-left">Notas</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @foreach($salaryExpenses as $payment)
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-4 py-3 text-gray-600">
                                            {{ $payment->expense_date?->format('d/m/Y') }}
                                        </td>
                                        <td class="px-4 py-3 text-gray-600">
                                            {{ $payment->period ?? '—' }}
                                        </td>
                                        <td class="px-4 py-3">
                                            <span class="text-xs px-2 py-1 rounded-full font-medium
                                                {{ $payment->payment_method === 'cash' ? 'bg-green-100 text-green-700' : 'bg-blue-100 text-blue-700' }}">
                                                {{ match($payment->payment_method) {
                                                    'cash' => '💵 Efectivo',
                                                    'card' => '💳 Tarjeta',
                                                    'transfer' => '🏦 Transfer',
                                                    default => $payment->payment_method
                                                } }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-3 text-right font-bold text-gray-800">
                                            {{ number_format($payment->amount, 0, ',', '.') }} Gs
                                        </td>
                                        <td class="px-4 py-3 text-xs text-gray-500">
                                            {{ $payment->notes ?? '—' }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>

    {{-- ══ VISTA LISTA ══ --}}
    @else

        {{-- Header con stats --}}
        <div class="bg-gradient-to-r from-purple-600 to-indigo-600 rounded-2xl p-5 text-white mb-6">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h1 class="text-2xl font-black">👥 Empleados</h1>
                    <p class="text-purple-200 text-sm">Gestión de personal y salarios</p>
                </div>
                <button wire:click="openModal()"
                    class="bg-white text-purple-700 hover:bg-purple-50 font-black px-5 py-2.5 rounded-xl text-sm transition-all shadow">
                    + Nuevo Empleado
                </button>
            </div>
            <div class="grid grid-cols-2 lg:grid-cols-5 gap-3">
                <div class="bg-white/10 rounded-xl p-3 text-center">
                    <div class="text-2xl font-black">{{ $stats['active'] }}</div>
                    <div class="text-xs text-purple-200">Activos</div>
                </div>
                <div class="bg-white/10 rounded-xl p-3 text-center">
                    <div class="text-2xl font-black">{{ $stats['inactive'] }}</div>
                    <div class="text-xs text-purple-200">Inactivos</div>
                </div>
                <div class="bg-white/10 rounded-xl p-3 text-center lg:col-span-1">
                    <div class="text-lg font-black">{{ number_format($stats['monthly_payroll'] / 1000, 0) }}k</div>
                    <div class="text-xs text-purple-200">Planilla mensual</div>
                </div>
                <div class="bg-green-500/30 rounded-xl p-3 text-center">
                    <div class="text-lg font-black">{{ number_format($stats['paid_this_month'] / 1000, 0) }}k</div>
                    <div class="text-xs text-green-200">Pagado este mes</div>
                </div>
                <div class="{{ $stats['pending_payroll'] > 0 ? 'bg-amber-500/30' : 'bg-white/10' }} rounded-xl p-3 text-center">
                    <div class="text-lg font-black">{{ number_format($stats['pending_payroll'] / 1000, 0) }}k</div>
                    <div class="text-xs text-{{ $stats['pending_payroll'] > 0 ? 'amber' : 'purple' }}-200">Pendiente</div>
                </div>
            </div>
        </div>

        {{-- Filtros --}}
        <div class="bg-white rounded-2xl shadow-sm border p-4 mb-4 flex flex-wrap gap-3">
            <div class="flex-1 min-w-48">
                <input wire:model.live="search" type="text"
                    placeholder="🔍 Buscar por nombre, cargo..."
                    class="w-full px-4 py-2 border border-gray-300 rounded-xl text-sm focus:ring-2 focus:ring-purple-400">
            </div>
            <select wire:model.live="filterStatus"
                class="px-4 py-2 border border-gray-300 rounded-xl text-sm focus:ring-2 focus:ring-purple-400">
                <option value="active">✅ Activos</option>
                <option value="inactive">❌ Inactivos</option>
                <option value="all">👥 Todos</option>
            </select>
            <select wire:model.live="filterPosition"
                class="px-4 py-2 border border-gray-300 rounded-xl text-sm focus:ring-2 focus:ring-purple-400">
                <option value="">Todos los cargos</option>
                @foreach($positions as $pos)
                    <option value="{{ $pos }}">{{ $pos }}</option>
                @endforeach
            </select>
        </div>

        {{-- Tabla de empleados --}}
        <div class="bg-white rounded-2xl shadow-sm border overflow-hidden">
            @if($employees->count() === 0)
                <div class="p-16 text-center text-gray-400">
                    <div class="text-5xl mb-3">👥</div>
                    <p class="font-bold text-lg">Sin empleados registrados</p>
                    <p class="text-sm mt-1">Agregá el primer empleado con el botón de arriba</p>
                </div>
            @else
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 border-b">
                        <tr>
                            <th class="px-5 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Empleado</th>
                            <th class="px-5 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Cargo</th>
                            <th class="px-5 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Salario</th>
                            <th class="px-5 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Últ. pago</th>
                            <th class="px-5 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Estado</th>
                            <th class="px-5 py-3 text-center text-xs font-bold text-gray-500 uppercase tracking-wider">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($employees as $emp)
                            @php
                                $lastPayment = $emp->expenses()
                                    ->where('type', 'salary')
                                    ->orderByDesc('expense_date')
                                    ->first();
                                $monthsPending = $emp->months_pending;
                                $hasAlert = $emp->is_active
                                    && in_array($emp->salary_type, ['fixed', 'biweekly'])
                                    && $monthsPending >= 1;
                            @endphp
                            <tr class="hover:bg-gray-50 transition-colors {{ $hasAlert ? 'bg-amber-50/50' : '' }}">
                                <td class="px-5 py-3.5">
                                    <div class="flex items-center gap-3">
                                        <div class="w-9 h-9 rounded-full bg-purple-100 flex items-center justify-center text-purple-600 font-black text-sm flex-shrink-0">
                                            {{ strtoupper(substr($emp->name, 0, 1)) }}
                                        </div>
                                        <div>
                                            <div class="font-bold text-gray-900">{{ $emp->name }}</div>
                                            @if($emp->phone)
                                                <div class="text-xs text-gray-400">{{ $emp->phone }}</div>
                                            @endif
                                        </div>
                                        @if($hasAlert)
                                            <span class="text-xs bg-amber-100 text-amber-700 font-bold px-2 py-0.5 rounded-full">
                                                ⚠️ {{ $monthsPending }}m
                                            </span>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-5 py-3.5 text-gray-600">{{ $emp->position }}</td>
                                <td class="px-5 py-3.5">
                                    <div class="font-bold text-gray-800">{{ number_format($emp->salary, 0, ',', '.') }} Gs</div>
                                    <div class="text-xs text-gray-400">{{ $salaryTypes[$emp->salary_type]['unit'] ?? '' }}</div>
                                </td>
                                <td class="px-5 py-3.5 text-gray-500 text-xs">
                                    @if($lastPayment)
                                        {{ $lastPayment->expense_date?->format('d/m/Y') }}
                                        <div class="text-gray-400">{{ $lastPayment->period }}</div>
                                    @else
                                        <span class="text-amber-600 font-medium">Sin pagos</span>
                                    @endif
                                </td>
                                <td class="px-5 py-3.5">
                                    @if($emp->is_active)
                                        <span class="text-xs bg-green-100 text-green-700 font-bold px-2 py-1 rounded-full">Activo</span>
                                    @else
                                        <span class="text-xs bg-gray-100 text-gray-500 font-bold px-2 py-1 rounded-full">Inactivo</span>
                                    @endif
                                </td>
                                <td class="px-5 py-3.5">
                                    <div class="flex items-center justify-center gap-1.5">
                                        {{-- Ver detalle --}}
                                        <button wire:click="viewEmployee({{ $emp->id }})"
                                            class="p-2 rounded-xl bg-purple-50 hover:bg-purple-100 text-purple-600 transition-all"
                                            title="Ver historial">
                                            👁️
                                        </button>
                                        {{-- Pagar salario --}}
                                        @if($emp->is_active)
                                            <button wire:click="openPayModal({{ $emp->id }})"
                                                class="p-2 rounded-xl bg-green-50 hover:bg-green-100 text-green-600 transition-all"
                                                title="Registrar pago">
                                                💰
                                            </button>
                                        @endif
                                        {{-- Editar --}}
                                        <button wire:click="openModal({{ $emp->id }})"
                                            class="p-2 rounded-xl bg-blue-50 hover:bg-blue-100 text-blue-600 transition-all"
                                            title="Editar">
                                            ✏️
                                        </button>
                                        {{-- Activar/Desactivar --}}
                                        <button wire:click="toggleActive({{ $emp->id }})"
                                            class="p-2 rounded-xl {{ $emp->is_active ? 'bg-red-50 hover:bg-red-100 text-red-500' : 'bg-gray-50 hover:bg-gray-100 text-gray-500' }} transition-all"
                                            title="{{ $emp->is_active ? 'Desactivar' : 'Reactivar' }}">
                                            {{ $emp->is_active ? '🚫' : '✅' }}
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>

                @if($employees->hasPages())
                    <div class="px-5 py-4 border-t bg-gray-50">
                        {{ $employees->links() }}
                    </div>
                @endif
            @endif
        </div>

    @endif

    {{-- ══ MODAL CRUD EMPLEADO ══ --}}
    @if($showModal)
        <div class="fixed inset-0 bg-black/60 backdrop-blur-sm flex items-center justify-center p-4 z-50"
             x-on:keydown.escape.window="$wire.closeModal()">
            <div class="bg-white rounded-2xl w-full max-w-lg shadow-2xl flex flex-col max-h-[90vh]">

                <div class="bg-gradient-to-r from-purple-500 to-indigo-600 px-5 py-4 text-white flex-shrink-0">
                    <h2 class="text-lg font-black">
                        {{ $editingId ? '✏️ Editar Empleado' : '👤 Nuevo Empleado' }}
                    </h2>
                </div>

                <div class="overflow-y-auto flex-1 p-5 space-y-4">

                    {{-- Nombre y Cargo --}}
                    <div class="grid grid-cols-2 gap-3">
                        <div class="col-span-2">
                            <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1">
                                Nombre completo <span class="text-red-500">*</span>
                            </label>
                            <input wire:model="name" type="text"
                                class="w-full px-3 py-2.5 border-2 border-gray-300 rounded-xl focus:ring-2 focus:ring-purple-400 focus:border-purple-400 text-sm"
                                placeholder="Nombre del empleado">
                            @error('name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1">
                                Cargo <span class="text-red-500">*</span>
                            </label>
                            <input wire:model="position" list="positions-list" type="text"
                                class="w-full px-3 py-2.5 border-2 border-gray-300 rounded-xl focus:ring-2 focus:ring-purple-400 text-sm"
                                placeholder="Cargo o posición">
                            <datalist id="positions-list">
                                @foreach($positions as $pos)
                                    <option value="{{ $pos }}">
                                @endforeach
                            </datalist>
                            @error('position') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1">Teléfono</label>
                            <input wire:model="phone" type="text"
                                class="w-full px-3 py-2.5 border-2 border-gray-300 rounded-xl focus:ring-2 focus:ring-purple-400 text-sm"
                                placeholder="0981 000 000">
                        </div>
                    </div>

                    {{-- Documento y Dirección --}}
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1">CI / Documento</label>
                            <input wire:model="document" type="text"
                                class="w-full px-3 py-2.5 border-2 border-gray-300 rounded-xl focus:ring-2 focus:ring-purple-400 text-sm"
                                placeholder="Nº de documento">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1">Fecha de contratación</label>
                            <input wire:model="hireDate" type="date"
                                class="w-full px-3 py-2.5 border-2 border-gray-300 rounded-xl focus:ring-2 focus:ring-purple-400 text-sm">
                        </div>
                    </div>

                    {{-- Salario --}}
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">
                            Tipo de salario <span class="text-red-500">*</span>
                        </label>
                        <div class="grid grid-cols-2 gap-2">
                            @foreach($salaryTypes as $stKey => $stData)
                                <label class="flex items-center gap-2 px-3 py-2 rounded-xl border-2 cursor-pointer transition-all text-sm
                                    {{ $salaryType === $stKey
                                        ? 'border-purple-400 bg-purple-50 text-purple-700 font-bold'
                                        : 'border-gray-200 text-gray-600 hover:border-purple-200' }}">
                                    <input wire:model.live="salaryType" type="radio" value="{{ $stKey }}" class="sr-only">
                                    {{ $stData['label'] }}
                                </label>
                            @endforeach
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1">
                            Monto de salario <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <input wire:model="salary" type="number" min="0" step="1000"
                                class="w-full px-4 py-3 text-xl font-black text-center border-2 border-gray-300 rounded-xl focus:ring-2 focus:ring-purple-400"
                                placeholder="0">
                            <span class="absolute right-4 top-1/2 -translate-y-1/2 text-gray-400 text-sm font-medium">
                                {{ $salaryTypes[$salaryType]['unit'] ?? 'Gs' }}
                            </span>
                        </div>
                        @error('salary') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    {{-- Usuario vinculado --}}
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1">
                            Usuario del sistema (opcional)
                        </label>
                        <select wire:model="linkedUserId"
                            class="w-full px-3 py-2.5 border-2 border-gray-300 rounded-xl focus:ring-2 focus:ring-purple-400 text-sm">
                            <option value="">Sin usuario vinculado</option>
                            @foreach($availableUsers as $user)
                                <option value="{{ $user->id }}">{{ $user->name }} — {{ $user->email }}</option>
                            @endforeach
                        </select>
                        <p class="text-xs text-gray-400 mt-1">Vinculá si el empleado usa el POS o el panel</p>
                    </div>

                    {{-- Dirección y Notas --}}
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1">Dirección</label>
                        <input wire:model="address" type="text"
                            class="w-full px-3 py-2.5 border-2 border-gray-300 rounded-xl focus:ring-2 focus:ring-purple-400 text-sm"
                            placeholder="Dirección del empleado">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1">Notas internas</label>
                        <input wire:model="notes" type="text"
                            class="w-full px-3 py-2.5 border-2 border-gray-300 rounded-xl focus:ring-2 focus:ring-purple-400 text-sm"
                            placeholder="Observaciones sobre el empleado...">
                    </div>

                    {{-- Activo/Inactivo (solo en edición) --}}
                    @if($editingId)
                        <label class="flex items-center gap-3 cursor-pointer">
                            <input wire:model="isActive" type="checkbox" class="w-5 h-5 rounded text-purple-600">
                            <span class="text-sm font-medium text-gray-700">Empleado activo</span>
                        </label>
                    @endif
                </div>

                <div class="px-5 py-4 bg-gray-50 border-t flex gap-3 flex-shrink-0">
                    <button wire:click="closeModal"
                        class="flex-1 bg-gray-200 hover:bg-gray-300 text-gray-700 font-bold py-2.5 rounded-xl text-sm transition-all">
                        Cancelar
                    </button>
                    <button wire:click="save" wire:loading.attr="disabled"
                        class="flex-1 bg-purple-500 hover:bg-purple-600 text-white font-bold py-2.5 rounded-xl text-sm transition-all disabled:opacity-50">
                        <span wire:loading.remove wire:target="save">✓ {{ $editingId ? 'Actualizar' : 'Registrar' }}</span>
                        <span wire:loading wire:target="save">Guardando...</span>
                    </button>
                </div>
            </div>
        </div>
    @endif

    {{-- ══ MODAL PAGO DE SALARIO ══ --}}
    @if($showPayModal && $payingEmployee)
        <div class="fixed inset-0 bg-black/60 backdrop-blur-sm flex items-center justify-center p-4 z-50"
             x-on:keydown.escape.window="$wire.closePayModal()">
            <div class="bg-white rounded-2xl w-full max-w-sm shadow-2xl overflow-hidden">

                <div class="bg-gradient-to-r from-green-500 to-emerald-600 px-5 py-4 text-white">
                    <h2 class="text-lg font-black">💰 Registrar Pago</h2>
                    <p class="text-green-100 text-sm mt-0.5">{{ $payingEmployee->name }} · {{ $payingEmployee->position }}</p>
                </div>

                <div class="p-5 space-y-4">

                    {{-- Período --}}
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1">Período</label>
                        <input wire:model="payPeriod" type="month"
                            class="w-full px-3 py-2.5 border-2 border-gray-300 rounded-xl focus:ring-2 focus:ring-green-400 text-sm">
                    </div>

                    {{-- Monto --}}
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1">
                            Monto <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <input wire:model="payAmount" type="number" min="1" step="1000"
                                class="w-full px-4 py-3 text-2xl font-black text-center border-2 border-gray-300 rounded-xl focus:ring-2 focus:ring-green-400"
                                placeholder="0">
                            <span class="absolute right-4 top-1/2 -translate-y-1/2 text-gray-400 text-sm font-medium">Gs</span>
                        </div>
                        <p class="text-xs text-gray-400 mt-1">
                            Salario base: {{ number_format($payingEmployee->salary, 0, ',', '.') }} Gs
                        </p>
                        @error('payAmount') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    {{-- Método --}}
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Pagado con</label>
                        <div class="grid grid-cols-3 gap-2">
                            @foreach(['cash' => '💵 Efectivo', 'card' => '💳 Tarjeta', 'transfer' => '🏦 Transfer'] as $key => $label)
                                <label class="flex items-center justify-center p-2.5 rounded-xl border-2 cursor-pointer transition-all text-xs font-bold
                                    {{ $payMethod === $key ? 'border-green-400 bg-green-50 text-green-700' : 'border-gray-200 text-gray-600 hover:border-green-200' }}">
                                    <input wire:model="payMethod" type="radio" value="{{ $key }}" class="sr-only">
                                    {{ $label }}
                                </label>
                            @endforeach
                        </div>
                    </div>

                    {{-- Notas --}}
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1">Notas (opcional)</label>
                        <input wire:model="payNotes" type="text"
                            class="w-full px-3 py-2.5 border-2 border-gray-300 rounded-xl focus:ring-2 focus:ring-green-400 text-sm"
                            placeholder="Ej: Adelanto, bonificación...">
                    </div>

                    {{-- Aviso caja --}}
                    @php $openReg = \App\Models\CashRegister::getOpenRegister(); @endphp
                    @if($openReg)
                        <div class="bg-green-50 border border-green-200 rounded-xl px-3 py-2 text-xs text-green-700 font-medium">
                            ✓ Se registrará en la caja abierta
                        </div>
                    @else
                        <div class="bg-amber-50 border border-amber-200 rounded-xl px-3 py-2 text-xs text-amber-700 font-medium">
                            ⚠️ Sin caja abierta — el egreso quedará sin caja
                        </div>
                    @endif
                </div>

                <div class="px-5 py-4 bg-gray-50 border-t flex gap-3">
                    <button wire:click="closePayModal"
                        class="flex-1 bg-gray-200 hover:bg-gray-300 text-gray-700 font-bold py-2.5 rounded-xl text-sm transition-all">
                        Cancelar
                    </button>
                    <button wire:click="processPay" wire:loading.attr="disabled"
                        class="flex-1 bg-green-500 hover:bg-green-600 text-white font-bold py-2.5 rounded-xl text-sm transition-all disabled:opacity-50">
                        <span wire:loading.remove wire:target="processPay">✓ Registrar Pago</span>
                        <span wire:loading wire:target="processPay">Procesando...</span>
                    </button>
                </div>
            </div>
        </div>
    @endif

</div>
