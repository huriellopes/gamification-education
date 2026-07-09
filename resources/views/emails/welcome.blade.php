@extends('emails.layout')

@section('title', 'Bem-vindo ao ' . config('app.name'))

@section('content')
    <h1 style="margin:0 0 16px; color:#ffffff; font-size:22px; font-weight:700; text-align:center;">Olá, {{ $user->name }}!</h1>

    <p style="margin:0 0 16px; color:#d4d4d8; font-size:15px; line-height:1.6;">
        Sua conta na plataforma de gamificação foi criada com sucesso! Estamos muito felizes em ter você conosco.
    </p>

    @if ($temporaryPassword)
        <p style="margin:0 0 16px; color:#d4d4d8; font-size:15px; line-height:1.6;">
            Um administrador ou professor configurou seu acesso. Aqui estão suas credenciais temporárias para o primeiro acesso:
        </p>
        <div style="background-color:#111116; border:1px solid #27272a; border-radius:12px; padding:16px; margin:16px 0; color:#e4e4e7; font-size:14px; line-height:1.7;">
            <div><strong>E-mail:</strong> {{ $user->email }}</div>
            <div><strong>Senha Temporária:</strong> <code style="color:#a5b4fc; font-size:16px;">{{ $temporaryPassword }}</code></div>
        </div>
        <p style="margin:0 0 16px; color:#fca5a5; font-size:15px; line-height:1.6; font-weight:700;">
            Importante: por motivos de segurança, você será solicitado a alterar sua senha assim que realizar o primeiro login.
        </p>
    @else
        <p style="margin:0 0 16px; color:#d4d4d8; font-size:15px; line-height:1.6;">
            Agora você já pode acessar a plataforma, completar matérias, responder desafios e subir no ranking!
        </p>
    @endif

    @include('emails.partials.button', ['url' => url('/login'), 'label' => 'Acessar Plataforma'])

    <p style="margin:0; color:#d4d4d8; font-size:15px; line-height:1.6;">
        Se tiver alguma dúvida, entre em contato com o suporte ou fale com seu professor.
    </p>
@endsection
