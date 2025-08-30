<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Payment;
use App\Models\PaymentNotification;
use App\Services\PaymentNotificationService;
use App\Services\WhatsAppService;
use App\Mail\PaymentCreatedMail;
use App\Mail\PaymentLinksAvailableMail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;
use Mockery;

class PaymentNotificationIntegrationTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $whatsAppService;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->user = User::factory()->create([
            'name' => 'João Silva',
            'email' => 'joao@exemplo.com',
            'phone' => '11999999999'
        ]);
        
        $this->whatsAppService = Mockery::mock(WhatsAppService::class);
        $this->app->instance(WhatsAppService::class, $this->whatsAppService);
        
        Mail::fake();
        Queue::fake();
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    /** @test */
    public function it_completes_full_payment_notification_flow_with_pix()
    {
        Auth::login($this->user);
        
        $mercadopagoResponse = [
            'id' => 123456789,
            'status' => 'pending',
            'point_of_interaction' => [
                'transaction_data' => [
                    'qr_code' => '00020126580014br.gov.bcb.pix0136123e4567-e12b-12d1-a456-426614174000520400005303986540510.005802BR5925João Silva6009São Paulo62070503***6304ABCD'
                ]
            ]
        ];
        
        Http::fake([
            'api.mercadopago.com/*' => Http::response($mercadopagoResponse, 200)
        ]);
        
        // Mock WhatsApp service para ambas as notificações
        $this->whatsAppService->shouldReceive('sendMessage')
            ->twice()
            ->andReturn(true);
        
        // Criar matrícula que deve disparar todo o fluxo
        $response = $this->post('/matricula', [
            'curso_id' => 1,
            'nome' => 'João Silva',
            'email' => 'joao@exemplo.com',
            'telefone' => '11999999999',
            'valor' => 100.00,
            'payment_method' => 'pix'
        ]);
        
        $response->assertStatus(302);
        
        // Verificar se o pagamento foi criado
        $payment = Payment::where('user_id', $this->user->id)->first();
        $this->assertNotNull($payment);
        $this->assertEquals(100.00, $payment->amount);
        $this->assertEquals('pending', $payment->status);
        
        // Verificar se os dados do Mercado Pago foram salvos
        $this->assertNotNull($payment->mercadopago_data);
        $mercadopagoData = json_decode($payment->mercadopago_data, true);
        $this->assertEquals($mercadopagoResponse, $mercadopagoData);
        
        // Verificar se ambos os emails foram enviados
        Mail::assertSent(PaymentCreatedMail::class, function ($mail) {
            return $mail->hasTo($this->user->email);
        });
        
        Mail::assertSent(PaymentLinksAvailableMail::class, function ($mail) {
            return $mail->hasTo($this->user->email);
        });
        
        // Verificar se as notificações foram registradas no banco
        $this->assertDatabaseHas('payment_notifications', [
            'payment_id' => $payment->id,
            'type' => 'payment_created',
            'channel' => 'whatsapp',
            'status' => 'sent'
        ]);
        
        $this->assertDatabaseHas('payment_notifications', [
            'payment_id' => $payment->id,
            'type' => 'payment_links',
            'channel' => 'whatsapp',
            'status' => 'sent'
        ]);
        
        $this->assertDatabaseHas('payment_notifications', [
            'payment_id' => $payment->id,
            'type' => 'payment_created',
            'channel' => 'email',
            'status' => 'sent'
        ]);
        
        $this->assertDatabaseHas('payment_notifications', [
            'payment_id' => $payment->id,
            'type' => 'payment_links',
            'channel' => 'email',
            'status' => 'sent'
        ]);
    }

    /** @test */
    public function it_handles_multiple_payment_methods_in_single_flow()
    {
        Auth::login($this->user);
        
        $mercadopagoResponse = [
            'id' => 123456789,
            'status' => 'pending',
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
        
        Http::fake([
            'api.mercadopago.com/*' => Http::response($mercadopagoResponse, 200)
        ]);
        
        // Mock WhatsApp service esperando mensagens com todos os métodos
        $this->whatsAppService->shouldReceive('sendMessage')
            ->once()
            ->with(
                $this->user->phone,
                Mockery::type('string')
            )
            ->andReturn(true);
            
        $this->whatsAppService->shouldReceive('sendMessage')
            ->once()
            ->with(
                $this->user->phone,
                Mockery::pattern('/PIX.*BOLETO.*CARTÃO/s')
            )
            ->andReturn(true);
        
        $response = $this->post('/matricula', [
            'curso_id' => 1,
            'nome' => 'João Silva',
            'email' => 'joao@exemplo.com',
            'telefone' => '11999999999',
            'valor' => 100.00,
            'payment_method' => 'pix'
        ]);
        
        $response->assertStatus(302);
        
        // Verificar se o email de links contém todos os métodos
        Mail::assertSent(PaymentLinksAvailableMail::class, function ($mail) use ($mercadopagoResponse) {
            $viewData = $mail->buildViewData();
            return $viewData['pixCode'] !== null &&
                   $viewData['boletoLink'] !== null &&
                   $viewData['cardLink'] !== null;
        });
    }

    /** @test */
    public function it_handles_whatsapp_failure_gracefully()
    {
        Auth::login($this->user);
        
        $mercadopagoResponse = [
            'id' => 123456789,
            'status' => 'pending',
            'point_of_interaction' => [
                'transaction_data' => [
                    'qr_code' => '00020126580014br.gov.bcb.pix0136123e4567-e12b-12d1-a456-426614174000520400005303986540510.005802BR5925João Silva6009São Paulo62070503***6304ABCD'
                ]
            ]
        ];
        
        Http::fake([
            'api.mercadopago.com/*' => Http::response($mercadopagoResponse, 200)
        ]);
        
        // Simular falha no WhatsApp mas sucesso no email
        $this->whatsAppService->shouldReceive('sendMessage')
            ->twice()
            ->andReturn(false);
        
        $response = $this->post('/matricula', [
            'curso_id' => 1,
            'nome' => 'João Silva',
            'email' => 'joao@exemplo.com',
            'telefone' => '11999999999',
            'valor' => 100.00,
            'payment_method' => 'pix'
        ]);
        
        $response->assertStatus(302);
        
        $payment = Payment::where('user_id', $this->user->id)->first();
        
        // Emails devem ter sido enviados mesmo com falha no WhatsApp
        Mail::assertSent(PaymentCreatedMail::class);
        Mail::assertSent(PaymentLinksAvailableMail::class);
        
        // Notificações de WhatsApp devem estar marcadas como falhadas
        $this->assertDatabaseHas('payment_notifications', [
            'payment_id' => $payment->id,
            'type' => 'payment_created',
            'channel' => 'whatsapp',
            'status' => 'failed'
        ]);
        
        $this->assertDatabaseHas('payment_notifications', [
            'payment_id' => $payment->id,
            'type' => 'payment_links',
            'channel' => 'whatsapp',
            'status' => 'failed'
        ]);
        
        // Notificações de email devem estar como enviadas
        $this->assertDatabaseHas('payment_notifications', [
            'payment_id' => $payment->id,
            'type' => 'payment_created',
            'channel' => 'email',
            'status' => 'sent'
        ]);
    }

    /** @test */
    public function it_continues_flow_even_with_mercadopago_api_failure()
    {
        Auth::login($this->user);
        
        Http::fake([
            'api.mercadopago.com/*' => Http::response(['error' => 'API Error'], 500)
        ]);
        
        $this->whatsAppService->shouldReceive('sendMessage')
            ->twice()
            ->andReturn(true);
        
        $response = $this->post('/matricula', [
            'curso_id' => 1,
            'nome' => 'João Silva',
            'email' => 'joao@exemplo.com',
            'telefone' => '11999999999',
            'valor' => 100.00,
            'payment_method' => 'pix'
        ]);
        
        $response->assertStatus(302);
        
        $payment = Payment::where('user_id', $this->user->id)->first();
        
        // Pagamento deve ter sido criado mesmo com falha na API
        $this->assertNotNull($payment);
        $this->assertEquals(100.00, $payment->amount);
        
        // Notificações devem ter sido enviadas mesmo sem dados do MP
        Mail::assertSent(PaymentCreatedMail::class);
        Mail::assertSent(PaymentLinksAvailableMail::class);
        
        // Dados do MP devem estar vazios ou null
        $this->assertTrue(
            $payment->mercadopago_data === null || 
            $payment->mercadopago_data === '[]' ||
            $payment->mercadopago_data === '{}'
        );
    }

    /** @test */
    public function it_processes_payment_update_with_new_mercadopago_data()
    {
        Auth::login($this->user);
        
        $payment = Payment::factory()->create([
            'user_id' => $this->user->id,
            'amount' => 100.00,
            'status' => 'pending'
        ]);
        
        $mercadopagoResponse = [
            'id' => 123456789,
            'status' => 'approved',
            'point_of_interaction' => [
                'transaction_data' => [
                    'qr_code' => '00020126580014br.gov.bcb.pix0136123e4567-e12b-12d1-a456-426614174000520400005303986540510.005802BR5925João Silva6009São Paulo62070503***6304ABCD'
                ]
            ]
        ];
        
        Http::fake([
            'api.mercadopago.com/*' => Http::response($mercadopagoResponse, 200)
        ]);
        
        $this->whatsAppService->shouldReceive('sendMessage')
            ->once()
            ->andReturn(true);
        
        $response = $this->put("/payments/{$payment->id}", [
            'status' => 'approved'
        ]);
        
        $response->assertStatus(302);
        
        $payment->refresh();
        
        // Status deve ter sido atualizado
        $this->assertEquals('approved', $payment->status);
        
        // Dados do MP devem ter sido salvos
        $this->assertNotNull($payment->mercadopago_data);
        $this->assertEquals($mercadopagoResponse, json_decode($payment->mercadopago_data, true));
        
        // Notificação de links deve ter sido enviada
        Mail::assertSent(PaymentLinksAvailableMail::class);
        
        $this->assertDatabaseHas('payment_notifications', [
            'payment_id' => $payment->id,
            'type' => 'payment_links',
            'channel' => 'whatsapp',
            'status' => 'sent'
        ]);
    }

    /** @test */
    public function it_tracks_notification_timing_correctly()
    {
        Auth::login($this->user);
        
        $startTime = now();
        
        $mercadopagoResponse = [
            'id' => 123456789,
            'status' => 'pending',
            'point_of_interaction' => [
                'transaction_data' => [
                    'qr_code' => '00020126580014br.gov.bcb.pix0136123e4567-e12b-12d1-a456-426614174000520400005303986540510.005802BR5925João Silva6009São Paulo62070503***6304ABCD'
                ]
            ]
        ];
        
        Http::fake([
            'api.mercadopago.com/*' => Http::response($mercadopagoResponse, 200)
        ]);
        
        $this->whatsAppService->shouldReceive('sendMessage')
            ->twice()
            ->andReturn(true);
        
        $this->post('/matricula', [
            'curso_id' => 1,
            'nome' => 'João Silva',
            'email' => 'joao@exemplo.com',
            'telefone' => '11999999999',
            'valor' => 100.00,
            'payment_method' => 'pix'
        ]);
        
        $endTime = now();
        
        $payment = Payment::where('user_id', $this->user->id)->first();
        $notifications = PaymentNotification::where('payment_id', $payment->id)->get();
        
        // Todas as notificações devem ter timestamps dentro do período de execução
        foreach ($notifications as $notification) {
            $this->assertNotNull($notification->sent_at);
            $this->assertTrue($notification->sent_at->between($startTime, $endTime));
        }
    }

    /** @test */
    public function it_prevents_duplicate_notifications()
    {
        Auth::login($this->user);
        
        $payment = Payment::factory()->create([
            'user_id' => $this->user->id,
            'amount' => 100.00,
            'status' => 'pending'
        ]);
        
        $mercadopagoResponse = [
            'id' => 123456789,
            'status' => 'pending'
        ];
        
        Http::fake([
            'api.mercadopago.com/*' => Http::response($mercadopagoResponse, 200)
        ]);
        
        $this->whatsAppService->shouldReceive('sendMessage')
            ->twice() // Deve ser chamado apenas uma vez por cada tipo de notificação
            ->andReturn(true);
        
        $paymentNotificationService = new PaymentNotificationService($this->whatsAppService);
        
        // Enviar notificações duas vezes
        $paymentNotificationService->sendPaymentCreatedNotifications($payment);
        $paymentNotificationService->sendPaymentCreatedNotifications($payment);
        
        $paymentNotificationService->sendPaymentLinksNotifications($payment, $mercadopagoResponse);
        $paymentNotificationService->sendPaymentLinksNotifications($payment, $mercadopagoResponse);
        
        // Verificar que não há duplicatas no banco
        $createdNotifications = PaymentNotification::where('payment_id', $payment->id)
            ->where('type', 'payment_created')
            ->count();
            
        $linksNotifications = PaymentNotification::where('payment_id', $payment->id)
            ->where('type', 'payment_links')
            ->count();
        
        // Deve haver apenas 2 notificações de cada tipo (whatsapp + email)
        $this->assertEquals(2, $createdNotifications);
        $this->assertEquals(2, $linksNotifications);
    }

    /** @test */
    public function it_handles_concurrent_payment_creation()
    {
        Auth::login($this->user);
        
        $mercadopagoResponse = [
            'id' => 123456789,
            'status' => 'pending'
        ];
        
        Http::fake([
            'api.mercadopago.com/*' => Http::response($mercadopagoResponse, 200)
        ]);
        
        $this->whatsAppService->shouldReceive('sendMessage')
            ->times(4) // 2 pagamentos x 2 notificações cada
            ->andReturn(true);
        
        // Simular criação de pagamentos concorrentes
        $responses = [];
        for ($i = 0; $i < 2; $i++) {
            $responses[] = $this->post('/payments', [
                'amount' => 100.00 + $i,
                'description' => "Pagamento {$i}",
                'payment_method' => 'pix'
            ]);
        }
        
        // Ambos devem ter sucesso
        foreach ($responses as $response) {
            $response->assertStatus(302);
        }
        
        // Deve haver 2 pagamentos criados
        $payments = Payment::where('user_id', $this->user->id)->get();
        $this->assertEquals(2, $payments->count());
        
        // Cada pagamento deve ter suas notificações
        foreach ($payments as $payment) {
            $this->assertDatabaseHas('payment_notifications', [
                'payment_id' => $payment->id,
                'type' => 'payment_created',
                'status' => 'sent'
            ]);
            
            $this->assertDatabaseHas('payment_notifications', [
                'payment_id' => $payment->id,
                'type' => 'payment_links',
                'status' => 'sent'
            ]);
        }
    }
} 