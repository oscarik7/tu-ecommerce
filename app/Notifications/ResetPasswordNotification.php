<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\ResetPassword as BaseResetPassword;
use Illuminate\Notifications\Messages\MailMessage;

class ResetPasswordNotification extends BaseResetPassword
{
    public function toMail($notifiable): MailMessage
    {
        $url = url(route('password.reset', [
            'token' => $this->token,
            'email' => $notifiable->getEmailForPasswordReset(),
        ], false));

        return (new MailMessage)
            ->subject('Recuperar contraseña - Taskinho Açaí')
            ->greeting('¡Hola!')
            ->line('Recibiste este correo porque solicitaste recuperar la contraseña de tu cuenta.')
            ->action('Resetear Contraseña', $url)
            ->line('Este link expira en ' . config('auth.passwords.users.expire', 60) . ' minutos.')
            ->line('Si no solicitaste recuperar tu contraseña, podés ignorar este correo.')
            ->salutation('Saludos, Taskinho Açaí');
    }
}