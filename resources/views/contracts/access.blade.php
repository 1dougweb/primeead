<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Acesso ao Contrato - {{ $contract->title }}</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .contract-container {
            max-width: 900px;
            margin: 2rem auto;
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        
        .contract-header {
            background: linear-gradient(135deg, #4CAF50 0%, #45a049 100%);
            color: white;
            padding: 2rem;
            text-align: center;
        }
        
        .contract-content {
            padding: 2rem;
        }
        
        .step-indicator {
            display: flex;
            justify-content: center;
            margin-bottom: 2rem;
        }
        
        .step {
            display: flex;
            align-items: center;
            margin: 0 1rem;
            color: #ccc;
            transition: all 0.3s ease;
        }
        
        .step.active {
            color: #4CAF50;
        }
        
        .step.completed {
            color: #4CAF50;
        }
        
        .step-number {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            background: #ccc;
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 0.5rem;
            font-weight: bold;
            transition: all 0.3s ease;
        }
        
        .step.active .step-number {
            background: #4CAF50;
            transform: scale(1.1);
        }
        
        .step.completed .step-number {
            background: #4CAF50;
        }
        
        .contract-text {
            max-height: 400px;
            overflow-y: auto;
            border: 1px solid #ddd;
            padding: 1rem;
            border-radius: 8px;
            background: #f8f9fa;
            margin-bottom: 1rem;
            font-size: 0.95rem;
            line-height: 1.6;
        }
        
        .signature-pad {
            border: 2px solid #ddd;
            border-radius: 8px;
            background: white;
            cursor: crosshair;
            transition: border-color 0.3s ease;
        }
        
        .signature-pad:hover {
            border-color: #4CAF50;
        }
        
        .signature-controls {
            margin-top: 1rem;
            text-align: center;
        }
        
        .loading {
            display: none;
            text-align: center;
            padding: 2rem;
        }
        
        .success-message {
            display: none;
            text-align: center;
            padding: 2rem;
            color: #4CAF50;
        }
        
        .error-message {
            display: none;
            text-align: center;
            padding: 1rem;
            color: #dc3545;
            background: #f8d7da;
            border: 1px solid #f5c6cb;
            border-radius: 8px;
            margin-bottom: 1rem;
        }
        
        .step-content {
            display: none;
            animation: fadeIn 0.5s ease-in-out;
        }
        
        .step-content.active {
            display: block;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .contract-info {
            background: #f8f9fa;
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1rem;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #4CAF50 0%, #45a049 100%);
            border: none;
            padding: 0.75rem 2rem;
            border-radius: 25px;
            font-weight: bold;
            transition: all 0.3s ease;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(76, 175, 80, 0.3);
        }
        
        .btn-secondary {
            background: #6c757d;
            border: none;
            padding: 0.5rem 1.5rem;
            border-radius: 20px;
            transition: all 0.3s ease;
        }
        
        .btn-secondary:hover {
            background: #5a6268;
            transform: translateY(-1px);
        }
        
        .btn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none !important;
        }
        
        .alert {
            border-radius: 8px;
            margin-bottom: 1rem;
        }
        
        .form-control:focus {
            border-color: #4CAF50;
            box-shadow: 0 0 0 0.2rem rgba(76, 175, 80, 0.25);
        }
        
        .contract-already-signed {
            background: linear-gradient(135deg, #d4edda 0%, #c3e6cb 100%);
            border: 2px solid #28a745;
            border-radius: 15px;
            padding: 3rem 2rem;
            margin: 2rem 0;
            box-shadow: 0 10px 30px rgba(40, 167, 69, 0.1);
        }
        
        .contract-already-signed h3 {
            color: #155724;
            font-weight: bold;
            margin-bottom: 1.5rem;
        }
        
        .contract-already-signed p {
            color: #155724;
            font-size: 1.1rem;
            margin-bottom: 1rem;
        }
        
        .contract-already-signed .email-info {
            background: rgba(255, 255, 255, 0.8);
            border-radius: 8px;
            padding: 1rem;
            margin: 1.5rem 0;
            border-left: 4px solid #28a745;
        }
        
        .contract-already-signed .btn {
            padding: 0.75rem 2rem;
            font-weight: bold;
            border-radius: 25px;
            transition: all 0.3s ease;
        }
        
        .contract-already-signed .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(40, 167, 69, 0.3);
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="contract-container">
            <!-- Header -->
            <div class="contract-header">
                <h1><i class="fas fa-file-contract me-2"></i>{{ $contract->title }}</h1>
                <p class="mb-0">Contrato Digital com Assinatura Eletrônica</p>
            </div>
            
            <!-- Content -->
            <div class="contract-content">
                @if($contract->status === 'signed')
                    <!-- Contract Already Signed -->
                    <div class="contract-already-signed text-center">
                        <i class="fas fa-check-circle fa-4x mb-4" style="color: #28a745;"></i>
                        <h3>Contrato Já Assinado!</h3>
                        <p>
                            Este contrato já foi assinado digitalmente em<br>
                            <strong>{{ $contract->signed_at->format('d/m/Y \à\s H:i:s') }}</strong>
                        </p>
                        
                        <div class="row mt-4">
                            <div class="col-md-6">
                                <div class="email-info">
                                    <i class="fas fa-user me-2"></i>
                                    <strong>Aluno:</strong><br>
                                    <span style="font-size: 1.1rem;">{{ $contract->matricula->nome_completo }}</span>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="email-info">
                                    <i class="fas fa-envelope me-2"></i>
                                    <strong>Email usado na assinatura:</strong><br>
                                    <span style="font-size: 1.1rem; color: #0d6efd;">{{ $contract->student_email }}</span>
                                </div>
                            </div>
                        </div>
                        
                        <div class="email-info mt-3">
                            <i class="fas fa-file-contract me-2"></i>
                            <strong>Contrato:</strong> {{ $contract->contract_number }}<br>
                            <small class="text-muted">{{ $contract->title }}</small>
                        </div>
                        
                        <div class="mt-4">
                            <a href="/contracts/{{ $contract->access_token }}/pdf" class="btn btn-success btn-lg" target="_blank">
                                <i class="fas fa-download me-2"></i>Baixar Contrato PDF
                            </a>
                        </div>
                    </div>
                @else
                    <!-- Step Indicator -->
                    <div class="step-indicator">
                        <div class="step active" id="step-1">
                            <div class="step-number">1</div>
                            <span>Verificação</span>
                        </div>
                        <div class="step" id="step-2">
                            <div class="step-number">2</div>
                            <span>Leitura do Contrato</span>
                        </div>
                        <div class="step" id="step-3">
                            <div class="step-number">3</div>
                            <span>Assinatura</span>
                        </div>
                        <div class="step" id="step-4">
                            <div class="step-number">4</div>
                            <span>Confirmação</span>
                        </div>
                    </div>
                    
                    <!-- Error Message -->
                    <div class="error-message" id="error-message"></div>
                    
                    <!-- Step 1: Email Verification -->
                    <div class="step-content active" id="step-1-content">
                        <div class="contract-info">
                            <h4><i class="fas fa-info-circle me-2"></i>Informações do Contrato</h4>
                            <p><strong>Número:</strong> {{ $contract->contract_number }}</p>
                            <p><strong>Data de Criação:</strong> {{ $contract->created_at->format('d/m/Y H:i') }}</p>
                            <p><strong>Validade:</strong> {{ $contract->access_expires_at->format('d/m/Y H:i') }}</p>
                            <p><strong>Status:</strong> <span class="badge bg-{{ $contract->status_color }}">{{ $contract->status_formatted }}</span></p>
                        </div>
                        
                        <div class="text-center">
                            <h3><i class="fas fa-envelope-open-text me-2"></i>Verificação de Email</h3>
                            <p>Para acessar o contrato, confirme seu email cadastrado:</p>
                            
                            <form id="verify-email-form">
                                <div class="row justify-content-center">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <input type="email" class="form-control form-control-lg" 
                                                   id="email" name="email" placeholder="Digite seu email" required>
                                        </div>
                                        <button type="submit" class="btn btn-primary btn-lg" id="verify-btn">
                                            <i class="fas fa-check me-2"></i>Verificar Email
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                    
                    <!-- Step 2: Contract Review -->
                    <div class="step-content" id="step-2-content">
                        <h3><i class="fas fa-file-text me-2"></i>Leitura do Contrato</h3>
                        <p>Leia atentamente todo o contrato antes de prosseguir para a assinatura:</p>
                        
                        <div class="contract-text" id="contract-text">
                            <!-- Contract content will be loaded here -->
                        </div>
                        
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            <strong>Importante:</strong> Certifique-se de ler todo o contrato antes de assinar. Role a área acima para ver todo o conteúdo.
                        </div>
                        
                        <div class="text-center">
                            <button type="button" class="btn btn-secondary me-2" onclick="goToStep(1)">
                                <i class="fas fa-arrow-left me-2"></i>Voltar
                            </button>
                            <button type="button" class="btn btn-primary" onclick="proceedToSignature()">
                                <i class="fas fa-arrow-right me-2"></i>Prosseguir para Assinatura
                            </button>
                        </div>
                    </div>
                    
                    <!-- Step 3: Digital Signature -->
                    <div class="step-content" id="step-3-content">
                        <h3><i class="fas fa-signature me-2"></i>Assinatura Digital</h3>
                        <p>Assine no campo abaixo usando o mouse ou toque na tela:</p>
                        
                        <div class="text-center">
                            <canvas id="signature-pad" class="signature-pad" width="600" height="200"></canvas>
                            <div class="signature-controls">
                                <button type="button" class="btn btn-secondary btn-sm me-2" onclick="clearSignature()">
                                    <i class="fas fa-eraser me-1"></i>Limpar
                                </button>
                                <button type="button" class="btn btn-secondary me-2" onclick="goToStep(2)">
                                    <i class="fas fa-arrow-left me-2"></i>Voltar
                                </button>
                                <button type="button" class="btn btn-primary" onclick="signContract()" id="sign-btn">
                                    <i class="fas fa-pen me-2"></i>Assinar Contrato
                                </button>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Step 4: Confirmation -->
                    <div class="step-content" id="step-4-content">
                        <div class="success-message" style="display: block;">
                            <i class="fas fa-check-circle fa-3x mb-3"></i>
                            <h3>Contrato Assinado com Sucesso!</h3>
                            <p>Seu contrato foi assinado digitalmente em <span id="signed-date"></span></p>
                            <p>Uma cópia foi enviada para seu email cadastrado.</p>
                            
                            <div class="mt-4">
                                <button type="button" class="btn btn-primary" onclick="downloadContractPdf()" id="download-pdf-btn">
                                    <i class="fas fa-download me-2"></i>Baixar Contrato PDF
                                </button>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Loading -->
                    <div class="loading" id="loading">
                        <i class="fas fa-spinner fa-spin fa-2x mb-3"></i>
                        <p>Processando...</p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Global variables
        let currentStep = 1;
        let signaturePad;
        let contractData = null;
        const token = '{{ $contract->access_token }}';
        const contractSigned = {{ $contract->status === 'signed' ? 'true' : 'false' }};
        
        // Initialize
        document.addEventListener('DOMContentLoaded', function() {
            console.log('Contract access page loaded');
            console.log('Current step:', currentStep);
            console.log('Contract signed:', contractSigned);
            
            // Se o contrato já foi assinado, não inicializar as funções
            if (contractSigned) {
                console.log('Contract already signed, skipping initialization');
                return;
            }
            
            initializeSignaturePad();
            setupEventListeners();
            
            // Debug: Log all step elements
            for (let i = 1; i <= 4; i++) {
                const stepContent = document.getElementById(`step-${i}-content`);
                const stepIndicator = document.getElementById(`step-${i}`);
                console.log(`Step ${i} - Content:`, stepContent ? 'Found' : 'Not found', 'Indicator:', stepIndicator ? 'Found' : 'Not found');
            }
        });
        
        function initializeSignaturePad() {
            const canvas = document.getElementById('signature-pad');
            if (!canvas) return;
            
            const ctx = canvas.getContext('2d');
            
            // Set canvas size - use fixed dimensions to avoid issues when hidden
            canvas.width = 600;
            canvas.height = 200;
            
            let isDrawing = false;
            let lastX = 0;
            let lastY = 0;
            
            // Mouse events
            canvas.addEventListener('mousedown', (e) => {
                isDrawing = true;
                [lastX, lastY] = [e.offsetX, e.offsetY];
            });
            
            canvas.addEventListener('mousemove', (e) => {
                if (!isDrawing) return;
                
                ctx.strokeStyle = '#000';
                ctx.lineWidth = 2;
                ctx.lineCap = 'round';
                ctx.lineJoin = 'round';
                
                ctx.beginPath();
                ctx.moveTo(lastX, lastY);
                ctx.lineTo(e.offsetX, e.offsetY);
                ctx.stroke();
                
                [lastX, lastY] = [e.offsetX, e.offsetY];
            });
            
            canvas.addEventListener('mouseup', () => isDrawing = false);
            canvas.addEventListener('mouseout', () => isDrawing = false);
            
            // Touch events
            canvas.addEventListener('touchstart', (e) => {
                e.preventDefault();
                const touch = e.touches[0];
                const rect = canvas.getBoundingClientRect();
                isDrawing = true;
                lastX = touch.clientX - rect.left;
                lastY = touch.clientY - rect.top;
            });
            
            canvas.addEventListener('touchmove', (e) => {
                e.preventDefault();
                if (!isDrawing) return;
                
                const touch = e.touches[0];
                const rect = canvas.getBoundingClientRect();
                const x = touch.clientX - rect.left;
                const y = touch.clientY - rect.top;
                
                ctx.strokeStyle = '#000';
                ctx.lineWidth = 2;
                ctx.lineCap = 'round';
                ctx.lineJoin = 'round';
                
                ctx.beginPath();
                ctx.moveTo(lastX, lastY);
                ctx.lineTo(x, y);
                ctx.stroke();
                
                [lastX, lastY] = [x, y];
            });
            
            canvas.addEventListener('touchend', () => isDrawing = false);
        }
        
        function setupEventListeners() {
            // Email verification form
            const verifyForm = document.getElementById('verify-email-form');
            if (verifyForm) {
                verifyForm.addEventListener('submit', function(e) {
                    e.preventDefault();
                    verifyEmail();
                });
            }
        }
        
        function verifyEmail() {
            // Se o contrato já foi assinado, não permitir verificação
            if (contractSigned) {
                console.log('Contract already signed, email verification disabled');
                return;
            }
            
            const email = document.getElementById('email').value.trim();
            const verifyBtn = document.getElementById('verify-btn');
            
            if (!email) {
                showError('Por favor, digite seu email.');
                return;
            }
            
            // Validate email format
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(email)) {
                showError('Por favor, digite um email válido.');
                return;
            }
            
            console.log('Verifying email:', email);
            
            // Disable button during request
            verifyBtn.disabled = true;
            verifyBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Verificando...';
            
            showLoading();
            
            fetch(`/contracts/${token}/verify`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({ email: email })
            })
            .then(response => response.json())
            .then(data => {
                console.log('Verify response:', data);
                hideLoading();
                
                // Re-enable button
                verifyBtn.disabled = false;
                verifyBtn.innerHTML = '<i class="fas fa-check me-2"></i>Verificar Email';
                
                if (data.success) {
                    contractData = data.contract;
                    document.getElementById('contract-text').innerHTML = contractData.content;
                    goToStep(2);
                } else {
                    showError(data.message || 'Erro ao verificar email. Verifique se o email está correto.');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                hideLoading();
                
                // Re-enable button
                verifyBtn.disabled = false;
                verifyBtn.innerHTML = '<i class="fas fa-check me-2"></i>Verificar Email';
                
                showError('Erro de conexão. Tente novamente.');
            });
        }
        
        function goToStep(step) {
            // Se o contrato já foi assinado, não permitir navegação
            if (contractSigned) {
                console.log('Contract already signed, step navigation disabled');
                return;
            }
            
            console.log('Going to step:', step);
            
            if (step < 1 || step > 4) {
                console.error('Invalid step:', step);
                return;
            }
            
            // Hide error message when changing steps
            hideError();
            
            // Hide all step contents
            document.querySelectorAll('.step-content').forEach(content => {
                content.classList.remove('active');
                content.style.display = 'none';
            });
            
            // Reset all step indicators
            document.querySelectorAll('.step').forEach(stepEl => {
                stepEl.classList.remove('active', 'completed');
            });
            
            // Show target step content
            const targetContent = document.getElementById(`step-${step}-content`);
            const targetStep = document.getElementById(`step-${step}`);
            
            if (targetContent && targetStep) {
                targetContent.classList.add('active');
                targetContent.style.display = 'block';
                targetStep.classList.add('active');
                
                // Mark completed steps
                for (let i = 1; i < step; i++) {
                    const completedStep = document.getElementById(`step-${i}`);
                    if (completedStep) {
                        completedStep.classList.add('completed');
                    }
                }
                
                currentStep = step;
                console.log('Successfully moved to step:', step);
                
                // Reinitialize signature pad when going to step 3
                if (step === 3) {
                    setTimeout(() => {
                        initializeSignaturePad();
                    }, 100);
                }
                
                // Scroll to top
                window.scrollTo({ top: 0, behavior: 'smooth' });
            } else {
                console.error('Step elements not found for step:', step);
            }
        }
        
        function clearSignature() {
            const canvas = document.getElementById('signature-pad');
            if (canvas) {
                // Ensure canvas has proper dimensions
                if (canvas.width === 0 || canvas.height === 0) {
                    canvas.width = 600;
                    canvas.height = 200;
                }
                
                const ctx = canvas.getContext('2d');
                ctx.clearRect(0, 0, canvas.width, canvas.height);
                console.log('Signature cleared');
            }
        }
        
        function signContract() {
            // Se o contrato já foi assinado, não permitir nova assinatura
            if (contractSigned) {
                console.log('Contract already signed, signing disabled');
                return;
            }
            
            const canvas = document.getElementById('signature-pad');
            const signBtn = document.getElementById('sign-btn');
            
            if (!canvas) {
                showError('Erro: Campo de assinatura não encontrado.');
                return;
            }
            
            // Ensure canvas has proper dimensions
            if (canvas.width === 0 || canvas.height === 0) {
                canvas.width = 600;
                canvas.height = 200;
            }
            
            const ctx = canvas.getContext('2d');
            
            // Check if signature is empty
            const imageData = ctx.getImageData(0, 0, canvas.width, canvas.height);
            const isEmpty = imageData.data.every(pixel => pixel === 0);
            
            if (isEmpty) {
                showError('Por favor, assine no campo acima antes de continuar.');
                return;
            }
            
            console.log('Signing contract...');
            
            // Disable button during request
            signBtn.disabled = true;
            signBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Assinando...';
            
            // Get signature data
            const signatureData = canvas.toDataURL('image/png');
            
            // Get screen resolution
            const screenResolution = `${screen.width}x${screen.height}`;
            
            showLoading();
            
            fetch(`/contracts/${token}/sign`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({
                    signature: signatureData,
                    screen_resolution: screenResolution,
                    user_agent: navigator.userAgent
                })
            })
            .then(response => response.json())
            .then(data => {
                console.log('Sign response:', data);
                hideLoading();
                
                // Re-enable button
                signBtn.disabled = false;
                signBtn.innerHTML = '<i class="fas fa-pen me-2"></i>Assinar Contrato';
                
                if (data.success) {
                    document.getElementById('signed-date').textContent = data.signed_at;
                    goToStep(4);
                } else {
                    showError(data.message || 'Erro ao assinar contrato. Tente novamente.');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                hideLoading();
                
                // Re-enable button
                signBtn.disabled = false;
                signBtn.innerHTML = '<i class="fas fa-pen me-2"></i>Assinar Contrato';
                
                showError('Erro de conexão. Tente novamente.');
            });
        }
        
        function showError(message) {
            console.log('Showing error:', message);
            const errorDiv = document.getElementById('error-message');
            if (errorDiv) {
                errorDiv.textContent = message;
                errorDiv.style.display = 'block';
                
                // Auto-hide after 8 seconds
                setTimeout(() => {
                    errorDiv.style.display = 'none';
                }, 8000);
                
                // Scroll to top to show error
                window.scrollTo({ top: 0, behavior: 'smooth' });
            }
        }
        
        function hideError() {
            const errorDiv = document.getElementById('error-message');
            if (errorDiv) {
                errorDiv.style.display = 'none';
            }
        }
        
        function proceedToSignature() {
            // Check if user has scrolled through the contract
            const contractText = document.getElementById('contract-text');
            if (contractText) {
                const scrollPercentage = (contractText.scrollTop + contractText.clientHeight) / contractText.scrollHeight;
                if (scrollPercentage < 0.8) {
                    showError('Por favor, leia todo o contrato antes de prosseguir. Role a área do contrato para baixo.');
                    return;
                }
            }
            
            goToStep(3);
        }
        
        function showLoading() {
            const loading = document.getElementById('loading');
            if (loading) {
                loading.style.display = 'block';
                
                // Hide all step contents
                document.querySelectorAll('.step-content').forEach(content => {
                    content.style.display = 'none';
                });
            }
        }
        
        function hideLoading() {
            const loading = document.getElementById('loading');
            if (loading) {
                loading.style.display = 'none';
                
                // Show current step content
                const currentContent = document.getElementById(`step-${currentStep}-content`);
                if (currentContent) {
                    currentContent.style.display = 'block';
                    currentContent.classList.add('active');
                }
            }
        }
        
        function downloadContractPdf() {
            console.log('Downloading contract PDF...');
            
            const downloadBtn = document.getElementById('download-pdf-btn');
            
            // Disable button and show loading
            if (downloadBtn) {
                downloadBtn.disabled = true;
                downloadBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Preparando PDF...';
            }
            
            // Criar link para download do PDF
            const pdfUrl = `/contracts/${token}/pdf`;
            
            try {
                // Abrir em nova aba para download
                window.open(pdfUrl, '_blank');
                
                // Re-enable button after a short delay
                setTimeout(() => {
                    if (downloadBtn) {
                        downloadBtn.disabled = false;
                        downloadBtn.innerHTML = '<i class="fas fa-download me-2"></i>Baixar Contrato PDF';
                    }
                }, 2000);
                
            } catch (error) {
                console.error('Error downloading PDF:', error);
                
                // Re-enable button
                if (downloadBtn) {
                    downloadBtn.disabled = false;
                    downloadBtn.innerHTML = '<i class="fas fa-download me-2"></i>Baixar Contrato PDF';
                }
                
                showError('Erro ao baixar o PDF. Tente novamente.');
            }
        }
    </script>
</body>
</html> 