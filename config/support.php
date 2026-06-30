<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | Contato de Suporte
    |--------------------------------------------------------------------------
    |
    | Dados de contato do suporte exibidos no painel (links de e-mail e
    | WhatsApp). Centralizados aqui para facilitar a configuração por ambiente.
    |
    */

    // E-mail público de suporte (usado no link mailto:).
    'email' => env('SUPPORT_EMAIL', 'suporte@gamificaedu.com.br'),

    // Telefone/WhatsApp em formato internacional, somente dígitos
    // (usado para montar o link https://wa.me/<phone>).
    'phone' => env('SUPPORT_PHONE', '5511999999999'),

];
