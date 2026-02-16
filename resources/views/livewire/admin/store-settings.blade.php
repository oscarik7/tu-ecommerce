<div class="space-y-6 max-w-2xl mx-auto">

    {{-- Header --}}
    <div class="bg-gradient-to-r from-purple-600 to-purple-800 rounded-2xl p-5 text-white">
        <h1 class="text-2xl font-black">⚙️ Configuración de Tienda</h1>
        <p class="text-purple-200 text-sm mt-0.5">Horarios de atención y datos de contacto</p>
    </div>

    {{-- ── TELÉFONO ── --}}
    <div class="bg-white rounded-2xl shadow-sm border p-6 space-y-4">
        <h2 class="text-base font-black text-gray-800 flex items-center gap-2">
            📱 Teléfono / WhatsApp
        </h2>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1">
                    Número WhatsApp
                    <span class="text-gray-400 font-normal normal-case ml-1">(sin + ni espacios)</span>
                </label>
                <div class="relative">
                    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm">+</span>
                    <input wire:model="phoneWhatsapp" type="text"
                        class="w-full pl-6 pr-3 py-2.5 border-2 border-gray-300 rounded-xl focus:ring-2 focus:ring-purple-400 focus:border-purple-400 text-sm"
                        placeholder="595986150627">
                </div>
                @error('phoneWhatsapp') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                <p class="text-xs text-gray-400 mt-1">Se usa en el link wa.me/{{ $phoneWhatsapp }}</p>
            </div>

            <div>
                <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1">
                    Número visible al cliente
                </label>
                <input wire:model="phoneDisplay" type="text"
                    class="w-full px-3 py-2.5 border-2 border-gray-300 rounded-xl focus:ring-2 focus:ring-purple-400 focus:border-purple-400 text-sm"
                    placeholder="+595 986 150627">
                @error('phoneDisplay') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                <p class="text-xs text-gray-400 mt-1">Aparece en el footer y botón de WhatsApp</p>
            </div>
        </div>
    </div>

    {{-- ── HORARIOS ── --}}
    <div class="bg-white rounded-2xl shadow-sm border p-6 space-y-4">
        <h2 class="text-base font-black text-gray-800 flex items-center gap-2">
            🕐 Horarios de Atención
        </h2>

        <div class="space-y-2">
            @foreach($dayNames as $day => $name)
                <div class="flex items-center gap-3 py-2.5 px-3 rounded-xl {{ $schedule[$day]['open'] ? 'bg-green-50 border border-green-200' : 'bg-gray-50 border border-gray-200' }} transition-all">

                    {{-- Toggle abierto/cerrado --}}
                    <label class="flex items-center gap-2 cursor-pointer flex-shrink-0 w-28">
                        <input type="checkbox"
                            wire:model.live="schedule.{{ $day }}.open"
                            class="w-4 h-4 rounded text-green-500">
                        <span class="text-sm font-bold {{ $schedule[$day]['open'] ? 'text-gray-800' : 'text-gray-400' }}">
                            {{ $name }}
                        </span>
                    </label>

                    @if($schedule[$day]['open'])
                        {{-- Desde --}}
                        <div class="flex items-center gap-1.5">
                            <span class="text-xs text-gray-500">Desde</span>
                            <input type="time"
                                wire:model="schedule.{{ $day }}.from"
                                class="px-2 py-1.5 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-400 text-sm font-bold text-gray-800 w-28">
                        </div>

                        {{-- Hasta --}}
                        <div class="flex items-center gap-1.5">
                            <span class="text-xs text-gray-500">Hasta</span>
                            <input type="time"
                                wire:model="schedule.{{ $day }}.to"
                                class="px-2 py-1.5 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-400 text-sm font-bold text-gray-800 w-28">
                        </div>

                        {{-- Aplicar a todos --}}
                        <button wire:click="applyToAll({{ $day }})" type="button"
                            title="Aplicar este horario a todos los días"
                            class="ml-auto text-xs text-purple-500 hover:text-purple-700 font-bold px-2 py-1 rounded-lg hover:bg-purple-50 transition-all flex-shrink-0">
                            Aplicar a todos
                        </button>
                    @else
                        <span class="text-xs text-gray-400 italic">Cerrado</span>
                    @endif
                </div>

                @error("schedule.{$day}.from") <p class="text-red-500 text-xs ml-3">{{ $message }}</p> @enderror
                @error("schedule.{$day}.to")   <p class="text-red-500 text-xs ml-3">{{ $message }}</p> @enderror
            @endforeach
        </div>
    </div>

    {{-- Botón guardar --}}
    <div class="flex justify-end">
        <button wire:click="save" wire:loading.attr="disabled"
            class="bg-purple-600 hover:bg-purple-700 text-white font-black px-8 py-3 rounded-xl text-sm transition-all disabled:opacity-50 shadow-lg">
            <span wire:loading.remove wire:target="save">✓ Guardar cambios</span>
            <span wire:loading wire:target="save">Guardando...</span>
        </button>
    </div>

</div>