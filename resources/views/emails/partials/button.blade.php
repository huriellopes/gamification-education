{{-- Botão de ação com estilo inline (cor sólida + gradiente como reforço). --}}
<div style="text-align:center; margin:28px 0;">
    <a href="{{ $url }}" style="display:inline-block; background-color:{{ $color ?? '#4f46e5' }}; background-image:linear-gradient(135deg, {{ $from ?? '#6366f1' }} 0%, {{ $color ?? '#4f46e5' }} 100%); color:#ffffff; text-decoration:none; padding:14px 32px; border-radius:12px; font-weight:700; font-size:15px;">{{ $label }}</a>
</div>
