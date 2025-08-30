@extends('layouts.admin')

@section('title', 'Gerenciar Papéis de Usuários')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">Gerenciar Papéis de Usuários</h1>
        <a href="{{ route('admin.permissions.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Voltar para Permissões
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Usuários e Seus Papéis</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" id="usersTable" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>Nome</th>
                            <th>Email</th>
                            <th>Tipo de Usuário</th>
                            <th>Papéis</th>
                            <th>Status</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($users as $user)
                            <tr>
                                <td>{{ $user->name }}</td>
                                <td>{{ $user->email }}</td>
                                <td>{{ $user->tipo_usuario_formatado }}</td>
                                <td>
                                    @if($user->roles->isEmpty())
                                        <span class="badge badge-warning">Sem papéis</span>
                                    @else
                                        @foreach($user->roles as $role)
                                            <span class="badge {{ $role->is_active ? 'badge-info' : 'badge-secondary' }} mr-1">
                                                {{ $role->name }}
                                            </span>
                                        @endforeach
                                    @endif
                                </td>
                                <td>
                                    @if($user->ativo)
                                        <span class="badge badge-success">Ativo</span>
                                    @else
                                        <span class="badge badge-danger">Inativo</span>
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ route('admin.permissions.user-roles.edit', $user) }}" class="btn btn-sm btn-primary">
                                        <i class="fas fa-edit"></i> Editar Papéis
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    $(document).ready(function() {
        $('#usersTable').DataTable({
            language: {
                url: '//cdn.datatables.net/plug-ins/1.10.24/i18n/Portuguese-Brasil.json'
            }
        });
        
        // Garantir que os links de edição funcionem corretamente
        $('.btn-primary').on('click', function(e) {
            e.preventDefault();
            const href = $(this).attr('href');
            if (href && href.includes('user-roles')) {
                window.location.href = href.split('#')[0]; // Remove o hash se existir
            } else {
                window.location.href = href;
            }
        });
    });
</script>
@endsection 