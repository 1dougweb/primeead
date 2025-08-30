@extends('layouts.help')

@section('title', 'Nova API do Mercado Pago')
@section('page-title', 'Nova API do Mercado Pago - Atualizações 2025')

@section('breadcrumb')
<li class="breadcrumb-item active">Nova API do Mercado Pago</li>
@endsection

@section('content')
<div class="help-section">
    <div class="alert alert-success alert-help">
        <h6><i class="fas fa-rocket me-2"></i>Sistema Atualizado!</h6>
        <p class="mb-0">
            <strong>Boas notícias!</strong> O sistema foi atualizado para usar a nova API do Mercado Pago (v1/orders), 
            oferecendo melhor desempenho, mais recursos e maior estabilidade para processar pagamentos.
        </p>
    </div>
</div>

<div class="help-section">
    <h3>O que mudou?</h3>
    
    <div class="row">
        <div class="col-md-6">
            <div class="help-card card">
                <div class="card-header bg-primary text-white">
                    <h6 class="mb-0">
                        <i class="fas fa-arrow-up me-2"></i>
                        Melhorias Implementadas
                    </h6>
                </div>
                <div class="card-body">
                    <ul class="list-unstyled">
                        <li class="mb-2">
                            <i class="fas fa-check text-success me-2"></i>
                            <strong>API mais moderna:</strong> Migração para `/v1/orders`
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-check text-success me-2"></i>
                            <strong>Melhor processamento:</strong> PIX e Boleto otimizados
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-check text-success me-2"></i>
                            <strong>Webhooks aprimorados:</strong> Notificações mais confiáveis
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-check text-success me-2"></i>
                            <strong>Validação robusta:</strong> Valor mínimo para boletos
                        </li>
                        <li class="mb-0">
                            <i class="fas fa-check text-success me-2"></i>
                            <strong>Logs detalhados:</strong> Melhor rastreamento de erros
                        </li>
                    </ul>
                </div>
            </div>
        </div>
        
        <div class="col-md-6">
            <div class="help-card card">
                <div class="card-header bg-info text-white">
                    <h6 class="mb-0">
                        <i class="fas fa-cog me-2"></i>
                        Recursos Novos
                    </h6>
                </div>
                <div class="card-body">
                    <ul class="list-unstyled">
                        <li class="mb-2">
                            <i class="fas fa-star text-warning me-2"></i>
                            <strong>QR Code PIX:</strong> Geração automática de QR codes
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-star text-warning me-2"></i>
                            <strong>Boleto PDF:</strong> Download automático de boletos
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-star text-warning me-2"></i>
                            <strong>Linha digitável:</strong> Captura automática para boletos
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-star text-warning me-2"></i>
                            <strong>Validação frontend:</strong> Verificação em tempo real
                        </li>
                        <li class="mb-0">
                            <i class="fas fa-star text-warning me-2"></i>
                            <strong>Processamento inteligente:</strong> Dados salvos automaticamente
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="help-section">
    <h3>Como usar os novos recursos</h3>
    
    <div class="help-card card">
        <div class="card-header">
            <h5 class="mb-0">
                <span class="step-number">1</span>
                Pagamentos PIX Aprimorados
            </h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-8">
                    <p><strong>Agora o sistema gera automaticamente:</strong></p>
                    <ul>
                        <li><strong>QR Code PIX:</strong> Imagem em Base64 para exibição direta</li>
                        <li><strong>Código Copia e Cola:</strong> Texto para pagamento manual</li>
                        <li><strong>Link de pagamento:</strong> URL com instruções completas</li>
                        <li><strong>Expiração configurável:</strong> Prazo personalizado (padrão: 24h)</li>
                    </ul>
                    
                    <div class="alert alert-info mt-3">
                        <i class="fas fa-info-circle me-2"></i>
                        <strong>Dica:</strong> O QR Code é exibido automaticamente na tela de pagamento e pode ser copiado pelo cliente.
                    </div>
                </div>
                <div class="col-md-4 text-center">
                    <i class="fab fa-pix fa-4x text-primary opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
    
    <div class="help-card card">
        <div class="card-header">
            <h5 class="mb-0">
                <span class="step-number">2</span>
                Boletos Inteligentes
            </h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-8">
                    <p><strong>Novos recursos para boletos:</strong></p>
                    <ul>
                        <li><strong>Valor mínimo:</strong> R$ 3,00 (validação automática)</li>
                        <li><strong>PDF automático:</strong> Download e salvamento na pasta `/public/storage/boletos/`</li>
                        <li><strong>Linha digitável:</strong> Capturada e salva automaticamente</li>
                        <li><strong>Código de barras:</strong> Armazenado para referência</li>
                        <li><strong>Banco emissor:</strong> Identificação automática</li>
                    </ul>
                    
                    <div class="alert alert-warning mt-3">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <strong>Importante:</strong> Boletos com valor inferior a R$ 3,00 são rejeitados automaticamente pelo Mercado Pago.
                    </div>
                </div>
                <div class="col-md-4 text-center">
                    <i class="fas fa-barcode fa-4x text-success opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="help-section">
    <h3>Validações e Segurança</h3>
    
    <div class="help-card card">
        <div class="card-header">
            <h5 class="mb-0">
                <i class="fas fa-shield-alt me-2"></i>
                Validações Implementadas
            </h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <h6>🔒 Validação Frontend (JavaScript)</h6>
                    <ul>
                        <li><strong>Valor mínimo:</strong> R$ 3,00 para boletos</li>
                        <li><strong>Feedback visual:</strong> Mensagens de erro em tempo real</li>
                        <li><strong>Prevenção de envio:</strong> Bloqueia formulários inválidos</li>
                        <li><strong>Experiência do usuário:</strong> Validação sem recarregar página</li>
                    </ul>
                </div>
                <div class="col-md-6">
                    <h6>🛡️ Validação Backend (PHP)</h6>
                    <ul>
                        <li><strong>Dupla verificação:</strong> Valida mesmo com JS desabilitado</li>
                        <li><strong>Mensagens informativas:</strong> Feedback claro para usuários</li>
                        <li><strong>Redirecionamento:</strong> Volta ao formulário com dados preservados</li>
                        <li><strong>Logs de segurança:</strong> Registro de tentativas inválidas</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="help-section">
    <h3>Webhooks Atualizados</h3>
    
    <div class="help-card card">
        <div class="card-header">
            <h5 class="mb-0">
                <i class="fas fa-webhook me-2"></i>
                Sistema de Notificações
            </h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-8">
                    <p><strong>O sistema de webhooks foi completamente atualizado:</strong></p>
                    <ul>
                        <li><strong>Nova API:</strong> Compatível com `/v1/orders`</li>
                        <li><strong>Processamento inteligente:</strong> Atualiza status automaticamente</li>
                        <li><strong>Dados completos:</strong> Salva informações de PIX e boleto</li>
                        <li><strong>Logs detalhados:</strong> Rastreamento completo de eventos</li>
                        <li><strong>Validação de assinatura:</strong> Segurança aprimorada</li>
                    </ul>
                    
                    <div class="code-block mt-3">
                        <strong>URL do Webhook:</strong> {{ url('/webhook/mercadopago') }}<br>
                        <strong>Eventos:</strong> payment.updated, order.updated<br>
                        <strong>Método:</strong> POST<br>
                        <strong>Formato:</strong> JSON
                    </div>
                </div>
                <div class="col-md-4 text-center">
                    <i class="fas fa-sync-alt fa-4x text-info opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="help-section">
    <h3>Mapeamento de Status</h3>
    
    <div class="help-card card">
        <div class="card-header">
            <h5 class="mb-0">
                <i class="fas fa-list-alt me-2"></i>
                Novos Status de Pagamento
            </h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <h6>📊 Status do Mercado Pago</h6>
                    <ul>
                        <li><strong>action_required:</strong> Aguardando ação do cliente</li>
                        <li><strong>waiting_payment:</strong> Aguardando pagamento</li>
                        <li><strong>approved:</strong> Pagamento aprovado</li>
                        <li><strong>rejected:</strong> Pagamento rejeitado</li>
                        <li><strong>cancelled:</strong> Pagamento cancelado</li>
                        <li><strong>failed:</strong> Falha no processamento</li>
                        <li><strong>expired:</strong> Pagamento expirado</li>
                    </ul>
                </div>
                <div class="col-md-6">
                    <h6>🎯 Status no Sistema</h6>
                    <ul>
                        <li><strong>pending:</strong> Aguardando pagamento</li>
                        <li><strong>paid:</strong> Pagamento confirmado</li>
                        <li><strong>failed:</strong> Pagamento falhou</li>
                        <li><strong>cancelled:</strong> Pagamento cancelado</li>
                        <li><strong>expired:</strong> Pagamento expirado</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="help-section">
    <h3>Configuração Atualizada</h3>
    
    <div class="help-card card">
        <div class="card-header">
            <h5 class="mb-0">
                <span class="step-number">1</span>
                Verificar Configurações
            </h5>
        </div>
        <div class="card-body">
            <p>Para aproveitar todos os novos recursos, verifique suas configurações:</p>
            <ol>
                <li>Acesse <strong>Configurações</strong> → <strong>Mercado Pago</strong></li>
                <li>Confirme que suas credenciais estão corretas</li>
                <li>Configure o webhook secret (se ainda não configurado)</li>
                <li>Teste a conexão usando o botão "Testar Conexão"</li>
                <li>Ative as notificações por email se desejar</li>
            </ol>
            
            <div class="text-center mt-3">
                <a href="{{ route('admin.settings.index') }}#mercadopago" class="btn btn-primary">
                    <i class="fas fa-cog me-2"></i>
                    Verificar Configurações
                </a>
            </div>
        </div>
    </div>
    
    <div class="help-card card">
        <div class="card-header">
            <h5 class="mb-0">
                <span class="step-number">2</span>
                Testar Novo Sistema
            </h5>
        </div>
        <div class="card-body">
            <p>Recomendamos testar o sistema atualizado:</p>
            <ol>
                <li>Crie um pagamento de teste com valor ≥ R$ 3,00</li>
                <li>Teste tanto PIX quanto boleto</li>
                <li>Verifique se o QR Code PIX é gerado</li>
                <li>Confirme se o PDF do boleto é baixado</li>
                <li>Monitore os logs para verificar o funcionamento</li>
            </ol>
            
            <div class="text-center mt-3">
                <a href="{{ route('admin.payments.create') }}" class="btn btn-success">
                    <i class="fas fa-plus me-2"></i>
                    Criar Pagamento de Teste
                </a>
            </div>
        </div>
    </div>
</div>

<div class="help-section">
    <h3>Solução de Problemas</h3>
    
    <div class="row">
        <div class="col-md-6">
            <div class="alert alert-danger alert-help">
                <h6><i class="fas fa-exclamation-triangle me-2"></i>Problemas Comuns</h6>
                <ul class="mb-0">
                    <li><strong>Valor abaixo do mínimo:</strong> Boletos < R$ 3,00 são rejeitados</li>
                    <li><strong>QR Code não aparece:</strong> Verifique se o pagamento foi processado</li>
                    <li><strong>PDF não baixa:</strong> Confirme permissões da pasta boletos</li>
                    <li><strong>Webhook não funciona:</strong> Verifique URL e credenciais</li>
                </ul>
            </div>
        </div>
        <div class="col-md-6">
            <div class="alert alert-success alert-help">
                <h6><i class="fas fa-tools me-2"></i>Soluções Rápidas</h6>
                <ul class="mb-0">
                    <li><strong>Logs detalhados:</strong> Verifique storage/logs/laravel.log</li>
                    <li><strong>Teste de conexão:</strong> Use o botão nas configurações</li>
                    <li><strong>Reprocessar pagamento:</strong> Edite e salve novamente</li>
                    <li><strong>Limpar cache:</strong> php artisan cache:clear</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<div class="help-section">
    <h3>Monitoramento e Logs</h3>
    
    <div class="help-card card">
        <div class="card-header">
            <h5 class="mb-0">
                <i class="fas fa-chart-line me-2"></i>
                Acompanhar Performance
            </h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <h6>📊 Métricas Importantes</h6>
                    <ul>
                        <li><strong>Taxa de sucesso:</strong> % de pagamentos aprovados</li>
                        <li><strong>Tempo de processamento:</strong> Velocidade das transações</li>
                        <li><strong>Webhooks recebidos:</strong> Notificações processadas</li>
                        <li><strong>Erros de validação:</strong> Tentativas com valor inválido</li>
                    </ul>
                </div>
                <div class="col-md-6">
                    <h6>🔍 Onde Monitorar</h6>
                    <ul>
                        <li><strong>Dashboard:</strong> Visão geral de pagamentos</li>
                        <li><strong>Lista de pagamentos:</strong> Status detalhado</li>
                        <li><strong>Logs do sistema:</strong> Eventos técnicos</li>
                        <li><strong>Painel Mercado Pago:</strong> Transações na plataforma</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="help-section">
    <div class="text-center">
        <a href="{{ route('admin.payments.dashboard') }}" class="btn btn-primary btn-lg">
            <i class="fas fa-chart-pie me-2"></i>
            Dashboard de Pagamentos
        </a>
        <a href="{{ route('admin.settings.index') }}#mercadopago" class="btn btn-outline-primary btn-lg ms-3">
            <i class="fas fa-cog me-2"></i>
            Configurações
        </a>
        <a href="{{ route('admin.help.index') }}" class="btn btn-outline-secondary btn-lg ms-3">
            <i class="fas fa-home me-2"></i>
            Central de Ajuda
        </a>
    </div>
</div>
@endsection 