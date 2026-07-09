@extends('emails.layout')

@section('title', 'Login Mágico')

@section('content')
    <h1 style="margin:0 0 16px; color:#ffffff; font-size:22px; font-weight:700; text-align:center;">Olá, {{ $user->name }}!</h1>

    <p style="margin:0 0 16px; color:#d4d4d8; font-size:15px; line-height:1.6;">
        Você solicitou um link de acesso instantâneo para a plataforma. Clique no botão abaixo para fazer login diretamente, sem digitar sua senha:
    </p>

    @include('emails.partials.button', ['url' => $url, 'label' => 'Entrar Instantaneamente'])

    <div style="background-color:#111116; border:1px solid #3f3f46; border-radius:12px; padding:16px; margin:24px 0;">
        <p style="margin:0 0 8px; color:#e4e4e7; font-size:13px;"><strong>Atenção:</strong></p>
        <ul style="margin:0; padding-left:20px; color:#a1a1aa; font-size:13px; line-height:1.6;">
            <li>Este link expira em 15 minutos por motivos de segurança.</li>
            <li>Este link só pode ser utilizado uma única vez.</li>
            <li>Se você não solicitou este login, pode ignorar este e-mail com segurança.</li>
        </ul>
    </div>
@endsection
