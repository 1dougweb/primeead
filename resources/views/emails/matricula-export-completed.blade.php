<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Exportação de Matrículas</title>
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
        .file-info {
            background-color: #fff3cd;
            border: 1px solid #ffeaa7;
            color: #856404;
            padding: 15px;
            border-radius: 5px;
            margin: 20px 0;
        }
        .error-message {
            background-color: #f8d7da;
            border: 1px solid #f5c6cb;
            color: #721c24;
            padding: 15px;
            border-radius: 5px;
            margin: 20px 0;
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
            <h1>✅ Exportação Concluída</h1>
            <p>A exportação de matrículas foi processada com sucesso!</p>
        @else
            <h1>❌ Erro na Exportação</h1>
            <p>Ocorreu um erro durante o processamento da exportação.</p>
        @endif
    </div>

    @if($success && isset($data['total_exported']))
        <div class="stats">
            <h3>📊 Resumo da Exportação</h3>
            <div class="stats-grid">
                <div class="stat-item">
                    <div class="stat-number">{{ $data['total_exported'] }}</div>
                    <div class="stat-label">Total Exportado</div>
                </div>
                <div class="stat-item">
                    <div class="stat-number">{{ strtoupper($data['format']) }}</div>
                    <div class="stat-label">Formato</div>
                </div>
                <div class="stat-item">
                    <div class="stat-number">{{ $data['filters_applied'] ? 'Sim' : 'Não' }}</div>
                    <div class="stat-label">Filtros Aplicados</div>
                </div>
            </div>
        </div>

        <div class="file-info">
            <h3>📁 Informações do Arquivo</h3>
            <p><strong>Nome:</strong> {{ $data['file_name'] ?? 'N/A' }}</p>
            <p><strong>Tamanho:</strong> {{ isset($data['file_size']) ? number_format($data['file_size'] / 1024, 2) . ' KB' : 'N/A' }}</p>
            <p><strong>Formato:</strong> {{ strtoupper($data['format'] ?? 'N/A') }}</p>
        </div>

        @if(isset($data['duration']) && $data['duration'] > 0)
            <div style="text-align: center; margin-top: 15px; color: #666;">
                ⏱️ Tempo de processamento: {{ gmdate('H:i:s', $data['duration']) }}
            </div>
        @endif
    @endif

    @if($hasError && isset($data['error']))
        <div class="error-message">
            <h3>⚠️ Erro Encontrado</h3>
            <p><strong>{{ $data['error'] }}</strong></p>
        </div>
    @endif

    <div class="footer">
        <p>Este e-mail foi enviado automaticamente pelo sistema de exportação.</p>
        <p>Data: {{ now()->format('d/m/Y H:i:s') }}</p>
    </div>
</body>
</html>
