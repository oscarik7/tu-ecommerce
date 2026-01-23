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
        // Verificar si está en modo mantenimiento
        if (Setting::isMaintenanceMode()) {
            
            // Permitir acceso a rutas de admin y login
            if ($this->shouldPassThrough($request)) {
                return $next($request);
            }

            // Permitir acceso a usuarios admin
            if (auth()->check() && auth()->user()->hasRole('admin')) {
                return $next($request);
            }

            // Mostrar página de mantenimiento
            return response()->view('maintenance', [
                'message' => Setting::getMaintenanceMessage(),
                'date' => Setting::getMaintenanceDate(),
                'storeName' => Setting::get('store_name', 'Taskinho Açaí'),
            ], 503);
        }

        return $next($request);
    }

    /**
     * Verificar si la ruta debe pasar sin verificación
     */
    protected function shouldPassThrough(Request $request): bool
    {
        foreach ($this->exceptRoutes as $route) {
            if ($request->routeIs($route)) {
                return true;
            }
        }

        return false;
    }
}