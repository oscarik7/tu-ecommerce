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
        <div class="w-64 bg-gray-800 text-white">
            <div class="p-4">
                <h1 class="text-2xl font-bold">🍇 Admin Panel</h1>
            </div>
            <nav class="mt-8">
                <a href="{{ route('admin.dashboard') }}" class="block py-2.5 px-4 hover:bg-gray-700 {{ request()->routeIs('admin.dashboard') ? 'bg-gray-700' : '' }}">
                    📊 Dashboard
                </a>
                <a href="{{ route('admin.orders') }}" class="block py-2.5 px-4 hover:bg-gray-700 {{ request()->routeIs('admin.orders') ? 'bg-gray-700' : '' }}">
                    🛒 Pedidos
                </a>
                <a href="{{ route('admin.products') }}" class="block py-2.5 px-4 hover:bg-gray-700 {{ request()->routeIs('admin.products') ? 'bg-gray-700' : '' }}">
                    🍨 Productos
                </a>
                <a href="{{ route('admin.categories') }}" class="block py-2.5 px-4 hover:bg-gray-700 {{ request()->routeIs('admin.categories') ? 'bg-gray-700' : '' }}">
                    📁 Categorías
                </a>
                <a href="{{ route('admin.delivery-zones') }}" class="block py-2.5 px-4 hover:bg-gray-700 {{ request()->routeIs('admin.delivery-zones') ? 'bg-gray-700' : '' }}">
                    📍 Zonas de Delivery
                </a>
                <a href="{{ route('admin.payment-methods') }}" class="block py-2.5 px-4 hover:bg-gray-700 {{ request()->routeIs('admin.payment-methods') ? 'bg-gray-700' : '' }}">
                    💳 Métodos de Pago
                </a>
                <a href="{{ route('home') }}" class="block py-2.5 px-4 hover:bg-gray-700">
                    🏠 Ver Tienda
                </a>
                <a href="{{ route('admin.pos') }}" 
                class="block py-2.5 px-4 hover:bg-gray-700 {{ request()->routeIs('admin.pos') ? 'bg-purple-100 text-purple-700 font-bold' : '' }}">
                    <span>🛒 Punto de Venta </span>
                </a>
                <form action="{{ route('logout') }}" method="POST" class="mt-4">
                    @csrf
                    <button type="submit" class="w-full text-left py-2.5 px-4 hover:bg-gray-700">
                        🚪 Cerrar Sesión
                    </button>
                </form>
            </nav>
        </div>

        <!-- Main Content -->
        <div class="flex-1">
            <div class="bg-white shadow">
                <div class="px-4 py-4">
                    <h2 class="text-2xl font-bold text-gray-800">{{ $title ?? 'Dashboard' }}</h2>
                </div>
            </div>
            <div class="p-6">
                {{ $slot }}
            </div>
        </div>
    </div>

    @livewireScripts
</body>
</html>