<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\EmailCampaign;
use App\Jobs\SendCampaignEmail;

class FixStuckCampaigns extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'campaigns:fix-stuck {--dry-run : Show what would be fixed without making changes}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fix campaigns that are stuck in sending state';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Verificando campanhas presas...');
        
        // Encontrar campanhas que estão "enviando" há mais de 1 hora
        $stuckCampaigns = EmailCampaign::where('status', 'sending')
            ->where('started_at', '<', now()->subHour())
            ->get();
            
        // Encontrar campanhas com status antigo "enviando"
        $oldStatusCampaigns = EmailCampaign::where('status', 'enviando')->get();
        
        $allStuckCampaigns = $stuckCampaigns->merge($oldStatusCampaigns);
        
        if ($allStuckCampaigns->isEmpty()) {
            $this->info('✅ Nenhuma campanha presa encontrada.');
            return 0;
        }
        
        $this->warn("🔍 Encontradas {$allStuckCampaigns->count()} campanhas presas:");
        
        foreach ($allStuckCampaigns as $campaign) {
            $this->line("  - ID: {$campaign->id} | {$campaign->name} | Status: {$campaign->status}");
            
            // Verificar status dos destinatários
            $pendingCount = $campaign->recipients()->where('status', 'pending')->count();
            $sendingCount = $campaign->recipients()->where('status', 'sending')->count();
            $sentCount = $campaign->recipients()->where('status', 'sent')->count();
            $failedCount = $campaign->recipients()->where('status', 'failed')->count();
            
            $this->line("    Pending: {$pendingCount} | Sending: {$sendingCount} | Sent: {$sentCount} | Failed: {$failedCount}");
            
            if ($this->option('dry-run')) {
                $this->line("    [DRY RUN] Seria corrigida");
                continue;
            }
            
            // Corrigir a campanha
            $this->fixCampaign($campaign);
        }
        
        if (!$this->option('dry-run')) {
            $this->info('✅ Campanhas corrigidas com sucesso!');
        }
        
        return 0;
    }
    
    private function fixCampaign(EmailCampaign $campaign)
    {
        $this->line("    🔧 Corrigindo campanha {$campaign->id}...");
        
        // Verificar se há jobs pendentes na fila para esta campanha
        $pendingJobs = \DB::table('jobs')
            ->where('payload', 'like', '%SendCampaignEmail%')
            ->where('payload', 'like', "%campaign_id\":{$campaign->id}%")
            ->count();
            
        if ($pendingJobs > 0) {
            $this->line("    ⚠️  Há {$pendingJobs} jobs ainda na fila, aguardando processamento...");
            return;
        }
        
        // Resetar destinatários que estão "sending" há muito tempo para "pending"
        $stuckRecipients = $campaign->recipients()
            ->where('status', 'sending')
            ->where('updated_at', '<', now()->subMinutes(10))
            ->get();
            
        if ($stuckRecipients->count() > 0) {
            $this->line("    🔄 Resetando {$stuckRecipients->count()} destinatários presos...");
            
            foreach ($stuckRecipients as $recipient) {
                $recipient->update(['status' => 'pending']);
            }
        }
        
        // Atualizar contadores da campanha
        $stats = $campaign->recipients()
            ->selectRaw('
                COUNT(*) as total,
                SUM(CASE WHEN status = "sent" THEN 1 ELSE 0 END) as sent_count,
                SUM(CASE WHEN status = "failed" THEN 1 ELSE 0 END) as failed_count,
                SUM(CASE WHEN status = "pending" OR status = "sending" THEN 1 ELSE 0 END) as pending_count
            ')
            ->first();
            
        $campaign->update([
            'sent_count' => $stats->sent_count,
            'failed_count' => $stats->failed_count,
            'pending_count' => $stats->pending_count
        ]);
        
        // Determinar novo status da campanha
        if ($stats->pending_count == 0) {
            // Todos processados
            $campaign->update([
                'status' => 'completed',
                'completed_at' => now()
            ]);
            $this->line("    ✅ Campanha marcada como concluída");
        } else {
            // Ainda há pendentes, criar jobs novamente
            $pendingRecipients = $campaign->recipients()->where('status', 'pending')->get();
            
            if ($pendingRecipients->count() > 0) {
                $this->line("    🚀 Recriando {$pendingRecipients->count()} jobs para destinatários pendentes...");
                
                foreach ($pendingRecipients as $index => $recipient) {
                    SendCampaignEmail::dispatch($campaign, $recipient)
                        ->delay(now()->addSeconds($index * 2));
                }
                
                $campaign->update([
                    'status' => 'sending',
                    'started_at' => now()
                ]);
                
                $this->line("    ✅ Jobs recriados, campanha retomada");
            }
        }
    }
}
