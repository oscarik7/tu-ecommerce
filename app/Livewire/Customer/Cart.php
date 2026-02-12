<?php

namespace App\Livewire\Customer;

use App\Models\CartItem;
use Livewire\Component;

class Cart extends Component
{
    public function updateQuantity(int $cartItemId, int $quantity): void
    {
        $cartItem = CartItem::with(['variant.cupSize'])
            ->where('id', $cartItemId)
            ->where('user_id', auth()->id())
            ->first();

        if (!$cartItem) return;

        if ($quantity <= 0) {
            $cartItem->delete();
        } else {
            // Usa hasStock() que delega a CupSize (no el campo legacy stock)
            if (!$cartItem->variant->hasStock($quantity)) {
                $this->dispatch('show-flash', type: 'error', message: 'No hay suficiente stock disponible.');
                return;
            }
            $cartItem->update(['quantity' => $quantity]);
        }

        $this->dispatch('cart-updated');
    }

    public function removeItem(int $cartItemId): void
    {
        CartItem::where('id', $cartItemId)
            ->where('user_id', auth()->id())
            ->delete();

        $this->dispatch('cart-updated');
        $this->dispatch('show-flash', type: 'success', message: 'Producto eliminado del carrito.');
    }

    public function clearCart(): void
    {
        auth()->user()->cartItems()->delete();
        $this->dispatch('cart-updated');
        $this->dispatch('show-flash', type: 'success', message: 'Carrito vaciado.');
    }

    public function render()
    {
        $cartItems = auth()->user()
            ->cartItems()
            ->with(['product', 'variant.cupSize'])  // cupSize para hasStock()
            ->get();

        $subtotal = $cartItems->sum(fn($item) => $item->variant->price * $item->quantity);

        return view('livewire.customer.cart', [
            'cartItems' => $cartItems,
            'subtotal'  => $subtotal,
        ])->layout('components.layouts.app');
    }
}
