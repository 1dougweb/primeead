<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Services\PaymentNotificationService;
use App\Services\WhatsAppService;
use App\Models\Payment;
use App\Models\User;
use Mockery;

class PaymentNotificationServiceSimpleTest extends TestCase
{
    protected $paymentNotificationService;
    protected $whatsAppService;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->whatsAppService = Mockery::mock(WhatsAppService::class);
        $this->paymentNotificationService = new PaymentNotificationService($this->whatsAppService);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    /** @test */
    public function it_can_build_whatsapp_message_for_pix()
    {
        $user = new User([
            'name' => 'João Silva',
            'email' => 'joao@exemplo.com',
            'phone' => '11999999999'
        ]);
        
        $payment = new Payment([
            'amount' => 100.00,
            'description' => 'Pagamento de teste',
            'status' => 'pending'
        ]);
        
        $payment->user = $user;
        
        $mercadopagoData = [
            'point_of_interaction' => [
                'transaction_data' => [
                    'qr_code' => '00020126580014br.gov.bcb.pix0136123e4567-e12b-12d1-a456-426614174000520400005303986540510.005802BR5925João Silva6009São Paulo62070503***6304ABCD'
                ]
            ]
        ];

        $message = $this->paymentNotificationService->buildWhatsAppMessage($payment, $mercadopagoData);

        $this->assertStringContainsString('🔑 PIX:', $message);
        $this->assertStringContainsString('00020126580014br.gov.bcb.pix', $message);
        $this->assertStringContainsString('João Silva', $message);
        $this->assertStringContainsString('R$ 100,00', $message);
        $this->assertStringContainsString('1. Abra o app do seu banco', $message);
        $this->assertStringContainsString('2. Escolha a opção PIX', $message);
        $this->assertStringContainsString('3. Selecione "Pix Copia e Cola"', $message);
    }

    /** @test */
    public function it_can_build_whatsapp_message_for_boleto()
    {
        $user = new User([
            'name' => 'João Silva',
            'email' => 'joao@exemplo.com',
            'phone' => '11999999999'
        ]);
        
        $payment = new Payment([
            'amount' => 100.00,
            'description' => 'Pagamento de teste',
            'status' => 'pending'
        ]);
        
        $payment->user = $user;
        
        $mercadopagoData = [
            'transaction_details' => [
                'external_resource_url' => 'https://www.mercadopago.com.br/payments/123456789/ticket?caller_id=123456&hash=abc123'
            ]
        ];

        $message = $this->paymentNotificationService->buildWhatsAppMessage($payment, $mercadopagoData);

        $this->assertStringContainsString('🧾 BOLETO:', $message);
        $this->assertStringContainsString('https://www.mercadopago.com.br/payments/123456789/ticket', $message);
        $this->assertStringContainsString('João Silva', $message);
        $this->assertStringContainsString('R$ 100,00', $message);
        $this->assertStringContainsString('1. Clique no link acima', $message);
        $this->assertStringContainsString('2. Faça o download do boleto', $message);
    }

    /** @test */
    public function it_can_build_whatsapp_message_for_card()
    {
        $user = new User([
            'name' => 'João Silva',
            'email' => 'joao@exemplo.com',
            'phone' => '11999999999'
        ]);
        
        $payment = new Payment([
            'amount' => 100.00,
            'description' => 'Pagamento de teste',
            'status' => 'pending'
        ]);
        
        $payment->user = $user;
        
        $mercadopagoData = [
            'point_of_interaction' => [
                'transaction_data' => [
                    'ticket_url' => 'https://www.mercadopago.com.br/checkout/v1/redirect?pref_id=123456789'
                ]
            ]
        ];

        $message = $this->paymentNotificationService->buildWhatsAppMessage($payment, $mercadopagoData);

        $this->assertStringContainsString('💳 CARTÃO:', $message);
        $this->assertStringContainsString('https://www.mercadopago.com.br/checkout/v1/redirect', $message);
        $this->assertStringContainsString('João Silva', $message);
        $this->assertStringContainsString('R$ 100,00', $message);
        $this->assertStringContainsString('1. Clique no link acima', $message);
        $this->assertStringContainsString('2. Preencha os dados do cartão', $message);
    }

    /** @test */
    public function it_can_build_payment_links_whatsapp_message()
    {
        $user = new User([
            'name' => 'João Silva',
            'email' => 'joao@exemplo.com',
            'phone' => '11999999999'
        ]);
        
        $payment = new Payment([
            'amount' => 100.00,
            'description' => 'Pagamento de teste',
            'status' => 'pending'
        ]);
        
        $payment->user = $user;
        
        $mercadopagoData = [
            'point_of_interaction' => [
                'transaction_data' => [
                    'qr_code' => '00020126580014br.gov.bcb.pix0136123e4567-e12b-12d1-a456-426614174000520400005303986540510.005802BR5925João Silva6009São Paulo62070503***6304ABCD'
                ]
            ]
        ];

        $message = $this->paymentNotificationService->buildPaymentLinksWhatsAppMessage($payment, $mercadopagoData);

        $this->assertStringContainsString('🔗 LINKS DE PAGAMENTO DISPONÍVEIS', $message);
        $this->assertStringContainsString('João Silva', $message);
        $this->assertStringContainsString('R$ 100,00', $message);
        $this->assertStringContainsString('🔑 PIX:', $message);
    }

    /** @test */
    public function it_handles_empty_mercadopago_data()
    {
        $user = new User([
            'name' => 'João Silva',
            'email' => 'joao@exemplo.com',
            'phone' => '11999999999'
        ]);
        
        $payment = new Payment([
            'amount' => 100.00,
            'description' => 'Pagamento de teste',
            'status' => 'pending'
        ]);
        
        $payment->user = $user;
        
        $mercadopagoData = [];

        $message = $this->paymentNotificationService->buildWhatsAppMessage($payment, $mercadopagoData);

        $this->assertStringContainsString('João Silva', $message);
        $this->assertStringContainsString('R$ 100,00', $message);
        $this->assertStringContainsString('Estamos processando seu pagamento', $message);
    }

    /** @test */
    public function it_handles_multiple_payment_methods_in_single_message()
    {
        $user = new User([
            'name' => 'João Silva',
            'email' => 'joao@exemplo.com',
            'phone' => '11999999999'
        ]);
        
        $payment = new Payment([
            'amount' => 100.00,
            'description' => 'Pagamento de teste',
            'status' => 'pending'
        ]);
        
        $payment->user = $user;
        
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

        $message = $this->paymentNotificationService->buildWhatsAppMessage($payment, $mercadopagoData);

        $this->assertStringContainsString('🔑 PIX:', $message);
        $this->assertStringContainsString('🧾 BOLETO:', $message);
        $this->assertStringContainsString('💳 CARTÃO:', $message);
        $this->assertStringContainsString('João Silva', $message);
        $this->assertStringContainsString('R$ 100,00', $message);
    }
} 