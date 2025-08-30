// Teste simples do CSRF Helper
console.log('Teste CSRF Helper carregado');

// Verificar se o jQuery está disponível
if (typeof $ !== 'undefined') {
    console.log('✓ jQuery disponível');
} else {
    console.log('✗ jQuery não disponível');
}

// Verificar se o fetch está disponível
if (typeof fetch !== 'undefined') {
    console.log('✓ Fetch disponível');
} else {
    console.log('✗ Fetch não disponível');
}

// Verificar se o meta tag CSRF está presente
const metaTag = document.querySelector('meta[name="csrf-token"]');
if (metaTag) {
    console.log('✓ Meta tag CSRF encontrada:', metaTag.getAttribute('content'));
} else {
    console.log('✗ Meta tag CSRF não encontrada');
}

// Teste simples de requisição
async function testRequest() {
    try {
        console.log('Testando requisição para /test-csrf...');
        const response = await fetch('/test-csrf');
        const data = await response.json();
        console.log('✓ Requisição bem-sucedida:', data);
    } catch (error) {
        console.error('✗ Erro na requisição:', error);
    }
}

// Executar teste quando a página carregar
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', testRequest);
} else {
    testRequest();
} 