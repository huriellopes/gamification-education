@extends('emails.layout')

@section('title', 'Sua solicitação foi respondida')

@section('content')
    <h1 style="margin:0 0 16px; color:#ffffff; font-size:20px; font-weight:700; text-align:center;">Sua solicitação de suporte foi respondida!</h1>

    <p style="margin:0 0 16px; color:#d4d4d8; font-size:15px; line-height:1.6;">
        Olá, {{ $support->user->name }}. O administrador respondeu à sua mensagem de suporte. Veja os detalhes abaixo:
    </p>

    <div style="background-color:#111116; border:1px solid #27272a; border-radius:12px; padding:16px; margin:16px 0; color:#e4e4e7; font-size:14px; line-height:1.7;">
        <div style="margin-bottom:8px;"><strong>Assunto:</strong> {{ $support->subject }}</div>
        <div style="margin-bottom:4px;"><strong>Sua mensagem:</strong></div>
        <div style="background-color:#09090b; border:1px solid #27272a; border-radius:8px; padding:12px; color:#f4f4f5; font-size:14px; line-height:1.6; white-space:pre-wrap;">{{ $support->message }}</div>
    </div>

    <p style="margin:0 0 8px; color:#d4d4d8; font-size:15px; font-weight:700;">Resposta do administrador:</p>
    <div style="background-color:#0b1220; border:1px solid #1e3a8a; border-radius:12px; padding:16px; margin:0 0 16px; color:#f4f4f5; font-size:14px; line-height:1.6; white-space:pre-wrap;">{{ $support->reply }}</div>

    <p style="margin:0; color:#a1a1aa; font-size:14px; line-height:1.6;">
        Se tiver mais dúvidas ou precisar de suporte adicional, entre em contato novamente pelo painel da plataforma.
    </p>
@endsection

@section('footer')
    Esta resposta foi gerada automaticamente pelo sistema em {{ now()->format('d/m/Y H:i:s') }}.
@endsection
