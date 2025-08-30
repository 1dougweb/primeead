@extends('emails.layout')

@section('content')
<div style="background-color: #f8f9fa; padding: 20px; border-radius: 8px; margin-bottom: 20px;">
    <h2 style="color: #28a745; margin: 0; font-size: 24px;">
        <i class="fas fa-handshake"></i> Novo Cadastro de Parceiro
    </h2>
    <p style="margin: 10px 0 0 0; color: #6c757d;">
        Um novo interessado se cadastrou para ser parceiro da Ensino Certo
    </p>
</div>

<div style="background-color: #ffffff; padding: 20px; border-radius: 8px; border-left: 4px solid #007bff;">
    <h3 style="color: #333; margin-top: 0;">📋 Dados do Candidato</h3>
    
    <table style="width: 100%; border-collapse: collapse; margin: 15px 0;">
        <tr>
            <td style="padding: 8px 0; font-weight: bold; color: #495057; width: 30%;">Nome Completo:</td>
            <td style="padding: 8px 0; color: #333;">{{ $parceiro->nome_completo }}</td>
        </tr>
        <tr>
            <td style="padding: 8px 0; font-weight: bold; color: #495057;">Email:</td>
            <td style="padding: 8px 0; color: #333;">
                <a href="mailto:{{ $parceiro->email }}" style="color: #007bff; text-decoration: none;">
                    {{ $parceiro->email }}
                </a>
            </td>
        </tr>
        <tr>
            <td style="padding: 8px 0; font-weight: bold; color: #495057;">WhatsApp:</td>
            <td style="padding: 8px 0; color: #333;">
                @if($parceiro->whatsapp)
                    <a href="https://wa.me/55{{ $parceiro->whatsapp }}" style="color: #25d366; text-decoration: none;">
                        {{ $parceiro->whatsapp_formatado }}
                    </a>
                @else
                    Não informado
                @endif
            </td>
        </tr>
        <tr>
            <td style="padding: 8px 0; font-weight: bold; color: #495057;">Documento:</td>
            <td style="padding: 8px 0; color: #333;">{{ $parceiro->documento_formatado }} ({{ strtoupper($parceiro->tipo_documento) }})</td>
        </tr>
        <tr>
            <td style="padding: 8px 0; font-weight: bold; color: #495057;">Cidade/Estado:</td>
            <td style="padding: 8px 0; color: #333;">{{ $parceiro->cidade }}/{{ $parceiro->estado }}</td>
        </tr>
    </table>
</div>

<div style="background-color: #ffffff; padding: 20px; border-radius: 8px; border-left: 4px solid #28a745; margin-top: 20px;">
    <h3 style="color: #333; margin-top: 0;">🎯 Informações da Parceria</h3>
    
    <table style="width: 100%; border-collapse: collapse; margin: 15px 0;">
        <tr>
            <td style="padding: 8px 0; font-weight: bold; color: #495057; width: 30%;">Modalidade:</td>
            <td style="padding: 8px 0; color: #333;">
                <span style="background-color: #007bff; color: white; padding: 4px 8px; border-radius: 4px; font-size: 12px;">
                    {{ $parceiro->modalidade_parceria }}
                </span>
            </td>
        </tr>
        <tr>
            <td style="padding: 8px 0; font-weight: bold; color: #495057;">Possui Estrutura:</td>
            <td style="padding: 8px 0; color: #333;">
                @if($parceiro->possui_estrutura)
                    <span style="background-color: #28a745; color: white; padding: 4px 8px; border-radius: 4px; font-size: 12px;">Sim</span>
                @else
                    <span style="background-color: #ffc107; color: #212529; padding: 4px 8px; border-radius: 4px; font-size: 12px;">Não</span>
                @endif
            </td>
        </tr>
        <tr>
            <td style="padding: 8px 0; font-weight: bold; color: #495057;">Tem Site:</td>
            <td style="padding: 8px 0; color: #333;">
                @if($parceiro->tem_site)
                    <span style="background-color: #28a745; color: white; padding: 4px 8px; border-radius: 4px; font-size: 12px;">Sim</span>
                    @if($parceiro->site_url)
                        <br><small><a href="{{ $parceiro->site_url }}" target="_blank" style="color: #007bff;">{{ $parceiro->site_url }}</a></small>
                    @endif
                @else
                    <span style="background-color: #ffc107; color: #212529; padding: 4px 8px; border-radius: 4px; font-size: 12px;">Não</span>
                @endif
            </td>
        </tr>
        <tr>
            <td style="padding: 8px 0; font-weight: bold; color: #495057;">Experiência Educacional:</td>
            <td style="padding: 8px 0; color: #333;">
                @if($parceiro->tem_experiencia_educacional)
                    <span style="background-color: #28a745; color: white; padding: 4px 8px; border-radius: 4px; font-size: 12px;">Sim</span>
                @else
                    <span style="background-color: #ffc107; color: #212529; padding: 4px 8px; border-radius: 4px; font-size: 12px;">Não</span>
                @endif
            </td>
        </tr>
    </table>
</div>

@if($parceiro->plano_negocio)
<div style="background-color: #ffffff; padding: 20px; border-radius: 8px; border-left: 4px solid #6f42c1; margin-top: 20px;">
    <h3 style="color: #333; margin-top: 0;">💼 Plano de Negócio</h3>
    <p style="color: #495057; line-height: 1.6; background-color: #f8f9fa; padding: 15px; border-radius: 4px; margin: 0;">
        {{ $parceiro->plano_negocio }}
    </p>
</div>
@endif

<div style="background-color: #ffffff; padding: 20px; border-radius: 8px; border-left: 4px solid #dc3545; margin-top: 20px;">
    <h3 style="color: #333; margin-top: 0;">⚡ Ação Necessária</h3>
    <p style="color: #495057; margin-bottom: 15px;">
        Acesse o painel administrativo para analisar e aprovar/rejeitar este candidato.
    </p>
    
    <div style="text-align: center; margin: 20px 0;">
        <a href="{{ url('/dashboard/parceiros/' . $parceiro->id) }}" 
           style="background-color: #007bff; color: white; padding: 12px 24px; text-decoration: none; border-radius: 6px; display: inline-block; font-weight: bold;">
            📋 Ver Detalhes do Candidato
        </a>
    </div>
</div>

<div style="margin-top: 30px; padding: 15px; background-color: #e9ecef; border-radius: 8px; text-align: center;">
    <p style="margin: 0; color: #6c757d; font-size: 14px;">
        <strong>Data do Cadastro:</strong> {{ $parceiro->created_at->format('d/m/Y H:i') }}
    </p>
</div>
@endsection 