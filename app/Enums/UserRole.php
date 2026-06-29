<?php

declare(strict_types=1);

namespace App\Enums;

use App\Traits\EnumOptions;

enum UserRole: string
{
    use EnumOptions;

    /**
     * Retorna a tradução amigável para o papel do usuário.
     */
    public function label(): string
    {
        return match ($this) {
            self::SUPER_ADMIN => __('messages.role_super_admin'),
            self::ADMIN => __('messages.role_admin'),
            self::TEACHER => __('messages.role_teacher'),
            self::STUDENT => __('messages.role_student'),
        };
    }

    /**
     * Retorna a cor correspondente para badge visual.
     */
    public function color(): string
    {
        return match ($this) {
            self::SUPER_ADMIN => 'purple',
            self::ADMIN => 'indigo',
            self::TEACHER => 'sky',
            self::STUDENT => 'amber',
        };
    }

    case SUPER_ADMIN = 'super_admin';
    case ADMIN = 'admin';
    case TEACHER = 'teacher';
    case STUDENT = 'student';
}
