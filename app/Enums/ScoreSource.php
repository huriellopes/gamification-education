<?php

declare(strict_types=1);

namespace App\Enums;

use App\Traits\EnumOptions;

enum ScoreSource: string
{
    use EnumOptions;

    public function label(): string
    {
        return match ($this) {
            self::TEST => 'Avaliação',
            self::MATERIAL => 'Material',
            self::ADMIN_ADJUSTMENT => 'Ajuste manual',
        };
    }

    case TEST = 'test';
    case MATERIAL = 'material';
    case ADMIN_ADJUSTMENT = 'admin_adjustment';
}
