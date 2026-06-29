<?php

declare(strict_types=1);

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Spatie\DeletedModels\Models\DeletedModel;

class RestoreDeletedModelController extends Controller
{
    /**
     * Restaura um registro previamente excluído.
     */
    public function __invoke(string $id): RedirectResponse
    {
        DeletedModel::findOrFail($id)->restore();

        return back()->with('success', 'Registro restaurado com sucesso!');
    }
}
