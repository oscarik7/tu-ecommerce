<?php

namespace App\Livewire\Customer;

use App\Models\CartItem;
use Livewire\Component;

class Cart extends Component
{
    public function updateQuantity($cartItemId, $quantity)
    {
        $cartItem = CartItem::where('id', $cartItemId)
            ->where('user_id', auth()->id())
            ->first();

        if ($cartItem) {
            if ($quantity <= 0) {
                $cartItem->delete();
            } else {
                $cartItem->update(['quantity' => $quantity]);
            }
            $this->dispatch('cart-updated');
        }
    }

    public function removeItem($cartItemId)
    {
        CartItem::where('id', $cartItemId)
            ->where('user_id', auth()->id())
            ->delete();
        
        $this->dispatch('cart-updated');
        session()->flash('message', 'Producto eliminado del carrito.');
    }

    public function clearCart()
    {
        auth()->user()->cartItems()->delete();
        $this->dispatch('cart-updated');
        session()->flash('message', 'Carrito vaciado.');
    }

    public function render()
    {
        $cartItems = auth()->user()->cartItems()->with('product')->get();
        $subtotal = $cartItems->sum(function ($item) {
            return $item->product->price * $item->quantity;
        });

        return view('livewire.customer.cart', [
            'cartItems' => $cartItems,
            'subtotal' => $subtotal,
        ])->layout('components.layouts.app');
    }
}