<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>EJA Supletivo Ensino Médio Online | Ensino Certo</title>
</head>
<body>
    <h1>EJA Supletivo Ensino Médio Online</h1>
    <p>Bem-vindo ao Ensino Certo</p>
    
    @if(isset($countdownSettings) && $countdownSettings['enabled'])
        <div class="countdown">
            <p>Promoção ativa!</p>
        </div>
    @endif
    
    @if(isset($whatsappSettings) && $whatsappSettings['whatsapp_enabled'])
        <p>WhatsApp disponível</p>
    @endif
    
    <p>Teste de variáveis: {{ $defaultCourse ?? 'N/A' }}</p>
</body>
</html>
