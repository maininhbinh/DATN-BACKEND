<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\Gallery;
use App\Models\Product;
use App\Models\Product_detail;
use App\Models\Product_item;
use App\Models\Product_value;
use App\Models\Value;
use App\Models\Variant;
use App\Models\Variant_option;
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
            $products = Product::with(['products.variants','products.variants.variant'])->get();
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
            $products = Product::where($request->feat, true)->with(['products.variants','products.variants.variant', 'category', 'brand', 'details.attributes','values'])->get();
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
            $product = Product::where('slug', $request->slug)->with(['products.variants','products.variants.variant', 'category', 'brand', 'details.attributes' => function ($query) use ($request){
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
                "variants" => "required",
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
                "variants" => 'Chưa có biến thể',
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
        $variants = json_decode($request->get('variants'));
        $gallery = json_decode($request->get('gallery'));

        if(count($product_items)<1){
            return response()->json([
                "success" => false,
                "message" => 'Chưa có sản phẩm'
            ], 404);
        }

        if(count($variants) < 1){
            return response()->json([
                "success" => false,
                "message" => 'Sản phẩm phải có biến thể'
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

                $variantId  = [];
                $product_index = 0;

                foreach ($variants as $variant) {
                    $name = IValidator::validatorName($variant->name);
                    $variantModel = Variant::firstOrCreate(
                        [
                            'name' => $name
                        ],
                        [
                            'category_id' => $category_id,
                            'name' => $name
                        ]
                    );
                    array_push($variantId, $variantModel);
                }

                foreach ($variants[0]->attribute as $attribute) {

                    $name = IValidator::validatorName($attribute);

                    $variantParent = Variant_option::firstOrCreate(
                        [
                            'name' => $name
                        ],
                        [
                            'variant_id' => $variantId[0]->id,
                            'name' => $name,
                        ]
                    );

                    if (count($variants) > 1) {

                        foreach ($variants[1]->attribute as $value) {
                            if (!empty($value)) {

                                $hasFile = isset($product_items[$product_index]->image);

                                if($hasFile){

                                    $imageData = $product_items[$product_index]->image;
                                    $imageData = preg_replace('/^data:image\/\w+;base64,/', '', $imageData);
                                    $imageData = base64_decode($imageData);

                                    $tempImagePath = storage_path('app/temp_image.jpg');
                                    file_put_contents($tempImagePath, $imageData);

                                    $url_item = Cloudinary::upload($tempImagePath, [
                                        'folder' => self::FOLDER,
                                        'public_id' => "variant-$attribute-".rand(1, 1000000)
                                    ])->getSecurePath();

                                    $public_id = Cloudinary::getPublicId();

                                    unlink($tempImagePath);

                                }

                                $variant_option = Variant_option::firstOrCreate(
                                    [
                                        'name' => $value
                                    ],
                                    [
                                    'variant_id' => $variantId[1]->id,
                                    'name' => $value,
                                    ]
                                );

                                $product_item = Product_item::create([
                                    'product_id' => $product->id,
                                    'price' => $product_items[$product_index]->price,
                                    'price_sale' => $product_items[$product_index]->price_sale,
                                    'image' => $hasFile ? $url_item : null,
                                    'quantity' => $product_items[$product_index]->quantity,
                                    'sku' => $product_items[$product_index]->sku,
                                    'public_id' => $hasFile ? $public_id : null,
                                ]);

                                $product_item->variants()->attach($variantParent->id);
                                $product_item->variants()->attach($variant_option->id);

                                $product_index++;

                            }
                        }

                    } else {

                        $hasFile = isset($product_items[$product_index]->image);

                        if($hasFile){
                            $imageData = $product_items[$product_index]->image;
                            $imageData = preg_replace('/^data:image\/\w+;base64,/', '', $imageData);
                            $imageData = base64_decode($imageData);

                            $tempImagePath = storage_path('app/temp_image.jpg');
                            file_put_contents($tempImagePath, $imageData);

                            $url_item = Cloudinary::upload($tempImagePath, [
                                'folder' => self::FOLDER,
                                'public_id' => "variant-$attribute-".rand(1, 1000000)
                            ])->getSecurePath();

                            $public_id = Cloudinary::getPublicId();

                            unlink($tempImagePath);

                        }

                        $product_item = Product_item::create([
                            'product_id' => $product->id,
                            'price' => $product_items[$product_index]->price,
                            'price_sale' => $product_items[$product_index]->price_sale,
                            'image' => $hasFile ? $url_item : null,
                            'quantity' => $product_items[$product_index]->quantity,
                            'sku' => $product_items[$product_index]->sku,
                            'public_id' => $hasFile ? $public_id : null,
                        ]);

                        $product_item->variants()->attach($variantParent->id);
                        $product_index++;

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

                    Product_value::create([
                        'product_id' => $product->id,
                        'value_id' => $value->id
                    ]);

                    Product_detail::create([
                        'product_id' => $product->id,
                        'detail_id' => $detail->idDetail,
                    ]);
                }
            }

            foreach ($gallery as $item) {
                $image = $item->image;

                $imageData = preg_replace('/^data:image\/\w+;base64,/', '', $image);
                $imageData = base64_decode($imageData);

                $tempImagePath = storage_path('app/temp_image.jpg');
                file_put_contents($tempImagePath, $imageData);

                $url_gallery = Cloudinary::upload($tempImagePath, [
                    'folder' => self::FOLDER,
                    'public_id' => "variant-$attribute-".rand(1, 1000000)
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
