<?php

namespace App\Mail;

use App\Models\Contract;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ContractReminderMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public Contract $contract;
    public int $daysLeft;

    /**
     * Create a new message instance.
     */
    public function __construct(Contract $contract, int $daysLeft)
    {
        $this->contract = $contract;
        $this->daysLeft = $daysLeft;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            to: $this->contract->student_email,
            subject: 'Lembrete: Contrato Digital expira em ' . $this->daysLeft . ' dias',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.contract-reminder',
            with: [
                'contract' => $this->contract,
                'student_name' => $this->contract->matricula->nome_completo,
                'access_link' => $this->contract->getAccessLink(),
                'expires_at' => $this->contract->access_expires_at,
                'days_left' => $this->daysLeft,
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