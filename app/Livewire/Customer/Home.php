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

    public function selectProduct($productId)
    {
        $this->selectedProduct = Product::with('activeVariants')->findOrFail($productId);
        
        // Si solo tiene una variante, agregar directamente
        if ($this->selectedProduct->activeVariants->count() === 1) {
            $this->addToCart($this->selectedProduct->activeVariants->first()->id);
            return;
        }
        
        // Si tiene múltiples variantes, mostrar modal
        $this->showVariantModal = true;
        $this->selectedVariantId = null;
    }

    public function addToCart($variantId = null)
    {
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        // Si viene del modal, usar la variante seleccionada
        if ($variantId === null && $this->selectedVariantId) {
            $variantId = $this->selectedVariantId;
        }

        if (!$variantId) {
            session()->flash('error', 'Por favor selecciona un tamaño.');
            return;
        }

        $variant = \App\Models\ProductVariant::with('product')->findOrFail($variantId);

        if ($variant->stock <= 0) {
            session()->flash('error', 'Esta variante no tiene stock disponible.');
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
        } else {
            CartItem::create([
                'user_id' => auth()->id(),
                'product_id' => $variant->product_id,
                'product_variant_id' => $variant->id,
                'quantity' => 1,
            ]);
        }

        $this->dispatch('cart-updated');
        session()->flash('message', 'Producto agregado al carrito!');
        
        // Cerrar modal
        $this->closeVariantModal();
    }

    public function closeVariantModal()
    {
        $this->showVariantModal = false;
        $this->selectedProduct = null;
        $this->selectedVariantId = null;
    }

    public function render()
    {
        $categories = Category::where('is_active', true)->get();
        
        $products = Product::with('activeVariants')
            ->where('is_active', true)
            ->when($this->selectedCategory, function ($query) {
                $query->where('category_id', $this->selectedCategory);
            })
            ->when($this->search, function ($query) {
                $query->where('name', 'like', '%' . $this->search . '%');
            })
            ->get();

        return view('livewire.customer.home', [
            'categories' => $categories,
            'products' => $products,
        ])->layout('components.layouts.app');
    }
}