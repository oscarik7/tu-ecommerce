<?php

namespace App\Livewire\Auth;

use App\Models\User;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class Register extends Component
{
    public string $name                  = '';
    public string $email                 = '';
    public string $phone                 = '';
    public string $password              = '';
    public string $password_confirmation = '';

    protected $rules = [
        'name'     => 'required|string|max:255',
        'email'    => 'required|email|unique:users,email',
        'phone'    => 'nullable|string|max:30',
        'password' => 'required|string|min:6|confirmed',
    ];

    protected $messages = [
        'name.required'      => 'El nombre es obligatorio.',
        'email.required'     => 'El correo es obligatorio.',
        'email.email'        => 'Ingresá un correo válido.',
        'email.unique'       => 'Este correo ya está registrado.',
        'password.required'  => 'La contraseña es obligatoria.',
        'password.min'       => 'La contraseña debe tener al menos 6 caracteres.',
        'password.confirmed' => 'Las contraseñas no coinciden.',
    ];

    public function register()
    {
        $this->validate();

        $user = User::create([
            'name'      => $this->name,
            'email'     => $this->email,
            'phone'     => $this->phone ?: null,
            'password'  => Hash::make($this->password),
            'city'      => 'Ciudad del Este',
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