<?php

namespace App\Livewire\Admin;

use App\Models\User;
use Carbon\Carbon;
use Livewire\Component;
use Livewire\WithPagination;
use Spatie\Activitylog\Models\Activity;
use Illuminate\Support\Facades\Cache;

class ActivityLog extends Component
{
    use WithPagination;

    // ══════════════════════════════════════════════════════════════════════════
    // FILTROS
    // ══════════════════════════════════════════════════════════════════════════

    public string $search      = '';
    public string $filterLog   = '';
    public string $filterDate  = '';
    public string $filterEvent = '';
    public string $filterUser  = '';
    public string $dateFrom    = '';
    public string $dateTo      = '';
    public int    $perPage     = 30;

    // ══════════════════════════════════════════════════════════════════════════
    // REACTIVIDAD
    // ══════════════════════════════════════════════════════════════════════════

    public function updatingSearch(): void     { $this->resetPage(); }
    public function updatingFilterLog(): void  { $this->resetPage(); }
    public function updatingFilterDate(): void {
        $this->dateFrom = '';
        $this->dateTo   = '';
        $this->resetPage();
    }
    public function updatingFilterEvent(): void { $this->resetPage(); }
    public function updatingFilterUser(): void  { $this->resetPage(); }
    public function updatingDateFrom(): void    { $this->filterDate = ''; $this->resetPage(); }
    public function updatingDateTo(): void      { $this->filterDate = ''; $this->resetPage(); }

    public function clearFilters(): void
    {
        $this->reset(['search', 'filterLog', 'filterDate', 'filterEvent', 'filterUser', 'dateFrom', 'dateTo']);
        $this->perPage = 30;
        $this->resetPage();
    }

    // ══════════════════════════════════════════════════════════════════════════
    // FORMATEO
    // ══════════════════════════════════════════════════════════════════════════

    /**
     * Campos técnicos que NUNCA se muestran al dueño.
     */
    public static array $hiddenFields = [
        'id', 'created_at', 'updated_at', 'deleted_at', 'slug',
        'image', 'image_hash', 'password', 'remember_token',
        // Timestamps internos de órdenes
        'confirmed_at', 'delivered_at', 'printed_at', 'cancelled_at',
        // Referencias técnicas (IDs foráneos — son números sin contexto para el dueño)
        'printed_by', 'cash_register_id', 'user_id', 'employee_id',
        'product_id', 'product_variant_id', 'order_id', 'payment_method_id',
        'delivery_zone_id', 'cup_size_id', 'category_id',
        // GAP FIX: registered_by es un ID técnico, no útil para el dueño
        'registered_by',
        // Campos internos de pago
        'is_split_payment', 'source', 'delivery_type', 'delivery_cost',
        // Laravel internos
        'email_verified_at', 'two_factor_secret', 'two_factor_recovery_codes',
    ];

    /**
     * Etiquetas legibles para el dueño.
     */
    public static array $fieldLabels = [
        // Órdenes
        'status'             => 'Estado',
        'payment_status'     => 'Estado de pago',
        'total'              => 'Total',
        'subtotal'           => 'Subtotal',
        'customer_name'      => 'Cliente',
        'customer_phone'     => 'Teléfono cliente',
        'notes'              => 'Notas',
        'order_number'       => 'Número de pedido',
        // Productos
        'name'               => 'Nombre',
        'price'              => 'Precio',
        'is_active'          => 'Activo',
        'stock'              => 'Stock',
        'description'        => 'Descripción',
        'weight'             => 'Peso (kg)',
        'sku'                => 'SKU',
        // Caja
        'opening_amount'     => 'Apertura Gs',
        'closing_amount'     => 'Cierre Gs',
        'opening_amount_brl' => 'Apertura R$',
        'closing_amount_brl' => 'Cierre R$',
        'difference'         => 'Diferencia Gs',
        'difference_brl'     => 'Diferencia R$',
        'opening_notes'      => 'Notas de apertura',
        'closing_notes'      => 'Notas de cierre',
        'opened_at'          => 'Abierta a las',
        'closed_at'          => 'Cerrada a las',
        // Egresos
        'amount'             => 'Monto Gs',
        'expense_date'       => 'Fecha del egreso',
        'expense_type'       => 'Tipo de egreso',
        'type'               => 'Tipo',
        'payment_method'     => 'Método de pago',
        // GAP FIX: campos BRL de egresos ahora tienen etiqueta legible
        'amount_brl'         => 'Monto R$',
        'currency'           => 'Moneda',
        // Empleados
        'salary'             => 'Salario',
        'position'           => 'Cargo',
        'phone'              => 'Teléfono',
        'address'            => 'Dirección',
        'email'              => 'Correo',
        // General
        'quantity'           => 'Cantidad',
        'discount'           => 'Descuento',
    ];

    /**
     * Campos monetarios en Guaraníes.
     */
    public static array $moneyGsFields = [
        'total', 'subtotal', 'price', 'amount', 'salary',
        'opening_amount', 'closing_amount', 'difference', 'discount',
    ];

    /**
     * Campos monetarios en Reales.
     * GAP FIX: 'amount_brl' agregado para que se formatee como "R$ X,XX"
     */
    public static array $moneyBrlFields = [
        'opening_amount_brl', 'closing_amount_brl', 'difference_brl',
        'amount_brl',   // ← FIX: monto de egresos en BRL
    ];

    /**
     * Campos booleanos.
     */
    public static array $boolFields = ['is_active', 'is_available'];

    /**
     * Traducciones de valores conocidos.
     */
    public static array $valueTranslations = [
        // Estados de pedido
        'pending'      => 'Pendiente',
        'confirmed'    => 'Confirmado',
        'preparing'    => 'Preparando',
        'ready'        => 'Listo',
        'delivered'    => 'Entregado',
        'cancelled'    => 'Cancelado',
        // Pago
        'paid'         => 'Pagado',
        'failed'       => 'Fallido',
        'refunded'     => 'Reembolsado',
        'unpaid'       => 'Sin pagar',
        // Método de pago de egresos
        'cash'         => 'Efectivo',
        'card'         => 'Tarjeta',
        'transfer'     => 'Transferencia',
        // Entrega
        'pickup'       => 'Retiro en local',
        'delivery'     => 'Delivery',
        // Caja
        'open'         => 'Abierta',
        'closed'       => 'Cerrada',
        // Egreso
        'insumo'       => 'Insumo',
        'servicio'     => 'Servicio',
        'salario'      => 'Salario',
        'otro'         => 'Otro',
        'operational'  => 'Gasto operacional',
        'purchase'     => 'Compra de insumos',
        'inventory'    => 'Compra de stock',
        'salary'       => 'Pago de salario',
        'other'        => 'Otro',
        // GAP FIX: valores del campo 'currency' del modelo Expense
        'gs'           => 'Guaraníes',
        'brl'          => 'Reales (R$)',
        // Bool
        '1'     => 'Sí', '0'     => 'No',
        'true'  => 'Sí', 'false' => 'No',
    ];

    /**
     * Formatea un valor de propiedad para mostrarlo al dueño.
     */
    public function formatValue(string $key, mixed $val): string
    {
        if (is_null($val) || $val === '') return '—';

        // Monto en Gs
        if (in_array($key, self::$moneyGsFields) && is_numeric($val)) {
            return number_format((float) $val, 0, ',', '.') . ' Gs';
        }

        // Monto en R$
        if (in_array($key, self::$moneyBrlFields) && is_numeric($val)) {
            return 'R$ ' . number_format((float) $val, 2, ',', '.');
        }

        // Booleano
        if (in_array($key, self::$boolFields)) {
            return $val ? 'Sí' : 'No';
        }

        // Fecha/datetime
        if (str_ends_with($key, '_at') || str_ends_with($key, '_date')) {
            try {
                return Carbon::parse($val)->format('d/m/Y H:i');
            } catch (\Throwable) {
                return (string) $val;
            }
        }

        // Traducción de valores conocidos
        $strVal = (string) $val;
        if (isset(self::$valueTranslations[$strVal])) {
            return self::$valueTranslations[$strVal];
        }

        // Strings largos: truncar
        if (is_string($val) && mb_strlen($val) > 60) {
            return mb_substr($val, 0, 57) . '...';
        }

        return $strVal;
    }

    /**
     * Devuelve el label legible de un campo.
     */
    public function fieldLabel(string $key): string
    {
        return self::$fieldLabels[$key]
            ?? ucfirst(str_replace('_', ' ', $key));
    }

    /**
     * Retorna true si el campo debe ocultarse al dueño.
     */
    public function isHiddenField(string $key): bool
    {
        // Ocultar todos los *_at técnicos excepto los que están en fieldLabels
        if (str_ends_with($key, '_at') && !isset(self::$fieldLabels[$key])) {
            return true;
        }
        return in_array($key, self::$hiddenFields);
    }

    /**
     * Extrae los cambios legibles de una actividad de tipo 'updated'.
     */
    public function extractChanges(Activity $activity): array
    {
        $attributes = $activity->properties['attributes'] ?? [];
        $old        = $activity->properties['old'] ?? [];
        $changes    = [];

        foreach ($attributes as $key => $val) {
            if ($this->isHiddenField($key)) continue;
            if (!isset($old[$key])) continue;
            if ((string) $old[$key] === (string) $val) continue;

            $changes[] = [
                'field' => $key,
                'label' => $this->fieldLabel($key),
                'from'  => $this->formatValue($key, $old[$key]),
                'to'    => $this->formatValue($key, $val),
            ];
        }

        return $changes;
    }

    /**
     * Extrae atributos relevantes de una actividad de tipo 'created'.
     */
    public function extractCreatedProps(Activity $activity): array
    {
        $attributes = $activity->properties['attributes'] ?? [];
        $props      = [];

        foreach ($attributes as $key => $val) {
            if ($this->isHiddenField($key)) continue;
            if (is_null($val) || $val === '') continue;

            // GAP FIX: Para egresos BRL, si amount=0 y amount_brl>0, ocultar "Monto Gs: 0 Gs"
            // para no confundir al dueño. Solo mostrar el campo con valor real.
            if ($key === 'amount' && (float) $val === 0.0) {
                $attrs = $activity->properties['attributes'] ?? [];
                if (isset($attrs['amount_brl']) && (float) $attrs['amount_brl'] > 0) {
                    continue; // omitir "Monto Gs: 0 Gs" cuando es egreso BRL
                }
            }
            if ($key === 'amount_brl' && (float) $val === 0.0) {
                continue; // omitir "Monto R$: R$ 0,00" cuando es egreso Gs
            }

            $props[] = [
                'field' => $key,
                'label' => $this->fieldLabel($key),
                'value' => $this->formatValue($key, $val),
            ];
        }

        return $props;
    }

    /**
     * Determina el nivel de criticidad del evento.
     * GAP FIX: Agregada regla para egresos BRL grandes (> R$ 100).
     */
    public function criticality(Activity $activity): string
    {
        $desc       = mb_strtolower($activity->description);
        $attributes = $activity->properties['attributes'] ?? [];
        $old        = $activity->properties['old'] ?? [];

        // Alta criticidad: eliminaciones y cancelaciones
        if (
            str_contains($desc, 'eliminado') ||
            str_contains($desc, 'borrado')   ||
            str_contains($desc, 'cancelado')
        ) return 'high';

        // Diferencia en caja mayor a 10.000 Gs
        if ($activity->log_name === 'caja' && isset($attributes['difference'])) {
            if (abs((float) $attributes['difference']) > 10000) return 'high';
        }

        // GAP FIX: Egreso en BRL grande (> R$ 100 → equivale a ~600.000 Gs aprox)
        if ($activity->log_name === 'egresos' && isset($attributes['amount_brl'])) {
            if ((float) $attributes['amount_brl'] > 100) return 'medium';
        }

        // Egreso en Gs grande (> 500.000 Gs)
        if ($activity->log_name === 'egresos' && isset($attributes['amount'])) {
            if ((float) $attributes['amount'] > 500000) return 'medium';
        }

        // Cambio de precio de producto
        if (isset($old['price']) && isset($attributes['price'])) return 'medium';

        // Cambio de stock manual
        if (isset($old['stock']) && isset($attributes['stock'])) return 'medium';

        // Cambio de estado de pedido a cancelado
        if (isset($attributes['status']) && $attributes['status'] === 'cancelled') return 'medium';

        return 'low';
    }

    /**
     * Nombre legible del modelo afectado.
     */
    public function subjectTypeName(string $subjectType): string
    {
        $map = [
            'App\\Models\\Order'           => 'Pedido',
            'App\\Models\\Product'         => 'Producto',
            'App\\Models\\ProductVariant'  => 'Variante',
            'App\\Models\\CashRegister'    => 'Caja',
            'App\\Models\\Expense'         => 'Egreso',
            'App\\Models\\Employee'        => 'Empleado',
            'App\\Models\\User'            => 'Usuario',
            'App\\Models\\Category'        => 'Categoría',
            'App\\Models\\Inventory'       => 'Inventario',
        ];
        return $map[$subjectType] ?? class_basename($subjectType);
    }

    /**
     * Intenta obtener el nombre/número identificador del subject.
     */
    public function subjectLabel(Activity $activity): ?string
    {
        $attrs = $activity->properties['attributes'] ?? [];
        $old   = $activity->properties['old'] ?? [];
        $props = array_merge($old, $attrs);

        if ($activity->subject) {
            $subject = $activity->subject;
            if (isset($subject->order_number)) return '#' . $subject->order_number;
            if (isset($subject->name))         return $subject->name;
            // GAP FIX: egresos no tienen 'name', usar description como label
            if (isset($subject->description))  return mb_substr($subject->description, 0, 40);
        }

        if (!empty($props['order_number'])) return '#' . $props['order_number'];
        if (!empty($props['name']))         return $props['name'];
        // GAP FIX: fallback a description para egresos eliminados
        if (!empty($props['description']))  return mb_substr($props['description'], 0, 40);

        if ($activity->subject_id) return 'ID #' . $activity->subject_id;

        return null;
    }

    // ══════════════════════════════════════════════════════════════════════════
    // RENDER
    // ══════════════════════════════════════════════════════════════════════════

    public function render()
    {
        $query = Activity::with(['causer'])
            ->when($this->filterLog,   fn($q) => $q->inLog($this->filterLog))
            ->when($this->filterUser,  fn($q) => $q->where('causer_id', $this->filterUser))
            ->when($this->filterEvent, function ($q) {
                return match($this->filterEvent) {
                    'created' => $q->where('description', 'like', '%cread%')
                                   ->orWhere('description', 'like', '%abierto%')
                                   ->orWhere('description', 'like', '%abierta%')
                                   ->orWhere('description', 'like', '%registrado%'),
                    'updated' => $q->where('description', 'like', '%actualizado%')
                                   ->orWhere('description', 'like', '%editado%')
                                   ->orWhere('description', 'like', '%cerrado%')
                                   ->orWhere('description', 'like', '%cerrada%'),
                    'deleted' => $q->where('description', 'like', '%eliminado%')
                                   ->orWhere('description', 'like', '%borrado%')
                                   ->orWhere('description', 'like', '%cancelado%'),
                    default   => $q,
                };
            })
            ->when($this->filterDate === 'today',     fn($q) => $q->whereDate('created_at', today()))
            ->when($this->filterDate === 'yesterday', fn($q) => $q->whereDate('created_at', today()->subDay()))
            ->when($this->filterDate === 'week',      fn($q) => $q->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()]))
            ->when($this->filterDate === 'month',     fn($q) => $q->whereMonth('created_at', now()->month)->whereYear('created_at', now()->year))
            ->when($this->dateFrom, fn($q) => $q->whereDate('created_at', '>=', $this->dateFrom))
            ->when($this->dateTo,   fn($q) => $q->whereDate('created_at', '<=', $this->dateTo))
            ->when($this->search, fn($q) =>
                $q->where(function ($q2) {
                    $term = '%' . $this->search . '%';
                    $q2->where('description', 'like', $term)
                       ->orWhereHas('causer', fn($q3) => $q3->where('name', 'like', $term))
                       ->orWhereRaw("JSON_SEARCH(LOWER(properties), 'one', LOWER(?)) IS NOT NULL", [$this->search]);
                })
            )
            ->latest()
            ->paginate($this->perPage);

        $users = Cache::remember('activity_log_causers', 60, function () {
            return User::whereIn('id', Activity::select('causer_id')->distinct()->whereNotNull('causer_id'))
                ->orderBy('name')
                ->get(['id', 'name']);
        });

        $statsQuery = Activity::query()
            ->when($this->filterLog,  fn($q) => $q->inLog($this->filterLog))
            ->when($this->filterUser, fn($q) => $q->where('causer_id', $this->filterUser));

        $stats = [
            'today'    => (clone $statsQuery)->whereDate('created_at', today())->count(),
            'week'     => (clone $statsQuery)->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])->count(),
            'critical' => (clone $statsQuery)
                ->whereDate('created_at', '>=', now()->subDays(7))
                ->where(function ($q) {
                    $q->where('description', 'like', '%eliminado%')
                      ->orWhere('description', 'like', '%cancelado%')
                      ->orWhere('description', 'like', '%borrado%');
                })
                ->count(),
            'users_today' => Activity::whereDate('created_at', today())
                ->distinct('causer_id')
                ->whereNotNull('causer_id')
                ->count('causer_id'),
        ];

        $hasFilters = $this->search || $this->filterLog || $this->filterDate
            || $this->filterEvent || $this->filterUser || $this->dateFrom || $this->dateTo;

        return view('livewire.admin.activity-log', [
            'activities' => $query,
            'users'      => $users,
            'stats'      => $stats,
            'hasFilters' => $hasFilters,
        ])->layout('components.layouts.admin', ['title' => 'Auditoría']);
    }
}