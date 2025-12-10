<?php

namespace App\Livewire\Auth;

use App\Models\User;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class Register extends Component
{
    public $name = '';
    public $phone = '';
    public $password = '';
    public $password_confirmation = '';

    protected $rules = [
        'name' => 'required|string|max:255',
        'phone' => 'required|string|unique:users,phone',
        'password' => 'required|string|min:6|confirmed',
    ];

    protected $messages = [
        'name.required' => 'El nombre es obligatorio.',
        'phone.required' => 'El teléfono es obligatorio.',
        'phone.unique' => 'Este teléfono ya está registrado.',
        'password.required' => 'La contraseña es obligatoria.',
        'password.min' => 'La contraseña debe tener al menos 6 caracteres.',
        'password.confirmed' => 'Las contraseñas no coinciden.',
    ];

    public function register()
    {
        $this->validate();

        $user = User::create([
            'name' => $this->name,
            'phone' => $this->phone,
            'email' => $this->phone . '@acai.com', // Email temporal basado en teléfono
            'password' => Hash::make($this->password),
            'city' => 'Ciudad del Este',
            'is_active' => true,
        ]);

        $user->assignRole('customer');

        Auth::login($user);

        return redirect()->route('home');
    }

    public function render()
    {
        return view('livewire.auth.register')->layout('components.layouts.guest');
    }
}