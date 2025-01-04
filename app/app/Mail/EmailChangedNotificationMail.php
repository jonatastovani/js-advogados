<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class EmailChangedNotificationMail extends Mailable
{
    use Queueable, SerializesModels;

    public $oldEmail;

    /**
     * Create a new message instance.
     *
     * @param string $oldEmail
     */
    public function __construct(string $oldEmail)
    {
        $this->oldEmail = $oldEmail;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Alteração no seu endereço de e-mail',
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
                    "O endereço de e-mail associado à sua conta foi alterado de **{$this->oldEmail}** para este novo endereço.",
                ],
                'actionText' => 'Verifique sua Conta',
                'actionUrl' => route('home'),
                'displayableActionUrl' => route('home'),
                'outroLines' => [
                    'Se você não realizou esta alteração, entre em contato com o suporte imediatamente.',
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
