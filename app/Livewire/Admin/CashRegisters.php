<?php

namespace App\Livewire\Admin;

use App\Models\CashRegister;
use App\Models\Expense;
use Livewire\Component;
use Illuminate\Support\Facades\DB;

class CashRegisters extends Component
{
    // ── ID de la caja abierta (nunca guardamos el modelo) ─
    public ?int $openRegisterId = null;

    // ── Formulario de apertura ────────────────────────
    public bool   $showOpenModal  = false;
    public        $openingAmount  = 0;
    public string $openingNotes   = '';

    // ── Formulario de cierre ──────────────────────────
    public bool   $showCloseModal = false;
    public        $closingAmount  = 0;
    public string $closingNotes   = '';

    // ── Resumen pre-calculado para el modal de cierre ─
    public array $closingSummary  = [];

    // ── Historial ─────────────────────────────────────
    public int $historyLimit = 10;

    // ==========================================
    // LIFECYCLE
    // ==========================================

    public function mount(): void
    {
        $register = CashRegister::getOpenRegister();
        $this->openRegisterId = $register?->id;
    }

    // ==========================================
    // APERTURA
    // ==========================================

    public function openOpenModal(): void
    {
        if ($this->openRegisterId) {
            $this->dispatch('show-notification', ['type' => 'error', 'message' => 'Ya hay una caja abierta.']);
            return;
        }
        $this->openingAmount = 0;
        $this->openingNotes  = '';
        $this->showOpenModal = true;
    }

    public function confirmOpen(): void
    {
        $this->validate([
            'openingAmount' => 'required|numeric|min:0',
        ], [
            'openingAmount.required' => 'Ingresá el monto inicial.',
            'openingAmount.min'      => 'El monto no puede ser negativo.',
        ]);

        try {
            $register = CashRegister::open(
                openingAmount: (float) $this->openingAmount,
                notes: $this->openingNotes ?: null,
            );
            $this->openRegisterId = $register->id;
            $this->showOpenModal  = false;
            $this->dispatch('show-notification', ['type' => 'success', 'message' => '✓ Caja abierta correctamente.']);
        } catch (\Exception $e) {
            $this->dispatch('show-notification', ['type' => 'error', 'message' => $e->getMessage()]);
        }
    }

    // ==========================================
    // CIERRE
    // ==========================================

    public function openCloseModal(): void
    {
        $register = $this->getOpenRegister();
        if (!$register) {
            $this->dispatch('show-notification', ['type' => 'error', 'message' => 'No hay caja abierta.']);
            return;
        }

        $this->closingSummary = $this->buildClosingSummary($register);
        $this->closingAmount  = $this->closingSummary['expected_cash'];
        $this->closingNotes   = '';
        $this->showCloseModal = true;
    }

    public function confirmClose(): void
    {
        $this->validate([
            'closingAmount' => 'required|numeric|min:0',
        ], [
            'closingAmount.required' => 'Ingresá el monto contado.',
            'closingAmount.min'      => 'El monto no puede ser negativo.',
        ]);

        $register = $this->getOpenRegister();
        if (!$register) {
            $this->dispatch('show-notification', ['type' => 'error', 'message' => 'No se encontró la caja abierta.']);
            return;
        }

        try {
            $register->close(
                closingAmount: (float) $this->closingAmount,
                notes: $this->closingNotes ?: null,
            );
            $this->openRegisterId = null;
            $this->closingSummary = [];
            $this->showCloseModal = false;
            $this->dispatch('show-notification', ['type' => 'success', 'message' => '✓ Caja cerrada correctamente.']);
        } catch (\Exception $e) {
            $this->dispatch('show-notification', ['type' => 'error', 'message' => $e->getMessage()]);
        }
    }

    // ==========================================
    // HELPER: resolver modelo fresco
    // ==========================================

    private function getOpenRegister(): ?CashRegister
    {
        if (!$this->openRegisterId) return null;
        return CashRegister::find($this->openRegisterId);
    }

    // ==========================================
    // CÁLCULO DE RESUMEN (cierre)
    // ==========================================

    private function buildClosingSummary(CashRegister $register): array
    {
        $data = $register->calculateSalesTotals();

        $expensesCash  = $register->expenses()->where('payment_method', 'cash')->sum('amount');
        $expensesTotal = $register->expenses()->sum('amount');
        $expectedCash  = $register->opening_amount + $data['cash'] - $expensesCash;

        $byMethod = $register->orders()
            ->where('status', '!=', 'cancelled')
            ->where('payment_status', 'paid')
            ->with('paymentMethod')
            ->get()
            ->groupBy(fn($o) => $o->paymentMethod->name ?? 'Otro')
            ->map(fn($group) => [
                'count'  => $group->count(),
                'amount' => $group->sum('total'),
            ])
            ->toArray();

        return [
            'opening_amount' => (float) $register->opening_amount,
            'total_sales'    => $data['total'],
            'total_orders'   => $data['count'],
            'cash_sales'     => $data['cash'],
            'card_sales'     => $data['card'],
            'transfer_sales' => $data['transfer'],
            'expenses_cash'  => $expensesCash,
            'expenses_total' => $expensesTotal,
            'expected_cash'  => $expectedCash,
            'by_method'      => $byMethod,
            'duration'       => $register->duration,
        ];
    }

    // ==========================================
    // HISTORIAL
    // ==========================================

    public function loadMore(): void
    {
        $this->historyLimit += 10;
    }

    // ==========================================
    // RENDER
    // ==========================================

    public function render()
    {
        // Resolver el modelo fresco en cada render
        $openRegister = $this->getOpenRegister();

        // Si no hay caja por ID, buscar si hay una abierta (por si se abrió en otra tab)
        if (!$openRegister) {
            $openRegister = CashRegister::getOpenRegister();
            $this->openRegisterId = $openRegister?->id;
        }

        $history = CashRegister::with(['opener', 'closer'])
            ->orderBy('opened_at', 'desc')
            ->limit($this->historyLimit)
            ->get();

        $monthStats = CashRegister::where('status', 'closed')
            ->whereMonth('opened_at', now()->month)
            ->whereYear('opened_at', now()->year)
            ->selectRaw('
                COUNT(*) as total_registers,
                SUM(total_sales) as total_sales,
                SUM(total_expenses) as total_expenses,
                AVG(ABS(difference)) as avg_difference
            ')
            ->first();

        return view('livewire.admin.cash-registers', [
            'openRegister' => $openRegister,
            'history'      => $history,
            'monthStats'   => $monthStats,
        ])->layout('components.layouts.admin', ['title' => 'Caja']);
    }
}