<?php

declare(strict_types=1);

namespace App\Http\Controllers\Support;

use App\Actions\Support\CreateSupportRequestAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Support\SendSupportRequest;
use App\Models\User;
use App\Services\SupportMailService;
use Illuminate\Http\RedirectResponse;

class SendSupportController extends Controller
{
    /**
     * Send support request to super admin.
     */
    public function __invoke(
        SendSupportRequest $request,
        CreateSupportRequestAction $createSupportRequest,
        SupportMailService $supportMail,
    ): RedirectResponse {
        /** @var User $user */
        $user = $request->user();

        $validated = $request->validated();
        $subject = (string) $validated['subject'];
        $message = (string) $validated['message'];

        $createSupportRequest($user, $subject, $message);

        $supportMail->notifySuperAdmin($user, $subject, $message);

        return back()->with('flash', [
            'success' => 'Mensagem de suporte enviada com sucesso! O chamado foi registrado e o administrador foi notificado por e-mail.',
        ]);
    }
}
