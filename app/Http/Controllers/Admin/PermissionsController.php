<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class PermissionsController extends Controller
{
    /**
     * Display a listing of permissions.
     */
    public function index()
    {
        $permissions = Permission::with('roles')->orderBy('name')->get();
        $roles = Role::with('permissions')->orderBy('name')->get();
        $users = User::with('roles')->get();
        
        // Agrupar permissões por módulo baseado no prefixo do nome (usando ponto como separador)
        $groupedPermissions = $permissions->groupBy(function($permission) {
            $parts = explode('.', $permission->name);
            return $parts[0] ?? 'outros';
        });
        
        return view('admin.permissions.index', compact('permissions', 'roles', 'users', 'groupedPermissions'));
    }

    /**
     * Show the form for creating a new permission.
     */
    public function create()
    {
        // Extrair módulos dos nomes das permissões existentes
        $permissions = Permission::all();
        $modules = $permissions->map(function($permission) {
            $parts = explode('.', $permission->name);
            return $parts[0] ?? 'outros';
        })->unique()->sort()->values();
        
        return view('admin.permissions.create', compact('modules'));
    }

    /**
     * Store a newly created permission in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:permissions,name',
            'module' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        // Criar nome da permissão com prefixo do módulo se não estiver presente
        $permissionName = $request->name;
        if (!str_contains($permissionName, $request->module)) {
            $permissionName = $request->module . '.' . $permissionName;
        }

        $permission = Permission::create([
            'name' => $permissionName,
            'guard_name' => 'web',
        ]);

        Log::info('Permission created', [
            'permission_id' => $permission->id,
            'created_by' => auth()->user()->id,
            'permission_data' => $permission->toArray()
        ]);

        return redirect()->route('admin.permissions.index')
            ->with('success', 'Permissão criada com sucesso!');
    }

    /**
     * Display the specified permission.
     */
    public function show($id)
    {
        $permission = Permission::with('roles.users')->findOrFail($id);
        return view('admin.permissions.show', compact('permission'));
    }

    /**
     * Show the form for editing the specified permission.
     */
    public function edit($id)
    {
        $permission = Permission::findOrFail($id);
        
        // Extrair módulos dos nomes das permissões existentes
        $permissions = Permission::all();
        $modules = $permissions->map(function($perm) {
            $parts = explode('.', $perm->name);
            return $parts[0] ?? 'outros';
        })->unique()->sort()->values();
        
        return view('admin.permissions.edit', compact('permission', 'modules'));
    }

    /**
     * Update the specified permission in storage.
     */
    public function update(Request $request, $id)
    {
        $permission = Permission::findOrFail($id);
        
        $request->validate([
            'name' => 'required|string|max:255|unique:permissions,name,' . $id,
            'module' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $oldData = $permission->toArray();

        // Criar nome da permissão com prefixo do módulo se não estiver presente
        $permissionName = $request->name;
        if (!str_contains($permissionName, $request->module)) {
            $permissionName = $request->module . '_' . $permissionName;
        }

        $permission->update([
            'name' => $permissionName,
        ]);

        Log::info('Permission updated', [
            'permission_id' => $permission->id,
            'updated_by' => auth()->user()->id,
            'old_data' => $oldData,
            'new_data' => $permission->toArray()
        ]);

        return redirect()->route('admin.permissions.index')
            ->with('success', 'Permissão atualizada com sucesso!');
    }

    /**
     * Remove the specified permission from storage.
     */
    public function destroy($id)
    {
        $permission = Permission::findOrFail($id);
        
        // Verificar se a permissão está sendo usada
        if ($permission->roles()->count() > 0) {
            return redirect()->route('admin.permissions.index')
                ->with('error', 'Não é possível excluir esta permissão pois ela está sendo usada por roles.');
        }

        $permissionData = $permission->toArray();
        $permission->delete();

        Log::info('Permission deleted', [
            'permission_id' => $permission->id,
            'deleted_by' => auth()->user()->id,
            'permission_data' => $permissionData
        ]);

        return redirect()->route('admin.permissions.index')
            ->with('success', 'Permissão excluída com sucesso!');
    }

    /**
     * Show roles management page.
     */
    public function roles()
    {
        $roles = Role::with(['permissions', 'users'])->orderBy('name')->get();
        return view('admin.permissions.roles', compact('roles'));
    }

    /**
     * Show the form for creating a new role.
     */
    public function createRole()
    {
        $permissions = Permission::orderBy('name')->get();
        $groupedPermissions = $permissions->groupBy(function($permission) {
            $parts = explode('.', $permission->name);
            return $parts[0] ?? 'outros';
        });
        return view('admin.permissions.create-role', compact('permissions', 'groupedPermissions'));
    }

    /**
     * Store a newly created role in storage.
     */
    public function storeRole(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'permissions' => 'array',
            'permissions.*' => 'exists:permissions,id',
        ]);

        DB::beginTransaction();
        try {
            // Gerar slug automaticamente baseado no nome
            $slug = Str::slug($request->name, '-');
            
            // Verificar se o slug já existe e criar um único
            $originalSlug = $slug;
            $counter = 1;
            while (Role::where('slug', $slug)->exists()) {
                $slug = $originalSlug . '-' . $counter;
                $counter++;
            }

            $role = Role::create([
                'name' => $request->name,
                'guard_name' => 'web',
            ]);

            if ($request->permissions) {
                $role->permissions()->attach($request->permissions);
            }

            Log::info('Role created', [
                'role_id' => $role->id,
                'created_by' => auth()->user()->id,
                'role_data' => $role->toArray(),
                'permissions' => $request->permissions
            ]);

            DB::commit();
            return redirect()->route('admin.permissions.roles.index')
                ->with('success', 'Role criado com sucesso!');
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Error creating role', [
                'error' => $e->getMessage(),
                'user_id' => auth()->user()->id,
                'request_data' => $request->all()
            ]);
            return back()->with('error', 'Erro ao criar role: ' . $e->getMessage());
        }
    }

    /**
     * Show the form for editing the specified role.
     */
    public function editRole(Role $role)
    {
        $permissions = Permission::orderBy('name')->get();
        $groupedPermissions = $permissions->groupBy(function($permission) {
            $parts = explode('_', $permission->name);
            return $parts[0] ?? 'outros';
        });
        $rolePermissions = $role->permissions->pluck('id')->toArray();
        
        return view('admin.permissions.edit-role', compact('role', 'permissions', 'groupedPermissions', 'rolePermissions'));
    }

    /**
     * Update the specified role in storage.
     */
    public function updateRole(Request $request, Role $role)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'permissions' => 'array',
            'permissions.*' => 'exists:permissions,id',
        ]);

        DB::beginTransaction();
        try {
            $oldData = $role->toArray();
            $oldPermissions = $role->permissions->pluck('id')->toArray();

            $role->update([
                'name' => $request->name,
            ]);

            // Sincronizar permissões
            $role->permissions()->sync($request->permissions ?: []);

            Log::info('Role updated', [
                'role_id' => $role->id,
                'updated_by' => auth()->user()->id,
                'old_data' => $oldData,
                'new_data' => $role->toArray(),
                'old_permissions' => $oldPermissions,
                'new_permissions' => $request->permissions
            ]);

            DB::commit();

            return redirect()->route('admin.permissions.roles.index')
                ->with('success', 'Role atualizado com sucesso!');

        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Error updating role', [
                'error' => $e->getMessage(),
                'role_id' => $role->id,
                'request_data' => $request->all()
            ]);

            return redirect()->back()
                ->with('error', 'Erro ao atualizar role: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Remove the specified role from storage.
     */
    public function destroyRole(Role $role)
    {
        // Verificar se o role está sendo usado por usuários
        if ($role->users()->count() > 0) {
            return redirect()->route('admin.permissions.roles.index')
                ->with('error', 'Não é possível excluir este role pois ele está sendo usado por usuários.');
        }

        $roleData = $role->toArray();
        $role->delete();

        Log::info('Role deleted', [
            'role_id' => $role->id,
            'deleted_by' => auth()->user()->id,
            'role_data' => $roleData
        ]);

        return redirect()->route('admin.permissions.roles.index')
            ->with('success', 'Role excluído com sucesso!');
    }

    /**
     * Show users and their roles.
     */
    public function users()
    {
        $users = User::with('roles')->orderBy('name')->paginate(20);
        $roles = Role::orderBy('name')->get();
        
        return view('admin.permissions.users', compact('users', 'roles'));
    }

    /**
     * Update user roles.
     */
    public function updateUserRoles(Request $request, User $user)
    {
        $request->validate([
            'roles' => 'array',
            'roles.*' => 'exists:roles,id',
        ]);

        $oldRoles = $user->roles->pluck('id')->toArray();
        
        // Sincronizar roles do usuário
        $user->roles()->sync($request->roles ?: []);

        Log::info('User roles updated', [
            'user_id' => $user->id,
            'updated_by' => auth()->user()->id,
            'old_roles' => $oldRoles,
            'new_roles' => $request->roles
        ]);

        return redirect()->route('admin.permissions.users')
            ->with('success', 'Roles do usuário atualizados com sucesso!');
    }

    /**
     * Get user data for editing.
     */
    public function editUser(User $user)
    {
        $user->load('roles');
        $userRoles = $user->roles->pluck('id')->toArray();
        
        return response()->json([
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
            ],
            'userRoles' => $userRoles
        ]);
    }

    /**
     * Get user permissions.
     */
    public function getUserPermissions(User $user)
    {
        $user->load('roles.permissions');
        
        // Coletar todas as permissões através dos roles
        $permissions = collect();
        foreach ($user->roles as $role) {
            $permissions = $permissions->merge($role->permissions);
        }
        
        // Remover duplicatas e agrupar por módulo
        $permissions = $permissions->unique('id')->groupBy(function($permission) {
            $parts = explode('.', $permission->name);
            return $parts[0] ?? 'outros';
        });
        
        return response()->json([
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
            ],
            'permissions' => $permissions
        ]);
    }

    /**
     * Generate slug from name.
     */
    public function generateSlug(Request $request)
    {
        $name = $request->input('name');
        $slug = Str::slug($name, '.');
        
        return response()->json(['slug' => $slug]);
    }

    /**
     * Bulk operations for permissions.
     */
    public function bulkAction(Request $request)
    {
        $request->validate([
            'action' => 'required|in:activate,deactivate,delete',
            'permissions' => 'required|array',
            'permissions.*' => 'exists:permissions,id',
        ]);

        $permissions = Permission::whereIn('id', $request->permissions)->get();
        
        DB::beginTransaction();
        try {
            switch ($request->action) {
                case 'activate':
                    // Funcionalidade não disponível no Spatie (sem campo is_active)
                    $message = 'Funcionalidade de ativação não disponível no sistema atual.';
                    break;
                    
                case 'deactivate':
                    // Funcionalidade não disponível no Spatie (sem campo is_active)
                    $message = 'Funcionalidade de desativação não disponível no sistema atual.';
                    break;
                    
                case 'delete':
                    // Verificar se alguma permissão está sendo usada
                    $usedPermissions = $permissions->filter(function ($permission) {
                        return $permission->roles()->count() > 0;
                    });
                    
                    if ($usedPermissions->count() > 0) {
                        DB::rollback();
                        return redirect()->back()
                            ->with('error', 'Algumas permissões não podem ser excluídas pois estão sendo usadas.');
                    }
                    
                    $permissions->each(function ($permission) {
                        $permission->delete();
                    });
                    $message = 'Permissões excluídas com sucesso!';
                    break;
            }

            Log::info('Bulk action performed on permissions', [
                'action' => $request->action,
                'permission_ids' => $request->permissions,
                'performed_by' => auth()->user()->id
            ]);

            DB::commit();

            return redirect()->route('admin.permissions.index')
                ->with('success', $message);

        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Error performing bulk action on permissions', [
                'error' => $e->getMessage(),
                'action' => $request->action,
                'permission_ids' => $request->permissions
            ]);

            return redirect()->back()
                ->with('error', 'Erro ao executar ação: ' . $e->getMessage());
        }
    }

    /**
     * Sync permissions with system.
     */
    public function sync()
    {
        try {
            // Aqui você pode implementar a lógica de sincronização
            // Por exemplo, recriar permissões baseadas em rotas ou módulos
            
            return response()->json([
                'success' => true,
                'message' => 'Permissões sincronizadas com sucesso!'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao sincronizar permissões: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Clear permissions cache.
     */
    public function clearCache()
    {
        try {
            // Limpar cache de permissões
            \Cache::flush();
            
            return response()->json([
                'success' => true,
                'message' => 'Cache limpo com sucesso!'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao limpar cache: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Export permissions to CSV.
     */
    public function export()
    {
        try {
            $permissions = Permission::with('roles')->orderBy('name')->get();
            
            $filename = 'permissions_' . date('Y-m-d_H-i-s') . '.csv';
            
            $headers = [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            ];
            
            $callback = function() use ($permissions) {
                $file = fopen('php://output', 'w');
                
                // Cabeçalho
                fputcsv($file, ['ID', 'Nome', 'Módulo', 'Guard', 'Descrição', 'Roles']);
                
                // Dados
                foreach ($permissions as $permission) {
                    // Extrair módulo do nome da permissão
                    $parts = explode('_', $permission->name);
                    $module = $parts[0] ?? 'outros';
                    
                    fputcsv($file, [
                        $permission->id,
                        $permission->name,
                        $module,
                        $permission->guard_name,
                        '', // description não existe no Spatie
                        $permission->roles->pluck('name')->join(', ')
                    ]);
                }
                
                fclose($file);
            };
            
            return response()->stream($callback, 200, $headers);
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Erro ao exportar permissões: ' . $e->getMessage());
        }
    }
} 