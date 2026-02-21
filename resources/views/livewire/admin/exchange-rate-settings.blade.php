<div>
    {{-- Notificaciones --}}
    <div x-data="{ notifications: [] }"
         x-on:show-notification.window="
            notifications.push($event.detail[0] || $event.detail);
            setTimeout(() => notifications.shift(), 3000);
         "
         class="fixed top-4 right-4 z-50 space-y-2">
        <template x-for="(n, i) in notifications" :key="i">
            <div x-transition
                 :class="{ 'bg-green-500': n.type==='success', 'bg-red-500': n.type==='error' }"
                 class="text-white px-4 py-2 rounded-lg shadow-lg font-bold text-sm">
                <span x-text="n.message"></span>
            </div>
        </template>
    </div>

    <div class="max-w-2xl mx-auto">
        <div class="bg-white rounded-2xl shadow-lg overflow-hidden">
            
            {{-- Header --}}
            <div class="bg-gradient-to-r from-blue-500 to-blue-600 px-6 py-5 text-white">
                <h2 class="text-2xl font-black">💱 Cotización del Real</h2>
                <p class="text-blue-100 text-sm mt-1">Configurar el tipo de cambio BRL → Gs</p>
            </div>

            {{-- Contenido --}}
            <div class="p-6 space-y-6">
                
                {{-- Explicación --}}
                <div class="bg-blue-50 border-l-4 border-blue-500 p-4 rounded-r-lg">
                    <div class="flex items-start gap-3">
                        <span class="text-2xl">ℹ️</span>
                        <div class="text-sm text-gray-700">
                            <p class="font-bold mb-1">¿Para qué sirve?</p>
                            <p>Esta cotización se usa en el POS para calcular el vuelto cuando un cliente paga con reales. Actualizala según la cotización del día.</p>
                        </div>
                    </div>
                </div>

                {{-- Campo cotización --}}
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-2">
                        Cotización actual (1 R$ = ? Gs) <span class="text-red-500">*</span>
                    </label>
                    <div class="relative">
                        <input wire:model="exchangeRateBrl" type="number" min="1" step="10"
                            class="w-full px-4 py-3 text-2xl font-bold text-center border-2 border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                            placeholder="3700">
                        <span class="absolute right-4 top-1/2 -translate-y-1/2 text-gray-400 font-medium">Gs</span>
                    </div>
                    @error('exchangeRateBrl') 
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p> 
                    @enderror

                    {{-- Atajos rápidos --}}
                    <div class="grid grid-cols-4 gap-2 mt-3">
                        @foreach([3500, 3600, 3700, 3800] as $rate)
                            <button wire:click="$set('exchangeRateBrl', {{ $rate }})" type="button"
                                class="py-2 text-sm font-bold rounded-lg border-2 
                                    {{ $exchangeRateBrl == $rate ? 'border-blue-500 bg-blue-50 text-blue-700' : 'border-gray-200 hover:border-blue-300 hover:bg-blue-50' }} 
                                    transition-all">
                                {{ number_format($rate, 0, ',', '.') }}
                            </button>
                        @endforeach
                    </div>
                </div>

                {{-- Ejemplo visual --}}
                <div class="bg-gray-50 rounded-xl p-4">
                    <p class="text-xs font-bold text-gray-500 uppercase mb-2">Ejemplo:</p>
                    <div class="space-y-1 text-sm">
                        <div class="flex justify-between">
                            <span class="text-gray-600">Cliente debe:</span>
                            <span class="font-bold">15.000 Gs</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Paga con:</span>
                            <span class="font-bold">10 R$</span>
                        </div>
                        <div class="flex justify-between border-t pt-1">
                            <span class="text-gray-600">Equivale a:</span>
                            <span class="font-bold text-green-600">{{ number_format($exchangeRateBrl * 10, 0, ',', '.') }} Gs</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Vuelto:</span>
                            <span class="font-bold text-blue-600">{{ number_format(($exchangeRateBrl * 10) - 15000, 0, ',', '.') }} Gs</span>
                        </div>
                    </div>
                </div>

                {{-- Botón guardar --}}
                <button wire:click="save" wire:loading.attr="disabled"
                    class="w-full bg-blue-500 hover:bg-blue-600 text-white font-bold py-3 rounded-xl transition-all shadow-lg disabled:opacity-50">
                    <span wire:loading.remove wire:target="save">💾 Guardar Cotización</span>
                    <span wire:loading wire:target="save">Guardando...</span>
                </button>
            </div>
        </div>

        {{-- Info adicional --}}
        <div class="mt-4 text-center text-sm text-gray-500">
            <p>💡 Tip: Actualizá la cotización al inicio de cada jornada</p>
        </div>
    </div>
</div>