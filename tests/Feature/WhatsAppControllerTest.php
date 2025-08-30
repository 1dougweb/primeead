<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\SystemSetting;
use App\Services\WhatsAppService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;

class WhatsAppControllerTest extends TestCase
{
    use RefreshDatabase;

    protected $adminUser;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Criar usuário admin
        $this->adminUser = User::factory()->create([
            'tipo' => 'admin',
            'email' => 'admin@test.com'
        ]);
        
        // Mock system settings
        SystemSetting::create(['key' => 'evolution_api_base_url', 'value' => 'http://localhost:8080']);
        SystemSetting::create(['key' => 'evolution_api_key', 'value' => 'test-api-key']);
        SystemSetting::create(['key' => 'evolution_api_instance', 'value' => 'test-instance']);
    }

    public function test_only_admin_can_access_whatsapp_settings()
    {
        // Usuário não logado
        $response = $this->get(route('admin.settings.whatsapp'));
        $response->assertRedirect(route('login'));

        // Usuário não-admin
        $vendorUser = User::factory()->create(['tipo' => 'vendedor']);
        $this->actingAs($vendorUser);
        
        $response = $this->get(route('admin.settings.whatsapp'));
        $response->assertStatus(403);

        // Admin pode acessar
        $this->actingAs($this->adminUser);
        $response = $this->get(route('admin.settings.whatsapp'));
        $response->assertStatus(200);
    }

    public function test_whatsapp_index_page_loads_correctly()
    {
        Http::fake([
            'localhost:8080/instance/fetchInstances' => Http::response([
                'instances' => []
            ], 200),
            'localhost:8080/instance/connectionState/test-instance' => Http::response([
                'instance' => ['state' => 'close']
            ], 200)
        ]);

        $this->actingAs($this->adminUser);
        
        $response = $this->get(route('admin.settings.whatsapp'));
        
        $response->assertStatus(200);
        $response->assertViewIs('admin.settings.whatsapp');
        $response->assertViewHas(['settings', 'connectionStatus', 'instanceExists']);
    }

    public function test_can_save_whatsapp_settings()
    {
        $this->actingAs($this->adminUser);
        
        $data = [
            'base_url' => 'http://localhost:8080',
            'api_key' => 'new-api-key',
            'instance' => 'new-instance'
        ];
        
        $response = $this->post(route('admin.settings.whatsapp.save'), $data);
        
        $response->assertRedirect();
        $response->assertSessionHas('success');
        
        $this->assertEquals('new-api-key', SystemSetting::get('evolution_api_key'));
        $this->assertEquals('new-instance', SystemSetting::get('evolution_api_instance'));
    }

    public function test_create_instance_endpoint()
    {
        Http::fake([
            'localhost:8080/instance/fetchInstances' => Http::response([
                'instances' => []
            ], 200),
            'localhost:8080/instance/create' => Http::response([
                'instance' => ['instanceName' => 'test-instance'],
                'qrcode' => ['base64' => 'data:image/png;base64,test']
            ], 200)
        ]);

        $this->actingAs($this->adminUser);
        
        $response = $this->post(route('admin.settings.whatsapp.create-instance'));
        
        $response->assertJson(['success' => true]);
        $response->assertJsonStructure(['success', 'data']);
    }

    public function test_check_instance_endpoint()
    {
        Http::fake([
            'localhost:8080/instance/fetchInstances' => Http::response([
                'instances' => [
                    ['instanceName' => 'test-instance', 'status' => 'open']
                ]
            ], 200)
        ]);

        $this->actingAs($this->adminUser);
        
        $response = $this->get(route('admin.settings.whatsapp.check-instance'));
        
        $response->assertJson(['success' => true, 'exists' => true]);
    }

    public function test_qr_code_endpoint()
    {
        Http::fake([
            'localhost:8080/instance/connect/test-instance' => Http::response([
                'qrcode' => [
                    'base64' => 'data:image/png;base64,test-qr-code'
                ]
            ], 200)
        ]);

        $this->actingAs($this->adminUser);
        
        $response = $this->get(route('admin.settings.whatsapp.qr-code'));
        
        $response->assertJson(['success' => true]);
        $response->assertJsonPath('data.qrcode.base64', 'data:image/png;base64,test-qr-code');
    }

    public function test_connection_status_endpoint()
    {
        Http::fake([
            'localhost:8080/instance/connectionState/test-instance' => Http::response([
                'instance' => ['state' => 'open']
            ], 200)
        ]);

        $this->actingAs($this->adminUser);
        
        $response = $this->get(route('admin.settings.whatsapp.status'));
        
        $response->assertJson(['success' => true]);
        $response->assertJsonPath('data.connected', true);
        $response->assertJsonPath('data.state', 'open');
    }

    public function test_test_message_endpoint()
    {
        Http::fake([
            'localhost:8080/message/sendText/test-instance' => Http::response([
                'message' => 'sent'
            ], 200)
        ]);

        $this->actingAs($this->adminUser);
        
        $data = [
            'phone' => '(11) 99999-9999',
            'message' => 'Test message'
        ];
        
        $response = $this->post(route('admin.settings.whatsapp.test-message'), $data);
        
        $response->assertJson(['success' => true]);
    }

    public function test_disconnect_endpoint()
    {
        Http::fake([
            'localhost:8080/instance/logout/test-instance' => Http::response([
                'status' => 'disconnected'
            ], 200)
        ]);

        $this->actingAs($this->adminUser);
        
        $response = $this->post(route('admin.settings.whatsapp.disconnect'));
        
        $response->assertJson(['success' => true]);
    }

    public function test_delete_instance_endpoint()
    {
        Http::fake([
            'localhost:8080/instance/delete/test-instance' => Http::response([
                'message' => 'Instance deleted'
            ], 200)
        ]);

        $this->actingAs($this->adminUser);
        
        $response = $this->delete(route('admin.settings.whatsapp.delete-instance'));
        
        $response->assertJson(['success' => true]);
    }

    public function test_reconnect_endpoint()
    {
        Http::fake([
            'localhost:8080/instance/logout/test-instance' => Http::response([
                'status' => 'disconnected'
            ], 200),
            'localhost:8080/instance/connect/test-instance' => Http::response([
                'qrcode' => [
                    'base64' => 'data:image/png;base64,new-qr-code'
                ]
            ], 200)
        ]);

        $this->actingAs($this->adminUser);
        
        $response = $this->post(route('admin.settings.whatsapp.reconnect'));
        
        $response->assertJson(['success' => true]);
    }

    public function test_validation_errors_are_handled()
    {
        $this->actingAs($this->adminUser);
        
        // Tentar salvar com dados inválidos
        $response = $this->post(route('admin.settings.whatsapp.save'), [
            'base_url' => 'invalid-url',
            'api_key' => '',
            'instance' => ''
        ]);
        
        $response->assertSessionHasErrors(['base_url', 'api_key', 'instance']);
    }

    public function test_api_errors_are_handled_gracefully()
    {
        Http::fake([
            'localhost:8080/*' => Http::response([], 500)
        ]);

        $this->actingAs($this->adminUser);
        
        $response = $this->post(route('admin.settings.whatsapp.create-instance'));
        
        $response->assertJson(['success' => false]);
        $response->assertJsonHasKey('error');
    }
} 