<?php
namespace App\Enums;

enum OrderStatus: string
{
    case PENDING = 'Chờ xử lý';
    case PREPARE = "Đang chuẩn bị";
    case PENDING_PAYMENT = "Chờ thanh toán";
    case READY_TO_PICK = "Sẳn sàng lấy hàng";
    case PICKING = "Đang lây hàng";
    case PICKED = "Đã lấy hàng";
    case STORING = "Đang nhập kho";
    case TRANSPORTING = "Đang vận chuyển";
    case SORTING = "Đang phân loại";
    case DELIVERTING = "Đang giao hàng";
    case DELIVERED = "Đã giao hàng";
    case MONEY_COLLECT_DELIVERING = "Đang tương tác thu tiền với người nhận";
    case DELIVERY_FAILED = "Giao hàng thành công";
    case WAITING_TO_RETURN = "Chờ xác nhận giao lại";
    case RETURN_RECEIVED = "Đã nhận han trả lại nhưng chưa xử lý hoàn trả";
    case REFUNDED = "Đã trả về shop";
    case CANCELLED = "Đơn hàng bị hủy (không xử lý gì thêm)";
    case ON_HOLD = "Đơn hàng bị tạm hoãn";
    case BACKORDERED = "Đơn hàng tái nhập kho";
    case AWAITING_REVIEW = "Đơn hàng đang được xem xét hoặc phê duyệt";
    case PROCESSING_PAYMENT = "Thanh toán đang được xử lý và chưa hoàn tất";
    case COMPLETED_WITH_ISSUES = "Trang thái đã được hoàn thành nhưng có vấn đề cần giải quyết";
    case READY_FOR_PICUP = "Đơn hàng sẵn sàng để khách nhận tại cửa hàng";
}
