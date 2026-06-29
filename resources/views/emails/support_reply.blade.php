<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Resposta de Suporte</title>
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
            color: #ef4444;
            text-transform: uppercase;
            letter-spacing: 2px;
        }
        h1 {
            color: #ffffff;
            font-size: 20px;
            margin-top: 0;
        }
        p {
            line-height: 1.6;
            font-size: 15px;
            color: #a1a1aa;
        }
        .details {
            background-color: #27272a;
            border: 1px solid #3f3f46;
            border-radius: 12px;
            padding: 16px;
            margin: 24px 0;
        }
        .details p {
            margin: 8px 0;
            font-size: 14px;
            color: #e4e4e7;
        }
        .message-box {
            background-color: #09090b;
            border-left: 4px solid #6366f1;
            padding: 16px;
            border-radius: 4px 12px 12px 4px;
            color: #f4f4f5;
            font-family: inherit;
            white-space: pre-wrap;
            margin: 16px 0;
            line-height: 1.6;
        }
        .reply-box {
            background-color: #111827;
            border-left: 4px solid #10b981;
            padding: 16px;
            border-radius: 4px 12px 12px 4px;
            color: #f4f4f5;
            font-family: inherit;
            white-space: pre-wrap;
            margin: 16px 0;
            line-height: 1.6;
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
            <div class="logo">Suporte {{ config('app.name') }}</div>
        </div>
        <h1>Sua solicitação de suporte foi respondida!</h1>
        <p>Olá, {{ $support->user->name }}. O administrador respondeu à sua mensagem de suporte. Veja os detalhes abaixo:</p>

        <div class="details">
            <p><strong>Assunto:</strong> {{ $support->subject }}</p>
            <p><strong>Sua Mensagem:</strong></p>
            <div class="message-box">{{ $support->message }}</div>
        </div>

        <p><strong>Resposta do Administrador:</strong></p>
        <div class="reply-box">{{ $support->reply }}</div>

        <p>Se tiver mais dúvidas ou precisar de suporte adicional, entre em contato novamente através do painel da plataforma.</p>
        
        <div class="footer">
            <p>Esta resposta foi gerada automaticamente pelo sistema em {{ now()->format('d/m/Y H:i:s') }}.</p>
        </div>
    </div>
</body>
</html>
