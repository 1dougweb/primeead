<!-- Rodapé -->
<footer class="footer-section">
    <div class="container">
        <div class="footer-content">
            <!-- Coluna 1: Logo e Descrição da Empresa -->
            <div class="footer-column">
                <div class="footer-logo">
                    <img src="{{ asset('assets/images/logotipo-dark.svg') }}" alt="Ensino Certo Logo" class="footer-logo-img">
                    <img src="{{ asset('assets/images/anhangue-vip.svg') }}" alt="Anhanguera Logo" class="footer-logo-partner">
                </div>
                <p class="footer-description">
                    {{ $landingSettings['footer_company_name'] ?? 'Centro de Ensino Certo Educacional' }} oferece cursos de EJA reconhecidos pelo MEC, 
                    proporcionando educação de qualidade para quem busca concluir seus estudos.
                </p>
                <div class="footer-badges">
                    <div class="badge-item">
                        @if(!empty($landingSettings['mec_authorization_file']))
                            <a href="{{ route('mec.authorization') }}" target="_blank" class="mec-authorization-link">
                                <span>Autorização MEC</span>
                                <i class="fas fa-download ms-1"></i>
                            </a>
                        @else
                            <span>Autorização MEC</span>
                        @endif
                    </div>
                    @if(!empty($landingSettings['mec_address']))
                        <div class="mec-address">
                            <i class="fas fa-map-marker-alt"></i>
                            <span>{{ $landingSettings['mec_address'] }}</span>
                        </div>
                    @endif
                </div>

                <!-- Imagens do Rodapé -->
                <div class="footer-images mt-3">
                    <div class="footer-images-row">
                        <div class="footer-image-item">
                            <img src="{{ asset('assets/images/footer_image_1.png') }}" 
                                 alt="Imagem 1" 
                                 class="img-fluid footer-image">
                        </div>
                        
                        <div class="footer-image-item">
                            <img src="{{ asset('assets/images/footer_image_2.png') }}" 
                                 alt="Imagem 2" 
                                 class="img-fluid footer-image">
                        </div>
                        
                        <div class="footer-image-item">
                            <img src="{{ asset('assets/images/footer_image_3.png') }}" 
                                 alt="Imagem 3" 
                                 class="img-fluid footer-image">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Coluna 2: Links Institucionais -->
            <div class="footer-column">
                <h3 class="footer-title">Institucional</h3>
                <ul class="footer-links">
                    <li><a href="/">Home</a></li>
                    <li><a href="/#beneficios-principais">Benefícios</a></li>
                    <li><a href="#perguntas-frequentes">FAQ</a></li>
                    <li><a href="{{ route('contato') }}">Contato</a></li>
                    <li><a href="https://hubflix.com.br/metodo/login.php" target="_blank">Área do Aluno</a></li>
                </ul>
            </div>

            <!-- Coluna 3: Contato -->
            <div class="footer-column">
                <h3 class="footer-title">Contato</h3>
                <div class="contact-info">
                    @if(!empty($landingSettings['footer_phone']))
                        <div class="contact-item">
                            <i class="fab fa-whatsapp"></i>
                            <a href="https://wa.me/55{{ preg_replace('/[^0-9]/', '', $landingSettings['footer_phone']) }}?text={{ urlencode('Olá! Gostaria de informações sobre o curso EJA.') }}" target="_blank">{{ $landingSettings['footer_phone'] }}</a>
                        </div>
                    @else
                        <div class="contact-item">
                            <i class="fab fa-whatsapp"></i>
                            <a href="https://wa.me/5511917012033?text={{ urlencode('Olá! Gostaria de informações sobre o curso EJA.') }}" target="_blank">(11) 91701-2033</a>
                        </div>
                    @endif
                    
                    <div class="contact-item">
                        <i class="fas fa-phone"></i>
                        <a href="tel:1142103596">(11) 4210-3596</a>
                    </div>
                    
                    @if(!empty($landingSettings['footer_email']))
                        <div class="contact-item">
                            <i class="fas fa-envelope"></i>
                            <a href="mailto:{{ $landingSettings['footer_email'] }}">{{ $landingSettings['footer_email'] }}</a>
                        </div>
                    @else
                        <div class="contact-item">
                            <i class="fas fa-envelope"></i>
                            <a href="mailto:contato@ensinocerto.com.br">contato@ensinocerto.com.br</a>
                        </div>
                    @endif
                    
                    @if(!empty($landingSettings['footer_address']))
                        <div class="contact-item">
                            <i class="fas fa-map-marker-alt"></i>
                            <span>{{ $landingSettings['footer_address'] }}</span>
                        </div>
                    @else
                        <div class="contact-item">
                            <i class="fas fa-map-marker-alt"></i>
                            <span>Av. José Caballero, 231 - Vila Bastos,<br>Santo André - SP, 09040-210</span>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Coluna 4: Ações e Redes Sociais -->
            <div class="footer-column">
                <h3 class="footer-title">Comece Agora</h3>
                <div class="footer-cta">
                    <a href="https://hubflix.com.br/metodo/login.php" target="_blank" class="btn-footer-primary">
                        <i class="fas fa-graduation-cap"></i>
                        Área do aluno
                    </a>
                    <a href="{{ route('parceiros.cadastro') }}" class="btn-footer-secondary">
                        <i class="fas fa-handshake"></i>
                        Seja um parceiro
                    </a>
                </div>
                
                @if(!empty($landingSettings['social_instagram']) || !empty($landingSettings['social_facebook']) || !empty($landingSettings['social_linkedin']) || !empty($landingSettings['social_youtube']) || !empty($landingSettings['social_tiktok']))
                    <div class="social-links">
                        <h4>Siga-nos</h4>
                        <div class="social-icons">
                            @if(!empty($landingSettings['social_instagram']))
                                <a href="{{ $landingSettings['social_instagram'] }}" target="_blank" aria-label="Instagram">
                                    <i class="fab fa-instagram"></i>
                                </a>
                            @endif
                            
                            @if(!empty($landingSettings['social_facebook']))
                                <a href="{{ $landingSettings['social_facebook'] }}" target="_blank" aria-label="Facebook">
                                    <i class="fab fa-facebook-f"></i>
                                </a>
                            @endif
                            
                            @if(!empty($landingSettings['social_linkedin']))
                                <a href="{{ $landingSettings['social_linkedin'] }}" target="_blank" aria-label="LinkedIn">
                                    <i class="fab fa-linkedin-in"></i>
                                </a>
                            @endif
                            
                            @if(!empty($landingSettings['social_youtube']))
                                <a href="{{ $landingSettings['social_youtube'] }}" target="_blank" aria-label="YouTube">
                                    <i class="fab fa-youtube"></i>
                                </a>
                            @endif
                            
                            @if(!empty($landingSettings['social_tiktok']))
                                <a href="{{ $landingSettings['social_tiktok'] }}" target="_blank" aria-label="TikTok">
                                    <i class="fab fa-tiktok"></i>
                                </a>
                            @endif
                        </div>
                    </div>
                @endif
            </div>
        </div>

        <!-- Linha separadora -->
        <div class="footer-divider"></div>

        <!-- Rodapé inferior -->
        <div class="footer-bottom">
            <div class="footer-bottom-left">
                <p>{{ $landingSettings['footer_copyright'] ?? 'Configure o copyright no painel de controle.' }} - <a href="https://wa.me/5511992950897" target="_blank" style="color: #1C7FDC;text-decoration: none;">Desenvolvido por Douglas <i class="fas fa-coffee"></i></a></p>
            </div>
            <div class="footer-bottom-right">
                <div class="footer-policies">
                    <a href="/politica-privacidade">Política de privacidade</a>
                    <a href="/politica-rembolso">Política de rembolso</a>
                </div>
            </div>
        </div>
    </div>
</footer>

<style>
.footer-images {
    margin-top: 1rem;
}

.footer-images-row {
    display: flex;
    gap: 10px;
    align-items: center;
    justify-content: flex-start;
}

.footer-image-item {
    flex: 1;
    max-width: 80px;
}

.footer-image {
    width: 100%;
    height: auto;
    border-radius: 8px;
    transition: transform 0.3s ease;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.footer-image:hover {
    transform: scale(1.05);
    box-shadow: 0 4px 8px rgba(0,0,0,0.2);
}

@media (max-width: 768px) {
    .footer-images-row {
        gap: 8px;
    }
    
    .footer-image-item {
        max-width: 60px;
    }
}
</style>