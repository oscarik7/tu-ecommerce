<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Expense extends Model
{
    use HasFactory;

    protected $fillable = [
        'cash_register_id',
        'employee_id',
        'registered_by',
        'type',
        'description',
        'amount',
        'payment_method',
        'receipt_image',
        'period',
        'expense_date',
        'notes',
    ];

    protected $casts = [
        'amount'       => 'decimal:2',
        'expense_date' => 'datetime',
    ];

    // ==========================================
    // RELACIONES
    // ==========================================

    public function cashRegister()
    {
        return $this->belongsTo(CashRegister::class);
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function registeredBy()
    {
        return $this->belongsTo(User::class, 'registered_by');
    }

    // ==========================================
    // SCOPES
    // ==========================================

    public function scopeByType($query, string $type)
    {
        return $query->where('type', $type);
    }

    public function scopeToday($query)
    {
        return $query->whereDate('expense_date', today());
    }

    public function scopeThisMonth($query)
    {
        return $query->whereMonth('expense_date', now()->month)
                     ->whereYear('expense_date', now()->year);
    }

    // ==========================================
    // ACCESSORS
    // ==========================================

    public function getTypeLabel(): string
    {
        return match($this->type) {
            'salary'      => '💰 Pago de Salario',
            'purchase'    => '🛒 Compra de Insumos',
            'inventory'   => '📦 Compra de Stock',
            'operational' => '🔧 Gasto Operacional',
            'other'       => '📋 Otro',
            default       => 'Desconocido',
        };
    }

    public function getTypeBadgeColor(): string
    {
        return match($this->type) {
            'salary'      => 'blue',
            'purchase'    => 'green',
            'inventory'   => 'yellow',
            'operational' => 'orange',
            'other'       => 'gray',
            default       => 'gray',
        };
    }

    public function getFormattedAmountAttribute(): string
    {
        return number_format($this->amount, 0, ',', '.') . ' Gs';
    }

    public function getReceiptUrlAttribute(): ?string
    {
        if (!$this->receipt_image) return null;
        return Storage::disk('public')->url($this->receipt_image);
    }

    // ==========================================
    // MÉTODOS ESTÁTICOS
    // ==========================================

    /**
     * Registrar pago de salario a un empleado
     */
    public static function registerSalaryPayment(
        Employee $employee,
        float $amount,
        string $period,
        ?int $cashRegisterId = null,
        ?string $notes = null
    ): self {
        return self::create([
            'cash_register_id' => $cashRegisterId,
            'employee_id'      => $employee->id,
            'registered_by'    => auth()->id(),
            'type'             => 'salary',
            'description'      => "Pago de salario - {$employee->name} - {$period}",
            'amount'           => $amount,
            'payment_method'   => 'cash',
            'period'           => $period,
            'expense_date'     => now(),
            'notes'            => $notes,
        ]);
    }
}