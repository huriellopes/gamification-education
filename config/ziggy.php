<?php

declare(strict_types=1);

/*
 * Ziggy: rotas nomeadas expostas ao JavaScript.
 *
 * Visitantes recebem apenas o grupo "guest" (ver resources/views/app.blade.php e
 * HandleInertiaRequests::ziggy()) — sem isso, qualquer pessoa anônima via no
 * HTML o mapa completo de URIs de admin/super-admin. Usuários autenticados
 * recebem a lista completa; por isso os logins bem-sucedidos usam
 * Inertia::location() (recarga completa) em vez de navegação SPA.
 */
return [
    'groups' => [
        'guest' => [
            'dashboard',
            'legal.*',
            'login',
            'logout',
            'register',
            'magic-login.*',
            'password.*',
            'two-factor.login',
            'two-factor.login.store',
            'verification.*',
        ],
    ],
];
