<?php

namespace App\Livewire\Admin;

use App\Models\CashRegister;
use Livewire\Component;
use Livewire\Attributes\Computed;

class CashRegisters extends Component
{
    // ══════════════════════════════════════════════════════════════════════════
    // ESTADO
    // ══════════════════════════════════════════════════════════════════════════

    public ?int $openRegisterId = null;

    // Apertura
    public bool   $showOpenModal    = false;
    public        $openingAmount    = 0;
    public        $openingAmountBrl = 0;
    public string $openingNotes     = '';

    // Cierre
    public bool   $showCloseModal   = false;
    public        $closingAmount    = 0;
    public        $closingAmountBrl = 0.0;
    public string $closingNotes     = '';

    public array $closingSummary = [];

    // Historial
    public int $historyLimit = 10;

    // ══════════════════════════════════════════════════════════════════════════
    // LIFECYCLE
    // ══════════════════════════════════════════════════════════════════════════

    public function mount(): void
    {
        $register = CashRegister::getOpenRegister();
        $this->openRegisterId = $register?->id;
    }

    // ══════════════════════════════════════════════════════════════════════════
    // APERTURA
    // ══════════════════════════════════════════════════════════════════════════

    public function openOpenModal(): void
    {
        if ($this->openRegisterId) {
            $this->notify('error', 'Ya hay una caja abierta.');
            return;
        }

        $this->openingAmount    = 0;
        $this->openingAmountBrl = 0;
        $this->openingNotes     = '';
        $this->resetErrorBag();
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
                openingAmount:    (float) $this->openingAmount,
                openingAmountBrl: (float) ($this->openingAmountBrl ?: 0),
                notes:            $this->openingNotes ?: null,
            );

            $this->openRegisterId   = $register->id;
            $this->showOpenModal    = false;
            $this->openingAmount    = 0;
            $this->openingAmountBrl = 0;
            $this->openingNotes     = '';
            $this->resetErrorBag();
            $this->notify('success', '✓ Caja abierta correctamente.');

        } catch (\Exception $e) {
            $this->notify('error', $e->getMessage());
        }
    }

    // ══════════════════════════════════════════════════════════════════════════
    // CIERRE
    // ══════════════════════════════════════════════════════════════════════════

    public function openCloseModal(): void
    {
        $register = $this->getOpenRegister();
        if (!$register) {
            $this->notify('error', 'No hay caja abierta.');
            return;
        }

        // buildClosingSummary() vive en el MODELO CashRegister, no en este Livewire
        $summary = $register->buildClosingSummary();

        $this->closingAmount    = (int) round($summary['expected_cash']);
        $this->closingAmountBrl = round((float) ($summary['expected_brl'] ?? 0), 2);
        $this->closingNotes     = '';
        $this->closingSummary   = $summary;

        $this->resetErrorBag();
        $this->showCloseModal = true;
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
            $this->notify('error', 'No se encontró la caja abierta.');
            return;
        }

        try {
            // close() vive en el MODELO CashRegister
            $register->close(
                closingAmount:    (float) $this->closingAmount,
                closingAmountBrl: (float) ($this->closingAmountBrl ?: 0),
                notes:            $this->closingNotes ?: null,
            );

            $this->openRegisterId   = null;
            $this->showCloseModal   = false;
            $this->closingSummary   = [];
            $this->closingAmount    = 0;
            $this->closingAmountBrl = 0.0;
            $this->closingNotes     = '';
            $this->resetErrorBag();
            $this->notify('success', '✓ Caja cerrada correctamente.');

        } catch (\Exception $e) {
            $this->notify('error', $e->getMessage());
        }
    }

    // ══════════════════════════════════════════════════════════════════════════
    // HISTORIAL
    // ══════════════════════════════════════════════════════════════════════════

    public function loadMore(): void
    {
        $this->historyLimit += 10;
    }

    // ══════════════════════════════════════════════════════════════════════════
    // HELPERS
    // ══════════════════════════════════════════════════════════════════════════

    private function getOpenRegister(): ?CashRegister
    {
        if ($this->openRegisterId) {
            $register = CashRegister::where('id', $this->openRegisterId)
                ->where('status', 'open')
                ->first();
            if ($register) return $register;
        }

        $register = CashRegister::getOpenRegister();
        $this->openRegisterId = $register?->id;
        return $register;
    }

    private function notify(string $type, string $message): void
    {
        $this->dispatch('show-notification', ['type' => $type, 'message' => $message]);
    }

    // ══════════════════════════════════════════════════════════════════════════
    // RENDER
    // ══════════════════════════════════════════════════════════════════════════

    public function render()
    {
        $openRegister = $this->getOpenRegister();

        $history = CashRegister::with(['opener', 'closer'])
            ->orderBy('opened_at', 'desc')
            ->limit($this->historyLimit)
            ->get();

        $monthStats = CashRegister::closed()
            ->whereMonth('opened_at', now()->month)
            ->whereYear('opened_at', now()->year)
            ->selectRaw('
                COUNT(*) as total_registers,
                COALESCE(SUM(total_sales), 0) as total_sales,
                COALESCE(SUM(total_expenses), 0) as total_expenses,
                COALESCE(SUM(total_sales), 0) - COALESCE(SUM(total_expenses), 0) as net_result,
                COALESCE(SUM(difference), 0) as total_difference,
                COALESCE(SUM(CASE WHEN ABS(difference) < 1 THEN 1 ELSE 0 END), 0) as exact_closes,
                COALESCE(MIN(difference), 0) as worst_shortage,
                COALESCE(MAX(difference), 0) as best_surplus
            ')
            ->first();

        return view('livewire.admin.cash-registers', [
            'openRegister' => $openRegister,
            'history'      => $history,
            'monthStats'   => $monthStats,
        ])->layout('components.layouts.admin', ['title' => 'Caja']);
    }
}