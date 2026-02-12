<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'document',
        'phone',
        'position',
        'address',
        'salary',
        'salary_type',
        'hire_date',
        'termination_date',
        'is_active',
        'notes',
    ];

    protected $casts = [
        'salary' => 'decimal:2',
        'hire_date' => 'date',
        'termination_date' => 'date',
        'is_active' => 'boolean',
    ];

    // ==========================================
    // RELACIONES
    // ==========================================

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function cashRegisters()
    {
        return $this->hasMany(CashRegister::class);
    }

    public function expenses()
    {
        return $this->hasMany(Expense::class);
    }

    // ==========================================
    // SCOPES
    // ==========================================

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    // ==========================================
    // ACCESSORS
    // ==========================================

    public function getSalaryTypeLabel(): string
    {
        return match($this->salary_type) {
            'fixed'      => 'Salario Fijo Mensual',
            'hourly'     => 'Por Hora',
            'commission' => 'Por Comisión',
            default      => 'Desconocido',
        };
    }

    public function getFormattedSalaryAttribute(): string
    {
        $formatted = number_format($this->salary, 0, ',', '.');
        return match($this->salary_type) {
            'fixed'      => "{$formatted} Gs/mes",
            'hourly'     => "{$formatted} Gs/hora",
            'commission' => "{$formatted}%",
            default      => "{$formatted} Gs",
        };
    }

    public function getMonthsPendingAttribute(): int
    {
        // Calcular meses desde el último pago de salario
        $lastPayment = $this->expenses()
            ->where('type', 'salary')
            ->orderByDesc('expense_date')
            ->first();

        if (!$lastPayment) {
            return (int) $this->hire_date->diffInMonths(now());
        }

        return (int) $lastPayment->expense_date->diffInMonths(now());
    }
}