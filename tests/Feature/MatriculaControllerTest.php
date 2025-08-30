<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Matricula;
use App\Models\Payment;
use App\Models\PaymentNotification;
use App\Services\PaymentNotificationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Mockery;

class MatriculaControllerTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $paymentNotificationService;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->user = User::factory()->create([
            'name' => 'João Silva',
            'email' => 'joao@exemplo.com',
            'phone' => '11999999999'
        ]);
        
        $this->paymentNotificationService = Mockery::mock(PaymentNotificationService::class);
        $this->app->instance(PaymentNotificationService::class, $this->paymentNotificationService);
        
        Mail::fake();
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    /** @test */
    public function it_can_create_matricula_with_payment_notifications()
    {
        Auth::login($this->user);
        
        // Mock da resposta do Mercado Pago
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
        
        // Mock do serviço de notificações
        $this->paymentNotificationService->shouldReceive('sendPaymentCreatedNotifications')
            ->once()
            ->andReturn(true);
            
        $this->paymentNotificationService->shouldReceive('sendPaymentLinksNotifications')
            ->once()
            ->with(Mockery::type(Payment::class), $mercadopagoResponse)
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
        
        // Verificar se a matrícula foi criada
        $this->assertDatabaseHas('matriculas', [
            'user_id' => $this->user->id,
            'nome' => 'João Silva',
            'email' => 'joao@exemplo.com'
        ]);
        
        // Verificar se o pagamento foi criado
        $this->assertDatabaseHas('payments', [
            'user_id' => $this->user->id,
            'amount' => 100.00,
            'status' => 'pending'
        ]);
    }

    /** @test */
    public function it_stores_mercadopago_data_in_payment()
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
        
        $this->paymentNotificationService->shouldReceive('sendPaymentCreatedNotifications')
            ->once()
            ->andReturn(true);
            
        $this->paymentNotificationService->shouldReceive('sendPaymentLinksNotifications')
            ->once()
            ->andReturn(true);
        
        $this->post('/matricula', [
            'curso_id' => 1,
            'nome' => 'João Silva',
            'email' => 'joao@exemplo.com',
            'telefone' => '11999999999',
            'valor' => 100.00,
            'payment_method' => 'pix'
        ]);
        
        $payment = Payment::where('user_id', $this->user->id)->first();
        
        $this->assertNotNull($payment->mercadopago_data);
        $this->assertEquals($mercadopagoResponse, json_decode($payment->mercadopago_data, true));
    }

    /** @test */
    public function it_handles_mercadopago_api_failure()
    {
        Auth::login($this->user);
        
        Http::fake([
            'api.mercadopago.com/*' => Http::response(['error' => 'API Error'], 500)
        ]);
        
        $this->paymentNotificationService->shouldReceive('sendPaymentCreatedNotifications')
            ->once()
            ->andReturn(true);
            
        $this->paymentNotificationService->shouldReceive('sendPaymentLinksNotifications')
            ->once()
            ->with(Mockery::type(Payment::class), [])
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
        
        // Verificar se o pagamento foi criado mesmo com falha na API
        $this->assertDatabaseHas('payments', [
            'user_id' => $this->user->id,
            'amount' => 100.00,
            'status' => 'pending'
        ]);
    }

    /** @test */
    public function it_sends_both_payment_notifications()
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
        
        // Verificar se ambas as notificações são enviadas
        $this->paymentNotificationService->shouldReceive('sendPaymentCreatedNotifications')
            ->once()
            ->with(Mockery::type(Payment::class))
            ->andReturn(true);
            
        $this->paymentNotificationService->shouldReceive('sendPaymentLinksNotifications')
            ->once()
            ->with(Mockery::type(Payment::class), $mercadopagoResponse)
            ->andReturn(true);
        
        $this->post('/matricula', [
            'curso_id' => 1,
            'nome' => 'João Silva',
            'email' => 'joao@exemplo.com',
            'telefone' => '11999999999',
            'valor' => 100.00,
            'payment_method' => 'pix'
        ]);
    }

    /** @test */
    public function it_continues_even_if_notifications_fail()
    {
        Auth::login($this->user);
        
        $mercadopagoResponse = [
            'id' => 123456789,
            'status' => 'pending'
        ];
        
        Http::fake([
            'api.mercadopago.com/*' => Http::response($mercadopagoResponse, 200)
        ]);
        
        // Simular falha nas notificações
        $this->paymentNotificationService->shouldReceive('sendPaymentCreatedNotifications')
            ->once()
            ->andReturn(false);
            
        $this->paymentNotificationService->shouldReceive('sendPaymentLinksNotifications')
            ->once()
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
        
        // Verificar se a matrícula e pagamento foram criados mesmo com falha nas notificações
        $this->assertDatabaseHas('matriculas', [
            'user_id' => $this->user->id,
            'nome' => 'João Silva'
        ]);
        
        $this->assertDatabaseHas('payments', [
            'user_id' => $this->user->id,
            'amount' => 100.00
        ]);
    }

    /** @test */
    public function it_requires_authentication()
    {
        $response = $this->post('/matricula', [
            'curso_id' => 1,
            'nome' => 'João Silva',
            'email' => 'joao@exemplo.com',
            'telefone' => '11999999999',
            'valor' => 100.00,
            'payment_method' => 'pix'
        ]);
        
        $response->assertRedirect('/login');
    }

    /** @test */
    public function it_validates_required_fields()
    {
        Auth::login($this->user);
        
        $response = $this->post('/matricula', []);
        
        $response->assertSessionHasErrors([
            'curso_id',
            'nome',
            'email',
            'telefone',
            'valor',
            'payment_method'
        ]);
    }

    /** @test */
    public function it_validates_email_format()
    {
        Auth::login($this->user);
        
        $response = $this->post('/matricula', [
            'curso_id' => 1,
            'nome' => 'João Silva',
            'email' => 'email-invalido',
            'telefone' => '11999999999',
            'valor' => 100.00,
            'payment_method' => 'pix'
        ]);
        
        $response->assertSessionHasErrors(['email']);
    }

    /** @test */
    public function it_validates_numeric_amount()
    {
        Auth::login($this->user);
        
        $response = $this->post('/matricula', [
            'curso_id' => 1,
            'nome' => 'João Silva',
            'email' => 'joao@exemplo.com',
            'telefone' => '11999999999',
            'valor' => 'não-é-número',
            'payment_method' => 'pix'
        ]);
        
        $response->assertSessionHasErrors(['valor']);
    }

    /** @test */
    public function it_validates_payment_method()
    {
        Auth::login($this->user);
        
        $response = $this->post('/matricula', [
            'curso_id' => 1,
            'nome' => 'João Silva',
            'email' => 'joao@exemplo.com',
            'telefone' => '11999999999',
            'valor' => 100.00,
            'payment_method' => 'metodo-invalido'
        ]);
        
        $response->assertSessionHasErrors(['payment_method']);
    }

    /** @test */
    public function it_handles_different_payment_methods()
    {
        Auth::login($this->user);
        
        $paymentMethods = ['pix', 'boleto', 'credit_card'];
        
        foreach ($paymentMethods as $method) {
            $mercadopagoResponse = [
                'id' => 123456789,
                'status' => 'pending'
            ];
            
            Http::fake([
                'api.mercadopago.com/*' => Http::response($mercadopagoResponse, 200)
            ]);
            
            $this->paymentNotificationService->shouldReceive('sendPaymentCreatedNotifications')
                ->once()
                ->andReturn(true);
                
            $this->paymentNotificationService->shouldReceive('sendPaymentLinksNotifications')
                ->once()
                ->andReturn(true);
            
            $response = $this->post('/matricula', [
                'curso_id' => 1,
                'nome' => 'João Silva',
                'email' => 'joao@exemplo.com',
                'telefone' => '11999999999',
                'valor' => 100.00,
                'payment_method' => $method
            ]);
            
            $response->assertStatus(302);
            
            // Limpar para próxima iteração
            Payment::truncate();
            Matricula::truncate();
        }
    }
} 