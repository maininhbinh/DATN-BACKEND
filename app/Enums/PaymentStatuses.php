<?php
namespace App\Enums;

enum PaymentStatuses: int
{
    case PENDING = 0;                   // Chờ thanh toán
    case COMPLETED = 1;                 // Đã thanh toán
    case FAILED = 2;                    // Thất bại
}
