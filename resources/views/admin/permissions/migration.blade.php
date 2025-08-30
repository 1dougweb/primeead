@extends('layouts.admin')

@section('title', 'Migração de Permissões')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">
                        <i class="fas fa-exchange-alt me-2"></i>
                        Migração de Permissões para Spatie
                    </h4>
                    <p class="card-subtitle text-muted">
                        Migre as permissões do sistema antigo para o Spatie Laravel Permission
                    </p>
                </div>
                <div class="card-body">
                    <!-- Status da Migração -->
                    <div class="row mb-4">
                        <div class="col-md-3">
                            <div class="card bg-primary text-white">
                                <div class="card-body text-center">
                                    <h3 class="mb-0">{{ $stats['permissions']['current'] }}</h3>
                                    <small>Permissões Atuais</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-success text-white">
                                <div class="card-body text-center">
                                    <h3 class="mb-0">{{ $stats['roles']['current'] }}</h3>
                                    <small>Roles Atuais</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-info text-white">
                                <div class="card-body text-center">
                                    <h3 class="mb-0">{{ $stats['users']['total'] }}</h3>
                                    <small>Usuários</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-warning text-white">
                                <div class="card-body text-center">
                                    <h3 class="mb-0">{{ $stats['users']['without_roles'] }}</h3>
                                    <small>Sem Roles</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Status da Migração -->
                    <div class="alert alert-info">
                        <h5><i class="fas fa-info-circle me-2"></i>Status da Migração</h5>
                        <div class="row">
                            <div class="col-md-6">
                                <strong>Migração Executada:</strong> 
                                @if($stats['migration_status']['migrated'])
                                    <span class="badge bg-success">Sim</span>
                                @else
                                    <span class="badge bg-danger">Não</span>
                                @endif
                            </div>
                            <div class="col-md-6">
                                <strong>Dados Antigos:</strong> 
                                @if($stats['migration_status']['has_old_data'])
                                    <span class="badge bg-warning">Encontrados</span>
                                @else
                                    <span class="badge bg-secondary">Não encontrados</span>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Tabelas Antigas -->
                    @if($stats['migration_status']['has_old_data'])
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5><i class="fas fa-database me-2"></i>Tabelas Antigas</h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-sm">
                                    <thead>
                                        <tr>
                                            <th>Tabela</th>
                                            <th>Registros</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($stats['permissions']['old_tables'] as $table => $count)
                                        <tr>
                                            <td><code>{{ $table }}</code></td>
                                            <td>
                                                @if($count > 0)
                                                    <span class="badge bg-warning">{{ $count }}</span>
                                                @else
                                                    <span class="badge bg-secondary">0</span>
                                                @endif
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    @endif

                    <!-- Roles Atuais -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5><i class="fas fa-users-cog me-2"></i>Roles Atuais</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                @foreach($stats['roles']['list'] as $role)
                                <div class="col-md-4 mb-2">
                                    <div class="d-flex justify-content-between align-items-center p-2 border rounded">
                                        <span>{{ $role['name'] }}</span>
                                        <span class="badge bg-primary">{{ $role['permissions_count'] }} permissões</span>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    <!-- Ações -->
                    <div class="card">
                        <div class="card-header">
                            <h5><i class="fas fa-cogs me-2"></i>Ações</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <button type="button" class="btn btn-primary btn-lg w-100 mb-2" id="btnMigrate">
                                        <i class="fas fa-exchange-alt me-2"></i>
                                        Executar Migração
                                    </button>
                                    
                                    @if($stats['migration_status']['needs_force'])
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="forceMigration">
                                        <label class="form-check-label" for="forceMigration">
                                            Forçar migração (executar novamente)
                                        </label>
                                    </div>
                                    @endif
                                </div>
                                <div class="col-md-6">
                                    <button type="button" class="btn btn-warning btn-lg w-100 mb-2" id="btnClearCache">
                                        <i class="fas fa-broom me-2"></i>
                                        Limpar Cache
                                    </button>
                                    
                                    <button type="button" class="btn btn-info btn-lg w-100" id="btnRefresh">
                                        <i class="fas fa-sync-alt me-2"></i>
                                        Atualizar Status
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Log de Execução -->
                    <div class="card mt-4" id="logCard" style="display: none;">
                        <div class="card-header">
                            <h5><i class="fas fa-terminal me-2"></i>Log de Execução</h5>
                        </div>
                        <div class="card-body">
                            <pre id="executionLog" class="bg-dark text-light p-3 rounded" style="max-height: 300px; overflow-y: auto;"></pre>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Loading Modal -->
<div class="modal fade" id="loadingModal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-body text-center">
                <div class="spinner-border text-primary mb-3" role="status">
                    <span class="visually-hidden">Carregando...</span>
                </div>
                <h5>Executando migração...</h5>
                <p class="text-muted">Aguarde, isso pode levar alguns segundos.</p>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Executar migração
    $('#btnMigrate').click(function() {
        const force = $('#forceMigration').is(':checked');
        
        if (!confirm('Tem certeza que deseja executar a migração?' + (force ? ' (modo forçado)' : ''))) {
            return;
        }

        const btn = $(this);
        const originalText = btn.html();
        
        btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-2"></i>Executando...');
        $('#loadingModal').modal('show');
        $('#logCard').hide();

        $.ajax({
            url: '{{ route("admin.permissions.migration.migrate") }}',
            method: 'POST',
            data: {
                force: force
            },
            success: function(response) {
                $('#loadingModal').modal('hide');
                btn.prop('disabled', false).html(originalText);
                
                if (response.success) {
                    showAlert('success', response.message);
                    $('#executionLog').text(response.output);
                    $('#logCard').show();
                    updateStats(response.stats);
                } else {
                    showAlert('danger', response.message);
                    if (response.output) {
                        $('#executionLog').text(response.output);
                        $('#logCard').show();
                    }
                }
            },
            error: function(xhr) {
                $('#loadingModal').modal('hide');
                btn.prop('disabled', false).html(originalText);
                
                const response = xhr.responseJSON;
                showAlert('danger', response?.message || 'Erro ao executar migração');
                
                if (response?.output) {
                    $('#executionLog').text(response.output);
                    $('#logCard').show();
                }
            }
        });
    });

    // Limpar cache
    $('#btnClearCache').click(function() {
        const btn = $(this);
        const originalText = btn.html();
        
        btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-2"></i>Limpando...');

        $.ajax({
            url: '{{ route("admin.permissions.migration.clear-cache") }}',
            method: 'POST',
            data: {},
            success: function(response) {
                btn.prop('disabled', false).html(originalText);
                showAlert('success', response.message);
            },
            error: function(xhr) {
                btn.prop('disabled', false).html(originalText);
                const response = xhr.responseJSON;
                showAlert('danger', response?.message || 'Erro ao limpar cache');
            }
        });
    });

    // Atualizar status
    $('#btnRefresh').click(function() {
        const btn = $(this);
        const originalText = btn.html();
        
        btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-2"></i>Atualizando...');

        $.ajax({
            url: '{{ route("admin.permissions.migration.status") }}',
            method: 'GET',
            success: function(response) {
                btn.prop('disabled', false).html(originalText);
                updateStats(response.stats);
                showAlert('success', 'Status atualizado com sucesso!');
            },
            error: function() {
                btn.prop('disabled', false).html(originalText);
                showAlert('danger', 'Erro ao atualizar status');
            }
        });
    });

    // Função para mostrar alertas
    function showAlert(type, message) {
        const alertHtml = `
            <div class="alert alert-${type} alert-dismissible fade show" role="alert">
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        `;
        $('.card-body').prepend(alertHtml);
        
        // Auto-remover após 5 segundos
        setTimeout(function() {
            $('.alert').fadeOut();
        }, 5000);
    }

    // Função para atualizar estatísticas
    function updateStats(stats) {
        // Atualizar contadores
        $('.card.bg-primary h3').text(stats.permissions.current);
        $('.card.bg-success h3').text(stats.roles.current);
        $('.card.bg-info h3').text(stats.users.total);
        $('.card.bg-warning h3').text(stats.users.without_roles);
        
        // Recarregar página para atualizar completamente
        setTimeout(function() {
            location.reload();
        }, 2000);
    }
});
</script>
@endpush 