<?php

namespace App\Livewire\Admin;

use App\Models\CashRegister;
use App\Models\Employee;
use App\Models\Expense;
use App\Models\User;
use Livewire\Component;
use Livewire\WithPagination;

class Employees extends Component
{
    use WithPagination;

    // ── Vista activa ──────────────────────────────────
    public string $view = 'list'; // list | detail

    // ── Empleado seleccionado (solo ID) ───────────────
    public ?int $selectedEmployeeId = null;

    // ── Modal CRUD ────────────────────────────────────
    public bool   $showModal   = false;
    public ?int   $editingId   = null;

    // Campos del formulario
    public string $name            = '';
    public string $document        = '';
    public string $phone           = '';
    public string $position        = '';
    public string $address         = '';
    public        $salary          = 0;
    public string $salaryType      = 'fixed';
    public string $hireDate        = '';
    public string $notes           = '';
    public bool   $isActive        = true;
    public ?int   $linkedUserId    = null;

    // ── Modal Pago de Salario ─────────────────────────
    public bool   $showPayModal    = false;
    public ?int   $payingEmployeeId = null;
    public        $payAmount       = 0;
    public string $payPeriod       = '';
    public string $payMethod       = 'cash';
    public string $payNotes        = '';

    // ── Filtros ───────────────────────────────────────
    public string $search          = '';
    public string $filterStatus    = 'active'; // active | inactive | all
    public string $filterPosition  = '';

    // Tipos de salario disponibles
    const SALARY_TYPES = [
        'fixed'      => ['label' => '📅 Fijo Mensual',   'unit' => 'Gs/mes'],
        'biweekly'   => ['label' => '📅 Fijo Quincenal', 'unit' => 'Gs/quinc.'],
        'hourly'     => ['label' => '⏱️ Por Hora',        'unit' => 'Gs/hora'],
        'commission' => ['label' => '📈 Comisión',        'unit' => '%'],
    ];

    // Cargos predefinidos
    const POSITIONS = [
        'Cajero/a',
        'Preparador/a',
        'Repartidor/a',
        'Limpieza',
        'Administrador/a',
        'Gerente',
    ];

    // ==========================================
    // LIFECYCLE
    // ==========================================

    public function mount(): void
    {
        $this->hireDate = today()->format('Y-m-d');
        $this->payPeriod = now()->format('Y-m');
    }

    // ==========================================
    // NAVEGACIÓN
    // ==========================================

    public function viewEmployee(int $id): void
    {
        $this->selectedEmployeeId = $id;
        $this->view = 'detail';
    }

    public function backToList(): void
    {
        $this->view = 'list';
        $this->selectedEmployeeId = null;
    }

    // ==========================================
    // MODAL CRUD
    // ==========================================

    public function openModal(?int $id = null): void
    {
        $this->resetForm();
        if ($id) {
            $emp = Employee::findOrFail($id);
            $this->editingId     = $id;
            $this->name          = $emp->name;
            $this->document      = $emp->document ?? '';
            $this->phone         = $emp->phone ?? '';
            $this->position      = $emp->position ?? '';
            $this->address       = $emp->address ?? '';
            $this->salary        = (float) $emp->salary;
            $this->salaryType    = $emp->salary_type;
            $this->hireDate      = $emp->hire_date?->format('Y-m-d') ?? today()->format('Y-m-d');
            $this->notes         = $emp->notes ?? '';
            $this->isActive      = $emp->is_active;
            $this->linkedUserId  = $emp->user_id;
        }
        $this->showModal = true;
    }

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->resetForm();
    }

    private function resetForm(): void
    {
        $this->editingId    = null;
        $this->name         = '';
        $this->document     = '';
        $this->phone        = '';
        $this->position     = '';
        $this->address      = '';
        $this->salary       = 0;
        $this->salaryType   = 'fixed';
        $this->hireDate     = today()->format('Y-m-d');
        $this->notes        = '';
        $this->isActive     = true;
        $this->linkedUserId = null;
        $this->resetValidation();
    }

    // ==========================================
    // GUARDAR EMPLEADO
    // ==========================================

    public function save(): void
    {
        $this->validate([
            'name'       => 'required|string|min:2|max:100',
            'position'   => 'required|string|max:80',
            'salary'     => 'required|numeric|min:0',
            'salaryType' => 'required|in:fixed,biweekly,hourly,commission',
            'hireDate'   => 'required|date',
            'phone'      => 'nullable|string|max:20',
            'document'   => 'nullable|string|max:30',
        ], [
            'name.required'     => 'El nombre es obligatorio.',
            'position.required' => 'El cargo es obligatorio.',
            'salary.min'        => 'El salario no puede ser negativo.',
        ]);

        $data = [
            'user_id'     => $this->linkedUserId,
            'name'        => $this->name,
            'document'    => $this->document ?: null,
            'phone'       => $this->phone ?: null,
            'position'    => $this->position,
            'address'     => $this->address ?: null,
            'salary'      => (float) $this->salary,
            'salary_type' => $this->salaryType,
            'hire_date'   => $this->hireDate,
            'notes'       => $this->notes ?: null,
            'is_active'   => $this->isActive,
        ];

        if ($this->editingId) {
            Employee::findOrFail($this->editingId)->update($data);
            $msg = '✓ Empleado actualizado.';
        } else {
            Employee::create($data);
            $msg = '✓ Empleado registrado.';
        }

        $this->closeModal();
        $this->dispatch('show-notification', ['type' => 'success', 'message' => $msg]);
    }

    // ==========================================
    // DESACTIVAR / ACTIVAR
    // ==========================================

    public function toggleActive(int $id): void
    {
        $emp = Employee::findOrFail($id);
        $emp->update(['is_active' => !$emp->is_active]);

        $msg = $emp->is_active ? '✓ Empleado reactivado.' : '✓ Empleado desactivado.';
        $this->dispatch('show-notification', ['type' => 'success', 'message' => $msg]);
    }

    // ==========================================
    // MODAL PAGO DE SALARIO
    // ==========================================

    public function openPayModal(int $employeeId): void
    {
        $emp = Employee::findOrFail($employeeId);
        $this->payingEmployeeId = $employeeId;
        $this->payAmount        = (float) $emp->salary;
        $this->payPeriod        = now()->format('Y-m');
        $this->payMethod        = 'cash';
        $this->payNotes         = '';
        $this->showPayModal     = true;
    }

    public function closePayModal(): void
    {
        $this->showPayModal     = false;
        $this->payingEmployeeId = null;
        $this->resetValidation();
    }

    public function processPay(): void
    {
        $this->validate([
            'payAmount' => 'required|numeric|min:1',
            'payPeriod' => 'required|string',
            'payMethod' => 'required|in:cash,card,transfer',
        ], [
            'payAmount.min' => 'El monto debe ser mayor a 0.',
        ]);

        $emp = Employee::findOrFail($this->payingEmployeeId);

        // Generar egreso automáticamente
        Expense::create([
            'cash_register_id' => CashRegister::getOpenRegister()?->id,
            'employee_id'      => $emp->id,
            'registered_by'    => auth()->id(),
            'type'             => 'salary',
            'description'      => "Pago de salario — {$emp->name} — {$this->payPeriod}",
            'amount'           => (float) $this->payAmount,
            'payment_method'   => $this->payMethod,
            'period'           => $this->payPeriod,
            'expense_date'     => now(),
            'notes'            => $this->payNotes ?: null,
        ]);

        $this->closePayModal();
        $this->dispatch('show-notification', [
            'type'    => 'success',
            'message' => "✓ Pago de {$emp->name} registrado y egreso generado.",
        ]);
    }

    // ==========================================
    // COMPUTED PROPERTIES
    // ==========================================

    public function getSelectedEmployeeProperty(): ?Employee
    {
        if (!$this->selectedEmployeeId) return null;
        return Employee::with(['user', 'expenses' => fn($q) => $q->orderByDesc('expense_date')])->find($this->selectedEmployeeId);
    }

    public function getPayingEmployeeProperty(): ?Employee
    {
        if (!$this->payingEmployeeId) return null;
        return Employee::find($this->payingEmployeeId);
    }

    public function getAlertEmployeesProperty(): \Illuminate\Support\Collection
    {
        // Empleados activos con salario fijo/quincenal sin pago en el período actual
        return Employee::active()
            ->whereIn('salary_type', ['fixed', 'biweekly'])
            ->where('salary', '>', 0)
            ->get()
            ->filter(function (Employee $emp) {
                $monthsPending = $emp->months_pending;
                return $monthsPending >= 1;
            });
    }

    public function getStatsProperty(): array
    {
        $active   = Employee::active()->count();
        $total    = Employee::count();
        $monthlyPayroll = Employee::active()
            ->whereIn('salary_type', ['fixed', 'biweekly'])
            ->sum('salary');
        $paidThisMonth = Expense::where('type', 'salary')
            ->whereMonth('expense_date', now()->month)
            ->whereYear('expense_date', now()->year)
            ->sum('amount');

        return [
            'active'         => $active,
            'inactive'       => $total - $active,
            'monthly_payroll'=> $monthlyPayroll,
            'paid_this_month'=> $paidThisMonth,
            'pending_payroll'=> max(0, $monthlyPayroll - $paidThisMonth),
        ];
    }

    // ==========================================
    // RENDER
    // ==========================================

    public function render()
    {
        $employees = Employee::with('user')
            ->when($this->search, fn($q) =>
                $q->where('name', 'like', '%' . $this->search . '%')
                  ->orWhere('position', 'like', '%' . $this->search . '%')
                  ->orWhere('phone', 'like', '%' . $this->search . '%')
            )
            ->when($this->filterStatus === 'active',   fn($q) => $q->where('is_active', true))
            ->when($this->filterStatus === 'inactive', fn($q) => $q->where('is_active', false))
            ->when($this->filterPosition, fn($q) => $q->where('position', $this->filterPosition))
            ->orderBy('is_active', 'desc')
            ->orderBy('name')
            ->paginate(15);

        // Usuarios sin empleado vinculado (para el selector de vinculación)
        $availableUsers = User::whereDoesntHave('employee')
            ->orWhereHas('employee', fn($q) => $q->where('id', $this->editingId))
            ->orderBy('name')
            ->get();

        return view('livewire.admin.employees', [
            'employees'        => $employees,
            'availableUsers'   => $availableUsers,
            'stats'            => $this->stats,
            'alerts'           => $this->alertEmployees,
            'salaryTypes'      => self::SALARY_TYPES,
            'positions'        => self::POSITIONS,
            'selectedEmployee' => $this->selectedEmployee,
            'payingEmployee'   => $this->payingEmployee,
        ])->layout('components.layouts.admin', ['title' => 'Empleados']);
    }
}
