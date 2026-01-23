<div>
    {{-- Mensajes Flash --}}
    @if (session()->has('message'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-6 flex items-center justify-between">
            <span>{{ session('message') }}</span>
            <button onclick="this.parentElement.remove()" class="text-green-700 hover:text-green-900">✕</button>
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        
        {{-- ============================================ --}}
        {{-- MODO MANTENIMIENTO --}}
        {{-- ============================================ --}}
        <div class="lg:col-span-2">
            <div class="bg-white rounded-xl shadow-md overflow-hidden {{ $maintenanceMode ? 'ring-2 ring-orange-500' : '' }}">
                <div class="p-6 {{ $maintenanceMode ? 'bg-gradient-to-r from-orange-500 to-red-500' : 'bg-gradient-to-r from-gray-700 to-gray-800' }}">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-4">
                            <div class="p-3 rounded-full {{ $maintenanceMode ? 'bg-white/20' : 'bg-white/10' }}">
                                <span class="text-3xl">🚧</span>
                            </div>
                            <div class="text-white">
                                <h2 class="text-xl font-bold">Modo Mantenimiento</h2>
                                <p class="text-white/80 text-sm">
                                    {{ $maintenanceMode ? '⚠️ La tienda está en modo mantenimiento' : 'La tienda está funcionando normalmente' }}
                                </p>
                            </div>
                        </div>
                        <button wire:click="toggleMaintenanceMode"
                            class="px-6 py-3 rounded-xl font-bold text-sm transition-all {{ $maintenanceMode ? 'bg-white text-orange-600 hover:bg-gray-100' : 'bg-orange-500 text-white hover:bg-orange-600' }}">
                            {{ $maintenanceMode ? '✓ Desactivar' : '🚧 Activar' }}
                        </button>
                    </div>
                </div>
                
                <div class="p-6 space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Mensaje para los visitantes
                        </label>
                        <textarea wire:model="maintenanceMessage" 
                            rows="3"
                            placeholder="Ej: ¡Estamos trabajando en algo increíble! Volvemos pronto..."
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent"></textarea>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Fecha estimada de lanzamiento (opcional)
                            </label>
                            <input wire:model="maintenanceDate" 
                                type="date"
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                        </div>
                        <div class="flex items-end">
                            <button wire:click="saveMaintenanceSettings"
                                class="w-full bg-purple-600 hover:bg-purple-700 text-white font-medium py-3 px-6 rounded-lg transition">
                                Guardar Configuración
                            </button>
                        </div>
                    </div>
                    
                    @if($maintenanceMode)
                        <div class="bg-orange-50 border border-orange-200 rounded-lg p-4">
                            <div class="flex items-start gap-3">
                                <span class="text-xl">💡</span>
                                <div class="text-sm text-orange-800">
                                    <p class="font-medium mb-1">Mientras el modo mantenimiento está activo:</p>
                                    <ul class="list-disc list-inside space-y-1 text-orange-700">
                                        <li>Los visitantes verán la página de "Próximamente"</li>
                                        <li>Los administradores pueden seguir accediendo normalmente</li>
                                        <li>Puedes usar este tiempo para configurar todo</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    @endif

                    {{-- Preview --}}
                    <div class="pt-4 border-t">
                        <a href="{{ route('home') }}" target="_blank" 
                            class="inline-flex items-center gap-2 text-purple-600 hover:text-purple-700 font-medium text-sm">
                            <span>👁️ Ver cómo se ve la tienda</span>
                            <span>↗</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        {{-- ============================================ --}}
        {{-- DATOS DE LA TIENDA --}}
        {{-- ============================================ --}}
        <div class="bg-white rounded-xl shadow-md overflow-hidden">
            <div class="p-6 bg-gradient-to-r from-purple-600 to-indigo-600">
                <div class="flex items-center gap-3 text-white">
                    <span class="text-2xl">🏪</span>
                    <h2 class="text-xl font-bold">Datos de la Tienda</h2>
                </div>
            </div>
            
            <div class="p-6 space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Nombre de la Tienda *
                    </label>
                    <input wire:model="storeName" 
                        type="text"
                        placeholder="Taskinho Açaí"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                </div>
                
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Teléfono
                        </label>
                        <input wire:model="storePhone" 
                            type="text"
                            placeholder="+595 981 000000"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Email
                        </label>
                        <input wire:model="storeEmail" 
                            type="email"
                            placeholder="hola@taskinho.com"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                    </div>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Dirección
                    </label>
                    <input wire:model="storeAddress" 
                        type="text"
                        placeholder="Av. Principal 123"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Ciudad
                    </label>
                    <input wire:model="storeCity" 
                        type="text"
                        placeholder="Ciudad del Este"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                </div>
                
                <button wire:click="saveStoreSettings"
                    class="w-full bg-purple-600 hover:bg-purple-700 text-white font-medium py-3 px-6 rounded-lg transition">
                    Guardar Datos
                </button>
            </div>
        </div>

        {{-- ============================================ --}}
        {{-- REDES SOCIALES --}}
        {{-- ============================================ --}}
        <div class="bg-white rounded-xl shadow-md overflow-hidden">
            <div class="p-6 bg-gradient-to-r from-pink-500 to-rose-500">
                <div class="flex items-center gap-3 text-white">
                    <span class="text-2xl">📱</span>
                    <h2 class="text-xl font-bold">Redes Sociales</h2>
                </div>
            </div>
            
            <div class="p-6 space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        <span class="inline-flex items-center gap-2">
                            <svg class="w-5 h-5 text-blue-600" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
                            </svg>
                            Facebook
                        </span>
                    </label>
                    <input wire:model="socialFacebook" 
                        type="url"
                        placeholder="https://facebook.com/taskinhoacai"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        <span class="inline-flex items-center gap-2">
                            <svg class="w-5 h-5 text-pink-600" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zM12 0C8.741 0 8.333.014 7.053.072 2.695.272.273 2.69.073 7.052.014 8.333 0 8.741 0 12c0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98C8.333 23.986 8.741 24 12 24c3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98C15.668.014 15.259 0 12 0zm0 5.838a6.162 6.162 0 100 12.324 6.162 6.162 0 000-12.324zM12 16a4 4 0 110-8 4 4 0 010 8zm6.406-11.845a1.44 1.44 0 100 2.881 1.44 1.44 0 000-2.881z"/>
                            </svg>
                            Instagram
                        </span>
                    </label>
                    <input wire:model="socialInstagram" 
                        type="url"
                        placeholder="https://instagram.com/taskinhoacai"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        <span class="inline-flex items-center gap-2">
                            <svg class="w-5 h-5 text-green-600" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
                            </svg>
                            WhatsApp
                        </span>
                    </label>
                    <input wire:model="socialWhatsapp" 
                        type="text"
                        placeholder="+595981000000"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                    <p class="text-xs text-gray-500 mt-1">Solo el número con código de país, sin espacios ni guiones</p>
                </div>
                
                <button wire:click="saveSocialSettings"
                    class="w-full bg-pink-500 hover:bg-pink-600 text-white font-medium py-3 px-6 rounded-lg transition">
                    Guardar Redes Sociales
                </button>
            </div>
        </div>

        {{-- ============================================ --}}
        {{-- HERRAMIENTAS --}}
        {{-- ============================================ --}}
        <div class="lg:col-span-2">
            <div class="bg-white rounded-xl shadow-md p-6">
                <h3 class="text-lg font-bold text-gray-900 mb-4 flex items-center gap-2">
                    <span>🛠️</span> Herramientas
                </h3>
                
                <div class="flex flex-wrap gap-4">
                    <button wire:click="clearCache"
                        class="bg-gray-100 hover:bg-gray-200 text-gray-800 font-medium py-2 px-4 rounded-lg transition flex items-center gap-2">
                        <span>🗑️</span> Limpiar Caché
                    </button>
                    
                    <a href="{{ route('home') }}" target="_blank"
                        class="bg-gray-100 hover:bg-gray-200 text-gray-800 font-medium py-2 px-4 rounded-lg transition flex items-center gap-2">
                        <span>🏠</span> Ver Tienda
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>