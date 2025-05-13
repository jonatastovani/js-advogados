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
    public $nomeSistema;

    /**
     * Create a new message instance.
     *
     * @param string $resetLink
     */
    public function __construct(string $resetLink)
    {
        $tenantName = tenant('name') ? ' - ' . tenant('name') : '';
        $this->nomeSistema = env('APP_NAME') . $tenantName;
        $this->resetLink = $resetLink;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "{$this->nomeSistema} - Acesso Inicial: Defina sua Senha",
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
                'level' => 'success',
                'greeting' => 'Bem-vindo ao Sistema ' . $this->nomeSistema . '!',
                'introLines' => [
                    "Você foi cadastrado com sucesso no sistema {$this->nomeSistema}.",
                    "Para garantir a segurança do seu acesso, clique no botão abaixo para definir sua senha.",
                    "O link de definição de senha é válido por um período limitado, portanto, recomendamos que você faça isso o quanto antes.",
                ],
                'actionText' => 'Definir Senha Agora',
                'actionUrl' => $this->resetLink,
                'displayableActionUrl' => $this->resetLink,
                'outroLines' => [
                    'Se você não realizou este cadastro ou acredita que foi um engano, desconsidere esta mensagem.',
                    "Agradecemos por fazer parte da {$this->nomeSistema}!",
                ],
                'salutation' => 'Atenciosamente,',
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
