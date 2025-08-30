<form id="leadEditForm" method="POST" action="{{ route('admin.inscricoes.atualizar', $inscricao->id ?? 0) }}">
    @csrf
    @method('PUT')
    
    <div class="row">
        <div class="col-md-6">
            <div class="mb-3">
                <label for="nome" class="form-label">
                    <i class="fas fa-user me-2"></i>Nome Completo
                </label>
                <input type="text" class="form-control" id="nome" name="nome" value="{{ $inscricao->nome ?? '' }}" required>
            </div>
        </div>
        
        <div class="col-md-6">
            <div class="mb-3">
                <label for="email" class="form-label">
                    <i class="fas fa-envelope me-2"></i>E-mail
                </label>
                <input type="email" class="form-control" id="email" name="email" value="{{ $inscricao->email ?? '' }}" required>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6">
            <div class="mb-3">
                <label for="telefone" class="form-label">
                    <i class="fas fa-phone me-2"></i>Telefone
                </label>
                <input type="text" class="form-control" id="telefone" name="telefone" value="{{ $inscricao->telefone ?? '' }}" required>
            </div>
        </div>
        
        <div class="col-md-6">
            <div class="mb-3">
                <label for="etiqueta" class="form-label">
                    <i class="fas fa-tag me-2"></i>Status
                </label>
                <select class="form-select" id="etiqueta" name="etiqueta" required>
                    <option value="pendente" {{ ($inscricao->etiqueta ?? '') === 'pendente' ? 'selected' : '' }}>🟡 Pendente</option>
                    <option value="contatado" {{ ($inscricao->etiqueta ?? '') === 'contatado' ? 'selected' : '' }}>🔵 Contatado</option>
                    <option value="interessado" {{ ($inscricao->etiqueta ?? '') === 'interessado' ? 'selected' : '' }}>🟢 Interessado</option>
                    <option value="nao_interessado" {{ ($inscricao->etiqueta ?? '') === 'nao_interessado' ? 'selected' : '' }}>🔴 Não Interessado</option>
                    <option value="matriculado" {{ ($inscricao->etiqueta ?? '') === 'matriculado' ? 'selected' : '' }}>⭐ Matriculado</option>
                </select>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6">
            <div class="mb-3">
                <label for="curso" class="form-label">
                    <i class="fas fa-graduation-cap me-2"></i>Curso
                </label>
                <select class="form-select" id="curso" name="curso" required>
                    @foreach($cursos as $key => $label)
                        <option value="{{ $key }}" {{ ($inscricao->curso ?? '') === $key ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        
        <div class="col-md-6">
            <div class="mb-3">
                <label for="modalidade" class="form-label">
                    <i class="fas fa-cog me-2"></i>Modalidade
                </label>
                <select class="form-select" id="modalidade" name="modalidade" required>
                    @foreach($modalidades as $key => $label)
                        <option value="{{ $key }}" {{ ($inscricao->modalidade ?? '') === $key ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6">
            <div class="mb-3">
                <label for="prioridade" class="form-label">
                    <i class="fas fa-flag me-2"></i>Prioridade
                </label>
                <select class="form-select" id="prioridade" name="prioridade">
                    <option value="baixa" {{ ($inscricao->prioridade ?? '') === 'baixa' ? 'selected' : '' }}>Baixa</option>
                    <option value="media" {{ ($inscricao->prioridade ?? '') === 'media' ? 'selected' : '' }}>Média</option>
                    <option value="alta" {{ ($inscricao->prioridade ?? '') === 'alta' ? 'selected' : '' }}>Alta</option>
                    <option value="urgente" {{ ($inscricao->prioridade ?? '') === 'urgente' ? 'selected' : '' }}>Urgente</option>
                </select>
            </div>
        </div>
        
        <div class="col-md-6">
            <div class="mb-3">
                <label for="proximo_followup" class="form-label">
                    <i class="fas fa-calendar-alt me-2"></i>Próximo Follow-up
                </label>
                <input type="date" class="form-control" id="proximo_followup" name="proximo_followup" 
                       value="{{ $inscricao && $inscricao->proximo_followup ? $inscricao->proximo_followup->format('Y-m-d') : '' }}">
            </div>
        </div>
    </div>

    <div class="mb-3">
        <label for="notas" class="form-label">
            <i class="fas fa-sticky-note me-2"></i>Notas
        </label>
        <textarea class="form-control" id="notas" name="notas" rows="4" placeholder="Adicione notas sobre este lead...">{{ $inscricao->notas ?? '' }}</textarea>
    </div>

    <!-- Informações adicionais -->
    <div class="row mt-4">
        <div class="col-12">
            <h6 class="text-muted mb-3">
                <i class="fas fa-info-circle me-2"></i>Informações Adicionais
            </h6>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6">
            <div class="mb-3">
                <label class="form-label text-muted">Data de Cadastro:</label>
                <p class="mb-0">{{ $inscricao->created_at ? $inscricao->created_at->format('d/m/Y H:i:s') : 'N/A' }}</p>
            </div>
        </div>
        
        <div class="col-md-6">
            <div class="mb-3">
                <label class="form-label text-muted">IP de Origem:</label>
                <p class="mb-0">{{ $inscricao->ip_address ?? 'N/A' }}</p>
            </div>
        </div>
    </div>

    @if($inscricao && $inscricao->isLocked())
        <div class="row">
            <div class="col-12">
                <div class="alert alert-info">
                    <i class="fas fa-lock me-2"></i>
                    <strong>Lead em atendimento:</strong>
                    {{ $inscricao->locked_by == auth()->id() ? 'Você está' : ($inscricao->lockedBy->name ?? 'Usuário') . ' está' }} 
                    atendendo este lead.
                </div>
            </div>
        </div>
    @endif
</form> 