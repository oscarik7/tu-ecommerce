<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Taskinho Açaí - El sabor de Brasil en Paraguay' }}</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    @livewireStyles
</head>
<body class="bg-gray-50 flex flex-col min-h-screen">
    
    <livewire:components.navbar />
    
    <main class="flex-grow">
        {{ $slot }}
    </main>

    {{-- Footer completo de Taskinho Açaí --}}
    <footer class="bg-gradient-to-r from-purple-900 to-purple-700 text-white py-8 sm:py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid md:grid-cols-4 gap-8 mb-8">
                {{-- Columna 1: Sobre Taskinho --}}
                <div>
                    <h3 class="text-lg font-bold mb-4">Taskinho Açaí</h3>
                    <p class="text-sm text-purple-200 mb-4">
                        El sabor de Brasil ahora en Paraguay 🇧🇷🇵🇾<br>
                        Açaí cremoso, auténtico y delicioso.
                    </p>
                    <p class="text-xs text-purple-300">
                        100% natural, 0% conservantes
                    </p>
                </div>

                {{-- Columna 2: Enlaces Rápidos --}}
                <div>
                    <h4 class="font-bold mb-4">Enlaces Rápidos</h4>
                    <ul class="space-y-2 text-sm">
                        <li><a href="{{ route('home') }}" class="text-purple-200 hover:text-white transition">Inicio</a></li>
                        <li><a href="{{ route('home') }}#productos" class="text-purple-200 hover:text-white transition">Productos</a></li>
                        @auth
                            <li><a href="{{ route('cart') }}" class="text-purple-200 hover:text-white transition">Mi Carrito</a></li>
                            <li><a href="{{ route('my-orders') }}" class="text-purple-200 hover:text-white transition">Mis Pedidos</a></li>
                        @endauth
                        @guest
                            <li><a href="{{ route('login') }}" class="text-purple-200 hover:text-white transition">Iniciar Sesión</a></li>
                            <li><a href="{{ route('register') }}" class="text-purple-200 hover:text-white transition">Registrarse</a></li>
                        @endguest
                    </ul>
                </div>

                {{-- Columna 3: Información de Contacto --}}
                <div>
                    <h4 class="font-bold mb-4">Información</h4>
                    <ul class="space-y-2 text-sm">
                        <li class="flex items-start gap-2">
                            <svg class="w-4 h-4 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"/>
                            </svg>
                            <span class="text-purple-200">Av. San José<br>Ciudad del Este 7000, Paraguay</span>
                        </li>
                        <li class="flex items-center gap-2">
                            <svg class="w-4 h-4 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"/>
                            </svg>
                            <span class="text-purple-200">Jue-Dom/Mar: 13:00-21:00</span>
                        </li>
                        <li class="flex items-center gap-2">
                            <svg class="w-4 h-4 flex-shrink-0 text-red-300" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M13.477 14.89A6 6 0 015.11 6.524l8.367 8.368zm1.414-1.414L6.524 5.11a6 6 0 018.367 8.367zM18 10a8 8 0 11-16 0 8 8 0 0116 0z" clip-rule="evenodd"/>
                            </svg>
                            <span class="text-red-300 font-semibold">Lunes: Cerrado</span>
                        </li>
                        <li>
                            <a href="https://wa.me/595986150627" target="_blank" class="flex items-center gap-2 text-purple-200 hover:text-white transition">
                                <svg class="w-4 h-4 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M2 3a1 1 0 011-1h2.153a1 1 0 01.986.836l.74 4.435a1 1 0 01-.54 1.06l-1.548.773a11.037 11.037 0 006.105 6.105l.774-1.548a1 1 0 011.059-.54l4.435.74a1 1 0 01.836.986V17a1 1 0 01-1 1h-2C7.82 18 2 12.18 2 5V3z"/>
                                </svg>
                                +595 986 150627
                            </a>
                        </li>
                    </ul>
                </div>

                {{-- Columna 4: Redes Sociales --}}
                <div>
                    <h4 class="font-bold mb-4">Síguenos</h4>
                    <div class="flex gap-3 mb-4">
                        <a href="https://www.instagram.com/taskinhoacai" 
                           target="_blank" 
                           class="bg-purple-800 hover:bg-purple-900 p-3 rounded-full transition-all duration-300 hover:scale-110"
                           title="Instagram">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/>
                            </svg>
                        </a>
                        <a href="https://wa.me/595986150627" 
                           target="_blank" 
                           class="bg-green-600 hover:bg-green-700 p-3 rounded-full transition-all duration-300 hover:scale-110"
                           title="WhatsApp">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z"/>
                            </svg>
                        </a>
                        <a href="https://maps.app.goo.gl/SQELP9yYZYx9DPrJ7" 
                           target="_blank" 
                           class="bg-purple-800 hover:bg-purple-900 p-3 rounded-full transition-all duration-300 hover:scale-110"
                           title="Ubicación">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"/>
                            </svg>
                        </a>
                    </div>
                    <p class="text-xs text-purple-200 mb-3">
                        ¡Contáctanos por WhatsApp para pedidos y consultas!
                    </p>
                    <a href="https://www.instagram.com/taskinhoacai" 
                       target="_blank" 
                       class="inline-flex items-center gap-2 text-sm text-purple-200 hover:text-white transition">
                        <span>📷</span>
                        <span>@taskinhoacai</span>
                    </a>
                </div>
            </div>

            {{-- Separador --}}
            <div class="border-t border-purple-800 pt-6">
                <div class="flex flex-col md:flex-row justify-between items-center gap-4">
                    {{-- Copyright --}}
                    <div class="text-center md:text-left">
                        <p class="text-sm text-purple-200">
                            &copy; {{ date('Y') }} Taskinho Açaí Py. Todos los derechos reservados.
                        </p>
                        <p class="text-xs text-purple-300 mt-1">
                            Hecho con 💜 en Ciudad del Este, Paraguay
                        </p>
                    </div>
                    
                    {{-- Créditos de desarrollo --}}
                    <div class="text-center md:text-right">
                        <p class="text-xs text-purple-300">
                            Desarrollado por <span class="font-semibold text-purple-200">Devparaguay</span>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </footer>

    {{-- Botón flotante de WhatsApp (disponible en todas las páginas) --}}
    <a href="https://wa.me/595986150627?text=Hola!%20Quiero%20hacer%20un%20pedido%20de%20Taskinho%20Açaí" 
       target="_blank"
       class="fixed bottom-6 right-6 bg-green-500 hover:bg-green-600 text-white rounded-full p-4 shadow-2xl z-50 transition-all duration-300 hover:scale-110 animate-bounce group"
       title="Chatea con nosotros por WhatsApp"
       id="whatsapp-float-global">
        <svg class="w-8 h-8" fill="currentColor" viewBox="0 0 24 24">
            <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z"/>
        </svg>
        {{-- Tooltip --}}
        <span class="absolute right-full mr-3 top-1/2 -translate-y-1/2 bg-gray-900 text-white text-xs px-3 py-2 rounded-lg whitespace-nowrap opacity-0 group-hover:opacity-100 transition-opacity pointer-events-none">
            Escríbenos por WhatsApp
        </span>
    </a>

    @livewireScripts
    @stack('scripts')

    {{-- Script para animar el botón de WhatsApp --}}
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Detener la animación bounce después de 3 segundos
            setTimeout(function() {
                const whatsappBtn = document.getElementById('whatsapp-float-global');
                if (whatsappBtn) {
                    whatsappBtn.classList.remove('animate-bounce');
                }
            }, 3000);
        });
    </script>
</body>
</html>