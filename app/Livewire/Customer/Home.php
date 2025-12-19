<?php

namespace App\Livewire\Customer;

use App\Models\Product;
use App\Models\Category;
use App\Models\CartItem;
use Livewire\Component;
use Carbon\Carbon; // <--- Importante: Importamos Carbon para manejar fechas

class Home extends Component
{
    public $selectedCategory = null;
    public $search = '';

    // Modal de selección de variante
    public $showVariantModal = false;
    public $selectedProduct = null;
    public $selectedVariantId = null;

    /**
     * Lógica para el horario: Cerrado solo Lunes.
     * Martes a Domingos de 13:00 a 21:00 (Hora Paraguay)
     */
    public function getShopStatus()
    {
        // Forzamos la zona horaria de Paraguay
        $now = Carbon::now('America/Asuncion');
        $diaSemana = $now->dayOfWeekIso; // 1 (Lunes) a 7 (Domingo)
        $horaActual = $now->format('H:i');

        $horaApertura = '13:00';
        $horaCierre = '21:00';

        // Lógica: No es lunes (1) Y la hora está en el rango
        $estaAbierto = ($diaSemana != 1) &&
                       ($horaActual >= $horaApertura) &&
                       ($horaActual < $horaCierre);

        return [
            'is_open' => $estaAbierto,
            'label' => $estaAbierto ? 'Abierto ahora' : 'Cerrado ahora',
            'color' => $estaAbierto ? 'bg-green-400' : 'bg-red-500',
            'hours' => ($diaSemana == 1) ? 'Cerrado los lunes' : "Mar. a Dom.: $horaApertura - $horaCierre"
        ];
    }

    /**
     * Seleccionar un producto para agregar al carrito
     */
    public function selectProduct($productId)
    {
        $this->selectedProduct = Product::with('activeVariants')->findOrFail($productId);

        $variantsWithStock = $this->selectedProduct->activeVariants->where('stock', '>', 0);

        if ($variantsWithStock->count() === 1) {
            $this->addToCart($variantsWithStock->first()->id);
            return;
        }

        if ($variantsWithStock->count() > 1) {
            $this->showVariantModal = true;
            $this->selectedVariantId = null;
        } else {
            session()->flash('error', 'Este producto no tiene stock disponible.');
        }
    }

    /**
     * Agregar producto al carrito
     */
    public function addToCart()
    {
        $status = $this->getShopStatus();

        if (!$status['is_open']) {
            session()->flash('error', 'Lo sentimos, el local está cerrado. Nuestro horario es de Martes a Domingos de 13:00 a 21:00.');
            return;
        }

        if (!auth()->check()) {
            session()->flash('error', 'Debes iniciar sesión para agregar productos al carrito.');
            return redirect()->route('login');
        }

        if (!$this->selectedVariantId) {
            session()->flash('error', 'Por favor selecciona un tamaño.');
            return;
        }

        try {
            $variant = \App\Models\ProductVariant::with('product')->findOrFail($this->selectedVariantId);

            if ($variant->stock <= 0) {
                session()->flash('error', 'Esta variante no tiene stock disponible.');
                $this->closeVariantModal();
                return;
            }

            $cartItem = CartItem::where('user_id', auth()->id())
                ->where('product_id', $variant->product_id)
                ->where('product_variant_id', $variant->id)
                ->first();

            if ($cartItem) {
                if ($cartItem->quantity >= $variant->stock) {
                    session()->flash('error', 'No hay más stock disponible de este tamaño.');
                    return;
                }

                $cartItem->increment('quantity');
                session()->flash('message', '¡Cantidad actualizada en el carrito!');
            } else {
                CartItem::create([
                    'user_id' => auth()->id(),
                    'product_id' => $variant->product_id,
                    'product_variant_id' => $variant->id,
                    'quantity' => 1,
                ]);

                session()->flash('message', '¡Producto agregado al carrito! 🛒');
            }

            $this->dispatch('cart-updated');
            $this->closeVariantModal();

        } catch (\Exception $e) {
            session()->flash('error', 'Error al agregar el producto al carrito.');
            \Log::error('Error al agregar al carrito: ' . $e->getMessage());
        }
    }

    public function closeVariantModal()
    {
        $this->showVariantModal = false;
        $this->selectedProduct = null;
        $this->selectedVariantId = null;
    }

    public function clearFilters()
    {
        $this->reset(['search', 'selectedCategory']);
    }

    public function updatedSearch() {}

    public function updatedSelectedCategory() {}

    public function render()
    {
        $categories = Category::where('is_active', true)
            ->orderBy('name', 'asc')
            ->get();

        $query = Product::with(['activeVariants' => function($query) {
                $query->orderBy('volume', 'asc');
            }])
            ->where('is_active', true);

        if ($this->selectedCategory) {
            $query->where('category_id', $this->selectedCategory);
        }

        if ($this->search) {
            $query->where(function($q) {
                $q->where('name', 'like', '%' . $this->search . '%')
                  ->orWhere('description', 'like', '%' . $this->search . '%')
                  ->orWhere('ingredients', 'like', '%' . $this->search . '%');
            });
        }

        $products = $query->whereHas('activeVariants')
            ->orderBy('name', 'asc')
            ->get();

        return view('livewire.customer.home', [
            'categories' => $categories,
            'products' => $products,
            'shopStatus' => $this->getShopStatus(), // <--- Pasamos el estado a la vista
        ])->layout('components.layouts.app');
    }
}
