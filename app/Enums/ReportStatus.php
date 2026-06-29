<?php

declare(strict_types=1);

namespace App\Enums;

use App\Traits\EnumOptions;

enum ReportStatus: string
{
    use EnumOptions;

    public function label(): string
    {
        return match ($this) {
            self::PENDING => 'Pendente',
            self::COMPLETED => 'Concluído',
            self::FAILED => 'Falhou',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::PENDING => 'amber',
            self::COMPLETED => 'emerald',
            self::FAILED => 'red',
        };
    }

    case PENDING = 'pending';
    case COMPLETED = 'completed';
    case FAILED = 'failed';
}
