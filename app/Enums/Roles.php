<?php

namespace App\Enums;

enum Roles: string
{
    case ADMIN = 'admin';
    case USER = 'user';
    case STAFF = 'staff';

    public static function getValues(): array
    {
        return array_column(Roles::cases(), 'value');
    }

    public static function getOrder(Roles $type): int
    {
        return match($type) {
            self::ADMIN => 1,
            self::USER => 2,
            self::STAFF => 3,
        };
    }
}
