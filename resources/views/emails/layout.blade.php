<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    {{-- color-scheme evita que o dark mode dos clientes reescreva as cores e cause texto preto sobre fundo escuro --}}
    <meta name="color-scheme" content="dark light">
    <meta name="supported-color-schemes" content="dark light">
    <title>@yield('title', config('app.name'))</title>
</head>
{{--
    Layout base dos e-mails com a identidade da plataforma (dark zinc/indigo).
    Usa TABELA + estilos INLINE de propósito: muitos clientes (Gmail/Outlook)
    descartam <style> do <head>, o que fazia o texto cair no preto default.
--}}
<body style="margin:0; padding:0; background-color:#0b0b0f; -webkit-text-size-adjust:100%;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color:#0b0b0f;">
        <tr>
            <td align="center" style="padding:40px 16px; font-family:'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;">
                <table role="presentation" width="600" cellpadding="0" cellspacing="0" border="0" style="width:600px; max-width:100%; background-color:#18181b; border:1px solid #27272a; border-radius:16px;">
                    <tr>
                        <td style="padding:32px;">
                            <div style="text-align:center; margin-bottom:28px;">
                                <span style="font-size:22px; font-weight:800; letter-spacing:1px; color:#ffffff;">Gamifica<span style="color:#6366f1;">Edu</span></span>
                            </div>

                            @yield('content')

                            <div style="text-align:center; font-size:12px; color:#71717a; margin-top:32px; border-top:1px solid #27272a; padding-top:16px;">
                                @hasSection('footer')
                                    @yield('footer')
                                @else
                                    Este é um e-mail automático enviado por {{ config('app.name') }}.
                                @endif
                            </div>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
