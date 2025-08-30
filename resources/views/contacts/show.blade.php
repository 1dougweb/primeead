<div class="row">
    <div class="col-md-12">
        <div class="card border-0">
            <div class="card-body">
                <div class="d-flex align-items-center mb-3">
                    <div class="avatar-circle bg-primary text-white me-3">
                        <i class="fas fa-user"></i>
                    </div>
                    <div>
                        <h5 class="mb-0">{{ $contact->name }}</h5>
                        <small class="text-muted">
                            Adicionado por {{ $contact->user->name }} em {{ $contact->created_at->format('d/m/Y') }}
                        </small>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label text-muted">WhatsApp</label>
                            <div class="d-flex align-items-center">
                                <a href="https://wa.me/55{{ $contact->whatsapp }}" 
                                   target="_blank" 
                                   class="btn btn-success btn-sm me-2">
                                    <i class="fab fa-whatsapp me-1"></i>
                                    {{ $contact->formatted_whatsapp }}
                                </a>
                                <button type="button" 
                                        class="btn btn-outline-secondary btn-sm" 
                                        onclick="copyToClipboard('{{ $contact->whatsapp }}')"
                                        title="Copiar número">
                                    <i class="fas fa-copy"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label text-muted">Proprietário</label>
                            <div>
                                <span class="badge {{ $contact->user_id === auth()->id() ? 'bg-success' : 'bg-secondary' }}">
                                    {{ $contact->user_id === auth()->id() ? 'Você' : $contact->user->name }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                @if($contact->notes)
                    <div class="mb-3">
                        <label class="form-label text-muted">Observações</label>
                        <div class="bg-light p-3 rounded">
                            {{ $contact->notes }}
                        </div>
                    </div>
                @endif

                <div class="row">
                    <div class="col-md-6">
                        <small class="text-muted">
                            <i class="fas fa-calendar-plus me-1"></i>
                            Criado em {{ $contact->created_at->format('d/m/Y H:i') }}
                        </small>
                    </div>
                    <div class="col-md-6">
                        <small class="text-muted">
                            <i class="fas fa-calendar-edit me-1"></i>
                            Atualizado em {{ $contact->updated_at->format('d/m/Y H:i') }}
                        </small>
                    </div>
                </div>

                @if($contact->user_id === auth()->id())
                    <div class="mt-4 pt-3 border-top">
                        <div class="d-flex gap-2">
                            <button type="button" 
                                    class="btn btn-primary btn-sm" 
                                    onclick="editContact({{ $contact->id }}); bootstrap.Modal.getInstance(document.getElementById('viewContactModal')).hide();">
                                <i class="fas fa-edit me-1"></i>
                                Editar
                            </button>
                            <button type="button" 
                                    class="btn btn-danger btn-sm" 
                                    onclick="deleteContact({{ $contact->id }}); bootstrap.Modal.getInstance(document.getElementById('viewContactModal')).hide();">
                                <i class="fas fa-trash me-1"></i>
                                Excluir
                            </button>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<style>
.avatar-circle {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.2rem;
}
</style>

<script>
function copyToClipboard(text) {
    navigator.clipboard.writeText(text).then(function() {
        showAlert('success', 'Número copiado para a área de transferência!');
    }, function(err) {
        console.error('Erro ao copiar: ', err);
        showAlert('error', 'Erro ao copiar número');
    });
}
</script> 