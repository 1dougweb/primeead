<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Services\WhatsAppService;
use App\Models\SystemSetting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Mockery;

class WhatsAppServiceTest extends TestCase
{
    protected $whatsappService;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Mockar SystemSetting facade
        SystemSetting::partialMock()
            ->shouldReceive('get')
            ->with('evolution_api_base_url', '')
            ->andReturn('http://localhost:8080')
            ->shouldReceive('get')
            ->with('evolution_api_key', '')
            ->andReturn('test-api-key')
            ->shouldReceive('get')
            ->with('evolution_api_instance', 'default')
            ->andReturn('test-instance')
            ->shouldReceive('set')
            ->andReturn(true);
        
        $this->whatsappService = new WhatsAppService();
    }

    public function test_has_valid_settings_returns_true_when_all_settings_present()
    {
        $this->assertTrue($this->whatsappService->hasValidSettings());
    }

    public function test_has_valid_settings_returns_false_when_settings_missing()
    {
        SystemSetting::partialMock()
            ->shouldReceive('get')
            ->with('evolution_api_base_url', '')
            ->andReturn('')
            ->shouldReceive('get')
            ->with('evolution_api_key', '')
            ->andReturn('test-api-key')
            ->shouldReceive('get')
            ->with('evolution_api_instance', 'default')
            ->andReturn('test-instance');
        
        $service = new WhatsAppService();
        $this->assertFalse($service->hasValidSettings());
    }

    public function test_instance_exists_returns_true_when_instance_found()
    {
        Http::fake([
            'localhost:8080/instance/fetchInstances' => Http::response([
                'instances' => [
                    ['instanceName' => 'test-instance', 'status' => 'open'],
                    ['instanceName' => 'other-instance', 'status' => 'close']
                ]
            ], 200)
        ]);

        $this->assertTrue($this->whatsappService->instanceExists());
    }

    public function test_instance_exists_returns_false_when_instance_not_found()
    {
        Http::fake([
            'localhost:8080/instance/fetchInstances' => Http::response([
                'instances' => [
                    ['instanceName' => 'other-instance', 'status' => 'open']
                ]
            ], 200)
        ]);

        $this->assertFalse($this->whatsappService->instanceExists());
    }

    public function test_create_instance_success()
    {
        Http::fake([
            'localhost:8080/instance/fetchInstances' => Http::response([
                'instances' => []
            ], 200),
            'localhost:8080/instance/create' => Http::response([
                'instance' => ['instanceName' => 'test-instance'],
                'qrcode' => ['base64' => 'data:image/png;base64,test-qr-code']
            ], 200)
        ]);

        $result = $this->whatsappService->createInstance();
        
        $this->assertArrayHasKey('instance', $result);
        $this->assertEquals('test-instance', $result['instance']['instanceName']);
    }

    public function test_create_instance_already_exists()
    {
        Http::fake([
            'localhost:8080/instance/fetchInstances' => Http::response([
                'instances' => [
                    ['instanceName' => 'test-instance', 'status' => 'open']
                ]
            ], 200)
        ]);

        $result = $this->whatsappService->createInstance();
        
        $this->assertArrayHasKey('message', $result);
        $this->assertStringContains('já existe', $result['message']);
    }

    public function test_get_qr_code_returns_qr_when_available()
    {
        Http::fake([
            'localhost:8080/instance/connect/test-instance' => Http::response([
                'qrcode' => [
                    'base64' => 'data:image/png;base64,test-qr-code'
                ]
            ], 200)
        ]);

        $result = $this->whatsappService->getQrCode();
        
        $this->assertArrayHasKey('qrcode', $result);
        $this->assertEquals('data:image/png;base64,test-qr-code', $result['qrcode']['base64']);
    }

    public function test_get_qr_code_returns_connected_when_already_connected()
    {
        Http::fake([
            'localhost:8080/instance/connect/test-instance' => Http::response([
                'instance' => ['state' => 'open']
            ], 200)
        ]);

        $result = $this->whatsappService->getQrCode();
        
        $this->assertTrue($result['connected']);
        $this->assertEquals('Dispositivo já conectado', $result['message']);
    }

    public function test_get_connection_status_returns_connected()
    {
        Http::fake([
            'localhost:8080/instance/connectionState/test-instance' => Http::response([
                'instance' => ['state' => 'open']
            ], 200)
        ]);

        $result = $this->whatsappService->getConnectionStatus();
        
        $this->assertTrue($result['connected']);
        $this->assertEquals('open', $result['state']);
    }

    public function test_send_message_formats_phone_and_sends()
    {
        Http::fake([
            'localhost:8080/message/sendText/test-instance' => Http::response([
                'message' => 'sent'
            ], 200)
        ]);

        $result = $this->whatsappService->sendMessage('(11) 99999-9999', 'Test message');
        
        Http::assertSent(function ($request) {
            $body = json_decode($request->body(), true);
            return $body['number'] === '5511999999999' && 
                   $body['textMessage']['text'] === 'Test message';
        });
        
        $this->assertArrayHasKey('message', $result);
    }

    public function test_format_phone_converts_brazilian_format()
    {
        $reflection = new \ReflectionClass($this->whatsappService);
        $method = $reflection->getMethod('formatPhone');
        $method->setAccessible(true);

        $formatted = $method->invoke($this->whatsappService, '(11) 99999-9999');
        $this->assertEquals('5511999999999', $formatted);

        $formatted = $method->invoke($this->whatsappService, '11999999999');
        $this->assertEquals('5511999999999', $formatted);

        $formatted = $method->invoke($this->whatsappService, '+5511999999999');
        $this->assertEquals('5511999999999', $formatted);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
} 