<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PasswordResetRequestMail extends Mailable
{
    use Queueable, SerializesModels;

    public $resetLink;
    public $nomeSistema;
    public $tempoExpiracao;

    /**
     * Create a new message instance.
     *
     * @param string $resetLink
     */
    public function __construct(string $resetLink)
    {
        $tenantName = tenant('name') ? ' - ' . tenant('name') : '';
        $this->nomeSistema = env('APP_NAME') . $tenantName;
        $this->tempoExpiracao = config('auth.passwords.' . config('auth.defaults.passwords') . '.expire');
        $this->resetLink = $resetLink;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "{$this->nomeSistema} - Solicitação de Redefinição de Senha",
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            markdown: 'vendor.notifications.email',
            with: [
                'level' => 'info',
                'greeting' => 'Olá!',
                'introLines' => [
                    "Recebemos uma solicitação para redefinir sua senha no sistema {$this->nomeSistema}.",
                    "Se você deseja redefinir sua senha, clique no botão abaixo. O link é válido por um período limitado de {$this->tempoExpiracao} minutos, então recomendamos que faça isso o quanto antes.",
                ],
                'actionText' => 'Redefinir Senha',
                'actionUrl' => $this->resetLink,
                'displayableActionUrl' => $this->resetLink,
                'outroLines' => [
                    'Se você não solicitou esta redefinição, pode ignorar este e-mail com segurança.',
                    "Caso tenha dúvidas, entre em contato com o suporte da {$this->nomeSistema}.",
                ],
                'salutation' => 'Atenciosamente, Equipe ' . $this->nomeSistema,
            ]
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
