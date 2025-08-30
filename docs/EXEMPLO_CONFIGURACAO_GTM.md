# Exemplo de Configuração do GTM na Landing Page

## Cenário de Exemplo

Vamos configurar o Google Tag Manager para uma landing page de EJA Supletivo com o ID `GTM-NPXJKW38`.

## Passo a Passo

### 1. Acessar o Painel Administrativo

```
URL: /admin/configuracoes
Usuário: Administrador com permissões de configuração
```

### 2. Navegar para a Aba Tracking

1. Clique na aba **"Tracking"**
2. Role até a seção **"Google Tag Manager - Landing Page"**

### 3. Configurar os Campos

#### ✅ Ativar GTM na Landing Page
- **Campo**: Checkbox "Ativar Google Tag Manager"
- **Ação**: Marcar a caixa de seleção
- **Status**: ✅ Ativo

#### 🔑 ID do GTM
- **Campo**: Input "ID do GTM"
- **Valor**: `GTM-NPXJKW38`
- **Formato**: GTM-XXXXXXX
- **Status**: ✅ Preenchido

#### 📝 Eventos Personalizados (Opcional)
- **Campo**: Área de texto "Eventos Personalizados"
- **Valor**: 
```json
{
  "form_submit": {
    "event": "form_submit",
    "form_name": "inscricao_eja",
    "page_type": "landing_page"
  },
  "scroll_depth": {
    "event": "scroll_depth",
    "thresholds": [25, 50, 75, 100]
  },
  "time_on_page": {
    "event": "time_on_page",
    "intervals": [30, 60, 120, 300]
  }
}
```

### 4. Salvar Configurações

1. Clique no botão **"Salvar Alterações"**
2. Aguarde a mensagem de sucesso
3. As configurações são aplicadas automaticamente

## Resultado na Landing Page

### Tag JavaScript (Head)
```html
<!-- Google Tag Manager -->
<script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
'https://www.googletagmanager.com/gtm.js?id=GTM-NPXJKW38';f.parentNode.insertBefore(j,f);
})(window,document,'script','dataLayer','GTM-NPXJKW38');</script>
<!-- End Google Tag Manager -->
```

### Tag Noscript (Body)
```html
<!-- Google Tag Manager (noscript) -->
<noscript><iframe src="https://www.googletagmanager.com/ns.html?id=GTM-NPXJKW38"
height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
<!-- End Google Tag Manager (noscript) -->
```

## Verificação da Implementação

### 1. Inspecionar o Código Fonte
1. Abra a landing page (`/`)
2. Clique com botão direito → "Ver código fonte"
3. Procure por `GTM-NPXJKW38`
4. Verifique se as tags estão presentes

### 2. Console do Navegador
1. Abra as Ferramentas do Desenvolvedor (F12)
2. Vá para a aba "Console"
3. Digite: `dataLayer`
4. Deve retornar um array (pode estar vazio inicialmente)

### 3. Extensão GTM Assistant
1. Instale a extensão "Google Tag Manager Assistant"
2. Ative na landing page
3. Verifique se o container está sendo carregado

## Configurações no Google Tag Manager

### 1. Acessar o GTM
```
URL: https://tagmanager.google.com/
Container: GTM-NPXJKW38
```

### 2. Configurar Tags
- **Google Analytics 4**: Para rastreamento de páginas
- **Facebook Pixel**: Para remarketing
- **Hotjar**: Para análise de comportamento
- **Google Ads**: Para conversões

### 3. Configurar Triggers
- **Page View**: Todas as páginas
- **Form Submit**: Envio de formulários
- **Scroll Depth**: Profundidade de scroll
- **Time on Page**: Tempo na página

### 4. Configurar Variáveis
- **Page URL**: URL da página atual
- **Page Title**: Título da página
- **Form Name**: Nome do formulário
- **User Type**: Tipo de usuário

## Exemplo de Evento Personalizado

### Envio de Formulário
```javascript
// No JavaScript da landing page
document.getElementById('formulario-inscricao').addEventListener('submit', function() {
    // Enviar evento para GTM
    if (typeof dataLayer !== 'undefined') {
        dataLayer.push({
            'event': 'form_submit',
            'form_name': 'inscricao_eja',
            'page_type': 'landing_page',
            'user_type': 'visitor'
        });
    }
});
```

### Profundidade de Scroll
```javascript
// Rastrear profundidade de scroll
let scrollDepthMarks = [25, 50, 75, 100];
let scrollDepthReached = [];

window.addEventListener('scroll', function() {
    let scrollPercent = Math.round((window.scrollY / (document.body.offsetHeight - window.innerHeight)) * 100);
    
    scrollDepthMarks.forEach(function(mark) {
        if (scrollPercent >= mark && !scrollDepthReached.includes(mark)) {
            scrollDepthReached.push(mark);
            
            if (typeof dataLayer !== 'undefined') {
                dataLayer.push({
                    'event': 'scroll_depth',
                    'scroll_percentage': mark,
                    'page_type': 'landing_page'
                });
            }
        }
    });
});
```

## Troubleshooting Comum

### ❌ GTM não carrega
**Sintoma**: Tags não aparecem no código fonte
**Solução**: 
1. Verificar se `landing_gtm_enabled` está ativo
2. Confirmar se `landing_gtm_id` está preenchido
3. Limpar cache do navegador

### ❌ Erro no Console
**Sintoma**: Erro JavaScript relacionado ao GTM
**Solução**:
1. Validar formato do ID (GTM-XXXXXXX)
2. Verificar se o container existe no GTM
3. Testar em modo de desenvolvimento

### ❌ Eventos não registram
**Sintoma**: Eventos não aparecem no GTM
**Solução**:
1. Verificar se o dataLayer está sendo inicializado
2. Confirmar se os triggers estão configurados
3. Testar com o modo de preview do GTM

## Próximos Passos

### 1. Configurar Google Analytics 4
- Criar propriedade no GA4
- Configurar tag de configuração no GTM
- Definir triggers para eventos importantes

### 2. Implementar Facebook Pixel
- Configurar conversões
- Definir públicos personalizados
- Configurar remarketing

### 3. Otimizar Conversões
- Rastrear micro-conversões
- Analisar jornada do usuário
- A/B testar elementos da página

---

**Exemplo baseado em**: GTM-NPXJKW38  
**Data**: Janeiro 2025  
**Versão**: 1.0
