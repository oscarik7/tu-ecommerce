<div class="space-y-5">

    {{-- ══ VISTA OPCIONES (dentro de un grupo) ══ --}}
    @if($view === 'options' && $selectedGroup)
        @php $group = $selectedGroup; @endphp

        <div class="flex items-center gap-3">
            <button wire:click="backToGroups"
                class="text-sm text-gray-500 hover:text-gray-800 font-medium flex items-center gap-1 transition-all">
                ← Volver
            </button>
            <span class="text-gray-300">/</span>
            <span class="text-sm font-bold text-gray-700">{{ $group->name }}</span>
        </div>

        {{-- Header del grupo --}}
        <div class="bg-gradient-to-r from-orange-500 to-amber-500 rounded-2xl p-5 text-white">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-xl font-black">{{ $group->name }}</h2>
                    @if($group->description)
                        <p class="text-orange-100 text-sm mt-0.5">{{ $group->description }}</p>
                    @endif
                    <div class="flex gap-3 mt-2 text-xs text-orange-100">
                        <span>{{ $group->required ? '⚠️ Obligatorio' : '✓ Opcional' }}</span>
                        <span>{{ $group->multiple ? '☑ Múltiple' : '◉ Una opción' }}</span>
                        @if($group->max_selections)
                            <span>Máx: {{ $group->max_selections }}</span>
                        @endif
                        @if($group->min_selections > 0)
                            <span>Mín: {{ $group->min_selections }}</span>
                        @endif
                    </div>
                </div>
                <div class="flex gap-2">
                    <button wire:click="openAssignModal({{ $group->id }})"
                        class="bg-white/20 hover:bg-white/30 text-white font-bold px-4 py-2 rounded-xl text-sm transition-all">
                        🔗 Asignar a Productos
                    </button>
                    <button wire:click="openOptionModal()"
                        class="bg-white text-orange-600 hover:bg-orange-50 font-black px-4 py-2 rounded-xl text-sm transition-all">
                        + Agregar Opción
                    </button>
                </div>
            </div>
        </div>

        {{-- Lista de opciones --}}
        <div class="bg-white rounded-2xl shadow-sm border overflow-hidden">
            @if($group->options->count() === 0)
                <div class="p-16 text-center text-gray-400">
                    <div class="text-5xl mb-3">🍓</div>
                    <p class="font-bold text-lg">Sin opciones todavía</p>
                    <p class="text-sm mt-1">Agregá la primera opción con el botón de arriba</p>
                </div>
            @else
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 border-b">
                        <tr>
                            <th class="px-5 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Opción</th>
                            <th class="px-5 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Precio</th>
                            <th class="px-5 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Tipo</th>
                            <th class="px-5 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Orden</th>
                            <th class="px-5 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Estado</th>
                            <th class="px-5 py-3 text-center text-xs font-bold text-gray-500 uppercase tracking-wider">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($group->options->sortBy('sort_order') as $option)
                            <tr class="hover:bg-gray-50 transition-colors {{ !$option->is_active ? 'opacity-50' : '' }}">
                                <td class="px-5 py-3.5">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 rounded-lg overflow-hidden flex-shrink-0 border border-gray-200 bg-gray-50">
                                            @if($option->image_url)
                                                <img src="{{ $option->image_url }}" alt="{{ $option->name }}"
                                                    class="w-full h-full object-cover">
                                            @else
                                                <div class="w-full h-full flex items-center justify-center text-lg">🍓</div>
                                            @endif
                                        </div>
                                        <span class="font-bold text-gray-900">{{ $option->name }}</span>
                                    </div>
                                </td>
                                <td class="px-5 py-3.5">
                                    @if($option->price == 0)
                                        <span class="text-green-600 font-bold text-xs bg-green-50 px-2 py-1 rounded-full">Incluido</span>
                                    @else
                                        <span class="text-orange-600 font-bold">+{{ number_format($option->price, 0, ',', '.') }} Gs</span>
                                    @endif
                                </td>
                                <td class="px-5 py-3.5">
                                    @if($option->price > 0)
                                        <span class="text-xs bg-orange-100 text-orange-700 font-bold px-2 py-1 rounded-full">Extra</span>
                                    @else
                                        <span class="text-xs bg-gray-100 text-gray-600 font-bold px-2 py-1 rounded-full">Base</span>
                                    @endif
                                </td>
                                <td class="px-5 py-3.5 text-gray-400 text-xs">{{ $option->sort_order }}</td>
                                <td class="px-5 py-3.5">
                                    @if($option->is_active)
                                        <span class="text-xs bg-green-100 text-green-700 font-bold px-2 py-1 rounded-full">Activo</span>
                                    @else
                                        <span class="text-xs bg-gray-100 text-gray-500 font-bold px-2 py-1 rounded-full">Inactivo</span>
                                    @endif
                                </td>
                                <td class="px-5 py-3.5">
                                    <div class="flex items-center justify-center gap-1.5">
                                        <button wire:click="openOptionModal({{ $option->id }})"
                                            class="p-2 rounded-xl bg-blue-50 hover:bg-blue-100 text-blue-600 transition-all" title="Editar">
                                            ✏️
                                        </button>
                                        <button wire:click="toggleOptionActive({{ $option->id }})"
                                            class="p-2 rounded-xl {{ $option->is_active ? 'bg-red-50 hover:bg-red-100 text-red-500' : 'bg-gray-50 hover:bg-gray-100 text-gray-500' }} transition-all">
                                            {{ $option->is_active ? '🚫' : '✅' }}
                                        </button>
                                        <button wire:click="deleteOption({{ $option->id }})"
                                            wire:confirm="¿Eliminar esta opción?"
                                            class="p-2 rounded-xl bg-red-50 hover:bg-red-100 text-red-500 transition-all" title="Eliminar">
                                            🗑️
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>

    {{-- ══ VISTA GRUPOS (lista principal) ══ --}}
    @else

        <div class="bg-gradient-to-r from-orange-500 to-amber-500 rounded-2xl p-5 text-white">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-black">🍓 Complementos</h1>
                    <p class="text-orange-100 text-sm mt-0.5">Grupos de toppings, salsas y extras para los bowls</p>
                </div>
                <button wire:click="openGroupModal()"
                    class="bg-white text-orange-600 hover:bg-orange-50 font-black px-5 py-2.5 rounded-xl text-sm transition-all shadow">
                    + Nuevo Grupo
                </button>
            </div>
        </div>

        @if($groups->count() === 0)
            <div class="bg-white rounded-2xl shadow-sm border p-16 text-center text-gray-400">
                <div class="text-5xl mb-3">🍨</div>
                <p class="font-bold text-lg">Sin grupos de complementos</p>
                <p class="text-sm mt-1">Creá el primer grupo con el botón de arriba</p>
            </div>
        @else
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach($groups as $group)
                    <div class="bg-white rounded-2xl shadow-sm border overflow-hidden hover:shadow-md transition-all {{ !$group->is_active ? 'opacity-60' : '' }}">
                        <div class="bg-gradient-to-r
                            @if(!$group->is_active) from-gray-300 to-gray-400
                            @elseif($group->required) from-red-400 to-rose-500
                            @else from-orange-400 to-amber-400 @endif
                            p-4 text-white">
                            <div class="flex items-start justify-between">
                                <div>
                                    <h3 class="font-black text-lg leading-tight">{{ $group->name }}</h3>
                                    @if($group->description)
                                        <p class="text-white/80 text-xs mt-0.5 line-clamp-1">{{ $group->description }}</p>
                                    @endif
                                </div>
                                @if($group->required)
                                    <span class="text-xs bg-white/30 font-bold px-2 py-0.5 rounded-full flex-shrink-0">Obligatorio</span>
                                @endif
                            </div>
                            <div class="flex gap-2 mt-2 text-xs text-white/70">
                                <span>{{ $group->multiple ? '☑ Múltiple' : '◉ Única' }}</span>
                                @if($group->max_selections)
                                    <span>· Máx {{ $group->max_selections }}</span>
                                @endif
                            </div>
                        </div>
                        <div class="px-4 py-3 flex items-center gap-4 border-b">
                            <button wire:click="selectGroup({{ $group->id }})"
                                class="flex-1 text-center hover:bg-orange-50 rounded-xl py-2 transition-all group">
                                <div class="text-2xl font-black text-gray-900 group-hover:text-orange-600">{{ $group->options_count }}</div>
                                <div class="text-xs text-gray-400">opciones</div>
                            </button>
                            <div class="w-px h-10 bg-gray-200"></div>
                            <button wire:click="openAssignModal({{ $group->id }})"
                                class="flex-1 text-center hover:bg-blue-50 rounded-xl py-2 transition-all group">
                                <div class="text-2xl font-black text-gray-900 group-hover:text-blue-600">{{ $group->products_count }}</div>
                                <div class="text-xs text-gray-400">productos</div>
                            </button>
                        </div>
                        <div class="px-4 py-3 flex gap-2">
                            <button wire:click="selectGroup({{ $group->id }})"
                                class="flex-1 bg-orange-50 hover:bg-orange-100 text-orange-600 font-bold py-2 rounded-xl text-xs transition-all">
                                Ver opciones
                            </button>
                            <button wire:click="openGroupModal({{ $group->id }})"
                                class="p-2 rounded-xl bg-blue-50 hover:bg-blue-100 text-blue-600 transition-all" title="Editar">
                                ✏️
                            </button>
                            <button wire:click="toggleGroupActive({{ $group->id }})"
                                class="p-2 rounded-xl {{ $group->is_active ? 'bg-red-50 hover:bg-red-100 text-red-400' : 'bg-gray-50 hover:bg-gray-100 text-gray-400' }} transition-all">
                                {{ $group->is_active ? '🚫' : '✅' }}
                            </button>
                            <button wire:click="deleteGroup({{ $group->id }})"
                                wire:confirm="¿Eliminar este grupo y todas sus opciones?"
                                class="p-2 rounded-xl bg-red-50 hover:bg-red-100 text-red-400 transition-all" title="Eliminar">
                                🗑️
                            </button>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    @endif

    {{-- ══ MODAL GRUPO ══ --}}
    @if($showGroupModal)
        <div class="fixed inset-0 bg-black/60 backdrop-blur-sm flex items-center justify-center p-4 z-50"
             x-on:keydown.escape.window="$wire.closeGroupModal()">
            <div class="bg-white rounded-2xl w-full max-w-md shadow-2xl flex flex-col max-h-[90vh]">

                <div class="bg-gradient-to-r from-orange-500 to-amber-500 px-5 py-4 text-white flex-shrink-0">
                    <h2 class="text-lg font-black">{{ $editingGroupId ? '✏️ Editar Grupo' : '✨ Nuevo Grupo' }}</h2>
                </div>

                <div class="overflow-y-auto flex-1 p-5 space-y-4">
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1">
                            Nombre <span class="text-red-500">*</span>
                        </label>
                        <input wire:model="groupName" type="text"
                            class="w-full px-3 py-2.5 border-2 border-gray-300 rounded-xl focus:ring-2 focus:ring-orange-400 focus:border-orange-400 text-sm"
                            placeholder="Ej: Toppings, Salsas, Extras...">
                        @error('groupName') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1">Descripción</label>
                        <input wire:model="groupDesc" type="text"
                            class="w-full px-3 py-2.5 border-2 border-gray-300 rounded-xl focus:ring-2 focus:ring-orange-400 text-sm"
                            placeholder="Descripción visible al cliente...">
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <label class="flex items-center gap-2 cursor-pointer px-3 py-3 rounded-xl border-2 transition-all
                            {{ $groupRequired ? 'border-red-400 bg-red-50' : 'border-gray-200' }}">
                            <input wire:model.live="groupRequired" type="checkbox" class="w-4 h-4 rounded text-red-500">
                            <div>
                                <div class="text-sm font-bold text-gray-800">Obligatorio</div>
                                <div class="text-xs text-gray-400">Debe elegir al menos 1</div>
                            </div>
                        </label>
                        <label class="flex items-center gap-2 cursor-pointer px-3 py-3 rounded-xl border-2 transition-all
                            {{ $groupMultiple ? 'border-orange-400 bg-orange-50' : 'border-gray-200' }}">
                            <input wire:model.live="groupMultiple" type="checkbox" class="w-4 h-4 rounded text-orange-500">
                            <div>
                                <div class="text-sm font-bold text-gray-800">Múltiple</div>
                                <div class="text-xs text-gray-400">Puede elegir varios</div>
                            </div>
                        </label>
                    </div>

                    @if($groupMultiple)
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1">Mínimo</label>
                                <input wire:model="groupMin" type="number" min="0"
                                    class="w-full px-3 py-2.5 border-2 border-gray-300 rounded-xl focus:ring-2 focus:ring-orange-400 text-sm"
                                    placeholder="0">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1">Máximo</label>
                                <input wire:model="groupMax" type="number" min="1"
                                    class="w-full px-3 py-2.5 border-2 border-gray-300 rounded-xl focus:ring-2 focus:ring-orange-400 text-sm"
                                    placeholder="Sin límite">
                            </div>
                        </div>
                    @endif

                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1">Orden de aparición</label>
                        <input wire:model="groupSort" type="number" min="0"
                            class="w-full px-3 py-2.5 border-2 border-gray-300 rounded-xl focus:ring-2 focus:ring-orange-400 text-sm">
                    </div>
                </div>

                <div class="px-5 py-4 bg-gray-50 border-t flex gap-3 flex-shrink-0">
                    <button wire:click="closeGroupModal"
                        class="flex-1 bg-gray-200 hover:bg-gray-300 text-gray-700 font-bold py-2.5 rounded-xl text-sm">
                        Cancelar
                    </button>
                    <button wire:click="saveGroup" wire:loading.attr="disabled"
                        class="flex-1 bg-orange-500 hover:bg-orange-600 text-white font-bold py-2.5 rounded-xl text-sm disabled:opacity-50">
                        <span wire:loading.remove wire:target="saveGroup">✓ {{ $editingGroupId ? 'Actualizar' : 'Crear' }}</span>
                        <span wire:loading wire:target="saveGroup">Guardando...</span>
                    </button>
                </div>
            </div>
        </div>
    @endif

    {{-- ══ MODAL OPCIÓN ══ --}}
    @if($showOptionModal)
        <div class="fixed inset-0 bg-black/60 backdrop-blur-sm flex items-center justify-center p-4 z-50"
             x-on:keydown.escape.window="$wire.closeOptionModal()">
            <div class="bg-white rounded-2xl w-full max-w-sm shadow-2xl flex flex-col max-h-[90vh]">

                <div class="bg-gradient-to-r from-orange-400 to-amber-400 px-5 py-4 text-white flex-shrink-0">
                    <h2 class="text-lg font-black">{{ $editingOptionId ? '✏️ Editar Opción' : '+ Nueva Opción' }}</h2>
                </div>

                <div class="overflow-y-auto flex-1 p-5 space-y-4">

                    {{-- ── IMAGEN ── --}}
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">
                            Foto del complemento
                            <span class="text-gray-400 font-normal normal-case ml-1">(opcional · máx. 2MB)</span>
                        </label>

                        @if($optionImageCurrent && !$optionImage)
                            <div class="relative w-full mb-3 rounded-xl overflow-hidden border-2 border-gray-200 bg-gray-50 flex items-center justify-center" style="height:140px">
                                <img src="{{ $optionImageCurrent }}" alt="Imagen actual"
                                    class="max-h-full max-w-full object-contain p-2">
                                <button type="button" wire:click="removeOptionImage"
                                    class="absolute top-2 right-2 w-7 h-7 bg-red-500 hover:bg-red-600 text-white rounded-full flex items-center justify-center text-xs font-bold shadow transition-all"
                                    title="Quitar imagen">✕</button>
                            </div>
                        @elseif($optionImage)
                            <div class="relative w-full mb-3 rounded-xl overflow-hidden border-2 border-purple-300 bg-purple-50 flex items-center justify-center" style="height:140px">
                                <img src="{{ $optionImage->temporaryUrl() }}" alt="Vista previa"
                                    class="max-h-full max-w-full object-contain p-2">
                                <button type="button" wire:click="$set('optionImage', null)"
                                    class="absolute top-2 right-2 w-7 h-7 bg-red-500 hover:bg-red-600 text-white rounded-full flex items-center justify-center text-xs font-bold shadow transition-all"
                                    title="Cancelar selección">✕</button>
                                <span class="absolute bottom-2 left-2 text-[10px] bg-purple-600 text-white px-2 py-0.5 rounded-full font-bold">Nueva imagen</span>
                            </div>
                        @else
                            <label for="optionImageInput"
                                class="flex flex-col items-center justify-center w-full border-2 border-dashed border-gray-300 rounded-xl bg-gray-50 hover:bg-gray-100 hover:border-orange-400 cursor-pointer transition-all mb-3"
                                style="height:100px">
                                <span class="text-3xl mb-1">📷</span>
                                <span class="text-xs text-gray-500">Clic para subir foto</span>
                                <span class="text-[10px] text-gray-400">JPG, PNG, WEBP</span>
                            </label>
                        @endif

                        <input id="optionImageInput" type="file" wire:model="optionImage" accept="image/*" class="hidden">

                        @if(!$optionImage && !$optionImageCurrent)
                            <button type="button" onclick="document.getElementById('optionImageInput').click()"
                                class="w-full py-2 text-xs font-bold text-gray-500 hover:text-orange-600 border border-gray-200 rounded-xl hover:border-orange-300 transition-all">
                                📷 Seleccionar imagen
                            </button>
                        @elseif(!$optionImage && $optionImageCurrent)
                            <button type="button" onclick="document.getElementById('optionImageInput').click()"
                                class="w-full py-2 text-xs font-bold text-gray-500 hover:text-orange-600 border border-gray-200 rounded-xl hover:border-orange-300 transition-all">
                                🔄 Cambiar imagen
                            </button>
                        @endif

                        @error('optionImage') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    {{-- ── NOMBRE ── --}}
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1">
                            Nombre <span class="text-red-500">*</span>
                        </label>
                        <input wire:model="optionName" type="text"
                            class="w-full px-3 py-2.5 border-2 border-gray-300 rounded-xl focus:ring-2 focus:ring-orange-400 text-sm"
                            placeholder="Ej: Granola, Banana, Extra Açaí...">
                        @error('optionName') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    {{-- ── PRECIO ── --}}
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">
                            Precio extra
                        </label>
                        <div class="relative">
                            <input wire:model="optionPrice" type="number" min="0" step="500"
                                class="w-full px-4 py-3 text-xl font-black text-center border-2 border-gray-300 rounded-xl focus:ring-2 focus:ring-orange-400"
                                placeholder="0">
                            <span class="absolute right-4 top-1/2 -translate-y-1/2 text-gray-400 text-sm font-medium">Gs</span>
                        </div>
                        <p class="text-xs text-gray-400 mt-1 text-center">
                            {{ (int)$optionPrice === 0 ? '✓ Incluido sin costo extra' : '+' . number_format((int)$optionPrice, 0, ',', '.') . ' Gs al total' }}
                        </p>
                        @error('optionPrice') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    {{-- ── ATAJOS PRECIO ── --}}
                    <div class="grid grid-cols-4 gap-1.5">
                        @foreach([0, 2000, 3000, 5000, 8000, 10000, 15000, 20000] as $quick)
                            <button wire:click="$set('optionPrice', {{ $quick }})" type="button"
                                class="text-xs py-1.5 font-bold rounded-lg border-2 transition-all
                                    {{ (int)$optionPrice === $quick ? 'border-orange-400 bg-orange-50 text-orange-600' : 'border-gray-200 hover:border-orange-300 text-gray-600' }}">
                                {{ $quick === 0 ? 'Gratis' : number_format($quick/1000, 0).'k' }}
                            </button>
                        @endforeach
                    </div>

                    {{-- ── ORDEN ── --}}
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1">Orden</label>
                        <input wire:model="optionSort" type="number" min="0"
                            class="w-full px-3 py-2.5 border-2 border-gray-300 rounded-xl focus:ring-2 focus:ring-orange-400 text-sm">
                    </div>
                </div>

                <div class="px-5 py-4 bg-gray-50 border-t flex gap-3 flex-shrink-0">
                    <button wire:click="closeOptionModal"
                        class="flex-1 bg-gray-200 hover:bg-gray-300 text-gray-700 font-bold py-2.5 rounded-xl text-sm">
                        Cancelar
                    </button>
                    <button wire:click="saveOption" wire:loading.attr="disabled"
                        class="flex-1 bg-orange-500 hover:bg-orange-600 text-white font-bold py-2.5 rounded-xl text-sm disabled:opacity-50">
                        <span wire:loading.remove wire:target="saveOption">✓ {{ $editingOptionId ? 'Actualizar' : 'Agregar' }}</span>
                        <span wire:loading wire:target="saveOption">Guardando...</span>
                    </button>
                </div>
            </div>
        </div>
    @endif

    {{-- ══ MODAL ASIGNACIÓN A PRODUCTOS ══ --}}
    @if($showAssignModal && $assigningGroup)
        <div class="fixed inset-0 bg-black/60 backdrop-blur-sm flex items-center justify-center p-4 z-50"
             x-on:keydown.escape.window="$wire.closeAssignModal()">
            <div class="bg-white rounded-2xl w-full max-w-lg shadow-2xl flex flex-col max-h-[85vh]">

                <div class="bg-gradient-to-r from-blue-500 to-indigo-600 px-5 py-4 text-white flex-shrink-0">
                    <h2 class="text-lg font-black">🔗 Asignar a Productos</h2>
                    <p class="text-blue-100 text-sm mt-0.5">
                        Grupo: <strong>{{ $assigningGroup->name }}</strong>
                        · {{ count($selectedProductIds) }} seleccionados
                    </p>
                </div>

                <div class="px-5 py-3 border-b bg-gray-50 flex-shrink-0">
                    <select wire:model.live="filterCategoryId"
                        class="w-full px-3 py-2 border-2 border-gray-300 rounded-xl text-sm focus:ring-2 focus:ring-blue-400">
                        <option value="">Todas las categorías</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="overflow-y-auto flex-1 divide-y divide-gray-100">
                    @forelse($assignProducts as $product)
                        <label class="flex items-center gap-3 px-5 py-3 hover:bg-gray-50 cursor-pointer transition-colors">
                            <input type="checkbox"
                                wire:click="toggleProductAssign({{ $product->id }})"
                                @checked(in_array($product->id, $selectedProductIds))
                                class="w-5 h-5 rounded text-blue-500">
                            <div class="flex-1">
                                <div class="font-bold text-gray-900 text-sm">{{ $product->name }}</div>
                                <div class="text-xs text-gray-400">{{ $product->category->name ?? '—' }}</div>
                            </div>
                            @if(in_array($product->id, $selectedProductIds))
                                <span class="text-xs bg-blue-100 text-blue-600 font-bold px-2 py-0.5 rounded-full">✓</span>
                            @endif
                        </label>
                    @empty
                        <div class="p-8 text-center text-gray-400 text-sm">
                            Sin productos en esta categoría
                        </div>
                    @endforelse
                </div>

                <div class="px-5 py-4 bg-gray-50 border-t flex gap-3 flex-shrink-0">
                    <button wire:click="closeAssignModal"
                        class="flex-1 bg-gray-200 hover:bg-gray-300 text-gray-700 font-bold py-2.5 rounded-xl text-sm">
                        Cancelar
                    </button>
                    <button wire:click="saveAssign" wire:loading.attr="disabled"
                        class="flex-1 bg-blue-500 hover:bg-blue-600 text-white font-bold py-2.5 rounded-xl text-sm disabled:opacity-50">
                        <span wire:loading.remove wire:target="saveAssign">✓ Guardar asignación</span>
                        <span wire:loading wire:target="saveAssign">Guardando...</span>
                    </button>
                </div>
            </div>
        </div>
    @endif

</div>