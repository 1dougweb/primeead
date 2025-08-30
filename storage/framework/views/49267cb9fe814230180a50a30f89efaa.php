<?php
    $landingSettings = \App\Models\SystemSetting::getLandingPageSettings();
    $chatEnabled = $landingSettings['chat_enabled'] ?? false;
    $chatTitle = $landingSettings['chat_title'] ?? 'Precisa de ajuda?';
    $chatWelcomeMessage = $landingSettings['chat_welcome_message'] ?? 'Olá! Como posso ajudá-lo hoje?';
    $chatPosition = $landingSettings['chat_position'] ?? 'bottom-right';
    $chatColor = $landingSettings['chat_color'] ?? '#007bff';
    $chatIcon = $landingSettings['chat_icon'] ?? 'fas fa-comments';
?>

<?php if($chatEnabled): ?>
<div id="chat-widget" class="chat-widget chat-widget-<?php echo e($chatPosition); ?>" style="--chat-color: <?php echo e($chatColor); ?>;">
    <!-- Botão do Chat -->
    <div class="chat-button" id="chat-button" onclick="toggleChat()">
        <i class="<?php echo e($chatIcon); ?>"></i>
        <span class="chat-title"><?php echo e($chatTitle); ?></span>
    </div>
    
    <!-- Janela do Chat -->
    <div class="chat-window" id="chat-window" style="display: none;">
        <div class="chat-header">
            <div class="chat-header-content">
                <i class="fas fa-robot"></i>
                <span class="mr-2">Suporte ao Cliente</span>
            </div>
            <div class="chat-header-actions">
                <button class="chat-clear-btn" onclick="clearChatHistory()" title="Limpar histórico">
                    <i class="fas fa-trash"></i>
                </button>
                <button class="chat-close" onclick="toggleChat()" title="Fechar chat">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        </div>
        
        <div class="chat-messages" id="chat-messages">
            <!-- Mensagens serão carregadas aqui -->
        </div>
        
        <!-- Indicador de digitação DENTRO da janela do chat -->
        <div class="chat-typing" id="chat-typing" style="display: none;">
            <div class="typing-indicator">
                <span></span>
                <span></span>
                <span></span>
            </div>
        </div>
        
        <div class="chat-input-container">
            <!-- Campo de Email (visível apenas na primeira mensagem) -->
            <div class="chat-email-container" id="chat-email-container">
                <div class="chat-email-input-wrapper">
                    <input type="email" id="chat-email-input" class="chat-email-input" placeholder="Digite seu email por favor" maxlength="100">
                    <button class="chat-email-btn" onclick="setUserEmail()">
                        <i class="fas fa-check"></i>
                    </button>
                </div>
            </div>
            
            <div class="chat-input-wrapper" id="chat-input-wrapper" style="display: none;">
                <input type="text" id="chat-input" class="chat-input" placeholder="Digite sua mensagem..." maxlength="1000">
                <button class="chat-send-btn" onclick="sendMessage()">
                    <i class="fas fa-paper-plane"></i>
                </button>
            </div>
        </div>
    </div>
</div>

<style>
.chat-widget {
    position: fixed;
    z-index: 9999;
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
}

.chat-widget-bottom-right {
    bottom: 20px;
    right: 20px;
}

.chat-widget-bottom-left {
    bottom: 20px;
    left: 20px;
}

.chat-button {
    background: var(--chat-color);
    color: white;
    padding: 15px 20px;
    border-radius: 50px;
    cursor: pointer;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    gap: 10px;
    font-weight: 500;
    min-width: 200px;
    justify-content: center;
}

.chat-button:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(0, 0, 0, 0.2);
}

.chat-button i {
    font-size: 18px;
}

.chat-title {
    font-size: 14px;
}

.chat-window {
    position: absolute;
    bottom: 80px;
    width: 350px;
    height: 500px;
    background: white;
    border-radius: 12px;
    box-shadow: 0 8px 32px rgba(0, 0, 0, 0.12);
    display: flex;
    flex-direction: column;
    overflow: hidden;
}

.chat-widget-bottom-left .chat-window {
    left: 0;
}

.chat-widget-bottom-right .chat-window {
    right: 0;
}



.chat-header {
    background: var(--chat-color);
    color: white;
    padding: 15px 20px;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.chat-header-content {
    display: flex;
    align-items: center;
    font-weight: 600;
}

.chat-header-actions {
    display: flex;
    gap: 8px;
    align-items: center;
}

.chat-clear-btn, .chat-close {
    background: none;
    border: none;
    color: white;
    cursor: pointer;
    font-size: 16px;
    padding: 0;
    width: 30px;
    height: 30px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
    transition: background-color 0.2s ease;
}

.chat-clear-btn:hover, .chat-close:hover {
    background-color: rgba(255, 255, 255, 0.2);
}

.chat-clear-btn {
    font-size: 14px;
}

.chat-messages {
    flex: 1;
    padding: 20px;
    overflow-y: auto;
    background: #f8f9fa;
}

.message {
    margin-bottom: 15px;
    display: flex;
    flex-direction: column;
}

.message-content {
    display: flex;
    align-items: flex-start;
    gap: 10px;
}

/* Mensagens do usuário: avatar à direita */
.user-message .message-content {
    flex-direction: row-reverse;
    justify-content: flex-start;
}

.user-message {
    align-items: flex-end;
}

.message-avatar {
    width: 32px;
    height: 32px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 14px;
    flex-shrink: 0;
}

.user-message .message-avatar {
    background: var(--chat-color);
    color: white;
}

.assistant-message .message-avatar {
    background: #6c757d;
    color: white;
}

.message-text {
    background: white;
    padding: 12px 16px;
    border-radius: 18px;
    max-width: 250px;
    word-wrap: break-word;
    line-height: 1.4;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

.user-message .message-text {
    background: var(--chat-color);
    color: white;
    margin-left: auto;
    margin-right: 0;
}

.assistant-message .message-text {
    background: white;
    color: #333;
}

.message-time {
    font-size: 11px;
    color: #6c757d;
    margin-top: 5px;
    margin-left: 42px;
}

/* Horário das mensagens do usuário: alinhar à direita */
.user-message .message-time {
    margin-left: auto;
    margin-right: 42px;
    text-align: right;
}

.chat-input-container {
    padding: 15px 20px;
    background: white;
    border-top: 1px solid #e9ecef;
}

.chat-email-container {
    margin-bottom: 15px;
    padding: 10px 15px;
    background: #f0f2f5;
    border-radius: 8px;
    border: 1px solid #d0d0d0;
    display: flex;
    align-items: center;
    gap: 10px;
}

.chat-email-input-wrapper {
    display: flex;
    align-items: center;
    gap: 8px;
}

.chat-email-input {
    flex: 1;
    border: none;
    padding: 5px 0;
    font-size: 14px;
    outline: none;
    background: transparent;
}

.chat-email-btn {
    background: var(--chat-color);
    color: white;
    border: none;
    border-radius: 50%;
    width: 30px;
    height: 30px;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: background-color 0.2s ease;
}

.chat-email-btn:hover {
    background-color: #0056b3;
}

.chat-email-help {
    font-size: 12px;
    color: #6c757d;
    margin-top: 5px;
}

.chat-input-wrapper {
    display: flex;
    gap: 10px;
    align-items: center;
}

.chat-input {
    flex: 1;
    border: 1px solid #ddd;
    border-radius: 20px;
    padding: 10px 15px;
    font-size: 14px;
    outline: none;
    transition: border-color 0.2s ease;
}

.chat-input:focus {
    border-color: var(--chat-color);
}

.chat-send-btn {
    background: var(--chat-color);
    color: white;
    border: none;
    border-radius: 50%;
    width: 40px;
    height: 40px;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all 0.2s ease;
}

.chat-send-btn:hover {
    transform: scale(1.05);
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.2);
}

.chat-send-btn:disabled {
    opacity: 0.6;
    cursor: not-allowed;
    transform: none;
}

.chat-typing {
    position: relative;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 12px 16px;
    background: white;
    border-radius: 20px;
    border: 1px solid #e9ecef;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    z-index: 10;
    margin: 0;
    animation: slideInUp 0.3s ease-out;
    transition: all 0.3s ease;
}





.typing-indicator {
    display: flex;
    gap: 6px;
    align-items: center;
    padding: 8px 12px;
    background: white;
    border: 1px solid #e9ecef;
    border-radius: 15px;
    box-shadow: 0 1px 4px rgba(0,0,0,0.1);
}

.typing-indicator span {
    width: 8px;
    height: 8px;
    border-radius: 50%;
    background: #6c757d;
    animation: typing 1.4s infinite ease-in-out;
    display: inline-block;
}

.typing-indicator span:nth-child(1) { animation-delay: -0.32s; }
.typing-indicator span:nth-child(2) { animation-delay: -0.16s; }

@keyframes typing {
    0%, 80%, 100% {
        transform: scale(0.8);
        opacity: 0.5;
    }
    40% {
        transform: scale(1);
        opacity: 1;
    }
}

@keyframes slideInUp {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.typing-text {
    font-size: 14px;
    color: #495057;
    font-weight: 600;
    letter-spacing: 0.3px;
}

/* Estilo para mensagem de digitação */
.typing-message {
    opacity: 0.8;
}

.typing-container {
    display: flex;
    align-items: center;
}

/* Estilo específico para o indicador dentro da mensagem */
.typing-message .chat-typing {
    position: static !important;
    margin: 0 !important;
    padding: 8px 12px !important;
    background: white !important;
    border: 1px solid #e9ecef !important;
    border-radius: 15px !important;
    box-shadow: 0 1px 4px rgba(0,0,0,0.1) !important;
}

/* Ajustar o layout da mensagem de digitação */
.typing-message .message-content {
    justify-content: flex-start;
}

.typing-message .typing-container {
    margin-left: 10px;
}

/* Responsividade */
@media (max-width: 480px) {
    .chat-window {
        width: calc(100vw - 40px);
        left: 20px !important;
        right: 20px !important;
    }
    
    .chat-button {
        min-width: auto;
        padding: 12px 16px;
    }
    
    .chat-title {
        display: none;
    }
}

/* Formatação de mensagens */
.message-text strong {
    font-weight: 700;
    color: #2c3e50;
}

.message-text em {
    font-style: italic;
    color: #7f8c8d;
}

/* Status com cores */
.status-success {
    color: #27ae60;
    font-weight: bold;
}

.status-warning {
    color: #f39c12;
    font-weight: bold;
}

.status-danger {
    color: #e74c3c;
    font-weight: bold;
}

.money-icon {
    color: #27ae60;
    font-weight: bold;
}

/* Estilo para links do chat */
.chat-link {
    color: var(--chat-color);
    text-decoration: none;
    font-weight: 600;
    padding: 4px 8px;
    background: rgba(0, 123, 255, 0.1);
    border-radius: 4px;
    transition: all 0.2s ease;
}

.chat-link:hover {
    background: rgba(0, 123, 255, 0.2);
    text-decoration: underline;
}
}

.date-icon {
    color: #3498db;
    font-weight: bold;
}

.whatsapp-icon {
    color: #25d366;
    font-weight: bold;
}

/* Botão de WhatsApp */
.whatsapp-button-container {
    margin: 15px 0;
    text-align: center;
}

.whatsapp-button {
    display: inline-flex;
    align-items: center;
    gap: 10px;
    background: #25d366;
    color: white;
    text-decoration: none;
    padding: 12px 20px;
    border-radius: 25px;
    font-weight: 600;
    font-size: 14px;
    transition: all 0.3s ease;
    box-shadow: 0 4px 15px rgba(37, 211, 102, 0.3);
}

.whatsapp-button:hover {
    background: #128c7e;
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(37, 211, 102, 0.4);
    color: white;
    text-decoration: none;
}

.whatsapp-button i {
    font-size: 18px;
}

.whatsapp-button span {
    font-size: 14px;
}

/* Melhorias na formatação das mensagens */
.message-text {
    line-height: 1.6;
}

.message-text br {
    margin-bottom: 8px;
}

/* Destaque para informações importantes */
.message-text strong {
    background: linear-gradient(45deg, #f39c12, #e67e22);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
    padding: 2px 4px;
    border-radius: 4px;
}

/* Efeito de digitação */
.typing-message .message-text {
    min-height: 20px;
}

.typing-cursor {
    color: var(--chat-color);
    font-weight: bold;
    animation: blink 1s infinite;
}

@keyframes blink {
    0%, 50% { opacity: 1; }
    51%, 100% { opacity: 0; }
}

.message-typed {
    animation: messageSlideIn 0.5s ease-out;
}

@keyframes messageSlideIn {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* Badge de notificação */
.chat-notification-badge {
    position: absolute;
    top: -8px;
    right: -8px;
    background: #e74c3c;
    color: white;
    border-radius: 50%;
    width: 24px;
    height: 24px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 12px;
    font-weight: bold;
    animation: badgePulse 2s infinite;
    box-shadow: 0 2px 8px rgba(231, 76, 60, 0.4);
}

@keyframes badgePulse {
    0% {
        transform: scale(1);
        box-shadow: 0 2px 8px rgba(231, 76, 60, 0.4);
    }
    50% {
        transform: scale(1.1);
        box-shadow: 0 4px 16px rgba(231, 76, 60, 0.6);
    }
    100% {
        transform: scale(1);
        box-shadow: 0 2px 8px rgba(231, 76, 60, 0.4);
    }
}

/* Posicionamento relativo para o botão do chat */
.chat-button {
    position: relative;
}

/* Melhorias na animação das mensagens */
.message {
    transition: all 0.3s ease;
}

.message:hover {
    transform: translateX(5px);
}

/* Indicador de digitação melhorado */
.chat-typing {
    margin-top: 10px;
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 0 15px;
    animation: fadeIn 0.3s ease-in;
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
}

/* Melhorias na responsividade */
@media (max-width: 480px) {
    .chat-notification-badge {
        width: 20px;
        height: 20px;
        font-size: 10px;
        top: -6px;
        right: -6px;
    }
    
    .typing-cursor {
        font-size: 14px;
    }
}

/* Scrollbar personalizada */
.chat-messages::-webkit-scrollbar {
    width: 8px;
}

.chat-messages::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 4px;
}

.chat-messages::-webkit-scrollbar-thumb {
    background: var(--chat-color);
    border-radius: 4px;
    transition: background-color 0.3s ease;
}

.chat-messages::-webkit-scrollbar-thumb:hover {
    background: #0056b3;
}

/* Para Firefox */
.chat-messages {
    scrollbar-width: thin;
    scrollbar-color: var(--chat-color) #f1f1f1;
}

/* Melhorias no scroll automático */
.chat-messages {
    scroll-behavior: smooth;
}

/* Indicador de scroll suave */
.chat-messages.scrolling {
    scroll-behavior: auto;
}

/* Melhorias na animação das mensagens */
.message {
    transition: all 0.3s ease;
}

.message:hover {
    transform: translateX(5px);
}

/* Indicador de digitação melhorado */
.chat-typing {
    margin-top: 10px;
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 0 15px;
    animation: fadeIn 0.3s ease-in;
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
}
</style>

<script>
let chatSessionId = null;
let isTyping = false;
let chatHistory = [];
let userEmail = null;
let userEmailSet = false;
let unreadMessages = 0;
let welcomeMessageShown = false;
const STORAGE_KEY = 'chat_history';
const SESSION_KEY = 'chat_session_id';
const EMAIL_KEY = 'chat_user_email';
const UNREAD_KEY = 'chat_unread_count';

// Inicializar chat ao carregar a página
document.addEventListener('DOMContentLoaded', function() {
    initializeChat();
    
    // Mostrar mensagem de boas-vindas após 3 segundos
    setTimeout(() => {
        if (!welcomeMessageShown && chatHistory.length === 0) {
            showWelcomeMessageWithDelay();
        }
    }, 3000);
});

// Inicializar chat
function initializeChat() {
    // Carregar histórico salvo
    loadChatHistory();
    
    // Carregar email salvo
    loadUserEmail();
    
    // Carregar contador de mensagens não lidas
    loadUnreadCount();
    
    // Gerar ou recuperar ID de sessão
    chatSessionId = getStoredSessionId();
    if (!chatSessionId) {
        generateSessionId();
    }
    
    // Configurar eventos
    setupChatEvents();
    
    // Mostrar/esconder campos baseado no email
    updateEmailDisplay();
    
    // Atualizar notificação
    updateNotificationBadge();
}

// Mostrar mensagem de boas-vindas com delay
function showWelcomeMessageWithDelay() {
    if (welcomeMessageShown || chatHistory.length > 0) return;
    
    welcomeMessageShown = true;
    
    let welcomeMessage = '<?php echo e($chatWelcomeMessage); ?>';
    
    if (!userEmailSet) {
        welcomeMessage += '\n\n💡 **Dica**: Digite seu email acima para que eu possa fornecer informações personalizadas sobre sua matrícula, pagamentos e progresso acadêmico!';
    }
    
    const welcomeMsg = {
        id: 'welcome',
        role: 'assistant',
        content: welcomeMessage,
        timestamp: new Date().toLocaleTimeString('pt-BR', { hour: '2-digit', minute: '2-digit' }),
        is_user: false
    };
    
    chatHistory.push(welcomeMsg);
    saveChatHistory();
    
    // Mostrar com efeito de digitação
    showMessageWithTypewriter(welcomeMsg);
}

// Carregar contador de mensagens não lidas
function loadUnreadCount() {
    try {
        const stored = localStorage.getItem(UNREAD_KEY);
        if (stored) {
            unreadMessages = parseInt(stored);
        }
    } catch (error) {
        console.error('Erro ao carregar contador de mensagens não lidas:', error);
        unreadMessages = 0;
    }
}

// Salvar contador de mensagens não lidas
function saveUnreadCount() {
    try {
        localStorage.setItem(UNREAD_KEY, unreadMessages.toString());
    } catch (error) {
        console.error('Erro ao salvar contador de mensagens não lidas:', error);
    }
}

// Incrementar contador de mensagens não lidas
function incrementUnreadCount() {
    unreadMessages++;
    saveUnreadCount();
    updateNotificationBadge();
}

// Resetar contador de mensagens não lidas
function resetUnreadCount() {
    unreadMessages = 0;
    saveUnreadCount();
    updateNotificationBadge();
}

// Atualizar badge de notificação
function updateNotificationBadge() {
    const chatButton = document.getElementById('chat-button');
    const notificationBadge = document.querySelector('.chat-notification-badge');
    
    if (unreadMessages > 0) {
        if (!notificationBadge) {
            // Criar badge se não existir
            const badge = document.createElement('div');
            badge.className = 'chat-notification-badge';
            badge.textContent = unreadMessages > 99 ? '99+' : unreadMessages;
            chatButton.appendChild(badge);
        } else {
            notificationBadge.textContent = unreadMessages > 99 ? '99+' : unreadMessages;
        }
    } else {
        // Remover badge se não há mensagens não lidas
        if (notificationBadge) {
            notificationBadge.remove();
        }
    }
}

// Carregar email do usuário
function loadUserEmail() {
    try {
        const stored = localStorage.getItem(EMAIL_KEY);
        if (stored) {
            userEmail = stored;
            userEmailSet = true;
        }
    } catch (error) {
        console.error('Erro ao carregar email:', error);
    }
}

// Salvar email do usuário
function saveUserEmail(email) {
    try {
        localStorage.setItem(EMAIL_KEY, email);
    } catch (error) {
        console.error('Erro ao salvar email:', error);
    }
}

// Definir email do usuário
function setUserEmail() {
    const emailInput = document.getElementById('chat-email-input');
    const email = emailInput.value.trim();
    
    if (!email || !isValidEmail(email)) {
        alert('Por favor, digite um email válido.');
        return;
    }
    
    userEmail = email;
    userEmailSet = true;
    saveUserEmail(email);
    
    // Esconder campo de email e mostrar campo de mensagem
    updateEmailDisplay();
    
    // Adicionar mensagem de confirmação
    addMessage(`Email configurado: ${email}. Agora posso fornecer informações personalizadas sobre sua matrícula e pagamentos!`, 'assistant');
    
    // Focar no campo de mensagem
    document.getElementById('chat-input').focus();
}

// Validar formato de email
function isValidEmail(email) {
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return emailRegex.test(email);
}

// Atualizar exibição dos campos baseado no email
function updateEmailDisplay() {
    const emailContainer = document.getElementById('chat-email-container');
    const inputWrapper = document.getElementById('chat-input-wrapper');
    
    if (userEmailSet) {
        emailContainer.style.display = 'none';
        inputWrapper.style.display = 'flex';
    } else {
        emailContainer.style.display = 'block';
        inputWrapper.style.display = 'none';
    }
}

// Carregar histórico do localStorage
function loadChatHistory() {
    try {
        const stored = localStorage.getItem(STORAGE_KEY);
        if (stored) {
            chatHistory = JSON.parse(stored);
            renderChatHistory();
        }
    } catch (error) {
        console.error('Erro ao carregar histórico:', error);
        chatHistory = [];
    }
}

// Salvar histórico no localStorage
function saveChatHistory() {
    try {
        localStorage.setItem(STORAGE_KEY, JSON.stringify(chatHistory));
    } catch (error) {
        console.error('Erro ao salvar histórico:', error);
    }
}

// Obter ID de sessão salvo
function getStoredSessionId() {
    return localStorage.getItem(SESSION_KEY);
}

// Salvar ID de sessão
function saveSessionId(sessionId) {
    localStorage.setItem(SESSION_KEY, sessionId);
}

// Gerar ID de sessão único
async function generateSessionId() {
    try {
        const response = await fetch('/api/chat/generate-session');
        const data = await response.json();
        if (data.success) {
            chatSessionId = data.session_id;
            saveSessionId(chatSessionId);
        }
    } catch (error) {
        console.error('Erro ao gerar ID de sessão:', error);
        // Fallback: gerar ID local
        chatSessionId = 'local_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9);
        saveSessionId(chatSessionId);
    }
}

// Configurar eventos do chat
function setupChatEvents() {
    const input = document.getElementById('chat-input');
    
    input.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            sendMessage();
        }
    });
    
    input.addEventListener('input', function() {
        const sendBtn = document.querySelector('.chat-send-btn');
        sendBtn.disabled = !this.value.trim();
    });
    
    // Evento para campo de email
    const emailInput = document.getElementById('chat-email-input');
    emailInput.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            setUserEmail();
        }
    });
}

// Alternar visibilidade do chat
function toggleChat() {
    const chatWindow = document.getElementById('chat-window');
    const chatButton = document.getElementById('chat-button');
    
    if (chatWindow.style.display === 'none') {
        chatWindow.style.display = 'flex';
        chatButton.style.display = 'none';
        
        // Resetar contador de mensagens não lidas
        resetUnreadCount();
        
        // Focar no campo apropriado
        if (userEmailSet) {
            document.getElementById('chat-input').focus();
        } else {
            document.getElementById('chat-email-input').focus();
        }
    } else {
        chatWindow.style.display = 'none';
        chatButton.style.display = 'flex';
    }
}

// Enviar mensagem
async function sendMessage() {
    const input = document.getElementById('chat-input');
    const message = input.value.trim();
    
    if (!message || isTyping) return;
    
    // Adicionar mensagem do usuário
    addMessage(message, 'user');
    input.value = '';
    
    // Desabilitar botão de envio
    const sendBtn = document.querySelector('.chat-send-btn');
    sendBtn.disabled = true;
    
    // Mostrar indicador de digitação
    showTyping(true);
    isTyping = true;
    
            try {
            // Usar URL absoluta para evitar problemas de rota
            const apiUrl = window.location.origin + '/api/chat/process-message';
            console.log('Enviando mensagem para:', apiUrl);
            
            const response = await fetch(apiUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || 'test-token'
                },
                body: JSON.stringify({
                    message: message,
                    session_id: chatSessionId,
                    user_email: userEmail, // ← Email do usuário para busca automática
                    user_name: null
                })
            });
        
        const data = await response.json();
        
        if (data.success) {
            // Processar resposta do ChatGPT
            let response = data.response;
            
            // Processar comandos especiais do ChatGPT
            if (response.includes('[GERAR_SEGUNDA_VIA:')) {
                response = processSecondViaCommand(response);
            } else if (response.includes('[VERIFICAR_ELEGIBILIDADE:')) {
                response = processEligibilityCommand(response);
            }
            
            // Delay de 3 segundos para simular tempo de resposta
            setTimeout(() => {
                addMessage(response, 'assistant');
                // Ocultar indicador de digitação APÓS mostrar a mensagem
                showTyping(false);
                isTyping = false;
            }, 3000);
        } else {
            addMessage('Desculpe, ocorreu um erro. Tente novamente.', 'assistant');
            showTyping(false);
            isTyping = false;
        }
            } catch (error) {
            console.error('Erro ao enviar mensagem:', error);
            console.error('Tipo de erro:', error.name);
            console.error('Mensagem de erro:', error.message);
            
            if (error.name === 'TypeError' && error.message.includes('NetworkError')) {
                addMessage('Erro de conexão com o servidor. Verifique se o servidor está rodando.', 'assistant');
            } else {
                addMessage('Erro de conexão. Tente novamente.', 'assistant');
            }
            showTyping(false);
            isTyping = false;
        } finally {
            // Apenas reabilitar o botão de envio
            sendBtn.disabled = false;
        }
}

// Adicionar mensagem ao chat
function addMessage(content, role) {
    const message = {
        id: Date.now() + '_' + Math.random().toString(36).substr(2, 9),
        role: role,
        content: content,
        timestamp: new Date().toLocaleTimeString('pt-BR', { hour: '2-digit', minute: '2-digit' }),
        is_user: role === 'user'
    };
    
    chatHistory.push(message);
    saveChatHistory();
    
    // Incrementar contador de mensagens não lidas se o chat estiver fechado
    if (document.getElementById('chat-window').style.display === 'none') {
        incrementUnreadCount();
    }
    
    // Para mensagens do usuário, renderizar imediatamente
    if (role === 'user') {
        renderChatHistory();
        
        // Scroll para a última mensagem
        scrollToBottom();
    } else {
        // Para mensagens do assistente, usar efeito de digitação
        showMessageWithTypewriter(message);
    }
}

// Mostrar mensagem com efeito de digitação (DESABILITADO)
function showMessageWithTypewriter(message) {
    // Mostrar mensagem diretamente sem efeito de digitação
    // NÃO adicionar ao histórico aqui - apenas exibir
    const messageDiv = document.createElement('div');
    messageDiv.className = `message ${message.role}-message`;
    
    // Processar formatação da mensagem
    let formattedContent = message.content;
    
    // Substituir [BOTÃO_WHATSAPP] por botão real
    if (formattedContent.includes('[BOTÃO_WHATSAPP]')) {
        formattedContent = formattedContent.replace('[BOTÃO_WHATSAPP]', createWhatsAppButton());
    }
    
    // Aplicar formatação Markdown básica
    formattedContent = formatMessage(formattedContent);
    
    messageDiv.innerHTML = `
        <div class="message-content">
            <i class="fas ${message.role === 'user' ? 'fa-user' : 'fa-robot'} message-avatar"></i>
            <div class="message-text">${formattedContent}</div>
        </div>
        <div class="message-time">${message.timestamp || new Date().toLocaleTimeString()}</div>
    `;
    
    // Adicionar mensagem ao container
    const messagesContainer = document.getElementById('chat-messages');
    messagesContainer.appendChild(messageDiv);
    
    // Scroll para o final
    scrollToBottom();
}

// Efeito de digitação (DESABILITADO)
function typeMessage(messageDiv, text, index) {
    // Função desabilitada - mensagens aparecem instantaneamente
}

// Função para scroll automático
function scrollToBottom() {
    const messagesContainer = document.getElementById('chat-messages');
    messagesContainer.scrollTop = messagesContainer.scrollHeight;
}

// Renderizar histórico do chat
function renderChatHistory() {
    const messagesContainer = document.getElementById('chat-messages');
    messagesContainer.innerHTML = '';
    
    chatHistory.forEach(message => {
        const messageDiv = document.createElement('div');
        messageDiv.className = `message ${message.role}-message`;
        
        // Processar formatação da mensagem
        let formattedContent = message.content;
        
        // Substituir [BOTÃO_WHATSAPP] por botão real
        if (formattedContent.includes('[BOTÃO_WHATSAPP]')) {
            formattedContent = formattedContent.replace('[BOTÃO_WHATSAPP]', createWhatsAppButton());
        }
        
        // Aplicar formatação Markdown básica
        formattedContent = formatMessage(formattedContent);
        
        messageDiv.innerHTML = `
            <div class="message-content">
                <i class="fas ${message.is_user ? 'fa-user' : 'fa-robot'} message-avatar"></i>
                <div class="message-text">${formattedContent}</div>
            </div>
            <div class="message-time">${message.timestamp}</div>
        `;
        
        messagesContainer.appendChild(messageDiv);
    });
    
    // Scroll para o final após renderizar histórico
    scrollToBottom();
}

// Formatar mensagem com Markdown básico (SEM processar botão WhatsApp)
function formatMessageWithoutWhatsApp(content) {
    // Negrito: **texto** -> <strong>texto</strong>
    content = content.replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>');
    
    // Itálico: *texto* -> <em>texto</em>
    content = content.replace(/\*(.*?)\*/g, '<em>$1</em>');
    
    // Quebras de linha
    content = content.replace(/\n/g, '<br>');
    
    // Cores baseadas em emojis
    content = content.replace(/🟢/g, '<span class="status-success">🟢</span>');
    content = content.replace(/🟠/g, '<span class="status-warning">🟠</span>');
    content = content.replace(/🔴/g, '<span class="status-danger">🔴</span>');
    content = content.replace(/💰/g, '<span class="money-icon">💰</span>');
    content = content.replace(/📅/g, '<span class="date-icon">📅</span>');
    content = content.replace(/📱/g, '<span class="whatsapp-icon">📱</span>');
    
    return content;
}

// Formatar mensagem com Markdown básico e emojis (COM processamento de botão)
function formatMessage(content) {
    // Processar botão WhatsApp primeiro
    if (content.includes('[BOTÃO_WHATSAPP]')) {
        content = content.replace('[BOTÃO_WHATSAPP]', createWhatsAppButton());
    }
    
    // Processar links Markdown: [texto](url)
    content = content.replace(/\[([^\]]+)\]\(([^)]+)\)/g, '<a href="$2" target="_blank" class="chat-link">$1</a>');
    
    // Aplicar formatação Markdown
    content = formatMessageWithoutWhatsApp(content);
    
    return content;
}

// Criar botão de WhatsApp
function createWhatsAppButton() {
    const phoneNumber = '<?php echo e($landingSettings["support_phone"] ?? "5511917012033"); ?>';
    const message = encodeURIComponent('Olá! Preciso de ajuda com EJA Supletivo.');
    const whatsappUrl = `https://wa.me/${phoneNumber}?text=${message}`;
    
    return `
        <div class="whatsapp-button-container">
            <a href="${whatsappUrl}" target="_blank" class="whatsapp-button">
                <i class="fab fa-whatsapp"></i>
                <span>Conversar no WhatsApp</span>
            </a>
        </div>
    `;
}

// Mostrar/ocultar indicador de digitação
function showTyping(show) {
    if (show) {
        // Criar uma mensagem temporária com o indicador
        const messageDiv = document.createElement('div');
        messageDiv.className = 'message assistant-message typing-message';
        messageDiv.id = 'typing-message';
        
        messageDiv.innerHTML = `
            <div class="message-content">
                <i class="fas fa-robot message-avatar"></i>
                <div class="typing-container">
                    <div class="typing-indicator">
                        <span></span>
                        <span></span>
                        <span></span>
                    </div>
                </div>
            </div>
        `;
        
        // Adicionar ao chat
        const messagesContainer = document.getElementById('chat-messages');
        messagesContainer.appendChild(messageDiv);
        
        // Scroll para o final
        scrollToBottom();
    } else {
        // Remover a mensagem de digitação
        const typingMessage = document.getElementById('typing-message');
        if (typingMessage) {
            typingMessage.remove();
        }
    }
}

// Limpar histórico do chat
function clearChatHistory() {
    if (confirm('Tem certeza que deseja limpar todo o histórico do chat?')) {
        chatHistory = [];
        localStorage.removeItem(STORAGE_KEY);
        renderChatHistory();
        
        // Resetar flag de mensagem de boas-vindas
        welcomeMessageShown = false;
        
        // Mostrar mensagem de boas-vindas novamente
        setTimeout(() => {
            showWelcomeMessageWithDelay();
        }, 500);
    }
}

// Sincronizar com servidor (opcional)
async function syncWithServer() {
    if (!chatSessionId || chatSessionId.startsWith('local_')) return;
    
    try {
        const response = await fetch(`/api/chat/history?session_id=${chatSessionId}`);
        const data = await response.json();
        
        if (data.success && data.history.length > 0) {
            // Mesclar histórico local com servidor
            const serverHistory = data.history.map(msg => ({
                id: msg.id,
                role: msg.role,
                content: msg.content,
                timestamp: msg.timestamp,
                is_user: msg.role === 'user'
            }));
            
            // Manter apenas mensagens únicas
            const existingIds = new Set(chatHistory.map(msg => msg.id));
            const newMessages = serverHistory.filter(msg => !existingIds.has(msg.id));
            
            if (newMessages.length > 0) {
                chatHistory = [...chatHistory, ...newMessages];
                renderChatHistory();
                saveChatHistory();
            }
        }
    } catch (error) {
        console.error('Erro ao sincronizar com servidor:', error);
    }
}

// Sincronizar quando a página ganha foco
document.addEventListener('visibilitychange', function() {
    if (!document.hidden) {
        syncWithServer();
    }
});

// Sincronizar quando a conexão é restaurada
window.addEventListener('online', function() {
    syncWithServer();
});

// Processar comando de segunda via do ChatGPT
function processSecondViaCommand(response) {
    const match = response.match(/\[GERAR_SEGUNDA_VIA:(\d+)\]/);
    if (match) {
        const paymentId = match[1];
        const email = userEmail || 'email@exemplo.com';
        const link = `/api/mercadopago/payment-link?payment_id=${paymentId}&email=${encodeURIComponent(email)}`;
        
        return response.replace(
            `[GERAR_SEGUNDA_VIA:${paymentId}]`,
            `🔗 [CLIQUE AQUI PARA GERAR SEGUNDA VIA](${link})`
        );
    }
    return response;
}

// Processar comando de verificação de elegibilidade do ChatGPT
function processEligibilityCommand(response) {
    const match = response.match(/\[VERIFICAR_ELEGIBILIDADE:(\d+)\]/);
    if (match) {
        const paymentId = match[1];
        const email = userEmail || 'email@exemplo.com';
        const link = `/api/mercadopago/payment-link?payment_id=${paymentId}&email=${encodeURIComponent(email)}`;
        
        return response.replace(
            `[VERIFICAR_ELEGIBILIDADE:${paymentId}]`,
            `🔍 [VERIFICAR STATUS](${link})`
        );
    }
    return response;
}
</script>
<?php endif; ?>
<?php /**PATH C:\Users\Douglas\Documents\Projetos\ensinocerto\resources\views/components/chat-widget.blade.php ENDPATH**/ ?>