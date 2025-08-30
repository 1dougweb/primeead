# Configuração do Webhook Mercado Pago - PRODUÇÃO

## URL do Webhook

**URL Pública:** `http://rngce-186-232-105-158.a.free.pinggy.link/api/webhook/mercadopago`

**URL Interna:** `/api/webhook/mercadopago`

## Configuração no Painel do Mercado Pago

### 1. Acesse o Painel do Desenvolvedor
- Faça login em [https://www.mercadopago.com.br/developers](https://www.mercadopago.com.br/developers)
- Selecione sua aplicação de produção

### 2. Configure o Webhook
- Vá para **"Webhooks"** no menu lateral
- Clique em **"Configurar notificações"**
- Adicione a URL: `http://rngce-186-232-105-158.a.free.pinggy.link/api/webhook/mercadopago`

### 3. Eventos a Configurar
Configure os seguintes eventos para receber notificações:

- ✅ `payment.updated` - Atualizações de pagamentos
- ✅ `order.updated` - Atualizações de ordens (nova API)
- ✅ `subscription.updated` - Atualizações de assinaturas
- ✅ `subscription.cancelled` - Cancelamentos de assinaturas

## Teste do Webhook

### Endpoint de Teste
- **URL:** `http://rngce-186-232-105-158.a.free.pinggy.link/api/webhook-test`
- **Método:** POST
- **Content-Type:** application/json

### Exemplo de Teste
```bash
curl -X POST "http://rngce-186-232-105-158.a.free.pinggy.link/api/webhook-test" \
  -H "Content-Type: application/json" \
  -d '{
    "action": "payment.updated",
    "api_version": "v1",
    "data": {"id": "ORD01K2D20J2SQWDTPGBRDAWVNQHT"},
    "date_created": "2021-11-01T02:02:02Z",
    "id": "123456",
    "live_mode": false,
    "type": "payment",
    "user_id": 158502083
  }'
```

## Configurações de Segurança

### Webhook Secret
O webhook está configurado para validar assinaturas. Para testes, você pode usar o endpoint de teste que não requer validação.

### IPs Permitidos
O webhook aceita requisições de qualquer IP para facilitar testes, mas em produção você pode configurar restrições de IP.

## Monitoramento

### Logs
- **Localização:** `storage/logs/laravel.log`
- **Filtro:** Buscar por "Mercado Pago webhook"

### Endpoints de Status
- **Teste:** `http://rngce-186-232-105-158.a.free.pinggy.link/api/webhook/test`
- **Debug:** `http://rngce-186-232-105-158.a.free.pinggy.link/api/webhook/debug`
- **Mercado Pago:** `http://rngce-186-232-105-158.a.free.pinggy.link/api/webhook/mercadopago-test`

## Solução de Problemas

### Erro 419 (Page Expired)
- **Causa:** Problema com middleware CSRF
- **Solução:** Use as rotas da API (`/api/webhook/*`)

### Webhook não recebido
- **Verificar:** Logs em `storage/logs/laravel.log`
- **Testar:** Endpoint de teste em `/api/webhook-test`

### Assinatura inválida
- **Causa:** Headers de assinatura ausentes
- **Solução:** Use o endpoint de teste para desenvolvimento

## URLs Importantes

- **Webhook Principal:** `http://rngce-186-232-105-158.a.free.pinggy.link/api/webhook/mercadopago`
- **Painel Admin:** `http://rngce-186-232-105-158.a.free.pinggy.link/admin/settings`
- **Documentação MP:** [https://www.mercadopago.com.br/developers/en/docs/checkout-api-v2/payment-integration](https://www.mercadopago.com.br/developers/en/docs/checkout-api-v2/payment-integration)

## Status do Sistema

✅ **Webhook funcionando corretamente**
✅ **Logs sendo registrados**
✅ **Validação de assinatura ativa**
✅ **Endpoints de teste disponíveis**
✅ **Integração com Mercado Pago ativa**
