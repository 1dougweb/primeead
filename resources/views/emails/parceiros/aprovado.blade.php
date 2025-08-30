@extends('emails.layout')

@section('content')
<div style="background-color: #d4edda; padding: 20px; border-radius: 8px; margin-bottom: 20px; border: 1px solid #c3e6cb;">
    <h2 style="color: #155724; margin: 0; font-size: 28px; text-align: center;">
        🎉 Parabéns, {{ $parceiro->nome_completo }}!
    </h2>
    <p style="margin: 10px 0 0 0; color: #155724; text-align: center; font-size: 18px; font-weight: bold;">
        Sua candidatura foi APROVADA!
    </p>
</div>

@if($user && $senha)
<div style="background-color: #ffffff; padding: 20px; border-radius: 8px; border-left: 4px solid #ffc107; margin-bottom: 20px;">
    <h3 style="color: #333; margin-top: 0;">🔐 Suas Credenciais de Acesso</h3>
    
    <div style="background-color: #fff3cd; padding: 15px; border-radius: 6px; margin: 15px 0;">
        <p style="margin: 0; color: #856404; line-height: 1.6;">
            <strong>Importante:</strong> Guarde estas informações em um local seguro. Você precisará delas para acessar o sistema.
        </p>
    </div>

    <table style="width: 100%; border-collapse: collapse; margin: 15px 0;">
        <tr>
            <td style="padding: 8px 0; font-weight: bold; color: #495057; width: 30%;">Email:</td>
            <td style="padding: 8px 0; color: #333;">{{ $user->email }}</td>
        </tr>
        <tr>
            <td style="padding: 8px 0; font-weight: bold; color: #495057;">Senha Temporária:</td>
            <td style="padding: 8px 0; color: #333;">{{ $senha }}</td>
        </tr>
    </table>

    <div style="background-color: #f8f9fa; padding: 15px; border-radius: 6px;">
        <p style="margin: 0; color: #495057; line-height: 1.6;">
            <strong>Próximos passos:</strong>
            <ol style="margin: 10px 0 0 0; padding-left: 20px;">
                <li>Acesse o sistema em <a href="{{ route('login') }}" style="color: #007bff;">{{ route('login') }}</a></li>
                <li>Use as credenciais acima para fazer seu primeiro login</li>
                <li>Por segurança, altere sua senha no primeiro acesso</li>
            </ol>
        </p>
    </div>
</div>
@endif

<div style="background-color: #ffffff; padding: 20px; border-radius: 8px; border-left: 4px solid #28a745;">
    <h3 style="color: #333; margin-top: 0;">🎯 Próximos Passos</h3>
    
    <div style="background-color: #f8f9fa; padding: 15px; border-radius: 6px; margin: 15px 0;">
        <p style="margin: 0; color: #495057; line-height: 1.6;">
            <strong>Bem-vindo à família Ensino Certo!</strong> Agora você faz parte da nossa rede de parceiros comprometidos com a educação de qualidade.
        </p>
    </div>

    <ol style="color: #495057; line-height: 1.8; padding-left: 20px;">
        <li><strong>Aguarde nosso contato:</strong> Nossa equipe entrará em contato em até 2 dias úteis para alinhar os próximos passos.</li>
        <li><strong>Documentação:</strong> Enviaremos o contrato de parceria e documentos necessários.</li>
        <li><strong>Treinamento:</strong> Você receberá acesso ao nosso portal e material de capacitação.</li>
        <li><strong>Suporte completo:</strong> Nossa equipe técnica e comercial estará sempre disponível.</li>
        <li><strong>Início das atividades:</strong> Após a documentação, você já pode começar a prospectar alunos!</li>
    </ol>
</div>

<div style="background-color: #ffffff; padding: 20px; border-radius: 8px; border-left: 4px solid #007bff; margin-top: 20px;">
    <h3 style="color: #333; margin-top: 0;">📋 Seus Dados de Parceria</h3>
    
    <table style="width: 100%; border-collapse: collapse; margin: 15px 0;">
        <tr>
            <td style="padding: 8px 0; font-weight: bold; color: #495057; width: 30%;">Modalidade:</td>
            <td style="padding: 8px 0; color: #333;">
                <span style="background-color: #007bff; color: white; padding: 4px 8px; border-radius: 4px; font-size: 12px;">
                    {{ $parceiro->modalidade_parceria }}
                </span>
            </td>
        </tr>
        <tr>
            <td style="padding: 8px 0; font-weight: bold; color: #495057;">Cidade/Estado:</td>
            <td style="padding: 8px 0; color: #333;">{{ $parceiro->cidade }}/{{ $parceiro->estado }}</td>
        </tr>
        <tr>
            <td style="padding: 8px 0; font-weight: bold; color: #495057;">Data de Aprovação:</td>
            <td style="padding: 8px 0; color: #333;">{{ $parceiro->data_aprovacao->format('d/m/Y H:i') }}</td>
        </tr>
    </table>
</div>

<div style="background-color: #fff3cd; padding: 20px; border-radius: 8px; border: 1px solid #ffeaa7; margin-top: 20px;">
    <h3 style="color: #856404; margin-top: 0;">💡 Benefícios da Parceria</h3>
    
    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
        <div style="background-color: white; padding: 15px; border-radius: 6px;">
            <h4 style="color: #333; margin: 0 0 10px 0; font-size: 16px;">💰 Baixo Investimento</h4>
            <p style="margin: 0; color: #495057; font-size: 14px;">Sem custos altos de implantação</p>
        </div>
        <div style="background-color: white; padding: 15px; border-radius: 6px;">
            <h4 style="color: #333; margin: 0 0 10px 0; font-size: 16px;">🚀 Plataforma Exclusiva</h4>
            <p style="margin: 0; color: #495057; font-size: 14px;">Sistema próprio de gestão</p>
        </div>
        <div style="background-color: white; padding: 15px; border-radius: 6px;">
            <h4 style="color: #333; margin: 0 0 10px 0; font-size: 16px;">📞 Assessoria Completa</h4>
            <p style="margin: 0; color: #495057; font-size: 14px;">Suporte técnico e comercial</p>
        </div>
        <div style="background-color: white; padding: 15px; border-radius: 6px;">
            <h4 style="color: #333; margin: 0 0 10px 0; font-size: 16px;">🎓 Reconhecimento Oficial</h4>
            <p style="margin: 0; color: #495057; font-size: 14px;">EJA autorizado pelo CEE/SP</p>
        </div>
    </div>
</div>

<div style="background-color: #ffffff; padding: 20px; border-radius: 8px; border-left: 4px solid #6f42c1; margin-top: 20px;">
    <h3 style="color: #333; margin-top: 0;">📞 Entre em Contato</h3>
    
    <p style="color: #495057; margin-bottom: 15px;">
        Tem alguma dúvida? Nossa equipe está pronta para ajudar!
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
    <h3 style="color: #495057; margin-top: 0;">🌟 Bem-vindo à Ensino Certo!</h3>
    <p style="margin: 0; color: #6c757d; font-size: 16px; line-height: 1.6;">
        Juntos, vamos transformar vidas através da educação de qualidade.<br>
        <strong>Muito obrigado por fazer parte da nossa missão!</strong>
    </p>
</div>
@endsection 