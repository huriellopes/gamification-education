<?php

declare(strict_types=1);

namespace App\Http\Controllers\Teacher;

use App\Actions\Content\ImportPdfMaterialAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Teacher\Content\ImportPdfRequest;
use App\Models\Subject;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;
use RuntimeException;

class ImportSubjectPdfController extends Controller
{
    /**
     * Importa um PDF, gera o resumo/conteúdo e cria um material de estudo.
     */
    public function __invoke(ImportPdfRequest $request, Subject $subject, ImportPdfMaterialAction $importPdf): RedirectResponse
    {
        Gate::authorize('manageContent', $subject);

        $file = $request->file('file');

        if (!$file instanceof UploadedFile) {
            throw ValidationException::withMessages([
                'file' => 'Selecione um arquivo PDF.',
            ]);
        }

        // Caminho temporário do upload — o conteúdo é processado e salvo no
        // banco; o arquivo NÃO é persistido no servidor (removido ao final).
        $temporaryPath = $file->getRealPath();

        try {
            $count = $importPdf->execute($subject, $file);
        } catch (RuntimeException $exception) {
            throw ValidationException::withMessages([
                'file' => $exception->getMessage(),
            ]);
        } finally {
            if (is_string($temporaryPath) && is_file($temporaryPath)) {
                @unlink($temporaryPath);
            }
        }

        return to_route('teacher.subjects.show', $subject)
            ->with('success', "Documento importado! {$count} material(is) de leitura e um desafio foram gerados a partir do arquivo.");
    }
}
