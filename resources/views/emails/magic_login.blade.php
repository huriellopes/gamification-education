<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Login Mágico</title>
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
            text-align: center;
        }
        .header {
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
            text-align: left;
        }
        .btn-container {
            text-align: center;
            margin: 32px 0;
        }
        .btn {
            background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%);
            color: #ffffff !important;
            text-decoration: none;
            padding: 14px 32px;
            font-size: 15px;
            font-weight: bold;
            border-radius: 12px;
            display: inline-block;
            box-shadow: 0 4px 15px rgba(99, 102, 241, 0.4);
        }
        .warning {
            background-color: #27272a;
            border: 1px solid #3f3f46;
            border-radius: 12px;
            padding: 16px;
            margin: 24px 0;
            font-size: 13px;
            color: #a1a1aa;
            text-align: left;
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
            <div class="logo">LOGIN MÁGICO</div>
        </div>
        <h1>Olá, {{ $user->name }}!</h1>
        <p>Você solicitou um link de acesso instantâneo para a plataforma. Clique no botão abaixo para fazer login diretamente sem digitar sua senha:</p>
        
        <div class="btn-container">
            <a href="{{ $url }}" class="btn">Entrar Instantaneamente</a>
        </div>

        <div class="warning">
            <p><strong>Atenção:</strong></p>
            <ul>
                <li>Este link expira em 15 minutos por motivos de segurança.</li>
                <li>Este link só pode ser utilizado uma única vez.</li>
                <li>Se você não solicitou este login, pode ignorar este e-mail com segurança.</li>
            </ul>
        </div>

        <div class="footer">
            <p>Este é um e-mail automático enviado por {{ config('app.name') }}.</p>
        </div>
    </div>
</body>
</html>
