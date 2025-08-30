<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\Payment;

class PaymentGeneratedMail extends Mailable
{
    use Queueable, SerializesModels;

    public $payment;
    public $paymentData;

    public function __construct(Payment $payment, $paymentData)
    {
        $this->payment = $payment;
        $this->paymentData = $paymentData;
    }

    public function build()
    {
        $matricula = $this->payment->matricula;
        $paymentType = $this->getPaymentType();

        return $this->view('emails.payment-generated')
                    ->subject("💳 {$paymentType} disponível - Mensalidade {$this->payment->installment_number} - {$matricula->curso}")
                    ->with([
                        'payment' => $this->payment,
                        'matricula' => $matricula,
                        'paymentData' => $this->paymentData,
                        'paymentType' => $paymentType
                    ]);
    }

    private function getPaymentType()
    {
        if (isset($this->paymentData['point_of_interaction'])) {
            return 'PIX';
        } elseif (isset($this->paymentData['transaction_details'])) {
            return 'Boleto';
        } elseif (isset($this->paymentData['init_point'])) {
            return 'Link de Pagamento';
        }
        
        return 'Pagamento';
    }
} 