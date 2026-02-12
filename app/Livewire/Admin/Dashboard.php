<?php

namespace App\Livewire\Admin;

use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Models\CashRegister;
use App\Models\Expense;
use Livewire\Component;
use Illuminate\Support\Facades\DB;

class Dashboard extends Component
{
    public function render()
    {
        // ── Estadísticas generales ────────────────────
        $totalOrders    = Order::count();
        $pendingOrders  = Order::where('status', 'pending')->count();
        $totalCustomers = User::role('customer')->count();

        // ── Ingresos ──────────────────────────────────
        $todayRevenue = Order::whereDate('created_at', today())
            ->where('status', '!=', 'cancelled')
            ->where('payment_status', 'paid')
            ->sum('total');

        $monthRevenue = Order::whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->where('status', '!=', 'cancelled')
            ->where('payment_status', 'paid')
            ->sum('total');

        // ── Egresos del mes ───────────────────────────
        $monthExpenses = Expense::whereMonth('expense_date', now()->month)
            ->whereYear('expense_date', now()->year)
            ->sum('amount');

        // ── Caja abierta ──────────────────────────────
        $openRegister = CashRegister::getOpenRegister();

        // ── Órdenes recientes ─────────────────────────
        $recentOrders = Order::with(['paymentMethod'])
            ->orderBy('created_at', 'desc')
            ->take(8)
            ->get();

        // ── TOP Productos (agrupado solo por product_id) ──
        // Sin join a product_variants para evitar null si el item
        // es por peso (product_variant_id puede ser null)
        $topProducts = DB::table('order_items')
            ->join('products', 'order_items.product_id', '=', 'products.id')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->where('orders.status', '!=', 'cancelled')
            ->where('orders.payment_status', 'paid')
            ->select(
                'products.id',
                'products.name',
                DB::raw('SUM(order_items.quantity) as total_qty'),
                DB::raw('SUM(order_items.subtotal) as total_revenue')
            )
            ->groupBy('products.id', 'products.name')
            ->orderByDesc('total_revenue')
            ->take(5)
            ->get();

        return view('livewire.admin.dashboard', [
            'totalOrders'    => $totalOrders,
            'pendingOrders'  => $pendingOrders,
            'totalCustomers' => $totalCustomers,
            'todayRevenue'   => $todayRevenue,
            'monthRevenue'   => $monthRevenue,
            'monthExpenses'  => $monthExpenses,
            'openRegister'   => $openRegister,
            'recentOrders'   => $recentOrders,
            'topProducts'    => $topProducts,
        ])->layout('components.layouts.admin', ['title' => 'Dashboard']);
    }
}
