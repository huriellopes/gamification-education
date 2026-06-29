<?php

declare(strict_types=1);

namespace App\Http\Controllers\SuperAdmin\Support;

use App\Http\Controllers\Controller;
use App\Mail\SupportReplyMail;
use App\Models\Support;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class ReplySupportController extends Controller
{
    /**
     * Reply to a support ticket and notify the user.
     */
    public function __invoke(Request $request, Support $support): RedirectResponse
    {
        $validated = $request->validate([
            'reply' => ['required', 'string', 'max:5000'],
        ]);

        $support->submitReply($validated['reply']);

        // Enviar e-mail de resposta em fila
        Mail::to($support->user->email)->send(new SupportReplyMail($support));

        return redirect()->back()->with('flash', [
            'success' => 'Resposta de suporte enviada com sucesso! O usuário foi notificado por e-mail.',
        ]);
    }
}
