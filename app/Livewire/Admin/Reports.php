<?php

namespace App\Livewire\Admin;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Expense;
use App\Models\CashRegister;
use Livewire\Component;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class Reports extends Component
{
    // ── Período principal ─────────────────────────────
    public string $dateFrom = '';
    public string $dateTo   = '';

    // ── Período de comparación ────────────────────────
    public bool   $showComparison  = false;
    public string $compareFrom     = '';
    public string $compareTo       = '';

    // ── Pestaña activa ────────────────────────────────
    public string $activeTab = 'overview'; // overview | sales | products | expenses | cash

    // ── Presets rápidos ───────────────────────────────
    public string $preset = 'this_month';

    // ==========================================
    // LIFECYCLE
    // ==========================================

    public function mount(): void
    {
        $this->applyPreset('this_month');
    }

    // ==========================================
    // PRESETS DE FECHA
    // ==========================================

    public function applyPreset(string $preset): void
    {
        $this->preset = $preset;

        [$this->dateFrom, $this->dateTo] = match($preset) {
            'today'        => [today()->format('Y-m-d'),                   today()->format('Y-m-d')],
            'yesterday'    => [today()->subDay()->format('Y-m-d'),          today()->subDay()->format('Y-m-d')],
            'this_week'    => [now()->startOfWeek()->format('Y-m-d'),       now()->endOfWeek()->format('Y-m-d')],
            'last_week'    => [now()->subWeek()->startOfWeek()->format('Y-m-d'), now()->subWeek()->endOfWeek()->format('Y-m-d')],
            'this_month'   => [now()->startOfMonth()->format('Y-m-d'),      now()->endOfMonth()->format('Y-m-d')],
            'last_month'   => [now()->subMonth()->startOfMonth()->format('Y-m-d'), now()->subMonth()->endOfMonth()->format('Y-m-d')],
            'this_year'    => [now()->startOfYear()->format('Y-m-d'),       now()->endOfYear()->format('Y-m-d')],
            default        => [$this->dateFrom, $this->dateTo],
        };

        // Auto-cargar período de comparación (mes anterior)
        if ($this->showComparison) {
            $this->loadComparisonPeriod();
        }
    }

    public function loadComparisonPeriod(): void
    {
        if (!$this->dateFrom || !$this->dateTo) return;

        $from = Carbon::parse($this->dateFrom);
        $to   = Carbon::parse($this->dateTo);
        $days = $from->diffInDays($to) + 1;

        $this->compareFrom = $from->copy()->subDays($days)->format('Y-m-d');
        $this->compareTo   = $from->copy()->subDay()->format('Y-m-d');
    }

    public function updatedShowComparison(): void
    {
        if ($this->showComparison) {
            $this->loadComparisonPeriod();
        }
    }

    // ==========================================
    // QUERY BASE
    // ==========================================

    private function baseOrderQuery(string $from = null, string $to = null)
    {
        $f = $from ?? $this->dateFrom;
        $t = $to   ?? $this->dateTo;

        return Order::where('status', '!=', 'cancelled')
            ->where('payment_status', 'paid')
            ->when($f, fn($q) => $q->whereDate('created_at', '>=', $f))
            ->when($t, fn($q) => $q->whereDate('created_at', '<=', $t));
    }

    private function baseExpenseQuery(string $from = null, string $to = null)
    {
        $f = $from ?? $this->dateFrom;
        $t = $to   ?? $this->dateTo;

        return Expense::when($f, fn($q) => $q->whereDate('expense_date', '>=', $f))
                      ->when($t, fn($q) => $q->whereDate('expense_date', '<=', $t));
    }

    // ==========================================
    // DATOS PRINCIPALES
    // ==========================================

    private function getOverviewData(): array
    {
        $orders   = $this->baseOrderQuery();
        $expenses = $this->baseExpenseQuery();

        $totalSales    = (float) $orders->sum('total');
        $totalExpenses = (float) $expenses->sum('amount');
        $totalOrders   = $orders->count();
        $totalCommissions = (float) $orders->whereNotNull('delivery_app_commission')->sum('delivery_app_commission');
        $netSales      = $totalSales - $totalCommissions;
        $netResult     = $netSales - $totalExpenses;
        $avgTicket     = $totalOrders > 0 ? $totalSales / $totalOrders : 0;

        // Comparación si está activa
        $comparison = null;
        if ($this->showComparison && $this->compareFrom) {
            $prevOrders   = $this->baseOrderQuery($this->compareFrom, $this->compareTo);
            $prevExpenses = $this->baseExpenseQuery($this->compareFrom, $this->compareTo);
            $prevSales    = (float) $prevOrders->sum('total');
            $prevExp      = (float) $prevExpenses->sum('amount');
            $prevOrders2  = $prevOrders->count();

            $comparison = [
                'sales'    => $prevSales,
                'expenses' => $prevExp,
                'orders'   => $prevOrders2,
                'sales_diff'   => $prevSales    > 0 ? (($totalSales    - $prevSales)    / $prevSales    * 100) : null,
                'expenses_diff'=> $prevExp      > 0 ? (($totalExpenses - $prevExp)      / $prevExp      * 100) : null,
                'orders_diff'  => $prevOrders2  > 0 ? (($totalOrders   - $prevOrders2)  / $prevOrders2  * 100) : null,
            ];
        }

        return compact('totalSales', 'totalExpenses', 'totalOrders', 'totalCommissions',
                       'netSales', 'netResult', 'avgTicket', 'comparison');
    }

    private function getSalesData(): array
    {
        // Ventas por día
        $salesByDay = $this->baseOrderQuery()
            ->select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('COUNT(*) as orders'),
                DB::raw('SUM(total) as total')
            )
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->map(fn($r) => [
                'date'   => Carbon::parse($r->date)->format('d/m'),
                'orders' => $r->orders,
                'total'  => (float) $r->total,
            ])
            ->toArray();

        // Por método de pago
        $byPayment = $this->baseOrderQuery()
            ->select('payment_method_id', DB::raw('COUNT(*) as orders'), DB::raw('SUM(total) as total'))
            ->groupBy('payment_method_id')
            ->with('paymentMethod')
            ->get()
            ->map(fn($r) => [
                'name'   => $r->paymentMethod->name ?? 'Sin método',
                'orders' => $r->orders,
                'total'  => (float) $r->total,
            ])
            ->toArray();

        // Por canal (tienda / web / delivery_app)
        $byChannel = $this->baseOrderQuery()
            ->select('source', DB::raw('COUNT(*) as orders'), DB::raw('SUM(total) as total'))
            ->groupBy('source')
            ->get()
            ->map(fn($r) => [
                'source' => match($r->source) {
                    'pos'          => '🏪 Tienda',
                    'web'          => '🌐 Web',
                    'delivery_app' => '🛵 Delivery App',
                    default        => $r->source ?? 'Otro',
                },
                'orders' => $r->orders,
                'total'  => (float) $r->total,
            ])
            ->toArray();

        // Por hora del día (para ver picos)
        $byHour = $this->baseOrderQuery()
            ->select(
                DB::raw('HOUR(created_at) as hour'),
                DB::raw('COUNT(*) as orders'),
                DB::raw('SUM(total) as total')
            )
            ->groupBy('hour')
            ->orderBy('hour')
            ->get()
            ->map(fn($r) => [
                'hour'   => str_pad($r->hour, 2, '0', STR_PAD_LEFT) . ':00',
                'orders' => $r->orders,
                'total'  => (float) $r->total,
            ])
            ->toArray();

        return compact('salesByDay', 'byPayment', 'byChannel', 'byHour');
    }

    private function getProductsData(): array
    {
        // Productos más vendidos (por unidades)
        $topByQty = OrderItem::whereHas('order', fn($q) =>
                $q->where('status', '!=', 'cancelled')
                  ->where('payment_status', 'paid')
                  ->when($this->dateFrom, fn($q2) => $q2->whereDate('created_at', '>=', $this->dateFrom))
                  ->when($this->dateTo,   fn($q2) => $q2->whereDate('created_at', '<=', $this->dateTo))
            )
            ->select(
                'product_name',
                DB::raw('SUM(quantity) as total_qty'),
                DB::raw('SUM(subtotal) as total_revenue'),
                DB::raw('COUNT(*) as order_count')
            )
            ->groupBy('product_name')
            ->orderByDesc('total_revenue')
            ->limit(10)
            ->get()
            ->toArray();

        // Ventas por categoría (via products)
        $byCategory = OrderItem::whereHas('order', fn($q) =>
                $q->where('status', '!=', 'cancelled')
                  ->where('payment_status', 'paid')
                  ->when($this->dateFrom, fn($q2) => $q2->whereDate('created_at', '>=', $this->dateFrom))
                  ->when($this->dateTo,   fn($q2) => $q2->whereDate('created_at', '<=', $this->dateTo))
            )
            ->join('products', 'order_items.product_id', '=', 'products.id')
            ->join('categories', 'products.category_id', '=', 'categories.id')
            ->select(
                'categories.name as category',
                DB::raw('SUM(order_items.subtotal) as total'),
                DB::raw('COUNT(*) as items')
            )
            ->groupBy('categories.name')
            ->orderByDesc('total')
            ->get()
            ->toArray();

        return compact('topByQty', 'byCategory');
    }

    private function getExpensesData(): array
    {
        // Por tipo
        $byType = $this->baseExpenseQuery()
            ->select('type', DB::raw('COUNT(*) as count'), DB::raw('SUM(amount) as total'))
            ->groupBy('type')
            ->get()
            ->map(fn($r) => [
                'type'  => match($r->type) {
                    'salary'      => '💰 Salarios',
                    'purchase'    => '🛒 Insumos',
                    'inventory'   => '📦 Stock',
                    'operational' => '🔧 Operacional',
                    'other'       => '📋 Otro',
                    default       => $r->type,
                },
                'count' => $r->count,
                'total' => (float) $r->total,
            ])
            ->toArray();

        // Por método de pago
        $byMethod = $this->baseExpenseQuery()
            ->select('payment_method', DB::raw('SUM(amount) as total'), DB::raw('COUNT(*) as count'))
            ->groupBy('payment_method')
            ->get()
            ->map(fn($r) => [
                'method' => match($r->payment_method) {
                    'cash'     => '💵 Efectivo',
                    'card'     => '💳 Tarjeta',
                    'transfer' => '🏦 Transfer',
                    default    => $r->payment_method,
                },
                'total' => (float) $r->total,
                'count' => $r->count,
            ])
            ->toArray();

        // Por día
        $byDay = $this->baseExpenseQuery()
            ->select(DB::raw('DATE(expense_date) as date'), DB::raw('SUM(amount) as total'), DB::raw('COUNT(*) as count'))
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->map(fn($r) => [
                'date'  => Carbon::parse($r->date)->format('d/m'),
                'total' => (float) $r->total,
                'count' => $r->count,
            ])
            ->toArray();

        $total = array_sum(array_column($byType, 'total'));

        return compact('byType', 'byMethod', 'byDay', 'total');
    }

    private function getCashData(): array
    {
        // Cajas cerradas en el período
        $registers = CashRegister::where('status', 'closed')
            ->when($this->dateFrom, fn($q) => $q->whereDate('opened_at', '>=', $this->dateFrom))
            ->when($this->dateTo,   fn($q) => $q->whereDate('opened_at', '<=', $this->dateTo))
            ->with(['opener', 'closer'])
            ->orderBy('opened_at', 'desc')
            ->get();

        $totalSales    = $registers->sum('total_sales');
        $totalExpenses = $registers->sum('total_expenses');
        $totalDiff     = $registers->sum('difference');
        $avgDiff       = $registers->count() > 0
            ? $registers->avg(fn($r) => abs($r->difference))
            : 0;

        $exactCount    = $registers->where('difference', 0)->count();
        $shortCount    = $registers->where('difference', '<', 0)->count();
        $overCount     = $registers->where('difference', '>', 0)->count();

        return compact(
            'registers', 'totalSales', 'totalExpenses',
            'totalDiff', 'avgDiff', 'exactCount', 'shortCount', 'overCount'
        );
    }

    // ==========================================
    // RENDER
    // ==========================================

    public function render()
    {
        $overview  = $this->getOverviewData();
        $sales     = $this->activeTab === 'sales'    || $this->activeTab === 'overview' ? $this->getSalesData()    : null;
        $products  = $this->activeTab === 'products'  ? $this->getProductsData()  : null;
        $expenses  = $this->activeTab === 'expenses'  ? $this->getExpensesData()  : null;
        $cashData  = $this->activeTab === 'cash'      ? $this->getCashData()      : null;

        // Para overview siempre cargamos datos de canal y método de pago
        $channelData = $sales ? $sales['byChannel']  : null;
        $paymentData = $sales ? $sales['byPayment']  : null;

        return view('livewire.admin.reports', [
            'overview'     => $overview,
            'sales'        => $sales,
            'products'     => $products,
            'expenses'     => $expenses,
            'cashData'     => $cashData,
            'channelData'  => $channelData,
            'paymentData'  => $paymentData,
        ])->layout('components.layouts.admin', ['title' => 'Reportes']);
    }
}