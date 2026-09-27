<?php

namespace App\Notifications;

use App\Services\PasswordRecovery;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PasswordRecoveryCode extends Notification
{
    public function __construct(public string $code) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Tu código para recuperar el acceso a Latina Pizza')
            ->greeting('Recuperá tu cuenta de Latina Pizza')
            ->line('Ingresá este código en la pantalla de recuperación:')
            ->line('**'.$this->code.'**')
            ->line('Vence en '.PasswordRecovery::MINUTES.' minutos y solo se puede usar una vez.')
            ->line('No compartás este código. Nuestro equipo nunca te lo pedirá.')
            ->line('Si no solicitaste cambiar tu contraseña, podés ignorar este correo. Tu contraseña sigue igual.')
            ->salutation('El equipo de Latina Pizza');
    }
}
