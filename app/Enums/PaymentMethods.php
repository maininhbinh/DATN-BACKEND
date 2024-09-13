<?php

namespace App\Enums;

enum PaymentMethods: string
{
    case STRIPE = 'stripe-payment';
    case MOMO = 'momo';
    case VNPAY = 'vnpay';
    case COD = 'COD';

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
            self::COD => 4,
        };
    }

    public static function getValue(PaymentMethods $type): int
    {
        return match ($type) {
            self::STRIPE => self::STRIPE->value,
            self::MOMO => self::MOMO->value,
            self::VNPAY => self::VNPAY->value,
            self::COD => self::COD->value,
        };
    }
}
