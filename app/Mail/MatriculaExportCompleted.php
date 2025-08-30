<?php

declare(strict_types=1);

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class MatriculaExportCompleted extends Mailable
{
    use Queueable, InteractsWithQueue, SerializesModels;

    /**
     * Create a new message instance.
     */
    public function __construct(
        private readonly array $data
    ) {}

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $subject = isset($this->data['success']) && !$this->data['success'] 
            ? '❌ Erro na Exportação de Matrículas'
            : '✅ Exportação de Matrículas Concluída';

        return new Envelope(
            subject: $subject,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.matricula-export-completed',
            with: [
                'data' => $this->data,
                'success' => $this->data['success'] ?? true,
                'hasError' => isset($this->data['error']),
            ],
        );
    }

    /**
     * Get the attachments for the message.
     */
    public function attachments(): array
    {
        return [];
    }
}
