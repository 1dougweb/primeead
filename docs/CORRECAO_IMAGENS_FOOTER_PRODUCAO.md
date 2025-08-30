# 🔧 Correção das Imagens do Footer em Produção

## 🚨 Problema
As imagens do footer não são exibidas quando o site está em produção.

## 🔍 Causas Comuns

### 1. **Symlink não existe ou quebrado**
```bash
# Verificar se o symlink existe
ls -la public/storage

# Se não existir ou estiver quebrado, recriar:
rm -rf public/storage
php artisan storage:link
```

### 2. **Permissões incorretas**
```bash
# Ajustar permissões das pastas
chmod -R 755 storage/app/public/
chmod -R 755 public/storage/

# Ajustar permissões dos arquivos
find storage/app/public/ -type f -exec chmod 644 {} \;
find public/storage/ -type f -exec chmod 644 {} \;

# Ajustar proprietário (substitua www-data pelo usuário do seu servidor)
chown -R www-data:www-data storage/app/public/
chown -R www-data:www-data public/storage/
```

### 3. **Cache desatualizado**
```bash
# Limpar todos os caches
php artisan config:clear
php artisan cache:clear
php artisan view:clear
php artisan route:clear

# Recriar caches
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### 4. **Configuração do .env**
Verificar se o `APP_URL` está correto no arquivo `.env`:
```env
APP_URL=https://seudominio.com
```

### 5. **Configuração do servidor web**
Para **Apache**, adicionar no `.htaccess`:
```apache
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteRule ^storage/(.*)$ storage/app/public/$1 [L]
</IfModule>
```

Para **Nginx**, adicionar no `nginx.conf`:
```nginx
location /storage {
    alias /path/to/your/project/storage/app/public;
    try_files $uri $uri/ =404;
}
```

## 🛠️ Script de Correção Automática

Execute o script criado:
```bash
chmod +x scripts/fix-storage-production.sh
./scripts/fix-storage-production.sh
```

## 🔍 Comando de Verificação

Use o comando criado para verificar o status:
```bash
php artisan storage:check
```

## 📋 Checklist de Verificação

- [ ] Symlink existe e aponta corretamente
- [ ] Permissões das pastas (755)
- [ ] Permissões dos arquivos (644)
- [ ] Proprietário correto (www-data ou usuário do servidor)
- [ ] APP_URL configurado corretamente
- [ ] Caches limpos e recriados
- [ ] Configuração do servidor web

## 🌐 Teste Final

1. Acesse: `https://seudominio.com/storage/footer/`
2. Verifique se as imagens carregam diretamente
3. Teste no footer do site
4. Verifique no console do navegador se há erros 404

## 🚀 Comandos Rápidos para Produção

```bash
# 1. Conectar via SSH ao servidor
ssh usuario@seudominio.com

# 2. Navegar para o projeto
cd /path/to/project

# 3. Executar correções
rm -rf public/storage
php artisan storage:link
chmod -R 755 storage/app/public/
chmod -R 755 public/storage/
find storage/app/public/ -type f -exec chmod 644 {} \;
find public/storage/ -type f -exec chmod 644 {} \;
chown -R www-data:www-data storage/app/public/
chown -R www-data:www-data public/storage/
php artisan config:clear
php artisan cache:clear
php artisan view:clear
php artisan config:cache

# 4. Verificar
php artisan storage:check
```

## 📞 Suporte

Se o problema persistir, verifique:
1. Logs do servidor web (`/var/log/apache2/error.log` ou `/var/log/nginx/error.log`)
2. Logs do Laravel (`storage/logs/laravel.log`)
3. Console do navegador para erros JavaScript
4. Network tab do DevTools para requisições falhadas
