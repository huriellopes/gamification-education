<?php

declare(strict_types=1);

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Spatie\DeletedModels\Models\DeletedModel;

class SuperAdminDeletedModelController extends Controller
{
    public function restore(Request $request, $id): RedirectResponse
    {
        $deletedModel = DeletedModel::findOrFail($id);

        $deletedModel->restore();

        return redirect()->back()->with('success', 'Registro restaurado com sucesso!');
    }
}
