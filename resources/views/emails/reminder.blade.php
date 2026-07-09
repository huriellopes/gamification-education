@extends('emails.layout')

@section('title', 'Continue seus desafios')

@section('content')
    <h1 style="margin:0 0 16px; color:#ffffff; font-size:22px; font-weight:700; text-align:center;">Olá, {{ $user->name }}!</h1>

    <p style="margin:0 0 16px; color:#d4d4d8; font-size:15px; line-height:1.6;">
        Sentimos sua falta nos desafios recentemente! Para se manter no topo do ranking da sua instituição, lembre-se de continuar completando seus conteúdos e respondendo aos testes.
    </p>

    <p style="margin:0 0 16px; color:#d4d4d8; font-size:15px; line-height:1.6;">
        A constância é a chave para o aprendizado e para acumular cada vez mais pontos e recompensas na nossa plataforma.
    </p>

    @include('emails.partials.button', ['url' => url('/dashboard'), 'label' => 'Continuar Desafios'])

    <p style="margin:0; color:#d4d4d8; font-size:15px; line-height:1.6;">
        Boa sorte nos seus testes!
    </p>
@endsection
