<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Admin - Açaí Store' }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="bg-gray-100">
    <div class="min-h-screen flex">

        <!-- Sidebar -->
        <div class="w-64 bg-gray-800 text-white flex flex-col">
            <div class="p-4 border-b border-gray-700">
                <h1 class="text-2xl font-bold">🍇 Admin Panel</h1>
                <p class="text-gray-400 text-xs mt-1">{{ auth()->user()->name }}</p>
            </div>

            <nav class="mt-4 flex-1">

                {{-- Dashboard --}}
                @can('view dashboard')
                    <a href="{{ route('admin.dashboard') }}"
                        class="block py-2.5 px-4 hover:bg-gray-700 transition {{ request()->routeIs('admin.dashboard') ? 'bg-gray-700 border-l-4 border-purple-500' : '' }}">
                        📊 Dashboard
                    </a>
                @endcan

                {{-- Sección Operaciones --}}
                @canany(['view pos', 'manage orders', 'manage cash registers', 'manage expenses', 'manage employees'])
                    <div class="my-4 border-t border-gray-700"></div>
                    <p class="px-4 text-xs text-gray-500 uppercase tracking-wider mb-2">Operaciones</p>
                @endcanany

                {{-- POS --}}
                @can('view pos')
                    <a href="{{ route('admin.pos') }}"
                        class="block py-2.5 px-4 hover:bg-gray-700 transition {{ request()->routeIs('admin.pos') ? 'bg-gray-700 border-l-4 border-purple-500' : '' }}">
                        🏪 Punto de Venta
                    </a>
                @endcan

                {{-- Pedidos --}}
                @can('manage orders')
                    <a href="{{ route('admin.orders') }}"
                        class="block py-2.5 px-4 hover:bg-gray-700 transition {{ request()->routeIs('admin.orders') ? 'bg-gray-700 border-l-4 border-purple-500' : '' }}">
                        🛒 Pedidos
                    </a>
                @endcan

                {{-- Caja --}}
                @can('manage cash registers')
                    <a href="{{ route('admin.cash-registers') }}"
                        class="block py-2.5 px-4 hover:bg-gray-700 transition {{ request()->routeIs('admin.cash-registers') ? 'bg-gray-700 border-l-4 border-purple-500' : '' }}">
                        <div class="flex items-center justify-between">
                            <span>🏦 Caja</span>
                            @php $cajaAbierta = \App\Models\CashRegister::hasOpenRegister(); @endphp
                            @if($cajaAbierta)
                                <span class="text-[10px] bg-green-500 text-white px-1.5 py-0.5 rounded-full font-bold">Abierta</span>
                            @else
                                <span class="text-[10px] bg-red-500 text-white px-1.5 py-0.5 rounded-full font-bold">Cerrada</span>
                            @endif
                        </div>
                    </a>
                @endcan

                {{-- Egresos --}}
                @can('manage expenses')
                    <a href="{{ route('admin.expenses') }}"
                        class="block py-2.5 px-4 hover:bg-gray-700 transition {{ request()->routeIs('admin.expenses') ? 'bg-gray-700 border-l-4 border-purple-500' : '' }}">
                        💸 Egresos
                    </a>
                @endcan

                {{-- Empleados --}}
                @can('manage employees')
                    <a href="{{ route('admin.employees') }}"
                        class="block py-2.5 px-4 hover:bg-gray-700 transition {{ request()->routeIs('admin.employees') ? 'bg-gray-700 border-l-4 border-purple-500' : '' }}">
                        👥 Empleados
                    </a>
                @endcan

                {{-- Sección Catálogo --}}
                @canany(['manage products', 'manage categories', 'manage inventory'])
                    <div class="my-4 border-t border-gray-700"></div>
                    <p class="px-4 text-xs text-gray-500 uppercase tracking-wider mb-2">Catálogo</p>
                @endcanany

                {{-- Productos --}}
                @can('manage products')
                    <a href="{{ route('admin.products') }}"
                        class="block py-2.5 px-4 hover:bg-gray-700 transition {{ request()->routeIs('admin.products') ? 'bg-gray-700 border-l-4 border-purple-500' : '' }}">
                        🍨 Productos
                    </a>
                @endcan

                {{-- Categorías --}}
                @can('manage categories')
                    <a href="{{ route('admin.categories') }}"
                        class="block py-2.5 px-4 hover:bg-gray-700 transition {{ request()->routeIs('admin.categories') ? 'bg-gray-700 border-l-4 border-purple-500' : '' }}">
                        📁 Categorías
                    </a>
                @endcan

                {{-- Inventario --}}
                @can('manage inventory')
                    <a href="{{ route('admin.inventory') }}"
                        class="block py-2.5 px-4 hover:bg-gray-700 transition {{ request()->routeIs('admin.inventory') ? 'bg-gray-700 border-l-4 border-purple-500' : '' }}">
                        📦 Inventario
                    </a>
                @endcan

                {{-- Sección Configuración --}}
                @canany(['manage delivery zones', 'manage payment methods', 'manage users'])
                    <div class="my-4 border-t border-gray-700"></div>
                    <p class="px-4 text-xs text-gray-500 uppercase tracking-wider mb-2">Configuración</p>
                @endcanany

                {{-- Zonas de Delivery --}}
                @can('manage delivery zones')
                    <a href="{{ route('admin.delivery-zones') }}"
                        class="block py-2.5 px-4 hover:bg-gray-700 transition {{ request()->routeIs('admin.delivery-zones') ? 'bg-gray-700 border-l-4 border-purple-500' : '' }}">
                        📍 Zonas de Delivery
                    </a>
                @endcan

                {{-- Métodos de Pago --}}
                @can('manage payment methods')
                    <a href="{{ route('admin.payment-methods') }}"
                        class="block py-2.5 px-4 hover:bg-gray-700 transition {{ request()->routeIs('admin.payment-methods') ? 'bg-gray-700 border-l-4 border-purple-500' : '' }}">
                        💳 Métodos de Pago
                    </a>
                @endcan

                {{-- Roles y Permisos --}}
                @can('manage users')
                    <a href="{{ route('admin.roles') }}"
                        class="block py-2.5 px-4 hover:bg-gray-700 transition {{ request()->routeIs('admin.roles') ? 'bg-gray-700 border-l-4 border-purple-500' : '' }}">
                        🔐 Roles y Permisos
                    </a>
                @endcan

                {{-- Configuración --}}
                @can('manage users')
                    <a href="{{ route('admin.settings') }}"
                        class="block py-2.5 px-4 hover:bg-gray-700 transition {{ request()->routeIs('admin.settings') ? 'bg-gray-700 border-l-4 border-purple-500' : '' }}">
                        ⚙️ Configuración
                    </a>
                @endcan

                {{-- Pantalla TV --}}
                @can('view pedidostv')
                    <div class="my-4 border-t border-gray-700"></div>
                    <a href="{{ route('pedidos.tv') }}" target="_blank"
                        class="block py-2.5 px-4 hover:bg-gray-700 transition">
                        📺 Pantalla Pedidos
                        <span class="text-xs text-gray-400 ml-1">↗</span>
                    </a>
                @endcan

            </nav>

            {{-- Footer del Sidebar --}}
            <div class="border-t border-gray-700 p-4">
                <a href="{{ route('home') }}" class="block py-2 px-2 hover:bg-gray-700 rounded transition text-sm">
                    🏠 Ver Tienda
                </a>
                <form action="{{ route('logout') }}" method="POST" class="mt-2">
                    @csrf
                    <button type="submit" class="w-full text-left py-2 px-2 hover:bg-gray-700 rounded transition text-sm text-red-400 hover:text-red-300">
                        🚪 Cerrar Sesión
                    </button>
                </form>
            </div>
        </div>

        <!-- Main Content -->
        <div class="flex-1 flex flex-col">
            <div class="bg-white shadow">
                <div class="px-6 py-4 flex items-center justify-between">
                    <h2 class="text-2xl font-bold text-gray-800">{{ $title ?? 'Dashboard' }}</h2>
                    <div class="flex items-center gap-4">
                        <span class="text-sm text-gray-500">{{ now()->format('d/m/Y H:i') }}</span>
                        <div class="flex items-center gap-2">
                            <div class="w-8 h-8 rounded-full bg-gradient-to-br from-purple-400 to-pink-400 flex items-center justify-center text-white font-bold text-sm">
                                {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                            </div>
                            <div class="text-sm">
                                <div class="font-medium text-gray-900">{{ auth()->user()->name }}</div>
                                <div class="text-xs text-gray-500">{{ auth()->user()->roles->pluck('name')->implode(', ') }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="flex-1 p-6 overflow-auto">
                {{ $slot }}
            </div>
        </div>
    </div>

    @livewireScripts
</body>
</html>
