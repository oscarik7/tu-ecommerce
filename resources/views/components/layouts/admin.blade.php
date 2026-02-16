<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Admin - Açaí Store' }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="bg-gray-100 antialiased">

{{-- ══════════════════════════════════════════
     OVERLAY (mobile) — cierra el sidebar al tocar fuera
════════════════════════════════════════════ --}}
<div id="sidebarOverlay"
     class="fixed inset-0 bg-black/50 z-30 hidden lg:hidden"
     onclick="closeSidebar()"></div>

<div class="min-h-screen flex">

    {{-- ══════════════════════════════════════
         SIDEBAR
    ══════════════════════════════════════ --}}
    <aside id="sidebar"
           class="fixed inset-y-0 left-0 z-40 w-64 bg-gray-900 text-white flex flex-col
                  transform -translate-x-full transition-transform duration-300 ease-in-out
                  lg:relative lg:translate-x-0 lg:z-auto lg:flex-shrink-0">

        {{-- Logo --}}
        <div class="flex items-center justify-between p-4 border-b border-gray-700/60">
            <div>
                <h1 class="text-lg font-black tracking-tight">🍇 Admin Panel</h1>
                <p class="text-gray-400 text-xs mt-0.5 truncate max-w-[170px]">{{ auth()->user()->name }}</p>
            </div>
            {{-- Botón X solo en móvil --}}
            <button onclick="closeSidebar()"
                    class="lg:hidden p-1.5 rounded-lg hover:bg-gray-700 text-gray-400 hover:text-white transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        {{-- Nav scrollable --}}
        <nav class="flex-1 overflow-y-auto py-3 space-y-0.5 px-2">

            {{-- Dashboard --}}
            @can('view dashboard')
                <a href="{{ route('admin.dashboard') }}" onclick="closeSidebarOnMobile()"
                   class="nav-link {{ request()->routeIs('admin.dashboard') ? 'nav-active' : '' }}">
                    <span class="text-base">📊</span> Dashboard
                </a>
            @endcan

            {{-- ── Operaciones ── --}}
            @canany(['view pos', 'manage orders', 'manage cash registers', 'manage expenses', 'manage employees', 'view reports'])
                <div class="nav-section-label">Operaciones</div>
            @endcanany

            @can('view pos')
                <a href="{{ route('admin.pos') }}" onclick="closeSidebarOnMobile()"
                   class="nav-link {{ request()->routeIs('admin.pos') ? 'nav-active' : '' }}">
                    <span class="text-base">🏪</span> Punto de Venta
                </a>
            @endcan

            @can('manage orders')
                <a href="{{ route('admin.orders') }}" onclick="closeSidebarOnMobile()"
                   class="nav-link {{ request()->routeIs('admin.orders') ? 'nav-active' : '' }}">
                    <span class="text-base">🛒</span> Pedidos
                </a>
            @endcan

            @can('manage cash registers')
                <a href="{{ route('admin.cash-registers') }}" onclick="closeSidebarOnMobile()"
                   class="nav-link {{ request()->routeIs('admin.cash-registers') ? 'nav-active' : '' }}">
                    <span class="text-base">🏦</span>
                    <span class="flex-1">Caja</span>
                    @php $cajaAbierta = \App\Models\CashRegister::hasOpenRegister(); @endphp
                    <span class="text-[10px] px-2 py-0.5 rounded-full font-bold
                        {{ $cajaAbierta ? 'bg-green-500/20 text-green-400 ring-1 ring-green-500/40' : 'bg-red-500/20 text-red-400 ring-1 ring-red-500/40' }}">
                        {{ $cajaAbierta ? 'Abierta' : 'Cerrada' }}
                    </span>
                </a>
            @endcan

            @can('manage expenses')
                <a href="{{ route('admin.expenses') }}" onclick="closeSidebarOnMobile()"
                   class="nav-link {{ request()->routeIs('admin.expenses') ? 'nav-active' : '' }}">
                    <span class="text-base">💸</span> Egresos
                </a>
            @endcan

            @can('manage employees')
                <a href="{{ route('admin.employees') }}" onclick="closeSidebarOnMobile()"
                   class="nav-link {{ request()->routeIs('admin.employees') ? 'nav-active' : '' }}">
                    <span class="text-base">👥</span> Funcionarios
                </a>
            @endcan

            @can('view reports')
                <a href="{{ route('admin.reports') }}" onclick="closeSidebarOnMobile()"
                   class="nav-link {{ request()->routeIs('admin.reports') ? 'nav-active' : '' }}">
                    <span class="text-base">📈</span> Reportes
                </a>
            @endcan

            @can('manage customizations')
                <a href="{{ route('admin.customizations') }}" onclick="closeSidebarOnMobile()"
                   class="nav-link {{ request()->routeIs('admin.customizations') ? 'nav-active' : '' }}">
                    <span class="text-base">🍓</span> Complementos
                </a>
            @endcan

            {{-- ── Catálogo ── --}}
            @canany(['manage products', 'manage categories', 'manage inventory'])
                <div class="nav-section-label">Catálogo</div>
            @endcanany

            @can('manage products')
                <a href="{{ route('admin.products') }}" onclick="closeSidebarOnMobile()"
                   class="nav-link {{ request()->routeIs('admin.products') ? 'nav-active' : '' }}">
                    <span class="text-base">🍨</span> Productos
                </a>
            @endcan

            @can('manage categories')
                <a href="{{ route('admin.categories') }}" onclick="closeSidebarOnMobile()"
                   class="nav-link {{ request()->routeIs('admin.categories') ? 'nav-active' : '' }}">
                    <span class="text-base">📁</span> Categorías
                </a>
            @endcan

            @can('manage inventory')
                <a href="{{ route('admin.inventory') }}" onclick="closeSidebarOnMobile()"
                   class="nav-link {{ request()->routeIs('admin.inventory') ? 'nav-active' : '' }}">
                    <span class="text-base">📦</span> Inventario
                </a>
            @endcan

            {{-- ── Configuración ── --}}
            @canany(['manage delivery zones', 'manage payment methods', 'manage users'])
                <div class="nav-section-label">Configuración</div>
            @endcanany

            @can('manage delivery zones')
                <a href="{{ route('admin.delivery-zones') }}" onclick="closeSidebarOnMobile()"
                   class="nav-link {{ request()->routeIs('admin.delivery-zones') ? 'nav-active' : '' }}">
                    <span class="text-base">📍</span> Zonas de Delivery
                </a>
            @endcan

            @can('manage payment methods')
                <a href="{{ route('admin.payment-methods') }}" onclick="closeSidebarOnMobile()"
                   class="nav-link {{ request()->routeIs('admin.payment-methods') ? 'nav-active' : '' }}">
                    <span class="text-base">💳</span> Métodos de Pago
                </a>
            @endcan

            @can('manage users')
                <a href="{{ route('admin.roles') }}" onclick="closeSidebarOnMobile()"
                   class="nav-link {{ request()->routeIs('admin.roles') ? 'nav-active' : '' }}">
                    <span class="text-base">🔐</span> Roles y Permisos
                </a>
                <a href="{{ route('admin.users') }}" onclick="closeSidebarOnMobile()"
                   class="nav-link {{ request()->routeIs('admin.users') ? 'nav-active' : '' }}">
                    <span class="text-base">👥</span> Clientes
                </a>
                <a href="{{ route('admin.settings') }}" onclick="closeSidebarOnMobile()"
                   class="nav-link {{ request()->routeIs('admin.settings') ? 'nav-active' : '' }}">
                    <span class="text-base">⚙️</span> Modo Mantenimiento
                </a>
                <a href="{{ route('admin.store-settings') }}"onclick="closeSidebarOnMobile()"
                  class="nav-link {{ request()->routeIs('admin.store-settings') ? 'nav-active' : '' }}">
                    <span class="text-base">🏪</span> Config. Tienda
                </a>
            @endcan

            {{-- ── Dueño ── --}}
            @can('view activity log')
                <div class="nav-section-label">Dueño</div>
                <a href="{{ route('admin.activity') }}" onclick="closeSidebarOnMobile()"
                   class="nav-link {{ request()->routeIs('admin.activity') ? 'nav-active' : '' }}">
                    <span class="text-base">📋</span> Historial
                </a>
            @endcan

            {{-- ── Pantalla TV ── --}}
            @can('view pedidostv')
                <div class="nav-section-label">Pantallas</div>
                <a href="{{ route('pedidos.tv') }}" target="_blank"
                   class="nav-link group">
                    <span class="text-base">📺</span>
                    <span class="flex-1">Pantalla Pedidos</span>
                    <svg class="w-3.5 h-3.5 text-gray-500 group-hover:text-gray-300 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                    </svg>
                </a>
            @endcan

        </nav>

        {{-- Footer --}}
        <div class="border-t border-gray-700/60 p-3 space-y-1">
            <a href="{{ route('home') }}"
               class="nav-link text-gray-400 hover:text-white">
                <span class="text-base">🏠</span> Ver Tienda
            </a>
            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button type="submit"
                        class="nav-link w-full text-left text-red-400 hover:text-red-300 hover:bg-red-500/10">
                    <span class="text-base">🚪</span> Cerrar Sesión
                </button>
            </form>
        </div>
    </aside>

    {{-- ══════════════════════════════════════
         MAIN CONTENT
    ══════════════════════════════════════ --}}
    <div class="flex-1 flex flex-col min-w-0">

        {{-- Topbar --}}
        <header class="bg-white border-b border-gray-200 sticky top-0 z-20">
            <div class="flex items-center gap-3 px-4 py-3 lg:px-6">

                {{-- Hamburger (solo móvil) --}}
                <button onclick="openSidebar()"
                        class="lg:hidden p-2 rounded-xl hover:bg-gray-100 text-gray-600 transition flex-shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                    </svg>
                </button>

                {{-- Título --}}
                <h2 class="text-base lg:text-xl font-black text-gray-900 truncate flex-1">
                    {{ $title ?? 'Dashboard' }}
                </h2>

                {{-- Right side --}}
                <div class="flex items-center gap-2 lg:gap-4 flex-shrink-0">
                    {{-- Fecha (solo desktop) --}}
                    <span class="hidden sm:block text-xs text-gray-400 font-medium">
                        {{ now()->format('d/m/Y H:i') }}
                    </span>

                    {{-- Avatar + info --}}
                    <div class="flex items-center gap-2">
                        <div class="w-8 h-8 rounded-full bg-gradient-to-br from-purple-500 to-pink-500
                                    flex items-center justify-center text-white font-black text-sm flex-shrink-0">
                            {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                        </div>
                        <div class="hidden sm:block text-sm leading-tight">
                            <div class="font-semibold text-gray-900 truncate max-w-[120px]">{{ auth()->user()->name }}</div>
                            <div class="text-xs text-gray-400">{{ auth()->user()->roles->pluck('name')->implode(', ') }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </header>

        {{-- Page content --}}
        <main class="flex-1 p-4 lg:p-6 overflow-auto">
            {{ $slot }}
        </main>
    </div>
</div>

{{-- ══ Estilos de nav reutilizables ══ --}}
<style>
    .nav-link {
        display: flex;
        align-items: center;
        gap: 0.625rem;
        padding: 0.5rem 0.625rem;
        border-radius: 0.625rem;
        font-size: 0.8125rem;
        font-weight: 500;
        color: #d1d5db;
        transition: background-color 0.15s, color 0.15s;
        cursor: pointer;
        text-decoration: none;
    }
    .nav-link:hover {
        background-color: rgba(255,255,255,0.08);
        color: #fff;
    }
    .nav-active {
        background-color: rgba(139,92,246,0.2);
        color: #fff;
        font-weight: 700;
        box-shadow: inset 3px 0 0 #8b5cf6;
    }
    .nav-section-label {
        padding: 0.625rem 0.625rem 0.25rem;
        margin-top: 0.5rem;
        font-size: 0.65rem;
        font-weight: 700;
        letter-spacing: 0.08em;
        text-transform: uppercase;
        color: #6b7280;
    }
</style>

{{-- ══ Script del sidebar ══ --}}
<script>
    function openSidebar() {
        document.getElementById('sidebar').classList.remove('-translate-x-full');
        document.getElementById('sidebarOverlay').classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    }
    function closeSidebar() {
        document.getElementById('sidebar').classList.add('-translate-x-full');
        document.getElementById('sidebarOverlay').classList.add('hidden');
        document.body.style.overflow = '';
    }
    function closeSidebarOnMobile() {
        if (window.innerWidth < 1024) closeSidebar();
    }
    // Cerrar con ESC
    document.addEventListener('keydown', e => { if (e.key === 'Escape') closeSidebar(); });
</script>

@livewireScripts
</body>
</html>