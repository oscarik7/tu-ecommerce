<?php

namespace App\Livewire\Admin;

use App\Models\Order;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use Livewire\Component;
use Illuminate\Support\Facades\DB;

class Dashboard extends Component
{
    public function render()
    {
        // Estadísticas generales
        $totalOrders = Order::count();
        $pendingOrders = Order::where('status', 'pending')->count();
        $totalProducts = Product::count();
        $totalCustomers = User::role('customer')->count();

        // Ingresos
        $todayRevenue = Order::whereDate('created_at', today())
            ->where('status', '!=', 'cancelled')
            ->sum('total');

        $monthRevenue = Order::whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->where('status', '!=', 'cancelled')
            ->sum('total');

        // Órdenes recientes
        $recentOrders = Order::with(['user', 'items.product', 'items.variant'])
            ->orderBy('created_at', 'desc')
            ->take(10)
            ->get();

        // TOP Productos (basado en OrderItems)
        $topProducts = DB::table('order_items')
            ->select('product_id', DB::raw('SUM(quantity) as total_sold'), DB::raw('MAX(product_name) as product_name'))
            ->groupBy('product_id')
            ->orderBy('total_sold', 'desc')
            ->take(5)
            ->get();

        return view('livewire.admin.dashboard', [
            'totalOrders' => $totalOrders,
            'pendingOrders' => $pendingOrders,
            'totalProducts' => $totalProducts,
            'totalCustomers' => $totalCustomers,
            'todayRevenue' => $todayRevenue,
            'monthRevenue' => $monthRevenue,
            'recentOrders' => $recentOrders,
            'topProducts' => $topProducts,
        ])->layout('components.layouts.admin', ['title' => 'Dashboard']);
    }
}