<nav class="bg-white shadow-lg sticky top-0 z-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex items-center">
                <a href="{{ route('home') }}" class="flex items-center">
                    <span class="text-3xl">🍇</span>
                    <span class="ml-2 text-xl font-bold text-purple-600">Taskinho Açaí</span>
                </a>
            </div>

            <div class="flex items-center space-x-4">
                @auth
                    @role('customer')
                        <!-- Carrito -->
                        <a href="{{ route('cart') }}" class="relative inline-flex items-center px-4 py-2 text-gray-700 hover:text-purple-600 transition">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                            </svg>
                            @if($cartCount > 0)
                                <span class="absolute -top-1 -right-1 bg-red-500 text-white text-xs font-bold rounded-full h-5 w-5 flex items-center justify-center">
                                    {{ $cartCount }}
                                </span>
                            @endif
                            <span class="ml-2 font-medium hidden sm:inline">Carrito</span>
                        </a>

                        <!-- Mis Pedidos -->
                        <a href="{{ route('my-orders') }}" class="text-gray-700 hover:text-purple-600 font-medium transition">
                            Mis Pedidos
                        </a>

                        <!-- Nombre Usuario -->
                        <span class="text-gray-700 hidden md:inline">{{ auth()->user()->name }}</span>
                    @endrole

                    @role('admin')
                        <a href="{{ route('admin.dashboard') }}" class="text-gray-700 hover:text-purple-600 font-medium transition">
                            Panel Admin
                        </a>
                        <span class="text-gray-700 hidden md:inline">{{ auth()->user()->name }}</span>
                    @endrole

                    <!-- Botón Cerrar Sesión -->
                    <form action="{{ route('logout') }}" method="POST" class="inline">
                        @csrf
                        <button type="submit" class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-lg transition font-medium">
                            Cerrar Sesión
                        </button>
                    </form>
                @else
                    <!-- Botones para visitantes -->
                    <a href="{{ route('login') }}" class="text-gray-700 hover:text-purple-600 font-medium transition">
                        Iniciar Sesión
                    </a>
                    <a href="{{ route('register') }}" class="bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded-lg transition font-medium">
                        Registrarse
                    </a>
                @endauth
            </div>
        </div>
    </div>
</nav>