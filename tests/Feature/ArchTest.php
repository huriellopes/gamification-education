<?php

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| Testes de arquitetura
|--------------------------------------------------------------------------
|
| Codificam as convenções do projeto para que o CI trave a erosão do padrão
| automaticamente (em vez de depender de disciplina/revisão manual).
|
*/

arch('todo o app usa strict types')
    ->expect('App')
    ->toUseStrictTypes();

arch('nenhuma função de debug em produção')
    ->expect(['dd', 'dump', 'ray', 'var_dump', 'var_export', 'die'])
    ->not->toBeUsed();

arch('controllers são de ação única (__invoke)')
    ->expect('App\Http\Controllers')
    ->toHaveMethod('__invoke')
    ->ignoring('App\Http\Controllers\Controller');

arch('a camada de serviço é agnóstica a HTTP')
    ->expect('App\Services')
    ->not->toUse(['Illuminate\Http\Request', 'Inertia\Inertia']);

arch('actions não renderizam respostas HTTP')
    ->expect('App\Actions')
    ->not->toUse('Inertia\Inertia');

arch('form requests estendem a base do framework')
    ->expect('App\Http\Requests')
    ->toExtend('Illuminate\Foundation\Http\FormRequest')
    ->ignoring('App\Http\Requests\Concerns');

arch('policies têm o sufixo Policy')
    ->expect('App\Policies')
    ->toHaveSuffix('Policy')
    ->ignoring('App\Policies\Concerns');

arch('enums são enums')
    ->expect('App\Enums')
    ->toBeEnums();

arch('models vivem em App\\Models')
    ->expect('App\Models')
    ->toBeClasses();
