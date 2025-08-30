<!-- Modal de Detalhes -->
<div class="modal fade" id="modalDetalhes" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Detalhes da Inscrição</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="detalhesConteudo">
                <div class="text-center py-4">
                    <div class="spinner-border text-primary"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal de Exclusão Individual -->
<div class="modal fade" id="modalExclusao" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    Confirmar Exclusão
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-danger" role="alert">
                    <h6 class="alert-heading">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        ATENÇÃO: Esta ação é IRREVERSÍVEL!
                    </h6>
                    <p class="mb-0">
                        Tem certeza que deseja excluir a inscrição de <strong id="nomeInscricao"></strong>?
                        Esta ação não pode ser desfeita!
                    </p>
                </div>
            </div>
            <div class="modal-footer">
                <form id="formExclusao" method="POST" style="display: inline;">
                    <?php echo csrf_field(); ?>
                    <?php echo method_field('DELETE'); ?>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-2"></i>Cancelar
                    </button>
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-trash me-2"></i>
                        Excluir
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modal de Confirmação para Apagar Todas as Inscrições -->
<div class="modal fade" id="modalApagarTodas" tabindex="-1" aria-labelledby="modalApagarTodasLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="modalApagarTodasLabel">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <?php if(auth()->user()->isAdmin()): ?>
                        Confirmar Exclusão em Massa
                    <?php else: ?>
                        Confirmar Exclusão dos Meus Leads
                    <?php endif; ?>
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-danger" role="alert">
                    <h6 class="alert-heading">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        ATENÇÃO: Esta ação é IRREVERSÍVEL!
                    </h6>
                    <p class="mb-0">
                        <?php if(auth()->user()->isAdmin()): ?>
                            Você está prestes a <strong>APAGAR TODAS AS INSCRIÇÕES</strong> do sistema. 
                            Esta ação não pode ser desfeita e resultará na perda permanente de todos os dados.
                        <?php else: ?>
                            Você está prestes a <strong>APAGAR SUAS INSCRIÇÕES TRAVADAS</strong>. 
                            Esta ação não pode ser desfeita e resultará na perda permanente dos seus leads.
                        <?php endif; ?>
                    </p>
                </div>
                
                <div class="mb-3">
                    <p class="text-muted mb-2">
                        <i class="fas fa-info-circle me-2"></i>
                        Para confirmar esta ação, digite <strong>CONFIRMAR</strong> no campo abaixo:
                    </p>
                    <input type="text" 
                           class="form-control" 
                           id="confirmacaoTexto" 
                           placeholder="Digite CONFIRMAR"
                           maxlength="10">
                    <div class="form-text text-muted">
                        <i class="fas fa-shield-alt me-1"></i>
                        Esta medida de segurança previne exclusões acidentais
                    </div>
                    

                </div>

                <div class="row">
                    <div class="col-6">
                        <div class="text-center p-3 bg-light rounded">
                            <h6 class="text-muted mb-1">
                                <?php if(auth()->user()->isAdmin()): ?>
                                    Total de Inscrições
                                <?php else: ?>
                                    Total do Sistema
                                <?php endif; ?>
                            </h6>
                            <h4 class="text-primary mb-0" id="totalInscricoes">0</h4>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="text-center p-3 bg-light rounded">
                            <h6 class="text-muted mb-1">
                                <?php if(auth()->user()->isAdmin()): ?>
                                    Inscrições Filtradas
                                <?php else: ?>
                                    Minhas Inscrições
                                <?php endif; ?>
                            </h6>
                            <h4 class="text-warning mb-0" id="inscricoesFiltradas">0</h4>
                        </div>
                    </div>
                </div>
                
                <?php if(!auth()->user()->isAdmin()): ?>
                    <div class="alert alert-info mt-3" role="alert">
                        <i class="fas fa-info-circle me-2"></i>
                        <strong>Escopo Limitado:</strong> Você só pode apagar inscrições que você travou (campo "locked_by").
                    </div>
                <?php endif; ?>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-2"></i>Cancelar
                </button>
                <button type="button" class="btn btn-danger" id="btnConfirmarApagar" disabled>
                    <i class="fas fa-trash-alt me-2"></i>Apagar Todas
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal para Edição Rápida de Status -->
<div class="modal fade" id="modalEditarStatus" tabindex="-1" aria-labelledby="modalEditarStatusLabel" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title" id="modalEditarStatusLabel">
                            <i class="fas fa-edit me-2"></i>
                            Alterar Status
                        </h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
            <div class="modal-body">
                <form id="formEditarStatus">
                    <div class="mb-3">
                        <label for="novoStatus" class="form-label">Novo Status</label>
                        <select class="form-select" id="novoStatus" name="etiqueta" required>
                            <option value="">Selecione um status</option>
                            <option value="pendente">🟡 Pendente</option>
                            <option value="contatado">🔵 Contatado</option>
                            <option value="interessado">🟢 Interessado</option>
                            <option value="nao_interessado">🔴 Não Interessado</option>
                            <option value="matriculado">⭐ Matriculado</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="observacoes" class="form-label">Observações</label>
                        <textarea class="form-control" id="observacoes" name="observacoes" rows="3" placeholder="Adicione observações sobre a mudança de status..."></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-2"></i>Cancelar
                </button>
                <button type="button" class="btn btn-primary" onclick="atualizarStatus()">
                    <i class="fas fa-save me-2"></i>Atualizar Status
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal para Adicionar Nota -->
<div class="modal fade" id="modalAdicionarNota" tabindex="-1" aria-labelledby="modalAdicionarNotaLabel" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header bg-info text-white">
                        <h5 class="modal-title" id="modalAdicionarNotaLabel">
                            <i class="fas fa-sticky-note me-2"></i>
                            Adicionar Nota
                        </h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
            <div class="modal-body">
                <form id="formAdicionarNota">
                    <div class="mb-3">
                        <label for="notaTexto" class="form-label">Nota</label>
                        <textarea class="form-control" id="notaTexto" name="nota" rows="4" placeholder="Digite sua nota sobre esta inscrição..." required></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label for="tipoNota" class="form-label">Tipo de Nota</label>
                        <select class="form-select" id="tipoNota" name="tipo">
                            <option value="geral">📝 Geral</option>
                            <option value="contato">📞 Contato</option>
                            <option value="followup">⏰ Follow-up</option>
                            <option value="importante">⚠️ Importante</option>
                            <option value="venda">💰 Venda</option>
                        </select>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-2"></i>Cancelar
                </button>
                <button type="button" class="btn btn-info" onclick="adicionarNota()">
                    <i class="fas fa-plus me-2"></i>Adicionar Nota
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal para Marcar Contato -->
<div class="modal fade" id="modalMarcarContato" tabindex="-1" aria-labelledby="modalMarcarContatoLabel" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header bg-success text-white">
                        <h5 class="modal-title" id="modalMarcarContatoLabel">
                            <i class="fas fa-phone me-2"></i>
                            Marcar Contato Realizado
                        </h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
            <div class="modal-body">
                <form id="formMarcarContato">
                    <div class="mb-3">
                        <label for="tipoContato" class="form-label">Tipo de Contato</label>
                        <select class="form-select" id="tipoContato" name="tipo_contato" required>
                            <option value="">Selecione o tipo</option>
                            <option value="telefone">📞 Telefone</option>
                            <option value="whatsapp">📱 WhatsApp</option>
                            <option value="email">📧 Email</option>
                            <option value="presencial">👥 Presencial</option>
                            <option value="outro">🔗 Outro</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="resultadoContato" class="form-label">Resultado do Contato</label>
                        <select class="form-select" id="resultadoContato" name="resultado" required>
                            <option value="">Selecione o resultado</option>
                            <option value="interessado">🟢 Interessado</option>
                            <option value="nao_interessado">🔴 Não Interessado</option>
                            <option value="agendado">📅 Agendado</option>
                            <option value="retorno">⏰ Retorno</option>
                            <option value="sem_resposta">📵 Sem Resposta</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="observacoesContato" class="form-label">Observações</label>
                        <textarea class="form-control" id="observacoesContato" name="observacoes" rows="3" placeholder="Detalhes sobre o contato realizado..."></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label for="proximoFollowup" class="form-label">Próximo Follow-up</label>
                        <input type="datetime-local" class="form-control" id="proximoFollowup" name="proximo_followup">
                        <div class="form-text">Opcional: Agende o próximo contato</div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-2"></i>Cancelar
                </button>
                <button type="button" class="btn btn-success" onclick="marcarContato()">
                    <i class="fas fa-check me-2"></i>Marcar Contato
                </button>
            </div>
        </div>
    </div>
</div>
<?php /**PATH /home/douglas/Downloads/ec-complete-backup-20250728_105142/ec-complete-backup-20250813_144041/resources/views/admin/inscricoes/_modals.blade.php ENDPATH**/ ?>