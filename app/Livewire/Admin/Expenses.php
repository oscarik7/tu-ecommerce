<?php

namespace App\Livewire\Admin;

use App\Models\CashRegister;
use App\Models\Expense;
use Livewire\Component;
use Livewire\WithPagination;

class Expenses extends Component
{
    use WithPagination;

    // ── Caja activa (guardamos solo el ID, no el modelo) ─────
    public ?int $openRegisterId = null;

    // ── Formulario nuevo egreso ───────────────────────
    public bool   $showModal     = false;
    public string $type          = 'operational';
    public string $description   = '';
    public        $amount        = 0;       // sin type hint float para evitar errores de hydration
    public string $paymentMethod = 'cash';
    public string $notes         = '';

    // ── Filtros ───────────────────────────────────────
    public string $filterType     = '';
    public string $filterDateFrom = '';
    public string $filterDateTo   = '';
    public string $search         = '';

    // ── Edición ───────────────────────────────────────
    public ?int $editingId = null;

    // Tipos predefinidos con etiquetas y descripciones sugeridas
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

    // ==========================================
    // LIFECYCLE
    // ==========================================

    public function mount(): void
    {
        $register = CashRegister::getOpenRegister();
        $this->openRegisterId = $register?->id;
        $this->filterDateFrom = today()->format('Y-m-d');
        $this->filterDateTo   = today()->format('Y-m-d');
    }

    // Cuando cambia el tipo, limpiar la descripción si estaba vacía
    // y asegurarse que siempre sea un tipo válido
    public function updatedType(string $value): void
    {
        if (!array_key_exists($value, self::TYPES)) {
            $this->type = 'operational';
        }
    }

    // ==========================================
    // MODAL
    // ==========================================

    public function openModal(): void
    {
        $this->resetForm();
        $this->editingId  = null;
        $this->showModal  = true;
    }

    public function editExpense(int $id): void
    {
        $expense = Expense::findOrFail($id);
        $this->editingId     = $id;
        $this->type          = $expense->type;
        $this->description   = $expense->description;
        $this->amount        = (float) $expense->amount;
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
        $this->paymentMethod = 'cash';
        $this->notes         = '';
        $this->editingId     = null;
        $this->resetValidation();
    }

    // ==========================================
    // GUARDAR
    // ==========================================

    public function save(): void
    {
        $this->validate([
            'type'          => 'required|in:operational,purchase,inventory,salary,other',
            'description'   => 'required|string|min:3|max:200',
            'amount'        => 'required|numeric|min:1',
            'paymentMethod' => 'required|in:cash,card,transfer',
        ], [
            'description.required' => 'Describí el egreso.',
            'description.min'      => 'Descripción demasiado corta.',
            'amount.required'      => 'Ingresá el monto.',
            'amount.min'           => 'El monto debe ser mayor a 0.',
        ]);

        $data = [
            'cash_register_id' => $this->openRegisterId,
            'registered_by'    => auth()->id(),
            'type'             => $this->type,
            'description'      => $this->description,
            'amount'           => (float) $this->amount,
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

    // ==========================================
    // ELIMINAR
    // ==========================================

    public function delete(int $id): void
    {
        $expense = Expense::findOrFail($id);

        // Solo se puede eliminar si la caja aún está abierta o si es admin
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

    // ==========================================
    // FILTROS
    // ==========================================

    public function clearFilters(): void
    {
        $this->filterType     = '';
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

    // ==========================================
    // STATS COMPUTADAS
    // ==========================================

    public function getStatsProperty(): array
    {
        $base = Expense::query();

        if ($this->filterDateFrom) $base->whereDate('expense_date', '>=', $this->filterDateFrom);
        if ($this->filterDateTo)   $base->whereDate('expense_date', '<=', $this->filterDateTo);

        return [
            'total'       => (clone $base)->sum('amount'),
            'cash'        => (clone $base)->where('payment_method', 'cash')->sum('amount'),
            'operational' => (clone $base)->where('type', 'operational')->sum('amount'),
            'purchase'    => (clone $base)->where('type', 'purchase')->sum('amount'),
            'inventory'   => (clone $base)->where('type', 'inventory')->sum('amount'),
            'salary'      => (clone $base)->where('type', 'salary')->sum('amount'),
            'count'       => (clone $base)->count(),
        ];
    }

    // ==========================================
    // RENDER
    // ==========================================

    public function render()
    {
        $expenses = Expense::with(['cashRegister', 'registeredBy'])
            ->when($this->filterType,     fn($q) => $q->where('type', $this->filterType))
            ->when($this->filterDateFrom, fn($q) => $q->whereDate('expense_date', '>=', $this->filterDateFrom))
            ->when($this->filterDateTo,   fn($q) => $q->whereDate('expense_date', '<=', $this->filterDateTo))
            ->when($this->search,         fn($q) => $q->where('description', 'like', '%' . $this->search . '%'))
            ->orderBy('expense_date', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        // Resolver el modelo fresco (no lo guardamos como propiedad para evitar hydration issues)
        $openRegister = $this->openRegisterId
            ? CashRegister::find($this->openRegisterId)
            : CashRegister::getOpenRegister();

        // Actualizar el ID si la caja cambió de estado
        $this->openRegisterId = $openRegister?->id;

        return view('livewire.admin.expenses', [
            'expenses'     => $expenses,
            'stats'        => $this->stats,
            'types'        => self::TYPES,
            'openRegister' => $openRegister,
        ])->layout('components.layouts.admin', ['title' => 'Egresos']);
    }
}
