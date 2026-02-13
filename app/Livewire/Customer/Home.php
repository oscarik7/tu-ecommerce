<?php

namespace App\Livewire\Customer;

use App\Models\Product;
use App\Models\Category;
use App\Models\CartItem;
use App\Models\ProductVariant;
use App\Models\CustomizationGroup;
use Livewire\Component;
use Carbon\Carbon;

class Home extends Component
{
    // ── Selección ────────────────────────────────
    public ?int  $selectedProductId  = null;
    public ?int  $selectedVariantId  = null;

    // ── Modal paso 1: tamaño ─────────────────────
    public bool  $showVariantModal   = false;

    // ── Modal paso 2: complementos ───────────────
    public bool  $showCustomizationsModal = false;
    // [group_id => [option_id, option_id, ...], ...]
    public array $selectedCustomizations  = [];

    // ── Filtros ──────────────────────────────────
    public string $search           = '';
    public        $selectedCategory = null;

    // ==========================================
    // HORARIO
    // ==========================================

    public function getShopStatus(): array
    {
        $now       = Carbon::now('America/Asuncion');
        $dayOfWeek = $now->dayOfWeekIso;
        $hora      = $now->format('H:i');
        $apertura  = '07:00';
        $cierre    = '21:00';

        $abierto = ($dayOfWeek !== 1) && ($hora >= $apertura) && ($hora < $cierre);

        return [
            'is_open' => $abierto,
            'label'   => $abierto ? 'Abierto ahora' : 'Cerrado ahora',
            'color'   => $abierto ? 'bg-green-400' : 'bg-red-500',
            'hours'   => ($dayOfWeek === 1)
                ? 'Cerrado los lunes'
                : "Mar. a Dom.: {$apertura} - {$cierre}",
        ];
    }

    // ==========================================
    // PASO 1 — SELECCIONAR PRODUCTO / TAMAÑO
    // ==========================================

    public function selectProduct(int $productId): void
    {
        $product = Product::with(['activeVariants.cupSize'])->findOrFail($productId);
        $variantsConStock = $product->activeVariants->filter(fn($v) => $v->hasStock(1));

        if ($variantsConStock->isEmpty()) {
            session()->flash('error', 'Este producto no tiene stock disponible.');
            return;
        }

        $this->selectedProductId      = $productId;
        $this->selectedCustomizations = [];

        if ($variantsConStock->count() === 1) {
            $this->selectedVariantId = $variantsConStock->first()->id;
            $this->proceedToCustomizations();
            return;
        }

        $this->selectedVariantId = null;
        $this->showVariantModal  = true;
    }

    public function confirmVariant(): void
    {
        if (!$this->selectedVariantId) {
            session()->flash('error', 'Por favor seleccioná un tamaño.');
            return;
        }
        $this->showVariantModal = false;
        $this->proceedToCustomizations();
    }

    // ==========================================
    // PASO 2 — COMPLEMENTOS
    // ==========================================

    private function proceedToCustomizations(): void
    {
        $groups = $this->getCustomizationGroups();

        if ($groups->isEmpty()) {
            $this->addToCart();
            return;
        }

        $this->selectedCustomizations = [];
        foreach ($groups as $group) {
            $this->selectedCustomizations[$group->id] = [];
        }
        $this->showCustomizationsModal = true;
    }

    public function toggleCustomization(int $groupId, int $optionId, bool $isMultiple): void
    {
        if (!isset($this->selectedCustomizations[$groupId])) {
            $this->selectedCustomizations[$groupId] = [];
        }

        if ($isMultiple) {
            $current = $this->selectedCustomizations[$groupId];
            if (in_array($optionId, $current)) {
                $this->selectedCustomizations[$groupId] = array_values(
                    array_filter($current, fn($id) => $id !== $optionId)
                );
            } else {
                $this->selectedCustomizations[$groupId][] = $optionId;
            }
        } else {
            $this->selectedCustomizations[$groupId] = [$optionId];
        }
    }

    public function confirmCustomizations(): void
    {
        $groups = $this->getCustomizationGroups();
        foreach ($groups as $group) {
            $n = count($this->selectedCustomizations[$group->id] ?? []);
            if ($group->required && $n < max(1, $group->min_selections)) {
                session()->flash('error', "El grupo \"{$group->name}\" es obligatorio.");
                return;
            }
            if ($group->max_selections && $n > $group->max_selections) {
                session()->flash('error', "Máximo {$group->max_selections} opciones en \"{$group->name}\".");
                return;
            }
        }

        $this->showCustomizationsModal = false;
        $this->addToCart();
    }

    public function closeCustomizationsModal(): void
    {
        $this->showCustomizationsModal = false;
        $this->resetSelection();
    }

    // ==========================================
    // AGREGAR AL CARRITO
    // ==========================================

    public function addToCart(): void
    {
        $status = $this->getShopStatus();

        if (!$status['is_open']) {
            session()->flash('error', 'El local está cerrado. Martes a Domingos de 13:00 a 21:00.');
            $this->resetSelection();
            return;
        }

        if (!auth()->check()) {
            session()->flash('error', 'Debés iniciar sesión para agregar productos al carrito.');
            $this->resetSelection();
            $this->redirect(route('login'));
            return;
        }

        if (!$this->selectedVariantId) {
            session()->flash('error', 'Por favor seleccioná un tamaño.');
            return;
        }

        try {
            $variant = ProductVariant::with(['product', 'cupSize'])->findOrFail($this->selectedVariantId);

            if (!$variant->hasStock(1)) {
                session()->flash('error', 'Esta variante no tiene stock disponible.');
                $this->resetSelection();
                return;
            }

            $snapshot = $this->buildCustomizationsSnapshot();

            $cartItem = CartItem::where('user_id', auth()->id())
                ->where('product_id', $variant->product_id)
                ->where('product_variant_id', $variant->id)
                ->first();

            if ($cartItem) {
                $nuevaCantidad = $cartItem->quantity + 1;
                if (!$variant->hasStock($nuevaCantidad)) {
                    session()->flash('error', 'No hay más stock disponible de este tamaño.');
                    return;
                }
                $cartItem->update([
                    'quantity'       => $nuevaCantidad,
                    'customizations' => $snapshot ?? $cartItem->customizations,
                ]);
                session()->flash('message', '¡Cantidad actualizada en el carrito!');
            } else {
                CartItem::create([
                    'user_id'            => auth()->id(),
                    'product_id'         => $variant->product_id,
                    'product_variant_id' => $variant->id,
                    'quantity'           => 1,
                    'customizations'     => $snapshot,
                ]);
                session()->flash('message', '¡Producto agregado al carrito! 🛒');
            }

            $this->dispatch('cart-updated');
            $this->resetSelection();

        } catch (\Exception $e) {
            session()->flash('error', 'Error al agregar el producto al carrito.');
            \Log::error('Error addToCart: ' . $e->getMessage());
        }
    }

    // ==========================================
    // HELPERS
    // ==========================================

    private function getCustomizationGroups()
    {
        if (!$this->selectedProductId) return collect();

        return CustomizationGroup::whereHas('products', fn($q) =>
                $q->where('products.id', $this->selectedProductId)
            )
            ->where('is_active', true)
            ->with(['activeOptions'])
            ->orderBy('sort_order')
            ->get();
    }

    private function buildCustomizationsSnapshot(): ?array
    {
        $flat = [];
        foreach ($this->selectedCustomizations as $groupId => $optionIds) {
            foreach ($optionIds as $optionId) {
                $group  = CustomizationGroup::with('activeOptions')->find($groupId);
                $option = $group?->activeOptions->find($optionId);
                if ($option) {
                    $flat[] = [
                        'option_id'  => $option->id,
                        'group_id'   => (int) $groupId,
                        'group_name' => $group->name,
                        'name'       => $option->name,
                        'price'      => (float) $option->price,
                    ];
                }
            }
        }
        return empty($flat) ? null : $flat;
    }

    private function resetSelection(): void
    {
        $this->showVariantModal        = false;
        $this->showCustomizationsModal = false;
        $this->selectedProductId       = null;
        $this->selectedVariantId       = null;
        $this->selectedCustomizations  = [];
    }

    public function closeVariantModal(): void
    {
        $this->resetSelection();
    }

    public function clearFilters(): void
    {
        $this->reset(['search', 'selectedCategory']);
    }

    // ==========================================
    // RENDER
    // ==========================================

    public function render()
    {
        $categories = Category::where('is_active', true)->orderBy('name')->get();

        $products = Product::with([
                'activeVariants'         => fn($q) => $q->orderBy('volume'),
                'activeVariants.cupSize',
                'category',
            ])
            ->where('is_active', true)
            ->when($this->selectedCategory, fn($q) => $q->where('category_id', $this->selectedCategory))
            ->when($this->search, fn($q) =>
                $q->where(fn($q2) =>
                    $q2->where('name', 'like', "%{$this->search}%")
                       ->orWhere('description', 'like', "%{$this->search}%")
                       ->orWhere('ingredients', 'like', "%{$this->search}%")
                )
            )
            ->whereHas('activeVariants')
            ->orderBy('name')
            ->get()
            ->filter(fn($p) => $p->activeVariants->some(fn($v) => $v->hasStock(1)));

        $selectedProduct = $this->selectedProductId
            ? Product::with(['activeVariants.cupSize'])->find($this->selectedProductId)
            : null;

        $customizationGroups = $this->showCustomizationsModal
            ? $this->getCustomizationGroups()
            : collect();

        return view('livewire.customer.home', [
            'categories'          => $categories,
            'products'            => $products,
            'shopStatus'          => $this->getShopStatus(),
            'selectedProduct'     => $selectedProduct,
            'customizationGroups' => $customizationGroups,
        ])->layout('components.layouts.app');
    }
}
