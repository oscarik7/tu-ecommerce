<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Expense extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'cash_register_id',
        'employee_id',
        'registered_by',
        'type',
        'description',
        'amount',        // siempre en Gs (0 si el egreso es en BRL)
        'amount_brl',    // monto en R$ (0 si el egreso es en Gs)
        'currency',      // 'gs' | 'brl'
        'payment_method',
        'receipt_image',
        'period',
        'expense_date',
        'notes',
    ];

    protected $casts = [
        'amount'       => 'decimal:2',
        'amount_brl'   => 'decimal:2',
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

    public function scopeInGs($query)
    {
        return $query->where('currency', 'gs');
    }

    public function scopeInBrl($query)
    {
        return $query->where('currency', 'brl');
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

    /**
     * Retorna true si el egreso es en reales.
     */
    public function getIsBrlAttribute(): bool
    {
        return ($this->currency ?? 'gs') === 'brl';
    }

    public function getFormattedAmountAttribute(): string
    {
        if ($this->is_brl) {
            return number_format((float) $this->amount_brl, 2, ',', '.') . ' R$';
        }
        return number_format((float) $this->amount, 0, ',', '.') . ' Gs';
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
     * Registrar pago de salario a un empleado (siempre en Gs)
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
            'amount_brl'       => 0,
            'currency'         => 'gs',
            'payment_method'   => 'cash',
            'period'           => $period,
            'expense_date'     => now(),
            'notes'            => $notes,
        ]);
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['description', 'amount', 'amount_brl', 'currency', 'type', 'expense_date'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->useLogName('egresos')
            ->setDescriptionForEvent(fn(string $event) => match($event) {
                'created' => 'Egreso registrado',
                'updated' => 'Egreso actualizado',
                'deleted' => 'Egreso eliminado',
                default   => $event,
            });
    }
}