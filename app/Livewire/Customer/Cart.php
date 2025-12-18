<?php

namespace App\Livewire\Customer;

use App\Models\CartItem;
use Livewire\Component;

class Cart extends Component
{
    public function updateQuantity($cartItemId, $quantity)
    {
        $cartItem = CartItem::with('variant')->where('id', $cartItemId)
            ->where('user_id', auth()->id())
            ->first();

        if ($cartItem) {
            if ($quantity <= 0) {
                $cartItem->delete();
            } else {
                // Verificar que no exceda el stock
                if ($quantity > $cartItem->variant->stock) {
                    session()->flash('error', 'No hay suficiente stock disponible.');
                    return;
                }
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
        $cartItems = auth()->user()->cartItems()->with(['product', 'variant'])->get();
        $subtotal = $cartItems->sum(function ($item) {
            return $item->variant->price * $item->quantity;
        });

        return view('livewire.customer.cart', [
            'cartItems' => $cartItems,
            'subtotal' => $subtotal,
        ])->layout('components.layouts.app');
    }
}