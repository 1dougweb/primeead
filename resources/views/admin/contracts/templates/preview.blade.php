<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Preview - {{ $template->name }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .preview-container {
            max-width: 800px;
            margin: 20px auto;
            background: white;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
            border-radius: 8px;
            overflow: hidden;
        }
        .preview-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px;
            text-align: center;
        }
        .preview-content {
            padding: 40px;
            line-height: 1.6;
        }
        .preview-footer {
            background-color: #f8f9fa;
            padding: 20px;
            text-align: center;
            border-top: 1px solid #dee2e6;
        }
        .watermark {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-45deg);
            font-size: 48px;
            color: rgba(0,0,0,0.1);
            pointer-events: none;
            z-index: 1000;
            font-weight: bold;
        }
        .template-info {
            background-color: #e9ecef;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
            font-size: 14px;
        }
        .template-info strong {
            color: #495057;
        }
        .close-btn {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 1001;
            background: rgba(0,0,0,0.7);
            color: white;
            border: none;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            font-size: 18px;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        .close-btn:hover {
            background: rgba(0,0,0,0.9);
            transform: scale(1.1);
        }
        .print-btn {
            position: fixed;
            top: 70px;
            right: 20px;
            z-index: 1001;
            background: #007bff;
            color: white;
            border: none;
            border-radius: 5px;
            padding: 8px 15px;
            font-size: 14px;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        .print-btn:hover {
            background: #0056b3;
            transform: scale(1.05);
        }
        @media print {
            .close-btn, .print-btn, .watermark {
                display: none !important;
            }
            .preview-container {
                box-shadow: none;
                margin: 0;
            }
            body {
                background: white;
            }
        }
    </style>
</head>
<body>
    <!-- Botão Fechar -->
    <button class="close-btn" onclick="window.close()" title="Fechar Preview">
        <i class="fas fa-times"></i>
    </button>
    
    <!-- Botão Imprimir -->
    <button class="print-btn" onclick="window.print()" title="Imprimir">
        <i class="fas fa-print me-1"></i>
        Imprimir
    </button>
    
    <!-- Watermark -->
    <div class="watermark">PREVIEW</div>
    
    <!-- Container Principal -->
    <div class="preview-container">
        <!-- Cabeçalho -->
        <div class="preview-header">
            <h1><i class="fas fa-file-contract me-2"></i>Preview do Template</h1>
            <p class="mb-0">{{ $template->name }}</p>
        </div>
        
        <!-- Informações do Template -->
        <div class="template-info">
            <div class="row">
                <div class="col-md-6">
                    <strong>Template:</strong> {{ $template->name }}<br>
                    <strong>Status:</strong> 
                    <span class="badge bg-{{ $template->is_active ? 'success' : 'secondary' }}">
                        {{ $template->is_active ? 'Ativo' : 'Inativo' }}
                    </span>
                </div>
                <div class="col-md-6">
                    <strong>Validade:</strong> {{ $template->validity_days }} dias<br>
                    <strong>Contratos Gerados:</strong> {{ $template->contracts_count }}
                </div>
            </div>
            @if($template->description)
                <div class="mt-2">
                    <strong>Descrição:</strong> {{ $template->description }}
                </div>
            @endif
        </div>
        
        <!-- Conteúdo do Template -->
        <div class="preview-content">
            {!! $content !!}
        </div>
        
        <!-- Rodapé -->
        <div class="preview-footer">
            <p class="mb-0 text-muted">
                <small>
                    <i class="fas fa-info-circle me-1"></i>
                    Este é um preview do template com dados de exemplo. 
                    Os dados reais serão inseridos quando o contrato for gerado.
                </small>
            </p>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Fechar com ESC
        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape') {
                window.close();
            }
        });
        
        // Fechar com Ctrl+P (abrir diálogo de impressão)
        document.addEventListener('keydown', function(event) {
            if (event.ctrlKey && event.key === 'p') {
                event.preventDefault();
                window.print();
            }
        });
        
        // Auto-resize para melhor visualização
        window.addEventListener('load', function() {
            // Ajustar altura do container se necessário
            const container = document.querySelector('.preview-container');
            const windowHeight = window.innerHeight;
            const containerHeight = container.offsetHeight;
            
            if (containerHeight > windowHeight - 100) {
                container.style.maxHeight = (windowHeight - 100) + 'px';
                container.style.overflowY = 'auto';
            }
        });
    </script>
</body>
</html> 