<!-- Modal de Follow-ups -->
<div class="modal fade" id="followupsModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-light">
                <h5 class="modal-title">Follow-ups Agendados</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <!-- Loading -->
                <div id="followupsLoading" class="text-center py-4">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Carregando...</span>
                    </div>
                </div>

                <!-- Calendário de Follow-ups -->
                <div id="followupsCalendar" class="d-none">
                    <!-- Preenchido via JavaScript -->
                </div>

                <!-- Lista de Follow-ups -->
                <div id="followupsList" class="d-none">
                    <div class="list-group">
                        <!-- Preenchido via JavaScript -->
                    </div>
                </div>
            </div>
        </div>
    </div>
</div> 