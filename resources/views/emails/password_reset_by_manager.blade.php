@extends('emails.layout')

@section('title', 'Sua senha foi redefinida — ' . config('app.name'))

@section('content')
    <h1 style="margin:0 0 16px; color:#ffffff; font-size:22px; font-weight:700; text-align:center;">Olá, {{ $user->name }}!</h1>

    <p style="margin:0 0 16px; color:#d4d4d8; font-size:15px; line-height:1.6;">
        Informamos que sua senha de acesso foi <strong>redefinida por um {{ $managerRoleLabel }}</strong> da plataforma.
        Utilize a senha temporária abaixo para entrar novamente:
    </p>

    <div style="background-color:#111116; border:1px solid #27272a; border-radius:12px; padding:16px; margin:16px 0; color:#e4e4e7; font-size:14px; line-height:1.7;">
        <div><strong>E-mail:</strong> {{ $user->email }}</div>
        <div><strong>Senha Temporária:</strong> <code style="color:#a5b4fc; font-size:16px;">{{ $temporaryPassword }}</code></div>
    </div>

    <p style="margin:0 0 16px; color:#fca5a5; font-size:15px; line-height:1.6; font-weight:700;">
        Importante: por motivos de segurança, você será solicitado a alterar sua senha assim que realizar o próximo login.
    </p>

    @include('emails.partials.button', ['url' => url('/login'), 'label' => 'Acessar Plataforma'])

    <p style="margin:0; color:#d4d4d8; font-size:15px; line-height:1.6;">
        Se você não esperava esta alteração, entre em contato com o suporte imediatamente.
    </p>
@endsection
