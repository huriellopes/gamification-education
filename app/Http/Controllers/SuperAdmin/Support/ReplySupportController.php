<?php

declare(strict_types=1);

namespace App\Http\Controllers\SuperAdmin\Support;

use App\Actions\Support\ReplyToSupportAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\SuperAdmin\Support\ReplySupportRequest;
use App\Models\Support;
use App\Services\SupportMailService;
use Illuminate\Http\RedirectResponse;

class ReplySupportController extends Controller
{
    /**
     * Reply to a support ticket and notify the user.
     */
    public function __invoke(ReplySupportRequest $request, Support $support, ReplyToSupportAction $reply, SupportMailService $mailService): RedirectResponse
    {
        $reply($support, $request->validated()['reply']);

        $mailService->notifyReply($support);

        return back()->with('flash', [
            'success' => 'Resposta de suporte enviada com sucesso! O usuário foi notificado por e-mail.',
        ]);
    }
}
