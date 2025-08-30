@extends('layouts.admin')

@section('title', 'Detalhes da Inscrição')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="h3 mb-0">
                        <i class="fas fa-user-graduate me-2"></i>
                        Detalhes da Inscrição
                    </h1>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item">
                                <a href="{{ route('admin.inscricoes') }}">Inscrições</a>
                            </li>
                            <li class="breadcrumb-item active" aria-current="page">
                                {{ $inscricao->nome }}
                            </li>
                        </ol>
                    </nav>
                </div>
                
                <div class="d-flex gap-2">
                    <a href="{{ route('admin.inscricoes') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left me-2"></i>Voltar
                    </a>
                    
                    @can('inscricoes.edit')
                        <a href="{{ route('admin.inscricoes.editar', $inscricao->id) }}" class="btn btn-primary">
                            <i class="fas fa-edit me-2"></i>Editar
                        </a>
                    @endcan
                    
                    @if($inscricao->isLocked())
                        @if($inscricao->isLockedBy(auth()->id()))
                            <button type="button" class="btn btn-warning" onclick="destravar({{ $inscricao->id }})">
                                <i class="fas fa-unlock me-2"></i>Destravar
                            </button>
                        @else
                            <span class="btn btn-secondary" disabled>
                                <i class="fas fa-lock me-2"></i>Travado por {{ $inscricao->lockedBy->name ?? 'Usuário' }}
                            </span>
                        @endif
                    @else
                        <button type="button" class="btn btn-success" onclick="pegarLead({{ $inscricao->id }})">
                            <i class="fas fa-hand-paper me-2"></i>Pegar Lead
                        </button>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-info-circle me-2 text-primary"></i>
                        Informações da Inscrição #{{ $inscricao->id }}
                    </h5>
                </div>
                <div class="card-body">
                    @include('admin.inscricoes._detalhes')
                </div>
            </div>
        </div>
    </div>
</div>

@include('admin.inscricoes._modals')
@endsection

@push('scripts')
<script>
    // Função para pegar lead
    function pegarLead(id) {
        fetch(`/dashboard/inscricoes/${id}/etiqueta`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({
                etiqueta: 'contatado',
                observacoes: 'Lead atribuído automaticamente'
            })
        })
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                toastr.success('Lead atribuído com sucesso!');
                // Recarregar a página para mostrar as mudanças
                setTimeout(() => {
                    window.location.reload();
                }, 1000);
            } else {
                toastr.error(data.message || 'Erro ao atribuir lead');
            }
        })
        .catch(error => {
            console.error('Erro:', error);
            toastr.error('Erro ao atribuir lead');
        });
    }

    // Função para destravar
    function destravar(id) {
        if (confirm('Tem certeza que deseja liberar este lead?')) {
            fetch(`/dashboard/inscricoes/${id}/unlock`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    toastr.success('Lead liberado com sucesso!');
                    // Recarregar a página para mostrar as mudanças
                    setTimeout(() => {
                        window.location.reload();
                    }, 1000);
                } else {
                    toastr.error(data.message || 'Erro ao liberar lead');
                }
            })
            .catch(error => {
                console.error('Erro:', error);
                toastr.error('Erro ao liberar lead');
            });
        }
    }

    // Função para atualizar status
    function atualizarStatus() {
        const novoStatus = document.getElementById('novoStatus').value;
        const observacoes = document.getElementById('observacoes').value;
        
        if (!novoStatus) {
            toastr.error('Selecione um status válido');
            return;
        }

        fetch(`/dashboard/inscricoes/{{ $inscricao->id }}/etiqueta`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({
                etiqueta: novoStatus,
                observacoes: observacoes
            })
        })
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                toastr.success('Status atualizado com sucesso!');
                // Fechar modal e recarregar página
                const modal = bootstrap.Modal.getInstance(document.getElementById('modalEditarStatus'));
                modal.hide();
                setTimeout(() => {
                    window.location.reload();
                }, 1000);
            } else {
                toastr.error(data.message || 'Erro ao atualizar status');
            }
        })
        .catch(error => {
            console.error('Erro:', error);
            toastr.error('Erro ao atualizar status');
        });
    }
</script>
@endpush
