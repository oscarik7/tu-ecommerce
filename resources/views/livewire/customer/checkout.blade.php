<div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <h1 class="text-3xl font-bold text-gray-900 mb-8">💳 Finalizar Pedido</h1>

        @if (session()->has('error'))
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                {{ session('error') }}
            </div>
        @endif

        <form wire:submit.prevent="placeOrder">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <!-- Formulario -->
                <div class="lg:col-span-2 space-y-6">
                    <!-- Información del Cliente -->
                    <div class="bg-white rounded-lg shadow-md p-6">
                        <h2 class="text-xl font-bold text-gray-900 mb-4">📋 Información del Cliente</h2>
                        
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Nombre completo</label>
                                <input wire:model="customer_name" type="text" required
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                                @error('customer_name') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Teléfono</label>
                                <input wire:model="customer_phone" type="text" required
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                                @error('customer_phone') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Tipo de Entrega -->
                    <div class="bg-white rounded-lg shadow-md p-6">
                        <h2 class="text-xl font-bold text-gray-900 mb-4">🚚 Tipo de Entrega</h2>
                        
                        <div class="grid grid-cols-2 gap-4 mb-4">
                            <button type="button" wire:click="$set('delivery_type', 'delivery')"
                                class="p-4 border-2 rounded-lg transition {{ $delivery_type == 'delivery' ? 'border-purple-600 bg-purple-50' : 'border-gray-300' }}">
                                <div class="text-center">
                                    <span class="text-3xl block mb-2">🚚</span>
                                    <span class="font-bold">Delivery</span>
                                </div>
                            </button>

                            <button type="button" wire:click="$set('delivery_type', 'pickup')"
                                class="p-4 border-2 rounded-lg transition {{ $delivery_type == 'pickup' ? 'border-purple-600 bg-purple-50' : 'border-gray-300' }}">
                                <div class="text-center">
                                    <span class="text-3xl block mb-2">🏪</span>
                                    <span class="font-bold">Retiro en Tienda</span>
                                </div>
                            </button>
                        </div>

                        @if($delivery_type == 'delivery')
                            <div class="space-y-4 mt-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Dirección</label>
                                    <textarea wire:model="customer_address" rows="3" required
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent"></textarea>
                                    @error('customer_address') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Zona de Delivery</label>
                                    <select wire:model.live="delivery_zone_id" required
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                                        <option value="">Selecciona una zona</option>
                                        @foreach($deliveryZones as $zone)
                                            <option value="{{ $zone->id }}">
                                                {{ $zone->name }} - {{ number_format($zone->delivery_cost, 0, ',', '.') }} Gs
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('delivery_zone_id') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                </div>

                                <!-- Google Maps -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">
                                        📍 Ubicación en el Mapa (Opcional)
                                    </label>
                                    <p class="text-xs text-gray-500 mb-2">Marca tu ubicación exacta para facilitar la entrega</p>
                                    
                                    <div id="map" class="w-full h-64 rounded-lg border border-gray-300"></div>
                                    
                                    <div class="mt-2 text-xs text-gray-600">
                                        <span class="font-semibold">Coordenadas:</span> 
                                        <span x-text="'Lat: ' + (@this.latitude || 'No seleccionado') + ', Lng: ' + (@this.longitude || 'No seleccionado')"></span>
                                    </div>
                                </div>
                            </div>
                        @else
                            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mt-4">
                                <p class="text-sm text-blue-800">
                                    <strong>📍 Dirección de la tienda:</strong><br>
                                    Av. Principal 123, Ciudad del Este<br>
                                    Horario: Lunes a Sábado 9:00 - 20:00
                                </p>
                            </div>
                        @endif
                    </div>

                    <!-- Método de Pago -->
                    <div class="bg-white rounded-lg shadow-md p-6">
                        <h2 class="text-xl font-bold text-gray-900 mb-4">💳 Método de Pago</h2>
                        
                        <div class="space-y-3">
                            @foreach($paymentMethods as $method)
                                <label class="flex items-start p-4 border-2 rounded-lg cursor-pointer transition {{ $payment_method_id == $method->id ? 'border-purple-600 bg-purple-50' : 'border-gray-300' }}">
                                    <input type="radio" wire:model="payment_method_id" value="{{ $method->id }}" class="mt-1">
                                    <div class="ml-3 flex-1">
                                        <div class="font-bold">{{ $method->name }}</div>
                                        <div class="text-sm text-gray-600">{{ $method->description }}</div>
                                        
                                        @if($method->instructions)
                                            <div class="text-xs text-gray-500 mt-2">{{ $method->instructions }}</div>
                                        @endif

                                        @if($method->bank_details)
                                            <div class="mt-2 text-xs bg-gray-50 p-2 rounded">
                                                <strong>Datos bancarios:</strong><br>
                                                Banco: {{ $method->bank_details['bank'] ?? '' }}<br>
                                                Cuenta: {{ $method->bank_details['account_number'] ?? '' }}<br>
                                                Titular: {{ $method->bank_details['account_holder'] ?? '' }}
                                            </div>
                                        @endif
                                    </div>
                                </label>
                            @endforeach
                        </div>
                        @error('payment_method_id') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>

                    <!-- Notas Adicionales -->
                    <div class="bg-white rounded-lg shadow-md p-6">
                        <h2 class="text-xl font-bold text-gray-900 mb-4">📝 Notas Adicionales (Opcional)</h2>
                        <textarea wire:model="notes" rows="3" 
                            placeholder="Ej: Sin frutos secos, entrega por la tarde, etc."
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent"></textarea>
                    </div>
                </div>

                <!-- Resumen del Pedido -->
                <div class="lg:col-span-1">
                    <div class="bg-white rounded-lg shadow-md p-6 sticky top-4">
                        <h2 class="text-xl font-bold text-gray-900 mb-4">📦 Resumen del Pedido</h2>
                        
                        <div class="space-y-3 mb-4">
                            @foreach($cartItems as $item)
                                <div class="flex justify-between text-sm">
                                    <span>{{ $item->quantity }}x {{ $item->product->name }}</span>
                                    <span class="font-bold">{{ number_format($item->product->price * $item->quantity, 0, ',', '.') }} Gs</span>
                                </div>
                            @endforeach
                        </div>

                        <div class="border-t pt-4 space-y-2 mb-4">
                            <div class="flex justify-between text-gray-700">
                                <span>Subtotal:</span>
                                <span class="font-bold">{{ number_format($subtotal, 0, ',', '.') }} Gs</span>
                            </div>
                            
                            @if($delivery_type == 'delivery')
                                <div class="flex justify-between text-gray-700">
                                    <span>Costo de Delivery:</span>
                                    <span class="font-bold">{{ number_format($deliveryCost, 0, ',', '.') }} Gs</span>
                                </div>
                            @endif
                        </div>

                        <div class="border-t pt-4 mb-6">
                            <div class="flex justify-between text-xl font-bold text-gray-900">
                                <span>Total:</span>
                                <span class="text-purple-600">{{ number_format($total, 0, ',', '.') }} Gs</span>
                            </div>
                        </div>

                        <button type="submit" 
                            class="w-full bg-purple-600 hover:bg-purple-700 text-white font-bold py-3 px-4 rounded-lg transition">
                            Confirmar Pedido
                        </button>

                        <a href="{{ route('cart') }}" 
                            class="block w-full text-center text-purple-600 hover:text-purple-700 font-medium mt-4">
                            Volver al Carrito
                        </a>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script src="https://maps.googleapis.com/maps/api/js?key=YOUR_GOOGLE_MAPS_API_KEY&callback=initMap" async defer></script>
<script>
    let map;
    let marker;

    function initMap() {
        // Centro en Ciudad del Este
        const ciudadDelEste = { lat: -25.5095, lng: -54.6112 };
        
        map = new google.maps.Map(document.getElementById("map"), {
            zoom: 13,
            center: ciudadDelEste,
        });

        // Evento de click en el mapa
        map.addListener("click", (e) => {
            placeMarker(e.latLng);
        });

        // Intentar obtener ubicación actual del usuario
        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(
                (position) => {
                    const pos = {
                        lat: position.coords.latitude,
                        lng: position.coords.longitude,
                    };
                    map.setCenter(pos);
                    placeMarker(new google.maps.LatLng(pos.lat, pos.lng));
                },
                () => {
                    // Si falla, mantener el centro por defecto
                }
            );
        }
    }

    function placeMarker(location) {
        if (marker) {
            marker.setPosition(location);
        } else {
            marker = new google.maps.Marker({
                position: location,
                map: map,
                draggable: true,
            });

            // Actualizar coordenadas cuando se arrastra el marcador
            marker.addListener("dragend", () => {
                updateCoordinates(marker.getPosition());
            });
        }
        updateCoordinates(location);
    }

    function updateCoordinates(location) {
        @this.set('latitude', location.lat());
        @this.set('longitude', location.lng());
    }
</script>
@endpush