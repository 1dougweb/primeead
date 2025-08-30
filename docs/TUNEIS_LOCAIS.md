# 🌐 Túneis Locais para Laravel

Este documento explica como configurar e usar diferentes tipos de túneis locais para expor seu Laravel localmente na internet.

## 🚀 Opções Disponíveis

### 1. **LocalTunnel (Recomendado para Início Rápido)**
- ✅ **Sem instalação** (usa npx)
- ✅ **Gratuito**
- ✅ **Configuração simples**
- ✅ **Estável para testes**

### 2. **Serveo (Mais Simples)**
- ✅ **Sem instalação** (usa SSH)
- ✅ **Completamente gratuito**
- ✅ **Configuração instantânea**
- ✅ **URLs personalizáveis**

### 3. **Ngrok (Mais Profissional)**
- ✅ **Muito estável**
- ✅ **Interface web**
- ✅ **Logs detalhados**
- ⚠️ **Requer conta gratuita**
- ⚠️ **Instalação necessária**

### 4. **Cloudflare Tunnel (Mais Estável)**
- ✅ **Muito estável**
- ✅ **Seguro**
- ✅ **URLs personalizáveis**
- ⚠️ **Instalação necessária**

## 🔧 Configuração Rápida

### **Opção 1: LocalTunnel (Mais Fácil)**

```bash
# 1. Iniciar o Laravel
php artisan serve

# 2. Em outro terminal, executar o túnel
./scripts/start-localtunnel.sh
```

### **Opção 2: Serveo (Sem Instalação)**

```bash
# 1. Iniciar o Laravel
php artisan serve

# 2. Em outro terminal, executar o túnel
./scripts/start-serveo.sh
```

### **Opção 3: Ngrok (Mais Profissional)**

```bash
# 1. Instalar ngrok
./scripts/setup-tunnel.sh --install ngrok

# 2. Configurar token (obter em ngrok.com)
ngrok config add-authtoken SEU_TOKEN_AQUI

# 3. Iniciar o Laravel
php artisan serve

# 4. Em outro terminal, criar túnel
./scripts/setup-tunnel.sh --tunnel ngrok
```

## 📋 Passo a Passo Detalhado

### **LocalTunnel (Recomendado)**

1. **Iniciar o Laravel:**
   ```bash
   php artisan serve
   ```

2. **Em outro terminal, executar:**
   ```bash
   ./scripts/start-localtunnel.sh
   ```

3. **Resultado esperado:**
   ```
   🚀 Iniciando LocalTunnel para Laravel
   =====================================
   ✅ Laravel detectado na porta 8000
   🌐 Iniciando túnel LocalTunnel...
   
   📱 URL pública será exibida abaixo:
   🔗 Use esta URL para configurar o webhook do Mercado Pago
   
   🚀 Usando npx localtunnel...
   your url is: https://abc123.loca.lt
   ```

4. **Usar a URL gerada:**
   - Webhook: `https://abc123.loca.lt/api/webhook/mercadopago`
   - Teste: `https://abc123.loca.lt/api/webhook-test`

### **Serveo (Sem Instalação)**

1. **Iniciar o Laravel:**
   ```bash
   php artisan serve
   ```

2. **Em outro terminal, executar:**
   ```bash
   ./scripts/start-serveo.sh
   ```

3. **Resultado esperado:**
   ```
   🚀 Iniciando Túnel Serveo para Laravel
   ======================================
   ✅ Laravel detectado na porta 8000
   🌐 Iniciando túnel Serveo...
   
   📱 URL pública será exibida abaixo:
   🔗 Use esta URL para configurar o webhook do Mercado Pago
   
   💡 Dica: O Serveo é gratuito e não requer instalação!
   
   🚀 Executando: ssh -R 80:localhost:8000 serveo.net
   ⏳ Aguarde a conexão...
   
   Forwarding HTTP traffic from https://abc123.serveo.net
   ```

## 🔗 Configurando o Webhook do Mercado Pago

### **1. Obter URL do Túnel**
Execute um dos scripts acima e anote a URL gerada.

### **2. Configurar no Mercado Pago**
- **URL do Webhook:** `https://SUA_URL.loca.lt/api/webhook/mercadopago`
- **Eventos:** `payment.updated`, `order.updated`

### **3. Testar o Webhook**
```bash
curl -X POST "https://SUA_URL.loca.lt/api/webhook-test" \
  -H "Content-Type: application/json" \
  -d '{"test": "data"}'
```

## 🛠️ Scripts Disponíveis

### **`scripts/start-localtunnel.sh`**
- Inicia LocalTunnel automaticamente
- Verifica se o Laravel está rodando
- Usa npx se disponível

### **`scripts/start-serveo.sh`**
- Inicia túnel Serveo via SSH
- Não requer instalação
- URLs personalizáveis

### **`scripts/setup-tunnel.sh`**
- Script completo para Ngrok/Cloudflare
- Instalação automática
- Gerenciamento de túneis

## 🚨 Solução de Problemas

### **Erro: "Laravel não está rodando na porta 8000"**
```bash
# Iniciar o Laravel primeiro
php artisan serve

# Ou em outra porta
php artisan serve --port=8080
# Depois ajustar o script para a porta correta
```

### **Erro: "Permissão negada"**
```bash
# Tornar scripts executáveis
chmod +x scripts/*.sh
```

### **Erro: "npx não encontrado"**
```bash
# Instalar Node.js
curl -fsSL https://deb.nodesource.com/setup_lts.x | sudo -E bash -
sudo apt-get install -y nodejs

# Ou usar o script de instalação
./scripts/setup-tunnel.sh --install localtunnel
```

### **Túnel não conecta**
1. Verificar se o Laravel está rodando
2. Verificar firewall/antivírus
3. Tentar outra porta
4. Usar outro serviço de túnel

## 📊 Comparação dos Serviços

| Serviço | Instalação | Gratuito | Estabilidade | Configuração |
|---------|------------|----------|--------------|--------------|
| **LocalTunnel** | ❌ (npx) | ✅ | ⭐⭐⭐ | ⭐⭐⭐⭐⭐ |
| **Serveo** | ❌ | ✅ | ⭐⭐⭐⭐ | ⭐⭐⭐⭐⭐ |
| **Ngrok** | ✅ | ✅ (limitado) | ⭐⭐⭐⭐⭐ | ⭐⭐⭐ |
| **Cloudflare** | ✅ | ✅ | ⭐⭐⭐⭐⭐ | ⭐⭐ |

## 🎯 Recomendação

### **Para Testes Rápidos:**
- Use **LocalTunnel** ou **Serveo**
- Não requer instalação
- Funciona imediatamente

### **Para Produção/Desenvolvimento:**
- Use **Ngrok** ou **Cloudflare**
- Mais estável e confiável
- Melhor para uso contínuo

## 🔄 Atualizações

Para manter os scripts atualizados:
```bash
# Verificar status
./scripts/setup-tunnel.sh --status

# Atualizar se necessário
git pull origin main
```

## 📞 Suporte

Se encontrar problemas:
1. Verifique se o Laravel está rodando
2. Teste com outro serviço de túnel
3. Verifique logs do Laravel
4. Teste em modo incógnito do navegador
