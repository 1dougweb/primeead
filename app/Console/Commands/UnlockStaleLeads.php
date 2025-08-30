<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Inscricao;
use App\Models\SystemSetting;
use Carbon\Carbon;

class UnlockStaleLeads extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'leads:unlock-stale {--dry-run : Show what would be unlocked without actually unlocking}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Unlock leads that have been locked for too long based on system settings';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $autoUnlockHours = SystemSetting::get('auto_unlock_hours', 24);
        $isDryRun = $this->option('dry-run');
        
        $this->info("🔍 Procurando leads travados há mais de {$autoUnlockHours} horas...");
        
        // Encontrar leads travados há muito tempo
        $cutoffTime = Carbon::now()->subHours($autoUnlockHours);
        
        $staleLeads = Inscricao::whereNotNull('locked_by')
                              ->whereNotNull('locked_at')
                              ->where('locked_at', '<', $cutoffTime)
                              ->with('lockedBy')
                              ->get();
        
        if ($staleLeads->isEmpty()) {
            $this->info('✅ Nenhum lead travado encontrado para destravamento.');
            return Command::SUCCESS;
        }
        
        $this->info("📋 Encontrados {$staleLeads->count()} leads para destravamento:");
        
        // Mostrar tabela com os leads que serão destravados
        $tableData = [];
        foreach ($staleLeads as $lead) {
            $tableData[] = [
                'ID' => $lead->id,
                'Nome' => $lead->nome,
                'Email' => $lead->email,
                'Travado Por' => $lead->lockedBy ? $lead->lockedBy->name : 'Usuário Removido',
                'Travado Em' => $lead->locked_at->format('d/m/Y H:i:s'),
                'Há Quanto Tempo' => $lead->locked_at->diffForHumans(),
            ];
        }
        
        $this->table([
            'ID', 'Nome', 'Email', 'Travado Por', 'Travado Em', 'Há Quanto Tempo'
        ], $tableData);
        
        if ($isDryRun) {
            $this->warn('🧪 Modo dry-run ativo - nenhum lead foi destravado.');
            $this->info('Execute sem --dry-run para destravar os leads listados acima.');
            return Command::SUCCESS;
        }
        
        // Confirmar ação
        if (!$this->confirm('Deseja prosseguir com o destravamento destes leads?')) {
            $this->info('❌ Operação cancelada.');
            return Command::SUCCESS;
        }
        
        // Destravar leads
        $unlockedCount = 0;
        $errors = [];
        
        foreach ($staleLeads as $lead) {
            try {
                $lead->unlock();
                $unlockedCount++;
                $this->line("✅ Lead #{$lead->id} ({$lead->nome}) destravado com sucesso.");
            } catch (\Exception $e) {
                $errors[] = "❌ Erro ao destravar lead #{$lead->id}: " . $e->getMessage();
                $this->error("❌ Erro ao destravar lead #{$lead->id}: " . $e->getMessage());
            }
        }
        
        // Resumo final
        $this->info("🎉 Processo concluído!");
        $this->info("✅ {$unlockedCount} leads destravados com sucesso.");
        
                 if (!empty($errors)) {
             $this->warn("⚠️  " . count($errors) . " erros encontrados:");
             foreach ($errors as $error) {
                 $this->error($error);
             }
         }
         
         // Sugerir agendamento
         if ($unlockedCount > 0) {
             $this->info("");
             $this->info("💡 Dica: Para automatizar este processo, adicione ao crontab:");
             $this->info("   0 */6 * * * cd " . base_path() . " && php artisan leads:unlock-stale");
             $this->info("   (executa a cada 6 horas)");
         }
         
         return Command::SUCCESS;
     }
}
