@props(['progress'])

<div class="profile-progress-container mb-4">
    <div class="card border-0 shadow-sm">
        <div class="card-body py-3">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <div class="d-flex align-items-center">
                        <div class="me-3">
                            @if($progress['percentage'] >= 80)
                                <i class="fas fa-check-circle text-success fa-2x"></i>
                            @elseif($progress['percentage'] >= 50)
                                <i class="fas fa-clock text-warning fa-2x"></i>
                            @else
                                <i class="fas fa-exclamation-circle text-danger fa-2x"></i>
                            @endif
                        </div>
                        <div class="flex-grow-1">
                            <h6 class="mb-1">
                                Completar Perfil 
                                <span class="badge 
                                    @if($progress['percentage'] >= 80) bg-success 
                                    @elseif($progress['percentage'] >= 50) bg-warning 
                                    @else bg-danger 
                                    @endif">
                                    {{ $progress['percentage'] }}%
                                </span>
                            </h6>
                            <div class="progress" style="height: 8px;">
                                <div class="progress-bar 
                                    @if($progress['percentage'] >= 80) bg-success 
                                    @elseif($progress['percentage'] >= 50) bg-warning 
                                    @else bg-danger 
                                    @endif" 
                                    role="progressbar" 
                                    style="width: {{ $progress['percentage'] }}%"
                                    aria-valuenow="{{ $progress['percentage'] }}" 
                                    aria-valuemin="0" 
                                    aria-valuemax="100">
                                </div>
                            </div>
                            <small class="text-muted">
                                {{ $progress['completed'] }} de {{ $progress['total'] }} campos preenchidos
                            </small>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 text-end">
                    @if($progress['percentage'] < 100)
                        <button type="button" 
                                class="btn btn-outline-primary btn-sm" 
                                data-bs-toggle="modal" 
                                data-bs-target="#missingFieldsModal">
                            <i class="fas fa-list me-1"></i>
                            Ver Campos Faltantes
                        </button>
                    @else
                        <span class="badge bg-success">
                            <i class="fas fa-check me-1"></i>
                            Perfil Completo
                        </span>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal com campos faltantes -->
<div class="modal fade" id="missingFieldsModal" tabindex="-1" aria-labelledby="missingFieldsModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="missingFieldsModalLabel">
                    <i class="fas fa-list me-2"></i>
                    Campos Faltantes
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                @if(count($progress['missing_fields']) > 0)
                    <p class="text-muted mb-3">
                        Complete os campos abaixo para melhorar seu perfil:
                    </p>
                    <div class="row">
                        @foreach($progress['missing_fields'] as $field)
                            <div class="col-md-6 mb-2">
                                <div class="d-flex align-items-center">
                                    <i class="fas fa-circle text-danger me-2" style="font-size: 8px;"></i>
                                    <span>{{ $field }}</span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-4">
                        <i class="fas fa-check-circle text-success fa-3x mb-3"></i>
                        <h6>Perfil Completo!</h6>
                        <p class="text-muted">Todos os campos foram preenchidos.</p>
                    </div>
                @endif
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
                @if(count($progress['missing_fields']) > 0)
                    <button type="button" class="btn btn-primary" data-bs-dismiss="modal" onclick="scrollToFirstMissingField()">
                        <i class="fas fa-edit me-1"></i>
                        Completar Agora
                    </button>
                @endif
            </div>
        </div>
    </div>
</div>

<script>
function scrollToFirstMissingField() {
    // Lista de campos faltantes para tentar encontrar no formulário
    const missingFields = @json($progress['missing_fields']);
    const fieldMapping = {
        'Data de Nascimento': 'data_nascimento',
        'RG': 'rg',
        'Órgão Emissor': 'orgao_emissor',
        'Sexo': 'sexo',
        'Estado Civil': 'estado_civil',
        'Nacionalidade': 'nacionalidade',
        'Naturalidade': 'naturalidade',
        'Nome da Mãe': 'nome_mae',
        'Nome do Pai': 'nome_pai',
        'CEP': 'cep',
        'Logradouro': 'logradouro',
        'Número': 'numero',
        'Bairro': 'bairro',
        'Cidade': 'cidade',
        'Estado': 'estado',
        'Última Série': 'ultima_serie',
        'Ano de Conclusão': 'ano_conclusao',
        'Escola de Origem': 'escola_origem',
        'Telefone Fixo': 'telefone_fixo'
    };
    
    // Encontrar o primeiro campo faltante no formulário
    for (let fieldLabel of missingFields) {
        const fieldId = fieldMapping[fieldLabel];
        if (fieldId) {
            const field = document.getElementById(fieldId);
            if (field) {
                field.scrollIntoView({ behavior: 'smooth', block: 'center' });
                field.focus();
                
                // Destacar o campo temporariamente
                field.classList.add('border-warning');
                setTimeout(() => {
                    field.classList.remove('border-warning');
                }, 3000);
                
                break;
            }
        }
    }
}
</script>

<style>
.profile-progress-container .card {
    border-left: 4px solid #007bff;
}

.profile-progress-container .progress {
    border-radius: 10px;
}

.profile-progress-container .progress-bar {
    border-radius: 10px;
}

.border-warning {
    border-color: #ffc107 !important;
    box-shadow: 0 0 0 0.2rem rgba(255, 193, 7, 0.25);
}
</style> 