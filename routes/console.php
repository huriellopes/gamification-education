<?php

declare(strict_types=1);

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

/*
|--------------------------------------------------------------------------
| Agendamento
|--------------------------------------------------------------------------
|
| As classes de command vivem em app/Console/Commands (auto-registradas).
| Aqui apenas registramos QUANDO cada uma roda.
|
*/

Schedule::command('app:send-study-reminders')->dailyAt('09:00');

Schedule::command('logs:prune')->dailyAt('01:00');
