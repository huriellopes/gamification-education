<?php

declare(strict_types=1);

namespace App\Enums;

use App\Traits\EnumOptions;

enum SupportStatus: string
{
    use EnumOptions;

    public function label(): string
    {
        return match ($this) {
            self::PENDING => 'Pendente',
            self::ANSWERED => 'Respondido',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::PENDING => 'amber',
            self::ANSWERED => 'emerald',
        };
    }

    case PENDING = 'pending';
    case ANSWERED = 'answered';
}
