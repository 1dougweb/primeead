<!-- Modal de Edição de Lead -->
<div class="modal fade" id="leadModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-light">
                <h5 class="modal-title">Editar Lead</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-0">
                <!-- Abas -->
                <ul class="nav nav-tabs" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link active" data-bs-toggle="tab" href="#info-tab">
                            <i class="fas fa-info-circle me-2"></i>Informações
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" data-bs-toggle="tab" href="#tasks-tab">
                            <i class="fas fa-tasks me-2"></i>Tarefas
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" data-bs-toggle="tab" href="#history-tab">
                            <i class="fas fa-history me-2"></i>Histórico
                        </a>
                    </li>
                </ul>

                <!-- Conteúdo das Abas -->
                <div class="tab-content">
                    <!-- Aba de Informações -->
                    <div class="tab-pane fade show active" id="info-tab">
                        <form id="leadForm" class="p-4">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Nome</label>
                                        <input type="text" class="form-control" name="nome" readonly>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Email</label>
                                        <input type="email" class="form-control" name="email" readonly>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Telefone</label>
                                        <input type="tel" class="form-control" name="telefone" readonly>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Prioridade</label>
                                        <select class="form-select" name="prioridade">
                                            <option value="baixa">Baixa</option>
                                            <option value="media">Média</option>
                                            <option value="alta">Alta</option>
                                            <option value="urgente">Urgente</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Próximo Follow-up</label>
                                <input type="datetime-local" class="form-control" name="proximo_followup">
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Notas</label>
                                <textarea class="form-control" name="notas" rows="4"></textarea>
                            </div>

                            <button type="submit" class="btn btn-primary">Salvar Alterações</button>
                        </form>
                    </div>

                    <!-- Aba de Tarefas -->
                    <div class="tab-pane fade" id="tasks-tab">
                        <div class="p-4">
                            <div class="d-flex justify-content-between align-items-center mb-4">
                                <h6 class="mb-0">Lista de Tarefas</h6>
                                <button class="btn btn-sm btn-success" onclick="showNewTaskForm()">
                                    <i class="fas fa-plus me-2"></i>Nova Tarefa
                                </button>
                            </div>

                            <!-- Loading -->
                            <div id="tasksLoading" class="text-center py-4">
                                <div class="spinner-border text-primary" role="status">
                                    <span class="visually-hidden">Carregando...</span>
                                </div>
                            </div>

                            <!-- Lista de Tarefas -->
                            <div id="tasksContainer" class="d-none">
                                <div class="list-group" id="tasksList">
                                    <!-- Preenchido via JavaScript -->
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Aba de Histórico -->
                    <div class="tab-pane fade" id="history-tab">
                        <div class="p-4">
                            <div class="timeline" id="historyTimeline">
                                <!-- Preenchido via JavaScript -->
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div> 