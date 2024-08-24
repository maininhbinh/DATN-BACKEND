<?php

namespace App\Http\Controllers\api;

use App\Helpers\ValidatorHelpers;
use App\Http\Controllers\Controller;
use App\Models\Category;
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
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use App\Helpers\ValidatorHelpers as IValidator;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;

class ProductController extends Controller
{

    const FOLDER = 'developer';

    public function index()
    {
        try {
            $products = Product::with([
                'products' => function ($query) {
                    $query
                        ->where('quantity', '>', '1')
                        ->orderBy('quantity', 'desc')
                        ->with(['variants' => function ($query) {
                            $query
                                ->whereHas('variant.category', function ($query) {
                                    $query->join('product_configurations', 'variant_options.id', '=', 'product_configurations.variant_option_id')
                                    ->join('product_items', 'product_items.id', '=', 'product_configurations.product_item_id')
                                    ->join('products', 'products.id', '=', 'product_items.product_id')
                                    ->join('variants', 'variants.id', '=', 'variant_options.variant_id')
                                    ->whereColumn('variants.category_id', 'products.category_id');
                                })
                                ->orderBy('product_configurations.id', 'asc');
                        }]);
                    },
                'category'
            ])
            ->orderBy('id', 'desc')
            ->withSum('products', 'product_items.quantity',)
            ->withSum('orderDetails', 'quantity',)
            ->get();

            return response()->json([
                'success' => true,
                'data' => $products
            ], 200);
        } catch (\Exception $exception) {

            return response()->json([
                'success' => false,
                'message' => $exception->getMessage()
            ]);
        }
    }

    public function update(Request $request, $id){
        try {

            $product = Product::with([
                'category.details.attributes' => function ($query) use ($id) {
                    $query->with(['values' => function ($query) use ($id) {
                        $query->whereHas('products', function ($query) use ($id) {
                            $query->where('id', $id);
                        });
                    }]);
                },
                'category.variants',
                'products' => function ($query){
                    $query
                        ->with(['variants' => function ($query) {
                            $query
                                ->orderBy('product_configurations.id', 'asc')
                                ->join('variants', 'variant_options.variant_id', '=', 'variants.id')
                                ->select('variant_options.*', 'variants.name as variant_name')
                                ->get();
                        }]);
                },
            ])->findOrFail($id);

            $variantModels = Variant::whereHas('variants.products.product', function ($query) use ($id) {
                $query->where('products.id', $id);
            })
            ->with(['variants'])
            ->get();

            $variants = $variantModels->map(function ($item){
                return [
                    'id' => $item->id,
                    'name' => $item->name,
                    'attribute' => $item->variants->map(function ($item) {
                        return [
                            'id' => $item->id,
                            'value' => $item->name,
                        ];
                    }),
                ];
            });

            return  response()->json([
                'success' => true,
                'data' => $product,
                'variants' => $variants
            ]);
        }catch (Exception $exception){
            return response()->json([
                'success' => false,
                'message' => $exception->getMessage()
            ], 500);
        }
    }

    public function featProducts(Request $request)
    {
        try {

            $products = Product::where($request->feat, true)
                ->where('is_active', true)
                ->with([
                    'products' => function ($query) {
                        $query
                            ->where('quantity', '>', '1')
                            ->orderBy('quantity', 'desc')
                            ->with(['variants' => function ($query) {
                                $query
                                    ->whereHas('variant.category', function ($query) {
                                        $query->join('product_configurations', 'variant_options.id', '=', 'product_configurations.variant_option_id')
                                            ->join('product_items', 'product_items.id', '=', 'product_configurations.product_item_id')
                                            ->join('products', 'products.id', '=', 'product_items.product_id')
                                            ->join('variants', 'variants.id', '=', 'variant_options.variant_id')
                                            ->whereColumn('variants.category_id', 'products.category_id');
                                    })
                                    ->orderBy('product_configurations.id', 'asc');
                            }]);
                    },
                    'category'
                ])
                ->orderBy('id', 'desc')
                ->withSum('products', 'product_items.quantity',)
                ->withSum('orderDetails', 'quantity',)
                ->limit(10)
                ->get();

            return response()->json([
                'success' => true,
                'data' => $products
            ], 200);
        } catch (\Exception $exception) {
            return response()->json([
                'success' => false,
                'message' => $exception->getMessage()
            ]);
        }
    }

    public function show(Request $request)
    {

        try {

            $product = Product::where('slug', $request->slug)
                ->where('is_active', true)
                ->with(['galleries'])
                ->with(
                    [
                        'products' => function ($query)  {
                            $query
                                ->where('quantity', '>', '1')
                                ->orderBy('quantity', 'desc')
                                ->with(['variants' => function ($query) {
                                    $query
                                        ->whereHas('variant.category', function ($query) {
                                            $query->join('product_configurations', 'variant_options.id', '=', 'product_configurations.variant_option_id')
                                                ->join('product_items', 'product_items.id', '=', 'product_configurations.product_item_id')
                                                ->join('products', 'products.id', '=', 'product_items.product_id')
                                                ->join('variants', 'variants.id', '=', 'variant_options.variant_id')
                                                ->whereColumn('variants.category_id', 'products.category_id');
                                        })
                                        ->orderBy('product_configurations.id', 'asc')
                                        ->join('variants', 'variant_options.variant_id', '=', 'variants.id')
                                        ->select('variant_options.*', 'variants.name as variant_name')
                                        ->get();
                            }]);
                        },
                        'category',
                        'brand',
                        'details.attributes' => function ($query) use ($request) {
                            $query->with(['values' => function ($query) use ($request) {
                                $query->whereHas('products', function ($query) use ($request) {
                                    $query->where('slug', $request->slug);
                                });
                            }]);
                        }
                    ]
                )
                ->withSum('products', 'product_items.quantity',)
                ->withSum('orderDetails', 'quantity',)
                ->firstOrFail();

            if ($product->products->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Sản phẩm đã hết!'
                ], 404);
            }

            if (!$product) {
                return response()->json([
                    'success' => true,
                    'message' => 'Không thể tìm thấy sản phẩm'
                ], 404);
            }
            return response()->json([
                'success' => true,
                'data' => $product
            ], 200);
        } catch (\Exception $exception) {
            return response()->json([
                'success' => false,
                'message' => $exception->getMessage()
            ]);
        }
    }
    public function store(Request $request)
    {

        $valid = Validator::make(
            $request->all(),
            [
                'thumbnail' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
                "name" => "required|max:70|min:10",
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
                "name.max" => "Tên sản phẩm không được vượt quá 70 ký tự",
                "content" => "Chưa có nội dung giới thiệu sản phẩm",
                "category_id" => "Chưa có danh mục",
                "brand_id" => 'chưa có thương hiệu',
                "is_active" => "Chưa lựa chọn loại hiển thị",
                "product_details" => 'Chưa có thông tin chi tiết',
                "product_items" => "Chưa có biến thể",
            ]
        );

        if ($valid->fails()) {
            return response()->json([
                'success' => false,
                'message' => $valid->errors()
            ], 422);
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
        $product_details = json_decode($request->get('product_details'));
        $product_items = json_decode($request->get('product_items'));
        $gallery = json_decode($request->get('gallery'));

        $product_check = ValidatorHelpers::validatorProductItem($product_items);

        if(!$product_check){
            return response()->json([
                'success' => false,
                'message' => 'Dữ liệu gửi lên không đúng'
            ], 422);
        }

        try {
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
                'public_id' => $public_id,
            ]);

            foreach ($product_items as $item) {
                if (!empty($item)) {

                    $hasFile = isset($item->image);

                    if ($hasFile) {

                        $imageData = $item->image;
                        $imageData = preg_replace('/^data:image\/\w+;base64,/', '', $imageData);
                        $imageData = base64_decode($imageData);

                        $tempImagePath = storage_path('app/temp_image.jpg');
                        file_put_contents($tempImagePath, $imageData);

                        $url_item = Cloudinary::upload($tempImagePath, [
                            'folder' => self::FOLDER,
                            'public_id' => "variant-" . implode('-', array_reduce($item->variants, function ($array, $item) {
                                $array[] = $item->attribute;
                                return $array;
                            }, [])) . "-" . rand(1, 1000000)
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
                        'sku' => $item->sku ?? '',
                        'public_id' => $hasFile ? $public_id : null,
                    ]);

                    foreach ($item->variants as $variantModel) {

                        $variant = \App\Helpers\ValidatorHelpers::validatorName($variantModel->variant);
                        $attribute = \App\Helpers\ValidatorHelpers::validatorName($variantModel->attribute);

                        $variants = Variant::firstOrCreate(
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
                                'variant_id' => $variants->id,
                                'name' => $attribute,
                            ]
                        );

                        $product_item->variants()->attach($variant_option->id);
                    }
                } else {
                    return response()->json([
                        "success" => false,
                        "message" => 'Thêm sản phẩm không thành công'
                    ], 422);
                }
            }

            foreach ($product_details as $detail) {

                ProductDetail::create([
                    'product_id' => $product->id,
                    'detail_id' => $detail->id,
                ]);

                foreach ($detail->attributes as $attribute) {
                    foreach($attribute->values as $value){
                        $name = IValidator::validatorName($value);
                        $value = Value::firstOrCreate(
                            [
                                'name' => $name
                            ],
                            [
                                'attribute_id' => $attribute->id,
                                'name' => $name,
                            ]
                        );

                        ProductValue::create([
                            'product_id' => $product->id,
                            'value_id' => $value->id
                        ]);
                    }
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
                    'public_id' => "$name-$key" . rand(1, 1000000)
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
            ], 200);
        } catch (\Exception $exception) {
            DB::rollBack();
            return response()->json([
                "success" => false,
                "message" => $exception->getMessage()
            ], 500);
        }

    }
    public function filter(Request $request)
    {
        try {
            if ($request->filled('min_price') && $request->filled('max_price') && $request->min_price > $request->max_price) {
                return response()->json(['success' => false, 'message' => 'Sai giá trị'], 400);
            }

            $query = Product::query();

            $query->join('product_items', 'products.id', '=', 'product_items.product_id')
                ->select('products.*', 'product_items.price_sale');

            if ($request->filled('brand')) {
                $query->whereHas('brand', function ($q) use ($request) {
                    $q->where('name', $request->brand);
                });
            }

            if ($request->filled('category')) {
                $query->whereHas('category', function ($q) use ($request) {
                    $q->where('name', $request->category);
                });
            }

            if ($request->filled('min_price') && $request->filled('max_price')) {
                $query->whereBetween('product_items.price_sale', [$request->min_price, $request->max_price]);
            } elseif ($request->filled('min_price')) {
                $query->where('product_items.price_sale', '>=', $request->min_price);
            } elseif ($request->filled('max_price')) {
                $query->where('product_items.price_sale', '<=', $request->max_price);
            }

            $products = $query->with(['products' => function ($query) {
                $query->with(['variants' => function ($query) {
                    $query->orderBy('product_configurations.id', 'asc');
                }]);
            }])->get();

            // Check if products are found


            return response()->json(['success' => true, 'data' => $products]);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function search(Request $request)
    {
        $name = $request->query('name');

        if (empty($name)) {
            return response()->json([
                'error' => 'Query parameter is required'
            ], 400);
        }

        try {
            $products = Product::where('name', 'LIKE', '%' . $name . '%')
                ->with(['products' => function ($query) {
                    $query->with(['variants' => function ($query) {
                        $query->orderBy('product_configurations.id', 'asc');
                    }]);
                }, 'category'])
                ->get();

            return response()->json($products);
        } catch (QueryException $e) {
            return response()->json([
                'error' => 'Database query error',
                'message' => $e->getMessage()
            ], 500);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'An unexpected error occurred',
                'message' => $e->getMessage()
            ], 500);
        }
    }
    public function category(Request $request, $slug)
    {
        try {
            $category = Category::where('slug', $slug)->first();

            if(!$category){
                return response()->json([
                    'success' => false,
                    'message' => 'Không tìm thấy danh mục'
                ]);
            }

            $products = $category
                ->products()
                ->where('is_active', true)
                ->with(
                [
                    'products' => function ($query) {
                        $query
                            ->where('quantity', '>', '1')
                            ->orderBy('quantity', 'desc')
                            ->with(['variants' => function ($query) {
                                $query
                                    ->whereHas('variant.category', function ($query) {
                                        $query->join('product_configurations', 'variant_options.id', '=', 'product_configurations.variant_option_id')
                                            ->join('product_items', 'product_items.id', '=', 'product_configurations.product_item_id')
                                            ->join('products', 'products.id', '=', 'product_items.product_id')
                                            ->join('variants', 'variants.id', '=', 'variant_options.variant_id')
                                            ->whereColumn('variants.category_id', 'products.category_id');
                                    })
                                    ->orderBy('product_configurations.id', 'asc')
                                    ->join('variants', 'variant_options.variant_id', '=', 'variants.id')
                                    ->select('variant_options.*', 'variants.name as variant_name')
                                    ->get();
                            }]);
                    },
                    'category'
                ])
                ->orderBy('id', 'desc')
                ->withSum('products', 'product_items.quantity',)
                ->withSum('orderDetails', 'quantity',)
                ->get();

            return response()->json([
                'success' => true,
                'data' => $products
            ]);
        } catch (QueryException $e) {
            return response()->json([
                'error' => 'Database name error',
                'message' => $e->getMessage()
            ], 500);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'An unexpected error occurred',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
