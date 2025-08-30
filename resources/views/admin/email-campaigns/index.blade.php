@extends('layouts.admin')

@section('title', 'Campanhas de Email')

@section('page-title', 'Campanhas de Email')

@section('page-actions')
    <div class="d-flex gap-2">
        <a href="{{ route('admin.email-campaigns.create') }}" class="btn btn-primary">
            <i class="fas fa-plus me-2"></i>
            Nova Campanha
        </a>
        <a href="{{ route('admin.email-campaigns.templates') }}" class="btn btn-outline-primary">
            <i class="fas fa-palette me-2"></i>
            Galeria de Templates
        </a>
    </div>
@endsection

@section('content')
<div class="container-fluid">
    <div class="card">
        <div class="card-body">
            @if($campaigns->isEmpty())
                <div class="text-center py-5">
                    <i class="fas fa-envelope fa-4x text-muted mb-3"></i>
                    <h3 class="text-muted">Nenhuma campanha encontrada</h3>
                    <p class="mb-4">Crie sua primeira campanha de email para começar a enviar mensagens para seus leads.</p>
                    <a href="{{ route('admin.email-campaigns.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus me-2"></i>
                        Criar Campanha
                    </a>
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Nome</th>
                                <th>Status</th>
                                <th>Destinatários</th>
                                <th>Taxa de Abertura</th>
                                <th width="200">Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($campaigns as $campaign)
                                <tr>
                                    <td>
                                        <a href="{{ route('admin.email-campaigns.show', $campaign->id) }}" class="fw-bold text-decoration-none">
                                            {{ $campaign->name }}
                                        </a>
                                        <div class="small text-muted">{{ $campaign->subject }}</div>
                                    </td>
                                    <td>
                                        @if($campaign->status === 'draft')
                                            <span class="badge bg-secondary">Rascunho</span>
                                        @elseif($campaign->status === 'scheduled')
                                            <span class="badge bg-info">Agendada</span>
                                        @elseif($campaign->status === 'sending')
                                            <span class="badge bg-warning">Enviando</span>
                                        @elseif($campaign->status === 'sent')
                                            <span class="badge bg-success">Enviada</span>
                                        @elseif($campaign->status === 'canceled')
                                            <span class="badge bg-danger">Cancelada</span>
                                        @endif
                                    </td>
                                    <td>
                                        {{ $campaign->total_recipients }}
                                        @if($campaign->status === 'sent' || $campaign->status === 'sending')
                                            <div class="small text-muted">
                                                {{ $campaign->sent_count }} enviados / {{ $campaign->failed_count }} falhas
                                            </div>
                                        @endif
                                    </td>
                                    <td>
                                        @if($campaign->status === 'sent')
                                            <div class="d-flex align-items-center">
                                                <div class="progress flex-grow-1 me-2" style="height: 6px;">
                                                    <div class="progress-bar bg-success" role="progressbar" 
                                                         style="width: {{ $campaign->openRate() }}%;" 
                                                         aria-valuenow="{{ $campaign->openRate() }}" 
                                                         aria-valuemin="0" 
                                                         aria-valuemax="100"></div>
                                                </div>
                                                <span>{{ $campaign->openRate() }}%</span>
                                            </div>
                                            <div class="small text-muted">
                                                {{ $campaign->opened_count }} aberturas / {{ $campaign->clicked_count }} cliques
                                            </div>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>

                                    <td>
                                        <div class="btn-group" role="group">
                                            <a href="{{ route('admin.email-campaigns.show', $campaign->id) }}" class="btn btn-sm btn-outline-primary" title="Visualizar">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            
                                            @if($campaign->canEdit())
                                                <a href="{{ route('admin.email-campaigns.edit', $campaign->id) }}" class="btn btn-sm btn-outline-secondary" title="Editar">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                            @endif
                                            
                                            @if($campaign->canSend() && $campaign->status !== 'scheduled')
                                                <button type="button" class="btn btn-sm btn-outline-success" 
                                                        onclick="confirmSend({{ $campaign->id }})" title="Enviar">
                                                    <i class="fas fa-paper-plane"></i>
                                                </button>
                                            @endif
                                            
                                            @if($campaign->canCancel())
                                                <button type="button" class="btn btn-sm btn-outline-warning" 
                                                        onclick="confirmCancel({{ $campaign->id }})" title="Cancelar">
                                                    <i class="fas fa-ban"></i>
                                                </button>
                                            @endif
                                            
                                            @if(!$campaign->isCompleted())
                                                <button type="button" class="btn btn-sm btn-outline-danger" 
                                                        onclick="confirmDelete({{ $campaign->id }}, '{{ $campaign->name }}')" title="Excluir">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            @endif
                                        </div>
                                        
                                        <form id="send-form-{{ $campaign->id }}" action="{{ route('admin.email-campaigns.send', $campaign->id) }}" method="POST" style="display: none;">
                                            @csrf
                                        </form>
                                        
                                        <form id="cancel-form-{{ $campaign->id }}" action="{{ route('admin.email-campaigns.cancel', $campaign->id) }}" method="POST" style="display: none;">
                                            @csrf
                                        </form>
                                        
                                        <form id="delete-form-{{ $campaign->id }}" action="{{ route('admin.email-campaigns.destroy', $campaign->id) }}" method="POST" style="display: none;">
                                            @csrf
                                            @method('DELETE')
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Modal de Confirmação de Exclusão -->
<div class="modal fade" id="modalExclusao" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirmar Exclusão</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Tem certeza que deseja excluir a campanha <strong id="nomeCampanha"></strong>?</p>
                <p class="text-danger small">Esta ação não pode ser desfeita.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <form id="formExclusao" method="POST" style="display: inline;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-trash me-1"></i>
                        Excluir
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        // Configurar Toastr
        toastr.options = {
            closeButton: true,
            progressBar: true,
            positionClass: 'toast-top-right',
            timeOut: 5000,
            extendedTimeOut: 2000,
            showEasing: 'swing',
            hideEasing: 'linear',
            showMethod: 'fadeIn',
            hideMethod: 'fadeOut'
        };
        
        // Exibir mensagens do backend usando Toastr
        @if(session('success'))
            toastr.success('{{ session('success') }}');
        @endif
        
        @if(session('error'))
            toastr.error('{{ session('error') }}');
        @endif

        window.confirmSend = function(campaignId) {
            if (confirm('Tem certeza que deseja enviar esta campanha? Esta ação não pode ser desfeita.')) {
                document.getElementById('send-form-' + campaignId).submit();
            }
        };
        
        window.confirmCancel = function(campaignId) {
            if (confirm('Tem certeza que deseja cancelar esta campanha? Esta ação não pode ser desfeita.')) {
                document.getElementById('cancel-form-' + campaignId).submit();
            }
        };
        
        window.confirmDelete = function(campaignId, nome) {
            document.getElementById('nomeCampanha').textContent = nome;
            document.getElementById('formExclusao').action = `/dashboard/email-campaigns/${campaignId}`;
            new bootstrap.Modal(document.getElementById('modalExclusao')).show();
        };
    });
</script>
@endpush 