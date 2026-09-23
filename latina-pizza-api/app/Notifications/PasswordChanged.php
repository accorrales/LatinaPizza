<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeEncrypted;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PasswordChanged extends Notification implements ShouldBeEncrypted, ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public function __construct()
    {
        $this->onQueue('password-recovery');
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Tu contraseña de Latina Pizza cambió')
            ->greeting('Tu contraseña fue actualizada')
            ->line('Se cambió la contraseña de tu cuenta y se cerraron las sesiones anteriores.')
            ->line('Si hiciste este cambio, ya podés iniciar sesión con tu nueva contraseña.')
            ->line('Si no fuiste vos, recuperá tu cuenta desde el sitio de Latina Pizza y contactá al equipo de atención.')
            ->salutation('El equipo de Latina Pizza');
    }
}
