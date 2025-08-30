<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Importação de Matrículas</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            background-color: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            text-align: center;
        }
        .success {
            background-color: #d4edda;
            border: 1px solid #c3e6cb;
            color: #155724;
        }
        .error {
            background-color: #f8d7da;
            border: 1px solid #f5c6cb;
            color: #721c24;
        }
        .stats {
            background-color: #e2e3e5;
            padding: 15px;
            border-radius: 5px;
            margin: 20px 0;
        }
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 15px;
            margin-top: 15px;
        }
        .stat-item {
            text-align: center;
            padding: 10px;
            background-color: white;
            border-radius: 5px;
        }
        .stat-number {
            font-size: 24px;
            font-weight: bold;
            color: #007bff;
        }
        .stat-label {
            font-size: 12px;
            color: #666;
            text-transform: uppercase;
        }
        .errors {
            background-color: #fff3cd;
            border: 1px solid #ffeaa7;
            color: #856404;
            padding: 15px;
            border-radius: 5px;
            margin: 20px 0;
        }
        .error-item {
            margin-bottom: 10px;
            padding: 10px;
            background-color: white;
            border-radius: 3px;
            border-left: 4px solid #ffc107;
        }
        .footer {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #eee;
            text-align: center;
            color: #666;
            font-size: 12px;
        }
    </style>
</head>
<body>
    <div class="header {{ $success ? 'success' : 'error' }}">
        @if($success)
            <h1>✅ Importação Concluída</h1>
            <p>A importação de matrículas foi processada com sucesso!</p>
        @else
            <h1>❌ Erro na Importação</h1>
            <p>Ocorreu um erro durante o processamento da importação.</p>
        @endif
    </div>

    @if($success && isset($data['total_processed']))
        <div class="stats">
            <h3>📊 Resumo da Importação</h3>
            <div class="stats-grid">
                <div class="stat-item">
                    <div class="stat-number">{{ $data['total_processed'] }}</div>
                    <div class="stat-label">Total Processados</div>
                </div>
                <div class="stat-item">
                    <div class="stat-number">{{ $data['created'] ?? 0 }}</div>
                    <div class="stat-label">Criados</div>
                </div>
                <div class="stat-item">
                    <div class="stat-number">{{ $data['updated'] ?? 0 }}</div>
                    <div class="stat-label">Atualizados</div>
                </div>
                <div class="stat-item">
                    <div class="stat-number">{{ $data['skipped'] ?? 0 }}</div>
                    <div class="stat-label">Ignorados</div>
                </div>
            </div>

            @if(isset($data['duration']))
                <div style="text-align: center; margin-top: 15px; color: #666;">
                    ⏱️ Tempo de processamento: {{ gmdate('H:i:s', $data['duration']) }}
                </div>
            @endif
        </div>
    @endif

    @if(isset($data['error']))
        <div class="errors">
            <h3>⚠️ Erro Encontrado</h3>
            <p><strong>{{ $data['error'] }}</strong></p>
        </div>
    @endif

    @if($hasErrors && !empty($data['errors']))
        <div class="errors">
            <h3>⚠️ Erros de Validação</h3>
            <p>Foram encontrados os seguintes erros durante a importação:</p>
            
            @foreach(array_slice($data['errors'], 0, 10) as $error)
                <div class="error-item">
                    <strong>Linha {{ $error['row'] }}:</strong>
                    <ul style="margin: 5px 0; padding-left: 20px;">
                        @foreach($error['errors'] as $errorMessage)
                            <li>{{ $errorMessage }}</li>
                        @endforeach
                    </ul>
                </div>
            @endforeach

            @if(count($data['errors']) > 10)
                <p style="margin-top: 15px; font-style: italic;">
                    ... e mais {{ count($data['errors']) - 10 }} erros. 
                    Verifique o log completo para mais detalhes.
                </p>
            @endif
        </div>
    @endif

    <div class="footer">
        <p>Este e-mail foi enviado automaticamente pelo sistema de importação.</p>
        <p>Data: {{ now()->format('d/m/Y H:i:s') }}</p>
    </div>
</body>
</html>
