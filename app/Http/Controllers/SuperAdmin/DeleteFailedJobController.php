<?php

declare(strict_types=1);

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Artisan;

class DeleteFailedJobController extends Controller
{
    /**
     * Remove/esquece um job falho (ou limpa todos quando o id é "all").
     */
    public function __invoke(string $id): RedirectResponse
    {
        if ($id === 'all') {
            Artisan::call('queue:flush');
            $msg = 'Histórico de todos os jobs falhos foi limpo!';
        } else {
            Artisan::call('queue:forget', ['id' => $id]);
            $msg = "Job falho ID #{$id} foi removido com sucesso!";
        }

        return back()->with('flash', [
            'success' => $msg,
        ]);
    }
}
