<?php

namespace App\Jobs;

use App\Models\EmailCampaign;
use App\Models\EmailCampaignRecipient;
use App\Mail\InscricaoMail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class SendCampaignEmail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;
    public $timeout = 30;
    public $backoff = [10, 30, 60]; // Retry delays in seconds

    protected $campaign;
    protected $recipient;

    /**
     * Create a new job instance.
     */
    public function __construct(EmailCampaign $campaign, EmailCampaignRecipient $recipient)
    {
        $this->campaign = $campaign;
        $this->recipient = $recipient;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            // Log do conteúdo original da campanha
            Log::info("Conteúdo original da campanha:", [
                'campaign_id' => $this->campaign->id,
                'recipient_id' => $this->recipient->id,
                'content' => $this->campaign->content,
                'subject' => $this->campaign->subject
            ]);

            // Verificar se o email ainda deve ser enviado
            if ($this->recipient->status !== 'pending') {
                Log::info("Email já processado para {$this->recipient->email}");
                return;
            }

            // Carregar configurações de email do banco de dados
            $settings = $this->configureMailSettings();
            
            if (!$settings) {
                throw new \Exception('Configurações de email não encontradas. Por favor, configure o email nas configurações do sistema.');
            }

            // Marcar como processando
            $this->recipient->update([
                'status' => 'sending',
                'sent_at' => now()
            ]);

            // Preparar dados para o template
            $customFields = $this->recipient->custom_fields ?? [];
            $data = [
                'nome' => $this->recipient->name,
                'email' => $this->recipient->email,
                'telefone' => $customFields['telefone'] ?? '',
                'curso' => $customFields['curso'] ?? '',
                'modalidade' => $customFields['modalidade'] ?? '',
                'campanha' => $this->campaign->name,
                'content' => $this->processEmailContent($this->campaign->content, [
                    'nome' => $this->recipient->name,
                    'email' => $this->recipient->email,
                    'telefone' => $customFields['telefone'] ?? '',
                    'curso' => $customFields['curso'] ?? '',
                    'modalidade' => $customFields['modalidade'] ?? '',
                    'campanha' => $this->campaign->name,
                    'tracking_code' => $this->recipient->tracking_code,
                    'unsubscribe_url' => route('admin.email-campaigns.unsubscribe', $this->recipient->tracking_code)
                ]),
                'trackingCode' => $this->recipient->tracking_code,
                'unsubscribe_url' => route('admin.email-campaigns.unsubscribe', $this->recipient->tracking_code)
            ];

            // Aguardar 10 segundos se houver muitos emails na fila
            if ($this->campaign->pending_count > 10) {
                sleep(10);
            }

            // Enviar email
            Mail::html($data['content'], function ($message) use ($data) {
                $message->to($this->recipient->email, $this->recipient->name)
                        ->subject($this->campaign->subject);
            });

            // Marcar como enviado com sucesso
            $this->recipient->update([
                'status' => 'sent',
                'sent_at' => now(),
                'error_message' => null
            ]);

            // Atualizar contadores da campanha
            $this->updateCampaignCounters();

            Log::info("Email enviado com sucesso para {$this->recipient->email}");

        } catch (\Exception $e) {
            Log::error("Erro ao enviar email para {$this->recipient->email}: " . $e->getMessage());
            
            // Marcar como erro
            $this->recipient->update([
                'status' => 'failed',
                'error_message' => $e->getMessage()
            ]);

            // Re-throw para que o Laravel tente novamente
            throw $e;
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error("Falha definitiva no envio de email para {$this->recipient->email}: " . $exception->getMessage());
        
        $this->recipient->update([
            'status' => 'failed',
            'error_message' => $exception->getMessage()
        ]);

        $this->updateCampaignCounters();
    }

    /**
     * Processar conteúdo do email substituindo variáveis
     */
    private function processEmailContent($content, $data)
    {
        // Garantir que o conteúdo não está com entidades HTML
        $content = html_entity_decode($content, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        
        // Log do conteúdo original
        Log::info("Conteúdo original da campanha:", ['content' => substr($content, 0, 200)]);
        
        // First replace variables in the content
        $content = str_replace('{{nome}}', $data['nome'], $content);
        $content = str_replace('{{email}}', $data['email'], $content);
        $content = str_replace('{{telefone}}', $data['telefone'], $content);
        $content = str_replace('{{curso}}', $data['curso'], $content);
        $content = str_replace('{{modalidade}}', $data['modalidade'], $content);
        $content = str_replace('{{campanha}}', $data['campanha'], $content);
        
        // Log do conteúdo após substituições
        Log::info("Conteúdo após substituições:", ['content' => substr($content, 0, 200)]);
        
        // Adicionar pixel de rastreamento e link de cancelamento de inscrição
        $trackingPixel = '<img src="' . route('admin.email-campaigns.track-open', ['trackingCode' => $data['tracking_code']]) . '" alt="" style="display:none" />';
        $unsubscribeLink = '<div style="text-align: center; margin-top: 20px; font-size: 12px; color: #666;">Se não deseja mais receber nossos emails, <a href="' . route('admin.email-campaigns.unsubscribe', ['trackingCode' => $data['tracking_code']]) . '" style="color: #666; text-decoration: underline;">clique aqui</a>.</div>';

        // Inserir pixel de rastreamento antes do </body>
        $content = str_replace('</body>', $trackingPixel . "\n" . $unsubscribeLink . "\n</body>", $content);
        
        // Log do conteúdo final
        Log::info("Conteúdo final:", ['content' => substr($content, 0, 300) . '...']);
        
        return $content;
    }

    /**
     * Configurar as configurações de email a partir do banco de dados
     */
    private function configureMailSettings()
    {
        try {
            $settings = \App\Models\SystemSetting::whereIn('key', [
                'mail_mailer', 'mail_host', 'mail_port', 'mail_username', 
                'mail_password', 'mail_encryption', 'mail_from_address', 'mail_from_name'
            ])->pluck('value', 'key');

            if ($settings->isEmpty()) {
                return false;
            }

            // Configurar o mailer temporariamente
            config([
                'mail.default' => $settings->get('mail_mailer', 'smtp'),
                'mail.mailers.smtp.host' => $settings->get('mail_host'),
                'mail.mailers.smtp.port' => $settings->get('mail_port'),
                'mail.mailers.smtp.encryption' => $settings->get('mail_encryption'),
                'mail.mailers.smtp.username' => $settings->get('mail_username'),
                'mail.mailers.smtp.password' => $settings->get('mail_password'),
                'mail.from.address' => $settings->get('mail_from_address'),
                'mail.from.name' => $settings->get('mail_from_name'),
            ]);

            Log::info('Configurações de email carregadas do banco de dados', [
                'host' => $settings->get('mail_host'),
                'port' => $settings->get('mail_port'),
                'from' => $settings->get('mail_from_address')
            ]);

            return $settings;
        } catch (\Exception $e) {
            Log::error('Erro ao carregar configurações de email: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Atualizar contadores da campanha
     */
    private function updateCampaignCounters()
    {
        $campaign = $this->campaign->fresh();
        
        $stats = $campaign->recipients()
            ->selectRaw('
                COUNT(*) as total,
                SUM(CASE WHEN status = "sent" THEN 1 ELSE 0 END) as sent_count,
                SUM(CASE WHEN status = "failed" THEN 1 ELSE 0 END) as failed_count,
                SUM(CASE WHEN status = "pending" OR status = "sending" THEN 1 ELSE 0 END) as pending_count,
                SUM(CASE WHEN opened_at IS NOT NULL THEN 1 ELSE 0 END) as opened_count,
                SUM(CASE WHEN clicked_at IS NOT NULL THEN 1 ELSE 0 END) as clicked_count
            ')
            ->first();

        $campaign->update([
            'total_recipients' => $stats->total,
            'sent_count' => $stats->sent_count,
            'failed_count' => $stats->failed_count,
            'pending_count' => $stats->pending_count,
            'opened_count' => $stats->opened_count,
            'clicked_count' => $stats->clicked_count
        ]);

        // Se todos foram processados, marcar campanha como concluída
        if ($stats->pending_count == 0) {
            $campaign->update([
                'status' => 'completed',
                'completed_at' => now()
            ]);
        }
    }
}
