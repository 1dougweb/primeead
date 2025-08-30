@extends("layouts.admin")

@section("title", "Nova Campanha - Passo 2")

@section("page-title", "Nova Campanha de Email")

@section("page-actions")
    <div class="d-flex gap-2">
        <a href="{{ route("admin.email-campaigns.create") }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-2"></i>
            Voltar
        </a>
        <a href="{{ route("admin.email-campaigns.index") }}" class="btn btn-outline-primary">
            <i class="fas fa-times me-2"></i>
            Cancelar
        </a>
    </div>
@endsection

@section("content")
<div class="container-fluid">
    @if(session("success"))
        <div class="alert alert-success">
            {{ session("success") }}
        </div>
    @endif
    
    @if(session("error"))
        <div class="alert alert-danger">
            {{ session("error") }}
        </div>
    @endif
    
    <!-- Progress Bar -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="step-progress">
                            <div class="step completed">
                                <div class="step-circle">1</div>
                                <div class="step-label">Conteúdo</div>
                            </div>
                            <div class="step-line completed"></div>
                            <div class="step active">
                                <div class="step-circle">2</div>
                                <div class="step-label">Destinatários</div>
                            </div>
                            <div class="step-line"></div>
                            <div class="step">
                                <div class="step-circle">3</div>
                                <div class="step-label">Confirmação</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Estatísticas dos Leads -->
        <div class="col-md-4">
            <div class="card h-100">
                <div class="card-header bg-light">
                    <h5 class="mb-0">
                        <i class="fas fa-chart-pie me-2"></i>
                        Estatísticas dos Leads
                    </h5>
                </div>
                <div class="card-body">
                    <div class="lead-stats">
                        <div class="stat-item mb-3 p-2 border-left-primary">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <span class="text-muted small">Total de Leads</span>
                                    <div class="font-weight-bold h5 mb-0">{{ number_format($leadStats["total"]) }}</div>
                                </div>
                                <i class="fas fa-users text-primary fa-2x"></i>
                            </div>
                        </div>
                        
                        <div class="stat-item mb-2 p-2">
                            <div class="d-flex justify-content-between">
                                <span class="text-muted">Pendentes</span>
                                <span class="badge bg-secondary">{{ $leadStats['by_status']['pendente'] ?? 0 }}</span>
                            </div>
                        </div>
                        
                        <div class="stat-item mb-2 p-2">
                            <div class="d-flex justify-content-between">
                                <span class="text-muted">Contatados</span>
                                <span class="badge bg-info">{{ $leadStats['by_status']['contatado'] ?? 0 }}</span>
                            </div>
                        </div>
                        
                        <div class="stat-item mb-2 p-2">
                            <div class="d-flex justify-content-between">
                                <span class="text-muted">Interessados</span>
                                <span class="badge bg-warning">{{ $leadStats['by_status']['interessado'] ?? 0 }}</span>
                            </div>
                        </div>
                        
                        <div class="stat-item mb-2 p-2">
                            <div class="d-flex justify-content-between">
                                <span class="text-muted">Não Interessados</span>
                                <span class="badge bg-danger">{{ $leadStats['by_status']['nao_interessado'] ?? 0 }}</span>
                            </div>
                        </div>
                        
                        <div class="stat-item mb-2 p-2">
                            <div class="d-flex justify-content-between">
                                <span class="text-muted">Matriculados</span>
                                <span class="badge bg-success">{{ $leadStats['by_status']['matriculado'] ?? 0 }}</span>
                            </div>
                        </div>
                    </div>
                    
                    <hr>
                    
                    <div class="selected-count mt-3">
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            <strong id="selected-count">0</strong> leads selecionados
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Formulário de Seleção -->
        <div class="col-md-8">
            <div class="card">
                <div class="card-header bg-light">
                    <h5 class="mb-0">Passo 2: Selecionar Destinatários</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route("admin.email-campaigns.create-step3") }}" method="POST">
                        @csrf
                        
                        <div class="form-group mb-4">
                            <label class="form-label">Tipo de Seleção <span class="text-danger">*</span></label>
                            
                            <div class="selection-options">
                                <div class="selection-option mb-3 p-3 border rounded" data-type="all">
                                    <div class="d-flex align-items-start">
                                        <input type="radio" name="selection_type" value="all" id="selection_all" class="form-check-input me-3 mt-1">
                                        <div class="flex-grow-1">
                                            <label for="selection_all" class="form-check-label cursor-pointer">
                                                <h6 class="mb-1">Todos os Leads</h6>
                                                <p class="text-muted mb-0 small">Enviar para todos os leads cadastrados ({{ $leadStats["total"] }} leads)</p>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="selection-option mb-3 p-3 border rounded" data-type="status">
                                    <div class="d-flex align-items-start">
                                        <input type="radio" name="selection_type" value="status" id="selection_status" class="form-check-input me-3 mt-1">
                                        <div class="flex-grow-1">
                                            <label for="selection_status" class="form-check-label cursor-pointer">
                                                <h6 class="mb-1">Por Status</h6>
                                                <p class="text-muted mb-2 small">Selecionar leads com um status específico</p>
                                            </label>
                                            
                                            <div class="status-options" style="display: none;">
                                                <select name="status" class="form-select">
                                                    <option value="">Selecione um status</option>
                                                    @foreach($leadStats['by_status'] as $status => $count)
                                                        <option value="{{ $status }}">
                                                            {{ ucfirst(str_replace('_', ' ', $status)) }} ({{ $count }} leads)
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="selection-option mb-3 p-3 border rounded" data-type="course">
                                    <div class="d-flex align-items-start">
                                        <input type="radio" name="selection_type" value="course" id="selection_course" class="form-check-input me-3 mt-1">
                                        <div class="flex-grow-1">
                                            <label for="selection_course" class="form-check-label cursor-pointer">
                                                <h6 class="mb-1">Por Curso</h6>
                                                <p class="text-muted mb-2 small">Selecionar leads interessados em um curso específico</p>
                                            </label>
                                            
                                            <div class="course-options" style="display: none;">
                                                <select name="course" class="form-select">
                                                    <option value="">Selecione um curso</option>
                                                    @foreach($cursos as $curso)
                                                        <option value="{{ $curso }}">{{ $curso }} ({{ $leadStats['by_course'][$curso] ?? 0 }} leads)</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="selection-option mb-3 p-3 border rounded" data-type="modality">
                                    <div class="d-flex align-items-start">
                                        <input type="radio" name="selection_type" value="modality" id="selection_modality" class="form-check-input me-3 mt-1">
                                        <div class="flex-grow-1">
                                            <label for="selection_modality" class="form-check-label cursor-pointer">
                                                <h6 class="mb-1">Por Modalidade</h6>
                                                <p class="text-muted mb-2 small">Selecionar leads por modalidade de interesse</p>
                                            </label>
                                            
                                            <div class="modality-options" style="display: none;">
                                                <select name="modality" class="form-select">
                                                    <option value="">Selecione uma modalidade</option>
                                                    @foreach($modalidades as $modalidade)
                                                        <option value="{{ $modalidade }}">{{ $modalidade }} ({{ $leadStats['by_modality'][$modalidade] ?? 0 }} leads)</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="selection-option mb-3 p-3 border rounded" data-type="custom">
                                    <div class="d-flex align-items-start">
                                        <input type="radio" name="selection_type" value="custom" id="selection_custom" class="form-check-input me-3 mt-1">
                                        <div class="flex-grow-1">
                                            <label for="selection_custom" class="form-check-label cursor-pointer">
                                                <h6 class="mb-1">Filtros Personalizados</h6>
                                                <p class="text-muted mb-2 small">Combine múltiplos filtros para uma seleção precisa</p>
                                            </label>
                                            
                                            <div class="custom-options" style="display: none;">
                                                <div class="row">
                                                    <div class="col-md-6 mb-3">
                                                        <label class="form-label small">Status</label>
                                                        <select name="custom_filters[status]" class="form-select form-select-sm">
                                                            <option value="">Qualquer status</option>
                                                            @foreach($leadStats['by_status'] as $status => $count)
                                                                <option value="{{ $status }}">
                                                                    {{ ucfirst(str_replace('_', ' ', $status)) }} ({{ $count }} leads)
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                    
                                                    <div class="col-md-6 mb-3">
                                                        <label class="form-label small">Curso</label>
                                                        <select name="custom_filters[curso]" class="form-select form-select-sm">
                                                            <option value="">Qualquer curso</option>
                                                            @foreach($cursos as $curso)
                                                                <option value="{{ $curso }}">{{ $curso }} ({{ $leadStats['by_course'][$curso] ?? 0 }} leads)</option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                    
                                                    <div class="col-md-6 mb-3">
                                                        <label class="form-label small">Modalidade</label>
                                                        <select name="custom_filters[modalidade]" class="form-select form-select-sm">
                                                            <option value="">Qualquer modalidade</option>
                                                            @foreach($modalidades as $modalidade)
                                                                <option value="{{ $modalidade }}">{{ $modalidade }} ({{ $leadStats['by_modality'][$modalidade] ?? 0 }} leads)</option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                    
                                                    <div class="col-md-6 mb-3">
                                                        <label class="form-label small">Cadastrado a partir de</label>
                                                        <input type="date" name="custom_filters[created_from]" class="form-control form-control-sm">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            @error("selection_type")
                                <div class="text-danger small">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="d-flex justify-content-between">
                            <a href="{{ route("admin.email-campaigns.create") }}" class="btn btn-outline-secondary">
                                <i class="fas fa-arrow-left me-2"></i>
                                Anterior: Conteúdo
                            </a>
                            
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-arrow-right me-2"></i>
                                Próximo: Confirmar
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('styles')
<style>
    .step-progress {
        display: flex;
        align-items: center;
        width: 100%;
        max-width: 400px;
        margin: 0 auto;
    }
    
    .step {
        display: flex;
        flex-direction: column;
        align-items: center;
        text-align: center;
    }
    
    .step-circle {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background-color: #e9ecef;
        color: #6c757d;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: bold;
        margin-bottom: 8px;
        transition: all 0.3s ease;
    }
    
    .step.active .step-circle {
        background-color: #007bff;
        color: white;
    }
    
    .step.completed .step-circle {
        background-color: #28a745;
        color: white;
    }
    
    .step-label {
        font-size: 12px;
        color: #6c757d;
        white-space: nowrap;
    }
    
    .step.active .step-label {
        color: #007bff;
        font-weight: bold;
    }
    
    .step.completed .step-label {
        color: #28a745;
        font-weight: bold;
    }
    
    .step-line {
        flex: 1;
        height: 2px;
        background-color: #e9ecef;
        margin: 0 15px;
        margin-bottom: 20px;
    }
    
    .step-line.completed {
        background-color: #28a745;
    }
    
    .selection-option {
        transition: all 0.3s ease;
        cursor: pointer;
    }
    
    .selection-option:hover {
        background-color: #f8f9fa;
        border-color: #007bff !important;
    }
    
    .selection-option.selected {
        background-color: #e7f3ff;
        border-color: #007bff !important;
        box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
    }
    
    .cursor-pointer {
        cursor: pointer;
    }
    
    .border-left-primary {
        border-left: 4px solid #007bff !important;
    }
    
    .stat-item {
        border-radius: 4px;
    }
</style>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        const selectionOptions = $('.selection-option');
        const selectedCountElement = $('#selected-count');
        const radioButtons = $('input[type="radio"][name="selection_type"]');
        
        // Dados dos leads para cálculo
        const leadStats = {!! json_encode($leadStats) !!};
        
        // Função para mostrar opções específicas
        function showSpecificOptions(type) {
            // Esconder todas as opções específicas primeiro
            $('.status-options, .course-options, .modality-options, .custom-options').hide();
            
            // Mostrar as opções específicas do tipo selecionado
            $(`.${type}-options`).show();
        }
        
        // Função para atualizar a seleção visual
        function updateSelectionVisual(selectedOption) {
            selectionOptions.removeClass('selected');
            
            if (selectedOption) {
                $(selectedOption).closest('.selection-option').addClass('selected');
            }
        }
        
        // Handler para atualizar o contador
        function updateSelectedCount(type) {
            let count = 0;
            
            switch (type) {
                case 'all':
                    count = leadStats.total;
                    break;
                case 'status':
                    const statusSelect = $('select[name="status"]');
                    if (statusSelect.length && statusSelect.val()) {
                        count = leadStats.by_status[statusSelect.val()] || 0;
                    }
                    break;
                case 'course':
                    const courseSelect = $('select[name="course"]');
                    if (courseSelect.length && courseSelect.val()) {
                        count = leadStats.by_course[courseSelect.val()] || 0;
                    }
                    break;
                case 'modality':
                    const modalitySelect = $('select[name="modality"]');
                    if (modalitySelect.length && modalitySelect.val()) {
                        count = leadStats.by_modality[modalitySelect.val()] || 0;
                    }
                    break;
                case 'custom':
                    count = '?';
                    break;
            }
            
            selectedCountElement.text(count);
        }
        
        // Event listener para os radio buttons
        radioButtons.on('change', function() {
            const type = $(this).val();
            updateSelectionVisual(this);
            showSpecificOptions(type);
            updateSelectedCount(type);
        });
        
        // Event listener para os selects
        $(document).on('change', 'select[name="status"], select[name="course"], select[name="modality"]', function() {
            const selectedRadio = $('input[type="radio"][name="selection_type"]:checked');
            if (selectedRadio.length) {
                updateSelectedCount(selectedRadio.val());
            }
        });
        
        // Event listener para cliques nas divs de opção (para melhor UX)
        selectionOptions.on('click', function(e) {
            // Não fazer nada se o clique foi em um select
            if ($(e.target).is('select') || $(e.target).closest('select').length) {
                return;
            }
            
            const radio = $(this).find('input[type="radio"]');
            if (radio.length) {
                radio.prop('checked', true).trigger('change');
            }
        });
        
        // Inicializar estado se houver uma opção já selecionada
        const initialSelectedRadio = $('input[type="radio"][name="selection_type"]:checked');
        if (initialSelectedRadio.length) {
            initialSelectedRadio.trigger('change');
        }
    });
</script>
@endpush
