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
        'total_sales_foreign_brl',   // monto real en R$ recibido
        'total_sales_split',         // total en Gs de órdenes con pago dividido
        'total_orders',
        'total_orders_split',        // cantidad de órdenes con pago dividido
        'total_expenses',
        'total_expenses_cash',       // egresos solo en efectivo
        'opened_at',
        'closed_at',
        'opening_notes',
        'closing_notes',
        'status',
    ];

    protected $casts = [
        'opening_amount'          => 'decimal:2',
        'opening_amount_brl'      => 'decimal:2',
        'closing_amount'          => 'decimal:2',
        'closing_amount_brl'      => 'decimal:2',
        'expected_amount'         => 'decimal:2',
        'expected_amount_brl'     => 'decimal:2',
        'difference'              => 'decimal:2',
        'difference_brl'          => 'decimal:2',
        'total_sales'             => 'decimal:2',
        'total_sales_cash'        => 'decimal:2',
        'total_sales_card'        => 'decimal:2',
        'total_sales_transfer'    => 'decimal:2',
        'total_sales_foreign'     => 'decimal:2',
        'total_sales_foreign_brl' => 'decimal:2',
        'total_sales_split'       => 'decimal:2',
        'total_expenses'          => 'decimal:2',
        'total_expenses_cash'     => 'decimal:2',
        'total_orders'            => 'integer',
        'total_orders_split'      => 'integer',
        'opened_at'               => 'datetime',
        'closed_at'               => 'datetime',
    ];

    // ══════════════════════════════════════════════════════════════════════════
    // RELACIONES
    // ══════════════════════════════════════════════════════════════════════════

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

    // ══════════════════════════════════════════════════════════════════════════
    // SCOPES
    // ══════════════════════════════════════════════════════════════════════════

    public function scopeOpen($query)
    {
        return $query->where('status', 'open');
    }

    public function scopeClosed($query)
    {
        return $query->where('status', 'closed');
    }

    // ══════════════════════════════════════════════════════════════════════════
    // MÉTODOS ESTÁTICOS
    // ══════════════════════════════════════════════════════════════════════════

    /**
     * Obtiene la caja abierta actualmente.
     * La caja es del negocio (no de un usuario): cualquier usuario autorizado puede cerrarla.
     */
    public static function getOpenRegister(): ?self
    {
        return self::where('status', 'open')->latest('opened_at')->first();
    }

    public static function hasOpenRegister(): bool
    {
        return self::where('status', 'open')->exists();
    }

    /**
     * Abre una nueva caja.
     * Usa transacción + lockForUpdate para evitar race conditions con múltiples cajeros.
     */
    public static function open(
        float $openingAmount,
        float $openingAmountBrl = 0,
        ?int $employeeId = null,
        ?string $notes = null
    ): self {
        return DB::transaction(function () use ($openingAmount, $openingAmountBrl, $employeeId, $notes) {
            // Re-verificar dentro de la transacción para evitar race condition
            $alreadyOpen = self::lockForUpdate()->where('status', 'open')->exists();
            if ($alreadyOpen) {
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
        });
    }

    // ══════════════════════════════════════════════════════════════════════════
    // CIERRE DE CAJA
    // ══════════════════════════════════════════════════════════════════════════

    /**
     * Cierra la caja calculando todos los totales correctamente,
     * incluyendo pagos divididos y montos en R$.
     */
    public function close(
        float $closingAmount,
        float $closingAmountBrl = 0,
        ?string $notes = null
    ): void {
        if ($this->status === 'closed') {
            throw new \Exception('Esta caja ya está cerrada.');
        }

        $sales    = $this->calculateSalesTotals();
        $expenses = $this->calculateExpenseTotals();

        // Efectivo esperado en cajón:
        // Apertura + todo lo que entró en efectivo (único + split) - egresos en efectivo
        $expectedAmount = $this->opening_amount
            + $sales['cash']
            + $sales['split_cash']
            - $expenses['cash'];

        // Reales esperados:
        // Apertura en R$ + todos los R$ recibidos (ventas únicas + split BRL)
        $expectedAmountBrl = $this->opening_amount_brl
            + $sales['foreign_brl']
            + $sales['split_foreign_brl'];

        $this->update([
            'closed_by'               => auth()->id(),
            'closing_amount'          => $closingAmount,
            'closing_amount_brl'      => $closingAmountBrl,
            'expected_amount'         => $expectedAmount,
            'expected_amount_brl'     => $expectedAmountBrl,
            'difference'              => $closingAmount - $expectedAmount,
            'difference_brl'          => $closingAmountBrl - $expectedAmountBrl,
            'total_sales'             => $sales['total'],
            'total_sales_cash'        => $sales['cash'] + $sales['split_cash'],
            'total_sales_card'        => $sales['card'] + $sales['split_card'],
            'total_sales_transfer'    => $sales['transfer'] + $sales['split_transfer'],
            'total_sales_foreign'     => $sales['foreign'] + $sales['split_foreign'],
            'total_sales_foreign_brl' => $sales['foreign_brl'] + $sales['split_foreign_brl'],
            'total_sales_split'       => $sales['split_total'],
            'total_orders'            => $sales['count'],
            'total_orders_split'      => $sales['split_count'],
            'total_expenses'          => $expenses['total'],
            'total_expenses_cash'     => $expenses['cash'],
            'closed_at'               => now(),
            'closing_notes'           => $notes,
            'status'                  => 'closed',
        ]);
    }

    // ══════════════════════════════════════════════════════════════════════════
    // CÁLCULO DE VENTAS
    // ══════════════════════════════════════════════════════════════════════════

    /**
     * Calcula totales separando pago único vs pago dividido,
     * y procesando BRL correctamente en ambos casos.
     *
     * FIX: El match() original con side effects en array fue reemplazado por if/elseif.
     * FIX: Split payments ahora se leen desde OrderPayment, no desde la orden principal.
     */
    public function calculateSalesTotals(): array
    {
        // ── Órdenes de pago ÚNICO ──
        $singleOrders = $this->orders()
            ->where('status', '!=', 'cancelled')
            ->where('payment_status', 'paid')
            ->where('is_split_payment', false)
            ->with('paymentMethod')
            ->get();

        $cash       = 0.0;
        $card       = 0.0;
        $transfer   = 0.0;
        $foreign    = 0.0;   // en Gs equivalente
        $foreignBrl = 0.0;   // en R$ real recibido

        foreach ($singleOrders as $order) {
            $type = $order->paymentMethod->type ?? 'other';

            if ($type === 'cash') {
                $cash += (float) $order->total;
            } elseif ($type === 'card') {
                $card += (float) $order->total;
            } elseif (in_array($type, ['bank_transfer', 'mobile_wallet'])) {
                $transfer += (float) $order->total;
            } elseif ($type === 'foreign_currency') {
                $foreign += (float) $order->total;
                // El monto real en R$ viene de payment_details guardado al momento de la venta
                $details = $order->payment_details;
                if (is_array($details) && ($details['currency'] ?? '') === 'BRL') {
                    $foreignBrl += (float) ($details['received'] ?? 0);
                }
            }
        }

        $singleTotal = (float) $singleOrders->sum('total');
        $singleCount = $singleOrders->count();

        // ── Órdenes de pago DIVIDIDO ──
        // FIX CRÍTICO: antes estas órdenes no se contabilizaban porque
        // is_split_payment=true implica payment_method_id=NULL
        $splitOrders = $this->orders()
            ->where('status', '!=', 'cancelled')
            ->where('payment_status', 'paid')
            ->where('is_split_payment', true)
            ->with('payments.paymentMethod')
            ->get();

        $splitCash       = 0.0;
        $splitCard       = 0.0;
        $splitTransfer   = 0.0;
        $splitForeign    = 0.0;
        $splitForeignBrl = 0.0;
        $splitTotal      = 0.0;
        $splitCount      = $splitOrders->count();

        foreach ($splitOrders as $order) {
            $splitTotal += (float) $order->total;

            foreach ($order->payments as $payment) {
                $type   = $payment->paymentMethod->type ?? 'other';
                $amount = (float) $payment->amount; // siempre en Gs

                if ($type === 'cash') {
                    $splitCash += $amount;
                } elseif ($type === 'card') {
                    $splitCard += $amount;
                } elseif (in_array($type, ['bank_transfer', 'mobile_wallet'])) {
                    $splitTransfer += $amount;
                } elseif ($type === 'foreign_currency') {
                    $splitForeign += $amount;
                    $details = $payment->details ?? [];
                    if (is_array($details) && ($details['original_currency'] ?? '') === 'BRL') {
                        $splitForeignBrl += (float) ($details['original_amount'] ?? 0);
                    }
                }
            }
        }

        return [
            // Pago único
            'cash'              => $cash,
            'card'              => $card,
            'transfer'          => $transfer,
            'foreign'           => $foreign,
            'foreign_brl'       => $foreignBrl,
            'count'             => $singleCount + $splitCount,
            // Pago dividido (desglosado por método)
            'split_cash'        => $splitCash,
            'split_card'        => $splitCard,
            'split_transfer'    => $splitTransfer,
            'split_foreign'     => $splitForeign,
            'split_foreign_brl' => $splitForeignBrl,
            'split_total'       => $splitTotal,
            'split_count'       => $splitCount,
            // Total general en Gs
            'total'             => $singleTotal + $splitTotal,
        ];
    }

    /**
     * Calcula totales de egresos separando por método de pago.
     * Solo los egresos en efectivo afectan el cajón físico de Gs.
     */
    public function calculateExpenseTotals(): array
    {
        $expenses = $this->expenses()->get();

        $gsExpenses  = $expenses->where('currency', 'gs');
        $brlExpenses = $expenses->where('currency', 'brl');

        return [
            // Gs
            'total'       => (float) $gsExpenses->sum('amount'),
            'cash'        => (float) $gsExpenses->where('payment_method', 'cash')->sum('amount'),
            'transfer'    => (float) $gsExpenses->where('payment_method', 'transfer')->sum('amount'),
            // BRL (NUEVO — antes siempre era 0)
            'total_brl'   => (float) $brlExpenses->sum('amount_brl'),
            'cash_brl'    => (float) $brlExpenses->where('payment_method', 'cash')->sum('amount_brl'),
        ];
    }

    // ══════════════════════════════════════════════════════════════════════════
    // RESUMEN PARA MODAL DE CIERRE
    // ══════════════════════════════════════════════════════════════════════════

    /**
     * Construye el array completo para el modal de cierre.
     *
     * FIX: Eliminado el groupBy('source') que usaba una columna inexistente.
     * El origen (POS vs App) ahora se detecta por el prefijo del order_number.
     */
    public function buildClosingSummary(): array
    {
        $sales    = $this->calculateSalesTotals();
        $expenses = $this->calculateExpenseTotals();

        $expectedCash = $this->opening_amount
            + $sales['cash']
            + $sales['split_cash']
            - $expenses['cash'];

        $expectedBrl = $this->opening_amount_brl
            + $sales['foreign_brl']
            + $sales['split_foreign_brl']
            - $expenses['cash_brl'];;

        $byMethod = $this->buildMethodBreakdown();

        // Desglose por origen usando el prefijo del order_number (APP- vs POS-)
        // FIX: antes usaba columna 'source' que no existe en la tabla
        $allOrders = $this->orders()
            ->where('status', '!=', 'cancelled')
            ->where('payment_status', 'paid')
            ->select('order_number', 'total')
            ->get();

        $appOrders = $allOrders->filter(fn($o) => str_starts_with($o->order_number ?? '', 'APP'));
        $posOrders = $allOrders->filter(fn($o) => str_starts_with($o->order_number ?? '', 'POS'));

        return [
            // Apertura
            'opening_amount'    => (float) $this->opening_amount,
            'opening_brl'       => (float) $this->opening_amount_brl,
            // Ventas totales
            'total_sales'       => $sales['total'],
            'total_orders'      => $sales['count'],
            // Por tipo de pago (único + split consolidado)
            'cash_sales'        => $sales['cash'] + $sales['split_cash'],
            'card_sales'        => $sales['card'] + $sales['split_card'],
            'transfer_sales'    => $sales['transfer'] + $sales['split_transfer'],
            'foreign_sales_gs'  => $sales['foreign'] + $sales['split_foreign'],
            'foreign_sales_brl' => $sales['foreign_brl'] + $sales['split_foreign_brl'],
            'split_total'       => $sales['split_total'],
            'split_count'       => $sales['split_count'],
            // Egresos
            'expenses_total'    => $expenses['total'],
            'expenses_cash'     => $expenses['cash'],
            'expenses_transfer' => $expenses['transfer'],
            // Esperados al cierre
            'expected_cash'     => $expectedCash,
            'expected_brl'      => $expectedBrl,
            // Desglose por método de pago (tabla en modal)
            'by_method'         => $byMethod,
            // Por origen (POS mostrador vs App delivery)
            'pos_sales_count'   => $posOrders->count(),
            'pos_sales_amount'  => (float) $posOrders->sum('total'),
            'app_sales_count'   => $appOrders->count(),
            'app_sales_amount'  => (float) $appOrders->sum('total'),
            // Metadata
            'duration'          => $this->duration,
            'opened_at'         => $this->opened_at,
            'expenses_cash_brl'  => $expenses['cash_brl'],
            'expenses_total_brl' => $expenses['total_brl'],
        ];
    }

    /**
     * Construye el desglose de ventas por método de pago,
     * unificando órdenes de pago único y los registros individuales de split payments.
     */
    private function buildMethodBreakdown(): array
    {
        // Órdenes de pago único
        $singleRows = $this->orders()
            ->where('status', '!=', 'cancelled')
            ->where('payment_status', 'paid')
            ->where('is_split_payment', false)
            ->with('paymentMethod')
            ->get();

        // Pagos individuales de órdenes divididas
        $splitPaymentRows = OrderPayment::whereHas('order', function ($q) {
            $q->where('cash_register_id', $this->id)
              ->where('status', '!=', 'cancelled')
              ->where('payment_status', 'paid');
        })->with('paymentMethod')->get();

        $methods = collect();

        foreach ($singleRows as $order) {
            $name = $order->paymentMethod->name ?? 'Sin método';
            $type = $order->paymentMethod->type ?? 'other';

            $existing = $methods->get($name, [
                'name'       => $name,
                'type'       => $type,
                'count'      => 0,
                'amount_gs'  => 0.0,
                'amount_brl' => 0.0,
                'is_foreign' => $type === 'foreign_currency',
                'has_split'  => false,
            ]);

            $existing['count']++;
            $existing['amount_gs'] += (float) $order->total;

            if ($type === 'foreign_currency') {
                $details = $order->payment_details ?? [];
                $existing['amount_brl'] += (float) ($details['received'] ?? 0);
            }

            $methods->put($name, $existing);
        }

        foreach ($splitPaymentRows as $payment) {
            $name = $payment->paymentMethod->name ?? 'Sin método';
            $type = $payment->paymentMethod->type ?? 'other';

            $existing = $methods->get($name, [
                'name'       => $name,
                'type'       => $type,
                'count'      => 0,
                'amount_gs'  => 0.0,
                'amount_brl' => 0.0,
                'is_foreign' => $type === 'foreign_currency',
                'has_split'  => false,
            ]);

            $existing['count']++;
            $existing['amount_gs'] += (float) $payment->amount;
            $existing['has_split'] = true;

            if ($type === 'foreign_currency') {
                $details = $payment->details ?? [];
                $existing['amount_brl'] += (float) ($details['original_amount'] ?? 0);
            }

            $methods->put($name, $existing);
        }

        return $methods->sortByDesc('amount_gs')->values()->toArray();
    }

    // ══════════════════════════════════════════════════════════════════════════
    // ACCESSORS
    // ══════════════════════════════════════════════════════════════════════════

    public function getIsOpenAttribute(): bool
    {
        return $this->status === 'open';
    }

    public function getDifferenceStatusAttribute(): string
    {
        if ($this->difference === null) return 'N/A';
        if (abs((float) $this->difference) < 1) return 'Exacto';
        return (float) $this->difference > 0 ? 'Sobrante' : 'Faltante';
    }

    public function getDifferenceBrlStatusAttribute(): string
    {
        if ($this->difference_brl === null) return 'N/A';
        if (abs((float) $this->difference_brl) < 0.01) return 'Exacto';
        return (float) $this->difference_brl > 0 ? 'Sobrante' : 'Faltante';
    }

    public function getDurationAttribute(): string
    {
        $end = $this->closed_at ?? now();
        return $this->opened_at->diffForHumans($end, true);
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly([
                'status',
                'opening_amount',
                'opening_amount_brl',
                'closing_amount',
                'closing_amount_brl',
                'difference',
                'difference_brl',
                'opened_at',
                'closed_at',
            ])
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