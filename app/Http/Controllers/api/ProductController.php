<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class ProductController extends Controller
{
    public function createProduct(Request $request){
        try{
            $request->validate([
                'thumbnail' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
                "name" => "required",
                "content" => "required",
                "category_id" => "required",
                "parameter" => "required",
                "product_variant" => "required",
                "in_active" => "required"
            ],
            [
                "thumbnail" => "Sản phẩm phải có ảnh đại diện",
                "name" => "Trường name phải bắt buộc",
                "content" => "Chưa có giới thiệu sản phẩm",
                "category_id" => "Chưa có category",
                "parameter" => 'Chưa có thông tin chi tiết',
                "product_variant" => 'Chưa có biến thể',
                "in_active" => "Chưa lựa chọn loại hiển thị"
            ]
            );




        }catch (\Exception $exception){
            return response()->json([
                "success" => false,
                "message" => $exception->getMessage()
            ]);
        }catch (ValidationException $exception){
            return response()->json([
                "success" => false,
                "message" => $exception->getMessage()
            ]);
        }
    }
}
