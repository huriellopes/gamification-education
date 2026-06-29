<?php

declare(strict_types=1);

namespace App\Actions\Support;

use App\Enums\SupportStatus;
use App\Models\Support;

class ReplyToSupportAction
{
    /**
     * Registra a resposta de um chamado e marca como respondido.
     */
    public function __invoke(Support $support, string $reply): Support
    {
        $support->update([
            'reply' => $reply,
            'status' => SupportStatus::ANSWERED,
            'replied_at' => now(),
        ]);

        return $support;
    }
}
