<!-- Modal de Filtros -->
<div class="modal fade" id="filtersModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-light">
                <h5 class="modal-title">Filtrar Leads</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="filterForm" onsubmit="applyFilters(event)">
                    <div class="mb-3">
                        <label class="form-label">Busca</label>
                        <input type="text" class="form-control" name="busca" placeholder="Nome, email ou telefone">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Prioridade</label>
                        <select class="form-select" name="prioridade">
                            <option value="">Todas</option>
                            <option value="baixa">Baixa</option>
                            <option value="media">Média</option>
                            <option value="alta">Alta</option>
                            <option value="urgente">Urgente</option>
                        </select>
                    </div>

                    @if(session('admin_tipo') === 'admin')
                    <div class="mb-3">
                        <label class="form-label">Responsável</label>
                        <select class="form-select" name="responsavel">
                            <option value="">Todos</option>
                            @foreach($usuarios as $usuario)
                                <option value="{{ $usuario->id }}">{{ $usuario->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    @endif

                    <div class="mb-3">
                        <label class="form-label">Data de Cadastro</label>
                        <div class="row">
                            <div class="col">
                                <input type="date" class="form-control" name="data_inicio" placeholder="De">
                            </div>
                            <div class="col">
                                <input type="date" class="form-control" name="data_fim" placeholder="Até">
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-between">
                        <button type="button" class="btn btn-light" onclick="clearFilters()">
                            Limpar Filtros
                        </button>
                        <button type="submit" class="btn btn-primary">
                            Aplicar Filtros
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div> 