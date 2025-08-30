@extends('layouts.help')

@section('title', 'Sistema de Ajuda')

@section('content')
<div class="container-fluid px-4">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="mb-0">
                        <i class="fas fa-question-circle me-2"></i>
                        Sistema de Ajuda
                    </h3>
                </div>
                <div class="card-body">
                    <!-- Seção de Atualizações -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                <div class="row align-items-center">
                                    <div class="col-md-8">
                                        <h5 class="alert-heading mb-2">
                                            <i class="fas fa-rocket me-2"></i>
                                            Sistema Atualizado - Nova API do Mercado Pago!
                                        </h5>
                                        <p class="mb-2">
                                            <strong>Novidades:</strong> PIX com QR Code automático, Boletos com PDF, Webhooks aprimorados, 
                                            Validação de valor mínimo e muito mais!
                                        </p>
                                        <a href="{{ route('admin.help.mercado-pago-nova-api') }}" class="btn btn-success btn-sm">
                                            <i class="fas fa-info-circle me-1"></i>
                                            Ver todas as atualizações
                                        </a>
                                    </div>
                                    <div class="col-md-4 text-center">
                                        <i class="fas fa-credit-card fa-3x text-success opacity-75"></i>
                                    </div>
                                </div>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <!-- Pagamentos -->
                        <div class="col-md-6 mb-4">
                            <div class="card h-100">
                                <div class="card-header bg-primary text-white">
                                    <h5 class="mb-0">
                                        <i class="fas fa-credit-card me-2"></i>
                                        Sistema de Pagamentos
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <div class="list-group list-group-flush">
                                        <a href="{{ route('admin.help.configuracao-pagamentos') }}" class="list-group-item list-group-item-action">
                                            <i class="fas fa-cog me-2"></i>
                                            Configuração do Mercado Pago
                                        </a>
                                        <a href="{{ route('admin.help.dashboard-pagamentos') }}" class="list-group-item list-group-item-action">
                                            <i class="fas fa-chart-bar me-2"></i>
                                            Dashboard de Pagamentos
                                        </a>
                                        <a href="{{ route('admin.help.automacao-pagamentos') }}" class="list-group-item list-group-item-action">
                                            <i class="fas fa-robot me-2"></i>
                                            Automação de Pagamentos
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Mercado Pago -->
                        <div class="col-md-6 mb-4">
                            <div class="card h-100">
                                <div class="card-header bg-success text-white">
                                    <h5 class="mb-0">
                                        <i class="fas fa-store me-2"></i>
                                        Mercado Pago
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <div class="list-group list-group-flush">
                                        <a href="{{ route('admin.help.mercado-pago-nova-api') }}" class="list-group-item list-group-item-action bg-light">
                                            <i class="fas fa-rocket me-2 text-success"></i>
                                            <strong>Nova API 2025 - Atualizações</strong>
                                            <small class="d-block text-muted">PIX, Boleto, Webhooks e mais</small>
                                        </a>
                                        <a href="{{ route('admin.help.mercado-pago') }}" class="list-group-item list-group-item-action">
                                            <i class="fas fa-info-circle me-2"></i>
                                            Como configurar o Mercado Pago
                                        </a>
                                        <a href="{{ route('admin.help.configuracao-pagamentos') }}" class="list-group-item list-group-item-action">
                                            <i class="fas fa-key me-2"></i>
                                            Obter credenciais da API
                                        </a>
                                        <a href="{{ route('admin.help.automacao-pagamentos') }}" class="list-group-item list-group-item-action">
                                            <i class="fas fa-webhook me-2"></i>
                                            Configurar Webhooks
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Seção de Contato -->
                    <div class="row mt-4">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header bg-info text-white">
                                    <h5 class="mb-0">
                                        <i class="fas fa-headset me-2"></i>
                                        Precisa de mais ajuda?
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <p class="mb-3">Se você não encontrou a resposta que procurava, entre em contato conosco:</p>
                                    <div class="row">
                                        <div class="col-md-4">
                                            <div class="d-flex align-items-center mb-2">
                                                <i class="fas fa-envelope text-primary me-2"></i>
                                                <span>suporte@nicedesign.com.br</span>
                                            </div>
                                        </div>
                                        
                                        <div class="col-md-4">
                                            <div class="d-flex align-items-center mb-2">
                                                <i class="fab fa-whatsapp text-success me-2"></i>
                                                <span>WhatsApp: (11) 99295-0897</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 