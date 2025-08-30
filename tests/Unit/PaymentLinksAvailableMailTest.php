<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Mail\PaymentLinksAvailableMail;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;

class PaymentLinksAvailableMailTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $payment;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->user = User::factory()->create([
            'name' => 'João Silva',
            'email' => 'joao@exemplo.com',
            'phone' => '11999999999'
        ]);
        
        $this->payment = Payment::factory()->create([
            'user_id' => $this->user->id,
            'amount' => 150.75,
            'description' => 'Pagamento de teste',
            'status' => 'pending'
        ]);
    }

    /** @test */
    public function it_can_be_constructed_with_payment_and_mercadopago_data()
    {
        $mercadopagoData = [
            'point_of_interaction' => [
                'transaction_data' => [
                    'qr_code' => '00020126580014br.gov.bcb.pix0136123e4567-e12b-12d1-a456-426614174000520400005303986540510.005802BR5925João Silva6009São Paulo62070503***6304ABCD'
                ]
            ]
        ];

        $mail = new PaymentLinksAvailableMail($this->payment, $mercadopagoData);

        $this->assertInstanceOf(PaymentLinksAvailableMail::class, $mail);
        $this->assertEquals($this->payment, $mail->payment);
        $this->assertEquals($mercadopagoData, $mail->mercadopagoData);
    }

    /** @test */
    public function it_has_correct_subject()
    {
        $mercadopagoData = [];
        $mail = new PaymentLinksAvailableMail($this->payment, $mercadopagoData);

        $this->assertEquals('Links de Pagamento Disponíveis', $mail->subject);
    }

    /** @test */
    public function it_uses_correct_view()
    {
        $mercadopagoData = [];
        $mail = new PaymentLinksAvailableMail($this->payment, $mercadopagoData);

        $this->assertEquals('emails.payment-links-available', $mail->view);
    }

    /** @test */
    public function it_passes_correct_data_to_view()
    {
        $mercadopagoData = [
            'point_of_interaction' => [
                'transaction_data' => [
                    'qr_code' => '00020126580014br.gov.bcb.pix0136123e4567-e12b-12d1-a456-426614174000520400005303986540510.005802BR5925João Silva6009São Paulo62070503***6304ABCD'
                ]
            ]
        ];

        $mail = new PaymentLinksAvailableMail($this->payment, $mercadopagoData);
        $viewData = $mail->buildViewData();

        $this->assertArrayHasKey('payment', $viewData);
        $this->assertArrayHasKey('user', $viewData);
        $this->assertArrayHasKey('mercadopagoData', $viewData);
        $this->assertArrayHasKey('pixCode', $viewData);
        $this->assertArrayHasKey('boletoLink', $viewData);
        $this->assertArrayHasKey('cardLink', $viewData);

        $this->assertEquals($this->payment, $viewData['payment']);
        $this->assertEquals($this->user, $viewData['user']);
        $this->assertEquals($mercadopagoData, $viewData['mercadopagoData']);
        $this->assertEquals('00020126580014br.gov.bcb.pix0136123e4567-e12b-12d1-a456-426614174000520400005303986540510.005802BR5925João Silva6009São Paulo62070503***6304ABCD', $viewData['pixCode']);
        $this->assertNull($viewData['boletoLink']);
        $this->assertNull($viewData['cardLink']);
    }

    /** @test */
    public function it_extracts_pix_code_correctly()
    {
        $mercadopagoData = [
            'point_of_interaction' => [
                'transaction_data' => [
                    'qr_code' => '00020126580014br.gov.bcb.pix0136123e4567-e12b-12d1-a456-426614174000520400005303986540510.005802BR5925João Silva6009São Paulo62070503***6304ABCD'
                ]
            ]
        ];

        $mail = new PaymentLinksAvailableMail($this->payment, $mercadopagoData);
        $viewData = $mail->buildViewData();

        $this->assertEquals('00020126580014br.gov.bcb.pix0136123e4567-e12b-12d1-a456-426614174000520400005303986540510.005802BR5925João Silva6009São Paulo62070503***6304ABCD', $viewData['pixCode']);
    }

    /** @test */
    public function it_extracts_boleto_link_correctly()
    {
        $mercadopagoData = [
            'transaction_details' => [
                'external_resource_url' => 'https://www.mercadopago.com.br/payments/123456789/ticket?caller_id=123456&hash=abc123'
            ]
        ];

        $mail = new PaymentLinksAvailableMail($this->payment, $mercadopagoData);
        $viewData = $mail->buildViewData();

        $this->assertEquals('https://www.mercadopago.com.br/payments/123456789/ticket?caller_id=123456&hash=abc123', $viewData['boletoLink']);
    }

    /** @test */
    public function it_extracts_card_link_correctly()
    {
        $mercadopagoData = [
            'point_of_interaction' => [
                'transaction_data' => [
                    'ticket_url' => 'https://www.mercadopago.com.br/checkout/v1/redirect?pref_id=123456789'
                ]
            ]
        ];

        $mail = new PaymentLinksAvailableMail($this->payment, $mercadopagoData);
        $viewData = $mail->buildViewData();

        $this->assertEquals('https://www.mercadopago.com.br/checkout/v1/redirect?pref_id=123456789', $viewData['cardLink']);
    }

    /** @test */
    public function it_handles_empty_mercadopago_data()
    {
        $mercadopagoData = [];

        $mail = new PaymentLinksAvailableMail($this->payment, $mercadopagoData);
        $viewData = $mail->buildViewData();

        $this->assertNull($viewData['pixCode']);
        $this->assertNull($viewData['boletoLink']);
        $this->assertNull($viewData['cardLink']);
    }

    /** @test */
    public function it_handles_multiple_payment_methods()
    {
        $mercadopagoData = [
            'point_of_interaction' => [
                'transaction_data' => [
                    'qr_code' => '00020126580014br.gov.bcb.pix0136123e4567-e12b-12d1-a456-426614174000520400005303986540510.005802BR5925João Silva6009São Paulo62070503***6304ABCD',
                    'ticket_url' => 'https://www.mercadopago.com.br/checkout/v1/redirect?pref_id=123456789'
                ]
            ],
            'transaction_details' => [
                'external_resource_url' => 'https://www.mercadopago.com.br/payments/123456789/ticket?caller_id=123456&hash=abc123'
            ]
        ];

        $mail = new PaymentLinksAvailableMail($this->payment, $mercadopagoData);
        $viewData = $mail->buildViewData();

        $this->assertNotNull($viewData['pixCode']);
        $this->assertNotNull($viewData['boletoLink']);
        $this->assertNotNull($viewData['cardLink']);
    }

    /** @test */
    public function it_can_be_sent_via_mail_facade()
    {
        Mail::fake();

        $mercadopagoData = [
            'point_of_interaction' => [
                'transaction_data' => [
                    'qr_code' => '00020126580014br.gov.bcb.pix0136123e4567-e12b-12d1-a456-426614174000520400005303986540510.005802BR5925João Silva6009São Paulo62070503***6304ABCD'
                ]
            ]
        ];

        Mail::to($this->user->email)->send(new PaymentLinksAvailableMail($this->payment, $mercadopagoData));

        Mail::assertSent(PaymentLinksAvailableMail::class, function ($mail) {
            return $mail->hasTo($this->user->email);
        });
    }

    /** @test */
    public function it_builds_view_data_with_user_relationship()
    {
        $mercadopagoData = [];
        $mail = new PaymentLinksAvailableMail($this->payment, $mercadopagoData);
        $viewData = $mail->buildViewData();

        $this->assertEquals($this->user->id, $viewData['user']->id);
        $this->assertEquals($this->user->name, $viewData['user']->name);
        $this->assertEquals($this->user->email, $viewData['user']->email);
    }

    /** @test */
    public function it_formats_payment_amount_correctly()
    {
        $mercadopagoData = [];
        $mail = new PaymentLinksAvailableMail($this->payment, $mercadopagoData);
        $viewData = $mail->buildViewData();

        $this->assertEquals(150.75, $viewData['payment']->amount);
    }
} 