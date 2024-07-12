<?php
namespace App\Helpers;

use Laravel\Sanctum\PersonalAccessToken;

class AuthHelpers{
    static public function CheckAuth($token)
    {
        // Kiểm tra token
        if (!$token) {
            return false;
        }

        // Xác thực token
        $accessToken = PersonalAccessToken::findToken($token);

        if (!$accessToken || $accessToken->expires_at > now()) {
            return false;
        }

        // Lấy user từ token
        $user = $accessToken->tokenable;

        // Tiếp tục xử lý với user đã xác thực
        return $user;
    }
}
