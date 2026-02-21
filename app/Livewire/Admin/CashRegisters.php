<?php

namespace App\Livewire\Admin;

use App\Models\CashRegister;
use App\Models\Expense;
use Livewire\Component;
use Illuminate\Support\Facades\DB;

class CashRegisters extends Component
{
    public ?int $openRegisterId = null;

    // Apertura
    public bool   $showOpenModal    = false;
    public        $openingAmount    = 0;
    public        $openingAmountBrl = 0;
    public string $openingNotes     = '';

    // Cierre
    public bool   $showCloseModal    = false;
    public        $closingAmount     = 0;
    public        $closingAmountBrl  = 0;
    public string $closingNotes      = '';

    public array $closingSummary  = [];
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
        $this->openingAmount    = 0;
        $this->openingAmountBrl = 0;
        $this->openingNotes     = '';
        $this->showOpenModal    = true;
    }

    public function confirmOpen(): void
    {
        $this->validate([
            'openingAmount'    => 'required|numeric|min:0',
            'openingAmountBrl' => 'nullable|numeric|min:0',
        ], [
            'openingAmount.required' => 'Ingresá el monto inicial en Gs.',
            'openingAmount.min'      => 'El monto no puede ser negativo.',
            'openingAmountBrl.min'   => 'El monto en R$ no puede ser negativo.',
        ]);

        try {
            $register = CashRegister::open(
                openingAmount: (float) $this->openingAmount,
                openingAmountBrl: (float) ($this->openingAmountBrl ?: 0),
                notes: $this->openingNotes ?: null,
            );
            $this->openRegisterId = $register->id;
            
            // Resetear modal y campos (Alpine ya cerró visualmente)
            $this->showOpenModal = false;
            $this->openingAmount = 0;
            $this->openingAmountBrl = 0;
            $this->openingNotes = '';
            
            $this->dispatch('show-notification', ['type' => 'success', 'message' => '✓ Caja abierta correctamente.']);
        } catch (\Exception $e) {
            // Si hay error, Alpine ya cerró pero Livewire reabre el modal
            $this->showOpenModal = true;
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

        $this->closingSummary    = $this->buildClosingSummary($register);
        $this->closingAmount     = $this->closingSummary['expected_cash'];
        $this->closingAmountBrl  = $this->closingSummary['expected_brl'];
        $this->closingNotes      = '';
        $this->showCloseModal    = true;
    }

    public function confirmClose(): void
    {
        $this->validate([
            'closingAmount'    => 'required|numeric|min:0',
            'closingAmountBrl' => 'nullable|numeric|min:0',
        ], [
            'closingAmount.required' => 'Ingresá el monto contado en Gs.',
            'closingAmount.min'      => 'El monto no puede ser negativo.',
            'closingAmountBrl.min'   => 'El monto en R$ no puede ser negativo.',
        ]);

        $register = $this->getOpenRegister();
        if (!$register) {
            $this->showCloseModal = true; // Reabrir si hay error
            $this->dispatch('show-notification', ['type' => 'error', 'message' => 'No se encontró la caja abierta.']);
            return;
        }

        try {
            $register->close(
                closingAmount: (float) $this->closingAmount,
                closingAmountBrl: (float) ($this->closingAmountBrl ?: 0),
                notes: $this->closingNotes ?: null,
            );
            
            // Resetear estado (Alpine ya cerró visualmente)
            $this->openRegisterId = null;
            $this->closingSummary = [];
            $this->showCloseModal = false;
            $this->closingAmount = 0;
            $this->closingAmountBrl = 0;
            $this->closingNotes = '';
            
            $this->dispatch('show-notification', ['type' => 'success', 'message' => '✓ Caja cerrada correctamente.']);
        } catch (\Exception $e) {
            // Si hay error, Alpine ya cerró pero Livewire reabre el modal
            $this->showCloseModal = true;
            $this->dispatch('show-notification', ['type' => 'error', 'message' => $e->getMessage()]);
        }
    }

    // ==========================================
    // HELPERS
    // ==========================================

    private function getOpenRegister(): ?CashRegister
    {
        if (!$this->openRegisterId) return null;
        return CashRegister::find($this->openRegisterId);
    }

    private function buildClosingSummary(CashRegister $register): array
    {
        $data = $register->calculateSalesTotals();

        $expensesCash  = $register->expenses()->where('payment_method', 'cash')->sum('amount');
        $expensesTotal = $register->expenses()->sum('amount');
        $expectedCash  = $register->opening_amount + $data['cash'] - $expensesCash;
        $expectedBrl   = $register->opening_amount_brl + $data['foreignCount'];

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
            'opening_amount'    => (float) $register->opening_amount,
            'opening_brl'       => (float) $register->opening_amount_brl,
            'total_sales'       => $data['total'],
            'total_orders'      => $data['count'],
            'cash_sales'        => $data['cash'],
            'card_sales'        => $data['card'],
            'transfer_sales'    => $data['transfer'],
            'foreign_sales'     => $data['foreign'],
            'foreign_count'     => $data['foreignCount'],
            'expenses_cash'     => $expensesCash,
            'expenses_total'    => $expensesTotal,
            'expected_cash'     => $expectedCash,
            'expected_brl'      => $expectedBrl,
            'by_method'         => $byMethod,
            'duration'          => $register->duration,
        ];
    }

    public function loadMore(): void
    {
        $this->historyLimit += 10;
    }

    // ==========================================
    // RENDER
    // ==========================================

    public function render()
    {
        $openRegister = $this->getOpenRegister();

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