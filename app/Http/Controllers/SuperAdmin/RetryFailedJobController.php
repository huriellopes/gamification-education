<?php

declare(strict_types=1);

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Artisan;

class RetryFailedJobController extends Controller
{
    /**
     * Reprocessa um job falho (ou todos quando o id é "all").
     */
    public function __invoke(string $id): RedirectResponse
    {
        if ($id === 'all') {
            Artisan::call('queue:retry all');
            $msg = 'Todos os jobs falhos foram reiniciados!';
        } else {
            Artisan::call('queue:retry', ['id' => $id]);
            $msg = "Job falho ID #{$id} foi colocado de volta na fila com sucesso!";
        }

        return back()->with('flash', [
            'success' => $msg,
        ]);
    }
}
