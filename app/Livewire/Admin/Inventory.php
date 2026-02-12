<?php

namespace App\Livewire\Admin;

use App\Models\CupSize;
use App\Models\Expense;
use App\Models\CashRegister;
use Livewire\Component;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class Inventory extends Component
{
    // Modal de ajuste de stock
    public $showAdjustModal = false;
    public $selectedCupSize = null;

    // Campos del ajuste
    public $adjustType    = 'add';      // 'add' = entrada, 'subtract' = salida, 'set' = ajuste directo
    public $adjustQty     = '';
    public $adjustReason  = '';
    public $adjustCost    = '';         // Costo de compra (opcional, para registrar gasto)
    public $registerExpense = false;    // ¿Registrar como gasto?

    // Historial de movimientos (últimas 50 entradas del log)
    public $showHistory = false;

    // Tipos de razón predefinidos para el dropdown
    const REASONS_ADD = [
        'Compra de stock',
        'Devolución de cliente',
        'Ajuste de inventario',
        'Otro',
    ];

    const REASONS_SUBTRACT = [
        'Vasitos rotos/dañados',
        'Ajuste de inventario',
        'Uso interno',
        'Otro',
    ];

    protected function rules(): array
    {
        $rules = [
            'adjustType'   => 'required|in:add,subtract,set',
            'adjustQty'    => 'required|integer|min:1',
            'adjustReason' => 'required|string|max:255',
        ];

        if ($this->registerExpense && $this->adjustType === 'add') {
            $rules['adjustCost'] = 'required|numeric|min:1';
        }

        return $rules;
    }

    protected $messages = [
        'adjustQty.required'    => 'Ingresá la cantidad.',
        'adjustQty.min'         => 'La cantidad debe ser mayor a 0.',
        'adjustReason.required' => 'Indicá el motivo del ajuste.',
        'adjustCost.required'   => 'Ingresá el costo de la compra.',
    ];

    // ==========================================
    // ABRIR MODAL DE AJUSTE
    // ==========================================

    public function openAdjust(int $cupSizeId, string $type = 'add'): void
    {
        $this->selectedCupSize = CupSize::findOrFail($cupSizeId);
        $this->adjustType      = $type;
        $this->adjustQty       = '';
        $this->adjustReason    = '';
        $this->adjustCost      = '';
        $this->registerExpense = false;
        $this->resetErrorBag();
        $this->showAdjustModal = true;
    }

    public function closeAdjust(): void
    {
        $this->showAdjustModal = false;
        $this->selectedCupSize = null;
        $this->resetErrorBag();
    }

    // ==========================================
    // GUARDAR AJUSTE
    // ==========================================

    public function saveAdjust(): void
    {
        $this->validate();

        try {
            DB::beginTransaction();

            $cupSize  = CupSize::lockForUpdate()->findOrFail($this->selectedCupSize->id);
            $qty      = (int) $this->adjustQty;
            $oldStock = $cupSize->stock;

            match($this->adjustType) {
                'add'      => $cupSize->increment('stock', $qty),
                'subtract' => $this->doSubtract($cupSize, $qty),
                'set'      => $cupSize->update(['stock' => $qty]),
            };

            $cupSize->refresh();
            $newStock = $cupSize->stock;

            // Registrar como gasto si corresponde (solo en entradas con costo)
            if ($this->registerExpense && $this->adjustType === 'add' && $this->adjustCost > 0) {
                $openRegister = CashRegister::getOpenRegister();

                Expense::create([
                    'cash_register_id' => $openRegister?->id,
                    'registered_by'    => auth()->id(),
                    'type'             => 'inventory',
                    'description'      => "Compra de vasitos {$cupSize->name} ({$qty} unidades)",
                    'amount'           => $this->adjustCost,
                    'payment_method'   => 'cash',
                    'expense_date'     => now(),
                    'notes'            => $this->adjustReason,
                ]);
            }

            DB::commit();

            $diff   = $newStock - $oldStock;
            $sign   = $diff >= 0 ? '+' : '';
            $action = match($this->adjustType) {
                'add'      => "Se agregaron {$qty} vasitos",
                'subtract' => "Se descontaron {$qty} vasitos",
                'set'      => "Stock ajustado a {$qty}",
            };

            session()->flash('message', "{$action} de {$cupSize->name}. Stock: {$oldStock} → {$newStock}");
            $this->closeAdjust();

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error ajuste inventario: ' . $e->getMessage());
            session()->flash('error', 'Error al ajustar: ' . $e->getMessage());
        }
    }

    private function doSubtract(CupSize $cupSize, int $qty): void
    {
        if ($cupSize->stock < $qty) {
            throw new \Exception(
                "Stock insuficiente. Disponible: {$cupSize->stock}, intentás descontar: {$qty}"
            );
        }
        $cupSize->decrement('stock', $qty);
    }

    // ==========================================
    // ACTUALIZAR STOCK MÍNIMO
    // ==========================================

    public function updateMinStock(int $cupSizeId, int $value): void
    {
        if ($value < 0) return;

        CupSize::findOrFail($cupSizeId)->update(['stock_min' => $value]);
        session()->flash('message', 'Stock mínimo actualizado.');
    }

    // ==========================================
    // TOGGLE ACTIVO/INACTIVO
    // ==========================================

    public function toggleActive(int $cupSizeId): void
    {
        $cup = CupSize::findOrFail($cupSizeId);
        $cup->update(['is_active' => !$cup->is_active]);
        session()->flash('message', "Vasito {$cup->name} " . ($cup->is_active ? 'activado' : 'desactivado') . '.');
    }

    // ==========================================
    // PROPIEDADES COMPUTADAS
    // ==========================================

    public function getStatsProperty(): array
    {
        $cups = CupSize::all();

        return [
            'total_types'   => $cups->count(),
            'total_vasitos' => $cups->sum('stock'),
            'low_stock'     => $cups->filter(fn($c) => $c->is_low_stock)->count(),
            'out_of_stock'  => $cups->where('stock', 0)->count(),
        ];
    }

    // ==========================================
    // RENDER
    // ==========================================

    public function render()
    {
        $cupSizes = CupSize::orderBy('volume_ml')->get();

        // Últimos gastos de inventario para mostrar en historial
        $recentExpenses = Expense::with('registeredBy')
            ->where('type', 'inventory')
            ->orderByDesc('expense_date')
            ->limit(20)
            ->get();

        return view('livewire.admin.inventory', [
            'cupSizes'       => $cupSizes,
            'recentExpenses' => $recentExpenses,
            'stats'          => $this->stats,
            'reasonsAdd'     => self::REASONS_ADD,
            'reasonsSubtract'=> self::REASONS_SUBTRACT,
        ])->layout('components.layouts.admin', ['title' => 'Inventario de Vasitos']);
    }
}