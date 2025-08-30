<?php
echo "<h1>🐘 PHP funcionando!</h1>";
echo "<p><strong>PHP Version:</strong> " . PHP_VERSION . "</p>";
echo "<p><strong>Extensions:</strong> " . implode(', ', get_loaded_extensions()) . "</p>";
echo "<hr>";
echo "<p><strong>Próximo teste:</strong> <a href=\"/\">Laravel App</a></p>";

// Testar conexão com banco
try {
    $pdo = new PDO('mysql:host=db;dbname=ensino_certo', 'ensino_certo_user', 'senha_segura');
    echo "<p>✅ <strong>Banco MySQL:</strong> Conectado com sucesso!</p>";
} catch (Exception $e) {
    echo "<p>❌ <strong>Banco MySQL:</strong> " . $e->getMessage() . "</p>";
}

// Verificar .env
if (file_exists('../.env')) {
    echo "<p>✅ <strong>Arquivo .env:</strong> Existe</p>";
} else {
    echo "<p>❌ <strong>Arquivo .env:</strong> Não encontrado</p>";
}

// Verificar storage
if (is_writable('../storage')) {
    echo "<p>✅ <strong>Storage:</strong> Gravável</p>";
} else {
    echo "<p>❌ <strong>Storage:</strong> Não gravável</p>";
}
?>
