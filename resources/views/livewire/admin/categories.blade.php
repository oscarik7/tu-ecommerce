<div>
    @if (session()->has('message'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
            {{ session('message') }}
        </div>
    @endif

    @if (session()->has('error'))
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
            {{ session('error') }}
        </div>
    @endif

    <!-- Header -->
    <div class="flex justify-between items-center mb-6">
        <input wire:model.live="search" type="text" placeholder="Buscar categorías..." 
            class="flex-1 max-w-md px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
        
        <button wire:click="create" 
            class="ml-4 bg-purple-600 hover:bg-purple-700 text-white px-6 py-2 rounded-lg transition font-medium">
            + Nueva Categoría
        </button>
    </div>

    <!-- Tabla de Categorías -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nombre</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Descripción</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Productos</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Estado</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($categories as $category)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-gray-900">{{ $category->name }}</div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="text-sm text-gray-500">{{ Str::limit($category->description, 60) }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="text-sm text-gray-900">{{ $category->products_count }} productos</span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <button wire:click="toggleActive({{ $category->id }})" 
                                class="px-3 py-1 rounded-full text-xs font-semibold {{ $category->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                {{ $category->is_active ? 'Activa' : 'Inactiva' }}
                            </button>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                            <button wire:click="edit({{ $category->id }})" 
                                class="text-purple-600 hover:text-purple-900 font-medium mr-3">
                                Editar
                            </button>
                            <button wire:click="delete({{ $category->id }})" 
                                wire:confirm="¿Estás seguro de eliminar esta categoría?"
                                class="text-red-600 hover:text-red-900 font-medium">
                                Eliminar
                            </button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-6 py-12 text-center text-gray-500">
                            No se encontraron categorías.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Paginación -->
    <div class="mt-4">
        {{ $categories->links() }}
    </div>

    <!-- Modal -->
    @if($showModal)
        <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50" wire:click="closeModal">
            <div class="bg-white rounded-lg max-w-lg w-full" wire:click.stop>
                <div class="p-6">
                    <div class="flex justify-between items-start mb-6">
                        <h2 class="text-2xl font-bold text-gray-900">
                            {{ $editMode ? 'Editar Categoría' : 'Nueva Categoría' }}
                        </h2>
                        <button wire:click="closeModal" class="text-gray-500 hover:text-gray-700">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>

                    <form wire:submit.prevent="save" class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Nombre</label>
                            <input wire:model="name" type="text" required
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                            @error('name') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Descripción</label>
                            <textarea wire:model="description" rows="3"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent"></textarea>
                            @error('description') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>

                        <div class="flex items-center">
                            <input wire:model="is_active" type="checkbox" id="is_active"
                                class="h-4 w-4 text-purple-600 focus:ring-purple-500 border-gray-300 rounded">
                            <label for="is_active" class="ml-2 block text-sm text-gray-900">
                                Categoría activa
                            </label>
                        </div>

                        <div class="flex justify-end gap-3 pt-4 border-t">
                            <button type="button" wire:click="closeModal"
                                class="px-6 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition">
                                Cancelar
                            </button>
                            <button type="submit"
                                class="px-6 py-2 bg-purple-600 hover:bg-purple-700 text-white rounded-lg transition">
                                {{ $editMode ? 'Actualizar' : 'Crear' }} Categoría
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
</div>