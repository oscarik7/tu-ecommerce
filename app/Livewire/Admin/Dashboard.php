<?php

namespace App\Livewire\Admin;

use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Livewire\Component;
use Illuminate\Support\Facades\DB;

class Dashboard extends Component
{
    public function render()
    {
        $totalOrders = Order::count();
        $pendingOrders = Order::where('status', 'pending')->count();
        $totalProducts = Product::where('is_active', true)->count();
        $totalCustomers = User::role('customer')->count();
        
        $todayRevenue = Order::whereDate('created_at', today())
            ->where('status', '!=', 'cancelled')
            ->sum('total');
        
        $monthRevenue = Order::whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->where('status', '!=', 'cancelled')
            ->sum('total');

        $recentOrders = Order::with(['user', 'items'])
            ->orderBy('created_at', 'desc')
            ->take(10)
            ->get();

        $topProducts = Product::withCount('orderItems')
            ->orderBy('order_items_count', 'desc')
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