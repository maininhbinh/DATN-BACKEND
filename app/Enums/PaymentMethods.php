<?php

namespace App\Enums;

enum PaymentMethods: string
{
    case STRIPE = 'Thanh toán qua Stripe GateWay';
    case MOMO = 'Thanh toán bằng MOMO';
    case VNPAY = 'Thanh toán bằng VNPay';

    public static function getValues(): array
    {
        return array_column(PaymentMethods::cases(), 'value');
    }

    public static function getOrder(PaymentMethods $type): int
    {
        return match ($type) {
            self::STRIPE => 1,
            self::MOMO => 2,
            self::VNPAY => 3,
        };
    }
}
