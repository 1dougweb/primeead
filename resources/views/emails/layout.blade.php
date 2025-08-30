<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <title>EJA Admin</title>
</head>
<body style="margin: 0; padding: 0; font-family: Arial, sans-serif; background-color: #f4f4f4; -webkit-font-smoothing: antialiased; font-size: 16px; line-height: 1.4; -ms-text-size-adjust: 100%; -webkit-text-size-adjust: 100%;">
    <table role="presentation" border="0" cellpadding="0" cellspacing="0" class="body" style="border-collapse: separate; width: 100%; background-color: #f4f4f4;">
        <tr>
            <td>&nbsp;</td>
            <td style="display: block; margin: 0 auto; max-width: 600px; padding: 10px;">
                <div style="background: #ffffff; border-radius: 3px; padding: 20px;">
                    {!! $content !!}
                    
                    @if(isset($trackingCode))
                        <!-- Pixel de rastreamento para saber quando o email foi aberto -->
                        <img src="{{ route('admin.email-campaigns.track-open', $trackingCode) }}" alt="" width="1" height="1" style="display: none;">
                    @endif
                    
                    @if(isset($unsubscribe_url))
                        <!-- Footer com link de descadastro -->
                        <div style="margin-top: 30px; padding-top: 20px; border-top: 1px solid #eee;">
                            <p style="font-size: 12px; color: #666; text-align: center; margin-top: 20px; padding: 10px;">
                                Se você não deseja mais receber nossos emails, <a href="{{ $unsubscribe_url }}" style="color: #666;">clique aqui para cancelar sua inscrição</a>.
                            </p>
                        </div>
                    @endif
                </div>
            </td>
            <td>&nbsp;</td>
        </tr>
    </table>
</body>
</html> 