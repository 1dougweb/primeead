@if($inscricoes->count() > 0)
    <!-- Versão Desktop -->
    <div class="d-none d-md-block">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>ID</th>
                        <th>Nome</th>
                        <th>Contato</th>
                        <th>Curso</th>
                        <th>Modalidade</th>
                        <th>Status</th>
                        <th>Data</th>
                        <th width="180">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($inscricoes as $inscricao)
                        <tr class="{{ $inscricao->isLocked() ? 'table-warning' : '' }}">
                            <td>
                                <span class="badge bg-light text-dark">{{ $inscricao->id }}</span>
                                @if($inscricao->isLocked())
                                    <br><small class="text-warning"><i class="fas fa-lock me-1"></i>Travado</small>
                                @endif
                            </td>
                            <td>
                                <div class="fw-medium">{{ $inscricao->nome }}</div>
                                @if($inscricao->isLocked())
                                    <small class="text-muted">
                                        <i class="fas fa-user me-1"></i>
                                        {{ $inscricao->lockedBy->name ?? 'N/A' }}
                                    </small>
                                @endif
                            </td>
                            <td>
                                <div class="d-flex flex-column">
                                    <a href="mailto:{{ $inscricao->email }}" class="text-decoration-none mb-1">
                                        <i class="fas fa-envelope me-1 text-muted"></i>
                                        {{ $inscricao->email }}
                                    </a>
                                    <div class="d-flex align-items-center">
                                        <a href="tel:{{ $inscricao->telefone }}" class="text-decoration-none me-2">
                                            <i class="fas fa-phone me-1 text-muted"></i>
                                            {{ $inscricao->telefone }}
                                        </a>
                                        <a href="https://wa.me/55{{ preg_replace('/[^0-9]/', '', $inscricao->telefone) }}" 
                                           target="_blank" 
                                           class="btn btn-sm btn-success">
                                            <i class="fab fa-whatsapp"></i>
                                        </a>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="badge bg-primary">
                                    {{ $inscricao->curso ?? 'N/A' }}
                                </span>
                            </td>
                            <td>
                                <span class="badge bg-info">
                                    {{ $inscricao->modalidade ?? 'N/A' }}
                                </span>
                            </td>
                            <td>
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
                                        <span class="badge bg-secondary">{{ $inscricao->etiqueta ?? 'N/A' }}</span>
                                @endswitch
                            </td>
                            <td>
                                <div>{{ $inscricao->created_at->format('d/m/Y') }}</div>
                                <small class="text-muted">{{ $inscricao->created_at->format('H:i') }}</small>
                            </td>
                            <td>
                                <div class="btn-group">
                                    @if($inscricao->isLocked() && $inscricao->locked_by == auth()->id())
                                        <button type="button" 
                                                class="btn btn-warning btn-sm" 
                                                onclick="destravar({{ $inscricao->id }})"
                                                title="Liberar Lead">
                                            <i class="fas fa-unlock"></i>
                                        </button>
                                    @else
                                        <button type="button" 
                                                class="btn btn-primary btn-sm" 
                                                onclick="pegarLead({{ $inscricao->id }})"
                                                {{ $inscricao->isLocked() ? 'disabled' : '' }}
                                                title="{{ $inscricao->isLocked() ? 'Lead Indisponível' : 'Pegar Lead' }}">
                                            <i class="fas fa-hand-paper"></i>
                                        </button>
                                    @endif

                                    <button type="button" 
                                            class="btn btn-info btn-sm" 
                                            onclick="verDetalhes({{ $inscricao->id }})"
                                            title="Ver Detalhes">
                                        <i class="fas fa-eye"></i>
                                    </button>

                                    <a href="{{ route('admin.inscricoes.editar', $inscricao->id) }}" 
                                       class="btn btn-secondary btn-sm"
                                       {{ $inscricao->isLockedByOther(auth()->id()) ? 'disabled' : '' }}
                                       title="Editar">
                                        <i class="fas fa-edit"></i>
                                    </a>

                                    @if(auth()->user()->isAdmin())
                                        <button type="button" 
                                                class="btn btn-danger btn-sm" 
                                                onclick="confirmarExclusao({{ $inscricao->id }}, '{{ $inscricao->nome }}')"
                                                {{ $inscricao->isLockedByOther(auth()->id()) ? 'disabled' : '' }}
                                                title="Excluir">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- Versão Mobile -->
    <div class="d-md-none">
        <div class="inscricoes-grid">
            @foreach($inscricoes as $inscricao)
                <div class="card mb-3 {{ $inscricao->isLocked() ? 'border-warning' : '' }}">
                    <div class="card-body p-3">
                        <!-- Cabeçalho do Card -->
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <div>
                                <h6 class="mb-0 fw-bold">{{ $inscricao->nome }}</h6>
                                <small class="text-muted">ID #{{ $inscricao->id }}</small>
                            </div>
                            @if($inscricao->isLocked())
                                <span class="badge bg-warning text-dark">
                                    <i class="fas fa-lock me-1"></i>Travado
                                </span>
                            @endif
                        </div>

                        <!-- Status e Curso/Modalidade -->
                        <div class="mb-3">
                            <div class="d-flex gap-2 mb-2">
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
                                        <span class="badge bg-secondary">{{ $inscricao->etiqueta ?? 'N/A' }}</span>
                                @endswitch
                            </div>
                            <div class="d-flex gap-2">
                                <span class="badge bg-primary">{{ $inscricao->curso ?? 'N/A' }}</span>
                                <span class="badge bg-info">{{ $inscricao->modalidade ?? 'N/A' }}</span>
                            </div>
                        </div>

                        <!-- Status do Lead -->
                        @if($inscricao->isLocked())
                            <div class="alert alert-warning py-1 px-2 mb-2">
                                <small>
                                    <i class="fas fa-user me-1"></i>
                                    Em atendimento por: {{ $inscricao->lockedBy->name ?? 'N/A' }}
                                </small>
                            </div>
                        @endif

                        <!-- Informações de Contato -->
                        <div class="contact-info mb-3">
                            <div class="d-flex align-items-center mb-2">
                                <div class="flex-grow-1">
                                    <a href="mailto:{{ $inscricao->email }}" class="text-decoration-none d-block text-truncate">
                                        <i class="fas fa-envelope me-2 text-muted"></i>
                                        {{ $inscricao->email }}
                                    </a>
                                </div>
                            </div>
                            <div class="d-flex align-items-center">
                                <div class="flex-grow-1">
                                    <a href="tel:{{ $inscricao->telefone }}" class="text-decoration-none">
                                        <i class="fas fa-phone me-2 text-muted"></i>
                                        {{ $inscricao->telefone }}
                                    </a>
                                </div>
                                <a href="https://wa.me/55{{ preg_replace('/[^0-9]/', '', $inscricao->telefone) }}" 
                                   target="_blank" 
                                   class="btn btn-success btn-sm ms-2">
                                    <i class="fab fa-whatsapp"></i>
                                    WhatsApp
                                </a>
                            </div>
                        </div>

                        <!-- Data -->
                        <div class="text-muted mb-3">
                            <i class="fas fa-calendar me-2"></i>
                            {{ $inscricao->created_at->format('d/m/Y H:i') }}
                        </div>

                        <!-- Botões de Ação -->
                        <div class="d-flex gap-2">
                            @if($inscricao->isLocked() && $inscricao->locked_by == auth()->id())
                                <button type="button" 
                                        class="btn btn-warning flex-grow-1" 
                                        onclick="destravar({{ $inscricao->id }})">
                                    <i class="fas fa-unlock me-2"></i>
                                    Liberar Lead
                                </button>
                            @else
                                <button type="button" 
                                        class="btn btn-primary flex-grow-1" 
                                        onclick="pegarLead({{ $inscricao->id }})"
                                        {{ $inscricao->isLocked() ? 'disabled' : '' }}>
                                    <i class="fas fa-hand-paper me-2"></i>
                                    {{ $inscricao->isLocked() ? 'Lead Indisponível' : 'Pegar Lead' }}
                                </button>
                            @endif
                        </div>
                        <div class="d-flex gap-2 mt-2">
                            <button type="button" 
                                    class="btn btn-info flex-grow-1" 
                                    onclick="verDetalhes({{ $inscricao->id }})">
                                <i class="fas fa-eye me-2"></i>
                                Detalhes
                            </button>

                            <a href="{{ route('admin.inscricoes.editar', $inscricao->id) }}" 
                               class="btn btn-secondary flex-grow-1"
                               {{ $inscricao->isLockedByOther(auth()->id()) ? 'disabled' : '' }}>
                                <i class="fas fa-edit me-2"></i>
                                Editar
                            </a>

                            @if(auth()->user()->isAdmin())
                                <button type="button" 
                                        class="btn btn-danger flex-grow-1" 
                                        onclick="confirmarExclusao({{ $inscricao->id }}, '{{ $inscricao->nome }}')"
                                        {{ $inscricao->isLockedByOther(auth()->id()) ? 'disabled' : '' }}>
                                    <i class="fas fa-trash me-2"></i>
                                    Excluir
                                </button>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
    
    <!-- Paginação -->
    <div class="card-footer bg-white border-0 pt-3">
        {{ $inscricoes->links() }}
    </div>
@else
    <div class="text-center py-5">
        <img src="{{ asset('images/no-data.svg') }}" alt="Sem dados" class="img-fluid mb-3" style="max-width: 200px;">
        <h4 class="text-muted">Nenhuma inscrição encontrada</h4>
        <p class="text-muted">
            @if(request()->hasAny(['busca', 'curso', 'data_inicio', 'data_fim']))
                Tente remover alguns filtros para ver mais resultados.
            @else
                Não há inscrições cadastradas no sistema.
            @endif
        </p>
    </div>
@endif 