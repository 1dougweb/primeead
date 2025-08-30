<?php

namespace App\Mail;

use App\Models\Payment;
use App\Models\Matricula;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PaymentCreatedMail extends Mailable
{
    use Queueable, SerializesModels;

    public $payment;
    public $matricula;

    /**
     * Create a new message instance.
     */
    public function __construct(Payment $payment, Matricula $matricula)
    {
        $this->payment = $payment;
        $this->matricula = $matricula;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '💳 Nova Cobrança Gerada - ' . $this->payment->descricao,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.payment-created',
            with: [
                'payment' => $this->payment,
                'matricula' => $this->matricula,
                'formattedAmount' => 'R$ ' . number_format($this->payment->valor, 2, ',', '.'),
                'formattedDueDate' => $this->payment->data_vencimento->format('d/m/Y'),
                'paymentMethod' => $this->getPaymentMethodLabel($this->payment->forma_pagamento),
                'isParceled' => $this->payment->total_parcelas > 1,
                'parcelInfo' => $this->payment->numero_parcela . '/' . $this->payment->total_parcelas,
            ]
        );
    }

    /**
     * Get payment method label
     */
    private function getPaymentMethodLabel($method)
    {
        return match($method) {
            'pix' => 'PIX',
            'boleto' => 'Boleto Bancário',
            'cartao_credito' => 'Cartão de Crédito',
            default => ucfirst($method)
        };
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