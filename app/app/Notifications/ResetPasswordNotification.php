<?php

namespace App\Notifications;

use App\Mail\PasswordResetRequestMail;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Mail;

class ResetPasswordNotification extends Notification
{
    public $token;
    public $nomeSistema;

    /**
     * Cria uma nova instância da notificação.
     *
     * @param string $token
     */
    public function __construct($token)
    {
        $this->token = $token;
        $this->nomeSistema = env('APP_NAME') . (tenant('name') ? ' - ' . tenant('name') : '');
    }

    /**
     * Definir os canais da notificação.
     *
     * @param mixed $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail'];
    }

    /**
     * Enviar a notificação por e-mail.
     *
     * @param mixed $notifiable
     * @return void
     */
    public function toMail($notifiable)
    {
        // URL de redefinição de senha
        $resetLink = url(route('password.reset', [
            'token' => $this->token,
            'email' => $notifiable->getEmailForPasswordReset(),
        ], false));

        // Envia o e-mail utilizando o Mailable
        return (new PasswordResetRequestMail($resetLink))->to($notifiable->email);
    }
}
