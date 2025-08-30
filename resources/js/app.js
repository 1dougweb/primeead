import './bootstrap';

import Alpine from 'alpinejs';

window.Alpine = Alpine;

Alpine.start();

/**
 * Biblioteca de rastreamento personalizada para o site
 * Facilita o envio de eventos para Google Analytics e Facebook Pixel
 */
const TrackingManager = {
    /**
     * Inicializa a biblioteca de rastreamento
     */
    init() {
        this.setupEventListeners();
        this.loadSettings();
    },

    /**
     * Detecta bloqueadores de rastreamento e cria fallbacks
     */
    detectBlockers() {
        setTimeout(() => {
            // Verificar se o GTM foi bloqueado
            if (typeof window.google_tag_manager === 'undefined' && 
                document.querySelector('script[src*="googletagmanager.com/gtm.js"]')) {
                console.warn('Google Tag Manager parece estar bloqueado. Usando fallback local.');
                this.setupGTMFallback();
            }
            
            // Verificar se o Facebook Pixel foi bloqueado
            if (typeof window.fbq === 'undefined' && 
                document.querySelector('script[src*="connect.facebook.net/en_US/fbevents.js"]')) {
                console.warn('Facebook Pixel parece estar bloqueado. Usando fallback local.');
                this.setupPixelFallback();
            }
        }, 2000);
    },
    
    /**
     * Configura um fallback local para o GTM
     */
    setupGTMFallback() {
        // Criar um dataLayer simulado que apenas registra eventos
        window.dataLayer = window.dataLayer || [];
        const originalPush = window.dataLayer.push;
        window.dataLayer.push = function(obj) {
            if (!window.dataLayer) return;
            window.dataLayer.push(obj);
        };
        
        // Criar um objeto google_tag_manager simulado
        window.google_tag_manager = window.google_tag_manager || {
            dataLayer: window.dataLayer
        };
    },
    
    /**
     * Configura um fallback local para o Facebook Pixel
     */
    setupPixelFallback() {
        // Criar uma função fbq simulada
        window.fbq = function(method, eventName, params) {
            if (!window.fbq) return;
            window.fbq(method, eventName, params);
        };
        window.fbq.loaded = true;
        window.fbq.version = '2.0 (fallback)';
        window.fbq.queue = [];
    },

    /**
     * Envia um evento para todas as plataformas de analytics configuradas
     * @param {string} eventName - Nome do evento
     * @param {object} eventParams - Parâmetros do evento
     */
    sendEvent(eventName, eventParams = {}) {
        // Adicionar timestamp ao evento
        const params = {
            ...eventParams,
            timestamp: new Date().toISOString()
        };

        this.gtmFallback({
            event: eventName,
            ...params
        });

        // Enviar para o Facebook Pixel
        if (window.fbq) {
            // Eventos padrão do Facebook têm nomes específicos
            const standardEvents = [
                'PageView', 'Lead', 'CompleteRegistration', 'Contact', 
                'Schedule', 'StartTrial', 'Subscribe', 'ViewContent'
            ];
            
            if (standardEvents.includes(eventName)) {
                window.fbq('track', eventName, params);
            } else {
                window.fbq('trackCustom', eventName, params);
            }
        }
    },

    /**
     * Rastreia visualização de página
     */
    trackPageView() {
        const pageTitle = document.title;
        const pageUrl = window.location.href;
        const pagePathname = window.location.pathname;
        
        this.sendEvent('page_view', {
            page_title: pageTitle,
            page_url: pageUrl,
            page_path: pagePathname
        });
    },

    /**
     * Configura rastreamento automático de formulários
     */
    setupFormTracking() {
        document.querySelectorAll('form').forEach(form => {
            // Rastrear envio de formulário
            form.addEventListener('submit', (e) => {
                const formId = form.id || form.getAttribute('name') || 'unknown_form';
                const formAction = form.getAttribute('action') || '';
                
                this.sendEvent('form_submit', {
                    form_id: formId,
                    form_action: formAction
                });
                
                // Se for um formulário de inscrição, enviar evento de Lead
                if (formId.includes('inscricao') || formAction.includes('inscricao')) {
                    this.sendEvent('Lead', {
                        form_id: formId,
                        form_action: formAction
                    });
                }
            });
            
            // Rastrear interações com campos do formulário
            form.querySelectorAll('input, select, textarea').forEach(field => {
                field.addEventListener('change', () => {
                    const fieldName = field.name || field.id || 'unknown_field';
                    const fieldType = field.type || field.tagName.toLowerCase();
                    
                    this.sendEvent('form_field_interaction', {
                        form_id: form.id || form.getAttribute('name') || 'unknown_form',
                        field_name: fieldName,
                        field_type: fieldType
                    });
                });
            });
        });
    },

    /**
     * Configura rastreamento de cliques em elementos importantes
     */
    setupClickTracking() {
        // Rastrear cliques em botões CTA
        document.querySelectorAll('.btn, .cta-button, [data-track="click"]').forEach(button => {
            button.addEventListener('click', (e) => {
                const buttonText = button.innerText.trim();
                const buttonHref = button.getAttribute('href') || '';
                const buttonId = button.id || '';
                const buttonClasses = Array.from(button.classList).join(' ');
                
                this.sendEvent('button_click', {
                    button_text: buttonText,
                    button_href: buttonHref,
                    button_id: buttonId,
                    button_classes: buttonClasses
                });
            });
        });
    },

    /**
     * Rastreia engajamento do usuário na página
     */
    trackEngagement() {
        // Rastrear tempo na página
        const timeMarks = [30, 60, 120, 300]; // segundos
        const timeReached = [];
        const startTime = new Date().getTime();
        
        setInterval(() => {
            const timeSpent = Math.floor((new Date().getTime() - startTime) / 1000);
            
            timeMarks.forEach(mark => {
                if (timeSpent >= mark && !timeReached.includes(mark)) {
                    timeReached.push(mark);
                    this.sendEvent('time_on_page', {
                        seconds: mark,
                        formatted_time: this.formatTime(mark)
                    });
                }
            });
        }, 1000);
        
        // Rastrear profundidade de scroll
        const scrollMarks = [25, 50, 75, 100]; // porcentagem
        const scrollReached = [];
        
        window.addEventListener('scroll', () => {
            const scrollPercent = Math.round((window.scrollY / (document.body.offsetHeight - window.innerHeight)) * 100);
            
            scrollMarks.forEach(mark => {
                if (scrollPercent >= mark && !scrollReached.includes(mark)) {
                    scrollReached.push(mark);
                    this.sendEvent('scroll_depth', {
                        percent: mark
                    });
                }
            });
        });
    },

    /**
     * Formata tempo em segundos para formato legível
     * @param {number} seconds - Tempo em segundos
     * @returns {string} - Tempo formatado
     */
    formatTime(seconds) {
        if (seconds < 60) {
            return `${seconds} segundos`;
        }
        
        const minutes = Math.floor(seconds / 60);
        const remainingSeconds = seconds % 60;
        
        if (remainingSeconds === 0) {
            return `${minutes} minutos`;
        }
        
        return `${minutes} minutos e ${remainingSeconds} segundos`;
    }
};

// Inicializar o TrackingManager quando o DOM estiver carregado
document.addEventListener('DOMContentLoaded', () => {
    // Inicializar sempre, independentemente das configurações do navegador
    TrackingManager.init();
});
