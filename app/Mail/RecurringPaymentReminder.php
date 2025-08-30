<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\Payment;

class RecurringPaymentReminder extends Mailable
{
    use Queueable, SerializesModels;

    public $payment;

    public function __construct(Payment $payment)
    {
        $this->payment = $payment;
    }

    public function build()
    {
        $matricula = $this->payment->matricula;
        $daysUntilDue = now()->diffInDays($this->payment->data_vencimento);

        return $this->view('emails.recurring-payment-reminder')
                    ->subject("🔔 Lembrete: Pagamento vence em {$daysUntilDue} dias - {$matricula->curso}")
                    ->with([
                        'payment' => $this->payment,
                        'matricula' => $matricula,
                        'daysUntilDue' => $daysUntilDue
                    ]);
    }
} 