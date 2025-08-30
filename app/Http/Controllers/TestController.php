<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TestController extends Controller
{
    public function testPermissions()
    {
        $user = Auth::user();
        
        if (!$user) {
            return response()->json(['error' => 'User not authenticated']);
        }
        
        $menuPermissions = [
            'admin.permissions.*' => 'permissoes.index',
            'admin.usuarios.*' => 'usuarios.index',
            'admin.settings.*' => 'configuracoes.index',
            'admin.monitoramento' => 'monitoramento.index',
            'admin.whatsapp.*' => 'whatsapp.admin',
            'admin.settings.whatsapp' => 'whatsapp.admin',
            'admin.settings.whatsapp.*' => 'whatsapp.admin',
            'admin.whatsapp.templates.*' => 'whatsapp.templates.index',
            'admin.email-campaigns.*' => 'whatsapp.admin',
            'admin.files.*' => 'arquivos.index',
            'contacts.*' => 'contatos.index',
            'admin.parceiros.*' => 'parceiros.index',
            'admin.contracts.*' => 'contratos.index',
            'admin.kanban.*' => 'kanban.index',
            'admin.matriculas.*' => 'matriculas.index',
            'admin.inscricoes' => 'inscricoes.index'
        ];
        
        $userMenuPermissions = [];
        
        foreach ($menuPermissions as $route => $permission) {
            $userMenuPermissions[$route] = $user->hasPermission($permission);
        }
        
        return response()->json([
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'tipo_usuario' => $user->tipo_usuario,
                'isAdmin' => $user->isAdmin(),
            ],
            'menu_permissions' => $userMenuPermissions,
            'current_route' => request()->route()->getName(),
            'has_current_permission' => $user->hasPermission('permissoes.index')
        ]);
    }
} 