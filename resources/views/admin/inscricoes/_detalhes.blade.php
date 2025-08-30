<!-- Informações Básicas -->
<div class="row">
    <div class="col-md-6">
        <h6 class="mb-3">Informações Pessoais</h6>
        <dl class="row">
            <dt class="col-sm-4">Nome</dt>
            <dd class="col-sm-8">{{ $inscricao->nome }}</dd>
            
            <dt class="col-sm-4">Email</dt>
            <dd class="col-sm-8">
                <a href="mailto:{{ $inscricao->email }}" class="text-decoration-none">
                    {{ $inscricao->email }}
                </a>
            </dd>
            
            <dt class="col-sm-4">Telefone</dt>
            <dd class="col-sm-8">
                <a href="tel:{{ $inscricao->telefone }}" class="text-decoration-none">
                    {{ $inscricao->telefone }}
                </a>
                <a href="https://wa.me/55{{ preg_replace('/[^0-9]/', '', $inscricao->telefone) }}" 
                   target="_blank" 
                   class="btn btn-sm btn-success ms-2">
                    <i class="fab fa-whatsapp"></i> WhatsApp
                </a>
            </dd>
        </dl>
    </div>
    
    <div class="col-md-6">
        <h6 class="mb-3">Informações do Curso</h6>
        <dl class="row">
            <dt class="col-sm-4">Curso</dt>
            <dd class="col-sm-8">
                <span class="badge bg-primary">{{ $cursos[$inscricao->curso] ?? $inscricao->curso }}</span>
            </dd>
            
            <dt class="col-sm-4">Modalidade</dt>
            <dd class="col-sm-8">
                <span class="badge bg-success">{{ $modalidades[$inscricao->modalidade] ?? $inscricao->modalidade }}</span>
            </dd>
            
            <dt class="col-sm-4">Status</dt>
            <dd class="col-sm-8">
                @switch($inscricao->etiqueta)
                    @case('pendente')
                        <span class="badge bg-warning">🟡 Pendente</span>
                        @break
                    @case('contatado')
                        <span class="badge bg-info">🔵 Contatado</span>
                        @break
                    @case('interessado')
                        <span class="badge bg-success">🟢 Interessado</span>
                        @break
                    @case('nao_interessado')
                        <span class="badge bg-danger">🔴 Não Interessado</span>
                        @break
                    @case('matriculado')
                        <span class="badge bg-primary">⭐ Matriculado</span>
                        @break
                    @default
                        <span class="badge bg-secondary">{{ $inscricao->etiqueta }}</span>
                @endswitch
            </dd>
        </dl>
    </div>
</div>

<hr>

<!-- Histórico de Status -->
<div class="row mt-4">
    <div class="col-12">
        <h6 class="mb-3">Histórico de Status</h6>
        @if($inscricao->statusHistories->count() > 0)
            <div class="table-responsive">
                <table class="table table-sm">
                    <thead>
                        <tr>
                            <th>Data</th>
                            <th>Status Anterior</th>
                            <th>Novo Status</th>
                            <th>Alterado por</th>
                            <th>Observações</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($inscricao->statusHistories->sortByDesc('created_at') as $history)
                            <tr>
                                <td>{{ $history->data_alteracao ? $history->data_alteracao->format('d/m/Y H:i:s') : $history->created_at->format('d/m/Y H:i:s') }}</td>
                                <td>
                                    @switch($history->status_anterior)
                                        @case('pendente')
                                            <span class="badge bg-warning">🟡 Pendente</span>
                                            @break
                                        @case('contatado')
                                            <span class="badge bg-info">🔵 Contatado</span>
                                            @break
                                        @case('interessado')
                                            <span class="badge bg-success">🟢 Interessado</span>
                                            @break
                                        @case('nao_interessado')
                                            <span class="badge bg-danger">🔴 Não Interessado</span>
                                            @break
                                        @case('matriculado')
                                            <span class="badge bg-primary">⭐ Matriculado</span>
                                            @break
                                        @default
                                            <span class="badge bg-secondary">{{ $history->status_anterior }}</span>
                                    @endswitch
                                </td>
                                <td>
                                    @switch($history->status_novo)
                                        @case('pendente')
                                            <span class="badge bg-warning">🟡 Pendente</span>
                                            @break
                                        @case('contatado')
                                            <span class="badge bg-info">🔵 Contatado</span>
                                            @break
                                        @case('interessado')
                                            <span class="badge bg-success">🟢 Interessado</span>
                                            @break
                                        @case('nao_interessado')
                                            <span class="badge bg-danger">🔴 Não Interessado</span>
                                            @break
                                        @case('matriculado')
                                            <span class="badge bg-primary">⭐ Matriculado</span>
                                            @break
                                        @default
                                            <span class="badge bg-secondary">{{ $history->status_novo }}</span>
                                    @endswitch
                                </td>
                                <td>{{ $history->alterado_por ?? 'N/A' }}</td>
                                <td>{{ $history->observacoes ?? '-' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="alert alert-info">
                <i class="fas fa-info-circle me-2"></i>
                Nenhuma alteração de status registrada.
            </div>
        @endif
    </div>
</div>

<!-- Informações do Sistema -->
<div class="row mt-4">
    <div class="col-12">
        <h6 class="mb-3">Informações do Sistema</h6>
        <dl class="row">
            <dt class="col-sm-3">ID</dt>
            <dd class="col-sm-9">#{{ $inscricao->id }}</dd>
            
            <dt class="col-sm-3">Data de Inscrição</dt>
            <dd class="col-sm-9">{{ $inscricao->created_at->format('d/m/Y H:i:s') }}</dd>
            
            <dt class="col-sm-3">IP</dt>
            <dd class="col-sm-9">{{ $inscricao->ip_address ?? 'N/A' }}</dd>
            
            <dt class="col-sm-3">Aceita Termos</dt>
            <dd class="col-sm-9">
                @if($inscricao->termos)
                    <span class="badge bg-success">Sim</span>
                @else
                    <span class="badge bg-danger">Não</span>
                @endif
            </dd>
            
            @if($inscricao->isLocked())
                <dt class="col-sm-3">Travado por</dt>
                <dd class="col-sm-9">
                    <span class="text-warning">
                        <i class="fas fa-lock me-1"></i>
                        {{ $inscricao->lockedBy->name ?? 'N/A' }}
                    </span>
                </dd>
                
                <dt class="col-sm-3">Travado em</dt>
                <dd class="col-sm-9">{{ $inscricao->locked_at ? $inscricao->locked_at->format('d/m/Y H:i:s') : 'N/A' }}</dd>
            @endif
        </dl>
    </div>
</div> 