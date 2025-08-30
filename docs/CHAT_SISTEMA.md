# Sistema de Chat Integrado com ChatGPT - Educa Prime

## 📋 **Visão Geral**

Sistema de chat inteligente integrado com ChatGPT para atendimento ao cliente da plataforma Educa Prime, especializado em EJA Supletivo. O sistema permite consultas personalizadas sobre matrículas, pagamentos e processos educacionais, incluindo a funcionalidade de geração de segunda via de boleto.

## 🏗️ **Arquitetura**

### **Componentes Principais**
- **ChatService**: Lógica de negócio do chat, geração de contexto e integração com IA
- **ChatGptService**: Integração com API do OpenAI
- **BoletoSecondViaService**: Geração de segunda via de boleto via Mercado Pago
- **ChatController**: Endpoints da API para o widget de chat
- **BoletoSecondViaController**: Endpoints para funcionalidades de boleto
- **ChatWidget**: Componente Blade reutilizável para o frontend

### **Modelos de Dados**
- **ChatConversation**: Metadados das conversas
- **ChatMessage**: Mensagens individuais
- **BoletoVia**: Histórico de vias de boleto
- **Payment**: Pagamentos (com campos adicionais para controle de vias)

## 🚀 **Funcionalidades**

### **Chat Inteligente**
- ✅ Integração com ChatGPT via OpenAI
- ✅ Contexto personalizado baseado em dados reais da plataforma
- ✅ Histórico de conversas persistente
- ✅ Formatação automática de mensagens (negrito, cores, emojis)
- ✅ Botão WhatsApp dinâmico
- ✅ Efeito typewriter para mensagens
- ✅ Mensagem de boas-vindas com delay
- ✅ Badge de notificação quando chat fechado
- ✅ Scrollbar estilizada e acompanhamento automático

### **Segunda Via de Boleto** 🆕
- ✅ Geração automática via Mercado Pago
- ✅ Verificação de elegibilidade
- ✅ Controle de limites de vias (padrão: 3 vias)
- ✅ Cancelamento automático de vias anteriores
- ✅ Histórico completo de vias geradas
- ✅ Integração com sistema de pagamentos existente
- ✅ Validação de acesso por email do usuário

### **Persistência e UX**
- ✅ Histórico salvo em `localStorage`
- ✅ Sessões persistentes entre refreshs
- ✅ Input de email para personalização
- ✅ Contador de mensagens não lidas
- ✅ Interface responsiva e moderna

## 🔧 **Configurações**

### **Configurações do Chat**
```php
// Em SystemSetting
'landing_chat_enabled' => true,
'landing_chat_title' => 'Suporte ao Cliente',
'landing_chat_welcome_message' => 'Olá! Como posso ajudar você hoje?',
'landing_chat_position' => 'bottom-right',
'landing_chat_color' => '#007bff',
'landing_chat_icon' => 'chat-bubble-left-right'
```

### **Configurações de Boleto** 🆕
```php
// Em SystemSetting
'boleto_max_vias' => 3,
'boleto_expiration_days' => 3,
'boleto_allow_second_via' => true,
'boleto_auto_cancel_previous' => true,
'boleto_notification_email' => true
```

### **Prompt de Suporte**
O sistema inclui um prompt avançado para EJA Supletivo com:
- Instruções específicas para o contexto educacional
- Formatação automática de mensagens
- Comandos especiais para segunda via de boleto
- Integração com botão WhatsApp

## 📡 **API Endpoints**

### **Chat**
```
POST /api/chat/process-message
GET  /api/chat/history
POST /api/chat/close-conversation
GET  /api/chat/generate-session
GET  /api/chat/test-connection
```

### **Segunda Via de Boleto** 🆕
```
POST /api/boleto/check-eligibility
POST /api/boleto/generate-second-via
GET  /api/boleto/vias-history
GET  /api/boleto/stats
POST /api/boleto/cancel-via
POST /api/boleto/reactivate-via
```

## 🗄️ **Estrutura do Banco**

### **Tabela `payments` (modificada)**
```sql
ALTER TABLE payments ADD COLUMN boleto_vias_count INT DEFAULT 1;
ALTER TABLE payments ADD COLUMN last_boleto_generated_at TIMESTAMP;
ALTER TABLE payments ADD COLUMN boleto_history JSON;
ALTER TABLE payments ADD COLUMN can_generate_second_via BOOLEAN DEFAULT TRUE;
ALTER TABLE payments ADD COLUMN max_boleto_vias INT DEFAULT 3;
ALTER TABLE payments ADD COLUMN boleto_expires_at TIMESTAMP;
```

### **Tabela `boleto_vias` (nova)**
```sql
CREATE TABLE boleto_vias (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    payment_id BIGINT UNSIGNED,
    via_number INT,
    generated_at TIMESTAMP,
    expires_at TIMESTAMP NULL,
    boleto_url VARCHAR(500),
    digitable_line VARCHAR(100),
    barcode_content TEXT,
    financial_institution VARCHAR(255),
    status ENUM('active', 'expired', 'paid', 'cancelled'),
    metadata JSON,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    FOREIGN KEY (payment_id) REFERENCES payments(id) ON DELETE CASCADE
);
```

## 🎯 **Como Usar**

### **Para Usuários Finais**
1. Acesse a landing page
2. Clique no ícone de chat
3. Digite seu email para personalização
4. Faça perguntas sobre matrículas, pagamentos, etc.
5. Para segunda via de boleto: "Preciso da segunda via do boleto do pagamento X"

### **Para Administradores**
1. Acesse `/admin/settings`
2. Configure as abas "ChatGPT" e "Landing Page"
3. Ajuste o prompt de suporte conforme necessário
4. Configure limites de vias de boleto

### **Para Desenvolvedores**
1. O sistema detecta automaticamente comandos especiais
2. Use `[GERAR_SEGUNDA_VIA:ID]` no prompt para gerar vias
3. Use `[VERIFICAR_ELEGIBILIDADE:ID]` para verificar status
4. Todas as operações são validadas por segurança

## 🔒 **Segurança**

- ✅ Validação de acesso por email do usuário
- ✅ Verificação de elegibilidade antes da geração
- ✅ Controle de limites de vias por pagamento
- ✅ Logs detalhados de todas as operações
- ✅ Transações de banco para consistência
- ✅ Validação de dados de entrada

## 📊 **Monitoramento**

### **Estatísticas Disponíveis**
- Total de conversas e mensagens
- Vias de boleto geradas e status
- Taxa de sucesso de geração
- Tempo de resposta do chat

### **Logs e Auditoria**
- Todas as operações de segunda via são logadas
- Histórico completo de vias geradas
- Rastreamento de comandos especiais
- Monitoramento de erros da API do Mercado Pago

## 🚀 **Deploy e Manutenção**

### **Comandos Artisan**
```bash
# Limpar conversas antigas
php artisan chat:cleanup

# Executar migrações
php artisan migrate

# Popular configurações
php artisan db:seed --class=BoletoSettingsSeeder
```

### **Configurações de Ambiente**
```env
# OpenAI
OPENAI_API_KEY=sua_chave_aqui
OPENAI_MODEL=gpt-4

# Mercado Pago
MERCADOPAGO_ACCESS_TOKEN=seu_token_aqui
MERCADOPAGO_SANDBOX=true
```

## 📈 **Changelog**

### **v2.0.0 - Segunda Via de Boleto** 🆕
- ✅ Implementação completa de segunda via via Mercado Pago
- ✅ Controle de elegibilidade e limites
- ✅ Histórico completo de vias
- ✅ Integração com ChatGPT para comandos especiais
- ✅ API completa para gerenciamento de vias
- ✅ Configurações centralizadas via SystemSetting

### **v1.5.0 - Melhorias de UX**
- ✅ Fix: Scrollbar estilizada e acompanhamento automático
- ✅ Fix: Velocidade de digitação aumentada
- ✅ Fix: Mensagem de boas-vindas sem duplicação
- ✅ Fix: Botão WhatsApp renderizado corretamente

### **v1.0.0 - Funcionalidades Base**
- ✅ Chat integrado com ChatGPT
- ✅ Persistência de histórico
- ✅ Formatação automática de mensagens
- ✅ Botão WhatsApp dinâmico
- ✅ Efeito typewriter
- ✅ Badge de notificação
- ✅ Input de email para personalização

## 🆘 **Suporte e Troubleshooting**

### **Problemas Comuns**
1. **Chat não carrega**: Verificar configurações em SystemSetting
2. **Segunda via falha**: Verificar token do Mercado Pago
3. **Histórico não persiste**: Verificar localStorage do navegador
4. **Formatação não funciona**: Verificar prompt de suporte

### **Contatos**
- **Desenvolvimento**: Equipe técnica
- **Suporte**: contato@primeead.com.br
- **Documentação**: Este arquivo e comentários no código

---

**Desenvolvido para Educa Prime** 🎓  
**Versão**: 2.0.0  
**Última atualização**: Julho 2024
