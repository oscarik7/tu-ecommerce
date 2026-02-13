<div class="min-h-screen bg-gradient-to-br from-purple-900 via-purple-800 to-indigo-900 p-6">

    {{-- HEADER --}}
    <div class="mb-6">
        <div class="bg-white/10 backdrop-blur-md rounded-xl p-6 shadow-xl">
            <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center gap-4">
                <div>
                    <h1 class="text-3xl font-bold text-white mb-1">👥 Clientes</h1>
                    <p class="text-purple-200">Gestión de clientes registrados</p>
                </div>
                <div class="flex flex-wrap gap-3">
                    <div class="bg-white/20 rounded-lg px-4 py-3 text-center min-w-[80px]">
                        <div class="text-2xl font-bold text-white">{{ $stats['total'] }}</div>
                        <div class="text-xs text-purple-200">Total</div>
                    </div>
                    <div class="bg-green-500/30 rounded-lg px-4 py-3 text-center min-w-[80px]">
                        <div class="text-2xl font-bold text-white">{{ $stats['with_doc'] }}</div>
                        <div class="text-xs text-green-200">🧾 Con documento</div>
                    </div>
                    <div class="bg-blue-500/30 rounded-lg px-4 py-3 text-center min-w-[80px]">
                        <div class="text-2xl font-bold text-white">{{ $stats['with_ruc'] }}</div>
                        <div class="text-xs text-blue-200">🏢 Con RUC</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- FLASH --}}
    @if(session()->has('message'))
        <div class="bg-green-500 text-white px-6 py-4 rounded-xl mb-6 shadow-xl flex items-center gap-2">
            <span class="text-xl">✓</span> <span class="font-semibold">{{ session('message') }}</span>
        </div>
    @endif

    {{-- FILTROS --}}
    <div class="bg-white/10 backdrop-blur-md rounded-xl p-4 mb-4 shadow-xl">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
            <div class="md:col-span-2">
                <input wire:model.live.debounce.300ms="search" type="text"
                    placeholder="🔍 Buscar por nombre, email, teléfono o documento..."
                    class="w-full px-4 py-2 bg-white rounded-lg text-gray-900 placeholder-gray-500 focus:ring-2 focus:ring-purple-500 focus:outline-none text-sm">
            </div>
            <div>
                <select wire:model.live="filterDoc"
                    class="w-full px-4 py-2 bg-white rounded-lg text-gray-900 focus:ring-2 focus:ring-purple-500 text-sm">
                    <option value="">🧾 Todos los clientes</option>
                    <option value="with">✓ Con documento</option>
                    <option value="without">✕ Sin documento</option>
                </select>
            </div>
        </div>
    </div>

    {{-- TABLA --}}
    <div class="bg-white rounded-xl shadow-2xl overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gradient-to-r from-purple-600 to-indigo-600">
                    <tr>
                        <th class="px-4 py-4 text-left text-xs font-bold text-white uppercase">Cliente</th>
                        <th class="px-4 py-4 text-left text-xs font-bold text-white uppercase">Contacto</th>
                        <th class="px-4 py-4 text-left text-xs font-bold text-white uppercase">Facturación</th>
                        <th class="px-4 py-4 text-center text-xs font-bold text-white uppercase">Pedidos</th>
                        <th class="px-4 py-4 text-left text-xs font-bold text-white uppercase">Acciones</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($users as $user)
                        <tr class="hover:bg-purple-50 transition-colors">

                            {{-- Cliente --}}
                            <td class="px-4 py-4">
                                <div class="font-semibold text-gray-900">{{ $user->name }}</div>
                                <div class="text-xs text-gray-500">{{ $user->email }}</div>
                                <div class="text-xs text-gray-400">Desde {{ $user->created_at->format('d/m/Y') }}</div>
                            </td>

                            {{-- Contacto --}}
                            <td class="px-4 py-4">
                                @if($user->phone)
                                    <div class="text-sm text-gray-700">📞 {{ $user->phone }}</div>
                                @endif
                                @if($user->address)
                                    <div class="text-xs text-gray-500 mt-0.5 line-clamp-1">📍 {{ $user->address }}</div>
                                @endif
                                @if(!$user->phone && !$user->address)
                                    <span class="text-xs text-gray-400">Sin datos de contacto</span>
                                @endif
                            </td>

                            {{-- Facturación --}}
                            <td class="px-4 py-4">
                                @if($user->document)
                                    <div class="flex items-center gap-1.5">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-bold
                                            {{ $user->document_type === 'ruc' ? 'bg-blue-100 text-blue-800' : 'bg-green-100 text-green-800' }}">
                                            {{ $user->document_type === 'ruc' ? '🏢 RUC' : '🪪 CI' }}
                                        </span>
                                        <span class="text-sm font-mono text-gray-700">{{ $user->document }}</span>
                                    </div>
                                    @if($user->document_type === 'ruc' && $user->company_name)
                                        <div class="text-xs text-gray-500 mt-0.5">{{ $user->company_name }}</div>
                                    @endif
                                @else
                                    <span class="text-xs text-gray-400">Sin documento</span>
                                @endif
                            </td>

                            {{-- Pedidos --}}
                            <td class="px-4 py-4 text-center">
                                <span class="inline-flex items-center justify-center w-8 h-8 rounded-full text-sm font-bold
                                    {{ $user->orders_count > 0 ? 'bg-purple-100 text-purple-700' : 'bg-gray-100 text-gray-400' }}">
                                    {{ $user->orders_count }}
                                </span>
                            </td>

                            {{-- Acciones --}}
                            <td class="px-4 py-4">
                                <div class="flex gap-2">
                                    <button wire:click="openEdit({{ $user->id }})"
                                        class="bg-purple-600 hover:bg-purple-700 text-white font-bold px-3 py-2 rounded-lg transition-all text-xs"
                                        title="Editar cliente">
                                        ✏️ Editar
                                    </button>
                                    @if($user->document)
                                        <button wire:click="clearDocument({{ $user->id }})"
                                            onclick="return confirm('¿Eliminar datos de facturación de {{ addslashes($user->name) }}?')"
                                            class="bg-red-100 hover:bg-red-200 text-red-700 font-bold px-3 py-2 rounded-lg transition-all text-xs"
                                            title="Quitar documento">
                                            🗑️
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center">
                                <div class="text-6xl mb-4">👤</div>
                                <div class="text-xl font-bold text-gray-600">No se encontraron clientes</div>
                                <div class="text-gray-400 mt-1">Intentá con otros filtros</div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($users->hasPages())
            <div class="px-6 py-4 bg-gray-50 border-t">
                {{ $users->links() }}
            </div>
        @endif
    </div>

    {{-- MODAL EDITAR --}}
    @if($editingUserId)
        <div class="fixed inset-0 bg-black/70 backdrop-blur-sm flex items-center justify-center p-4 z-50"
            wire:click="closeEdit">
            <div class="bg-white rounded-2xl w-full max-w-md shadow-2xl" wire:click.stop>

                <div class="flex items-center justify-between px-6 py-4 border-b">
                    <h2 class="text-lg font-black text-gray-900">✏️ Editar Cliente</h2>
                    <button wire:click="closeEdit"
                        class="w-8 h-8 rounded-full bg-gray-100 hover:bg-gray-200 flex items-center justify-center">
                        <svg class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                <div class="p-6 space-y-4">

                    {{-- Nombre --}}
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1.5">Nombre <span class="text-red-500">*</span></label>
                        <input wire:model="editName" type="text"
                            class="w-full px-4 py-2.5 text-sm border-2 rounded-xl focus:outline-none transition-all
                                @error('editName') border-red-400 bg-red-50 @else border-gray-200 focus:border-purple-500 @enderror">
                        @error('editName')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Teléfono --}}
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1.5">Teléfono</label>
                        <input wire:model="editPhone" type="text" placeholder="0981 123 456"
                            class="w-full px-4 py-2.5 text-sm border-2 border-gray-200 rounded-xl focus:border-purple-500 focus:outline-none transition-all">
                    </div>

                    {{-- Dirección --}}
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1.5">Dirección</label>
                        <input wire:model="editAddress" type="text" placeholder="Av. San Blas 123"
                            class="w-full px-4 py-2.5 text-sm border-2 border-gray-200 rounded-xl focus:border-purple-500 focus:outline-none transition-all">
                    </div>

                    {{-- Separador facturación --}}
                    <div class="border-t pt-4">
                        <p class="text-xs font-bold text-gray-500 uppercase tracking-wide mb-3">🧾 Datos de Facturación</p>

                        {{-- Tipo doc --}}
                        <div class="grid grid-cols-2 gap-2 mb-3">
                            <button type="button" wire:click="$set('editDocType', 'ci')"
                                class="py-2 rounded-xl border-2 text-sm font-semibold transition-all
                                    {{ $editDocType === 'ci' ? 'border-purple-500 bg-purple-50 text-purple-700' : 'border-gray-200 text-gray-600 hover:border-gray-300' }}">
                                🪪 CI
                            </button>
                            <button type="button" wire:click="$set('editDocType', 'ruc')"
                                class="py-2 rounded-xl border-2 text-sm font-semibold transition-all
                                    {{ $editDocType === 'ruc' ? 'border-purple-500 bg-purple-50 text-purple-700' : 'border-gray-200 text-gray-600 hover:border-gray-300' }}">
                                🏢 RUC
                            </button>
                        </div>

                        {{-- Número --}}
                        <div class="mb-3">
                            <label class="block text-xs font-semibold text-gray-600 mb-1.5">
                                {{ $editDocType === 'ruc' ? 'Número de RUC' : 'Número de CI' }}
                            </label>
                            <input wire:model="editDoc" type="text"
                                placeholder="{{ $editDocType === 'ruc' ? 'Ej: 80012345-6' : 'Ej: 4567890' }}"
                                class="w-full px-4 py-2.5 text-sm border-2 rounded-xl focus:outline-none transition-all
                                    @error('editDoc') border-red-400 bg-red-50 @else border-gray-200 focus:border-purple-500 @enderror">
                            @error('editDoc')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Razón social (solo RUC) --}}
                        @if($editDocType === 'ruc')
                            <div>
                                <label class="block text-xs font-semibold text-gray-600 mb-1.5">
                                    Razón Social <span class="text-gray-400 font-normal">(opcional)</span>
                                </label>
                                <input wire:model="editCompany" type="text" placeholder="Nombre de la empresa"
                                    class="w-full px-4 py-2.5 text-sm border-2 border-gray-200 rounded-xl focus:border-purple-500 focus:outline-none transition-all">
                            </div>
                        @endif
                    </div>
                </div>

                <div class="px-6 pb-6 flex gap-3">
                    <button wire:click="closeEdit"
                        class="flex-1 py-3 rounded-xl bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold text-sm transition-colors">
                        Cancelar
                    </button>
                    <button wire:click="saveUser"
                        wire:loading.attr="disabled"
                        class="flex-1 py-3 rounded-xl bg-gradient-to-r from-purple-600 to-indigo-600 hover:from-purple-700 hover:to-indigo-700 text-white font-bold text-sm transition-all disabled:opacity-60">
                        <span wire:loading.remove wire:target="saveUser">💾 Guardar</span>
                        <span wire:loading wire:target="saveUser">Guardando...</span>
                    </button>
                </div>
            </div>
        </div>
    @endif

</div>
