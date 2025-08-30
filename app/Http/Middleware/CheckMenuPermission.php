<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use Symfony\Component\HttpFoundation\Response;

class CheckMenuPermission
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (auth()->check()) {
            $user = auth()->user();
            
            // Definir permissões de menu baseadas no sistema Spatie
            $menuPermissions = [
                'admin.inscricoes' => 'inscricoes.index',
                'admin.matriculas.*' => 'matriculas.index',
                'admin.contracts.*' => 'contratos.index',
                'admin.kanban.*' => 'kanban.index',
                'admin.files.*' => 'google-drive.index',
                'contacts.*' => 'contatos.index',
                'admin.parceiros.*' => 'parceiros.index',
                'admin.monitoramento' => 'monitoramento.index',
                'admin.usuarios.*' => 'usuarios.index',
                'admin.usuarios.impersonate' => 'usuarios.impersonate',
                'admin.settings.*' => 'configuracoes.index',
                'admin.whatsapp.*' => 'whatsapp.index',
                'admin.permissions.*' => 'permissoes.index',
                'admin.permissions.migration.*' => 'permissoes.migrate',
                'admin.payments.*' => 'pagamentos.index',
                'admin.email-campaigns.*' => 'email-campaigns.index',
                'admin.email-templates.*' => 'email-templates.index',
                
                // Templates de Contratos
                'admin.contracts.templates.*' => 'contract-templates.index',
                'templates.*' => 'contract-templates.index',
                
                // Templates de Pagamentos
                'admin.payment-templates.*' => 'payment-templates.index',
                
                // Templates de Inscrições
                'admin.enrollment-templates.*' => 'enrollment-templates.index',
                
                // Templates de Matrículas
                'admin.matriculation-templates.*' => 'matriculation-templates.index',
            ];

            // Verificar permissões do usuário
            $userMenuPermissions = [];
            foreach ($menuPermissions as $route => $permission) {
                $userMenuPermissions[$route] = $user->hasPermissionTo($permission);
            }

            // Compartilhar as permissões com todas as views
            View::share('userMenuPermissions', $userMenuPermissions);
            View::share('user', $user);
        }

        return $next($request);
    }
} 