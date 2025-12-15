<?php

declare(strict_types=1);

namespace App\Enum;

enum UserRole: string
{
    case BLOGGER = 'blogger';
    case ADMIN = 'admin';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
