<?php

namespace App\Livewire\Auth;

use Livewire\Component;
use Illuminate\Support\Facades\Password;

class ForgotPassword extends Component
{
    public string $email   = '';
    public string $status  = '';

    protected $rules = [
        'email' => 'required|email',
    ];

    protected $messages = [
        'email.required' => 'El correo es obligatorio.',
        'email.email'    => 'Ingresá un correo válido.',
    ];

    public function sendResetLink(): void
    {
        $this->validate();

        $response = Password::sendResetLink(['email' => $this->email]);

        if ($response === Password::RESET_LINK_SENT) {
            $this->status = '¡Listo! Revisá tu correo para el link de recuperación.';
            $this->email  = '';
        } else {
            // No revelamos si el correo existe o no (seguridad)
            $this->status = '¡Listo! Si ese correo está registrado, recibirás un link en breve.';
            $this->email  = '';
        }
    }

    public function render()
    {
        return view('livewire.auth.forgot-password')
            ->layout('components.layouts.guest');
    }
}