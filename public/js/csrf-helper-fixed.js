// CSRF Helper Fixado - Baseado no padrão usado em matriculas/edit
console.log('CSRF Helper Fixado carregado');

// Função para obter o token CSRF (mesmo padrão usado em matriculas/edit)
function getCsrfToken() {
    const metaTag = document.querySelector('meta[name="csrf-token"]');
    if (!metaTag) {
        console.error('Meta tag CSRF não encontrada');
        return '';
    }
    return metaTag.getAttribute('content');
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
        
        // Atualizar meta tag (mesmo padrão usado em matriculas/edit)
        const metaTag = document.querySelector('meta[name="csrf-token"]');
        if (metaTag) {
            metaTag.setAttribute('content', data.token);
            console.log('Meta tag CSRF atualizada:', data.token);
        }
        
        // Atualizar campos hidden
        document.querySelectorAll('input[name="_token"]').forEach(input => {
            input.value = data.token;
        });

        // Atualizar configuração do Axios se disponível
        if (typeof axios !== 'undefined') {
            axios.defaults.headers.common['X-CSRF-TOKEN'] = data.token;
        }

        console.log('Token CSRF atualizado com sucesso');
        return data.token;
    } catch (error) {
        console.error('Erro ao atualizar token CSRF:', error);
        throw error;
    }
}

// Configurar jQuery se disponível (mesmo padrão usado em matriculas/edit)
if (typeof $ !== 'undefined') {
    console.log('Configurando jQuery para CSRF...');
    
    // Configurar headers padrão
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': getCsrfToken()
        }
    });
    
    // Interceptar erros 419 (CSRF token mismatch)
    $(document).ajaxError(function(event, xhr, settings) {
        if (xhr.status === 419) {
            console.log('Erro 419 detectado, atualizando token...');
            refreshCsrfToken().then(() => {
                // Reenviar a requisição original
                $.ajax(settings);
            }).catch(error => {
                console.error('Falha ao atualizar token:', error);
                // Redirecionar para login se falhar
                window.location.href = '/login';
            });
        }
    });
}

// Configurar Axios se disponível
if (typeof axios !== 'undefined') {
    console.log('Configurando Axios para CSRF...');
    axios.defaults.headers.common['X-CSRF-TOKEN'] = getCsrfToken();
}

// Função para fazer requisições fetch com CSRF (mesmo padrão usado em matriculas/edit)
async function fetchWithCsrf(url, options = {}) {
    const token = getCsrfToken();
    
    const fetchOptions = {
        ...options,
        headers: {
            ...options.headers,
            'X-CSRF-TOKEN': token
        }
    };

    try {
        const response = await fetch(url, fetchOptions);
        
        // Se receber erro 419, atualizar token e tentar novamente
        if (response.status === 419) {
            console.log('Erro 419 em fetch, atualizando token...');
            await refreshCsrfToken();
            
            // Tentar novamente com o novo token
            const newToken = getCsrfToken();
            fetchOptions.headers['X-CSRF-TOKEN'] = newToken;
            return await fetch(url, fetchOptions);
        }
        
        return response;
    } catch (error) {
        console.error('Erro na requisição fetch:', error);
        throw error;
    }
}

// Expor funções globalmente
window.getCsrfToken = getCsrfToken;
window.refreshCsrfToken = refreshCsrfToken;
window.fetchWithCsrf = fetchWithCsrf;

console.log('CSRF Helper Fixado inicializado'); 