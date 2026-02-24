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
    // FIX 9: polling cada 60s para mantener datos frescos en un POS activo
    protected $listeners = [];

    public function render()
    {
        // ── Estadísticas generales ──────────────────────────────────────────
        $totalOrders   = Order::count();

        // FIX 2: incluir todos los estados activos, no solo 'pending'
        $pendingOrders = Order::whereIn('status', ['pending', 'confirmed', 'preparing', 'ready'])->count();
        $todayOrders   = Order::whereDate('created_at', today())->count();

        $totalCustomers = User::role('customer')->count();

        // ── Ingresos ────────────────────────────────────────────────────────
        // FIX 3: castear a float para evitar problemas de división
        $todayRevenue = (float) Order::whereDate('created_at', today())
            ->where('status', '!=', 'cancelled')
            ->where('payment_status', 'paid')
            ->sum('total');

        // FIX 7: ingresos de ayer para calcular tendencia
        $yesterdayRevenue = (float) Order::whereDate('created_at', today()->subDay())
            ->where('status', '!=', 'cancelled')
            ->where('payment_status', 'paid')
            ->sum('total');

        $todayTrend = $yesterdayRevenue > 0
            ? round((($todayRevenue - $yesterdayRevenue) / $yesterdayRevenue) * 100)
            : null;

        $monthRevenue = (float) Order::whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->where('status', '!=', 'cancelled')
            ->where('payment_status', 'paid')
            ->sum('total');

        // ── Egresos ─────────────────────────────────────────────────────────
        // FIX 1: separar egresos Gs y BRL — sum('amount') ignoraba los BRL
        $monthExpensesGs = (float) Expense::whereMonth('expense_date', now()->month)
            ->whereYear('expense_date', now()->year)
            ->where('currency', 'gs')
            ->sum('amount');

        $monthExpensesBrl = (float) Expense::whereMonth('expense_date', now()->month)
            ->whereYear('expense_date', now()->year)
            ->where('currency', 'brl')
            ->sum('amount_brl');

        // FIX 8: neto real del mes (solo en Gs — BRL es otra moneda)
        $monthNet = $monthRevenue - $monthExpensesGs;

        // ── Caja abierta ────────────────────────────────────────────────────
        $openRegister = CashRegister::getOpenRegister();

        // FIX 6: calcular ventas activas de la sesión de caja actual
        $registerLiveSales = null;
        if ($openRegister) {
            $registerLiveSales = (float) Order::where('cash_register_id', $openRegister->id)
                ->where('status', '!=', 'cancelled')
                ->where('payment_status', 'paid')
                ->sum('total');
        }

        // ── Órdenes recientes ────────────────────────────────────────────────
        $recentOrders = Order::with(['paymentMethod'])
            ->orderBy('created_at', 'desc')
            ->take(8)
            ->get();

        // ── TOP Productos (del mes actual) ───────────────────────────────────
        // FIX 4: filtrar por mes actual para mostrar tendencia relevante
        $topProducts = DB::table('order_items')
            ->join('products', 'order_items.product_id', '=', 'products.id')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->where('orders.status', '!=', 'cancelled')
            ->where('orders.payment_status', 'paid')
            ->whereMonth('orders.created_at', now()->month)
            ->whereYear('orders.created_at', now()->year)
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

        // ── Ventas de hoy por método de pago ────────────────────────────────
        $todayByPayment = Order::with('paymentMethod')
            ->whereDate('created_at', today())
            ->where('status', '!=', 'cancelled')
            ->where('payment_status', 'paid')
            ->select('payment_method_id', DB::raw('SUM(total) as total'), DB::raw('COUNT(*) as count'))
            ->groupBy('payment_method_id')
            ->get()
            ->map(fn($row) => [
                'name'  => $row->paymentMethod->name ?? 'Sin método',
                'total' => (float) $row->total,
                'count' => (int) $row->count,
            ]);

        // ── Ventas de hoy por canal ──────────────────────────────────────────
        // FIX 5: COALESCE convierte NULL → 'pos' antes de agrupar
        $todayBySource = Order::whereDate('created_at', today())
            ->where('status', '!=', 'cancelled')
            ->where('payment_status', 'paid')
            ->select(
                DB::raw("COALESCE(source, 'pos') as source"),
                DB::raw('SUM(total) as total'),
                DB::raw('COUNT(*) as count')
            )
            ->groupBy(DB::raw("COALESCE(source, 'pos')"))
            ->get();

        return view('livewire.admin.dashboard', [
            'totalOrders'         => $totalOrders,
            'pendingOrders'       => $pendingOrders,
            'todayOrders'         => $todayOrders,
            'totalCustomers'      => $totalCustomers,
            'todayRevenue'        => $todayRevenue,
            'yesterdayRevenue'    => $yesterdayRevenue,
            'todayTrend'          => $todayTrend,
            'monthRevenue'        => $monthRevenue,
            'monthExpensesGs'     => $monthExpensesGs,
            'monthExpensesBrl'    => $monthExpensesBrl,
            'monthNet'            => $monthNet,
            'openRegister'        => $openRegister,
            'registerLiveSales'   => $registerLiveSales,
            'recentOrders'        => $recentOrders,
            'topProducts'         => $topProducts,
            'todayByPayment'      => $todayByPayment,
            'todayBySource'       => $todayBySource,
        ])->layout('components.layouts.admin', ['title' => 'Dashboard']);
    }
}