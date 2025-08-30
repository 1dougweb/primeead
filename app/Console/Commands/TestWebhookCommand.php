<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\SystemSetting;

class TestWebhookCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'webhook:test {--type=order} {--id=123}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test Mercado Pago webhook endpoint';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $type = $this->option('type');
        $id = $this->option('id');
        
        $this->info("Testing webhook for type: {$type}, id: {$id}");
        
        // Get webhook URL
        $webhookUrl = url('/webhook/mercadopago');
        $this->info("Webhook URL: {$webhookUrl}");
        
        // Create test webhook data
        $webhookData = [
            'type' => $type,
            'data' => [
                'id' => $id
            ],
            'action' => 'updated'
        ];
        
        // Get webhook secret for signature
        $settings = SystemSetting::getPaymentSettings();
        $webhookSecret = $settings['mercadopago_webhook_secret'] ?? '';
        
        if (empty($webhookSecret)) {
            $this->warn('Webhook secret not configured - signature validation will fail');
        }
        
        // Generate signature if secret is available
        $headers = [
            'Content-Type' => 'application/json',
            'x-request-id' => 'test-' . uniqid(),
            'x-signature' => 'ts=' . time() . ',v1=' . uniqid()
        ];
        
        if (!empty($webhookSecret)) {
            $requestId = $headers['x-request-id'];
            $ts = time();
            $signatureString = "id:{$id};request-id:{$requestId};ts:{$ts};";
            $v1 = hash_hmac('sha256', $signatureString, $webhookSecret);
            $headers['x-signature'] = "ts={$ts},v1={$v1}";
        }
        
        $this->info('Sending test webhook...');
        $this->info('Headers: ' . json_encode($headers, JSON_PRETTY_PRINT));
        $this->info('Data: ' . json_encode($webhookData, JSON_PRETTY_PRINT));
        
        try {
            $response = Http::withHeaders($headers)
                ->post($webhookUrl, $webhookData);
            
            $this->info("Response Status: {$response->status()}");
            $this->info("Response Body: " . $response->body());
            
            if ($response->successful()) {
                $this->info('✅ Webhook test successful!');
            } else {
                $this->error('❌ Webhook test failed!');
            }
            
        } catch (\Exception $e) {
            $this->error("Error testing webhook: " . $e->getMessage());
        }
        
        return 0;
    }
}
