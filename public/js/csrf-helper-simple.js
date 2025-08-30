// CSRF Helper Simples
console.log('CSRF Helper Simples carregado');

// Função para obter o token CSRF
function getCsrfToken() {
    const metaTag = document.querySelector('meta[name="csrf-token"]');
    return metaTag ? metaTag.getAttribute('content') : '';
}

// Função para atualizar o token CSRF
async function refreshCsrfToken() {
    try {
        console.log('Atualizando token CSRF...');
        const response = await fetch('/refresh-csrf', {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json'
            }
        });

        if (!response.ok) {
            throw new Error(`HTTP ${response.status}`);
        }

        const data = await response.json();
        
        // Atualizar meta tag
        const metaTag = document.querySelector('meta[name="csrf-token"]');
        if (metaTag) {
            metaTag.setAttribute('content', data.token);
        }
        
        // Atualizar campos hidden
        document.querySelectorAll('input[name="_token"]').forEach(input => {
            input.value = data.token;
        });

        console.log('Token CSRF atualizado:', data.token);
        return data.token;
    } catch (error) {
        console.error('Erro ao atualizar token CSRF:', error);
        throw error;
    }
}

// Configurar jQuery se disponível
if (typeof $ !== 'undefined') {
    console.log('Configurando jQuery para CSRF...');
    
    // Configurar headers padrão
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': getCsrfToken()
        }
    });
    
    // Interceptar erros 419
    $(document).ajaxError(function(event, xhr, settings) {
        if (xhr.status === 419) {
            console.log('Erro 419 detectado, atualizando token...');
            refreshCsrfToken().then(() => {
                // Reenviar a requisição original
                $.ajax(settings);
            }).catch(error => {
                console.error('Falha ao atualizar token:', error);
                window.location.href = '/login';
            });
        }
    });
}

// Expor funções globalmente
window.getCsrfToken = getCsrfToken;
window.refreshCsrfToken = refreshCsrfToken;

console.log('CSRF Helper Simples inicializado'); 