<div class="min-h-screen bg-gray-50 py-6 sm:py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <h1 class="text-2xl sm:text-3xl font-bold text-gray-900 mb-6 sm:mb-8">💳 Finalizar Pedido</h1>

        @if (session()->has('error'))
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                {{ session('error') }}
            </div>
        @endif

        <form wire:submit.prevent="placeOrder">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                
                <div class="lg:col-span-2 space-y-6 order-2 lg:order-1">
                    
                    <div class="bg-white rounded-lg shadow-md p-4 sm:p-6">
                        <h2 class="text-xl font-bold text-gray-900 mb-4">📋 Información del Cliente</h2>
                        
                        <div class="space-y-4">
                            <div>
                                <label for="customer_name" class="block text-sm font-medium text-gray-700 mb-1">Nombre completo</label>
                                <input wire:model="customer_name" type="text" id="customer_name" required
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                                @error('customer_name') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <label for="customer_phone" class="block text-sm font-medium text-gray-700 mb-1">Teléfono</label>
                                <input wire:model="customer_phone" type="text" id="customer_phone" required
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                                @error('customer_phone') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>

                    <div class="bg-white rounded-lg shadow-md p-4 sm:p-6">
                        <h2 class="text-xl font-bold text-gray-900 mb-4">🚚 Tipo de Entrega</h2>
                        
                        <div class="grid grid-cols-2 gap-4 mb-4">
                            <button type="button" wire:click="$set('delivery_type', 'delivery')"
                                class="p-4 border-2 rounded-lg transition text-sm sm:text-base {{ $delivery_type == 'delivery' ? 'border-purple-600 bg-purple-50 shadow-inner' : 'border-gray-300 hover:border-gray-400' }}">
                                <div class="text-center">
                                    <span class="text-2xl sm:text-3xl block mb-1 sm:mb-2">🚚</span>
                                    <span class="font-bold">Delivery</span>
                                </div>
                            </button>

                            <button type="button" wire:click="$set('delivery_type', 'pickup')"
                                class="p-4 border-2 rounded-lg transition text-sm sm:text-base {{ $delivery_type == 'pickup' ? 'border-purple-600 bg-purple-50 shadow-inner' : 'border-gray-300 hover:border-gray-400' }}">
                                <div class="text-center">
                                    <span class="text-2xl sm:text-3xl block mb-1 sm:mb-2">🏪</span>
                                    <span class="font-bold">Retiro en Tienda</span>
                                </div>
                            </button>
                        </div>

                        @if($delivery_type == 'delivery')
                            <div class="space-y-4 mt-4">
                                <div>
                                    <label for="customer_address" class="block text-sm font-medium text-gray-700 mb-1">Dirección exacta</label>
                                    <textarea wire:model="customer_address" rows="2" id="customer_address" required
                                        placeholder="Ej: Calle 10, Casa #25, al lado de la farmacia"
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent"></textarea>
                                    @error('customer_address') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                                </div>

                                <div>
                                    <label for="delivery_zone" class="block text-sm font-medium text-gray-700 mb-1">Zona de Delivery</label>
                                    <select wire:model.live="delivery_zone_id" id="delivery_zone" required
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                                        <option value="">Selecciona una zona</option>
                                        @foreach($deliveryZones as $zone)
                                            <option value="{{ $zone->id }}">
                                                {{ $zone->name }} - {{ number_format($zone->delivery_cost, 0, ',', '.') }} Gs
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('delivery_zone_id') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">
                                        📍 Ubicación en el Mapa (Opcional, pero recomendado)
                                    </label>
                                    <p class="text-xs text-gray-500 mb-2">Haz click en el mapa para marcar tu punto de entrega.</p>
                                    
                                    <div id="map" class="w-full h-48 sm:h-64 rounded-lg border border-gray-300"></div>
                                    
                                    <div class="mt-2 text-xs text-gray-600 p-2 bg-gray-100 rounded">
                                        <span class="font-semibold">Coordenadas guardadas:</span> 
                                        <span x-data="{ lat: @entangle('latitude'), lng: @entangle('longitude') }">
                                            Lat: <span x-text="lat || 'No seleccionado'"></span>, Lng: <span x-text="lng || 'No seleccionado'"></span>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        @else
                            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mt-4">
                                <p class="text-sm text-blue-800">
                                    <strong>✅ Has seleccionado Retiro en Tienda.</strong><br>
                                    <strong>📍 Dirección de la tienda:</strong> Av. Principal 123, Ciudad del Este.<br>
                                    <strong>🕒 Horario:</strong> Lunes a Sábado 9:00 - 20:00
                                </p>
                            </div>
                        @endif
                    </div>

                    <div class="bg-white rounded-lg shadow-md p-4 sm:p-6">
                        <h2 class="text-xl font-bold text-gray-900 mb-4">💳 Método de Pago</h2>
                        
                        <div class="space-y-3">
                            @foreach($paymentMethods as $method)
                                <label class="flex items-start p-3 border-2 rounded-lg cursor-pointer transition {{ $payment_method_id == $method->id ? 'border-purple-600 bg-purple-50 shadow-inner' : 'border-gray-300 hover:border-gray-400' }}">
                                    <input type="radio" wire:model="payment_method_id" value="{{ $method->id }}" 
                                        class="mt-1 text-purple-600 focus:ring-purple-500 h-4 w-4 flex-shrink-0">
                                    
                                    <div class="ml-3 flex-1 min-w-0">
                                        <div class="font-bold text-base">{{ $method->name }}</div>
                                        <div class="text-sm text-gray-600">{{ $method->description }}</div>
                                        
                                        @if($payment_method_id == $method->id && ($method->instructions || ($method->bank_details ?? false)))
                                            <div class="mt-2 text-xs bg-white p-3 rounded border border-purple-200">
                                                @if($method->instructions)
                                                    <p class="font-semibold mb-1">Instrucciones:</p>
                                                    <p class="text-gray-700">{{ $method->instructions }}</p>
                                                @endif
                                                
                                                @if($method->bank_details)
                                                    <p class="font-semibold mt-2 mb-1">Datos Bancarios:</p>
                                                    <ul class="list-disc pl-4 text-gray-700 space-y-0">
                                                        <li>Banco: **{{ $method->bank_details['bank'] ?? 'N/A' }}**</li>
                                                        <li>Cuenta: **{{ $method->bank_details['account_number'] ?? 'N/A' }}**</li>
                                                        <li>Titular: **{{ $method->bank_details['account_holder'] ?? 'N/A' }}**</li>
                                                    </ul>
                                                @endif
                                            </div>
                                        @endif
                                    </div>
                                </label>
                            @endforeach
                        </div>
                        @error('payment_method_id') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <div class="bg-white rounded-lg shadow-md p-4 sm:p-6">
                        <h2 class="text-xl font-bold text-gray-900 mb-4">📝 Notas Adicionales (Opcional)</h2>
                        <textarea wire:model="notes" rows="3" 
                            placeholder="Ej: Sin frutos secos, entrega por la tarde, dejar en portería, etc."
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent"></textarea>
                    </div>
                </div>

                <div class="lg:col-span-1 order-1 lg:order-2">
                    <div class="bg-white rounded-lg shadow-xl p-4 sm:p-6 lg:sticky lg:top-4">
                        <h2 class="text-xl font-bold text-gray-900 mb-4">📦 Resumen del Pedido</h2>
                        
                        <div class="space-y-3 mb-4 border-b pb-4">
                            @foreach($cartItems as $item)
                                <div class="flex justify-between items-start gap-2">
                                    <div class="flex-1 min-w-0">
                                        <div class="text-sm font-medium text-gray-800">
                                            {{ $item->quantity }}x {{ $item->product->name }}
                                        </div>
                                        <span class="inline-block bg-purple-100 text-purple-700 text-xs font-semibold px-2 py-0.5 rounded-full mt-1">
                                            {{ $item->variant->volume }} ml
                                        </span>
                                    </div>
                                    <span class="text-sm font-bold text-gray-900 whitespace-nowrap">
                                        {{ number_format($item->variant->price * $item->quantity, 0, ',', '.') }} Gs
                                    </span>
                                </div>
                            @endforeach
                        </div>

                        <div class="space-y-2 mb-4">
                            <div class="flex justify-between text-gray-700 text-base">
                                <span>Subtotal:</span>
                                <span class="font-bold">{{ number_format($subtotal, 0, ',', '.') }} Gs</span>
                            </div>
                            
                            @if($delivery_type == 'delivery')
                                <div class="flex justify-between text-gray-700 text-base">
                                    <span>Costo de Delivery:</span>
                                    <span class="font-bold text-green-600">{{ number_format($deliveryCost, 0, ',', '.') }} Gs</span>
                                </div>
                            @else
                                <div class="flex justify-between text-gray-700 text-base">
                                    <span>Costo de Delivery:</span>
                                    <span class="font-bold text-green-600">GRATIS</span>
                                </div>
                            @endif
                        </div>

                        <div class="border-t pt-4 mb-6">
                            <div class="flex justify-between text-2xl font-bold text-gray-900">
                                <span>Total:</span>
                                <span class="text-purple-600">{{ number_format($total, 0, ',', '.') }} Gs</span>
                            </div>
                        </div>

                        <button type="submit" 
                            class="w-full bg-purple-600 hover:bg-purple-700 text-white font-bold py-3 px-4 rounded-lg transition text-base sm:text-lg">
                            Confirmar Pedido
                        </button>

                        <a href="{{ route('cart') }}" 
                            class="block w-full text-center text-purple-600 hover:text-purple-700 font-medium mt-4 text-sm">
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
                    // Inicializar marcador si ya hay coordenadas en Livewire (opcional)
                    const initialLat = @json($latitude);
                    const initialLng = @json($longitude);
                    if (initialLat && initialLng) {
                         placeMarker(new google.maps.LatLng(initialLat, initialLng));
                    } else {
                         placeMarker(new google.maps.LatLng(pos.lat, pos.lng));
                    }
                },
                () => {
                    // Si falla, mantener el centro por defecto
                    // Inicializar marcador si ya hay coordenadas en Livewire
                    const initialLat = @json($latitude);
                    const initialLng = @json($longitude);
                    if (initialLat && initialLng) {
                         placeMarker(new google.maps.LatLng(initialLat, initialLng));
                    }
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
        // Usamos Alpine/Livewire para actualizar las propiedades
        @this.set('latitude', location.lat());
        @this.set('longitude', location.lng());
    }

    // Inicializar el mapa después de que Livewire haya terminado de cargar si es necesario.
    // window.initMap = initMap; // Se asume que el script de Google Maps llama a initMap
</script>
@endpush