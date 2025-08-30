// Registra o componente de máscara
new Vue({
    el: '#app',
    data: {
        formData: {
            nome: '',
            email: '',
            telefone: '',
            curso: '',
            termos: false
        },
        mensagemEnvio: '',
        statusEnvio: false,
        activeTab: 'linguagens', // Para a seção de conteúdo do curso
        phoneInput: null, // Referência para o objeto intlTelInput
        phoneInputMask: null, // Referência para o objeto Cleave
        enviando: false // Flag para controlar o estado de envio
    },
    mounted() {
        // Carregar Axios via CDN se não estiver disponível
        if (!window.axios) {
            const script = document.createElement('script');
            script.src = 'https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js';
            script.async = true;
            document.head.appendChild(script);
        }
        
        // Inicializar o seletor de país com bandeira para o telefone
        this.phoneInput = window.intlTelInput(this.$refs.phoneInput, {
            utilsScript: "https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.8/js/utils.js",
            preferredCountries: ['br', 'pt', 'us'],
            initialCountry: 'br',
            separateDialCode: true,
            autoPlaceholder: 'aggressive'
        });
        
        // Inicializar máscara para o telefone
        new Cleave(this.$refs.phoneInput, {
            phone: true,
            phoneRegionCode: 'BR'
        });
    },
    methods: {
        scrollToSection(sectionId) {
            const element = document.getElementById(sectionId);
            if (element) {
                element.scrollIntoView({ behavior: 'smooth' });
            }
        },
        enviarFormulario() {
            // Validação do formulário
            if (!this.formData.nome || !this.formData.email || !this.$refs.phoneInput.value || !this.formData.curso || !this.formData.termos) {
                this.mensagemEnvio = 'Por favor, preencha todos os campos e aceite os termos.';
                this.statusEnvio = false;
                return;
            }
            
            // Validação do email
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(this.formData.email)) {
                this.mensagemEnvio = 'Por favor, insira um email válido.';
                this.statusEnvio = false;
                return;
            }
            
            // Validação do telefone
            if (!this.phoneInput.isValidNumber()) {
                this.mensagemEnvio = 'Por favor, insira um número de telefone válido.';
                this.statusEnvio = false;
                return;
            }
            
            // Capturar o número de telefone completo com código do país
            const numeroTelefone = this.phoneInput.getNumber();
            
            // Evitar múltiplos envios
            if (this.enviando) return;
            this.enviando = true;
            
            // Exibir mensagem de processamento
            this.mensagemEnvio = 'Fazendo sua inscrição...';
            this.statusEnvio = true;
            
            // Preparar os dados para envio
            const dadosFormulario = {
                nome: this.formData.nome,
                email: this.formData.email,
                telefone: numeroTelefone,
                curso: this.formData.curso,
                termos: this.formData.termos ? 'Aceito' : 'Não aceito'
            };
            
            // ESCOLHA UMA DAS OPÇÕES ABAIXO E COMENTE AS OUTRAS
            
            // OPÇÃO 1: EmailJS com SMTP
            const serviceId = 'service_3x2jlwc'; // Substitua pelo seu Service ID do EmailJS
            const templateId = 'template_9nbrras'; // Substitua pelo seu Template ID
            const userId = 'c1I8zxgiJocY-8hqL'; // Substitua pelo seu User ID (Public Key)
            
            // Verificar se os IDs do EmailJS foram configurados
            if (serviceId === 'service_3x2jlwc' || templateId === 'seu_template_id' || userId === 'seu_user_id') {
                this.mensagemEnvio = 'Erro: EmailJS não configurado. Verifique o service_id, template_id e user_id no código.';
                this.statusEnvio = false;
                this.enviando = false;
                return;
            }
            
            const urlEnvio = 'https://api.emailjs.com/api/v1.0/email/send';
            const configEnvio = {
                service_id: serviceId,
                template_id: templateId,
                user_id: userId,
                template_params: {
                    to_email: 'douglaseps@gmail.com', // Email onde receberá as inscrições
                    to_name: 'Equipe EJA',
                    from_name: this.formData.nome,
                    from_email: this.formData.email,
                    phone: numeroTelefone,
                    curso: this.formData.curso,
                    termos: this.formData.termos ? 'Aceito' : 'Não aceito',
                    message: `
                        Nova inscrição recebida:
                        
                        Nome: ${this.formData.nome}
                        Email: ${this.formData.email}
                        Telefone: ${numeroTelefone}
                        Curso de interesse: ${this.formData.curso}
                        Aceita termos: ${this.formData.termos ? 'Sim' : 'Não'}
                        
                        Data/Hora: ${new Date().toLocaleString('pt-BR')}
                        Página: ${window.location.href}
                    `
                }
            };
            
            /*
            // OPÇÃO 2: Formspree (DESABILITADO)
            const urlEnvio = 'https://formspree.io/f/xdknkqko';
            const configEnvio = {
                ...dadosFormulario,
                _subject: 'Nova inscrição EJA',
                _format: 'plain'
            };
            */
            
            /*
            // OPÇÃO 3: HubSpot via backend próprio
            const urlEnvio = 'https://seudominio.com/api/hubspot-webhook';
            const configEnvio = {
                portalId: '4719984',
                formId: 'ab669972-5501-4f11-b157-3be2431ffa29',
                dados: dadosFormulario
            };
            */
            
            // Configuração para EmailJS
            const axiosConfig = {
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                }
            };
            
            // Enviar o formulário usando Axios
            axios.post(urlEnvio, configEnvio, axiosConfig)
                .then(response => {
                    // Sucesso no envio
                    this.mensagemEnvio = 'Inscrição realizada com sucesso! Entraremos em contato em breve.';
                    this.statusEnvio = true;
                    
                    // Limpar o formulário após o envio
                    this.formData.nome = '';
                    this.formData.email = '';
                    this.$refs.phoneInput.value = '';
                    this.formData.curso = '';
                    this.formData.termos = false;
                    this.phoneInput.setCountry('br');
                    
                    // Redirecionar para a página de agradecimento após um pequeno delay
                    setTimeout(() => {
                        window.location.href = 'obrigado.html';
                    }, 2000);
                })
                .catch(error => {
                    console.error('Erro ao enviar o formulário:', error);
                    
                    // Tratamento específico de erros
                    if (error.response) {
                        // Erro de resposta do servidor
                        const status = error.response.status;
                        const data = error.response.data;
                        
                        console.error('Status:', status);
                        console.error('Dados da resposta:', data);
                        
                        if (status === 400) {
                            this.mensagemEnvio = 'Erro 400: Configuração inválida. Verifique se o service_id, template_id e user_id estão corretos, e se o template foi criado no EmailJS.';
                        } else if (status === 401) {
                            this.mensagemEnvio = 'Erro 401: User ID inválido. Verifique seu Public Key no EmailJS.';
                        } else if (status === 403) {
                            this.mensagemEnvio = 'Erro 403: Acesso negado. Verifique as configurações de segurança no EmailJS.';
                        } else if (status === 404) {
                            this.mensagemEnvio = 'Erro 404: Service ID ou Template ID não encontrados.';
                        } else {
                            this.mensagemEnvio = `Erro ${status}: ${data.message || 'Erro desconhecido no servidor'}`;
                        }
                    } else if (error.request) {
                        // Erro de rede
                        this.mensagemEnvio = 'Erro de conexão. Verifique sua internet e tente novamente.';
                    } else {
                        // Outro tipo de erro
                        this.mensagemEnvio = `Erro inesperado: ${error.message}`;
                    }
                    
                    this.statusEnvio = false;
                    this.enviando = false;
                });
        }
    }
}); 