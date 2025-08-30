<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $contract->title }} - {{ $contract->contract_number }}</title>
    <style>
        @page {
            margin: 2cm;
            font-family: 'DejaVu Sans', sans-serif;
        }
        
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 12px;
            line-height: 1.6;
            color: #333;
            margin: 0;
            padding: 0;
        }
        
        .header {
            text-align: center;
            border-bottom: 2px solid #333;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }
        
        .header h1 {
            font-size: 18px;
            font-weight: bold;
            margin: 0 0 10px 0;
            color: #2c3e50;
        }
        
        .header .contract-number {
            font-size: 14px;
            color: #666;
            margin: 5px 0;
        }
        
        .header .generation-info {
            font-size: 10px;
            color: #999;
            margin-top: 10px;
        }
        
        .contract-info {
            background-color: #f8f9fa;
            border: 1px solid #ddd;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 5px;
        }
        
        .contract-info h3 {
            font-size: 14px;
            margin: 0 0 10px 0;
            color: #2c3e50;
        }
        
        .contract-info table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .contract-info td {
            padding: 5px;
            border-bottom: 1px solid #eee;
        }
        
        .contract-info td:first-child {
            font-weight: bold;
            width: 30%;
        }
        
        .contract-content {
            margin: 20px 0;
            text-align: justify;
        }
        
        .contract-content h2 {
            font-size: 16px;
            color: #2c3e50;
            margin: 25px 0 15px 0;
            border-bottom: 1px solid #ccc;
            padding-bottom: 5px;
        }
        
        .contract-content h3 {
            font-size: 14px;
            color: #34495e;
            margin: 20px 0 10px 0;
        }
        
        .contract-content p {
            margin: 10px 0;
            text-align: justify;
        }
        
        .contract-content ul, .contract-content ol {
            margin: 10px 0;
            padding-left: 20px;
        }
        
        .contract-content li {
            margin: 5px 0;
        }
        
        .signature-section {
            margin-top: 40px;
            border-top: 2px solid #333;
            padding-top: 20px;
        }
        
        .signature-section h3 {
            font-size: 14px;
            color: #2c3e50;
            margin-bottom: 20px;
            text-align: center;
        }
        
        .signature-info {
            background-color: #e8f4f8;
            border: 1px solid #bee5eb;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 5px;
        }
        
        .signature-info table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .signature-info td {
            padding: 5px;
            border-bottom: 1px solid #d4edda;
        }
        
        .signature-info td:first-child {
            font-weight: bold;
            width: 30%;
        }
        
        .signature-image {
            text-align: center;
            margin: 20px 0;
            padding: 20px;
            border: 2px solid #333;
            border-radius: 5px;
        }
        
        .signature-image img {
            max-width: 300px;
            max-height: 100px;
            border: 1px solid #ccc;
        }
        
        .signature-image .signature-label {
            font-size: 10px;
            color: #666;
            margin-top: 10px;
        }
        
        .validity-info {
            background-color: #d4edda;
            border: 1px solid #c3e6cb;
            padding: 15px;
            margin-top: 20px;
            border-radius: 5px;
            text-align: center;
        }
        
        .validity-info h4 {
            font-size: 12px;
            color: #155724;
            margin: 0 0 10px 0;
        }
        
        .validity-info p {
            font-size: 10px;
            color: #155724;
            margin: 5px 0;
        }
        
        .footer {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            height: 50px;
            border-top: 1px solid #ccc;
            padding-top: 10px;
            text-align: center;
            font-size: 10px;
            color: #666;
        }
        
        .page-break {
            page-break-before: always;
        }
        
        .watermark {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-45deg);
            font-size: 80px;
            color: rgba(0, 0, 0, 0.05);
            z-index: -1;
            font-weight: bold;
        }
        
        .qr-code {
            text-align: center;
            margin: 20px 0;
        }
        
        .qr-code img {
            width: 100px;
            height: 100px;
        }
        
        .qr-code .qr-label {
            font-size: 10px;
            color: #666;
            margin-top: 5px;
        }
    </style>
</head>
<body>
    <!-- Marca d'água -->
    <div class="watermark">ASSINADO DIGITALMENTE</div>
    
    <!-- Cabeçalho -->
    <div class="header">
        <h1>{{ $contract->title }}</h1>
        <div class="contract-number">Contrato Nº {{ $contract->contract_number }}</div>
        <div class="generation-info">
            Documento gerado em {{ now()->format('d/m/Y \à\s H:i:s') }}
        </div>
    </div>
    
    <!-- Informações do Contrato -->
    <div class="contract-info">
        <h3>Informações do Contrato</h3>
        <table>
            <tr>
                <td>Aluno:</td>
                <td>{{ $contract->matricula->nome_completo }}</td>
            </tr>
            <tr>
                <td>CPF:</td>
                <td>{{ $contract->matricula->cpf_formatado }}</td>
            </tr>
            <tr>
                <td>Email:</td>
                <td>{{ $contract->student_email }}</td>
            </tr>
            <tr>
                <td>Matrícula:</td>
                <td>{{ $contract->matricula->numero_matricula }}</td>
            </tr>
            <tr>
                <td>Curso:</td>
                <td>{{ $contract->matricula->curso }}</td>
            </tr>
            <tr>
                <td>Modalidade:</td>
                <td>{{ $contract->matricula->modalidade }}</td>
            </tr>
            <tr>
                <td>Data de Criação:</td>
                <td>{{ $contract->created_at->format('d/m/Y \à\s H:i:s') }}</td>
            </tr>
            <tr>
                <td>Data de Assinatura:</td>
                <td>{{ $contract->signed_at->format('d/m/Y \à\s H:i:s') }}</td>
            </tr>
        </table>
    </div>
    
    <!-- Conteúdo do Contrato -->
    <div class="contract-content">
        {!! $contract->processContent() !!}
    </div>
    
    <!-- Seção de Assinatura -->
    <div class="signature-section">
        <h3>ASSINATURAS DIGITAIS</h3>
        
        <!-- Assinatura do Aluno -->
        <div class="signature-info">
            <h4 style="margin-bottom: 10px; color: #2c3e50;">ASSINATURA DO ALUNO</h4>
            <p><strong>Assinado por:</strong> {{ $contract->matricula->nome_completo }}</p>
            <p><strong>CPF:</strong> {{ $contract->matricula->cpf_formatado }}</p>
            <p><strong>Email:</strong> {{ $contract->student_email }}</p>
            <p><strong>Data/Hora:</strong> {{ $contract->signed_at->format('d/m/Y \à\s H:i:s') }}</p>
            <p><strong>IP:</strong> {{ $contract->signature_ip }}</p>
        </div>
        
        @if($contract->signature_data)
            <div class="signature-image">
                <img src="{{ $contract->signature_data }}" alt="Assinatura Digital do Aluno">
                <div class="signature-label">
                    Assinatura Digital do Aluno - Capturada em {{ $contract->signed_at->format('d/m/Y \à\s H:i:s') }}
                </div>
            </div>
        @endif
        
        <!-- Assinatura da Escola -->
        @if($contract->school_signature_data)
            <div class="signature-info" style="margin-top: 30px; background-color: #f0f8ff;">
                <h4 style="margin-bottom: 10px; color: #2c3e50;">ASSINATURA DA INSTITUIÇÃO</h4>
                <p><strong>Assinado por:</strong> {{ $contract->school_signature_name }}</p>
                <p><strong>Cargo:</strong> {{ $contract->school_signature_title }}</p>
                <p><strong>Data/Hora:</strong> {{ $contract->school_signed_at->format('d/m/Y \à\s H:i:s') }}</p>
                <p><strong>Instituição:</strong> {{ config('app.name') }}</p>
            </div>
            
            <div class="signature-image">
                <img src="{{ $contract->school_signature_data }}" alt="Assinatura Digital da Escola">
                <div class="signature-label">
                    Assinatura Digital da Instituição - Aplicada automaticamente em {{ $contract->school_signed_at->format('d/m/Y \à\s H:i:s') }}
                </div>
            </div>
        @endif
    </div>
    
    <!-- Informações de Validade -->
    <div class="validity-info">
        <h4>VALIDADE JURÍDICA</h4>
        <p>
            Este documento foi assinado digitalmente e possui validade jurídica conforme:
        </p>
        <p>
            • Lei nº 14.063/2020 - Lei de Assinatura Eletrônica<br>
            • Medida Provisória nº 2.200-2/2001 - ICP-Brasil<br>
            • Código Civil Brasileiro - Art. 107 e 221
        </p>
        <p>
            <strong>Hash do Documento:</strong> {{ md5($contract->processContent() . $contract->signed_at) }}
        </p>
        <p>
            <strong>Verificação:</strong> Este documento pode ser verificado através do sistema de contratos digitais.
        </p>
    </div>
    
    <!-- Rodapé -->
    <div class="footer">
        <p>
            {{ config('app.name') }} - Sistema de Contratos Digitais | 
            Documento gerado em {{ now()->format('d/m/Y H:i:s') }} | 
            Página <span class="pagenum"></span>
        </p>
    </div>
</body>
</html> 