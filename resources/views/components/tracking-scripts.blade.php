@php
    $trackingSettings = \App\Models\SystemSetting::getTrackingSettings();
    $pageTitle = isset($title) ? $title : 'EJA Supletivo';
    $pageType = isset($pageType) ? $pageType : 'page';
    
    // Gerar um ID de sessão único se não existir
    if (!session()->has('visitor_id')) {
        session(['visitor_id' => uniqid('visitor_', true)]);
    }
    $visitorId = session('visitor_id');
    
    // Informações da página para dataLayer
    $dataLayer = [
        'pageTitle' => $pageTitle,
        'pageType' => $pageType,
        'visitorId' => $visitorId,
        'userLoggedIn' => auth()->check(),
        'timestamp' => now()->timestamp
    ];
    
    // Adicionar informações do usuário se estiver logado
    if (auth()->check()) {
        $dataLayer['userId'] = auth()->id();
        $dataLayer['userType'] = session('admin_tipo') ?? 'user';
    }
@endphp

{{-- Tratamento global de erros --}}
<script>
    // Configuração para suprimir erros de extensões
    (function() {
        'use strict';
        
        // Lista expandida de padrões de erro de extensões
        const extensionErrorPatterns = [
            // Spoofer específico
            'spoofer.js',
            'spoofer',
            
            // Extensões gerais
            'extension installed',
            'content_script',
            'content-script',
            'injected script',
            
            // Erros comuns de extensões
            'Non-Error promise rejection captured',
            'ResizeObserver loop limit exceeded',
            'Script error.',
            'SecurityError',
            'Permission denied',
            
            // URLs de extensões
            'chrome-extension://',
            'moz-extension://',
            'safari-extension://',
            'edge-extension://',
            'extension://',
            
            // Bloqueadores específicos
            'ublock',
            'adblock',
            'ghostery',
            'disconnect',
            'privacy badger',
            'duckduckgo',
            'black & white web',
            'black and white web',
            'contentScript.js',
            
            // Outros padrões conhecidos
            'webkit-masked-url',
            'anonymous function',
            'eval code',
            'anonymous',
            '<anonymous>',
            'at Object.eval',
            'at eval',
            'unexpected end of input',
            'syntaxerror'
        ];
        
        // Função para verificar se um erro é de extensão
        function isExtensionError(message, source, filename) {
            const textToCheck = [
                message || '',
                source || '',
                filename || ''
            ].join(' ').toLowerCase();
            
            // Verificar padrões específicos primeiro
            if (textToCheck.includes('black & white web') || 
                textToCheck.includes('black and white web') ||
                textToCheck.includes('contentScript.js')) {
                return true;
            }
            
            // Verificar se é um erro de sintaxe genérico de extensão
            if (textToCheck.includes('unexpected end of input') && 
                (textToCheck.includes('contentScript') || textToCheck.includes('extension'))) {
                return true;
            }
            
            return extensionErrorPatterns.some(pattern => 
                textToCheck.includes(pattern.toLowerCase())
            );
        }
        
        // Substituir o console.error original para interceptar erros
        const originalConsoleError = console.error;
        const originalConsoleWarn = console.warn;
        const originalConsoleLog = console.log;
        
        console.error = function(...args) {
            const errorText = args.join(' ');
            if (isExtensionError(errorText, '', '')) {
                return; // Suprimir erros de extensões
            }
            // Remover todos os logs em produção
            if (window.location.hostname !== 'localhost' && 
                !window.location.hostname.includes('127.0.0.1') &&
                !window.location.hostname.includes('dev.')) {
                return;
            }
            originalConsoleError.apply(console, args);
        };
        
        console.warn = function(...args) {
            const errorText = args.join(' ');
            if (isExtensionError(errorText, '', '')) {
                return; // Suprimir warnings de extensões
            }
            // Remover todos os logs em produção
            if (window.location.hostname !== 'localhost' && 
                !window.location.hostname.includes('127.0.0.1') &&
                !window.location.hostname.includes('dev.')) {
                return;
            }
            originalConsoleWarn.apply(console, args);
        };
        
        console.log = function(...args) {
            const errorText = args.join(' ');
            if (isExtensionError(errorText, '', '')) {
                return; // Suprimir logs de extensões
            }
            // Remover todos os logs em produção
            if (window.location.hostname !== 'localhost' && 
                !window.location.hostname.includes('127.0.0.1') &&
                !window.location.hostname.includes('dev.')) {
                return;
            }
            originalConsoleLog.apply(console, args);
        };
        
        // Handler de erro global mais robusto
        window.addEventListener('error', function(event) {
            const errorMessage = event.message || '';
            const errorSource = event.filename || '';
            const errorStack = event.error?.stack || '';
            
            if (isExtensionError(errorMessage, errorSource, errorStack)) {
                event.preventDefault();
                event.stopPropagation();
                event.stopImmediatePropagation();
                return false;
            }
        }, true);
        
        // Handler de promises rejeitadas mais robusto
        window.addEventListener('unhandledrejection', function(event) {
            const reason = event.reason;
            const reasonStr = typeof reason === 'string' ? reason : 
                             (reason?.message || reason?.toString() || JSON.stringify(reason));
            
            if (isExtensionError(reasonStr, '', '')) {
                event.preventDefault();
                event.stopPropagation();
                event.stopImmediatePropagation();
                return;
            }
        }, true);
        
        // Interceptar erros de imagens, scripts e outros recursos
        document.addEventListener('error', function(event) {
            if (event.target && event.target !== window) {
                const source = event.target.src || event.target.href || '';
                if (isExtensionError('', source, '')) {
                    event.preventDefault();
                    event.stopPropagation();
                    return false;
                }
            }
        }, true);
        
        // Proteção adicional contra erros de MutationObserver (comum em extensões)
        const originalMutationObserver = window.MutationObserver;
        if (originalMutationObserver) {
            window.MutationObserver = function(callback) {
                const wrappedCallback = function(mutations, observer) {
                    try {
                        return callback.call(this, mutations, observer);
                    } catch (error) {
                        const errorStr = error?.message || error?.toString() || '';
                        if (isExtensionError(errorStr, '', '')) {
                            return; // Suprimir erro de extensão
                        }
                        throw error;
                    }
                };
                return new originalMutationObserver(wrappedCallback);
            };
            window.MutationObserver.prototype = originalMutationObserver.prototype;
        }
        
    })();
</script>

{{-- Inicialização do dataLayer --}}
<script>
    // Verificar se window e document estão disponíveis
    if (typeof window !== 'undefined' && typeof document !== 'undefined') {
        try {
            window.dataLayer = window.dataLayer || [];
            dataLayer.push({!! json_encode($dataLayer) !!});
            
            // Função auxiliar para envio de eventos com tratamento de erro
            window.sendTrackingEvent = function(eventName, eventParams) {
                try {
                    // Enviar para o GTM/GA4
                    if (typeof dataLayer !== 'undefined') {
                        dataLayer.push({
                            'event': eventName,
                            ...eventParams
                        });
                    }
                    
                    // Enviar para o Facebook Pixel
                    if (typeof fbq !== 'undefined') {
                        fbq('trackCustom', eventName, eventParams);
                    }
                } catch (error) {
                    // Não fazer nada - suprimir todos os erros
                }
            };
            
            // Detectar bloqueadores de rastreamento com tratamento de erro
            window.checkTrackingBlockers = function() {
                try {
                    let trackingBlocked = false;
                    
                    // Verificar se o script do GTM foi carregado
                    if (typeof window.google_tag_manager === 'undefined' && 
                        document.querySelector('script[src*="googletagmanager.com/gtm.js"]')) {
                        trackingBlocked = true;
                    }
                    
                    // Verificar se o Facebook Pixel foi carregado
                    if (typeof fbq === 'undefined' && 
                        document.querySelector('script[src*="connect.facebook.net/en_US/fbevents.js"]')) {
                        trackingBlocked = true;
                    }
                    
                    return trackingBlocked;
                } catch (error) {
                    return false;
                }
            };
            
            // Verificar bloqueadores após um tempo com tratamento de erro
            setTimeout(function() {
                try {
                    window.checkTrackingBlockers();
                } catch (error) {
                    // Suprimir erro
                }
            }, 2000);
            
        } catch (error) {
            // Suprimir erro na inicialização
        }
    }
</script>

{{-- Google Tag Manager --}}
@if($trackingSettings['enable_google_analytics'] && !empty($trackingSettings['google_tag_manager_id']))
    <!-- Google Tag Manager -->
    <script>
    (function(w,d,s,l,i){
        try {
            w[l]=w[l]||[];
            w[l].push({'gtm.start': new Date().getTime(), event:'gtm.js'});
            var f=d.getElementsByTagName(s)[0],
                j=d.createElement(s),
                dl=l!='dataLayer'?'&l='+l:'';
            j.async=true;
            j.src='https://www.googletagmanager.com/gtm.js?id='+i+dl;
            j.onerror = function() {
                // Criar um objeto simulado para evitar erros
                w.google_tag_manager = w.google_tag_manager || {};
                w.google_tag_manager[i] = w.google_tag_manager[i] || {
                    dataLayer: w.dataLayer,
                    push: function(obj) { w.dataLayer.push(obj); }
                };
            };
            f.parentNode.insertBefore(j,f);
            
            // Verificar se o script foi carregado corretamente
            setTimeout(function() {
                if (typeof w.google_tag_manager === 'undefined') {
                    // Script não carregado - modo silencioso
                }
            }, 2000);
        } catch(e) {
            // Suprimir erro na inicialização do GTM
        }
    })(window,document,'script','dataLayer','{{ $trackingSettings['google_tag_manager_id'] }}');
    </script>
    <!-- End Google Tag Manager -->
@else
    <!-- Google Tag Manager desativado -->
    <script>
        // Criar um dataLayer simulado para testes
        window.dataLayer = window.dataLayer || [];
        window.dataLayer.push = function(obj) {
            Array.prototype.push.call(this, obj);
        };
    </script>
@endif

{{-- Facebook Pixel --}}
@if($trackingSettings['enable_facebook_pixel'] && !empty($trackingSettings['facebook_pixel_id']))
    <!-- Facebook Pixel Code -->
    <script>
    try {
        !function(f,b,e,v,n,t,s) {
            if(f.fbq)return;
            n=f.fbq=function(){
                n.callMethod ? n.callMethod.apply(n,arguments) : n.queue.push(arguments)
            };
            if(!f._fbq)f._fbq=n;
            n.push=n;
            n.loaded=!0;
            n.version='2.0';
            n.queue=[];
            t=b.createElement(e);
            t.async=!0;
            t.src=v;
            t.onerror = function() {
                // Criar uma função simulada para evitar erros
                f.fbq = function() {
                    // Função silenciosa
                };
                f.fbq.loaded = true;
                f.fbq.version = '2.0 (simulado)';
                f.fbq.queue = [];
            };
            s=b.getElementsByTagName(e)[0];
            s.parentNode.insertBefore(t,s);
        }(window, document,'script','https://connect.facebook.net/en_US/fbevents.js');
        
        // Inicializar o pixel com configurações avançadas
        fbq('init', '{{ $trackingSettings['facebook_pixel_id'] }}', {
            external_id: '{{ $visitorId }}'
        });
        
        // Evento de visualização de página
        fbq('track', 'PageView', {
            page_title: '{{ $pageTitle }}',
            page_type: '{{ $pageType }}'
        });
        
        // Detectar formulários na página e adicionar rastreamento automático
        document.addEventListener('DOMContentLoaded', function() {
            const forms = document.querySelectorAll('form');
            forms.forEach(function(form) {
                form.addEventListener('submit', function() {
                    const formId = form.id || 'unknown_form';
                    fbq('track', 'Lead', {
                        form_id: formId,
                        content_name: '{{ $pageTitle }}'
                    });
                });
            });
        });
    } catch(e) {
        // Suprimir erro na inicialização do Facebook Pixel
    }
    </script>
    <noscript>
        <img height="1" width="1" style="display:none" 
             src="https://www.facebook.com/tr?id={{ $trackingSettings['facebook_pixel_id'] }}&ev=PageView&noscript=1"/>
    </noscript>
    <!-- End Facebook Pixel Code -->
@else
    <!-- Facebook Pixel desativado -->
    <script>
        // Criar uma função simulada para testes
        window.fbq = function() {
            // Função silenciosa
        };
        window.fbq.loaded = true;
        window.fbq.version = '2.0 (simulado)';
        window.fbq.queue = [];
    </script>
@endif 