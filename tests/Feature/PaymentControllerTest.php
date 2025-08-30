<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Payment;
use App\Models\PaymentNotification;
use App\Services\PaymentNotificationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Mockery;

class PaymentControllerTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $payment;
    protected $paymentNotificationService;

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
            'amount' => 100.00,
            'description' => 'Pagamento de teste',
            'status' => 'pending'
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
    public function it_can_create_payment_with_notifications()
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
            ->with(Mockery::type(Payment::class), $mercadopagoResponse)
            ->andReturn(true);
        
        $response = $this->post('/payments', [
            'amount' => 150.00,
            'description' => 'Novo pagamento',
            'payment_method' => 'pix'
        ]);
        
        $response->assertStatus(302);
        
        $this->assertDatabaseHas('payments', [
            'user_id' => $this->user->id,
            'amount' => 150.00,
            'description' => 'Novo pagamento',
            'status' => 'pending'
        ]);
    }

    /** @test */
    public function it_stores_mercadopago_data_in_payment_update()
    {
        Auth::login($this->user);
        
        $mercadopagoResponse = [
            'id' => 123456789,
            'status' => 'pending',
            'transaction_details' => [
                'external_resource_url' => 'https://www.mercadopago.com.br/payments/123456789/ticket?caller_id=123456&hash=abc123'
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
        
        $this->post('/payments', [
            'amount' => 150.00,
            'description' => 'Novo pagamento',
            'payment_method' => 'boleto'
        ]);
        
        $payment = Payment::where('user_id', $this->user->id)
            ->where('amount', 150.00)
            ->first();
        
        $this->assertNotNull($payment->mercadopago_data);
        $this->assertEquals($mercadopagoResponse, json_decode($payment->mercadopago_data, true));
    }

    /** @test */
    public function it_can_update_payment_with_new_notifications()
    {
        Auth::login($this->user);
        
        $mercadopagoResponse = [
            'id' => 123456789,
            'status' => 'approved',
            'point_of_interaction' => [
                'transaction_data' => [
                    'ticket_url' => 'https://www.mercadopago.com.br/checkout/v1/redirect?pref_id=123456789'
                ]
            ]
        ];
        
        Http::fake([
            'api.mercadopago.com/*' => Http::response($mercadopagoResponse, 200)
        ]);
        
        $this->paymentNotificationService->shouldReceive('sendPaymentLinksNotifications')
            ->once()
            ->with(Mockery::type(Payment::class), $mercadopagoResponse)
            ->andReturn(true);
        
        $response = $this->put("/payments/{$this->payment->id}", [
            'amount' => 200.00,
            'description' => 'Pagamento atualizado',
            'status' => 'approved'
        ]);
        
        $response->assertStatus(302);
        
        $this->assertDatabaseHas('payments', [
            'id' => $this->payment->id,
            'amount' => 200.00,
            'description' => 'Pagamento atualizado',
            'status' => 'approved'
        ]);
    }

    /** @test */
    public function it_handles_payment_status_updates()
    {
        Auth::login($this->user);
        
        $mercadopagoResponse = [
            'id' => 123456789,
            'status' => 'approved'
        ];
        
        Http::fake([
            'api.mercadopago.com/*' => Http::response($mercadopagoResponse, 200)
        ]);
        
        $this->paymentNotificationService->shouldReceive('sendPaymentLinksNotifications')
            ->once()
            ->andReturn(true);
        
        $response = $this->put("/payments/{$this->payment->id}", [
            'status' => 'approved'
        ]);
        
        $response->assertStatus(302);
        
        $this->assertDatabaseHas('payments', [
            'id' => $this->payment->id,
            'status' => 'approved'
        ]);
    }

    /** @test */
    public function it_can_show_payment_details()
    {
        Auth::login($this->user);
        
        $response = $this->get("/payments/{$this->payment->id}");
        
        $response->assertStatus(200);
        $response->assertViewHas('payment', $this->payment);
    }

    /** @test */
    public function it_can_list_user_payments()
    {
        Auth::login($this->user);
        
        // Criar alguns pagamentos adicionais
        Payment::factory()->count(3)->create([
            'user_id' => $this->user->id
        ]);
        
        $response = $this->get('/payments');
        
        $response->assertStatus(200);
        $response->assertViewHas('payments');
        
        $payments = $response->viewData('payments');
        $this->assertEquals(4, $payments->count()); // 1 do setUp + 3 criados
    }

    /** @test */
    public function it_prevents_unauthorized_access_to_other_users_payments()
    {
        $otherUser = User::factory()->create();
        $otherPayment = Payment::factory()->create([
            'user_id' => $otherUser->id
        ]);
        
        Auth::login($this->user);
        
        $response = $this->get("/payments/{$otherPayment->id}");
        
        $response->assertStatus(403);
    }

    /** @test */
    public function it_prevents_unauthorized_updates_to_other_users_payments()
    {
        $otherUser = User::factory()->create();
        $otherPayment = Payment::factory()->create([
            'user_id' => $otherUser->id
        ]);
        
        Auth::login($this->user);
        
        $response = $this->put("/payments/{$otherPayment->id}", [
            'amount' => 999.99
        ]);
        
        $response->assertStatus(403);
        
        // Verificar que o pagamento não foi alterado
        $this->assertDatabaseMissing('payments', [
            'id' => $otherPayment->id,
            'amount' => 999.99
        ]);
    }

    /** @test */
    public function it_requires_authentication_for_payment_actions()
    {
        $response = $this->get('/payments');
        $response->assertRedirect('/login');
        
        $response = $this->get("/payments/{$this->payment->id}");
        $response->assertRedirect('/login');
        
        $response = $this->post('/payments', []);
        $response->assertRedirect('/login');
        
        $response = $this->put("/payments/{$this->payment->id}", []);
        $response->assertRedirect('/login');
    }

    /** @test */
    public function it_validates_payment_creation_data()
    {
        Auth::login($this->user);
        
        $response = $this->post('/payments', []);
        
        $response->assertSessionHasErrors([
            'amount',
            'description',
            'payment_method'
        ]);
    }

    /** @test */
    public function it_validates_payment_update_data()
    {
        Auth::login($this->user);
        
        $response = $this->put("/payments/{$this->payment->id}", [
            'amount' => 'não-é-número',
            'status' => 'status-inválido'
        ]);
        
        $response->assertSessionHasErrors([
            'amount',
            'status'
        ]);
    }

    /** @test */
    public function it_handles_mercadopago_api_errors_gracefully()
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
        
        $response = $this->post('/payments', [
            'amount' => 150.00,
            'description' => 'Novo pagamento',
            'payment_method' => 'pix'
        ]);
        
        $response->assertStatus(302);
        
        // Pagamento deve ser criado mesmo com erro na API do MP
        $this->assertDatabaseHas('payments', [
            'user_id' => $this->user->id,
            'amount' => 150.00,
            'description' => 'Novo pagamento'
        ]);
    }

    /** @test */
    public function it_continues_processing_even_if_notifications_fail()
    {
        Auth::login($this->user);
        
        Http::fake([
            'api.mercadopago.com/*' => Http::response(['id' => 123456789], 200)
        ]);
        
        $this->paymentNotificationService->shouldReceive('sendPaymentCreatedNotifications')
            ->once()
            ->andReturn(false);
            
        $this->paymentNotificationService->shouldReceive('sendPaymentLinksNotifications')
            ->once()
            ->andReturn(false);
        
        $response = $this->post('/payments', [
            'amount' => 150.00,
            'description' => 'Novo pagamento',
            'payment_method' => 'pix'
        ]);
        
        $response->assertStatus(302);
        
        // Pagamento deve ser criado mesmo com falha nas notificações
        $this->assertDatabaseHas('payments', [
            'user_id' => $this->user->id,
            'amount' => 150.00,
            'description' => 'Novo pagamento'
        ]);
    }

    /** @test */
    public function it_can_delete_payment()
    {
        Auth::login($this->user);
        
        $response = $this->delete("/payments/{$this->payment->id}");
        
        $response->assertStatus(302);
        
        $this->assertDatabaseMissing('payments', [
            'id' => $this->payment->id
        ]);
    }

    /** @test */
    public function it_prevents_deleting_other_users_payments()
    {
        $otherUser = User::factory()->create();
        $otherPayment = Payment::factory()->create([
            'user_id' => $otherUser->id
        ]);
        
        Auth::login($this->user);
        
        $response = $this->delete("/payments/{$otherPayment->id}");
        
        $response->assertStatus(403);
        
        // Verificar que o pagamento não foi deletado
        $this->assertDatabaseHas('payments', [
            'id' => $otherPayment->id
        ]);
    }
} 