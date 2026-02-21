<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class CashRegister extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'opened_by',
        'closed_by',
        'employee_id',
        'opening_amount',
        'opening_amount_brl',
        'closing_amount',
        'closing_amount_brl',
        'expected_amount',
        'expected_amount_brl',
        'difference',
        'difference_brl',
        'total_sales',
        'total_sales_cash',
        'total_sales_card',
        'total_sales_transfer',
        'total_sales_foreign',
        'total_expenses',
        'total_orders',
        'opened_at',
        'closed_at',
        'opening_notes',
        'closing_notes',
        'status',
    ];

    protected $casts = [
        'opening_amount'        => 'decimal:2',
        'opening_amount_brl'    => 'decimal:2',
        'closing_amount'        => 'decimal:2',
        'closing_amount_brl'    => 'decimal:2',
        'expected_amount'       => 'decimal:2',
        'expected_amount_brl'   => 'decimal:2',
        'difference'            => 'decimal:2',
        'difference_brl'        => 'decimal:2',
        'total_sales'           => 'decimal:2',
        'total_sales_cash'      => 'decimal:2',
        'total_sales_card'      => 'decimal:2',
        'total_sales_transfer'  => 'decimal:2',
        'total_sales_foreign'   => 'decimal:2',
        'total_expenses'        => 'decimal:2',
        'total_orders'          => 'integer',
        'opened_at'             => 'datetime',
        'closed_at'             => 'datetime',
    ];

    // ==========================================
    // RELACIONES
    // ==========================================

    public function opener()
    {
        return $this->belongsTo(User::class, 'opened_by');
    }

    public function closer()
    {
        return $this->belongsTo(User::class, 'closed_by');
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function expenses()
    {
        return $this->hasMany(Expense::class);
    }

    // ==========================================
    // SCOPES
    // ==========================================

    public function scopeOpen($query)
    {
        return $query->where('status', 'open');
    }

    public function scopeByUser($query, $userId)
    {
        return $query->where('opened_by', $userId);
    }

    // ==========================================
    // MÉTODOS ESTÁTICOS
    // ==========================================

    public static function getOpenRegister(?int $userId = null): ?self
    {
        $userId = $userId ?? auth()->id();
        return self::where('opened_by', $userId)
            ->where('status', 'open')
            ->first();
    }

    public static function hasOpenRegister(?int $userId = null): bool
    {
        return self::getOpenRegister($userId) !== null;
    }

    public static function open(
        float $openingAmount,
        float $openingAmountBrl = 0,
        ?int $employeeId = null,
        ?string $notes = null
    ): self {
        if (self::hasOpenRegister()) {
            throw new \Exception('Ya hay una caja abierta. Ciérrela antes de abrir una nueva.');
        }

        return self::create([
            'opened_by'          => auth()->id(),
            'employee_id'        => $employeeId,
            'opening_amount'     => $openingAmount,
            'opening_amount_brl' => $openingAmountBrl,
            'opened_at'          => now(),
            'opening_notes'      => $notes,
            'status'             => 'open',
        ]);
    }

    // ==========================================
    // MÉTODOS DE INSTANCIA
    // ==========================================

    public function close(
        float $closingAmount,
        float $closingAmountBrl = 0,
        ?string $notes = null
    ): void {
        if ($this->status === 'closed') {
            throw new \Exception('Esta caja ya está cerrada.');
        }

        $salesData = $this->calculateSalesTotals();
        $expensesTotal = $this->expenses()->sum('amount');
        $expensesCash = $this->expenses()->where('payment_method', 'cash')->sum('amount');

        // Esperado en Gs
        $expectedAmount = $this->opening_amount + $salesData['cash'] - $expensesCash;

        // Esperado en R$ (inicial + cantidad de ventas en R$)
        $expectedAmountBrl = $this->opening_amount_brl + $salesData['foreignCount'];

        $this->update([
            'closed_by'             => auth()->id(),
            'closing_amount'        => $closingAmount,
            'closing_amount_brl'    => $closingAmountBrl,
            'expected_amount'       => $expectedAmount,
            'expected_amount_brl'   => $expectedAmountBrl,
            'difference'            => $closingAmount - $expectedAmount,
            'difference_brl'        => $closingAmountBrl - $expectedAmountBrl,
            'total_sales'           => $salesData['total'],
            'total_sales_cash'      => $salesData['cash'],
            'total_sales_card'      => $salesData['card'],
            'total_sales_transfer'  => $salesData['transfer'],
            'total_sales_foreign'   => $salesData['foreign'],
            'total_expenses'        => $expensesTotal,
            'total_orders'          => $salesData['count'],
            'closed_at'             => now(),
            'closing_notes'         => $notes,
            'status'                => 'closed',
        ]);
    }

    public function calculateSalesTotals(): array
    {
        $orders = $this->orders()
            ->where('status', '!=', 'cancelled')
            ->where('payment_status', 'paid')
            ->with('paymentMethod')
            ->get();

        $total = $orders->sum('total');
        $count = $orders->count();
        $cash = 0;
        $card = 0;
        $transfer = 0;
        $foreign = 0;
        $foreignCount = 0;

        foreach ($orders as $order) {
            $type = $order->paymentMethod->type ?? 'other';
            match($type) {
                'cash'             => $cash += $order->total,
                'card'             => $card += $order->total,
                'bank_transfer',
                'mobile_wallet'    => $transfer += $order->total,
                'foreign_currency' => [$foreign += $order->total, $foreignCount++],
                default            => null,
            };
        }

        return compact('total', 'count', 'cash', 'card', 'transfer', 'foreign', 'foreignCount');
    }

    // ==========================================
    // ACCESSORS
    // ==========================================

    public function getIsOpenAttribute(): bool
    {
        return $this->status === 'open';
    }

    public function getDifferenceStatusAttribute(): string
    {
        if ($this->difference === null) return 'N/A';
        if ($this->difference == 0) return 'Exacto';
        return $this->difference > 0 ? 'Sobrante' : 'Faltante';
    }

    public function getDifferenceBrlStatusAttribute(): string
    {
        if ($this->difference_brl === null) return 'N/A';
        if ($this->difference_brl == 0) return 'Exacto';
        return $this->difference_brl > 0 ? 'Sobrante' : 'Faltante';
    }

    public function getDurationAttribute(): string
    {
        if (!$this->closed_at) {
            return $this->opened_at->diffForHumans(now(), true);
        }
        return $this->opened_at->diffForHumans($this->closed_at, true);
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['status', 'opening_amount', 'opening_amount_brl', 'closing_amount', 'closing_amount_brl', 'opened_at', 'closed_at'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->useLogName('caja')
            ->setDescriptionForEvent(fn(string $event) => match($event) {
                'created' => 'Caja abierta',
                'updated' => 'Caja actualizada',
                'deleted' => 'Caja eliminada',
                default   => $event,
            });
    }
}