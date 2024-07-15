<?php
namespace App\Enums;

enum TypeDiscounts: string
{
    case None = 'none';
    case Percent = 'Percent';
    case Fixed = 'Fixed';

    public static function getValues(): array
    {
        return array_column(TypeDiscounts::cases(), 'value');
    }
}
