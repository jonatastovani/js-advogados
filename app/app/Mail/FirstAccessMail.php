<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class FirstAccessMail extends Mailable
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
            subject: 'Defina sua senha no primeiro acesso',
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
                'level' => 'success',
                'greeting' => 'Bem-vindo ao Sistema!',
                'introLines' => [
                    'Você foi cadastrado no sistema. Clique no botão abaixo para definir sua senha no primeiro acesso.',
                ],
                'actionText' => 'Definir Senha',
                'actionUrl' => $this->resetLink,
                'displayableActionUrl' => $this->resetLink,
                // 'outroLines' => [
                //     'Se você não solicitou este cadastro, entre em contato com o suporte.',
                // ],
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
