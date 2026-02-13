<div class="min-h-screen bg-gray-50">

    {{-- ══ HEADER ══ --}}
    <div class="bg-gradient-to-r from-purple-600 to-pink-500 pt-8 pb-16">
        <div class="max-w-4xl mx-auto px-4 sm:px-6">
            <div class="flex items-center gap-4">
                <div class="w-14 h-14 rounded-full bg-white/20 flex items-center justify-center text-2xl font-black text-white flex-shrink-0">
                    {{ strtoupper(substr($user->name, 0, 1)) }}
                </div>
                <div>
                    <h1 class="text-2xl font-black text-white">{{ $user->name }}</h1>
                    <p class="text-purple-200 text-sm">{{ $user->email }}</p>
                </div>
            </div>

            {{-- Tabs --}}
            <div class="flex gap-1 mt-6">
                <button wire:click="setTab('orders')"
                    class="px-5 py-2 rounded-t-xl text-sm font-bold transition-all
                        {{ $activeTab === 'orders' ? 'bg-white text-purple-700' : 'text-white/70 hover:text-white' }}">
                    📦 Mis Pedidos
                    @if($orders->count() > 0)
                        <span class="ml-1.5 bg-purple-100 text-purple-700 px-1.5 py-0.5 rounded-full text-xs">
                            {{ $orders->count() }}
                        </span>
                    @endif
                </button>
                <button wire:click="setTab('account')"
                    class="px-5 py-2 rounded-t-xl text-sm font-bold transition-all
                        {{ $activeTab === 'account' ? 'bg-white text-purple-700' : 'text-white/70 hover:text-white' }}">
                    👤 Mi Cuenta
                </button>
            </div>
        </div>
    </div>

    <div class="max-w-4xl mx-auto px-4 sm:px-6 -mt-8">

        {{-- ══ TAB: PEDIDOS ══ --}}
        @if($activeTab === 'orders')

            @if(session()->has('order_created'))
                <div class="bg-green-500 text-white px-5 py-4 rounded-2xl mb-5 shadow-lg flex items-center gap-3">
                    <span class="text-2xl">🎉</span>
                    <div>
                        <p class="font-bold">¡Pedido realizado con éxito!</p>
                        <p class="text-sm text-green-100">Tu pedido fue registrado. Pronto recibirás confirmación.</p>
                    </div>
                </div>
            @endif

            @if($orders->count() > 0)
                <div class="space-y-4">
                    @foreach($orders as $order)
                        @php
                            $statusConfig = [
                                'pending'    => ['label' => '⏳ Pendiente',   'bg' => 'bg-amber-100',  'text' => 'text-amber-800'],
                                'confirmed'  => ['label' => '✅ Confirmado',  'bg' => 'bg-blue-100',   'text' => 'text-blue-800'],
                                'preparing'  => ['label' => '👨‍🍳 Preparando', 'bg' => 'bg-purple-100', 'text' => 'text-purple-800'],
                                'ready'      => ['label' => '✨ Listo',       'bg' => 'bg-indigo-100', 'text' => 'text-indigo-800'],
                                'delivering' => ['label' => '🚚 En camino',   'bg' => 'bg-orange-100', 'text' => 'text-orange-800'],
                                'delivered'  => ['label' => '🎉 Entregado',   'bg' => 'bg-green-100',  'text' => 'text-green-800'],
                                'cancelled'  => ['label' => '❌ Cancelado',   'bg' => 'bg-red-100',    'text' => 'text-red-800'],
                            ];
                            $st = $statusConfig[$order->status] ?? ['label' => $order->status, 'bg' => 'bg-gray-100', 'text' => 'text-gray-700'];
                        @endphp

                        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                            {{-- Cabecera --}}
                            <div class="px-5 py-4 flex items-center justify-between gap-3">
                                <div>
                                    <div class="font-black text-gray-900 text-base">#{{ $order->order_number }}</div>
                                    <div class="text-xs text-gray-400 mt-0.5">
                                        {{ $order->created_at->format('d/m/Y H:i') }}
                                        · {{ $order->delivery_type === 'delivery' ? '🚚 Delivery' : '🏪 Retiro' }}
                                    </div>
                                </div>
                                <div class="flex items-center gap-2 flex-shrink-0">
                                    <span class="text-xs font-bold px-2.5 py-1 rounded-full {{ $st['bg'] }} {{ $st['text'] }}">
                                        {{ $st['label'] }}
                                    </span>
                                </div>
                            </div>

                            {{-- Items (preview) --}}
                            <div class="px-5 pb-3 border-t border-gray-50 pt-3 space-y-1.5">
                                @foreach($order->items->take(3) as $item)
                                    <div class="flex items-start justify-between text-sm gap-2">
                                        <span class="text-gray-700">
                                            {{ $item->quantity }}× {{ $item->product_name }}
                                            @if($item->volume) <span class="text-gray-400 text-xs">{{ $item->volume }}ml</span> @endif
                                        </span>
                                        <span class="font-semibold text-gray-900 flex-shrink-0">
                                            {{ number_format($item->subtotal, 0, ',', '.') }} Gs
                                        </span>
                                    </div>
                                    {{-- Complementos preview --}}
                                    @if($item->customizations && $item->customizations->count() > 0)
                                        <div class="pl-4 space-y-0.5">
                                            @foreach($item->customizations as $c)
                                                <div class="text-xs text-purple-500">
                                                    + {{ $c->option_name }}
                                                    @if($c->price > 0)
                                                        <span class="text-orange-400">+{{ number_format($c->price, 0, ',', '.') }} Gs</span>
                                                    @endif
                                                </div>
                                            @endforeach
                                        </div>
                                    @endif
                                @endforeach
                                @if($order->items->count() > 3)
                                    <div class="text-xs text-gray-400">+{{ $order->items->count() - 3 }} producto(s) más</div>
                                @endif
                            </div>

                            {{-- Footer --}}
                            <div class="px-5 py-3 bg-gray-50 border-t flex items-center justify-between gap-3">
                                <div class="text-base font-black text-purple-600">
                                    {{ number_format($order->total, 0, ',', '.') }} Gs
                                </div>
                                <div class="flex gap-2">
                                    <button wire:click="showOrder({{ $order->id }})"
                                        class="text-xs font-bold text-purple-600 hover:text-purple-700 px-3 py-1.5 rounded-lg border border-purple-200 hover:bg-purple-50 transition-colors">
                                        Ver detalles
                                    </button>
                                    <button wire:click="sendToWhatsApp({{ $order->id }})"
                                        class="text-xs font-bold text-green-700 hover:text-green-800 px-3 py-1.5 rounded-lg border border-green-200 hover:bg-green-50 transition-colors flex items-center gap-1">
                                        <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 24 24">
                                            <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
                                        </svg>
                                        WhatsApp
                                    </button>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

            @else
                <div class="bg-white rounded-2xl shadow-sm border p-12 text-center mt-4">
                    <div class="text-6xl mb-4">📦</div>
                    <h2 class="text-xl font-bold text-gray-900 mb-2">No tenés pedidos aún</h2>
                    <p class="text-gray-500 mb-6 text-sm">¡Hacé tu primer pedido de açaí!</p>
                    <a href="{{ route('home') }}"
                        class="inline-block bg-gradient-to-r from-purple-600 to-pink-500 hover:from-purple-700 hover:to-pink-600 text-white font-bold py-3 px-8 rounded-2xl transition-all">
                        Ver Productos
                    </a>
                </div>
            @endif

        @endif

        {{-- ══ TAB: MI CUENTA ══ --}}
        @if($activeTab === 'account')
            <div class="space-y-4 pb-8">

                @if($profileSuccess)
                    <div class="bg-green-500 text-white px-5 py-3.5 rounded-2xl flex items-center gap-2 shadow-sm">
                        <span>✓</span> <span class="font-semibold text-sm">{{ $profileSuccess }}</span>
                    </div>
                @endif

                {{-- Datos personales --}}
                <div class="bg-white rounded-2xl shadow-sm border overflow-hidden">
                    <div class="px-5 py-4 border-b flex items-center justify-between">
                        <h2 class="font-black text-gray-900">👤 Datos Personales</h2>
                        @if(!$editingProfile)
                            <button wire:click="startEditProfile"
                                class="text-xs font-bold text-purple-600 hover:text-purple-700 px-3 py-1.5 rounded-lg border border-purple-200 hover:bg-purple-50 transition-colors">
                                ✏️ Editar
                            </button>
                        @endif
                    </div>

                    @if(!$editingProfile)
                        {{-- Modo lectura --}}
                        <div class="p-5 space-y-3">
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                <div class="bg-gray-50 rounded-xl p-3">
                                    <p class="text-xs text-gray-500 mb-0.5">Nombre</p>
                                    <p class="font-semibold text-gray-900 text-sm">{{ $user->name }}</p>
                                </div>
                                <div class="bg-gray-50 rounded-xl p-3">
                                    <p class="text-xs text-gray-500 mb-0.5">Email</p>
                                    <p class="font-semibold text-gray-900 text-sm">{{ $user->email }}</p>
                                </div>
                                <div class="bg-gray-50 rounded-xl p-3">
                                    <p class="text-xs text-gray-500 mb-0.5">Teléfono</p>
                                    <p class="font-semibold text-gray-900 text-sm">{{ $user->phone ?? '—' }}</p>
                                </div>
                                <div class="bg-gray-50 rounded-xl p-3">
                                    <p class="text-xs text-gray-500 mb-0.5">Dirección</p>
                                    <p class="font-semibold text-gray-900 text-sm">{{ $user->address ?? '—' }}</p>
                                </div>
                            </div>
                        </div>

                    @else
                        {{-- Modo edición --}}
                        <div class="p-5 space-y-4">
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-xs font-semibold text-gray-600 mb-1.5">Nombre <span class="text-red-500">*</span></label>
                                    <input wire:model="editName" type="text"
                                        class="w-full px-4 py-2.5 text-sm border-2 rounded-xl focus:outline-none transition-all
                                            @error('editName') border-red-400 bg-red-50 @else border-gray-200 focus:border-purple-500 @enderror">
                                    @error('editName') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-gray-600 mb-1.5">Teléfono</label>
                                    <input wire:model="editPhone" type="text" placeholder="0981 123 456"
                                        class="w-full px-4 py-2.5 text-sm border-2 border-gray-200 rounded-xl focus:border-purple-500 focus:outline-none transition-all">
                                </div>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-600 mb-1.5">Dirección</label>
                                <input wire:model="editAddress" type="text" placeholder="Av. San Blas 123"
                                    class="w-full px-4 py-2.5 text-sm border-2 border-gray-200 rounded-xl focus:border-purple-500 focus:outline-none transition-all">
                            </div>

                            {{-- Facturación --}}
                            <div class="border-t pt-4">
                                <p class="text-xs font-bold text-gray-500 uppercase tracking-wide mb-3">🧾 Datos de Facturación</p>
                                <div class="grid grid-cols-2 gap-2 mb-3">
                                    <button type="button" wire:click="$set('editDocType', 'ci')"
                                        class="py-2.5 rounded-xl border-2 text-sm font-semibold transition-all
                                            {{ $editDocType === 'ci' ? 'border-purple-500 bg-purple-50 text-purple-700' : 'border-gray-200 text-gray-600 hover:border-gray-300' }}">
                                        🪪 Cédula (CI)
                                    </button>
                                    <button type="button" wire:click="$set('editDocType', 'ruc')"
                                        class="py-2.5 rounded-xl border-2 text-sm font-semibold transition-all
                                            {{ $editDocType === 'ruc' ? 'border-purple-500 bg-purple-50 text-purple-700' : 'border-gray-200 text-gray-600 hover:border-gray-300' }}">
                                        🏢 Empresa (RUC)
                                    </button>
                                </div>
                                <div class="mb-3">
                                    <label class="block text-xs font-semibold text-gray-600 mb-1.5">
                                        {{ $editDocType === 'ruc' ? 'Número de RUC' : 'Número de CI' }}
                                    </label>
                                    <input wire:model="editDoc" type="text"
                                        placeholder="{{ $editDocType === 'ruc' ? 'Ej: 80012345-6' : 'Ej: 4567890' }}"
                                        class="w-full px-4 py-2.5 text-sm border-2 rounded-xl focus:outline-none transition-all
                                            @error('editDoc') border-red-400 bg-red-50 @else border-gray-200 focus:border-purple-500 @enderror">
                                    @error('editDoc') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                                </div>
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

                            <div class="flex gap-3 pt-2">
                                <button wire:click="cancelEditProfile"
                                    class="flex-1 py-3 rounded-xl bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold text-sm transition-colors">
                                    Cancelar
                                </button>
                                <button wire:click="saveProfile"
                                    wire:loading.attr="disabled"
                                    class="flex-1 py-3 rounded-xl bg-gradient-to-r from-purple-600 to-pink-500 hover:from-purple-700 hover:to-pink-600 text-white font-bold text-sm transition-all disabled:opacity-60">
                                    <span wire:loading.remove wire:target="saveProfile">💾 Guardar</span>
                                    <span wire:loading wire:target="saveProfile">Guardando...</span>
                                </button>
                            </div>
                        </div>
                    @endif
                </div>

                {{-- Facturación (solo lectura si tiene doc y no está editando) --}}
                @if(!$editingProfile)
                    <div class="bg-white rounded-2xl shadow-sm border overflow-hidden">
                        <div class="px-5 py-4 border-b">
                            <h2 class="font-black text-gray-900">🧾 Facturación</h2>
                        </div>
                        <div class="p-5">
                            @if($user->document)
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                    <div class="bg-gray-50 rounded-xl p-3">
                                        <p class="text-xs text-gray-500 mb-0.5">Tipo de documento</p>
                                        <p class="font-semibold text-gray-900 text-sm">
                                            {{ $user->document_type === 'ruc' ? '🏢 RUC' : '🪪 Cédula de Identidad' }}
                                        </p>
                                    </div>
                                    <div class="bg-gray-50 rounded-xl p-3">
                                        <p class="text-xs text-gray-500 mb-0.5">Número</p>
                                        <p class="font-semibold text-gray-900 text-sm font-mono">{{ $user->document }}</p>
                                    </div>
                                    @if($user->document_type === 'ruc' && $user->company_name)
                                        <div class="sm:col-span-2 bg-gray-50 rounded-xl p-3">
                                            <p class="text-xs text-gray-500 mb-0.5">Razón Social</p>
                                            <p class="font-semibold text-gray-900 text-sm">{{ $user->company_name }}</p>
                                        </div>
                                    @endif
                                </div>
                                <p class="text-xs text-gray-400 mt-3">
                                    Estos datos se usan automáticamente al pedir factura en tu próximo pedido.
                                    <button wire:click="startEditProfile" class="text-purple-600 hover:underline">Cambiarlos</button>
                                </p>
                            @else
                                <div class="text-center py-4">
                                    <p class="text-sm text-gray-500 mb-3">No tenés datos de facturación guardados.</p>
                                    <button wire:click="startEditProfile"
                                        class="text-sm font-bold text-purple-600 hover:text-purple-700 px-4 py-2 rounded-xl border border-purple-200 hover:bg-purple-50 transition-colors">
                                        + Agregar CI / RUC
                                    </button>
                                </div>
                            @endif
                        </div>
                    </div>
                @endif

                {{-- Stats rápidas --}}
                <div class="grid grid-cols-3 gap-3">
                    <div class="bg-white rounded-2xl shadow-sm border p-4 text-center">
                        <div class="text-2xl font-black text-purple-600">{{ $orders->count() }}</div>
                        <div class="text-xs text-gray-500 mt-0.5">Pedidos</div>
                    </div>
                    <div class="bg-white rounded-2xl shadow-sm border p-4 text-center">
                        <div class="text-2xl font-black text-green-600">
                            {{ $orders->where('status', 'delivered')->count() }}
                        </div>
                        <div class="text-xs text-gray-500 mt-0.5">Entregados</div>
                    </div>
                    <div class="bg-white rounded-2xl shadow-sm border p-4 text-center">
                        <div class="text-xl font-black text-gray-800">
                            {{ number_format($orders->where('status', '!=', 'cancelled')->sum('total') / 1000, 0, ',', '.') }}k
                        </div>
                        <div class="text-xs text-gray-500 mt-0.5">Gs gastados</div>
                    </div>
                </div>

            </div>
        @endif

    </div>

    {{-- ══ MODAL DETALLE PEDIDO ══ --}}
    @if($selectedOrder)
        <div class="fixed inset-0 bg-black/70 backdrop-blur-sm flex items-center justify-center p-4 z-50"
            wire:click="closeModal">
            <div class="bg-white rounded-3xl w-full max-w-lg max-h-[90vh] overflow-y-auto shadow-2xl"
                wire:click.stop>
                <div class="p-6">

                    {{-- Header --}}
                    <div class="flex justify-between items-start mb-5">
                        <div>
                            <h2 class="text-xl font-black text-gray-900">#{{ $selectedOrder->order_number }}</h2>
                            <p class="text-sm text-gray-400">{{ $selectedOrder->created_at->format('d/m/Y H:i') }}</p>
                        </div>
                        <button wire:click="closeModal"
                            class="w-8 h-8 rounded-full bg-gray-100 hover:bg-gray-200 flex items-center justify-center">
                            <svg class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>

                    {{-- Productos --}}
                    <div class="bg-gray-50 rounded-2xl p-4 mb-4">
                        <h3 class="font-bold text-gray-700 text-sm mb-3">📦 Productos</h3>
                        <div class="space-y-3">
                            @foreach($selectedOrder->items as $item)
                                <div>
                                    <div class="flex justify-between text-sm">
                                        <span class="font-semibold text-gray-900">
                                            {{ $item->quantity }}× {{ $item->product_name }}
                                            @if($item->volume) <span class="text-gray-400 font-normal">{{ $item->volume }}ml</span> @endif
                                        </span>
                                        <span class="font-black text-purple-600">
                                            {{ number_format($item->subtotal, 0, ',', '.') }} Gs
                                        </span>
                                    </div>
                                    {{-- Complementos --}}
                                    @if($item->customizations && $item->customizations->count() > 0)
                                        <div class="mt-1 pl-3 space-y-0.5">
                                            @foreach($item->customizations as $c)
                                                <div class="flex justify-between text-xs text-gray-500">
                                                    <span class="text-purple-500">+ {{ $c->option_name }}</span>
                                                    @if($c->price > 0)
                                                        <span class="text-orange-400">+{{ number_format($c->price, 0, ',', '.') }} Gs</span>
                                                    @else
                                                        <span class="text-green-500">incluido</span>
                                                    @endif
                                                </div>
                                            @endforeach
                                        </div>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>

                    {{-- Entrega --}}
                    <div class="bg-gray-50 rounded-2xl p-4 mb-4">
                        <h3 class="font-bold text-gray-700 text-sm mb-2">🚚 Entrega</h3>
                        <div class="text-sm space-y-1">
                            <p><span class="text-gray-500">Tipo:</span> <span class="font-semibold">{{ $selectedOrder->delivery_type === 'delivery' ? 'Delivery' : 'Retiro en tienda' }}</span></p>
                            @if($selectedOrder->delivery_type === 'delivery')
                                <p><span class="text-gray-500">Dirección:</span> <span class="font-semibold">{{ $selectedOrder->customer_address }}</span></p>
                                @if($selectedOrder->deliveryZone)
                                    <p><span class="text-gray-500">Zona:</span> <span class="font-semibold">{{ $selectedOrder->deliveryZone->name }}</span></p>
                                @endif
                                @if($selectedOrder->latitude && $selectedOrder->longitude)
                                    <a href="http://maps.google.com/maps?q={{ $selectedOrder->latitude }},{{ $selectedOrder->longitude }}"
                                        target="_blank"
                                        class="inline-flex items-center gap-1 text-purple-600 hover:text-purple-700 text-xs font-semibold mt-1">
                                        📍 Ver en Google Maps
                                    </a>
                                @endif
                            @endif
                        </div>
                    </div>

                    {{-- Totales --}}
                    <div class="bg-gradient-to-r from-purple-600 to-pink-500 rounded-2xl p-4 mb-4 text-white">
                        <div class="space-y-1.5 text-sm">
                            <div class="flex justify-between">
                                <span class="opacity-80">Método de pago:</span>
                                <span class="font-semibold">{{ $selectedOrder->paymentMethod->name ?? '—' }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="opacity-80">Subtotal:</span>
                                <span>{{ number_format($selectedOrder->subtotal, 0, ',', '.') }} Gs</span>
                            </div>
                            @if($selectedOrder->delivery_cost > 0)
                                <div class="flex justify-between">
                                    <span class="opacity-80">Delivery:</span>
                                    <span>{{ number_format($selectedOrder->delivery_cost, 0, ',', '.') }} Gs</span>
                                </div>
                            @elseif($selectedOrder->delivery_type === 'delivery')
                                <div class="flex justify-between">
                                    <span class="opacity-80">Delivery:</span>
                                    <span class="italic opacity-70">A confirmar</span>
                                </div>
                            @endif
                            <div class="border-t border-white/30 pt-1.5 flex justify-between text-base font-black">
                                <span>TOTAL</span>
                                <span>{{ number_format($selectedOrder->total, 0, ',', '.') }} Gs</span>
                            </div>
                        </div>
                    </div>

                    @if($selectedOrder->notes)
                        <div class="bg-amber-50 border border-amber-200 rounded-2xl p-4 mb-4">
                            <h3 class="font-bold text-amber-800 text-sm mb-1">📝 Notas</h3>
                            <p class="text-sm text-amber-700">{{ $selectedOrder->notes }}</p>
                        </div>
                    @endif

                    <button wire:click="sendToWhatsApp({{ $selectedOrder->id }})"
                        class="w-full bg-green-500 hover:bg-green-600 text-white font-bold py-3.5 rounded-2xl transition-all flex items-center justify-center gap-2">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
                        </svg>
                        Enviar por WhatsApp
                    </button>
                </div>
            </div>
        </div>
    @endif

</div>

@push('scripts')
<script>
    document.addEventListener('livewire:initialized', () => {
        Livewire.on('openWhatsApp', (event) => {
            window.open(event.url, '_blank');
        });
    });
</script>
@endpush
