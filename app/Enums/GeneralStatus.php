<?php

declare(strict_types=1);

namespace App\Enums;

use App\Traits\EnumOptions;

enum GeneralStatus: int
{
    use EnumOptions;

    public function label(): string
    {
        return match ($this) {
            self::INACTIVE => __('messages.status_inactive'),
            self::ACTIVE => __('messages.status_active'),
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::INACTIVE => 'red',
            self::ACTIVE => 'emerald',
        };
    }

    case INACTIVE = 0;
    case ACTIVE = 1;
}
