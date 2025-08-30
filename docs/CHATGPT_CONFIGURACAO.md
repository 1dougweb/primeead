# Configuração do ChatGPT - Sistema de Prompts Configuráveis

## Visão Geral

O sistema agora permite configurar todos os prompts do ChatGPT através do painel administrativo, movendo as configurações do backend para o frontend. Isso oferece maior flexibilidade e controle sobre como o ChatGPT gera conteúdo.

## Funcionalidades

### 🎯 Prompts Configuráveis

- **Prompt do Sistema**: Personalidade e comportamento geral do assistente
- **Templates de Email**: Instruções específicas para emails de marketing
- **Templates de WhatsApp**: Instruções para mensagens de WhatsApp
- **Templates de Contratos**: Instruções para contratos educacionais
- **Templates de Pagamento**: Instruções para documentos de pagamento
- **Templates de Inscrição**: Instruções para documentos de inscrição
- **Templates de Matrícula**: Instruções para documentos de matrícula
- **Suporte ao Cliente**: Comportamento do assistente de suporte

### 🔧 Configurações Básicas

- **API Key**: Chave de acesso à API do OpenAI
- **Modelo**: Modelo do ChatGPT a ser utilizado
- **Status**: Ativar/desativar o sistema de IA

## Como Configurar

### 1. Acesso às Configurações

Navegue para: `Admin > Configurações > ChatGPT`

### 2. Aba Básico

Configure as configurações essenciais:
- **API Key**: Sua chave da OpenAI
- **Modelo**: Escolha o modelo (recomendado: GPT-4o Mini)
- **Prompt do Sistema**: Personalidade do assistente
- **Ativar ChatGPT**: Checkbox para ativar o sistema

### 3. Aba Prompts

Configure cada tipo de prompt específico:

#### Email Marketing
```text
Crie um template HTML completo de email marketing para campanhas de certificação do ensino médio e fundamental com as seguintes especificações:

🎯 TIPO DE TEMPLATE: {templateType}
🎯 OBJETIVO: {objective}
🎯 PÚBLICO-ALVO: {targetAudience}

🏫 CONTEXTO ESPECÍFICO:
- Empresa de certificação EJA/Supletivo
- Público adulto que não completou os estudos básicos
- Diplomas reconhecidos pelo MEC
- Foco em superação pessoal e conquista do certificado

📋 REQUISITOS OBRIGATÓRIOS:
- Template responsivo (600px máximo de largura)
- HTML com CSS inline para máxima compatibilidade
- Design moderno, colorido e divertido que inspire confiança
- Estrutura baseada em tabelas para compatibilidade
- CTAs com gradientes vibrantes e efeitos hover
- Paleta de cores vibrante: vermelhos, dourados, azuis

🔧 VARIÁVEIS DISPONÍVEIS (OBRIGATÓRIO USAR):
{variablesText}

{additionalInstructions}
```

#### WhatsApp
```text
Crie uma mensagem de WhatsApp profissional e persuasiva para campanhas de certificação do ensino médio e fundamental:

🎯 OBJETIVO: {objective}
🎯 PÚBLICO-ALVO: {targetAudience}

🏫 CONTEXTO:
- Empresa de certificação EJA/Supletivo
- Público adulto que não completou os estudos
- Diplomas reconhecidos pelo MEC
- Foco em superação pessoal

📱 REQUISITOS:
- Mensagem clara e direta
- Tom motivacional e encorajador
- Máximo 300 caracteres
- Inclua emojis relevantes
- Call-to-action claro
- Linguagem acessível

{additionalInstructions}
```

#### Contratos
```text
Crie um template de contrato HTML profissional com as seguintes especificações:

🎯 OBJETIVO: {objective}
📋 TIPO DE CONTRATO: {contractType}

📝 VARIÁVEIS DISPONÍVEIS (use no formato {{variavel}}):
{variablesText}

{referenceInstructions}

📋 REQUISITOS OBRIGATÓRIOS:
- Contrato deve ser para instituição de ensino brasileira
- Incluir identificação completa das partes (escola e aluno)
- Objeto do contrato (curso, modalidade, duração)
- Valores e formas de pagamento
- Direitos e obrigações de ambas as partes
- Política de cancelamento e reembolso
- Cláusulas sobre certificação e documentação
- Local e data para assinatura
- Foro e legislação aplicável

🎨 FORMATAÇÃO:
- Use HTML com CSS inline para formatação profissional
- Aparência formal similar a documentos jurídicos
- Fonte legível (Arial, Times New Roman)
- Margens adequadas para impressão (2cm)
- Cabeçalho com nome da instituição
- Numeração clara de cláusulas e subcláusulas
- Espaços adequados para assinatura das partes
- Quebras de página quando necessário

⚖️ ASPECTOS LEGAIS:
- Conforme legislação brasileira
- CDC (Código de Defesa do Consumidor)
- Lei de Diretrizes e Bases da Educação
- Linguagem jurídica apropriada e clara

{additionalInstructions}
```

## Variáveis Disponíveis

### 📧 Email Marketing
- `{templateType}`: Tipo de template (welcome, followup, promotional, etc.)
- `{objective}`: Objetivo da campanha
- `{targetAudience}`: Público-alvo
- `{variablesText}`: Lista de variáveis disponíveis
- `{additionalInstructions}`: Instruções adicionais do usuário

### 📱 WhatsApp
- `{objective}`: Objetivo da mensagem
- `{targetAudience}`: Público-alvo
- `{additionalInstructions}`: Instruções adicionais

### 📄 Documentos (Contratos, Pagamento, Inscrição, Matrícula)
- `{objective}`: Objetivo do documento
- `{contractType}` / `{paymentType}` / `{enrollmentType}` / `{matriculationType}`: Tipo do documento
- `{variablesText}`: Lista de variáveis disponíveis
- `{referenceInstructions}`: Instruções para documentos de referência
- `{additionalInstructions}`: Instruções adicionais do usuário

## Comandos Artisan

### Configurar Prompts Padrão
```bash
php artisan ai:setup
```

### Resetar para Valores Padrão
```bash
php artisan ai:setup --reset
```

## Estrutura Técnica

### Modelos
- `SystemSetting`: Armazena todas as configurações
- `AiSetting`: Modelo legado para compatibilidade

### Controllers
- `AiSettingsController`: Gerencia configurações específicas de IA
- `SettingsController`: Gerencia configurações gerais do sistema

### Services
- `ChatGptService`: Usa os prompts configurados para gerar conteúdo
- `ChatService`: Serviço de chat que utiliza o ChatGPT

### Views
- `admin/settings/ai.blade.php`: Interface de configuração com abas
- `admin/settings/index.blade.php`: Configurações gerais (inclui IA)

## Fluxo de Funcionamento

1. **Configuração**: Admin define prompts no painel
2. **Armazenamento**: Prompts são salvos no `SystemSetting`
3. **Uso**: `ChatGptService` busca prompts das configurações
4. **Fallback**: Se não configurado, usa prompt padrão
5. **Geração**: ChatGPT gera conteúdo baseado no prompt configurado

## Benefícios

### ✅ Para Administradores
- Controle total sobre o comportamento do ChatGPT
- Personalização sem necessidade de alterar código
- Prompts específicos para cada tipo de conteúdo
- Fácil ajuste e otimização

### ✅ Para Desenvolvedores
- Código mais limpo e organizado
- Prompts centralizados em um local
- Fácil manutenção e atualização
- Sistema flexível e extensível

### ✅ Para Usuários Finais
- Conteúdo mais consistente e alinhado com a marca
- Melhor qualidade nos templates gerados
- Experiência mais personalizada

## Dicas de Uso

### 🎯 Prompts Efetivos
- Seja específico sobre o formato desejado
- Inclua exemplos quando possível
- Defina claramente o tom e estilo
- Especifique requisitos técnicos

### 🔧 Variáveis
- Use as variáveis disponíveis para personalização
- Mantenha consistência na nomenclatura
- Teste diferentes combinações

### 📝 Manutenção
- Revise prompts regularmente
- Ajuste baseado no feedback dos usuários
- Mantenha prompts atualizados com mudanças na marca

## Troubleshooting

### Problema: ChatGPT não está funcionando
**Solução**: Verifique se:
- API Key está configurada
- ChatGPT está ativado
- Modelo selecionado é válido

### Problema: Prompts não estão sendo aplicados
**Solução**: 
- Limpe o cache: `php artisan cache:clear`
- Verifique se os prompts foram salvos
- Confirme que o `ChatGptService` está usando as configurações

### Problema: Erro na API
**Solução**:
- Teste a conexão no painel
- Verifique se a API Key é válida
- Confirme se há créditos na conta OpenAI

## Próximos Passos

### 🚀 Funcionalidades Futuras
- Editor visual para prompts
- Histórico de versões dos prompts
- Templates de prompts pré-definidos
- A/B testing de prompts
- Analytics de performance dos prompts

### 🔗 Integrações
- Suporte a outros modelos de IA
- Integração com sistemas de feedback
- API para gerenciamento de prompts
- Webhooks para notificações

---

**Nota**: Este sistema substitui a configuração hardcoded anterior, oferecendo maior flexibilidade e controle sobre a geração de conteúdo com IA.
