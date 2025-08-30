<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\DB;

class RoleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Verificar permissão
        if (!auth()->user()->hasPermissionTo('roles.index')) {
            return redirect()->back()->with('error', 'Você não tem permissão para acessar esta página.');
        }

        $roles = Role::with('permissions')->get();
        
        return view('admin.roles.index', compact('roles'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // Verificar permissão
        if (!auth()->user()->hasPermissionTo('roles.create')) {
            return redirect()->back()->with('error', 'Você não tem permissão para criar roles.');
        }

        $permissions = Permission::all()->groupBy(function($permission) {
            return explode('.', $permission->name)[0];
        });

        return view('admin.roles.create', compact('permissions'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Verificar permissão
        if (!auth()->user()->hasPermissionTo('roles.create')) {
            return redirect()->back()->with('error', 'Você não tem permissão para criar roles.');
        }

        $request->validate([
            'name' => 'required|string|max:255|unique:roles,name',
            'permissions' => 'required|array|min:1',
            'permissions.*' => 'exists:permissions,id'
        ]);

        DB::beginTransaction();
        try {
            // Criar o role
            $role = Role::create([
                'name' => $request->name,
                'guard_name' => 'web'
            ]);

            // Atribuir permissões
            $permissions = Permission::whereIn('id', $request->permissions)->get();
            $role->syncPermissions($permissions);

            DB::commit();

            return redirect()->route('admin.roles.index')
                ->with('success', 'Role criado com sucesso!');
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()
                ->with('error', 'Erro ao criar role: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Role $role)
    {
        // Verificar permissão
        if (!auth()->user()->hasPermissionTo('roles.index')) {
            return redirect()->back()->with('error', 'Você não tem permissão para visualizar roles.');
        }

        $role->load('permissions', 'users');
        
        return view('admin.roles.show', compact('role'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Role $role)
    {
        // Verificar permissão
        if (!auth()->user()->hasPermissionTo('roles.edit')) {
            return redirect()->back()->with('error', 'Você não tem permissão para editar roles.');
        }

        $permissions = Permission::all()->groupBy(function($permission) {
            return explode('.', $permission->name)[0];
        });

        $rolePermissions = $role->permissions->pluck('id')->toArray();

        return view('admin.roles.edit', compact('role', 'permissions', 'rolePermissions'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Role $role)
    {
        // Verificar permissão
        if (!auth()->user()->hasPermissionTo('roles.edit')) {
            return redirect()->back()->with('error', 'Você não tem permissão para editar roles.');
        }

        $request->validate([
            'name' => 'required|string|max:255|unique:roles,name,' . $role->id,
            'permissions' => 'required|array|min:1',
            'permissions.*' => 'exists:permissions,id'
        ]);

        DB::beginTransaction();
        try {
            // Atualizar o role
            $role->update([
                'name' => $request->name
            ]);

            // Sincronizar permissões
            $permissions = Permission::whereIn('id', $request->permissions)->get();
            $role->syncPermissions($permissions);

            DB::commit();

            return redirect()->route('admin.roles.index')
                ->with('success', 'Role atualizado com sucesso!');
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()
                ->with('error', 'Erro ao atualizar role: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Role $role)
    {
        // Verificar permissão
        if (!auth()->user()->hasPermissionTo('roles.delete')) {
            return redirect()->back()->with('error', 'Você não tem permissão para deletar roles.');
        }

        // Verificar se o role tem usuários associados
        if ($role->users()->count() > 0) {
            return redirect()->back()->with('error', 'Não é possível deletar um role que possui usuários associados.');
        }

        try {
            $role->delete();
            return redirect()->route('admin.roles.index')
                ->with('success', 'Role deletado com sucesso!');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Erro ao deletar role: ' . $e->getMessage());
        }
    }

    /**
     * Listar usuários de um role
     */
    public function users(Role $role)
    {
        // Verificar permissão
        if (!auth()->user()->hasPermissionTo('roles.index')) {
            return redirect()->back()->with('error', 'Você não tem permissão para visualizar roles.');
        }

        $users = $role->users()->paginate(15);
        
        return view('admin.roles.users', compact('role', 'users'));
    }

    /**
     * Clonar um role
     */
    public function clone(Role $role)
    {
        // Verificar permissão
        if (!auth()->user()->hasPermissionTo('roles.create')) {
            return redirect()->back()->with('error', 'Você não tem permissão para criar roles.');
        }

        $permissions = Permission::all()->groupBy(function($permission) {
            return explode('.', $permission->name)[0];
        });

        $rolePermissions = $role->permissions->pluck('id')->toArray();

        return view('admin.roles.clone', compact('role', 'permissions', 'rolePermissions'));
    }
}
