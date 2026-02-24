<?php

namespace App\Livewire\Admin;

use App\Models\CashRegister;
use App\Models\Expense;
use Livewire\Component;
use Livewire\WithPagination;

class Expenses extends Component
{
    use WithPagination;

    // ── Caja activa ───────────────────────────────────────
    public ?int $openRegisterId = null;

    // ── Formulario nuevo egreso ───────────────────────────
    public bool   $showModal     = false;
    public string $type          = 'operational';
    public string $description   = '';
    public        $amount        = 0;       // Gs — sin type hint para evitar hydration errors
    public        $amountBrl     = 0;       // R$ — sin type hint
    public string $currency      = 'gs';    // 'gs' | 'brl'
    public string $paymentMethod = 'cash';
    public string $notes         = '';

    // ── Filtros ───────────────────────────────────────────
    public string $filterType     = '';
    public string $filterCurrency = '';     // '' | 'gs' | 'brl'
    public string $filterDateFrom = '';
    public string $filterDateTo   = '';
    public string $search         = '';

    // ── Edición ───────────────────────────────────────────
    public ?int $editingId = null;

    // ── Tipos predefinidos ────────────────────────────────
    const TYPES = [
        'operational' => [
            'label'    => '🔧 Gasto Operacional',
            'color'    => 'orange',
            'examples' => ['Electricidad', 'Agua', 'Gas', 'Internet', 'Alquiler', 'Limpieza'],
        ],
        'purchase' => [
            'label'    => '🛒 Compra de Insumos',
            'color'    => 'green',
            'examples' => ['Frutas y verduras', 'Açaí', 'Leche de coco', 'Granola', 'Packaging'],
        ],
        'inventory' => [
            'label'    => '📦 Compra de Stock (Vasitos)',
            'color'    => 'yellow',
            'examples' => ['Vasitos 300ml', 'Vasitos 500ml', 'Vasitos 1L', 'Tapas', 'Cucharas'],
        ],
        'salary' => [
            'label'    => '💰 Pago de Salario',
            'color'    => 'blue',
            'examples' => ['Salario quincenal', 'Salario mensual', 'Adelanto de sueldo', 'Horas extra'],
        ],
        'other' => [
            'label'    => '📋 Otro',
            'color'    => 'gray',
            'examples' => ['Transporte', 'Herramientas', 'Reparación', 'Publicidad'],
        ],
    ];

    // ══════════════════════════════════════════════════════
    // LIFECYCLE
    // ══════════════════════════════════════════════════════

    public function mount(): void
    {
        $register = CashRegister::getOpenRegister();
        $this->openRegisterId = $register?->id;
        $this->filterDateFrom = today()->format('Y-m-d');
        $this->filterDateTo   = today()->format('Y-m-d');
    }

    public function updatedType(string $value): void
    {
        if (!array_key_exists($value, self::TYPES)) {
            $this->type = 'operational';
        }
    }

    /**
     * Cuando cambia la moneda, resetear el monto de la moneda que no aplica.
     * Esto evita que queden valores "fantasma" que confundan la validación.
     */
    public function updatedCurrency(string $value): void
    {
        if ($value === 'gs') {
            $this->amountBrl = 0;
        } else {
            $this->amount = 0;
        }
        $this->resetValidation(['amount', 'amountBrl']);
    }

    // ══════════════════════════════════════════════════════
    // MODAL
    // ══════════════════════════════════════════════════════

    public function openModal(): void
    {
        $this->resetForm();
        $this->editingId = null;
        $this->showModal = true;
    }

    public function editExpense(int $id): void
    {
        $expense = Expense::findOrFail($id);

        $this->editingId     = $id;
        $this->type          = $expense->type;
        $this->description   = $expense->description;
        $this->currency      = $expense->currency ?? 'gs';
        $this->amount        = $this->currency === 'gs'  ? (float) $expense->amount     : 0;
        $this->amountBrl     = $this->currency === 'brl' ? (float) $expense->amount_brl : 0;
        $this->paymentMethod = $expense->payment_method;
        $this->notes         = $expense->notes ?? '';
        $this->showModal     = true;
    }

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->resetForm();
    }

    private function resetForm(): void
    {
        $this->type          = 'operational';
        $this->description   = '';
        $this->amount        = 0;
        $this->amountBrl     = 0;
        $this->currency      = 'gs';
        $this->paymentMethod = 'cash';
        $this->notes         = '';
        $this->editingId     = null;
        $this->resetValidation();
    }

    // ══════════════════════════════════════════════════════
    // GUARDAR
    // ══════════════════════════════════════════════════════

    public function save(): void
    {
        // Validación dinámica según moneda seleccionada
        $rules = [
            'type'          => 'required|in:operational,purchase,inventory,salary,other',
            'description'   => 'required|string|min:3|max:200',
            'paymentMethod' => 'required|in:cash,card,transfer',
            'currency'      => 'required|in:gs,brl',
        ];

        if ($this->currency === 'gs') {
            $rules['amount']    = 'required|numeric|min:1';
            $rules['amountBrl'] = 'nullable|numeric|min:0';
        } else {
            $rules['amount']    = 'nullable|numeric|min:0';
            $rules['amountBrl'] = 'required|numeric|min:0.01';
        }

        $this->validate($rules, [
            'description.required'  => 'Describí el egreso.',
            'description.min'       => 'Descripción demasiado corta.',
            'amount.required'       => 'Ingresá el monto en Gs.',
            'amount.min'            => 'El monto en Gs debe ser mayor a 0.',
            'amountBrl.required'    => 'Ingresá el monto en R$.',
            'amountBrl.min'         => 'El monto en R$ debe ser mayor a 0.',
        ]);

        $data = [
            'cash_register_id' => $this->openRegisterId,
            'registered_by'    => auth()->id(),
            'type'             => $this->type,
            'description'      => $this->description,
            'currency'         => $this->currency,
            'amount'           => $this->currency === 'gs'  ? (float) $this->amount    : 0,
            'amount_brl'       => $this->currency === 'brl' ? (float) $this->amountBrl : 0,
            'payment_method'   => $this->paymentMethod,
            'notes'            => $this->notes ?: null,
            'expense_date'     => now(),
        ];

        if ($this->editingId) {
            Expense::findOrFail($this->editingId)->update($data);
            $msg = '✓ Egreso actualizado.';
        } else {
            Expense::create($data);
            $msg = '✓ Egreso registrado.';
        }

        $this->closeModal();
        $this->dispatch('show-notification', ['type' => 'success', 'message' => $msg]);
    }

    // ══════════════════════════════════════════════════════
    // ELIMINAR
    // ══════════════════════════════════════════════════════

    public function delete(int $id): void
    {
        $expense = Expense::findOrFail($id);

        if ($expense->cash_register_id && $expense->cashRegister?->status === 'closed') {
            $this->dispatch('show-notification', [
                'type'    => 'error',
                'message' => 'No se puede eliminar un egreso de una caja ya cerrada.',
            ]);
            return;
        }

        $expense->delete();
        $this->dispatch('show-notification', ['type' => 'success', 'message' => '✓ Egreso eliminado.']);
    }

    // ══════════════════════════════════════════════════════
    // FILTROS
    // ══════════════════════════════════════════════════════

    public function clearFilters(): void
    {
        $this->filterType     = '';
        $this->filterCurrency = '';
        $this->filterDateFrom = today()->format('Y-m-d');
        $this->filterDateTo   = today()->format('Y-m-d');
        $this->search         = '';
        $this->resetPage();
    }

    public function showAllDates(): void
    {
        $this->filterDateFrom = '';
        $this->filterDateTo   = '';
        $this->resetPage();
    }

    // ══════════════════════════════════════════════════════
    // STATS COMPUTADAS
    // ══════════════════════════════════════════════════════

    public function getStatsProperty(): array
    {
        $base = Expense::query();

        if ($this->filterDateFrom) $base->whereDate('expense_date', '>=', $this->filterDateFrom);
        if ($this->filterDateTo)   $base->whereDate('expense_date', '<=', $this->filterDateTo);

        // Stats en Gs (solo egresos con currency='gs')
        $baseGs = (clone $base)->where('currency', 'gs');

        // Stats en BRL (solo egresos con currency='brl')
        $baseBrl = (clone $base)->where('currency', 'brl');

        return [
            // Gs
            'total'       => (float) (clone $baseGs)->sum('amount'),
            'cash'        => (float) (clone $baseGs)->where('payment_method', 'cash')->sum('amount'),
            'operational' => (float) (clone $baseGs)->where('type', 'operational')->sum('amount'),
            'purchase'    => (float) (clone $baseGs)->where('type', 'purchase')->sum('amount'),
            'inventory'   => (float) (clone $baseGs)->where('type', 'inventory')->sum('amount'),
            'salary'      => (float) (clone $baseGs)->where('type', 'salary')->sum('amount'),
            'count'       => (int)   (clone $base)->count(),
            // BRL (NUEVO)
            'total_brl'   => (float) (clone $baseBrl)->sum('amount_brl'),
            'count_brl'   => (int)   (clone $baseBrl)->count(),
        ];
    }

    // ══════════════════════════════════════════════════════
    // RENDER
    // ══════════════════════════════════════════════════════

    public function render()
    {
        $expenses = Expense::with(['cashRegister', 'registeredBy'])
            ->when($this->filterType,     fn($q) => $q->where('type', $this->filterType))
            ->when($this->filterCurrency, fn($q) => $q->where('currency', $this->filterCurrency))
            ->when($this->filterDateFrom, fn($q) => $q->whereDate('expense_date', '>=', $this->filterDateFrom))
            ->when($this->filterDateTo,   fn($q) => $q->whereDate('expense_date', '<=', $this->filterDateTo))
            ->when($this->search,         fn($q) => $q->where('description', 'like', '%' . $this->search . '%'))
            ->orderBy('expense_date', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        $openRegister = $this->openRegisterId
            ? CashRegister::find($this->openRegisterId)
            : CashRegister::getOpenRegister();

        $this->openRegisterId = $openRegister?->id;

        return view('livewire.admin.expenses', [
            'expenses'     => $expenses,
            'stats'        => $this->stats,
            'types'        => self::TYPES,
            'openRegister' => $openRegister,
        ])->layout('components.layouts.admin', ['title' => 'Egresos']);
    }
}