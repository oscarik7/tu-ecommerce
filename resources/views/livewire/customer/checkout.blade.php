<div class="min-h-screen bg-gray-50">

    {{-- Barra de progreso fija arriba --}}
    <div class="sticky top-0 z-40 bg-white border-b border-gray-200 shadow-sm">
        <div class="max-w-5xl mx-auto px-4 py-3">
            <div class="flex items-center justify-between">
                <a href="{{ route('cart') }}" class="flex items-center gap-1.5 text-sm text-gray-500 hover:text-purple-600 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                    <span class="hidden sm:inline">Volver al carrito</span>
                </a>
                <div class="flex items-center gap-2 sm:gap-3">
                    <div class="flex items-center gap-1.5">
                        <div class="w-6 h-6 rounded-full bg-green-500 flex items-center justify-center">
                            <svg class="w-3.5 h-3.5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
                            </svg>
                        </div>
                        <span class="text-xs text-gray-400 hidden sm:inline">Carrito</span>
                    </div>
                    <div class="w-8 sm:w-16 h-0.5 bg-purple-400"></div>
                    <div class="flex items-center gap-1.5">
                        <div class="w-6 h-6 rounded-full bg-purple-600 flex items-center justify-center">
                            <span class="text-xs font-bold text-white">2</span>
                        </div>
                        <span class="text-xs font-semibold text-purple-600 hidden sm:inline">Datos</span>
                    </div>
                    <div class="w-8 sm:w-16 h-0.5 bg-gray-200"></div>
                    <div class="flex items-center gap-1.5">
                        <div class="w-6 h-6 rounded-full bg-gray-200 flex items-center justify-center">
                            <span class="text-xs font-bold text-gray-400">3</span>
                        </div>
                        <span class="text-xs text-gray-400 hidden sm:inline">Confirmación</span>
                    </div>
                </div>
                <span class="text-sm font-bold text-gray-900">
                    {{ number_format($total, 0, ',', '.') }} <span class="text-gray-400 font-normal text-xs">Gs</span>
                </span>
            </div>
        </div>
    </div>

    <div class="max-w-5xl mx-auto px-4 sm:px-6 py-6 sm:py-8">

        @if (session()->has('error'))
            <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-xl mb-5 flex items-center gap-3">
                <svg class="w-5 h-5 flex-shrink-0 text-red-500" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                </svg>
                <span class="text-sm font-medium">{{ session('error') }}</span>
            </div>
        @endif

        <form wire:submit.prevent="placeOrder" novalidate>
            <div class="grid grid-cols-1 lg:grid-cols-5 gap-5">

                {{-- COLUMNA PRINCIPAL --}}
                <div class="lg:col-span-3 space-y-4">

                    {{-- 1. TIPO DE ENTREGA --}}
                    <div class="bg-white rounded-2xl border border-gray-200 overflow-hidden">
                        <div class="px-5 py-4 border-b border-gray-100 flex items-center gap-3">
                            <div class="w-7 h-7 rounded-full bg-purple-100 text-purple-700 flex items-center justify-center text-xs font-black">1</div>
                            <h2 class="font-bold text-gray-900">¿Cómo recibís tu pedido?</h2>
                        </div>
                        <div class="p-4">
                            <div class="grid grid-cols-2 gap-3 mb-4">
                                <button type="button" wire:click="$set('delivery_type', 'delivery')"
                                    class="relative flex flex-col items-center gap-2 p-4 rounded-xl border-2 transition-all duration-200
                                        {{ $delivery_type == 'delivery' ? 'border-purple-500 bg-purple-50' : 'border-gray-200 hover:border-gray-300' }}">
                                    <span class="text-3xl">🚚</span>
                                    <div class="text-center">
                                        <div class="font-bold text-sm {{ $delivery_type == 'delivery' ? 'text-purple-700' : 'text-gray-700' }}">Delivery</div>
                                        <div class="text-xs text-gray-400 mt-0.5">A domicilio</div>
                                    </div>
                                    @if($delivery_type == 'delivery')
                                        <div class="absolute top-2 right-2 w-5 h-5 bg-purple-500 rounded-full flex items-center justify-center">
                                            <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
                                            </svg>
                                        </div>
                                    @endif
                                </button>
                                <button type="button" wire:click="$set('delivery_type', 'pickup')"
                                    class="relative flex flex-col items-center gap-2 p-4 rounded-xl border-2 transition-all duration-200
                                        {{ $delivery_type == 'pickup' ? 'border-purple-500 bg-purple-50' : 'border-gray-200 hover:border-gray-300' }}">
                                    <span class="text-3xl">🏪</span>
                                    <div class="text-center">
                                        <div class="font-bold text-sm {{ $delivery_type == 'pickup' ? 'text-purple-700' : 'text-gray-700' }}">Retiro</div>
                                        <div class="text-xs text-gray-400 mt-0.5">Sin costo extra</div>
                                    </div>
                                    @if($delivery_type == 'pickup')
                                        <div class="absolute top-2 right-2 w-5 h-5 bg-purple-500 rounded-full flex items-center justify-center">
                                            <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
                                            </svg>
                                        </div>
                                    @endif
                                </button>
                            </div>
                            @if($delivery_type == 'delivery')
                                <div class="bg-amber-50 border border-amber-200 rounded-xl p-3 flex gap-2.5 animate-fadeIn">
                                    <span class="text-base flex-shrink-0">📍</span>
                                    <p class="text-xs text-amber-800">
                                        <span class="font-semibold">Ciudad del Este y alrededores.</span>
                                        El costo de envío se coordina por WhatsApp después de confirmar.
                                    </p>
                                </div>
                            @else
                                <div class="bg-blue-50 border border-blue-200 rounded-xl p-3 flex gap-2.5 animate-fadeIn">
                                    <span class="text-base flex-shrink-0">📌</span>
                                    <div class="text-xs text-blue-800 space-y-1">
                                        <div><span class="font-semibold">Av. Principal 123</span>, Ciudad del Este</div>
                                        <div>Mar. a Dom. · <span class="font-semibold">13:00 – 21:00</span> · 🎉 Sin costo</div>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>

                    {{-- 2. DATOS DE CONTACTO --}}
                    <div class="bg-white rounded-2xl border border-gray-200 overflow-hidden">
                        <div class="px-5 py-4 border-b border-gray-100 flex items-center gap-3">
                            <div class="w-7 h-7 rounded-full bg-purple-100 text-purple-700 flex items-center justify-center text-xs font-black">2</div>
                            <h2 class="font-bold text-gray-900">Tus datos de contacto</h2>
                        </div>
                        <div class="p-4 space-y-4">

                            {{-- Nombre --}}
                            <div>
                                <label class="block text-xs font-bold text-gray-500 uppercase tracking-wide mb-1.5">
                                    Nombre completo <span class="text-red-500 normal-case font-normal tracking-normal">*</span>
                                </label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-3 flex items-center pointer-events-none">
                                        <svg class="w-4 h-4 {{ $errors->has('customer_name') ? 'text-red-400' : 'text-gray-300' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                        </svg>
                                    </div>
                                    <input wire:model.blur="customer_name"
                                        type="text"
                                        placeholder="Ej: Juan Pérez"
                                        autocomplete="name"
                                        class="w-full pl-9 pr-4 py-3 text-sm rounded-xl border-2 outline-none transition-all duration-200
                                            @error('customer_name')
                                                border-red-400 bg-red-50 text-red-900 placeholder-red-300 focus:border-red-500 focus:ring-2 focus:ring-red-200
                                            @else
                                                border-gray-200 bg-white focus:border-purple-500 focus:ring-2 focus:ring-purple-100
                                            @enderror">
                                    @error('customer_name')
                                        <div class="absolute inset-y-0 right-3 flex items-center pointer-events-none">
                                            <svg class="w-4 h-4 text-red-500" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                            </svg>
                                        </div>
                                    @enderror
                                </div>
                                @error('customer_name')
                                    <p class="text-xs text-red-600 font-medium mt-1.5 flex items-center gap-1">
                                        <svg class="w-3 h-3 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                        </svg>
                                        {{ $message }}
                                    </p>
                                @enderror
                            </div>

                            {{-- Teléfono --}}
                            <div>
                                <label class="block text-xs font-bold text-gray-500 uppercase tracking-wide mb-1.5">
                                    WhatsApp <span class="text-red-500 normal-case font-normal tracking-normal">*</span>
                                </label>
                                <div class="flex gap-2">
                                    <div class="flex items-center px-3 py-3 bg-gray-50 border-2 border-gray-200 rounded-xl text-sm font-semibold text-gray-600 flex-shrink-0 whitespace-nowrap">
                                        🇵🇾 +595
                                    </div>
                                    <div class="relative flex-1">
                                        <input wire:model.blur="customer_phone"
                                            type="tel"
                                            placeholder="981 123 456"
                                            autocomplete="tel"
                                            class="w-full px-4 py-3 text-sm rounded-xl border-2 outline-none transition-all duration-200
                                                @error('customer_phone')
                                                    border-red-400 bg-red-50 text-red-900 placeholder-red-300 focus:border-red-500 focus:ring-2 focus:ring-red-200
                                                @else
                                                    border-gray-200 bg-white focus:border-purple-500 focus:ring-2 focus:ring-purple-100
                                                @enderror">
                                        @error('customer_phone')
                                            <div class="absolute inset-y-0 right-3 flex items-center pointer-events-none">
                                                <svg class="w-4 h-4 text-red-500" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                                </svg>
                                            </div>
                                        @enderror
                                    </div>
                                </div>
                                @error('customer_phone')
                                    <p class="text-xs text-red-600 font-medium mt-1.5 flex items-center gap-1">
                                        <svg class="w-3 h-3 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                        </svg>
                                        {{ $message }}
                                    </p>
                                @else
                                    <p class="text-xs text-gray-400 mt-1.5">📲 Te confirmamos el pedido por acá</p>
                                @enderror
                            </div>

                            {{-- Dirección (solo delivery) --}}
                            @if($delivery_type == 'delivery')
                                <div class="animate-fadeIn">
                                    <label class="block text-xs font-bold text-gray-500 uppercase tracking-wide mb-1.5">
                                        Dirección de entrega <span class="text-red-500 normal-case font-normal tracking-normal">*</span>
                                    </label>
                                    <div class="relative">
                                        <div class="absolute top-3 left-3 pointer-events-none">
                                            <svg class="w-4 h-4 {{ $errors->has('customer_address') ? 'text-red-400' : 'text-gray-300' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                                            </svg>
                                        </div>
                                        <textarea wire:model.blur="customer_address"
                                            rows="3"
                                            placeholder="Barrio, calle, número, referencias (color de la casa, al lado de…)"
                                            autocomplete="street-address"
                                            class="w-full pl-9 pr-4 py-3 text-sm rounded-xl border-2 outline-none resize-none transition-all duration-200
                                                @error('customer_address')
                                                    border-red-400 bg-red-50 text-red-900 placeholder-red-300 focus:border-red-500 focus:ring-2 focus:ring-red-200
                                                @else
                                                    border-gray-200 bg-white focus:border-purple-500 focus:ring-2 focus:ring-purple-100
                                                @enderror"></textarea>
                                    </div>
                                    @error('customer_address')
                                        <p class="text-xs text-red-600 font-medium mt-1.5 flex items-center gap-1">
                                            <svg class="w-3 h-3 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                            </svg>
                                            {{ $message }}
                                        </p>
                                    @else
                                        <p class="text-xs text-gray-400 mt-1.5">Incluí referencias para encontrarte fácil</p>
                                    @enderror
                                </div>
                            @endif
                        </div>
                    </div>

                    {{-- 3. MÉTODO DE PAGO --}}
                    <div class="bg-white rounded-2xl border border-gray-200 overflow-hidden">
                        <div class="px-5 py-4 border-b border-gray-100 flex items-center gap-3">
                            <div class="w-7 h-7 rounded-full bg-purple-100 text-purple-700 flex items-center justify-center text-xs font-black">3</div>
                            <h2 class="font-bold text-gray-900">¿Cómo vas a pagar?</h2>
                        </div>
                        <div class="p-4 space-y-2">
                            @foreach($paymentMethods as $method)
                                <label class="flex items-start gap-3 p-4 rounded-xl border-2 cursor-pointer transition-all duration-200
                                    {{ $payment_method_id == $method->id ? 'border-purple-500 bg-purple-50' : 'border-gray-200 hover:border-gray-300' }}">
                                    <div class="flex-shrink-0 mt-0.5">
                                        <div class="w-5 h-5 rounded-full border-2 flex items-center justify-center transition-all
                                            {{ $payment_method_id == $method->id ? 'border-purple-500 bg-purple-500' : 'border-gray-300' }}">
                                            @if($payment_method_id == $method->id)
                                                <div class="w-2 h-2 rounded-full bg-white"></div>
                                            @endif
                                        </div>
                                        <input type="radio" wire:model.live="payment_method_id" value="{{ $method->id }}" class="sr-only">
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <div class="font-semibold text-sm text-gray-900">{{ $method->name }}</div>
                                        @if($method->description)
                                            <div class="text-xs text-gray-500 mt-0.5">{{ $method->description }}</div>
                                        @endif
                                        @if($payment_method_id == $method->id)
                                            @if($method->instructions)
                                                <div class="mt-3 p-3 bg-white rounded-lg border border-purple-200 text-xs text-gray-700 leading-relaxed">
                                                    💬 {{ $method->instructions }}
                                                </div>
                                            @endif
                                            @if($method->bank_details ?? false)
                                                <div class="mt-3 p-3 bg-white rounded-lg border border-purple-200 space-y-2">
                                                    <p class="text-xs font-bold text-purple-800 mb-2">🏦 Datos bancarios:</p>
                                                    @foreach(['bank' => 'Banco', 'account_number' => 'Nro. Cuenta', 'account_holder' => 'Titular'] as $key => $label)
                                                        @if(isset($method->bank_details[$key]))
                                                            <div class="flex items-center justify-between">
                                                                <span class="text-xs text-gray-400 w-20">{{ $label }}</span>
                                                                <div class="flex items-center gap-2">
                                                                    <span class="text-xs font-bold text-gray-800">{{ $method->bank_details[$key] }}</span>
                                                                    @if($key === 'account_number')
                                                                        <button type="button"
                                                                            onclick="navigator.clipboard.writeText('{{ $method->bank_details[$key] }}').then(()=>{this.textContent='✓';setTimeout(()=>this.textContent='⧉',1500)})"
                                                                            class="text-purple-400 hover:text-purple-600 text-xs transition-colors" title="Copiar">⧉</button>
                                                                    @endif
                                                                </div>
                                                            </div>
                                                        @endif
                                                    @endforeach
                                                </div>
                                            @endif
                                        @endif
                                    </div>
                                </label>
                            @endforeach
                            @error('payment_method_id')
                                <p class="text-xs text-red-600 font-medium mt-1 flex items-center gap-1">
                                    <svg class="w-3 h-3 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                    </svg>
                                    {{ $message }}
                                </p>
                            @enderror
                        </div>
                    </div>

                    {{-- 4. NOTAS --}}
                    <div class="bg-white rounded-2xl border border-gray-200 overflow-hidden">
                        <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <div class="w-7 h-7 rounded-full bg-gray-100 text-gray-400 flex items-center justify-center text-xs font-black">4</div>
                                <h2 class="font-bold text-gray-900">Notas adicionales</h2>
                            </div>
                            <span class="text-xs bg-gray-100 text-gray-400 px-2 py-0.5 rounded-full">Opcional</span>
                        </div>
                        <div class="p-4">
                            <textarea wire:model="notes" rows="3"
                                placeholder="Sin granola, entregar después de las 18:00, dejar con el portero..."
                                class="w-full px-4 py-3 text-sm border-2 border-gray-200 rounded-xl focus:border-purple-500 focus:ring-2 focus:ring-purple-100 focus:outline-none resize-none transition-all hover:border-gray-300"></textarea>
                        </div>
                    </div>

                    {{-- BOTÓN MÓVIL --}}
                    <div class="lg:hidden pb-2">
                        <button type="submit"
                            wire:loading.attr="disabled"
                            class="w-full bg-gradient-to-r from-purple-600 to-pink-500 hover:from-purple-700 hover:to-pink-600 active:scale-95 text-white font-bold py-4 rounded-2xl shadow-lg transition-all duration-200 flex items-center justify-center gap-2 text-base disabled:opacity-70">
                            <span wire:loading.remove wire:target="placeOrder">
                                ✓ Confirmar — {{ number_format($total, 0, ',', '.') }} Gs
                            </span>
                            <span wire:loading wire:target="placeOrder" class="flex items-center gap-2">
                                <svg class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                                </svg>
                                Procesando...
                            </span>
                        </button>
                    </div>
                </div>

                {{-- RESUMEN LATERAL --}}
                <div class="lg:col-span-2">
                    <div class="bg-white rounded-2xl border border-gray-200 lg:sticky lg:top-20 overflow-hidden">
                        <div class="px-5 py-4 bg-gradient-to-r from-purple-600 to-pink-500">
                            <h2 class="font-bold text-white flex items-center gap-2 text-sm">
                                🛒 Resumen de tu pedido
                            </h2>
                        </div>

                        {{-- Items --}}
                        <div class="p-4 space-y-3 max-h-60 overflow-y-auto border-b border-gray-100">
                            @foreach($cartItems as $item)
                                @php
                                    $extrasItem = collect($item->customizations ?? [])->sum('price');
                                    $itemTotal  = ($item->variant->price + $extrasItem) * $item->quantity;
                                @endphp
                                <div class="flex gap-3">
                                    <div class="w-6 h-6 rounded-full bg-purple-500 text-white text-xs font-bold flex items-center justify-center flex-shrink-0 mt-0.5">
                                        {{ $item->quantity }}
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <div class="flex justify-between items-start gap-2 mb-0.5">
                                            <span class="text-sm font-semibold text-gray-800 leading-tight">{{ $item->product->name }}</span>
                                            <span class="text-sm font-bold text-gray-900 whitespace-nowrap flex-shrink-0">{{ number_format($itemTotal, 0, ',', '.') }} Gs</span>
                                        </div>
                                        <span class="inline-block text-xs text-purple-600 font-semibold">{{ $item->variant->volume }}ml</span>

                                        @if($item->customizations && count($item->customizations))
                                            <div class="mt-1.5 bg-purple-50 rounded-lg p-2 space-y-1">
                                                <div class="flex justify-between text-xs text-gray-400">
                                                    <span>Base</span>
                                                    <span>{{ number_format($item->variant->price, 0, ',', '.') }} Gs</span>
                                                </div>
                                                @foreach($item->customizations as $c)
                                                    <div class="flex justify-between text-xs">
                                                        <span class="text-gray-500">+ {{ $c['name'] }}</span>
                                                        @if($c['price'] > 0)
                                                            <span class="text-orange-500 font-semibold">+{{ number_format($c['price'], 0, ',', '.') }} Gs</span>
                                                        @else
                                                            <span class="text-green-500">incluido</span>
                                                        @endif
                                                    </div>
                                                @endforeach
                                                @if($extrasItem > 0 && $item->quantity > 1)
                                                    <div class="flex justify-between text-xs font-bold text-purple-700 border-t border-purple-200 pt-1">
                                                        <span>× {{ $item->quantity }}</span>
                                                        <span>= {{ number_format($itemTotal, 0, ',', '.') }} Gs</span>
                                                    </div>
                                                @endif
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        {{-- Totales --}}
                        <div class="px-4 py-3 space-y-2 border-b border-gray-100 bg-gray-50">
                            <div class="flex justify-between text-sm text-gray-600">
                                <span>Subtotal</span>
                                <span class="font-semibold">{{ number_format($subtotal, 0, ',', '.') }} Gs</span>
                            </div>
                            <div class="flex justify-between text-sm text-gray-600">
                                <span>Envío</span>
                                @if($delivery_type == 'delivery')
                                    <span class="text-amber-600 font-semibold text-xs bg-amber-50 px-2 py-0.5 rounded-full">A confirmar</span>
                                @else
                                    <span class="text-green-600 font-bold">GRATIS 🎉</span>
                                @endif
                            </div>
                        </div>

                        {{-- Total + botón --}}
                        <div class="p-4">
                            <div class="flex justify-between items-baseline mb-1">
                                <span class="font-bold text-gray-700 text-sm">Total{{ $delivery_type == 'delivery' ? ' parcial' : '' }}</span>
                                <div>
                                    <span class="text-2xl font-black text-gray-900">{{ number_format($total, 0, ',', '.') }}</span>
                                    <span class="text-xs text-gray-400 ml-0.5">Gs</span>
                                </div>
                            </div>
                            @if($delivery_type == 'delivery')
                                <p class="text-xs text-gray-400 mb-4">+ envío a coordinar por WhatsApp</p>
                            @else
                                <div class="mb-4"></div>
                            @endif

                            <button type="submit"
                                wire:loading.attr="disabled"
                                class="hidden lg:flex w-full bg-gradient-to-r from-purple-600 to-pink-500 hover:from-purple-700 hover:to-pink-600 text-white font-bold py-3.5 rounded-xl shadow-md transition-all duration-200 items-center justify-center gap-2 text-sm disabled:opacity-70 hover:shadow-lg">
                                <span wire:loading.remove wire:target="placeOrder" class="flex items-center gap-1.5">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    Confirmar Pedido
                                </span>
                                <span wire:loading wire:target="placeOrder" class="flex items-center gap-2">
                                    <svg class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                                    </svg>
                                    Procesando...
                                </span>
                            </button>

                            <p class="text-center text-xs text-gray-400 mt-3 flex items-center justify-center gap-1">
                                <svg class="w-3.5 h-3.5 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M2.166 4.999A11.954 11.954 0 0010 1.944 11.954 11.954 0 0017.834 5c.11.65.166 1.32.166 2.001 0 5.225-3.34 9.67-8 11.317C5.34 16.67 2 12.225 2 7c0-.682.057-1.35.166-2.001zm11.541 3.708a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                </svg>
                                Compra 100% segura
                            </p>
                        </div>
                    </div>
                </div>

            </div>
        </form>
    </div>
</div>

@push('styles')
<style>
@keyframes fadeIn { from { opacity: 0; transform: translateY(-5px); } to { opacity: 1; transform: translateY(0); } }
.animate-fadeIn { animation: fadeIn 0.2s ease-out; }
.overflow-y-auto::-webkit-scrollbar { width: 3px; }
.overflow-y-auto::-webkit-scrollbar-track { background: transparent; }
.overflow-y-auto::-webkit-scrollbar-thumb { background: #d8b4fe; border-radius: 99px; }
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('livewire:initialized', () => {
    Livewire.on('openWhatsAppNow', (event) => {
        window.open(event.url, '_blank');
        setTimeout(() => { window.location.href = '{{ route("my-orders") }}'; }, 1500);
    });
});
</script>
@endpush