<?php

namespace App\Mail;

use App\Models\Parceiro;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ParceiroRejeitadoMail extends Mailable
{
    use Queueable, SerializesModels;

    public $parceiro;

    /**
     * Create a new message instance.
     */
    public function __construct(Parceiro $parceiro)
    {
        $this->parceiro = $parceiro;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Sobre sua candidatura - Ensino Certo',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.parceiros.rejeitado',
            with: [
                'parceiro' => $this->parceiro,
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
