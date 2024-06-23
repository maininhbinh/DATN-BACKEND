<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Product_detail;
use App\Models\Product_item;
use App\Models\Value;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class ProductController extends Controller
{

    const FOLDER = 'developer';
    public function create(Request $request){
        try{
            $request->validate([
                'thumbnail' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
                "name" => "required|max:155|min:10",
                "content" => "required",
                "category_id" => "required",
                'brand_id' => "required",
                "active" => "required",
                "detail" => "required",
                "product_variant" => "required",
            ],
            [
                "thumbnail" => "Sản phẩm phải có ảnh đại diện",
                "thumbnail.image" => 'thumbnail phải là annh',
                "thumbnail.mimes" => 'định dạng cu thumbnail là jpeg, png, jpg, gif',
                "name" => "Trường name phải bắt buộc",
                "content" => "Chưa có giới thiệu sản phẩm",
                "category_id" => "Chưa có category",
                "brand_id" => 'chưa có thương hiệu',
                "active" => "Chưa lựa chọn loại hiển thị",
                "detail" => 'Chưa có thông tin chi tiết',
                "product_variant" => 'Chưa có biến thể',
            ]);

            $thumbnail = $request->file('thumbnail');
            $fileName = $thumbnail->getClientOriginalName() . '-' . time() . '.' . rand(1, 1000000);
            $discount = $request->get("discount") ? $request->get("discount") : null;
            $type_discount = $request->get("type_discount") ? $request->get("type_discount") : null;
//
            $detail = json_decode($request->get('detail'));
            $product_variant = json_decode($request->get('product_variant'));

//
            if(count($detail)<1){
                return response()->json([
                    "success" => false,
                    "message" => 'Chưa có thông tin chi tiết'
                ], 404);
            }

            if(count($product_variant) < 1){
                return response()->json([
                    "success" => false,
                    "message" => 'Sản phẩm phải có biến thể'
                ], 404);
            }

            $url = Cloudinary::upload($thumbnail->getRealPath(), [
                'folder' => self::FOLDER,
                'public_id' => $fileName
            ])->getSecurePath();

            $public_id = Cloudinary::getPublicId();

            $name = $request->get("name");
            $content = $request->get("content");
            $category_id = $request->get("category_id");
            $brand_id = $request->get("brand_id");
            $active = $request->get("active");

            $product = Product::create([
                'thumbnail' => $url,
                'public_id' => $public_id,
                'name' => $name,
                'content' => $content,
                'category_id' => $category_id,
                'discount' => $discount,
                'type_discount' => $type_discount,
                'brand_id' => $brand_id,
                'active' => $active,
            ]);

            if(!$product){
                return response()->json([
                    "success" => false,
                    "message" => 'tạo sản phẩm thất bại'
                ]);
            }

            foreach ($detail as $value) {
                foreach($value->value as $item){
                   $valueModel = Value::create([
                        'value' => $item,
                        'attribute_id' => $value->attribute,
                   ]);

                   Product_detail::create([
                       'product_id' => $product->id,
                       'detail_id' => $value->detail,
                       'value_id' => $valueModel->id
                   ]);
                }
            }



            foreach ($product_variant as $model){
                $image = $model->image;
                $galleryName = $thumbnail->getClientOriginalName() . '-' . time() . '.' . rand(1, 1000000);
                $urlGallery = Cloudinary::upload($image->getRealPath(), [
                    'folder' => self::FOLDER,
                    'public_id' => $galleryName
                ])->getSecurePath();

                $public_id = Cloudinary::getPublicId();
                Product_item::create([
                    'product_id' => $product->id,
                    'price' => $model->price,
                    'quantity' => $model->quantiry,
                    'sku' => $model->sku,
                    'image' => $urlGallery,
                    'public_id' => $public_id
                ]);
            }

        }
        catch (ValidationException $exception){
            return response()->json([
                "success" => false,
                "message" => $exception->getMessage()
            ], 500);
        }catch (\Exception $exception){
            return response()->json([
                "success" => false,
                "message" => $exception->getMessage()
            ], 500);
        }
    }
}
