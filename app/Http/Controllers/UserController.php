<?php

namespace App\Http\Controllers;

use App\Models\User;
use Spatie\Permission\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    /**
     * Listar usuários
     */
    public function index()
    {
        $usuarios = User::with('roles')->orderBy('created_at', 'desc')->paginate(10);
        
        return view('admin.usuarios.index', compact('usuarios'));
    }

    /**
     * Mostrar formulário de criação
     */
    public function create()
    {
        $roles = Role::orderBy('name')->get();
        return view('admin.usuarios.create', compact('roles'));
    }

    /**
     * Salvar novo usuário
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:6|confirmed',
            'tipo_usuario' => 'required|in:admin,vendedor,colaborador,midia',
            'ativo' => 'boolean',
            'roles' => 'nullable|array',
            'roles.*' => 'exists:roles,id'
        ]);

        $usuario = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'tipo_usuario' => $request->tipo_usuario,
            'ativo' => $request->has('ativo'),
            'criado_por' => session('admin_name', session('admin_email', 'Sistema'))
        ]);

        // Atribuir papéis ao usuário
        if ($request->has('roles')) {
            $usuario->roles()->attach($request->roles);
        }

        return redirect()->route('admin.usuarios.index')
                        ->with('success', 'Usuário criado com sucesso!');
    }

    /**
     * Mostrar detalhes do usuário
     */
    public function show($id)
    {
        $usuario = User::with('roles')->findOrFail($id);
        
        return view('admin.usuarios.show', compact('usuario'));
    }

    /**
     * Mostrar formulário de edição
     */
    public function edit($id)
    {
        $usuario = User::with('roles')->findOrFail($id);
        $roles = Role::orderBy('name')->get();
        $userRoles = $usuario->roles->pluck('id')->toArray();
        
        return view('admin.usuarios.edit', compact('usuario', 'roles', 'userRoles'));
    }

    /**
     * Atualizar usuário
     */
    public function update(Request $request, $id)
    {
        $usuario = User::findOrFail($id);
        
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $id,
            'password' => 'nullable|min:6|confirmed',
            'tipo_usuario' => 'required|in:admin,vendedor,colaborador,midia',
            'ativo' => 'boolean',
            'roles' => 'nullable|array',
            'roles.*' => 'exists:roles,id'
        ]);

        $dados = [
            'name' => $request->name,
            'email' => $request->email,
            'tipo_usuario' => $request->tipo_usuario,
            'ativo' => $request->has('ativo')
        ];

        // Só atualizar senha se foi fornecida
        if ($request->filled('password')) {
            $dados['password'] = Hash::make($request->password);
        }

        $usuario->update($dados);

        // Atualizar papéis do usuário
        if ($request->has('roles')) {
            $usuario->roles()->sync($request->roles);
        } else {
            $usuario->roles()->detach();
        }

        return redirect()->route('admin.usuarios.index')
                        ->with('success', 'Usuário atualizado com sucesso!');
    }

    /**
     * Excluir usuário
     */
    public function destroy($id)
    {
        $usuario = User::findOrFail($id);
        
        // Não permitir excluir o próprio usuário
        if ($usuario->id == session('admin_id')) {
            return redirect()->route('admin.usuarios.index')
                            ->with('error', 'Você não pode excluir seu próprio usuário!');
        }
        
        // Remover relações com papéis
        $usuario->roles()->detach();
        
        $usuario->delete();
        
        return redirect()->route('admin.usuarios.index')
                        ->with('success', 'Usuário excluído com sucesso!');
    }

    /**
     * Alternar status ativo/inativo
     */
    public function toggleStatus($id)
    {
        $usuario = User::findOrFail($id);
        
        // Não permitir desativar o próprio usuário
        if ($usuario->id == session('admin_id')) {
            return response()->json([
                'success' => false,
                'message' => 'Você não pode desativar seu próprio usuário!'
            ]);
        }
        
        $usuario->update(['ativo' => !$usuario->ativo]);
        
        return response()->json([
            'success' => true,
            'message' => 'Status do usuário alterado com sucesso!',
            'novo_status' => $usuario->ativo
        ]);
    }

    /**
     * Fazer login como outro usuário (impersonation)
     */
    public function impersonate($id)
    {
        // Verificar se o usuário atual tem permissão para impersonation
        if (!auth()->user()->hasPermissionTo('usuarios.impersonate')) {
            return redirect()->route('admin.usuarios.index')
                            ->with('error', 'Você não tem permissão para fazer login como outro usuário.');
        }

        $userToImpersonate = User::findOrFail($id);

        // Não permitir impersonar a si mesmo
        if ($userToImpersonate->id === auth()->id()) {
            return redirect()->route('admin.usuarios.index')
                            ->with('error', 'Você não pode fazer login como você mesmo.');
        }

        // Não permitir impersonar outro admin (segurança)
        if ($userToImpersonate->isAdmin() && !auth()->user()->isAdmin()) {
            return redirect()->route('admin.usuarios.index')
                            ->with('error', 'Você não pode fazer login como um administrador.');
        }

        // Salvar dados originais da sessão
        session([
            'impersonating_user_id' => auth()->id(),
            'impersonating_user_name' => auth()->user()->name,
            'original_admin_id' => session('admin_id'),
            'original_admin_name' => session('admin_name'),
            'original_admin_email' => session('admin_email'),
            'original_admin_tipo' => session('admin_tipo'),
            'original_admin_logged_in' => session('admin_logged_in'),
        ]);

        // Fazer login como o usuário selecionado
        auth()->login($userToImpersonate);

        // Atualizar variáveis de sessão para o usuário impersonado
        session([
            'admin_id' => $userToImpersonate->id,
            'admin_name' => $userToImpersonate->name,
            'admin_email' => $userToImpersonate->email,
            'admin_tipo' => $userToImpersonate->tipo_usuario,
            'admin_logged_in' => $userToImpersonate->isAdmin(),
        ]);

        // Marcar que estamos em modo de impersonation
        session(['is_impersonating' => true]);
        session(['impersonated_user_name' => $userToImpersonate->name]);

        // Log para debug
        \Log::info('Impersonation started', [
            'original_user_id' => session('impersonating_user_id'),
            'impersonated_user_id' => $userToImpersonate->id,
            'is_impersonating' => session('is_impersonating'),
            'session_admin_tipo' => session('admin_tipo')
        ]);

        return redirect()->route('dashboard')
                        ->with('success', "Você está agora logado como {$userToImpersonate->name}");
    }

    /**
     * Sair do modo de impersonation
     */
    public function stopImpersonation()
    {
        try {
            // Log para debug
            \Log::info('stopImpersonation called', [
                'is_impersonating' => session('is_impersonating'),
                'impersonating_user_id' => session('impersonating_user_id'),
                'current_user_id' => auth()->id(),
                'current_user_type' => auth()->user()->tipo_usuario ?? 'not_authenticated'
            ]);

            // Verificar se estamos em modo de impersonation
            if (!session('is_impersonating')) {
                \Log::warning('Not in impersonation mode');
                return redirect()->route('dashboard')
                                ->with('error', 'Você não está em modo de impersonation.');
            }

            // Recuperar o usuário original
            $originalUserId = session('impersonating_user_id') ?? session('original_admin_id');
            \Log::info('Original user ID: ' . $originalUserId);
            
            $originalUser = User::find($originalUserId);
            
            if (!$originalUser) {
                \Log::error('Original user not found: ' . $originalUserId);
                // Se não encontrar o usuário original, limpar a sessão e redirecionar para login
                session()->flush();
                return redirect()->route('login')
                                ->with('error', 'Usuário original não encontrado. Faça login novamente.');
            }

            \Log::info('Original user found: ' . $originalUser->name);

            // Restaurar variáveis de sessão originais
            $originalData = [
                'admin_id' => session('original_admin_id') ?? $originalUser->id,
                'admin_name' => session('original_admin_name') ?? $originalUser->name,
                'admin_email' => session('original_admin_email') ?? $originalUser->email,
                'admin_tipo' => session('original_admin_tipo') ?? $originalUser->tipo_usuario,
                'admin_logged_in' => session('original_admin_logged_in') ?? $originalUser->isAdmin(),
            ];

            // Log para debug
            \Log::info('Restoring session data', $originalData);

            session($originalData);

            // Limpar dados de impersonation da sessão
            session()->forget([
                'is_impersonating', 
                'impersonating_user_id', 
                'impersonating_user_name', 
                'impersonated_user_name',
                'original_admin_id',
                'original_admin_name',
                'original_admin_email',
                'original_admin_tipo',
                'original_admin_logged_in'
            ]);

            // Fazer login como o usuário original
            auth()->login($originalUser);

            // Log de sucesso
            \Log::info('Impersonation stopped successfully', [
                'original_user_id' => $originalUser->id,
                'current_user_id' => auth()->id()
            ]);

            return redirect()->route('admin.usuarios.index')
                            ->with('success', 'Você saiu do modo de impersonation com sucesso.');
                            
        } catch (\Exception $e) {
            \Log::error('Error in stopImpersonation: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            
            return redirect()->route('dashboard')
                            ->with('error', 'Erro ao sair do modo de impersonation: ' . $e->getMessage());
        }
    }
}
