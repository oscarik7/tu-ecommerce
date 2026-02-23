<?php

namespace App\Livewire\Admin;

use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\CupSize;
use App\Models\Category;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\DB;

class InventoryGeneral extends Component
{
    use WithPagination;

    // Filtros
    public $search = '';
    public $filterCategory = '';
    public $filterType = ''; // 'cups', 'simple_products', 'weight_products'
    public $filterStock = ''; // 'all', 'low', 'out'

    // Modal de ajuste
    public $showAdjustModal = false;
    public $adjustItemType = ''; // 'cup' o 'variant'
    public $adjustItemId = null;
    public $adjustItemName = '';
    public $adjustCurrentStock = 0;
    public $adjustType = 'add'; // 'add', 'subtract', 'set'
    public $adjustQty = '';
    public $adjustReason = '';

    const REASONS = [
        'Compra de stock',
        'Devolución de cliente',
        'Rotura/Pérdida',
        'Ajuste de inventario',
        'Vencimiento',
        'Otro',
    ];

    protected $queryString = ['search', 'filterCategory', 'filterType', 'filterStock'];

    // ==========================================
    // VALIDACIÓN
    // ==========================================

    protected function rules(): array
    {
        return [
            'adjustType'   => 'required|in:add,subtract,set',
            'adjustQty'    => 'required|integer|min:1',
            'adjustReason' => 'required|string|max:255',
        ];
    }

    protected $messages = [
        'adjustQty.required'    => 'Ingresá la cantidad.',
        'adjustQty.min'         => 'La cantidad debe ser mayor a 0.',
        'adjustReason.required' => 'Indicá el motivo del ajuste.',
    ];

    // ==========================================
    // ABRIR MODAL DE AJUSTE
    // ==========================================

    public function openAdjust(string $type, int $id, string $action = 'add'): void
    {
        $this->adjustItemType = $type;
        $this->adjustItemId = $id;
        $this->adjustType = $action;
        $this->adjustQty = '';
        $this->adjustReason = '';

        if ($type === 'cup') {
            $item = CupSize::findOrFail($id);
            $this->adjustItemName = $item->name;
            $this->adjustCurrentStock = $item->stock;
        } else {
            $item = ProductVariant::with('product')->findOrFail($id);
            $this->adjustItemName = "{$item->product->name} - {$item->formatted_volume}";
            $this->adjustCurrentStock = $item->stock;
        }

        $this->resetErrorBag();
        $this->showAdjustModal = true;
    }

    public function closeAdjust(): void
    {
        $this->showAdjustModal = false;
        $this->reset(['adjustItemType', 'adjustItemId', 'adjustItemName', 'adjustCurrentStock']);
    }

    // ==========================================
    // GUARDAR AJUSTE
    // ==========================================

    public function saveAdjust(): void
    {
        $this->validate();

        try {
            DB::beginTransaction();

            $qty = (int) $this->adjustQty;

            if ($this->adjustItemType === 'cup') {
                $item = CupSize::lockForUpdate()->findOrFail($this->adjustItemId);
                $oldStock = $item->stock;

                match($this->adjustType) {
                    'add'      => $item->increment('stock', $qty),
                    'subtract' => $this->doSubtract($item, $qty),
                    'set'      => $item->update(['stock' => $qty]),
                };

                $item->refresh();
                $newStock = $item->stock;
            } else {
                $item = ProductVariant::lockForUpdate()->findOrFail($this->adjustItemId);
                $oldStock = $item->stock;

                match($this->adjustType) {
                    'add'      => $item->increment('stock', $qty),
                    'subtract' => $this->doSubtract($item, $qty),
                    'set'      => $item->update(['stock' => $qty]),
                };

                $item->refresh();
                $newStock = $item->stock;
            }

            DB::commit();

            $action = match($this->adjustType) {
                'add'      => "Se agregaron {$qty} unidades",
                'subtract' => "Se descontaron {$qty} unidades",
                'set'      => "Stock ajustado a {$qty}",
            };

            session()->flash('message', "{$action} de {$this->adjustItemName}. Stock: {$oldStock} → {$newStock}");
            $this->closeAdjust();

        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Error al ajustar: ' . $e->getMessage());
        }
    }

    private function doSubtract($item, int $qty): void
    {
        if ($item->stock < $qty) {
            throw new \Exception(
                "Stock insuficiente. Disponible: {$item->stock}, intentás descontar: {$qty}"
            );
        }
        $item->decrement('stock', $qty);
    }

    // ==========================================
    // ACTUALIZAR STOCK MÍNIMO
    // ==========================================

    public function updateMinStock(string $type, int $id, int $value): void
    {
        if ($value < 0) return;

        if ($type === 'cup') {
            CupSize::findOrFail($id)->update(['stock_min' => $value]);
        } else {
            ProductVariant::findOrFail($id)->update(['stock_min' => $value]);
        }

        session()->flash('message', 'Stock mínimo actualizado.');
    }

    // ==========================================
    // PROPIEDADES COMPUTADAS
    // ==========================================

    public function getStatsProperty(): array
    {
        // Vasitos
        $cups = CupSize::all();
        $cupsTotal = $cups->sum('stock');
        $cupsLow = $cups->filter(fn($c) => $c->is_low_stock)->count();
        $cupsOut = $cups->where('stock', 0)->count();

        // Productos simples (sin cup_size_id)
        $simpleVariants = ProductVariant::whereNull('cup_size_id')->get();
        $productsTotal = $simpleVariants->sum('stock');
        $productsLow = $simpleVariants->filter(fn($v) => $v->stock > 0 && $v->stock <= 10)->count();
        $productsOut = $simpleVariants->where('stock', 0)->count();

        return [
            'total_items'   => $cups->count() + $simpleVariants->count(),
            'total_units'   => $cupsTotal + $productsTotal,
            'low_stock'     => $cupsLow + $productsLow,
            'out_of_stock'  => $cupsOut + $productsOut,
            'cups_total'    => $cupsTotal,
            'products_total'=> $productsTotal,
        ];
    }

    // ==========================================
    // RENDER
    // ==========================================

    public function render()
    {
        // Construir query unificada
        $items = collect();

        // Si no hay filtro de tipo o es 'cups', incluir vasitos
        if (!$this->filterType || $this->filterType === 'cups') {
            $cups = CupSize::query()
                ->when($this->search, fn($q) =>
                    $q->where('name', 'like', '%' . $this->search . '%')
                )
                ->when($this->filterStock === 'low', fn($q) =>
                    $q->whereColumn('stock', '<=', 'stock_min')
                )
                ->when($this->filterStock === 'out', fn($q) =>
                    $q->where('stock', 0)
                )
                ->get()
                ->map(fn($cup) => [
                    'id'          => $cup->id,
                    'type'        => 'cup',
                    'name'        => $cup->name,
                    'category'    => 'Vasitos',
                    'volume'      => $cup->volume_ml . 'ml',
                    'stock'       => $cup->stock,
                    'stock_min'   => $cup->stock_min,
                    'is_low'      => $cup->is_low_stock,
                    'is_active'   => $cup->is_active,
                    'sort_key'    => 'A_' . $cup->name, // Para ordenar vasitos primero
                ]);

            $items = $items->merge($cups);
        }

        // Si no hay filtro de tipo o es 'simple_products', incluir productos simples
        if (!$this->filterType || $this->filterType === 'simple_products') {
            $variants = ProductVariant::with(['product.category'])
                ->whereNull('cup_size_id')
                ->whereHas('product', function($q) {
                    $q->when($this->search, fn($query) =>
                        $query->where('name', 'like', '%' . $this->search . '%')
                    )
                    ->when($this->filterCategory, fn($query) =>
                        $query->where('category_id', $this->filterCategory)
                    );
                })
                ->when($this->filterStock === 'low', fn($q) =>
                    $q->where('stock', '>', 0)->where('stock', '<=', 10)
                )
                ->when($this->filterStock === 'out', fn($q) =>
                    $q->where('stock', 0)
                )
                ->get()
                ->map(fn($variant) => [
                    'id'          => $variant->id,
                    'type'        => 'variant',
                    'name'        => $variant->product->name,
                    'category'    => $variant->product->category->name ?? 'Sin categoría',
                    'volume'      => $variant->formatted_volume,
                    'stock'       => $variant->stock,
                    'stock_min'   => 10, // Stock mínimo por defecto para productos
                    'is_low'      => $variant->stock > 0 && $variant->stock <= 10,
                    'is_active'   => $variant->is_active && $variant->product->is_active,
                    'sort_key'    => 'B_' . $variant->product->name . '_' . $variant->volume,
                ]);

            $items = $items->merge($variants);
        }

        // Ordenar y paginar
        $items = $items->sortBy('sort_key')->values();

        // Paginación manual
        $page = request()->query('page', 1);
        $perPage = 20;
        $total = $items->count();
        $items = $items->slice(($page - 1) * $perPage, $perPage)->values();

        $categories = Category::where('is_active', true)->get();

        return view('livewire.admin.inventory-general', [
            'items'      => $items,
            'categories' => $categories,
            'stats'      => $this->stats,
            'reasons'    => self::REASONS,
            'total'      => $total,
            'perPage'    => $perPage,
            'currentPage'=> $page,
        ])->layout('components.layouts.admin', ['title' => 'Inventario General']);
    }
}
