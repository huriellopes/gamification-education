@extends('emails.layout')

@section('title', 'Nova conquista!')

@section('content')
    <div style="text-align:center; margin-bottom:8px;">
        <span style="display:inline-block; background-color:#10b981; background-image:linear-gradient(135deg,#10b981 0%,#059669 100%); color:#ffffff; font-size:11px; font-weight:800; letter-spacing:1px; text-transform:uppercase; padding:4px 12px; border-radius:999px;">Conquista!</span>
    </div>

    <h1 style="margin:0 0 16px; color:#ffffff; font-size:22px; font-weight:700; text-align:center;">Incrível, {{ $user->name }}!</h1>

    <p style="margin:0 0 16px; color:#d4d4d8; font-size:15px; line-height:1.6;">
        Você acaba de alcançar uma nova marca de pontuação na plataforma:
    </p>

    <div style="text-align:center; margin:24px 0;">
        <span style="display:inline-block; color:#34d399; font-size:32px; font-weight:800;">{{ $points }} PONTOS</span>
    </div>

    <p style="margin:0 0 16px; color:#d4d4d8; font-size:15px; line-height:1.6;">
        Seu comprometimento com os estudos e com os desafios está rendendo excelentes frutos. Continue assim para conquistar ainda mais espaço e se consolidar entre os primeiros colocados no ranking global!
    </p>

    @include('emails.partials.button', ['url' => url('/dashboard'), 'label' => 'Ver meu Ranking'])
@endsection
