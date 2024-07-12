<?php
namespace App\Enums;

enum PaymentMethods: string
{
    case COD = 'Thanh toán khi nhận hàng';
    case MOMO = 'Thanh toán bằng MOMO';

    public static function getValues(): array
    {
        return array_column(PaymentMethods::cases(), 'value');
    }
}
