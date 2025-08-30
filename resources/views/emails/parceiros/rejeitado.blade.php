@extends('emails.layout')

@section('content')
<div style="background-color: #f8d7da; padding: 20px; border-radius: 8px; margin-bottom: 20px; border: 1px solid #f5c6cb;">
    <h2 style="color: #721c24; margin: 0; font-size: 24px; text-align: center;">
        Sobre sua candidatura - Ensino Certo
    </h2>
    <p style="margin: 10px 0 0 0; color: #721c24; text-align: center; font-size: 16px;">
        Olá, {{ $parceiro->nome_completo }}
    </p>
</div>

<div style="background-color: #ffffff; padding: 20px; border-radius: 8px; border-left: 4px solid #dc3545;">
    <h3 style="color: #333; margin-top: 0;">📋 Status da sua Candidatura</h3>
    
    <div style="background-color: #f8f9fa; padding: 15px; border-radius: 6px; margin: 15px 0;">
        <p style="margin: 0; color: #495057; line-height: 1.6;">
            Agradecemos seu interesse em se tornar um parceiro da <strong>Ensino Certo</strong>. 
            Após uma análise cuidadosa da sua candidatura, decidimos que no momento 
            não podemos prosseguir com sua solicitação de parceria.
        </p>
    </div>

    <p style="color: #495057; line-height: 1.6; margin: 15px 0;">
        Esta decisão não reflete o valor do seu trabalho ou potencial, mas sim nossas necessidades 
        específicas e critérios internos para expansão da rede de parceiros neste momento.
    </p>
</div>

<div style="background-color: #ffffff; padding: 20px; border-radius: 8px; border-left: 4px solid #ffc107; margin-top: 20px;">
    <h3 style="color: #333; margin-top: 0;">🔄 Oportunidades Futuras</h3>
    
    <div style="background-color: #fff3cd; padding: 15px; border-radius: 6px; margin: 15px 0; border: 1px solid #ffeaa7;">
        <p style="margin: 0; color: #856404; line-height: 1.6;">
            <strong>Não desista do seu sonho!</strong> Encorajamos você a se candidatar novamente no futuro, 
            quando pudermos ter novas oportunidades de parceria em sua região.
        </p>
    </div>

    <ul style="color: #495057; line-height: 1.8; padding-left: 20px;">
        <li><strong>Mantenha-se atualizado:</strong> Continue acompanhando as novidades da educação e empreendedorismo</li>
        <li><strong>Desenvolva suas habilidades:</strong> Invista em capacitação na área educacional</li>
        <li><strong>Expanda sua rede:</strong> Conecte-se com outros profissionais da educação</li>
        <li><strong>Monitore oportunidades:</strong> Fique atento a futuras chamadas para parcerias</li>
    </ul>
</div>

<div style="background-color: #ffffff; padding: 20px; border-radius: 8px; border-left: 4px solid #007bff; margin-top: 20px;">
    <h3 style="color: #333; margin-top: 0;">💡 Outras Oportunidades</h3>
    
    <p style="color: #495057; margin-bottom: 15px;">
        Enquanto isso, há outras formas de se envolver com a educação:
    </p>
    
    <div style="display: grid; grid-template-columns: 1fr; gap: 10px;">
        <div style="background-color: #f8f9fa; padding: 12px; border-radius: 6px; border-left: 3px solid #007bff;">
            <h4 style="color: #333; margin: 0 0 8px 0; font-size: 16px;">🎓 Cursos Livres</h4>
            <p style="margin: 0; color: #495057; font-size: 14px;">Explore oportunidades em cursos profissionalizantes e livres</p>
        </div>
        <div style="background-color: #f8f9fa; padding: 12px; border-radius: 6px; border-left: 3px solid #28a745;">
            <h4 style="color: #333; margin: 0 0 8px 0; font-size: 16px;">📚 Consultorias Educacionais</h4>
            <p style="margin: 0; color: #495057; font-size: 14px;">Considere atuar como consultor independente na área educacional</p>
        </div>
        <div style="background-color: #f8f9fa; padding: 12px; border-radius: 6px; border-left: 3px solid #6f42c1;">
            <h4 style="color: #333; margin: 0 0 8px 0; font-size: 16px;">🤝 Outras Instituições</h4>
            <p style="margin: 0; color: #495057; font-size: 14px;">Busque parcerias com outras instituições de ensino da sua região</p>
        </div>
    </div>
</div>

<div style="background-color: #ffffff; padding: 20px; border-radius: 8px; border-left: 4px solid #6c757d; margin-top: 20px;">
    <h3 style="color: #333; margin-top: 0;">📞 Mantenha Contato</h3>
    
    <p style="color: #495057; margin-bottom: 15px;">
        Se tiver dúvidas ou quiser mais informações sobre futuras oportunidades:
    </p>
    
    <div style="background-color: #f8f9fa; padding: 15px; border-radius: 6px;">
        <p style="margin: 5px 0; color: #495057;">
            <strong>📧 Email:</strong> <a href="mailto:parceiros@ensinocerto.com.br" style="color: #007bff; text-decoration: none;">parceiros@ensinocerto.com.br</a>
        </p>
        <p style="margin: 5px 0; color: #495057;">
            <strong>📱 WhatsApp:</strong> <a href="https://wa.me/5511999999999" style="color: #25d366; text-decoration: none;">(11) 99999-9999</a>
        </p>
        <p style="margin: 5px 0; color: #495057;">
            <strong>🕒 Horário:</strong> Segunda a Sexta, das 8h às 18h
        </p>
    </div>
</div>

<div style="margin-top: 30px; padding: 20px; background-color: #e9ecef; border-radius: 8px; text-align: center;">
    <h3 style="color: #495057; margin-top: 0;">🙏 Agradecimento</h3>
    <p style="margin: 0; color: #6c757d; font-size: 16px; line-height: 1.6;">
        Agradecemos sinceramente seu interesse pela Ensino Certo.<br>
        Continuamos torcendo pelo seu sucesso na área educacional!<br>
        <strong>Muito obrigado por considerar nossa empresa.</strong>
    </p>
</div>

<div style="margin-top: 20px; padding: 15px; background-color: #d1ecf1; border-radius: 8px; border: 1px solid #bee5eb; text-align: center;">
    <p style="margin: 0; color: #0c5460; font-size: 14px;">
        <strong>💡 Dica:</strong> Mantenha-se conectado conosco nas redes sociais para ficar por dentro das novidades e futuras oportunidades!
    </p>
</div>
@endsection 