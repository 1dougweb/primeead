<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Seja um Polo Parceiro | Ensino Certo</title>
    <meta name="description" content="Seja um polo parceiro da Ensino Certo e leve o EJA EaD autorizado pelo CEE/SP para sua cidade">
    
    <!-- CSS -->
    <link rel="stylesheet" href="{{ asset('assets/css/styles.css') }}">
    
    <!-- Font Rubik -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Rubik:ital,wght@0,300..900;1,300..900&display=swap" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Input Mask -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.16/jquery.mask.min.js"></script>
    
    <style>
        body {
            font-family: 'Rubik', sans-serif;
            margin: 0;
            padding: 0;
            background: #f8f9fa;
            line-height: 1.6;
        }
        
        .polo-page {
            min-height: 100vh;
            background: linear-gradient(135deg, #0F001A 0%, #1A0B2E 100%);
        }
        
        .hero-section {
            background: linear-gradient(135deg, #2E1065 0%, #1A0B2E 100%);
            color: white;
            padding: 80px 0 60px;
            text-align: center;
        }
        
        .hero-content h1 {
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            font-size: 3rem;
            margin-bottom: 20px;
            color: white;
            font-weight: 700;
        }
        
        .hero-content p {
            font-size: 1.3rem;
            margin-bottom: 0;
            opacity: 0.9;
            max-width: 800px;
            margin: 0 auto;
            color: white;
        }
        
        .main-content {
            background: white;
            margin-top: -30px;
            border-radius: 30px 30px 0 0;
            padding: 60px 0;
            position: relative;
            z-index: 2;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }
        
        .benefits-section {
            margin-bottom: 60px;
        }
        
        .section-title {
            font-size: 2.2rem;
            color: #0d47a1;
            margin-bottom: 30px;
            text-align: center;
            font-weight: 700;
        }
        
        .benefits-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 30px;
            margin-bottom: 40px;
        }
        
        .benefit-card {
            background: #f8f9fa;
            padding: 30px;
            border-radius: 15px;
            border-left: 5px solid #1e88e5;
            transition: all 0.3s ease;
        }
        
        .benefit-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }
        
        .benefit-card h3 {
            color: #1e88e5;
            margin-bottom: 15px;
            font-size: 1.3rem;
        }
        
        .benefit-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        
        .benefit-list li {
            padding: 8px 0;
            position: relative;
            padding-left: 25px;
            color: #333;
        }
        
        .benefit-list li::before {
            content: "✓";
            position: absolute;
            left: 0;
            color: #28a745;
            font-weight: bold;
        }
        
        .presence-section {
            background: linear-gradient(135deg, #1e88e5, #0d47a1);
            color: white;
            padding: 40px;
            border-radius: 15px;
            text-align: center;
            margin-bottom: 60px;
        }
        
        .presence-section h3 {
            font-size: 1.8rem;
            margin-bottom: 15px;
            color: white;
        }
        
        .presence-section p {
            font-size: 1.1rem;
            margin: 0;
            opacity: 0.9;
            color: white;
        }
        
        .form-section {
            background: white;
            padding: 50px 40px;
            border-radius: 15px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.1);
            border: 1px solid #e9ecef;
        }
        
        .form-title {
            font-size: 2rem;
            color: #0d47a1;
            margin-bottom: 30px;
            text-align: center;
            font-weight: 600;
        }
        
        .form-subtitle {
            text-align: center;
            color: #6c757d;
            margin-bottom: 40px;
            font-size: 1.1rem;
        }
        
        .form-step {
            margin-bottom: 40px;
            padding: 30px;
            border: 2px solid #e9ecef;
            border-radius: 15px;
            background: #f8f9fa;
        }
        
        .step-title {
            color: #0d47a1;
            font-size: 1.4rem;
            font-weight: 600;
            margin-bottom: 25px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .step-number {
            background: #1e88e5;
            color: white;
            width: 30px;
            height: 30px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 14px;
        }
        
        .form-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 25px;
            margin-bottom: 25px;
        }
        
        .form-group {
            margin-bottom: 25px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #333;
            font-size: 16px;
        }
        
        .form-group.required label::after {
            content: " *";
            color: #dc3545;
        }
        
        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 15px;
            border: 2px solid #e1e5e9;
            border-radius: 10px;
            font-size: 16px;
            transition: all 0.3s ease;
            font-family: 'Rubik', sans-serif;
            background: white;
            color: #333;
        }
        
        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            border-color: #1e88e5;
            box-shadow: 0 0 0 3px rgba(30, 136, 229, 0.15);
            outline: none;
        }
        
        .form-group textarea {
            resize: vertical;
            min-height: 120px;
        }
        
        .radio-group {
            display: flex;
            gap: 15px;
            margin-top: 10px;
        }
        
        .radio-option {
            display: flex;
            align-items: center;
            gap: 8px;
            cursor: pointer;
        }
        
        .radio-option input[type="radio"] {
            width: auto;
            margin: 0;
        }
        
        .radio-option label {
            margin: 0;
            cursor: pointer;
            color: #333;
        }
        
        .site-url-group {
            margin-top: 15px;
            display: none;
        }
        
        .btn-submit {
            background: linear-gradient(135deg, #1e88e5, #0d47a1);
            color: white;
            padding: 18px 50px;
            border: none;
            border-radius: 10px;
            font-size: 18px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            width: 100%;
            margin-top: 30px;
        }
        
        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 30px rgba(30, 136, 229, 0.3);
        }
        
        .alert {
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 25px;
        }
        
        .alert-danger {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        .back-link {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            color: #1e88e5;
            text-decoration: none;
            margin-bottom: 30px;
            font-weight: 500;
            font-size: 16px;
        }
        
        .back-link:hover {
            color: #0d47a1;
        }
        
        .loading-cep {
            display: none;
            color: #1e88e5;
            font-size: 14px;
            margin-top: 5px;
        }
        
        @media (max-width: 768px) {
            .hero-content h1 {
                font-size: 2.2rem;
            }
            
            .hero-content p {
                font-size: 1.1rem;
            }
            
            .benefits-grid {
                grid-template-columns: 1fr;
                gap: 20px;
            }
            
            .form-section {
                padding: 30px 20px;
            }
            
            .form-row {
                grid-template-columns: 1fr;
                gap: 20px;
            }
            
            .radio-group {
                flex-direction: column;
                gap: 10px;
            }
            
            .form-step {
                padding: 20px;
            }
        }

        .footer-badges .badge-item {
            display: flex;
            align-items: center;
            gap: 8px;
            color: #333;
            font-size: 14px;
        }

        .footer-badges .mec-address {
            margin-top: 8px;
            display: flex;
            align-items: flex-start;
            gap: 8px;
            color: #666;
            font-size: 13px;
            padding-left: 24px;
        }

        .footer-badges .mec-address i {
            color: #999;
            font-size: 14px;
            margin-top: 2px;
        }

        .footer-badges .mec-address span {
            line-height: 1.4;
        }
    </style>
    <!-- Google Tag Manager -->
    <script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
    new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
    j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
    'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
    })(window,document,'script','dataLayer','GTM-NPXJKW38');</script>
    <!-- End Google Tag Manager -->
</head>
<body>
    <!-- Google Tag Manager (noscript) -->
    <noscript><iframe src="https://www.googletagmanager.com/ns.html?id=GTM-NPXJKW38"
    height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
    <!-- End Google Tag Manager (noscript) -->
    <div class="polo-page">
        <!-- Hero Section -->
        <section class="hero-section">
            <div class="container ">
                <div class="hero-content cadastro-parceiros">
                    <h1><center>🎓 Seja um Polo Parceiro da Ensino Certo</center></h1>
                    <p>Leve o EJA EaD autorizado pelo CEE/SP para sua cidade e transforme vidas com educação de qualidade!</p>
                </div>
            </div>
        </section>
        
        <!-- Main Content -->
        <div class="main-content">
            <div class="container">
                <!-- Benefits Section -->
                <section class="benefits-section">
                    <h2 class="section-title">🌟 Benefícios da parceria</h2>
                    
                    <div class="benefits-grid">
                                              
                        <div class="benefit-card">
                            <h3><i class="fas fa-laptop"></i> Plataforma Exclusiva</h3>
                            <ul class="benefit-list">
                                <li>Conteúdo baseado no BNCC</li>
                                <li>Novo Ensino Médio</li>
                                <li>CEE 191/2020</li>
                            </ul>
                        </div>
                        
                        <div class="benefit-card">
                            <h3><i class="fas fa-headset"></i> Assessoria Completa</h3>
                            <ul class="benefit-list">
                                <li>Assessoria na certificação</li>
                                <li>Publicação na SED</li>
                                <li>Secretaria Escolar Digital</li>
                            </ul>
                        </div>
                        
                        <div class="benefit-card">
                            <h3><i class="fas fa-certificate"></i> Reconhecimento Oficial</h3>
                            <ul class="benefit-list">
                                <li>Secretaria de Educação</li>
                                <li>Reconhecido pelo CEE</li>
                                <li>Certificação garantida</li>
                            </ul>
                        </div>
                    </div>
                </section>
                
                <!-- Presence Section -->
                <section class="presence-section">
                    <h3>📍 Nossa presença</h3>
                    <p>Mais de 7 polos credenciados e autorizados pela Secretaria de Educação e pelo CEE/SP.</p>
                </section>
                
                <!-- Form Section -->
                <section class="form-section">
                    <h2 class="form-title">✍ Cadastre-se para se tornar um parceiro</h2>
                    <p class="form-subtitle">Estamos felizes em saber que você quer se tornar um parceiro/polo da Ensino Certo. Para isso, preencha todas as informações abaixo:</p>
                    
                    <a href="{{ route('home') }}" class="back-link">
                        <i class="fas fa-arrow-left"></i> Voltar ao site
                    </a>
                    
                    @if($errors->any())
                        <div class="alert alert-danger">
                            <strong>Erro no cadastro:</strong>
                            <ul style="margin: 10px 0 0 0; padding-left: 20px;">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                    
                    <form action="{{ route('parceiros.store') }}" method="POST" id="parceiroForm">
                        @csrf
                        
                        <!-- Dados Pessoais -->
                        <div class="form-step">
                            <h3 class="step-title">
                                <span class="step-number">1</span>
                                <i class="fas fa-user"></i> Dados Pessoais
                            </h3>
                            
                            <div class="form-group required">
                                <label for="nome_completo">Nome Completo</label>
                                <input type="text" id="nome_completo" name="nome_completo" value="{{ old('nome_completo') }}" required>
                            </div>
                            
                            <div class="form-row">
                                <div class="form-group required">
                                    <label for="email">Email</label>
                                    <input type="email" id="email" name="email" value="{{ old('email') }}" required>
                                </div>
                                
                                <div class="form-group required">
                                    <label for="whatsapp">WhatsApp</label>
                                    <input type="tel" id="whatsapp" name="whatsapp" value="{{ old('whatsapp') }}" required>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Endereço -->
                        <div class="form-step">
                            <h3 class="step-title">
                                <span class="step-number">2</span>
                                <i class="fas fa-map-marker-alt"></i> Localização
                            </h3>
                            
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="cep">CEP</label>
                                    <input type="text" id="cep" name="cep" value="{{ old('cep') }}" placeholder="00000-000">
                                    <div class="loading-cep" id="loading-cep">
                                        <i class="fas fa-spinner fa-spin"></i> Buscando endereço...
                                    </div>
                                </div>
                                
                                <div class="form-group">
                                    <label for="estado">Estado</label>
                                    <select id="estado" name="estado">
                                        <option value="">Selecione</option>
                                        <option value="AC" {{ old('estado') == 'AC' ? 'selected' : '' }}>Acre</option>
                                        <option value="AL" {{ old('estado') == 'AL' ? 'selected' : '' }}>Alagoas</option>
                                        <option value="AP" {{ old('estado') == 'AP' ? 'selected' : '' }}>Amapá</option>
                                        <option value="AM" {{ old('estado') == 'AM' ? 'selected' : '' }}>Amazonas</option>
                                        <option value="BA" {{ old('estado') == 'BA' ? 'selected' : '' }}>Bahia</option>
                                        <option value="CE" {{ old('estado') == 'CE' ? 'selected' : '' }}>Ceará</option>
                                        <option value="DF" {{ old('estado') == 'DF' ? 'selected' : '' }}>Distrito Federal</option>
                                        <option value="ES" {{ old('estado') == 'ES' ? 'selected' : '' }}>Espírito Santo</option>
                                        <option value="GO" {{ old('estado') == 'GO' ? 'selected' : '' }}>Goiás</option>
                                        <option value="MA" {{ old('estado') == 'MA' ? 'selected' : '' }}>Maranhão</option>
                                        <option value="MT" {{ old('estado') == 'MT' ? 'selected' : '' }}>Mato Grosso</option>
                                        <option value="MS" {{ old('estado') == 'MS' ? 'selected' : '' }}>Mato Grosso do Sul</option>
                                        <option value="MG" {{ old('estado') == 'MG' ? 'selected' : '' }}>Minas Gerais</option>
                                        <option value="PA" {{ old('estado') == 'PA' ? 'selected' : '' }}>Pará</option>
                                        <option value="PB" {{ old('estado') == 'PB' ? 'selected' : '' }}>Paraíba</option>
                                        <option value="PR" {{ old('estado') == 'PR' ? 'selected' : '' }}>Paraná</option>
                                        <option value="PE" {{ old('estado') == 'PE' ? 'selected' : '' }}>Pernambuco</option>
                                        <option value="PI" {{ old('estado') == 'PI' ? 'selected' : '' }}>Piauí</option>
                                        <option value="RJ" {{ old('estado') == 'RJ' ? 'selected' : '' }}>Rio de Janeiro</option>
                                        <option value="RN" {{ old('estado') == 'RN' ? 'selected' : '' }}>Rio Grande do Norte</option>
                                        <option value="RS" {{ old('estado') == 'RS' ? 'selected' : '' }}>Rio Grande do Sul</option>
                                        <option value="RO" {{ old('estado') == 'RO' ? 'selected' : '' }}>Rondônia</option>
                                        <option value="RR" {{ old('estado') == 'RR' ? 'selected' : '' }}>Roraima</option>
                                        <option value="SC" {{ old('estado') == 'SC' ? 'selected' : '' }}>Santa Catarina</option>
                                        <option value="SP" {{ old('estado') == 'SP' ? 'selected' : '' }}>São Paulo</option>
                                        <option value="SE" {{ old('estado') == 'SE' ? 'selected' : '' }}>Sergipe</option>
                                        <option value="TO" {{ old('estado') == 'TO' ? 'selected' : '' }}>Tocantins</option>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label for="cidade">Cidade</label>
                                <input type="text" id="cidade" name="cidade" value="{{ old('cidade') }}">
                            </div>
                        </div>
                        
                        <!-- Informações da Parceria -->
                        <div class="form-step">
                            <h3 class="step-title">
                                <span class="step-number">3</span>
                                <i class="fas fa-handshake"></i> Informações da Parceria
                            </h3>
                            
                            <div class="form-row">
                                <div class="form-group required">
                                    <label for="modalidade_parceria">Modalidade da parceria</label>
                                    <select id="modalidade_parceria" name="modalidade_parceria" required>
                                        <option value="">Selecione a modalidade</option>
                                        <option value="polo_presencial" {{ old('modalidade_parceria') == 'polo_presencial' ? 'selected' : '' }}>Polo Presencial</option>
                                        <option value="polo_ead" {{ old('modalidade_parceria') == 'polo_ead' ? 'selected' : '' }}>Polo EaD</option>
                                        <option value="polo_hibrido" {{ old('modalidade_parceria') == 'polo_hibrido' ? 'selected' : '' }}>Polo Híbrido</option>
                                        <option value="representante_comercial" {{ old('modalidade_parceria') == 'representante_comercial' ? 'selected' : '' }}>Representante Comercial</option>
                                    </select>
                                </div>
                                
                                <div class="form-group required">
                                    <label>Possui estrutura?</label>
                                    <div class="radio-group">
                                        <div class="radio-option">
                                            <input type="radio" id="estrutura_sim" name="possui_estrutura" value="1" {{ old('possui_estrutura') == '1' ? 'checked' : '' }} required>
                                            <label for="estrutura_sim">Sim</label>
                                        </div>
                                        <div class="radio-option">
                                            <input type="radio" id="estrutura_nao" name="possui_estrutura" value="0" {{ old('possui_estrutura') == '0' ? 'checked' : '' }} required>
                                            <label for="estrutura_nao">Não</label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="form-row">
                                <div class="form-group required">
                                    <label>Tem site?</label>
                                    <div class="radio-group">
                                        <div class="radio-option">
                                            <input type="radio" id="site_sim" name="tem_site" value="1" {{ old('tem_site') == '1' ? 'checked' : '' }} required>
                                            <label for="site_sim">Sim</label>
                                        </div>
                                        <div class="radio-option">
                                            <input type="radio" id="site_nao" name="tem_site" value="0" {{ old('tem_site') == '0' ? 'checked' : '' }} required>
                                            <label for="site_nao">Não</label>
                                        </div>
                                    </div>
                                    <div class="site-url-group" id="site-url-group">
                                        <label for="site_url">URL do Site</label>
                                        <input type="url" id="site_url" name="site_url" value="{{ old('site_url') }}" placeholder="https://exemplo.com.br">
                                    </div>
                                </div>
                                
                                <div class="form-group required">
                                    <label>Tem experiência na área educacional?</label>
                                    <div class="radio-group">
                                        <div class="radio-option">
                                            <input type="radio" id="exp_sim" name="tem_experiencia_educacional" value="1" {{ old('tem_experiencia_educacional') == '1' ? 'checked' : '' }} required>
                                            <label for="exp_sim">Sim</label>
                                        </div>
                                        <div class="radio-option">
                                            <input type="radio" id="exp_nao" name="tem_experiencia_educacional" value="0" {{ old('tem_experiencia_educacional') == '0' ? 'checked' : '' }} required>
                                            <label for="exp_nao">Não</label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label for="plano_negocio">Qual é seu plano de negócio?</label>
                                <textarea id="plano_negocio" name="plano_negocio" placeholder="Descreva seu plano de negócio para a parceria...">{{ old('plano_negocio') }}</textarea>
                            </div>
                        </div>
                        
                        <button type="submit" class="btn-submit">
                            <i class="fas fa-paper-plane"></i> Enviar Cadastro
                        </button>
                    </form>
                </section>
            </div>
        </div>
    </div>

    <!-- WhatsApp Floating Button -->
    @if($whatsappSettings['whatsapp_enabled'] ?? true)
        <div id="whatsapp-float" class="whatsapp-float {{ $whatsappSettings['whatsapp_button_position'] ?? 'bottom-right' }}" 
             style="background-color: {{ $whatsappSettings['whatsapp_button_color'] ?? '#25d366' }}">
            <a href="https://wa.me/{{ $whatsappSettings['whatsapp_number'] ?? '5511999999999' }}?text={{ urlencode($whatsappSettings['whatsapp_message'] ?? 'Olá! Tenho interesse no curso EJA. Podem me ajudar?') }}" 
               target="_blank" 
               rel="noopener noreferrer"
               aria-label="Conversar no WhatsApp">
                <i class="fab fa-whatsapp"></i>
            </a>
        </div>
    @endif

    <!-- Incluir o componente footer -->
    @include('components.footer', ['landingSettings' => $landingSettings])
    
    
    <script>
        $(document).ready(function() {
            // Máscara para WhatsApp e CEP
            $('#whatsapp').mask('(00) 00000-0000');
            $('#cep').mask('00000-000');
            
            // Lógica do radio site
            $('input[name="tem_site"]').change(function() {
                if ($(this).val() === '1') {
                    $('#site-url-group').slideDown();
                    $('#site_url').prop('required', true);
                } else {
                    $('#site-url-group').slideUp();
                    $('#site_url').prop('required', false).val('');
                }
            });
            
            // Verificar estado inicial
            if ($('input[name="tem_site"]:checked').val() === '1') {
                $('#site-url-group').show();
                $('#site_url').prop('required', true);
            }
            
            // Buscar CEP
            $('#cep').blur(function() {
                const cep = $(this).val().replace(/\D/g, '');
                if (cep.length === 8) {
                    $('#loading-cep').show();
                    
                    fetch(`/api/cep/${cep}`)
                        .then(response => response.json())
                        .then(data => {
                            $('#loading-cep').hide();
                            if (!data.error && !data.erro) {
                                $('#cidade').val(data.localidade);
                                $('#estado').val(data.uf);
                                
                                // Focar no próximo campo
                                $('#modalidade_parceria').focus();
                            } else {
                                alert('CEP não encontrado');
                            }
                        })
                        .catch(error => {
                            $('#loading-cep').hide();
                            console.log('Erro ao buscar CEP:', error);
                        });
                }
            });
        });
    </script>
</body>
</html> 