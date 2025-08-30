<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

<p align="center">
<a href="https://github.com/laravel/framework/actions"><img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

## About Laravel

Laravel is a web application framework with expressive, elegant syntax. We believe development must be an enjoyable and creative experience to be truly fulfilling. Laravel takes the pain out of development by easing common tasks used in many web projects, such as:

- [Simple, fast routing engine](https://laravel.com/docs/routing).
- [Powerful dependency injection container](https://laravel.com/docs/container).
- Multiple back-ends for [session](https://laravel.com/docs/session) and [cache](https://laravel.com/docs/cache) storage.
- Expressive, intuitive [database ORM](https://laravel.com/docs/eloquent).
- Database agnostic [schema migrations](https://laravel.com/docs/migrations).
- [Robust background job processing](https://laravel.com/docs/queues).
- [Real-time event broadcasting](https://laravel.com/docs/broadcasting).

Laravel is accessible, powerful, and provides tools required for large, robust applications.

## Learning Laravel

Laravel has the most extensive and thorough [documentation](https://laravel.com/docs) and video tutorial library of all modern web application frameworks, making it a breeze to get started with the framework.

You may also try the [Laravel Bootcamp](https://bootcamp.laravel.com), where you will be guided through building a modern Laravel application from scratch.

If you don't feel like reading, [Laracasts](https://laracasts.com) can help. Laracasts contains thousands of video tutorials on a range of topics including Laravel, modern PHP, unit testing, and JavaScript. Boost your skills by digging into our comprehensive video library.

## Laravel Sponsors

We would like to extend our thanks to the following sponsors for funding Laravel development. If you are interested in becoming a sponsor, please visit the [Laravel Partners program](https://partners.laravel.com).

### Premium Partners

- **[Vehikl](https://vehikl.com)**
- **[Tighten Co.](https://tighten.co)**
- **[Kirschbaum Development Group](https://kirschbaumdevelopment.com)**
- **[64 Robots](https://64robots.com)**
- **[Curotec](https://www.curotec.com/services/technologies/laravel)**
- **[DevSquad](https://devsquad.com/hire-laravel-developers)**
- **[Redberry](https://redberry.international/laravel-development)**
- **[Active Logic](https://activelogic.com)**

## Contributing

Thank you for considering contributing to the Laravel framework! The contribution guide can be found in the [Laravel documentation](https://laravel.com/docs/contributions).

## Code of Conduct

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
# 🎓 EJA Supletivo - Landing Page

Sistema completo de landing page para captação de leads educacionais com painel administrativo avançado e sistema de tracking integrado.

## 🚀 Funcionalidades Principais

### 📊 **Sistema de Configurações Avançado**
- **Interface organizada por abas** (Leads, Notificações, Tracking, Geral)
- **Google Tag Manager** - Configuração automática de analytics
- **Facebook Pixel** - Remarketing e conversões
- **Gerenciamento de Leads** - Cooldown, limites e destravamento automático
- **Design moderno** com CSS personalizado e animações

### 🎯 **Painel Administrativo**
- Dashboard com métricas em tempo real
- Sistema Kanban para gerenciamento de leads
- Controle de usuários com diferentes níveis de acesso
- Monitoramento de atividades e relatórios

### 📱 **Landing Page Otimizada**
- Design responsivo e moderno
- Formulário de captação integrado
- Scripts de tracking automáticos
- Otimizada para conversão

## 📚 Documentação

### 📖 **Documentação Principal**
- [`DOCUMENTACAO-CONFIGURACOES.md`](DOCUMENTACAO-CONFIGURACOES.md) - Guia completo das funcionalidades
- [`EXEMPLOS-CONFIGURACAO.md`](EXEMPLOS-CONFIGURACAO.md) - Exemplos práticos de uso

### 🔧 **Configuração Rápida**

1. **Instalação:**
```bash
composer install
npm install
cp .env.example .env
php artisan key:generate
php artisan migrate
```

2. **Configurar Google Drive:**
```bash
# Gerar refresh token do Google Drive
php artisan google-drive:generate-refresh-token

# Validar configuração
php artisan google-drive:validate-config
```

3. **Configurar Tracking:**
- Acesse `/admin/configuracoes`
- Aba "Tracking & Analytics"
- Configure GTM ID: `GTM-XXXXXXX`
- Configure Facebook Pixel ID: `123456789`
- Ative os switches necessários

### 🎨 **Tecnologias Utilizadas**
- **Backend:** Laravel 11
- **Frontend:** Bootstrap 5, Font Awesome
- **Database:** MySQL/PostgreSQL
- **Tracking:** Google Tag Manager, Facebook Pixel
- **Charts:** Chart.js

## 🔐 Acesso ao Sistema

### **Usuários do Sistema:**
- **Admin:** Acesso completo às configurações
- **Vendedor:** Gerenciamento de leads
- **Colaborador:** Visualização limitada
- **Mídia:** Foco em campanhas

### **URLs Importantes:**
- Landing Page: `/`
- Admin Dashboard: `/admin/dashboard`
- Configurações: `/admin/configuracoes`
- Kanban: `/admin/kanban`

## 🛠 Configurações de Tracking

### **Google Tag Manager**
```bash
# Configuração no painel admin
GTM ID: GTM-ABC123D
Status: ✅ Ativo
```

### **Facebook Pixel**
```bash
# Configuração no painel admin
Pixel ID: 123456789012345
Status: ✅ Ativo
```

## 📈 Benefícios

### **Para Administradores:**
- ✅ Interface intuitiva sem necessidade de código
- ✅ Configuração centralizada de tracking
- ✅ Relatórios em tempo real
- ✅ Controle completo do sistema

### **Para Marketing:**
- ✅ Tracking automático implementado
- ✅ Remarketing via Facebook Pixel
- ✅ Analytics detalhado via GTM
- ✅ Otimização de campanhas

### **Para Desenvolvedores:**
- ✅ Código limpo e organizado
- ✅ Componentes reutilizáveis
- ✅ Fácil manutenção e expansão
- ✅ Documentação completa

## 🆘 Suporte

Para dúvidas sobre configuração ou uso:
1. Consulte a documentação em `DOCUMENTACAO-CONFIGURACOES.md`
2. Veja exemplos práticos em `EXEMPLOS-CONFIGURACAO.md`
3. Verifique os logs em `storage/logs/laravel.log`

---

**Versão:** 1.0  
**Última atualização:** Janeiro 2025
