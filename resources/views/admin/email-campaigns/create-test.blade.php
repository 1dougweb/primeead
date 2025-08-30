@extends('layouts.admin')

@section('title', 'Criar Campanha de Email - TESTE')

@section('content')
<div style="padding: 20px;">
    <h1>TESTE - Criar Campanha de Email</h1>
    
    <div style="display: flex; gap: 20px;">
        <!-- Templates -->
        <div style="width: 300px; border: 1px solid #ccc; padding: 15px;">
            <h3>Templates:</h3>
            
            @foreach($templates as $template)
                <div class="template-option" 
                     style="border: 1px solid #ddd; padding: 10px; margin: 5px 0; cursor: pointer;"
                     data-template-id="{{ $template['id'] }}"
                     data-name="{{ $template['name'] }}"
                     data-subject="{{ $template['subject'] }}"
                     data-content="{{ base64_encode($template['content']) }}">
                    <strong>{{ $template['name'] }}</strong><br>
                    <small>{{ $template['description'] }}</small>
                </div>
            @endforeach
            
            <div class="template-option" 
                 style="border: 1px solid #ddd; padding: 10px; margin: 5px 0; cursor: pointer;"
                 data-template-id="blank"
                 data-name=""
                 data-subject=""
                 data-content="">
                <strong>Template em Branco</strong><br>
                <small>Comece do zero</small>
            </div>
        </div>
        
        <!-- Form -->
        <div style="flex: 1;">
            <form>
                <div style="margin: 10px 0;">
                    <label>Nome da Campanha:</label><br>
                    <input type="text" id="name" style="width: 100%; padding: 5px;">
                </div>
                
                <div style="margin: 10px 0;">
                    <label>Assunto:</label><br>
                    <input type="text" id="subject" style="width: 100%; padding: 5px;">
                </div>
                
                <div style="margin: 10px 0;">
                    <label>Conteúdo:</label><br>
                    <textarea id="content" style="width: 100%; height: 200px; font-family: monospace; font-size: 12px;"></textarea>
                </div>
                
                <input type="hidden" id="template_id">
            </form>
        </div>
    </div>
    
    <div id="debug" style="background: #f0f0f0; padding: 10px; margin: 20px 0;"></div>
</div>

<script>
console.log('TESTE: Script carregado');

document.addEventListener('DOMContentLoaded', function() {
    console.log('TESTE: DOM carregado');
    
    const templateOptions = document.querySelectorAll('.template-option');
    const nameInput = document.getElementById('name');
    const subjectInput = document.getElementById('subject');
    const contentTextarea = document.getElementById('content');
    const templateIdInput = document.getElementById('template_id');
    const debugDiv = document.getElementById('debug');
    
    console.log('TESTE: Elementos encontrados:', {
        templateOptions: templateOptions.length,
        nameInput: !!nameInput,
        subjectInput: !!subjectInput,
        contentTextarea: !!contentTextarea,
        templateIdInput: !!templateIdInput
    });
    
    debugDiv.innerHTML = `<h4>Debug Info:</h4>
        <p>Templates encontrados: ${templateOptions.length}</p>
        <p>Inputs encontrados: name=${!!nameInput}, subject=${!!subjectInput}, content=${!!contentTextarea}</p>`;
    
    templateOptions.forEach((option, index) => {
        console.log(`TESTE: Configurando template ${index}:`, option.dataset.templateId);
        
        option.addEventListener('click', function() {
            console.log('TESTE: Template clicado:', this.dataset.templateId);
            
            // Visual feedback
            templateOptions.forEach(opt => opt.style.backgroundColor = '');
            this.style.backgroundColor = '#e7f3ff';
            
            const templateId = this.dataset.templateId;
            const templateName = this.dataset.name;
            const templateSubject = this.dataset.subject;
            
            let templateContent = '';
            if (templateId !== 'blank' && this.dataset.content) {
                try {
                    templateContent = atob(this.dataset.content);
                    console.log('TESTE: Conteúdo decodificado, length:', templateContent.length);
                } catch (e) {
                    console.error('TESTE: Erro ao decodificar:', e);
                }
            }
            
            templateIdInput.value = templateId;
            
            if (templateId !== 'blank') {
                nameInput.value = 'Campanha ' + templateName;
                subjectInput.value = templateSubject;
                contentTextarea.value = templateContent;
            } else {
                nameInput.value = '';
                subjectInput.value = '';
                contentTextarea.value = '';
            }
            
            debugDiv.innerHTML = `<h4>Debug Info:</h4>
                <p>Template ID: ${templateId}</p>
                <p>Template Name: ${templateName}</p>
                <p>Template Subject: ${templateSubject}</p>
                <p>Content Length: ${templateContent.length}</p>
                <p>Content Preview: ${templateContent.substring(0, 100)}...</p>`;
            
            console.log('TESTE: Campos preenchidos com sucesso');
        });
    });
});
</script>
@endsection 