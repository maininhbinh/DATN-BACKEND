<?php

namespace App\Enums;

enum Roles: int
{
    case ADMIN = 0;
    case USER = 1;
    case STAFF = 2;

    public static function getValues(): array
    {
        return array_column(Roles::cases(), 'value');
    }
}
