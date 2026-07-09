@extends('emails.layout')

@section('title', 'Novo chamado de suporte')

@section('content')
    <h1 style="margin:0 0 16px; color:#ffffff; font-size:20px; font-weight:700; text-align:center;">Novo chamado de suporte recebido!</h1>

    <p style="margin:0 0 16px; color:#d4d4d8; font-size:15px; line-height:1.6;">
        Um usuário enviou uma solicitação de suporte pela plataforma. Veja os detalhes abaixo:
    </p>

    <div style="background-color:#111116; border:1px solid #27272a; border-radius:12px; padding:16px; margin:16px 0; color:#e4e4e7; font-size:14px; line-height:1.8;">
        <div><strong>Remetente:</strong> {{ $sender->name }} ({{ $sender->email }})</div>
        <div><strong>Nível de Acesso (Role):</strong> {{ ucfirst($sender->role->value) }}</div>
        <div><strong>Instituição:</strong> {{ $sender->institution ? $sender->institution->name : 'Nenhuma' }}</div>
        <div><strong>Assunto:</strong> {{ $supportSubject }}</div>
    </div>

    <p style="margin:0 0 8px; color:#d4d4d8; font-size:15px; font-weight:700;">Mensagem enviada:</p>
    <div style="background-color:#09090b; border:1px solid #27272a; border-radius:12px; padding:16px; margin:0 0 16px; color:#f4f4f5; font-size:14px; line-height:1.6; white-space:pre-wrap;">{{ $supportMessage }}</div>

    <p style="margin:0; color:#a1a1aa; font-size:14px; line-height:1.6;">
        Você pode responder a este chamado clicando em "Responder" no seu cliente de e-mail para falar diretamente com o usuário.
    </p>
@endsection

@section('footer')
    Este chamado foi gerado em {{ now()->format('d/m/Y H:i:s') }} pela área de suporte.
@endsection
