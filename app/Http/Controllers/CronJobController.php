<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;

class CronJobController extends Controller
{
    public function index()
    {
        return view('admin.settings.cron-jobs');
    }

    public function runCommand(Request $request)
    {
        $request->validate([
            'command' => 'required|string'
        ]);

        $command = $request->input('command');
        
        // Lista de comandos permitidos por segurança
        $allowedCommands = [
            'payments:process-recurring',
            'payments:process-recurring --dry-run',
            'payments:send-reminders',
            'payments:send-reminders --dry-run',
            'queue:work',
            'queue:work --stop-when-empty',
            'schedule:run'
        ];

        if (!in_array($command, $allowedCommands)) {
            return response()->json([
                'success' => false,
                'error' => 'Comando não permitido'
            ], 400);
        }

        try {
            // Capturar a saída do comando
            $output = '';
            
            // Executar o comando e capturar a saída
            Artisan::call($command, [], $output);
            $output = Artisan::output();

            Log::info('Comando executado via interface', [
                'command' => $command,
                'output' => $output
            ]);

            return response()->json([
                'success' => true,
                'output' => $output,
                'command' => $command
            ]);

        } catch (\Exception $e) {
            Log::error('Erro ao executar comando', [
                'command' => $command,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }
} 