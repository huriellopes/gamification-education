<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Jobs\PruneLogsJob;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('logs:prune')]
#[Description('Prune Laravel log files keeping the last 3 days (including today)')]
class PruneLogsCommand extends Command
{
    public function handle(): int
    {
        dispatch(new PruneLogsJob());

        $this->info('Limpeza de logs enfileirada.');

        return self::SUCCESS;
    }
}
