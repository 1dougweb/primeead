<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'tipo_usuario',
        'parceiro_id',
        'ativo',
        'ultimo_acesso',
        'criado_por'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'ultimo_acesso' => 'datetime',
            'ativo' => 'boolean'
        ];
    }

    /**
     * Formatar tipo de usuário
     */
    public function getTipoUsuarioFormatadoAttribute()
    {
        $tiposMap = [
            'admin' => '👑 Admin',
            'vendedor' => '💼 Vendedor',
            'colaborador' => '👤 Colaborador',
            'midia' => '📱 Mídia',
            'parceiro' => '🤝 Parceiro'
        ];

        return $tiposMap[$this->tipo_usuario] ?? $this->tipo_usuario;
    }

    /**
     * Relacionamento com o parceiro
     */
    public function parceiro()
    {
        return $this->belongsTo(Parceiro::class);
    }

    /**
     * Verifica se o usuário é admin
     */
    public function isAdmin(): bool
    {
        return $this->tipo_usuario === 'admin';
    }

    /**
     * Verifica se o usuário é mídia
     */
    public function isMedia(): bool
    {
        return $this->tipo_usuario === 'midia';
    }

    /**
     * Verifica se o usuário é admin ou mídia
     */
    public function isAdminOrMedia(): bool
    {
        return in_array($this->tipo_usuario, ['admin', 'midia']);
    }

    /**
     * Verificar se é vendedor
     */
    public function isVendedor(): bool
    {
        return $this->tipo_usuario === 'vendedor';
    }

    /**
     * Verificar se é parceiro
     */
    public function isParceiro(): bool
    {
        return $this->tipo_usuario === 'parceiro';
    }

    /**
     * Verificar se é funcionário (qualquer tipo exceto parceiro)
     */
    public function isStaff(): bool
    {
        return in_array($this->tipo_usuario, ['admin', 'midia', 'vendedor', 'colaborador']);
    }

    /**
     * Relacionamento com leads travados
     */
    public function leadsTravasdos()
    {
        return $this->hasMany(Inscricao::class, 'locked_by');
    }

    /**
     * Relacionamento com as inscrições travadas pelo usuário
     */
    public function lockedInscricoes()
    {
        return $this->hasMany(Inscricao::class, 'locked_by');
    }

    /**
     * Relacionamento com o histórico de status
     */
    public function statusHistories()
    {
        return $this->hasMany(StatusHistory::class, 'usuario_id');
    }

    /**
     * Verifica se o usuário tem uma permissão específica.
     * Sobrescreve o método do Spatie para incluir lógica de admin.
     *
     * @param string $permission
     * @param string|null $guardName
     * @return bool
     */
    public function hasPermissionTo($permission, $guardName = null): bool
    {
        // Se for admin, tem todas as permissões
        if ($this->isAdmin()) {
            return true;
        }

        // Verificar se tem permissão direta
        if ($this->permissions()->where('name', $permission)->exists()) {
            return true;
        }

        // Verificar se tem permissão via roles
        return $this->roles()->whereHas('permissions', function ($query) use ($permission) {
            $query->where('name', $permission);
        })->exists();
    }

    /**
     * Verifica se o usuário tem acesso a um módulo específico.
     *
     * @param string $module
     * @return bool
     */
    public function hasModuleAccess(string $module): bool
    {
        // Se for admin, tem acesso a todos os módulos
        if ($this->isAdmin()) {
            return true;
        }

        // Verificar se o usuário tem alguma permissão que comece com o módulo
        return $this->getAllPermissions()->contains(function ($permission) use ($module) {
            return str_starts_with($permission->name, $module . '.');
        });
    }

    /**
     * Obter todas as permissões do usuário (incluindo as herdadas de roles).
     * Sobrescreve o método do Spatie para incluir lógica de admin.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getAllPermissions()
    {
        // Se for admin, retornar todas as permissões
        if ($this->isAdmin()) {
            return \Spatie\Permission\Models\Permission::all();
        }

        // Usar o método padrão do Spatie
        return $this->getPermissionsViaRoles()->merge($this->permissions);
    }

    /**
     * Método de compatibilidade com o sistema antigo
     */
    public function hasPermission(string $permissionSlug): bool
    {
        return $this->hasPermissionTo($permissionSlug);
    }

    /**
     * Verificar se o usuário tem um papel específico.
     * Sobrescreve o método do Spatie para incluir lógica de admin.
     *
     * @param string $role
     * @param string|null $guardName
     * @return bool
     */
    public function hasRole($role, $guardName = null): bool
    {
        // Se for admin, tem todos os roles
        if ($this->isAdmin()) {
            return true;
        }

        // Usar o método padrão do Spatie
        return $this->roles()->where('name', $role)->exists();
    }

    /**
     * Obter todos os módulos que um usuário tem acesso.
     *
     * @return array
     */
    public function getUserModules(): array
    {
        if ($this->isAdmin()) {
            return \Spatie\Permission\Models\Permission::distinct()->pluck('name')->map(function ($permission) {
                return explode('.', $permission)[0];
            })->unique()->values()->toArray();
        }

        return $this->getAllPermissions()->pluck('name')->map(function ($permission) {
            return explode('.', $permission)[0];
        })->unique()->values()->toArray();
    }

    /**
     * Método de compatibilidade - criar usuário com role
     */
    public static function createWithRole(array $userData, string $roleName): self
    {
        $user = static::create($userData);
        $user->assignRole($roleName);
        return $user;
    }

    /**
     * Método de compatibilidade - sincronizar roles
     */
    public function syncRoles(array $roleNames): self
    {
        return parent::syncRoles($roleNames);
    }

    /**
     * Método de compatibilidade - atribuir permissão diretamente
     */
    public function givePermissionTo($permission): self
    {
        $permissionModel = \Spatie\Permission\Models\Permission::where('name', $permission)->first();
        if ($permissionModel) {
            $this->permissions()->attach($permissionModel->id);
        }
        return $this;
    }

    /**
     * Método de compatibilidade - remover permissão
     */
    public function revokePermissionTo($permission): self
    {
        return parent::revokePermissionTo($permission);
    }
}
