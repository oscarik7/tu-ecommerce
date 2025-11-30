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

    public function addToCart($productId)
    {
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        $product = Product::findOrFail($productId);

        if ($product->stock <= 0) {
            session()->flash('error', 'Producto sin stock disponible.');
            return;
        }

        $cartItem = CartItem::where('user_id', auth()->id())
            ->where('product_id', $productId)
            ->first();

        if ($cartItem) {
            $cartItem->increment('quantity');
        } else {
            CartItem::create([
                'user_id' => auth()->id(),
                'product_id' => $productId,
                'quantity' => 1,
            ]);
        }

        $this->dispatch('cart-updated');
        session()->flash('message', 'Producto agregado al carrito!');
    }

    public function render()
    {
        $categories = Category::where('is_active', true)->get();
        
        $products = Product::where('is_active', true)
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