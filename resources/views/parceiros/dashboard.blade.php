@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">{{ __('Dashboard do Parceiro') }}</div>

                <div class="card-body">
                    @if (session('status'))
                        <div class="alert alert-success" role="alert">
                            {{ session('status') }}
                        </div>
                    @endif

                    <h2>Bem-vindo, {{ auth()->user()->name }}!</h2>
                    <p>Este é o seu painel de controle.</p>

                    <!-- Adicione aqui o conteúdo específico para parceiros -->
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 