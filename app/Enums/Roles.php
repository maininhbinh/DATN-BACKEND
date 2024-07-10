<?php

namespace App\Enums;

enum Roles: int
{
    case ADMIN = 0;
    case USER = 1;
    case STAFF = 2;
}
