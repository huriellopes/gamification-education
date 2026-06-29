<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Meta Alcançada!</title>
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
            color: #10b981;
            text-transform: uppercase;
            letter-spacing: 2px;
        }
        h1 {
            color: #ffffff;
            font-size: 24px;
            margin-top: 0;
        }
        p {
            line-height: 1.6;
            font-size: 15px;
            text-align: left;
        }
        .badge {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: #ffffff;
            font-size: 32px;
            font-weight: bold;
            padding: 16px 32px;
            border-radius: 50px;
            display: inline-block;
            margin: 24px 0;
            box-shadow: 0 4px 20px rgba(16, 185, 129, 0.4);
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
            <div class="logo">CONQUISTA!</div>
        </div>
        <h1>Incrível, {{ $user->name }}!</h1>
        <p>Você acaba de alcançar uma nova marca de pontuação na plataforma:</p>
        
        <div class="badge">
            {{ $points }} PONTOS
        </div>

        <p>Seu comprometimento com os estudos e com os desafios está rendendo excelentes frutos. Continue assim para conquistar ainda mais espaço e se consolidar entre os primeiros colocados no ranking global!</p>

        <div class="btn-container">
            <a href="{{ url('/dashboard') }}" class="btn">Ver meu Ranking</a>
        </div>

        <div class="footer">
            <p>Este é um e-mail automático enviado por {{ config('app.name') }}.</p>
        </div>
    </div>
</body>
</html>
