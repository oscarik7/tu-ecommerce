<div>
    @if (session()->has('message'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
            {{ session('message') }}
        </div>
    @endif

    <!-- Header -->
    <div class="flex justify-between items-center mb-6">
        <input wire:model.live="search" type="text" placeholder="Buscar métodos de pago..."
            class="flex-1 max-w-md px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
        <button wire:click="create"
            class="ml-4 bg-purple-600 hover:bg-purple-700 text-white px-6 py-2 rounded-lg transition font-medium">
            + Nuevo Método
        </button>
    </div>

    <!-- Tabla -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nombre</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tipo</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Disponible para</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Estado</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($methods as $method)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4">
                            <div class="text-sm font-medium text-gray-900">{{ $method->name }}</div>
                            @if($method->description)
                                <div class="text-xs text-gray-400 mt-0.5">{{ Str::limit($method->description, 50) }}</div>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 py-1 text-xs font-semibold rounded-full
                                {{ $method->type == 'bank_transfer' ? 'bg-blue-100 text-blue-800' : 'bg-green-100 text-green-800' }}">
                                {{ $method->type == 'bank_transfer' ? 'Transferencia' : ucfirst($method->type) }}
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex flex-wrap gap-1">
                                @if(empty($method->allowed_delivery_types))
                                    <span class="text-xs bg-gray-100 text-gray-600 px-2 py-0.5 rounded-full">🚚🏪 Todos</span>
                                @else
                                    @if(in_array('delivery', $method->allowed_delivery_types))
                                        <span class="text-xs bg-blue-100 text-blue-700 px-2 py-0.5 rounded-full">🚚 Delivery</span>
                                    @endif
                                    @if(in_array('pickup', $method->allowed_delivery_types))
                                        <span class="text-xs bg-green-100 text-green-700 px-2 py-0.5 rounded-full">🏪 Retiro</span>
                                    @endif
                                @endif
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <button wire:click="toggleActive({{ $method->id }})"
                                class="px-3 py-1 rounded-full text-xs font-semibold
                                    {{ $method->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                {{ $method->is_active ? 'Activo' : 'Inactivo' }}
                            </button>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                            <button wire:click="edit({{ $method->id }})"
                                class="text-purple-600 hover:text-purple-900 font-medium mr-3">
                                Editar
                            </button>
                            <button wire:click="delete({{ $method->id }})"
                                wire:confirm="¿Estás seguro de eliminar este método de pago?"
                                class="text-red-600 hover:text-red-900 font-medium">
                                Eliminar
                            </button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-6 py-12 text-center text-gray-500">
                            No se encontraron métodos de pago.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Paginación -->
    <div class="mt-4">
        {{ $methods->links() }}
    </div>

    <!-- Modal -->
    @if($showModal)
        <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50" wire:click="closeModal">
            <div class="bg-white rounded-lg max-w-2xl w-full max-h-[90vh] overflow-y-auto" wire:click.stop>
                <div class="p-6">
                    <div class="flex justify-between items-start mb-6">
                        <h2 class="text-2xl font-bold text-gray-900">
                            {{ $editMode ? 'Editar Método de Pago' : 'Nuevo Método de Pago' }}
                        </h2>
                        <button wire:click="closeModal" class="text-gray-500 hover:text-gray-700">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>

                    <form wire:submit.prevent="save" class="space-y-4">

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Nombre</label>
                            <input wire:model="name" type="text" required
                                placeholder="Ej: Transferencia Bancaria Itaú"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                            @error('name') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Tipo</label>
                            <select wire:model.live="type" required
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                                <option value="bank_transfer">Transferencia Bancaria</option>
                                <option value="cash">Efectivo</option>
                                <option value="credit_card">Tarjeta de Crédito</option>
                                <option value="debit_card">Tarjeta de Débito</option>
                                <option value="other">Otro</option>
                            </select>
                            @error('type') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Descripción</label>
                            <textarea wire:model="description" rows="2"
                                placeholder="Descripción breve del método de pago"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent"></textarea>
                            @error('description') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Instrucciones</label>
                            <textarea wire:model="instructions" rows="3"
                                placeholder="Instrucciones para el cliente sobre cómo realizar el pago"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent"></textarea>
                            @error('instructions') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>

                        @if($type == 'bank_transfer')
                            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 space-y-3">
                                <h3 class="font-semibold text-gray-900">Datos Bancarios</h3>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Banco</label>
                                    <input wire:model="bank_name" type="text" placeholder="Ej: Banco Itaú"
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Número de Cuenta</label>
                                    <input wire:model="account_number" type="text" placeholder="123456789"
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Titular de la Cuenta</label>
                                    <input wire:model="account_holder" type="text" placeholder="Nombre del titular"
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">RUC</label>
                                    <input wire:model="ruc" type="text" placeholder="12345678-9"
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                                </div>
                            </div>
                        @endif

                        {{-- ── Disponible para ── --}}
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-2">
                                🚚 Disponible para
                                <span class="text-xs font-normal text-gray-400 ml-1">
                                    (desactivá los que no aplican)
                                </span>
                            </label>
                            <div class="flex gap-3">
                                <label class="flex items-center gap-2 cursor-pointer select-none
                                    px-4 py-2.5 rounded-xl border-2 transition-all flex-1 justify-center
                                    {{ in_array('delivery', $allowed_delivery)
                                        ? 'border-purple-500 bg-purple-50 text-purple-700'
                                        : 'border-gray-200 text-gray-400' }}">
                                    <input type="checkbox"
                                        wire:model.live="allowed_delivery"
                                        value="delivery"
                                        class="sr-only">
                                    <span class="text-lg">🚚</span>
                                    <span class="font-bold text-sm">Delivery</span>
                                    @if(in_array('delivery', $allowed_delivery))
                                        <span class="text-purple-500">✓</span>
                                    @endif
                                </label>

                                <label class="flex items-center gap-2 cursor-pointer select-none
                                    px-4 py-2.5 rounded-xl border-2 transition-all flex-1 justify-center
                                    {{ in_array('pickup', $allowed_delivery)
                                        ? 'border-purple-500 bg-purple-50 text-purple-700'
                                        : 'border-gray-200 text-gray-400' }}">
                                    <input type="checkbox"
                                        wire:model.live="allowed_delivery"
                                        value="pickup"
                                        class="sr-only">
                                    <span class="text-lg">🏪</span>
                                    <span class="font-bold text-sm">Retiro</span>
                                    @if(in_array('pickup', $allowed_delivery))
                                        <span class="text-purple-500">✓</span>
                                    @endif
                                </label>
                            </div>
                            @if(empty($allowed_delivery))
                                <p class="text-xs text-red-500 mt-1.5">
                                    ⚠️ Seleccioná al menos un tipo de entrega
                                </p>
                            @endif
                        </div>

                        {{-- ── Activo ── --}}
                        <div class="flex items-center">
                            <input wire:model="is_active" type="checkbox" id="is_active"
                                class="h-4 w-4 text-purple-600 focus:ring-purple-500 border-gray-300 rounded">
                            <label for="is_active" class="ml-2 block text-sm text-gray-900">
                                Método de pago activo
                            </label>
                        </div>

                        <div class="flex justify-end gap-3 pt-4 border-t">
                            <button type="button" wire:click="closeModal"
                                class="px-6 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition">
                                Cancelar
                            </button>
                            <button type="submit"
                                class="px-6 py-2 bg-purple-600 hover:bg-purple-700 text-white rounded-lg transition">
                                {{ $editMode ? 'Actualizar' : 'Crear' }} Método
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
</div>