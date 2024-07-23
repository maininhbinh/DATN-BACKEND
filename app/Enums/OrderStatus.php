<?php
namespace App\Enums;

enum OrderStatus: string
{
    case PENDING = 'Chờ xử lý';
    case PREPARE = "Đang chuẩn bị";
    case TRANSPORTING = "Đang vận chuyển";
    case DELIVERTING = "Đang giao hàng";
    case DELIVERED = "Đã giao hàng";
    case CANCELLED = "Đơn hàng bị hủy";

    public static function getValues(): array
    {
        return array_column(OrderStatus::cases(), 'value');
    }

    public static function getOrder(OrderStatus $type): int
    {
        return match($type) {
            self::PENDING => 1,
            self::PREPARE => 2,
            self::TRANSPORTING => 3,
            self::DELIVERTING => 4,
            self::DELIVERED => 5,
            self::CANCELLED => 6,
        };
    }

}
