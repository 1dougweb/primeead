@extends('layouts.admin')

@section('title', 'Contatos')

@section('page-actions')
<div class="d-flex gap-2">
    <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#createContactModal">
        <i class="fas fa-plus"></i> Novo Contato
    </button>
    <div class="input-group" style="width: 300px;">
        <input type="text" class="form-control" id="searchInput" placeholder="Buscar contatos...">
        <button class="btn btn-outline-secondary" type="button" id="searchButton">
            <i class="fas fa-search"></i>
        </button>
    </div>
</div>
@endsection

@section('content')
<div class="container-fluid">
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i>
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-triangle me-2"></i>
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row">
        <!-- Meus Contatos -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-user me-2"></i>
                        Meus Contatos ({{ $myContacts->count() }})
                    </h5>
                </div>
                <div class="card-body">
                    @if($myContacts->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Nome</th>
                                        <th>WhatsApp</th>
                                        <th>Ações</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($myContacts as $contact)
                                        <tr>
                                            <td>
                                                <strong>{{ $contact->name }}</strong>
                                                @if($contact->notes)
                                                    <br>
                                                    <small class="text-muted">{{ Str::limit($contact->notes, 50) }}</small>
                                                @endif
                                            </td>
                                            <td>
                                                <a href="https://wa.me/55{{ $contact->whatsapp }}" 
                                                   target="_blank" 
                                                   class="text-success text-decoration-none">
                                                    <i class="fab fa-whatsapp me-1"></i>
                                                    {{ $contact->formatted_whatsapp }}
                                                </a>
                                            </td>
                                            <td>
                                                <div class="btn-group btn-group-sm">
                                                    <button type="button" 
                                                            class="btn btn-outline-primary" 
                                                            onclick="viewContact({{ $contact->id }})"
                                                            title="Visualizar">
                                                        <i class="fas fa-eye"></i>
                                                    </button>
                                                    <button type="button" 
                                                            class="btn btn-outline-warning" 
                                                            onclick="editContact({{ $contact->id }})"
                                                            title="Editar">
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                    <button type="button" 
                                                            class="btn btn-outline-danger" 
                                                            onclick="deleteContact({{ $contact->id }})"
                                                            title="Excluir">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-4 d-flex flex-column align-items-center justify-content-center">
                            <i class="fas fa-address-book fa-3x text-muted mb-3"></i>
                            <p class="text-muted">Você ainda não tem contatos cadastrados.</p>
                            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createContactModal">
                                <i class="fas fa-plus me-2"></i>
                                Adicionar Primeiro Contato
                            </button>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Todos os Contatos -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-users me-2"></i>
                        Todos os Contatos ({{ $allContacts->count() }})
                    </h5>
                </div>
                <div class="card-body">
                    @if($allContacts->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Nome</th>
                                        <th>WhatsApp</th>
                                        <th>Usuário</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($allContacts as $contact)
                                        <tr class="{{ $contact->user_id === auth()->id() ? 'table-success' : '' }}">
                                            <td>
                                                <strong>{{ $contact->name }}</strong>
                                                @if($contact->notes)
                                                    <br>
                                                    <small class="text-muted">{{ Str::limit($contact->notes, 30) }}</small>
                                                @endif
                                            </td>
                                            <td>
                                                <a href="https://wa.me/55{{ $contact->whatsapp }}" 
                                                   target="_blank" 
                                                   class="text-success text-decoration-none">
                                                    <i class="fab fa-whatsapp me-1"></i>
                                                    {{ $contact->formatted_whatsapp }}
                                                </a>
                                            </td>
                                            <td>
                                                <span class="badge {{ $contact->user_id === auth()->id() ? 'bg-success' : 'bg-secondary' }}">
                                                    {{ $contact->user_id === auth()->id() ? 'Você' : $contact->user->name }}
                                                </span>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="fas fa-address-book fa-3x text-muted mb-3"></i>
                            <p class="text-muted">Nenhum contato cadastrado ainda.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal para Criar Contato -->
<div class="modal fade" id="createContactModal" tabindex="-1" aria-labelledby="createContactModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="createContactModalLabel">
                    <i class="fas fa-plus me-2"></i>
                    Novo Contato
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="createContactForm">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="name" class="form-label">Nome <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="name" name="name" required>
                    </div>
                    <div class="mb-3">
                        <label for="whatsapp" class="form-label">WhatsApp <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="whatsapp" name="whatsapp" 
                               placeholder="(11) 99999-9999" required>
                        <div class="form-text">Digite apenas números ou use o formato (11) 99999-9999</div>
                    </div>
                    <div class="mb-3">
                        <label for="notes" class="form-label">Observações</label>
                        <textarea class="form-control" id="notes" name="notes" rows="3" 
                                  placeholder="Informações adicionais sobre o contato"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-2"></i>
                        Salvar Contato
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal para Visualizar Contato -->
<div class="modal fade" id="viewContactModal" tabindex="-1" aria-labelledby="viewContactModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="viewContactModalLabel">
                    <i class="fas fa-eye me-2"></i>
                    Detalhes do Contato
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="viewContactContent">
                <!-- Conteúdo carregado via AJAX -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal para Editar Contato -->
<div class="modal fade" id="editContactModal" tabindex="-1" aria-labelledby="editContactModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editContactModalLabel">
                    <i class="fas fa-edit me-2"></i>
                    Editar Contato
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="editContactForm">
                @csrf
                @method('PUT')
                <input type="hidden" id="editContactId" name="contact_id">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="editName" class="form-label">Nome <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="editName" name="name" required>
                    </div>
                    <div class="mb-3">
                        <label for="editWhatsapp" class="form-label">WhatsApp <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="editWhatsapp" name="whatsapp" 
                               placeholder="(11) 99999-9999" required>
                        <div class="form-text">Digite apenas números ou use o formato (11) 99999-9999</div>
                    </div>
                    <div class="mb-3">
                        <label for="editNotes" class="form-label">Observações</label>
                        <textarea class="form-control" id="editNotes" name="notes" rows="3" 
                                  placeholder="Informações adicionais sobre o contato"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-2"></i>
                        Atualizar Contato
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
    // Máscaras para campos de telefone
    document.getElementById('whatsapp').addEventListener('input', function(e) {
        let value = e.target.value.replace(/\D/g, '');
        if (value.length <= 11) {
            if (value.length === 11) {
                value = value.replace(/(\d{2})(\d{5})(\d{4})/, '($1) $2-$3');
            } else if (value.length === 10) {
                value = value.replace(/(\d{2})(\d{4})(\d{4})/, '($1) $2-$3');
            }
        }
        e.target.value = value;
    });

    document.getElementById('editWhatsapp').addEventListener('input', function(e) {
        let value = e.target.value.replace(/\D/g, '');
        if (value.length <= 11) {
            if (value.length === 11) {
                value = value.replace(/(\d{2})(\d{5})(\d{4})/, '($1) $2-$3');
            } else if (value.length === 10) {
                value = value.replace(/(\d{2})(\d{4})(\d{4})/, '($1) $2-$3');
            }
        }
        e.target.value = value;
    });

    // Criar contato
    document.getElementById('createContactForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        const submitBtn = this.querySelector('button[type="submit"]');
        const originalText = submitBtn.innerHTML;
        
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Salvando...';
        submitBtn.disabled = true;
        
        fetch('{{ route("contacts.store") }}', {
            method: 'POST',
            body: formData,
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showAlert('success', data.message);
                bootstrap.Modal.getInstance(document.getElementById('createContactModal')).hide();
                this.reset();
                setTimeout(() => location.reload(), 1500);
            } else {
                showAlert('error', data.message || 'Erro ao criar contato');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showAlert('error', 'Erro ao criar contato');
        })
        .finally(() => {
            submitBtn.innerHTML = originalText;
            submitBtn.disabled = false;
        });
    });

    // Visualizar contato
    function viewContact(contactId) {
        fetch(`/contacts/${contactId}`)
            .then(response => response.text())
            .then(html => {
                document.getElementById('viewContactContent').innerHTML = html;
                new bootstrap.Modal(document.getElementById('viewContactModal')).show();
            })
            .catch(error => {
                console.error('Error:', error);
                showAlert('error', 'Erro ao carregar contato');
            });
    }

    // Editar contato
    function editContact(contactId) {
        fetch(`/contacts/${contactId}`)
            .then(response => response.json())
            .then(contact => {
                document.getElementById('editContactId').value = contact.id;
                document.getElementById('editName').value = contact.name;
                document.getElementById('editWhatsapp').value = contact.whatsapp;
                document.getElementById('editNotes').value = contact.notes || '';
                
                new bootstrap.Modal(document.getElementById('editContactModal')).show();
            })
            .catch(error => {
                console.error('Error:', error);
                showAlert('error', 'Erro ao carregar contato');
            });
    }

    // Atualizar contato
    document.getElementById('editContactForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const contactId = document.getElementById('editContactId').value;
        const formData = new FormData(this);
        const submitBtn = this.querySelector('button[type="submit"]');
        const originalText = submitBtn.innerHTML;
        
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Atualizando...';
        submitBtn.disabled = true;
        
        fetch(`/contacts/${contactId}`, {
            method: 'PUT',
            body: formData,
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showAlert('success', data.message);
                bootstrap.Modal.getInstance(document.getElementById('editContactModal')).hide();
                setTimeout(() => location.reload(), 1500);
            } else {
                showAlert('error', data.message || 'Erro ao atualizar contato');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showAlert('error', 'Erro ao atualizar contato');
        })
        .finally(() => {
            submitBtn.innerHTML = originalText;
            submitBtn.disabled = false;
        });
    });

    // Excluir contato
    function deleteContact(contactId) {
        if (confirm('Tem certeza que deseja excluir este contato?')) {
            fetch(`/contacts/${contactId}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showAlert('success', data.message);
                    setTimeout(() => location.reload(), 1500);
                } else {
                    showAlert('error', data.message || 'Erro ao excluir contato');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showAlert('error', 'Erro ao excluir contato');
            });
        }
    }

    // Função para mostrar alertas
    function showAlert(type, message) {
        const alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
        const iconClass = type === 'success' ? 'fas fa-check-circle' : 'fas fa-exclamation-triangle';
        
        const alertHtml = `
            <div class="alert ${alertClass} alert-dismissible fade show" role="alert">
                <i class="${iconClass} me-2"></i>
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        `;
        
        const container = document.querySelector('.container-fluid');
        container.insertAdjacentHTML('afterbegin', alertHtml);
        
        setTimeout(() => {
            const alert = container.querySelector('.alert');
            if (alert) {
                alert.remove();
            }
        }, 5000);
    }
</script>
@endpush
@endsection 