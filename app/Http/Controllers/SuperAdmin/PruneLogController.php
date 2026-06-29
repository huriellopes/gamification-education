<?php

declare(strict_types=1);

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Jobs\PruneLogsJob;
use Illuminate\Http\RedirectResponse;

class PruneLogController extends Controller
{
    /**
     * Despacha o job de limpeza de logs antigos (mantém os últimos 3 dias).
     */
    public function __invoke(): RedirectResponse
    {
        dispatch(new PruneLogsJob());

        return back()->with('flash', [
            'success' => 'O job de limpeza de logs antigos foi enviado para a fila de processamento!',
        ]);
    }
}
