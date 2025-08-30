<?php

namespace App\Mail;

use App\Models\Payment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PaymentReminderMail extends Mailable
{
    use Queueable, SerializesModels;

    public $payment;
    public $type;
    public $daysOverdue;
    public $daysUntilDue;

    /**
     * Create a new message instance.
     */
    public function __construct(Payment $payment, $type = 'upcoming')
    {
        $this->payment = $payment;
        $this->type = $type;
        
        if ($type === 'overdue') {
            $this->daysOverdue = now()->diffInDays($payment->due_date);
        } else {
            $this->daysUntilDue = now()->diffInDays($payment->due_date, false);
        }
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $subject = $this->type === 'overdue' 
            ? '🚨 Pagamento em Atraso - Ação Necessária'
            : '⏰ Lembrete de Pagamento - Vencimento Próximo';

        return new Envelope(
            subject: $subject,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        $view = $this->type === 'overdue' 
            ? 'emails.payment-overdue-reminder'
            : 'emails.payment-upcoming-reminder';

        return new Content(
            view: $view,
            with: [
                'payment' => $this->payment,
                'type' => $this->type,
                'daysOverdue' => $this->daysOverdue,
                'daysUntilDue' => $this->daysUntilDue,
                'formattedAmount' => 'R$ ' . number_format($this->payment->amount, 2, ',', '.'),
                'formattedDueDate' => $this->payment->due_date->format('d/m/Y'),
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
