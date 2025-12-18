<?php

namespace App\Livewire\Customer;

use App\Models\Product;
use App\Models\Category;
use App\Models\CartItem;
use Livewire\Component;

class Home extends Component
{
    public $selectedCategory = null;
    public $search = '';
    
    // Modal de selección de variante
    public $showVariantModal = false;
    public $selectedProduct = null;
    public $selectedVariantId = null;

    /**
     * Seleccionar un producto para agregar al carrito
     */
    public function selectProduct($productId)
    {
        $this->selectedProduct = Product::with('activeVariants')->findOrFail($productId);
        
        // Si solo tiene una variante activa con stock, agregar directamente
        $variantsWithStock = $this->selectedProduct->activeVariants->where('stock', '>', 0);
        
        if ($variantsWithStock->count() === 1) {
            $this->addToCart($variantsWithStock->first()->id);
            return;
        }
        
        // Si tiene múltiples variantes o ninguna con stock, mostrar modal
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
    public function addToCart($variantId = null)
    {
        // Verificar autenticación
        if (!auth()->check()) {
            session()->flash('error', 'Debes iniciar sesión para agregar productos al carrito.');
            return redirect()->route('login');
        }

        // Si viene del modal, usar la variante seleccionada
        if ($variantId === null && $this->selectedVariantId) {
            $variantId = $this->selectedVariantId;
        }

        // Validar que se seleccionó una variante
        if (!$variantId) {
            session()->flash('error', 'Por favor selecciona un tamaño.');
            return;
        }

        try {
            // Obtener la variante con su producto
            $variant = \App\Models\ProductVariant::with('product')->findOrFail($variantId);

            // Verificar stock disponible
            if ($variant->stock <= 0) {
                session()->flash('error', 'Esta variante no tiene stock disponible.');
                $this->closeVariantModal();
                return;
            }

            // Verificar si ya existe en el carrito
            $cartItem = CartItem::where('user_id', auth()->id())
                ->where('product_id', $variant->product_id)
                ->where('product_variant_id', $variant->id)
                ->first();

            if ($cartItem) {
                // Verificar que no exceda el stock
                if ($cartItem->quantity >= $variant->stock) {
                    session()->flash('error', 'No hay más stock disponible de este tamaño.');
                    return;
                }
                
                // Incrementar cantidad
                $cartItem->increment('quantity');
                session()->flash('message', '¡Cantidad actualizada en el carrito!');
            } else {
                // Crear nuevo item en el carrito
                CartItem::create([
                    'user_id' => auth()->id(),
                    'product_id' => $variant->product_id,
                    'product_variant_id' => $variant->id,
                    'quantity' => 1,
                ]);
                
                session()->flash('message', '¡Producto agregado al carrito! 🛒');
            }

            // Disparar evento para actualizar contador del carrito
            $this->dispatch('cart-updated');
            
            // Cerrar modal
            $this->closeVariantModal();
            
        } catch (\Exception $e) {
            session()->flash('error', 'Error al agregar el producto al carrito.');
            \Log::error('Error al agregar al carrito: ' . $e->getMessage());
        }
    }

    /**
     * Cerrar modal de selección de variantes
     */
    public function closeVariantModal()
    {
        $this->showVariantModal = false;
        $this->selectedProduct = null;
        $this->selectedVariantId = null;
    }

    /**
     * Limpiar filtros de búsqueda
     */
    public function clearFilters()
    {
        $this->reset(['search', 'selectedCategory']);
    }

    /**
     * Actualizar búsqueda cuando cambia el input
     */
    public function updatedSearch()
    {
        // Opcionalmente podrías agregar lógica adicional aquí
    }

    /**
     * Actualizar categoría seleccionada
     */
    public function updatedSelectedCategory()
    {
        // Opcionalmente podrías agregar lógica adicional aquí
    }

    /**
     * Renderizar el componente
     */
    public function render()
    {
        // Obtener categorías activas
        $categories = Category::where('is_active', true)
            ->orderBy('name', 'asc')
            ->get();
        
        // Query de productos
        $query = Product::with(['activeVariants' => function($query) {
                $query->orderBy('volume', 'asc');
            }])
            ->where('is_active', true);

        // Filtrar por categoría si está seleccionada
        if ($this->selectedCategory) {
            $query->where('category_id', $this->selectedCategory);
        }

        // Filtrar por búsqueda si hay texto
        if ($this->search) {
            $query->where(function($q) {
                $q->where('name', 'like', '%' . $this->search . '%')
                  ->orWhere('description', 'like', '%' . $this->search . '%')
                  ->orWhere('ingredients', 'like', '%' . $this->search . '%');
            });
        }

        // Obtener productos (solo los que tienen variantes activas con stock)
        $products = $query->whereHas('activeVariants', function($q) {
                // Opcional: solo mostrar productos con stock
                // $q->where('stock', '>', 0);
            })
            ->orderBy('name', 'asc')
            ->get();

        return view('livewire.customer.home', [
            'categories' => $categories,
            'products' => $products,
        ])->layout('components.layouts.app');
    }
}