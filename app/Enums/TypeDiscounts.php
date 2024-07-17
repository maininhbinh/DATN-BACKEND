<?php
namespace App\Enums;

enum TypeDiscounts: string
{
    case Percent = 'percent';
    case Fixed = 'fixed';

    public static function getValues(): array
    {
        return array_column(TypeDiscounts::cases(), 'value');
    }


}
