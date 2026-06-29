<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Continue seus Estudos!</title>
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
        <p>Sentimos sua falta nos desafios recentemente! Para se manter no topo do ranking da sua instituição, lembre-se de continuar completando seus conteúdos e respondendo aos testes.</p>
        <p>A constância é a chave para o aprendizado e para acumular cada vez mais pontos e recompensas na nossa plataforma.</p>

        <div class="btn-container">
            <a href="{{ url('/dashboard') }}" class="btn">Continuar Desafios</a>
        </div>

        <p>Boa sorte nos seus testes!</p>
        
        <div class="footer">
            <p>Este é um e-mail automático enviado por {{ config('app.name') }}.</p>
        </div>
    </div>
</body>
</html>
