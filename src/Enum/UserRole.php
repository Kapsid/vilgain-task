<?php

declare(strict_types=1);

namespace App\Enum;

enum UserRole: string
{
    case ADMIN = 'admin';
    case AUTHOR = 'author';
    case READER = 'reader';

    public function toSymfonyRole(): string
    {
        return match ($this) {
            self::ADMIN => 'ROLE_ADMIN',
            self::AUTHOR => 'ROLE_AUTHOR',
            self::READER => 'ROLE_READER',
        };
    }
}
