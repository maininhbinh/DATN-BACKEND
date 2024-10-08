<?php
namespace App\Enums;

enum OrderStatus: string
{
    case PENDING = 'Chờ xử lý';
    case PREPARING = "Đang chuẩn bị";
    case READY_FOR_PICKUP = "Đơn hàng sẵn sàng";
    case TRANSPORTING = "Đang vận chuyển";
    case DELIVERTING = "Đang giao hàng";
    case DELIVERED = "Đã giao hàng";
    case COMPLETED = "Hoàn thành";
    case CANCELLED = "Đơn hàng bị hủy";

    public static function getValues(): array
    {
        return array_column(OrderStatus::cases(), 'value');
    }

    public static function getOrder(OrderStatus $type): int
    {
        return match($type) {
            self::PENDING => 1,
            self::PREPARING => 2,
            self::READY_FOR_PICKUP => 3,
            self::TRANSPORTING => 4,
            self::DELIVERTING => 5,
            self::DELIVERED => 6,
            self::COMPLETED => 7,
            self::CANCELLED => 8,
        };
    }

}
