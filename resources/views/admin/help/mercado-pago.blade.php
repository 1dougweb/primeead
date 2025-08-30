@extends('layouts.help')

@section('title', 'Configurar Mercado Pago')
@section('page-title', 'Como Configurar o Mercado Pago')

@section('breadcrumb')
<li class="breadcrumb-item active">Configurar Mercado Pago</li>
@endsection

@section('content')
<div class="help-section">
    <div class="alert alert-success alert-help">
        <h6><i class="fas fa-rocket me-2"></i>Sistema Atualizado - Nova API!</h6>
        <p class="mb-2">
            <strong>Boa notícia!</strong> O sistema foi atualizado para usar a nova API do Mercado Pago (v1/orders) com recursos aprimorados.
        </p>
        <a href="{{ route('admin.help.mercado-pago-nova-api') }}" class="btn btn-success btn-sm">
            <i class="fas fa-info-circle me-1"></i>
            Ver todas as atualizações
        </a>
    </div>
    
    <div class="alert alert-info alert-help">
        <h6><i class="fas fa-info-circle me-2"></i>Sobre o Mercado Pago</h6>
        <p class="mb-0">
            O Mercado Pago é a plataforma de pagamentos que permite processar pagamentos via PIX, cartão de crédito, débito e boleto. 
            Esta integração permite que o sistema processe pagamentos de forma automatizada e segura.
        </p>
    </div>
</div>

<div class="help-section">
    <h3>Pré-requisitos</h3>
    <div class="help-card card">
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <h6><i class="fas fa-check-circle text-success me-2"></i>Você precisará de:</h6>
                    <ul>
                        <li>Conta no Mercado Pago (pessoa física ou jurídica)</li>
                        <li>Documentos validados na plataforma</li>
                        <li>Acesso às credenciais da aplicação</li>
                        <li>Permissões administrativas no sistema</li>
                    </ul>
                </div>
                <div class="col-md-6">
                    <h6><i class="fas fa-clock text-warning me-2"></i>Tempo estimado:</h6>
                    <ul>
                        <li><strong>Criar conta:</strong> 10-15 minutos</li>
                        <li><strong>Validar documentos:</strong> 1-2 dias úteis</li>
                        <li><strong>Configurar sistema:</strong> 15-20 minutos</li>
                        <li><strong>Testar integração:</strong> 5-10 minutos</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="help-section">
    <h3>Passo a Passo</h3>
    
    <div class="help-card card">
        <div class="card-header">
            <h5 class="mb-0">
                <span class="step-number">1</span>
                Criar Conta no Mercado Pago
            </h5>
        </div>
        <div class="card-body">
            <p>Se você ainda não tem uma conta:</p>
            <ol>
                <li>Acesse <a href="https://www.mercadopago.com.br" target="_blank">www.mercadopago.com.br</a></li>
                <li>Clique em <strong>"Criar conta"</strong></li>
                <li>Escolha <strong>"Vender"</strong> para conta empresarial</li>
                <li>Preencha os dados solicitados</li>
                <li>Valide seu email e telefone</li>
                <li>Envie os documentos necessários</li>
            </ol>
            <div class="alert alert-warning">
                <i class="fas fa-exclamation-triangle me-2"></i>
                <strong>Importante:</strong> Aguarde a validação dos documentos antes de prosseguir.
            </div>
        </div>
    </div>

    <div class="help-card card">
        <div class="card-header">
            <h5 class="mb-0">
                <span class="step-number">2</span>
                Acessar o Painel de Desenvolvedor
            </h5>
        </div>
        <div class="card-body">
            <p>Para obter as credenciais da API:</p>
            <ol>
                <li>Acesse <a href="https://developers.mercadopago.com" target="_blank">developers.mercadopago.com</a></li>
                <li>Faça login com sua conta do Mercado Pago</li>
                <li>Clique em <strong>"Suas integrações"</strong></li>
                <li>Clique em <strong>"Criar aplicação"</strong></li>
                <li>Preencha os dados da aplicação:</li>
            </ol>
            <div class="row">
                <div class="col-md-6">
                    <div class="code-block">
                        <strong>Nome:</strong> EJA Supletivo - Pagamentos<br>
                        <strong>Descrição:</strong> Sistema de pagamentos para matrículas<br>
                        <strong>Categoria:</strong> Educação<br>
                        <strong>URL:</strong> https://seudominio.com.br
                    </div>
                </div>
                <div class="col-md-6">
                    <h6>Permissões necessárias:</h6>
                    <ul>
                        <li>✅ read (leitura)</li>
                        <li>✅ write (escrita)</li>
                        <li>✅ offline_access (acesso offline)</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <div class="help-card card">
        <div class="card-header">
            <h5 class="mb-0">
                <span class="step-number">3</span>
                Obter Credenciais
            </h5>
        </div>
        <div class="card-body">
            <p>Após criar a aplicação, você terá acesso às credenciais:</p>
            <div class="row">
                <div class="col-md-6">
                    <h6><i class="fas fa-vial text-warning me-2"></i>Ambiente de Teste (Sandbox)</h6>
                    <ul>
                        <li><strong>Public Key:</strong> TEST-xxx</li>
                        <li><strong>Access Token:</strong> TEST-xxx</li>
                    </ul>
                    <small class="text-muted">Use para testes sem movimentar dinheiro real</small>
                </div>
                <div class="col-md-6">
                    <h6><i class="fas fa-globe text-success me-2"></i>Ambiente de Produção</h6>
                    <ul>
                        <li><strong>Public Key:</strong> APP_USR-xxx</li>
                        <li><strong>Access Token:</strong> APP_USR-xxx</li>
                    </ul>
                    <small class="text-muted">Use para processar pagamentos reais</small>
                </div>
            </div>
            <div class="alert alert-danger mt-3">
                <i class="fas fa-shield-alt me-2"></i>
                <strong>Segurança:</strong> Nunca compartilhe suas credenciais. O Access Token é secreto!
            </div>
        </div>
    </div>

    <div class="help-card card">
        <div class="card-header">
            <h5 class="mb-0">
                <span class="step-number">4</span>
                Configurar Webhooks
            </h5>
        </div>
        <div class="card-body">
            <p>Configure os webhooks para receber notificações automáticas:</p>
            <ol>
                <li>No painel do desenvolvedor, acesse sua aplicação</li>
                <li>Vá para <strong>"Webhooks"</strong></li>
                <li>Clique em <strong>"Configurar notificações"</strong></li>
                <li>Configure a URL do webhook:</li>
            </ol>
            <div class="code-block">
                <strong>URL:</strong> {{ url('/webhook/mercadopago') }}<br>
                <strong>Eventos:</strong> payment.updated, order.updated<br>
                <strong>Método:</strong> POST<br>
                <strong>Formato:</strong> JSON
            </div>
            <div class="alert alert-success mt-3">
                <i class="fas fa-check-circle me-2"></i>
                <strong>Atualizado!</strong> O sistema de webhooks foi aprimorado para a nova API v1/orders com melhor processamento e logs detalhados.
            </div>
        </div>
    </div>

    <div class="help-card card">
        <div class="card-header">
            <h5 class="mb-0">
                <span class="step-number">5</span>
                Configurar no Sistema
            </h5>
        </div>
        <div class="card-body">
            <p>Agora configure as credenciais no sistema EJA Supletivo:</p>
            <ol>
                <li>Acesse <strong>Configurações</strong> → <strong>Mercado Pago</strong></li>
                <li>Preencha os campos:</li>
            </ol>
            <div class="row">
                <div class="col-md-6">
                    <h6>Configurações Básicas:</h6>
                    <ul>
                        <li><strong>Habilitado:</strong> ✅ Sim</li>
                        <li><strong>Modo Sandbox:</strong> ✅ Sim (para testes)</li>
                        <li><strong>Access Token:</strong> Sua credencial</li>
                        <li><strong>Public Key:</strong> Sua credencial</li>
                    </ul>
                </div>
                <div class="col-md-6">
                    <h6>Configurações Avançadas:</h6>
                    <ul>
                        <li><strong>Webhook Secret:</strong> Gerado automaticamente</li>
                        <li><strong>Moeda:</strong> BRL</li>
                        <li><strong>País:</strong> BR</li>
                        <li><strong>Notificações:</strong> Configurar conforme necessário</li>
                    </ul>
                </div>
            </div>
            <div class="text-center mt-3">
                <a href="{{ route('admin.settings.index') }}#mercadopago" class="btn btn-primary">
                    <i class="fas fa-cog me-2"></i>
                    Ir para Configurações
                </a>
            </div>
        </div>
    </div>

    <div class="help-card card">
        <div class="card-header">
            <h5 class="mb-0">
                <span class="step-number">6</span>
                Testar Conexão
            </h5>
        </div>
        <div class="card-body">
            <p>Teste se a integração está funcionando:</p>
            <ol>
                <li>Na página de configurações, clique em <strong>"Testar Conexão"</strong></li>
                <li>O sistema verificará:</li>
            </ol>
            <div class="row">
                <div class="col-md-6">
                    <h6><i class="fas fa-check-circle text-success me-2"></i>Testes Realizados:</h6>
                    <ul>
                        <li>Validação das credenciais</li>
                        <li>Conectividade com a API</li>
                        <li>Permissões da aplicação</li>
                        <li>Configuração do webhook</li>
                    </ul>
                </div>
                <div class="col-md-6">
                    <h6><i class="fas fa-info-circle text-info me-2"></i>Resultados:</h6>
                    <ul>
                        <li><span class="badge bg-success">✅ Conectado</span> - Tudo funcionando</li>
                        <li><span class="badge bg-warning">⚠️ Atenção</span> - Verificar configuração</li>
                        <li><span class="badge bg-danger">❌ Erro</span> - Corrigir problema</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="help-section">
    <h3>Ambientes de Teste</h3>
    <div class="help-card card">
        <div class="card-body">
            <div class="row">
                <div class="col-md-8">
                    <h5>Testando Pagamentos</h5>
                    <p>Para testar pagamentos no ambiente sandbox, use estes cartões de teste:</p>
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Cartão</th>
                                    <th>Número</th>
                                    <th>Resultado</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>Visa</td>
                                    <td>4013 5406 8274 6260</td>
                                    <td><span class="badge bg-success">Aprovado</span></td>
                                </tr>
                                <tr>
                                    <td>Mastercard</td>
                                    <td>5031 7557 3453 0604</td>
                                    <td><span class="badge bg-success">Aprovado</span></td>
                                </tr>
                                <tr>
                                    <td>Visa</td>
                                    <td>4509 9535 6623 3704</td>
                                    <td><span class="badge bg-danger">Recusado</span></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <small class="text-muted">CVV: qualquer 3 dígitos | Vencimento: qualquer data futura</small>
                </div>
                <div class="col-md-4 text-center">
                    <i class="fas fa-credit-card fa-4x text-primary opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="help-section">
    <h3>Problemas Comuns</h3>
    <div class="row">
        <div class="col-md-6">
            <div class="alert alert-danger alert-help">
                <h6><i class="fas fa-exclamation-triangle me-2"></i>Erro de Credenciais</h6>
                <p><strong>Problema:</strong> "Invalid credentials"</p>
                <p><strong>Solução:</strong></p>
                <ul class="mb-0">
                    <li>Verifique se copiou as credenciais corretas</li>
                    <li>Confirme se está usando o ambiente certo (teste/produção)</li>
                    <li>Regenere as credenciais se necessário</li>
                </ul>
            </div>
        </div>
        <div class="col-md-6">
            <div class="alert alert-warning alert-help">
                <h6><i class="fas fa-exclamation-triangle me-2"></i>Webhook não funciona</h6>
                <p><strong>Problema:</strong> Notificações não chegam</p>
                <p><strong>Solução:</strong></p>
                <ul class="mb-0">
                    <li>Verifique se a URL está acessível</li>
                    <li>Confirme se o SSL está funcionando</li>
                    <li>Teste a URL manualmente</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<div class="help-section">
    <div class="text-center">
        <a href="{{ route('admin.settings.index') }}#mercadopago" class="btn btn-primary btn-lg">
            <i class="fas fa-cog me-2"></i>
            Configurar Mercado Pago
        </a>
        <a href="{{ route('admin.help.configuracao-pagamentos') }}" class="btn btn-outline-info btn-lg ms-3">
            <i class="fas fa-arrow-right me-2"></i>
            Próximo: Configurações de Pagamento
        </a>
    </div>
</div>
@endsection 