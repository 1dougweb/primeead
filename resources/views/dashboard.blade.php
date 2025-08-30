@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">{{ __('Dashboard') }}</div>

                <div class="card-body">
                    @if (session('status'))
                        <div class="alert alert-success" role="alert">
                            {{ session('status') }}
                        </div>
                    @endif

                    <h2>Bem-vindo, {{ auth()->user()->name }}!</h2>
                    <p>Este é o seu painel de controle.</p>

                    @if(auth()->user()->isAdmin())
                        <!-- Conteúdo específico para administradores -->
                        <div class="row mt-4">
                            <div class="col-md-3">
                                <div class="card bg-primary text-white">
                                    <div class="card-body">
                                        <h5 class="card-title">Total de Inscrições</h5>
                                        <h3>{{ $totalInscricoes ?? 0 }}</h3>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card bg-success text-white">
                                    <div class="card-body">
                                        <h5 class="card-title">Inscrições Hoje</h5>
                                        <h3>{{ $inscricoesHoje ?? 0 }}</h3>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card bg-info text-white">
                                    <div class="card-body">
                                        <h5 class="card-title">Últimos 7 Dias</h5>
                                        <h3>{{ $inscricoesUltimos7Dias ?? 0 }}</h3>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card bg-warning text-white">
                                    <div class="card-body">
                                        <h5 class="card-title">Últimos 30 Dias</h5>
                                        <h3>{{ $inscricoesUltimos30Dias ?? 0 }}</h3>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif

                    @if(auth()->user()->isParceiro())
                        <!-- Conteúdo específico para parceiros -->
                        <div class="row mt-4">
                            <div class="col-md-12">
                                <h4>Suas Informações</h4>
                                <p>Aqui você pode gerenciar suas informações e acompanhar suas atividades.</p>
                            </div>
                        </div>
                    @endif

                    <!-- Conteúdo comum a todos os usuários -->
                    <div class="row mt-4">
                        <div class="col-md-12">
                            <div class="card">
                                <div class="card-header">
                                    Ações Rápidas
                                </div>
                                <div class="card-body">
                                    <div class="list-group">
                                        <a href="{{ route('profile.edit') }}" class="list-group-item list-group-item-action">
                                            <i class="fas fa-user-edit"></i> Editar Perfil
                                        </a>
                                        @if(auth()->user()->isAdmin())
                                            <a href="{{ route('admin.inscricoes') }}" class="list-group-item list-group-item-action">
                                                <i class="fas fa-list"></i> Ver Inscrições
                                            </a>
                                            <a href="{{ route('admin.settings.index') }}" class="list-group-item list-group-item-action">
                                                <i class="fas fa-cog"></i> Configurações
                                            </a>
                                        @endif
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
