<?php

namespace App\Livewire\Components;

use Livewire\Component;
use Livewire\Attributes\On;

class Navbar extends Component
{
    public $cartCount = 0;

    public function mount()
    {
        $this->updateCartCount();
    }

    #[On('cart-updated')]
    public function updateCartCount()
    {
        if (auth()->check()) {
            $this->cartCount = auth()->user()->cartItems()->count();
        }
    }

    public function render()
    {
        return view('livewire.components.navbar');
    }
}