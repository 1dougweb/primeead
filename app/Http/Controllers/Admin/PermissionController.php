<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class PermissionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Verificar permissão
        if (!auth()->user()->hasPermissionTo('permissoes.index')) {
            return redirect()->back()->with('error', 'Você não tem permissão para acessar esta página.');
        }

        $permissions = Permission::all()->groupBy(function($permission) {
            return explode('.', $permission->name)[0];
        });
        
        return view('admin.permissions.index', compact('permissions'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // Verificar permissão
        if (!auth()->user()->hasPermissionTo('permissoes.create')) {
            return redirect()->back()->with('error', 'Você não tem permissão para criar permissões.');
        }

        $modules = Permission::all()->map(function($permission) {
            return explode('.', $permission->name)[0];
        })->unique()->sort()->values();

        return view('admin.permissions.create', compact('modules'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Verificar permissão
        if (!auth()->user()->hasPermissionTo('permissoes.create')) {
            return redirect()->back()->with('error', 'Você não tem permissão para criar permissões.');
        }

        $request->validate([
            'name' => 'required|string|max:255|unique:permissions,name',
        ]);

        try {
            Permission::create([
                'name' => $request->name,
                'guard_name' => 'web'
            ]);

            return redirect()->route('admin.permissions.index')
                ->with('success', 'Permissão criada com sucesso!');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Erro ao criar permissão: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Permission $permission)
    {
        // Verificar permissão
        if (!auth()->user()->hasPermissionTo('permissoes.index')) {
            return redirect()->back()->with('error', 'Você não tem permissão para visualizar permissões.');
        }

        $permission->load('roles', 'users');
        
        return view('admin.permissions.show', compact('permission'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Permission $permission)
    {
        // Verificar permissão
        if (!auth()->user()->hasPermissionTo('permissoes.edit')) {
            return redirect()->back()->with('error', 'Você não tem permissão para editar permissões.');
        }

        return view('admin.permissions.edit', compact('permission'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Permission $permission)
    {
        // Verificar permissão
        if (!auth()->user()->hasPermissionTo('permissoes.edit')) {
            return redirect()->back()->with('error', 'Você não tem permissão para editar permissões.');
        }

        $request->validate([
            'name' => 'required|string|max:255|unique:permissions,name,' . $permission->id,
        ]);

        try {
            $permission->update([
                'name' => $request->name
            ]);

            return redirect()->route('admin.permissions.index')
                ->with('success', 'Permissão atualizada com sucesso!');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Erro ao atualizar permissão: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Permission $permission)
    {
        // Verificar permissão
        if (!auth()->user()->hasPermissionTo('permissoes.delete')) {
            return redirect()->back()->with('error', 'Você não tem permissão para deletar permissões.');
        }

        // Verificar se a permissão tem roles associados
        if ($permission->roles()->count() > 0) {
            return redirect()->back()->with('error', 'Não é possível deletar uma permissão que possui roles associados.');
        }

        try {
            $permission->delete();
            return redirect()->route('admin.permissions.index')
                ->with('success', 'Permissão deletada com sucesso!');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Erro ao deletar permissão: ' . $e->getMessage());
        }
    }

    /**
     * Gerenciar permissões de usuários
     */
    public function manageUsers()
    {
        // Verificar permissão
        if (!auth()->user()->hasPermissionTo('permissoes.index')) {
            return redirect()->back()->with('error', 'Você não tem permissão para gerenciar permissões de usuários.');
        }

        $users = User::with('roles', 'permissions')->paginate(15);
        $roles = Role::all();
        $permissions = Permission::all();
        
        return view('admin.permissions.manage_users', compact('users', 'roles', 'permissions'));
    }

    /**
     * Atualizar permissões de um usuário
     */
    public function updateUserPermissions(Request $request, User $user)
    {
        // Verificar permissão
        if (!auth()->user()->hasPermissionTo('permissoes.edit')) {
            return redirect()->back()->with('error', 'Você não tem permissão para editar permissões de usuários.');
        }

        $request->validate([
            'roles' => 'nullable|array',
            'roles.*' => 'exists:roles,id',
            'permissions' => 'nullable|array',
            'permissions.*' => 'exists:permissions,id'
        ]);

        DB::beginTransaction();
        try {
            // Sincronizar roles
            if ($request->has('roles')) {
                $roles = Role::whereIn('id', $request->roles)->get();
                $user->syncRoles($roles);
            } else {
                $user->syncRoles([]);
            }

            // Sincronizar permissões diretas
            if ($request->has('permissions')) {
                $permissions = Permission::whereIn('id', $request->permissions)->get();
                $user->syncPermissions($permissions);
            } else {
                $user->syncPermissions([]);
            }

            DB::commit();

            return redirect()->back()
                ->with('success', 'Permissões do usuário atualizadas com sucesso!');
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()
                ->with('error', 'Erro ao atualizar permissões: ' . $e->getMessage());
        }
    }

    /**
     * Criar permissões em lote para um módulo
     */
    public function createModulePermissions(Request $request)
    {
        // Verificar permissão
        if (!auth()->user()->hasPermissionTo('permissoes.create')) {
            return redirect()->back()->with('error', 'Você não tem permissão para criar permissões.');
        }

        $request->validate([
            'module' => 'required|string|max:255',
            'actions' => 'required|array|min:1',
            'actions.*' => 'required|string|max:255'
        ]);

        DB::beginTransaction();
        try {
            $createdPermissions = [];
            
            foreach ($request->actions as $action) {
                $permissionName = $request->module . '.' . $action;
                
                // Verificar se já existe
                if (!Permission::where('name', $permissionName)->exists()) {
                    $permission = Permission::create([
                        'name' => $permissionName,
                        'guard_name' => 'web'
                    ]);
                    $createdPermissions[] = $permission->name;
                }
            }

            DB::commit();

            if (count($createdPermissions) > 0) {
                return redirect()->route('admin.permissions.index')
                    ->with('success', 'Permissões criadas: ' . implode(', ', $createdPermissions));
            } else {
                return redirect()->back()
                    ->with('warning', 'Todas as permissões já existem.');
            }
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()
                ->with('error', 'Erro ao criar permissões: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Limpar cache de permissões
     */
    public function clearCache()
    {
        // Verificar permissão
        if (!auth()->user()->hasPermissionTo('permissoes.edit')) {
            return redirect()->back()->with('error', 'Você não tem permissão para limpar cache.');
        }

        try {
            app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
            
            return redirect()->back()
                ->with('success', 'Cache de permissões limpo com sucesso!');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Erro ao limpar cache: ' . $e->getMessage());
        }
    }
} 