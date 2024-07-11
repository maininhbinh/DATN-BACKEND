<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\Gallery;
use App\Models\Product;
use App\Models\ProductDetail;
use App\Models\ProductItem;
use App\Models\ProductValue;
use App\Models\Value;
use App\Models\Variant;
use App\Models\VariantOption;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Helpers\Validator as IValidator;

class ProductController extends Controller
{

    const FOLDER = 'developer';

    public function index(){
        try {

            $products = Product::with(['products' => function ($query){
                $query->with(['variants' => function ($query) {
                    $query->orderBy('product_configurations.id', 'asc');
                }]);
            }, 'category'])->get();

            return response()->json([
                'success' => true,
                'data' => $products
            ], 200);

        }catch (\Exception $exception){

            return response()->json([
                'success' => false,
                'message' => $exception->getMessage()
            ]);

        }
    }

    public function featProducts(Request $request){
        try{
            $products = Product::where($request->feat, true)->with(['products.variants'])->get();
            return response()->json([
                'success' => true,
                'data' => $products
            ], 200);
        }catch (\Exception $exception){
            return response()->json([
                'success' => false,
                'message' => $exception->getMessage()
            ]);
        }
    }

    public function show(Request $request){

        try {
            $product = Product::where('slug', $request->slug)->with(['products.variants', 'category', 'brand', 'details.attributes' => function ($query) use ($request){
                $query->with(['values' => function($query) use ($request) {
                    $query->whereHas('products', function ($query) use ($request) {
                        $query->where('slug', $request->slug);
                    });
                }]);
            }])->firstOrFail();

            if(!$product){
                return response()->json([
                    'success' => true,
                    'message' => 'Không thể tìm thấy sản phẩm'
                ], 404);
            }
            return response()->json([
                'success' => true,
                'data' => $product
            ], 200);
        }catch (\Exception $exception){
            return response()->json([
                'success' => false,
                'message' => $exception->getMessage()
            ]);
        }
    }
    public function store(Request $request){

        $valid = Validator::make($request->all(),[
            'thumbnail' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
            "name" => "required|max:155|min:10",
            "content" => "required",
            "category_id" => "required",
            'brand_id' => "required",
            "is_active" => "required",
            "product_details" => "required",
            "product_items" => "required",
        ],
            [
                "thumbnail" => "Sản phẩm phải có ảnh đại diện",
                "thumbnail.image" => 'thumbnail phải là ảnh',
                "thumbnail.mimes" => 'định dạng cu thumbnail là jpeg, png, jpg, gif',
                "name" => "Trường name phải bắt buộc",
                "name.min" => "Tên sản phẩm phải hơn 10 ký tự",
                "name.max" => "Tên sản phẩm không được vượt quá 155 ký tự",
                "content" => "Chưa có nội dung giới thiệu sản phẩm",
                "category_id" => "Chưa có danh mục",
                "brand_id" => 'chưa có thương hiệu',
                "is_active" => "Chưa lựa chọn loại hiển thị",
                "product_details" => 'Chưa có thông tin chi tiết',
                "product_items" => "Chưa có biến thể",
            ]
        );

        if($valid->fails()){
            return response()->json([
                'success' => false,
                'message' => $valid->errors()
            ], 200);
        }

        $name = $request->get("name");
        $content = $request->get("content");
        $category_id = $request->get("category_id");
        $brand_id = $request->get("brand_id");
        $is_active = $request->get("is_active") == 1 ? 1 : 0;
        $is_host_deal = $request->get("is_hot_deal") == 1 ? 1 : 0;
        $is_good_deal = $request->get("is_good_deal") == 1 ? 1 : 0;
        $is_new = $request->get("is_new") == 1 ? 1 : 0;
        $is_show_home = $request->get("is_show_home") == 1 ? 1 : 0;
        $type_discount = $request->get("type_discount") ? $request->get("type_discount") : null;
        $discount = $request->get("discount") ? $request->get("discount") : null;
        $product_details = json_decode($request->get('product_details'));
        $product_items = json_decode($request->get('product_items'));
        $gallery = json_decode($request->get('gallery'));

        if(count($product_items)<1){
            return response()->json([
                "success" => false,
                "message" => 'Chưa có sản phẩm'
            ], 404);
        }

        try{
            DB::beginTransaction();

            $thumbnail = $request->file('thumbnail');
            $fileName = $thumbnail->getClientOriginalName() . '-' . time() . '.' . rand(1, 1000000);

            $url = Cloudinary::upload($thumbnail->getRealPath(), [
                'folder' => self::FOLDER,
                'public_id' => $fileName
            ])->getSecurePath();

            $public_id = Cloudinary::getPublicId();

            $product = Product::create([
                'thumbnail' => $url,
                'name' => $name,
                'content' => $content,
                'category_id' => $category_id,
                'brand_id' => $brand_id,
                'is_active' => $is_active,
                'is_host_deal' => $is_host_deal,
                'is_good_deal' => $is_good_deal,
                'is_new' => $is_new,
                'is_show_home' => $is_show_home,
                'type_discount' => $type_discount,
                'discount' => $discount,
                'public_id' => $public_id,
            ]);

            foreach ($product_items as $item) {
                if (!empty($item)) {

                    $hasFile = isset($item->image);

                    if($hasFile){

                        $imageData = $item->image;
                        $imageData = preg_replace('/^data:image\/\w+;base64,/', '', $imageData);
                        $imageData = base64_decode($imageData);

                        $tempImagePath = storage_path('app/temp_image.jpg');
                        file_put_contents($tempImagePath, $imageData);

                        $url_item = Cloudinary::upload($tempImagePath, [
                            'folder' => self::FOLDER,
                            'public_id' => "variant-".implode('-', array_reduce($item->variants, function($array, $item){
                                    $array[] = $item->attribute;
                                    return $array;
                                }, []))."-".rand(1, 1000000)
                        ])->getSecurePath();

                        $public_id = Cloudinary::getPublicId();

                        unlink($tempImagePath);

                    }

                    $product_item = ProductItem::create([
                        'product_id' => $product->id,
                        'price' => $item->price,
                        'price_sale' => $item->price_sale,
                        'image' => $hasFile ? $url_item : null,
                        'quantity' => $item->quantity,
                        'sku' => $item->sku,
                        'public_id' => $hasFile ? $public_id : null,
                    ]);

                    foreach ($item->variants as $variant) {
                        $variant = \App\Helpers\Validator::validatorName($variant->variant);
                        $attribute = \App\Helpers\Validator::validatorName($variant->attribute);

                        $variantModel = Variant::firstOrCreate(
                            [
                                'name' => $variant
                            ],
                            [
                                'category_id' => $category_id,
                                'name' => $variant
                            ]
                        );


                        $variant_option = VariantOption::firstOrCreate(
                            [
                                'name' => $attribute
                            ],
                            [
                                'variant_id' => $variantModel->id,
                                'name' => $attribute,
                            ]
                        );

                        $product_item->variants()->attach($variant_option->id);
                    }

                }else{
                    return response()->json([
                        "success" => false,
                        "message" => 'Thêm sản phẩm không thành công'
                    ], 500);
                }
            }

            foreach ($product_details as $detail) {
                foreach ($detail->values as $value) {
                    $name = IValidator::validatorName($value);
                    $value = Value::firstOrCreate(
                        [
                            'name' => $name
                        ],
                        [
                            'attribute_id' => $detail->id,
                            'name' => $name,
                        ]
                    );

                    ProductValue::create([
                        'product_id' => $product->id,
                        'value_id' => $value->id
                    ]);

                    ProductDetail::create([
                        'product_id' => $product->id,
                        'detail_id' => $detail->idDetail,
                    ]);
                }
            }

            foreach ($gallery as $key => $item) {
                $image = $item->image;

                $imageData = preg_replace('/^data:image\/\w+;base64,/', '', $image);
                $imageData = base64_decode($imageData);

                $tempImagePath = storage_path('app/temp_image.jpg');
                file_put_contents($tempImagePath, $imageData);

                $url_gallery = Cloudinary::upload($tempImagePath, [
                    'folder' => self::FOLDER,
                    'public_id' => "$name-$key".rand(1, 1000000)
                ])->getSecurePath();

                $public_id = Cloudinary::getPublicId();

                unlink($tempImagePath);

                Gallery::create([
                    'product_id' => $product->id,
                    'image' => $url_gallery,
                    'public_id' => $public_id,
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Product added successfully!',
                'data' => $product->id,
            ]);

        }catch (\Exception $exception){
            DB::rollBack();
            return response()->json([
                "success" => false,
                "message" => $exception->getMessage()
            ], 500);
        }
    }
}
