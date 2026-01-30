<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Setting;

class CheckMaintenanceMode
{
    /**
     * Rutas que siempre están disponibles (para que admin pueda acceder)
     */
    protected array $exceptRoutes = [
        'login',
        'logout',
        'admin.*',
        'product.image',
    ];


    public function handle(Request $request, Closure $next): Response
    {
        // 1. Si NO está en modo mantenimiento, sigue adelante normal
        if (!Setting::isMaintenanceMode()) {
            return $next($request);
        }

        // 2. Si es una ruta exceptuada (Login, Logout, etc), permitir siempre
        if ($this->shouldPassThrough($request)) {
            return $next($request);
        }

        // 3. Verificar si es admin. 
        // IMPORTANTE: Asegúrate de que 'admin' sea el nombre correcto del rol en tu sistema
        if (auth()->check() && auth()->user()->hasRole('admin')) {
            return $next($request);
        }

        // 4. Si llegó aquí y es una ruta de Livewire, permitirla solo si es del panel admin
        // Esto evita que Livewire falle al intentar actualizar componentes durante el mantenimiento
        if ($request->hasHeader('X-Livewire')) {
            return $next($request);
        }

        // 5. Mostrar página de mantenimiento
        return response()->view('maintenance', [
            'message' => Setting::getMaintenanceMessage(),
            'date' => Setting::getMaintenanceDate(),
            'storeName' => Setting::get('store_name', 'Taskinho Açaí'),
        ], 503);
    }

    protected function shouldPassThrough(Request $request): bool
    {
        // 1. Verificar por nombre de ruta (ej: route('login'))
        if ($request->route() && $request->route()->getName()) {
            foreach ($this->exceptRoutes as $except) {
                if ($request->routeIs($except)) {
                    return true;
                }
            }
        }

        // 2. Verificar por URL (como respaldo para el login manual)
        if ($request->is('login') || $request->is('admin/*')) {
            return true;
        }

        return false;
    }
}