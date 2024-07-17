<?php
namespace App\Enums;


enum PaymentStatuses: string
{
    case PENDING = 'Chờ thanh toán';                    // Chờ thanh toán
    case COMPLETED = 'Đã thanh toán';                   // Đã thanh toán
    case FAILED = 'Thất bại';                           // Thất bại

    public static function getValues(): array
    {
        return array_column(PaymentStatuses::cases(), 'value');
    }

    public static function getOrder(PaymentStatuses $type): int
    {
        return match($type) {
            self::PENDING => 1,
            self::COMPLETED => 2,
            self::FAILED => 3,
        };
    }
}
