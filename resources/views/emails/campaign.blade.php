@extends('emails.layout')

@section('content')
    <div style="font-family: Arial, sans-serif; line-height: 1.6;">
        {!! $conteudo !!}
    </div>
    
    <!-- Pixel de rastreamento para saber quando o email foi aberto -->
    <img src="{{ route('admin.email-campaigns.track-open', $trackingCode) }}" alt="" width="1" height="1" style="display: none;">
@endsection

@section('footer')
    <div style="margin-top: 30px; padding-top: 20px; border-top: 1px solid #eee;">
        <p style="font-size: 12px; color: #666; text-align: center; margin-top: 20px; padding: 10px;">
            Se você não deseja mais receber nossos emails, <a href="{{ route('admin.email-campaigns.unsubscribe', $trackingCode) }}" style="color: #666;">clique aqui para cancelar sua inscrição</a>.
        </p>
    </div>
@endsection 