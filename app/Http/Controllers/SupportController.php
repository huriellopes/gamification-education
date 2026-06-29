<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Mail\SupportRequestMail;
use App\Models\Support;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use App\Data\SupportRequestData;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;

class SupportController extends Controller
{
    /**
     * Show the support request page.
     */
    public function index(): \Inertia\Response
    {
        return \Inertia\Inertia::render('Support/Index');
    }

    /**
     * Send support request to super admin.
     */
    public function send(SupportRequestData $data): RedirectResponse
    {
        $user = Auth::user();

        $support = Support::createRequest($user, $data->subject, $data->message);

        $superAdmin = User::getSuperAdmin();
        $toEmail = $superAdmin ? $superAdmin->email : 'admin@gamificaedu.com.br';

        Mail::to($toEmail)->send(new SupportRequestMail(
            $user,
            $data->subject,
            $data->message,
        ));

        return redirect()->back()->with('flash', [
            'success' => 'Mensagem de suporte enviada com sucesso! O chamado foi registrado e o administrador foi notificado por e-mail.',
        ]);
    }
}
