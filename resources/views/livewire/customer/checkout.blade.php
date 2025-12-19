<div class="min-h-screen bg-gradient-to-br from-purple-50 via-white to-pink-50 py-6 sm:py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header con progreso -->
        <div class="mb-8">
            <h1 class="text-3xl sm:text-4xl font-bold text-gray-900 mb-2">💳 Finalizar Pedido</h1>
            <p class="text-gray-600">Complete los datos para confirmar su compra</p>

            <!-- Indicador de progreso -->
            <div class="mt-6 flex items-center justify-center space-x-2 sm:space-x-4">
                <div class="flex items-center">
                    <div class="flex items-center justify-center w-8 h-8 sm:w-10 sm:h-10 rounded-full bg-green-500 text-white font-bold">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                    </div>
                    <span class="ml-2 text-xs sm:text-sm font-medium text-gray-700">Carrito</span>
                </div>
                <div class="w-12 sm:w-20 h-1 bg-purple-500"></div>
                <div class="flex items-center">
                    <div class="flex items-center justify-center w-8 h-8 sm:w-10 sm:h-10 rounded-full bg-purple-500 text-white font-bold">2</div>
                    <span class="ml-2 text-xs sm:text-sm font-medium text-purple-600">Checkout</span>
                </div>
                <div class="w-12 sm:w-20 h-1 bg-gray-300"></div>
                <div class="flex items-center">
                    <div class="flex items-center justify-center w-8 h-8 sm:w-10 sm:h-10 rounded-full bg-gray-300 text-gray-600 font-bold">3</div>
                    <span class="ml-2 text-xs sm:text-sm font-medium text-gray-500">Confirmación</span>
                </div>
            </div>
        </div>

        @if (session()->has('error'))
            <div class="bg-red-50 border-l-4 border-red-500 text-red-700 px-4 py-3 rounded-lg mb-6 shadow-sm flex items-start">
                <svg class="w-5 h-5 mr-2 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                </svg>
                <span>{{ session('error') }}</span>
            </div>
        @endif

        <form wire:submit.prevent="placeOrder">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 lg:gap-8">

                <!-- Columna principal - Formularios -->
                <div class="lg:col-span-2 space-y-6 order-2 lg:order-1">

                    <!-- Información del Cliente -->
                    <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-6 sm:p-8 hover:shadow-xl transition-shadow duration-300">
                        <div class="flex items-center mb-6">
                            <div class="flex items-center justify-center w-10 h-10 rounded-full bg-purple-100 text-purple-600 mr-3">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                </svg>
                            </div>
                            <h2 class="text-xl sm:text-2xl font-bold text-gray-900">Información del Cliente</h2>
                        </div>

                        <div class="space-y-5">
                            <div>
                                <label for="customer_name" class="block text-sm font-semibold text-gray-700 mb-2">
                                    Nombre completo <span class="text-red-500">*</span>
                                </label>
                                <input wire:model="customer_name" type="text" id="customer_name" required
                                    placeholder="Ej: Juan Pérez"
                                    class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition-all duration-200 hover:border-gray-300">
                                @error('customer_name')
                                    <span class="flex items-center text-red-600 text-xs mt-2">
                                        <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                        </svg>
                                        {{ $message }}
                                    </span>
                                @enderror
                            </div>

                            <div>
                                <label for="customer_phone" class="block text-sm font-semibold text-gray-700 mb-2">
                                    Teléfono / WhatsApp <span class="text-red-500">*</span>
                                </label>
                                <div class="relative">
                                    <span class="absolute left-4 top-3.5 text-gray-500 font-medium">+595</span>
                                    <input wire:model="customer_phone" type="tel" id="customer_phone" required
                                        placeholder="981 123 456"
                                        class="w-full pl-16 pr-4 py-3 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition-all duration-200 hover:border-gray-300">
                                </div>
                                <p class="text-xs text-gray-500 mt-1.5 flex items-center">
                                    <svg class="w-3.5 h-3.5 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                                    </svg>
                                    Recibirás actualizaciones de tu pedido por WhatsApp
                                </p>
                                @error('customer_phone')
                                    <span class="flex items-center text-red-600 text-xs mt-2">
                                        <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                        </svg>
                                        {{ $message }}
                                    </span>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Tipo de Entrega -->
                    <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-6 sm:p-8 hover:shadow-xl transition-shadow duration-300">
                        <div class="flex items-center mb-6">
                            <div class="flex items-center justify-center w-10 h-10 rounded-full bg-green-100 text-green-600 mr-3">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/>
                                </svg>
                            </div>
                            <h2 class="text-xl sm:text-2xl font-bold text-gray-900">Tipo de Entrega</h2>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-6">
                            <button type="button" wire:click="$set('delivery_type', 'delivery')"
                                class="group relative p-6 border-3 rounded-2xl transition-all duration-300 {{ $delivery_type == 'delivery' ? 'border-purple-500 bg-gradient-to-br from-purple-50 to-purple-100 shadow-lg scale-105' : 'border-gray-200 hover:border-purple-300 hover:shadow-md' }}">
                                <div class="text-center">
                                    <span class="text-5xl block mb-3 transform group-hover:scale-110 transition-transform">🚚</span>
                                    <span class="font-bold text-lg block mb-1">Delivery</span>
                                    <span class="text-xs text-gray-600">Envío a domicilio</span>
                                </div>
                                @if($delivery_type == 'delivery')
                                    <div class="absolute -top-2 -right-2 w-7 h-7 bg-purple-500 rounded-full flex items-center justify-center shadow-lg">
                                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path>
                                        </svg>
                                    </div>
                                @endif
                            </button>

                            <button type="button" wire:click="$set('delivery_type', 'pickup')"
                                class="group relative p-6 border-3 rounded-2xl transition-all duration-300 {{ $delivery_type == 'pickup' ? 'border-purple-500 bg-gradient-to-br from-purple-50 to-purple-100 shadow-lg scale-105' : 'border-gray-200 hover:border-purple-300 hover:shadow-md' }}">
                                <div class="text-center">
                                    <span class="text-5xl block mb-3 transform group-hover:scale-110 transition-transform">🏪</span>
                                    <span class="font-bold text-lg block mb-1">Retiro en Tienda</span>
                                    <span class="text-xs text-gray-600">Sin costo adicional</span>
                                </div>
                                @if($delivery_type == 'pickup')
                                    <div class="absolute -top-2 -right-2 w-7 h-7 bg-purple-500 rounded-full flex items-center justify-center shadow-lg">
                                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path>
                                        </svg>
                                    </div>
                                @endif
                            </button>
                        </div>

                        @if($delivery_type == 'delivery')
                            <div class="space-y-5 pt-4 border-t-2 border-dashed border-gray-200 animate-fadeIn">
                                <div>
                                    <label for="customer_address" class="block text-sm font-semibold text-gray-700 mb-2">
                                        Dirección de entrega <span class="text-red-500">*</span>
                                    </label>
                                    <textarea wire:model="customer_address" rows="3" id="customer_address" required
                                        placeholder="Ej: Av. Ejemplo 123, Barrio Centro, Casa color azul, al lado del supermercado"
                                        class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition-all duration-200 hover:border-gray-300 resize-none"></textarea>
                                    <p class="text-xs text-gray-500 mt-1.5">Incluye referencias para encontrar fácilmente tu ubicación</p>
                                    @error('customer_address')
                                        <span class="flex items-center text-red-600 text-xs mt-2">
                                            <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                            </svg>
                                            {{ $message }}
                                        </span>
                                    @enderror
                                </div>

                                <div>
                                    <label for="delivery_zone" class="block text-sm font-semibold text-gray-700 mb-2">
                                        Zona de delivery <span class="text-red-500">*</span>
                                    </label>
                                    <div class="relative">
                                        <select wire:model.live="delivery_zone_id" id="delivery_zone" required
                                            class="w-full px-4 py-3 pr-10 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition-all duration-200 hover:border-gray-300 appearance-none bg-white cursor-pointer">
                                            <option value="">📍 Selecciona tu zona</option>
                                            @foreach($deliveryZones as $zone)
                                                <option value="{{ $zone->id }}">
                                                    {{ $zone->name }} - {{ number_format($zone->delivery_cost, 0, ',', '.') }} Gs
                                                </option>
                                            @endforeach
                                        </select>
                                        <div class="absolute inset-y-0 right-0 flex items-center px-3 pointer-events-none">
                                            <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                            </svg>
                                        </div>
                                    </div>
                                    @error('delivery_zone_id')
                                        <span class="flex items-center text-red-600 text-xs mt-2">
                                            <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                            </svg>
                                            {{ $message }}
                                        </span>
                                    @enderror
                                </div>
                            </div>
                        @else
                            <div class="bg-gradient-to-r from-blue-50 to-indigo-50 border-2 border-blue-200 rounded-2xl p-6 mt-4 animate-fadeIn">
                                <div class="flex items-start">
                                    <div class="flex-shrink-0">
                                        <svg class="w-7 h-7 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                    </div>
                                    <div class="ml-4">
                                        <h3 class="text-base font-bold text-blue-900 mb-2">Retiro en Tienda</h3>
                                        <div class="space-y-2 text-sm text-blue-800">
                                            <p class="flex items-center">
                                                <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"/>
                                                </svg>
                                                <strong>Dirección:</strong>&nbsp;Av. Principal 123, Ciudad del Este
                                            </p>
                                            <p class="flex items-center">
                                                <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"/>
                                                </svg>
                                                <strong>Horario:</strong>&nbsp;Lunes a Sábado 9:00 - 20:00
                                            </p>
                                            <p class="flex items-center text-green-700 font-semibold mt-3">
                                                <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                                </svg>
                                                ¡Ahorra el costo de envío!
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>

                    <!-- Método de Pago -->
                    <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-6 sm:p-8 hover:shadow-xl transition-shadow duration-300">
                        <div class="flex items-center mb-6">
                            <div class="flex items-center justify-center w-10 h-10 rounded-full bg-blue-100 text-blue-600 mr-3">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                                </svg>
                            </div>
                            <div class="flex-1">
                                <h2 class="text-xl sm:text-2xl font-bold text-gray-900">Método de Pago</h2>
                                <p class="text-xs text-gray-500 mt-1">Selecciona cómo deseas pagar <span class="text-red-500">*</span></p>
                            </div>
                        </div>

                        <div class="space-y-3">
                            @foreach($paymentMethods as $method)
                                <label class="group relative flex items-start p-5 border-2 rounded-2xl cursor-pointer transition-all duration-300 {{ $payment_method_id == $method->id ? 'border-purple-500 bg-gradient-to-br from-purple-50 to-purple-100 shadow-lg' : 'border-gray-200 hover:border-purple-300 hover:shadow-md' }}">
                                    <input type="radio" wire:model.live="payment_method_id" value="{{ $method->id }}"
                                        class="mt-1 text-purple-600 focus:ring-purple-500 h-5 w-5 flex-shrink-0 cursor-pointer">

                                    <div class="ml-4 flex-1 min-w-0">
                                        <div class="flex items-center justify-between">
                                            <div class="font-bold text-base text-gray-900">{{ $method->name }}</div>
                                            @if($payment_method_id == $method->id)
                                                <span class="ml-2 inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-purple-500 text-white">
                                                    <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                                    </svg>
                                                    Seleccionado
                                                </span>
                                            @endif
                                        </div>
                                        <div class="text-sm text-gray-600 mt-1">{{ $method->description }}</div>

                                        @if($payment_method_id == $method->id && ($method->instructions || ($method->bank_details ?? false)))
                                            <div class="mt-4 p-4 bg-white rounded-xl border-2 border-purple-200 shadow-sm animate-fadeIn">
                                                @if($method->instructions)
                                                    <div class="flex items-start mb-3">
                                                        <svg class="w-5 h-5 text-purple-600 mr-2 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                                                        </svg>
                                                        <div>
                                                            <p class="font-bold text-sm text-gray-900 mb-1">Instrucciones:</p>
                                                            <p class="text-sm text-gray-700 leading-relaxed">{{ $method->instructions }}</p>
                                                        </div>
                                                    </div>
                                                @endif

                                                @if($method->bank_details)
                                                    <div class="bg-gradient-to-br from-purple-50 to-pink-50 rounded-lg p-4 border border-purple-200">
                                                        <p class="font-bold text-sm text-purple-900 mb-3 flex items-center">
                                                            <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                                                <path d="M4 4a2 2 0 00-2 2v1h16V6a2 2 0 00-2-2H4z"/>
                                                                <path fill-rule="evenodd" d="M18 9H2v5a2 2 0 002 2h12a2 2 0 002-2V9zM4 13a1 1 0 011-1h1a1 1 0 110 2H5a1 1 0 01-1-1zm5-1a1 1 0 100 2h1a1 1 0 100-2H9z" clip-rule="evenodd"/>
                                                            </svg>
                                                            Datos Bancarios:
                                                        </p>
                                                        <div class="space-y-2.5">
                                                            <div class="flex items-center bg-white rounded-lg p-3 shadow-sm">
                                                                <span class="text-xs font-medium text-gray-600 w-20">Banco:</span>
                                                                <span class="text-sm font-bold text-gray-900">{{ $method->bank_details['bank'] ?? 'N/A' }}</span>
                                                            </div>
                                                            <div class="flex items-center bg-white rounded-lg p-3 shadow-sm">
                                                                <span class="text-xs font-medium text-gray-600 w-20">Cuenta:</span>
                                                                <span class="text-sm font-bold text-gray-900 font-mono">{{ $method->bank_details['account_number'] ?? 'N/A' }}</span>
                                                                <button type="button" onclick="navigator.clipboard.writeText('{{ $method->bank_details['account_number'] ?? '' }}')"
                                                                    class="ml-auto text-purple-600 hover:text-purple-700 p-1.5 hover:bg-purple-100 rounded transition-colors">
                                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                                                                    </svg>
                                                                </button>
                                                            </div>
                                                            <div class="flex items-center bg-white rounded-lg p-3 shadow-sm">
                                                                <span class="text-xs font-medium text-gray-600 w-20">Titular:</span>
                                                                <span class="text-sm font-bold text-gray-900">{{ $method->bank_details['account_holder'] ?? 'N/A' }}</span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endif
                                            </div>
                                        @endif
                                    </div>
                                </label>
                            @endforeach
                        </div>
                        @error('payment_method_id')
                            <span class="flex items-center text-red-600 text-xs mt-3">
                                <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                </svg>
                                {{ $message }}
                            </span>
                        @enderror
                    </div>

                    <!-- Notas Adicionales -->
                    <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-6 sm:p-8 hover:shadow-xl transition-shadow duration-300">
                        <div class="flex items-center mb-4">
                            <div class="flex items-center justify-center w-10 h-10 rounded-full bg-amber-100 text-amber-600 mr-3">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                </svg>
                            </div>
                            <div>
                                <h2 class="text-xl sm:text-2xl font-bold text-gray-900">Notas Adicionales</h2>
                                <p class="text-xs text-gray-500 mt-1">Opcional - Información extra sobre tu pedido</p>
                            </div>
                        </div>
                        <textarea wire:model="notes" rows="4"
                            placeholder="Ej: Sin frutos secos por favor, entregar por la tarde después de las 14:00, dejar con el portero, etc."
                            class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition-all duration-200 hover:border-gray-300 resize-none"></textarea>
                    </div>
                </div>

                <!-- Columna lateral - Resumen -->
                <div class="lg:col-span-1 order-1 lg:order-2">
                    <div class="bg-white rounded-2xl shadow-2xl border border-gray-100 p-6 sm:p-8 lg:sticky lg:top-4">
                        <h2 class="text-xl sm:text-2xl font-bold text-gray-900 mb-6 flex items-center">
                            <span class="text-3xl mr-2">📦</span>
                            Resumen del Pedido
                        </h2>

                        <!-- Items del carrito -->
                        <div class="space-y-4 mb-6 pb-6 border-b-2 border-dashed border-gray-200 max-h-64 overflow-y-auto pr-2">
                            @foreach($cartItems as $item)
                                <div class="flex justify-between items-start gap-3 p-3 bg-gray-50 rounded-xl hover:bg-gray-100 transition-colors">
                                    <div class="flex-1 min-w-0">
                                        <div class="text-sm font-bold text-gray-800 leading-tight mb-1.5">
                                            <span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-purple-500 text-white text-xs font-bold mr-1.5">
                                                {{ $item->quantity }}
                                            </span>
                                            {{ $item->product->name }}
                                        </div>
                                        <span class="inline-block bg-purple-100 text-purple-700 text-xs font-bold px-3 py-1 rounded-full">
                                            {{ $item->variant->volume }} ml
                                        </span>
                                    </div>
                                    <span class="text-sm font-bold text-gray-900 whitespace-nowrap">
                                        {{ number_format($item->variant->price * $item->quantity, 0, ',', '.') }} Gs
                                    </span>
                                </div>
                            @endforeach
                        </div>

                        <!-- Cálculos -->
                        <div class="space-y-3 mb-6 pb-6 border-b-2 border-gray-200">
                            <div class="flex justify-between text-gray-700 text-base">
                                <span class="font-medium">Subtotal:</span>
                                <span class="font-bold">{{ number_format($subtotal, 0, ',', '.') }} Gs</span>
                            </div>

                            @if($delivery_type == 'delivery')
                                <div class="flex justify-between text-gray-700 text-base items-center">
                                    <span class="font-medium flex items-center">
                                        <svg class="w-4 h-4 mr-1 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                                            <path d="M8 16.5a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0zM15 16.5a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0z"/>
                                            <path d="M3 4a1 1 0 00-1 1v10a1 1 0 001 1h1.05a2.5 2.5 0 014.9 0H10a1 1 0 001-1V5a1 1 0 00-1-1H3zM14 7a1 1 0 00-1 1v6.05A2.5 2.5 0 0115.95 16H17a1 1 0 001-1v-5a1 1 0 00-.293-.707l-2-2A1 1 0 0015 7h-1z"/>
                                        </svg>
                                        Delivery:
                                    </span>
                                    <span class="font-bold text-green-600">
                                        {{ $deliveryCost > 0 ? number_format($deliveryCost, 0, ',', '.') . ' Gs' : 'Calculando...' }}
                                    </span>
                                </div>
                            @else
                                <div class="flex justify-between text-gray-700 text-base items-center">
                                    <span class="font-medium flex items-center">
                                        <svg class="w-4 h-4 mr-1 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                        </svg>
                                        Delivery:
                                    </span>
                                    <span class="font-bold text-green-600 text-lg">¡GRATIS!</span>
                                </div>
                            @endif
                        </div>

                        <!-- Total -->
                        <div class="bg-gradient-to-r from-purple-500 to-pink-500 rounded-2xl p-6 mb-6 shadow-lg">
                            <div class="flex justify-between items-center text-white">
                                <span class="text-lg font-medium">Total a Pagar:</span>
                                <span class="text-3xl font-black">{{ number_format($total, 0, ',', '.') }}</span>
                            </div>
                            <div class="text-right text-white text-opacity-90 text-sm mt-1">Guaraníes</div>
                        </div>

                        <!-- Botones -->
                        <button type="submit"
                            class="w-full bg-gradient-to-r from-purple-600 to-pink-600 hover:from-purple-700 hover:to-pink-700 text-white font-bold py-4 px-6 rounded-xl transition-all duration-300 text-lg shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 flex items-center justify-center">
                            <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            Confirmar Pedido
                        </button>

                        <a href="{{ route('cart') }}"
                            class="block w-full text-center text-purple-600 hover:text-purple-700 font-semibold mt-4 py-2 hover:bg-purple-50 rounded-lg transition-colors">
                            ← Volver al Carrito
                        </a>

                        <!-- Seguridad -->
                        <div class="mt-6 pt-6 border-t border-gray-200">
                            <div class="flex items-center justify-center text-xs text-gray-500">
                                <svg class="w-4 h-4 mr-1.5 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M2.166 4.999A11.954 11.954 0 0010 1.944 11.954 11.954 0 0017.834 5c.11.65.166 1.32.166 2.001 0 5.225-3.34 9.67-8 11.317C5.34 16.67 2 12.225 2 7c0-.682.057-1.35.166-2.001zm11.541 3.708a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                </svg>
                                Compra 100% segura y protegida
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

@push('styles')
<style>
@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateY(-10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.animate-fadeIn {
    animation: fadeIn 0.3s ease-in-out;
}

/* Scrollbar personalizado */
.overflow-y-auto::-webkit-scrollbar {
    width: 6px;
}

.overflow-y-auto::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 10px;
}

.overflow-y-auto::-webkit-scrollbar-thumb {
    background: #a855f7;
    border-radius: 10px;
}

.overflow-y-auto::-webkit-scrollbar-thumb:hover {
    background: #9333ea;
}
</style>
@endpush
