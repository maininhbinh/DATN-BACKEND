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

    public static function getOrder(PaymentMethods $type): int
    {
        return match($type) {
            self::COD => 1,
            self::MOMO => 2,
        };
    }
}
