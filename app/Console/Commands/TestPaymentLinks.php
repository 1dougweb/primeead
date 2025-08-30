<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Payment;
use App\Models\Matricula;

class TestPaymentLinks extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'payment:test-links {email?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Testar funcionalidade de links de pagamento para parcelas vencidas';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $email = $this->argument('email') ?? 'contato@ensinocerto.com.br';
        
        $this->info('🧪 Testando funcionalidade de links de pagamento...');
        $this->info("📧 Email: {$email}");
        
        try {
            // Buscar matrículas do usuário
            $matriculas = Matricula::where('email', $email)->get();
            
            if ($matriculas->isEmpty()) {
                $this->warn("⚠️  Nenhuma matrícula encontrada para o email: {$email}");
                return 0;
            }
            
            $this->info("✅ Encontradas " . $matriculas->count() . " matrícula(s)");
            
            foreach ($matriculas as $matricula) {
                $this->info("\n📚 Matrícula ID: {$matricula->id}");
                $this->info("   Nome: {$matricula->nome_completo}");
                $this->info("   Curso: {$matricula->curso}");
                $this->info("   Status: {$matricula->status}");
                
                // Buscar pagamentos da matrícula
                $payments = $matricula->payments()->orderBy('data_vencimento', 'desc')->get();
                
                if ($payments->isEmpty()) {
                    $this->warn("   ⚠️  Nenhum pagamento encontrado");
                    continue;
                }
                
                $this->info("   💰 Total de pagamentos: " . $payments->count());
                
                $pendingCount = 0;
                $overdueCount = 0;
                $paidCount = 0;
                
                foreach ($payments as $payment) {
                    $isOverdue = $payment->data_vencimento < now() && $payment->status === 'pending';
                    $statusIcon = $isOverdue ? '🔴' : ($payment->status === 'paid' ? '🟢' : '🟠');
                    $overdueInfo = $isOverdue ? " (VENCIDO há " . now()->diffInDays($payment->data_vencimento) . " dia(s))" : "";
                    
                    $this->info("      {$statusIcon} ID {$payment->id}: {$payment->descricao} - R$ " . number_format($payment->valor, 2, ',', '.'));
                    $this->info("         Vence: {$payment->data_vencimento->format('d/m/Y')} - Status: {$payment->status}{$overdueInfo}");
                    
                    if ($isOverdue) {
                        $overdueCount++;
                        $this->info("         🔗 Link de pagamento: " . $this->generatePaymentLink($payment));
                    }
                    
                    switch ($payment->status) {
                        case 'pending':
                            $pendingCount++;
                            break;
                        case 'paid':
                            $paidCount++;
                            break;
                    }
                }
                
                $this->info("\n   📊 Resumo:");
                $this->info("      🟢 Pagos: {$paidCount}");
                $this->info("      🟠 Pendentes: {$pendingCount}");
                $this->info("      🔴 Vencidos: {$overdueCount}");
                
                if ($overdueCount > 0) {
                    $this->warn("   ⚠️  ATENÇÃO: {$overdueCount} parcela(s) vencida(s)!");
                    $this->info("   🔗 Links de pagamento disponíveis para regularização");
                }
            }
            
            $this->info("\n🎉 Teste concluído com sucesso!");
            return 0;
            
        } catch (\Exception $e) {
            $this->error("❌ Erro durante o teste: " . $e->getMessage());
            $this->error("Stack trace: " . $e->getTraceAsString());
            return 1;
        }
    }
    
    /**
     * Gerar link de pagamento para um pagamento específico
     */
    protected function generatePaymentLink($payment): string
    {
        try {
            // Verificar se o pagamento está vencido e pendente
            if ($payment->data_vencimento >= now() || $payment->status !== 'pending') {
                return "N/A (não elegível)";
            }
            
            // Usar a mesma lógica do ChatService para gerar links do Mercado Pago
            $paymentType = $this->mapPaymentType($payment->forma_pagamento);
            
            switch ($paymentType) {
                case 'boleto':
                    return $this->generateMercadoPagoBoletoLink($payment);
                    
                case 'pix':
                    return $this->generateMercadoPagoPixLink($payment);
                    
                case 'cartao':
                    return $this->generateMercadoPagoCardLink($payment);
                    
                default:
                    return $this->generateMercadoPagoGenericLink($payment);
            }
            
        } catch (\Exception $e) {
            return "Erro ao gerar link: " . $e->getMessage();
        }
    }
    
    /**
     * Mapear forma de pagamento para tipo do Mercado Pago
     */
    protected function mapPaymentType(string $formaPagamento): string
    {
        $mapping = [
            'boleto' => 'boleto',
            'cartao_credito' => 'cartao',
            'cartao_debito' => 'cartao',
            'cartao' => 'cartao',
            'pix' => 'pix',
            'pagamento_a_vista' => 'boleto',
            'pagamento_parcelado' => 'cartao'
        ];
        
        return $mapping[$formaPagamento] ?? 'boleto';
    }
    
    /**
     * Gerar link do Mercado Pago para boleto
     */
    protected function generateMercadoPagoBoletoLink($payment): string
    {
        try {
            // Chamar a API para gerar o link real do Mercado Pago
            $response = \Illuminate\Support\Facades\Http::get(config('app.url') . '/api/mercadopago/payment-link', [
                'payment_id' => $payment->id,
                'email' => $payment->matricula->email
            ]);
            
            if ($response->successful()) {
                $data = $response->json();
                return $data['payment_link'] ?? 'Link não disponível';
            }
            
            throw new \Exception('Erro ao gerar link do Mercado Pago');
            
        } catch (\Exception $e) {
            return 'Erro: ' . $e->getMessage();
        }
    }
    
    /**
     * Gerar link do Mercado Pago para PIX
     */
    protected function generateMercadoPagoPixLink($payment): string
    {
        try {
            // Chamar a API para gerar o link real do Mercado Pago
            $response = \Illuminate\Support\Facades\Http::get(config('app.url') . '/api/mercadopago/payment-link', [
                'payment_id' => $payment->id,
                'email' => $payment->matricula->email
            ]);
            
            if ($response->successful()) {
                $data = $response->json();
                return $data['payment_link'] ?? 'Link não disponível';
            }
            
            throw new \Exception('Erro ao gerar link do Mercado Pago');
            
        } catch (\Exception $e) {
            return 'Erro: ' . $e->getMessage();
        }
    }
    
    /**
     * Gerar link do Mercado Pago para cartão
     */
    protected function generateMercadoPagoCardLink($payment): string
    {
        try {
            // Chamar a API para gerar o link real do Mercado Pago
            $response = \Illuminate\Support\Facades\Http::get(config('app.url') . '/api/mercadopago/payment-link', [
                'payment_id' => $payment->id,
                'email' => $payment->matricula->email
            ]);
            
            if ($response->successful()) {
                $data = $response->json();
                return $data['payment_link'] ?? 'Link não disponível';
            }
            
            throw new \Exception('Erro ao gerar link do Mercado Pago');
            
        } catch (\Exception $e) {
            return 'Erro: ' . $e->getMessage();
        }
    }
    
    /**
     * Gerar link genérico do Mercado Pago
     */
    protected function generateMercadoPagoGenericLink($payment): string
    {
        try {
            // Chamar a API para gerar o link real do Mercado Pago
            $response = \Illuminate\Support\Facades\Http::get(config('app.url') . '/api/mercadopago/payment-link', [
                'payment_id' => $payment->id,
                'email' => $payment->matricula->email
            ]);
            
            if ($response->successful()) {
                $data = $response->json();
                return $data['payment_link'] ?? 'Link não disponível';
            }
            
            throw new \Exception('Erro ao gerar link do Mercado Pago');
            
        } catch (\Exception $e) {
            return 'Erro: ' . $e->getMessage();
        }
    }
}
