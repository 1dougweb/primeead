<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Services\PaymentNotificationService;
use App\Services\WhatsAppService;
use App\Mail\PaymentCreatedMail;
use App\Mail\PaymentLinksAvailableMail;
use App\Models\Payment;
use App\Models\User;
use App\Models\PaymentNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Mockery;

class PaymentNotificationServiceTest extends TestCase
{
    use RefreshDatabase;

    protected $paymentNotificationService;
    protected $whatsAppService;
    protected $user;
    protected $payment;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->whatsAppService = Mockery::mock(WhatsAppService::class);
        $this->paymentNotificationService = new PaymentNotificationService($this->whatsAppService);
        
        // Criar usuário de teste
        $this->user = User::factory()->create([
            'name' => 'João Silva',
            'email' => 'joao@exemplo.com',
            'phone' => '11999999999'
        ]);
        
        // Criar pagamento de teste
        $this->payment = Payment::factory()->create([
            'user_id' => $this->user->id,
            'amount' => 100.00,
            'description' => 'Pagamento de teste',
            'status' => 'pending'
        ]);
        
        Mail::fake();
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    /** @test */
    public function it_can_send_payment_created_notifications()
    {
        $this->whatsAppService->shouldReceive('sendMessage')
            ->once()
            ->with(
                $this->user->phone,
                Mockery::type('string')
            )
            ->andReturn(true);

        $result = $this->paymentNotificationService->sendPaymentCreatedNotifications($this->payment);

        $this->assertTrue($result);
        
        // Verificar se o email foi enviado
        Mail::assertSent(PaymentCreatedMail::class, function ($mail) {
            return $mail->hasTo($this->user->email);
        });
        
        // Verificar se a notificação foi salva no banco
        $this->assertDatabaseHas('payment_notifications', [
            'payment_id' => $this->payment->id,
            'type' => 'payment_created',
            'channel' => 'whatsapp',
            'status' => 'sent'
        ]);
    }

    /** @test */
    public function it_can_send_payment_links_notifications_with_pix_code()
    {
        $mercadopagoData = [
            'point_of_interaction' => [
                'transaction_data' => [
                    'qr_code' => '00020126580014br.gov.bcb.pix0136123e4567-e12b-12d1-a456-426614174000520400005303986540510.005802BR5925João Silva6009São Paulo62070503***6304ABCD'
                ]
            ]
        ];

        $this->whatsAppService->shouldReceive('sendMessage')
            ->once()
            ->with(
                $this->user->phone,
                Mockery::pattern('/PIX.*00020126580014br\.gov\.bcb\.pix/')
            )
            ->andReturn(true);

        $result = $this->paymentNotificationService->sendPaymentLinksNotifications($this->payment, $mercadopagoData);

        $this->assertTrue($result);
        
        Mail::assertSent(PaymentLinksAvailableMail::class, function ($mail) {
            return $mail->hasTo($this->user->email);
        });
    }

    /** @test */
    public function it_can_send_payment_links_notifications_with_boleto_link()
    {
        $mercadopagoData = [
            'transaction_details' => [
                'external_resource_url' => 'https://www.mercadopago.com.br/payments/123456789/ticket?caller_id=123456&hash=abc123'
            ]
        ];

        $this->whatsAppService->shouldReceive('sendMessage')
            ->once()
            ->with(
                $this->user->phone,
                Mockery::pattern('/BOLETO.*https:\/\/www\.mercadopago\.com\.br\/payments/')
            )
            ->andReturn(true);

        $result = $this->paymentNotificationService->sendPaymentLinksNotifications($this->payment, $mercadopagoData);

        $this->assertTrue($result);
    }

    /** @test */
    public function it_can_send_payment_links_notifications_with_card_link()
    {
        $mercadopagoData = [
            'point_of_interaction' => [
                'transaction_data' => [
                    'ticket_url' => 'https://www.mercadopago.com.br/checkout/v1/redirect?pref_id=123456789'
                ]
            ]
        ];

        $this->whatsAppService->shouldReceive('sendMessage')
            ->once()
            ->with(
                $this->user->phone,
                Mockery::pattern('/CARTÃO.*https:\/\/www\.mercadopago\.com\.br\/checkout/')
            )
            ->andReturn(true);

        $result = $this->paymentNotificationService->sendPaymentLinksNotifications($this->payment, $mercadopagoData);

        $this->assertTrue($result);
    }

    /** @test */
    public function it_handles_empty_mercadopago_data_gracefully()
    {
        $this->whatsAppService->shouldReceive('sendMessage')
            ->once()
            ->with(
                $this->user->phone,
                Mockery::pattern('/Estamos processando seu pagamento/')
            )
            ->andReturn(true);

        $result = $this->paymentNotificationService->sendPaymentLinksNotifications($this->payment, []);

        $this->assertTrue($result);
    }

    /** @test */
    public function it_builds_correct_whatsapp_message_for_pix()
    {
        $mercadopagoData = [
            'point_of_interaction' => [
                'transaction_data' => [
                    'qr_code' => '00020126580014br.gov.bcb.pix0136123e4567-e12b-12d1-a456-426614174000520400005303986540510.005802BR5925João Silva6009São Paulo62070503***6304ABCD'
                ]
            ]
        ];

        $message = $this->paymentNotificationService->buildWhatsAppMessage($this->payment, $mercadopagoData);

        $this->assertStringContainsString('🔑 PIX:', $message);
        $this->assertStringContainsString('00020126580014br.gov.bcb.pix', $message);
        $this->assertStringContainsString('1. Abra o app do seu banco', $message);
        $this->assertStringContainsString('2. Escolha a opção PIX', $message);
        $this->assertStringContainsString('3. Selecione "Pix Copia e Cola"', $message);
    }

    /** @test */
    public function it_builds_correct_whatsapp_message_for_boleto()
    {
        $mercadopagoData = [
            'transaction_details' => [
                'external_resource_url' => 'https://www.mercadopago.com.br/payments/123456789/ticket?caller_id=123456&hash=abc123'
            ]
        ];

        $message = $this->paymentNotificationService->buildWhatsAppMessage($this->payment, $mercadopagoData);

        $this->assertStringContainsString('🧾 BOLETO:', $message);
        $this->assertStringContainsString('https://www.mercadopago.com.br/payments/123456789/ticket', $message);
        $this->assertStringContainsString('1. Clique no link acima', $message);
        $this->assertStringContainsString('2. Faça o download do boleto', $message);
    }

    /** @test */
    public function it_builds_correct_whatsapp_message_for_card()
    {
        $mercadopagoData = [
            'point_of_interaction' => [
                'transaction_data' => [
                    'ticket_url' => 'https://www.mercadopago.com.br/checkout/v1/redirect?pref_id=123456789'
                ]
            ]
        ];

        $message = $this->paymentNotificationService->buildWhatsAppMessage($this->payment, $mercadopagoData);

        $this->assertStringContainsString('💳 CARTÃO:', $message);
        $this->assertStringContainsString('https://www.mercadopago.com.br/checkout/v1/redirect', $message);
        $this->assertStringContainsString('1. Clique no link acima', $message);
        $this->assertStringContainsString('2. Preencha os dados do cartão', $message);
    }

    /** @test */
    public function it_builds_payment_links_whatsapp_message_correctly()
    {
        $mercadopagoData = [
            'point_of_interaction' => [
                'transaction_data' => [
                    'qr_code' => '00020126580014br.gov.bcb.pix0136123e4567-e12b-12d1-a456-426614174000520400005303986540510.005802BR5925João Silva6009São Paulo62070503***6304ABCD'
                ]
            ]
        ];

        $message = $this->paymentNotificationService->buildPaymentLinksWhatsAppMessage($this->payment, $mercadopagoData);

        $this->assertStringContainsString('🔗 LINKS DE PAGAMENTO DISPONÍVEIS', $message);
        $this->assertStringContainsString('João Silva', $message);
        $this->assertStringContainsString('R$ 100,00', $message);
        $this->assertStringContainsString('🔑 PIX:', $message);
    }

    /** @test */
    public function it_handles_whatsapp_service_failure()
    {
        $this->whatsAppService->shouldReceive('sendMessage')
            ->once()
            ->andReturn(false);

        $result = $this->paymentNotificationService->sendPaymentCreatedNotifications($this->payment);

        $this->assertFalse($result);
        
        // Verificar se a notificação foi salva com status de erro
        $this->assertDatabaseHas('payment_notifications', [
            'payment_id' => $this->payment->id,
            'type' => 'payment_created',
            'channel' => 'whatsapp',
            'status' => 'failed'
        ]);
    }

    /** @test */
    public function it_logs_notification_attempts()
    {
        Log::shouldReceive('info')
            ->once()
            ->with('Enviando notificação de pagamento criado', [
                'payment_id' => $this->payment->id,
                'user_id' => $this->user->id
            ]);

        Log::shouldReceive('info')
            ->once()
            ->with('Notificação de pagamento criado enviada com sucesso', [
                'payment_id' => $this->payment->id
            ]);

        $this->whatsAppService->shouldReceive('sendMessage')
            ->once()
            ->andReturn(true);

        $this->paymentNotificationService->sendPaymentCreatedNotifications($this->payment);
    }

    /** @test */
    public function it_saves_notification_record_with_correct_data()
    {
        $this->whatsAppService->shouldReceive('sendMessage')
            ->once()
            ->andReturn(true);

        $this->paymentNotificationService->sendPaymentCreatedNotifications($this->payment);

        $notification = PaymentNotification::where('payment_id', $this->payment->id)->first();
        
        $this->assertNotNull($notification);
        $this->assertEquals('payment_created', $notification->type);
        $this->assertEquals('whatsapp', $notification->channel);
        $this->assertEquals('sent', $notification->status);
        $this->assertNotNull($notification->sent_at);
    }

    /** @test */
    public function it_handles_multiple_payment_methods_in_single_message()
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

        $message = $this->paymentNotificationService->buildWhatsAppMessage($this->payment, $mercadopagoData);

        $this->assertStringContainsString('🔑 PIX:', $message);
        $this->assertStringContainsString('🧾 BOLETO:', $message);
        $this->assertStringContainsString('💳 CARTÃO:', $message);
    }
} 