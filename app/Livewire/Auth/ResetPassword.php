<?php

namespace App\Livewire\Auth;

use App\Models\User;
use Livewire\Component;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Auth\Events\PasswordReset;

class ResetPassword extends Component
{
    public string $token                 = '';
    public string $email                 = '';
    public string $password              = '';
    public string $password_confirmation = '';

    protected $rules = [
        'email'    => 'required|email',
        'password' => 'required|string|min:6|confirmed',
    ];

    protected $messages = [
        'email.required'     => 'El correo es obligatorio.',
        'password.required'  => 'La contraseña es obligatoria.',
        'password.min'       => 'Mínimo 6 caracteres.',
        'password.confirmed' => 'Las contraseñas no coinciden.',
    ];

    public function mount(string $token): void
    {
        $this->token = $token;
        // Prellenar email si viene en la URL
        $this->email = request()->query('email', '');
    }

    public function resetPassword(): void
    {
        $this->validate();

        $status = Password::reset(
            [
                'email'                 => $this->email,
                'password'              => $this->password,
                'password_confirmation' => $this->password_confirmation,
                'token'                 => $this->token,
            ],
            function (User $user, string $password) {
                $user->forceFill([
                    'password'       => Hash::make($password),
                    'remember_token' => Str::random(60),
                ])->save();

                event(new PasswordReset($user));
            }
        );

        if ($status === Password::PASSWORD_RESET) {
            session()->flash('status', '¡Contraseña actualizada! Ya podés iniciar sesión.');
            $this->redirect(route('login'));
        } else {
            $this->addError('email', __($status));
        }
    }

    public function render()
    {
        return view('livewire.auth.reset-password')
            ->layout('components.layouts.guest');
    }
}