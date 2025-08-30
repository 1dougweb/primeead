<?php

namespace App\Mail;

use App\Models\Parceiro;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ParceiroAprovadoMail extends Mailable
{
    use Queueable, SerializesModels;

    public $parceiro;
    public $user;
    public $senha;

    /**
     * Create a new message instance.
     */
    public function __construct(Parceiro $parceiro, ?User $user = null, ?string $senha = null)
    {
        $this->parceiro = $parceiro;
        $this->user = $user;
        $this->senha = $senha;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '✅ Parabéns! Sua candidatura foi aprovada - Ensino Certo',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.parceiros.aprovado',
            with: [
                'parceiro' => $this->parceiro,
                'user' => $this->user,
                'senha' => $this->senha,
            ],
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
