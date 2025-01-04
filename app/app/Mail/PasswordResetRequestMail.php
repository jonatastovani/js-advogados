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

    /**
     * Create a new message instance.
     *
     * @param string $resetLink
     */
    public function __construct(string $resetLink)
    {
        $this->resetLink = $resetLink;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Solicitação de Redefinição de Senha',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            markdown: 'vendor.notifications.email', // Usa a estrutura padrão do Laravel
            with: [
                'level' => 'info',
                'greeting' => 'Olá!',
                'introLines' => [
                    'Recebemos uma solicitação para redefinir sua senha. Se você deseja redefinir sua senha, clique no botão abaixo.',
                ],
                'actionText' => 'Redefinir Senha',
                'actionUrl' => $this->resetLink,
                'displayableActionUrl' => $this->resetLink,
                'outroLines' => [
                    'Se você não solicitou esta redefinição, pode ignorar este e-mail com segurança.',
                ],
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
