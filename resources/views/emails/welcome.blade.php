<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Bem-vindo!</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #0f0f15;
            color: #d4d4d8;
            margin: 0;
            padding: 40px 20px;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            background-color: #18181b;
            border: 1px solid #27272a;
            border-radius: 16px;
            padding: 32px;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.3);
        }
        .header {
            text-align: center;
            margin-bottom: 32px;
        }
        .logo {
            font-size: 24px;
            font-weight: bold;
            color: #6366f1;
            text-transform: uppercase;
            letter-spacing: 2px;
        }
        h1 {
            color: #ffffff;
            font-size: 22px;
            margin-top: 0;
        }
        p {
            line-height: 1.6;
            font-size: 15px;
        }
        .btn-container {
            text-align: center;
            margin: 32px 0;
        }
        .btn {
            background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%);
            color: #ffffff !important;
            text-decoration: none;
            padding: 12px 28px;
            font-size: 14px;
            font-weight: bold;
            border-radius: 12px;
            display: inline-block;
            box-shadow: 0 4px 15px rgba(99, 102, 241, 0.4);
        }
        .credentials {
            background-color: #27272a;
            border: 1px solid #3f3f46;
            border-radius: 12px;
            padding: 16px;
            margin: 24px 0;
        }
        .credentials p {
            margin: 8px 0;
        }
        .footer {
            text-align: center;
            font-size: 12px;
            color: #71717a;
            margin-top: 32px;
            border-top: 1px solid #27272a;
            padding-top: 16px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="logo">{{ config('app.name') }}</div>
        </div>
        <h1>Olá, {{ $user->name }}!</h1>
        <p>Sua conta na plataforma de gamificação foi criada com sucesso! Estamos muito felizes em ter você conosco.</p>

        @if($temporaryPassword)
            <p>Um administrador ou professor configurou seu acesso. Aqui estão suas credenciais temporárias para o primeiro acesso:</p>
            <div class="credentials">
                <p><strong>E-mail:</strong> {{ $user->email }}</p>
                <p><strong>Senha Temporária:</strong> <code style="color: #a5b4fc; font-size: 16px;">{{ $temporaryPassword }}</code></p>
            </div>
            <p style="color: #fca5a5; font-weight: bold;">Importante: Por motivos de segurança, você será solicitado a alterar sua senha assim que realizar o primeiro login.</p>
        @else
            <p>Agora você já pode acessar a plataforma, completar matérias, responder desafios e subir no ranking!</p>
        @endif

        <div class="btn-container">
            <a href="{{ url('/login') }}" class="btn">Acessar Plataforma</a>
        </div>

        <p>Se tiver alguma dúvida, entre em contato com o suporte ou fale com seu professor.</p>
        
        <div class="footer">
            <p>Este é um e-mail automático enviado por {{ config('app.name') }}.</p>
        </div>
    </div>
</body>
</html>
