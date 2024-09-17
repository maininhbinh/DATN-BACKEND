<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\Attribute;
use App\Models\AttributeCategory;
use App\Models\Category;
use App\Models\Detail;
use App\Models\DetailCategory;
use App\Models\Variant;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class CategoryController extends Controller
{
    //
    const FOLDER = 'develop';

    public function index(Request $request){
        try {
            $categories = Category::orderBy('id', 'DESC')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $categories
            ]);

        }catch (\Exception $exception){
            return response()->json([
                'success' => false,
                'message' => $exception->getMessage()
            ]);
        }
    }

    public function listCategory(Request $request)
    {
        try {
            $categories = Category::orderBy('id', 'DESC')
                ->whereHas('products', function ($query) use ($request) {
                    $query
                    ->whereHas('products', function ($query) use ($request) {
                        $query
                            ->whereHas('variants.variant.category', function ($query) {
                                $query
                                    ->join('product_configurations', 'variant_options.id', '=', 'product_configurations.variant_option_id')
                                    ->join('product_items', 'product_items.id', '=', 'product_configurations.product_item_id')
                                    ->join('products', 'products.id', '=', 'product_items.product_id')
                                    ->join('variants', 'variants.id', '=', 'variant_options.variant_id')
                                    ->whereColumn('variants.category_id', 'products.category_id');
                            })
                            ->where('quantity', '>', 1);
                    });
                })
                ->get();

            return response()->json([
                'success' => true,
                'data' => $categories
            ]);

        }catch (\Exception $exception){
            return response()->json([
                'success' => false,
                'message' => $exception->getMessage()
            ]);
        }
    }

    public function show(Request $request, $id){
        try {
            $category = Category::with(
                [
                    'details'=> function ($query) use ($id) {
                        $query->with(['attributes' => function ($query) use ($id) {
                            $query->whereHas('category', function($query) use ($id){
                                $query->where('categories.id', $id);
                            });
                        }]);
                    },
                    'variants'
                ])
                ->find($id);

            return response()->json([
                'success' => true,
                'data' => $category,
            ], 200);
        }catch (\Exception $exception){
            return response()->json([
                'success' => false,
                'message' => $exception->getMessage()
            ]);
        }
    }

    public function edit($id){
        try {

            $category = Category::with(['details' => function ($query) use ($id) {
                $query->with(['attributes' => function ($query) use ($id) {
                    $query->whereHas('category', function($query) use ($id){
                        $query->where('categories.id', $id);
                    });
                }]);
            }])->find($id);

            if(!$category){
                return response()->json([
                    'success' => false,
                    'message' => 'Không tìm thấy danh mục'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $category
            ], 200);

        }catch (\Exception $exception){

            return response()->json([
                'success' => false,
                'message' => $exception->getMessage()
            ], 500);

        }
    }

    public function update(Request $request, $id){

        $valid = Validator::make(
            $request->all(),
            [
                'name' => 'required',
                'image' => 'image|mimes:jpeg,png,jpg,gif,svg|max:2048',
                'is_active' => 'required'
            ],
            [
                'name' => 'không được để trống',
                'image.image' => 'file phải là ảnh',
            ]
        );

        if($valid->fails()){
            return response()->json([
                'success' => false,
                'message' => $valid->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();

            $category = Category::find($id);
            $newDetails = json_decode($request->get('newDetails'));
            $attributes = json_decode($request->get('attributes'));
            $detailDeletes = json_decode($request->get('detailDeletes'));

            if(!$category){
                return response()->json([
                    'success' => false,
                    'message' => 'Không tìm thấy danh mục'
                ], 404);
            }

            $url = $category->image;
            $public_id = $category->public_id;

            $image = $request->hasFile('image');

            if ($image) {
                $file = $request->file('image');
                $fileName = $file->getClientOriginalName() . '-' . time() . '.' . rand(1, 1000000);
//
                $url = Cloudinary::upload($file->getRealPath(), [
                    'folder' => self::FOLDER,
                    'public_id' => $fileName
                ])->getSecurePath();
//
                if (!$url) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Không thể tải ảnh'
                    ], 500);
                }
//
                $public_id = Cloudinary::getPublicId();
            }
//
            $is_active = (int)$request['is_active'] == 1 ? true : false;

            $newCategory = [
                'name' => $request->get('name'),
                'image' => $url,
                'is_active' => $is_active,
                'public_id' => $public_id
            ];

            $category->update($newCategory);

            foreach ($newDetails as $item) {
                $detail = Detail::firstOrCreate(
                    [
                        'name' => $item->name,
                    ],
                    [
                        'name' => $item->name,
                    ]
                );

                DetailCategory::create([
                    'detail_id' => $detail->id,
                    'category_id' => $id,
                ]);

                foreach ($item->attributes as $value) {
                    $attribute = Attribute::firstOrCreate(
                        [
                            'name' => $value->name
                        ],
                        [
                            'name' => $value->name
                        ]
                    );

                    $attribute->details()->syncWithoutDetaching([$detail->id]);

                    AttributeCategory::create([
                        'attribute_id' => $attribute->id,
                        'category_id' => $id
                    ]);
                }
            }

            $category->details()->detach($detailDeletes);
//
            foreach ($detailDeletes as $item){
                $detail = Detail::find($item);
                if($detail){

                    $category->attributes()->detach($detail->attributes->pluck('id'));
                }
            }

            $attributeDelete = $attributes->delete;
            $attributeAdd = $attributes->add;

            $category->attributes()->detach($attributeDelete);

            foreach ($attributeAdd as $item){
                foreach ($item->attributes as $attribute){
                    $attribute = Attribute::firstOrCreate(
                        [
                            'name' => $attribute
                        ],
                        [
                            'name' => $attribute
                        ]
                    );

                    $attribute->details()->syncWithoutDetaching([$item->id]);

                    AttributeCategory::create([
                        'attribute_id' => $attribute->id,
                        'category_id' => $id
                    ]);
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Cập nhật thành công'
            ], 200);

        }catch (\Exception $exception){
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => $exception->getMessage()
            ], 500);
        }
    }

    public function store(Request $request){
        $valid = Validator::make($request->all(), [
            'name' => 'required|unique:categories,name',
            'is_active' => 'required',
            'detail' => 'required',
            'image' => 'image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ],[
            'name.required' => 'Không được để trống name',
            'name.unique' => 'Danh mục đã tồn tại trong cơ sở dữ liệu',
            'is_active.require' => 'Active cần phải được thể hiện',
            'image.image' => 'File phải là ảnh',
            'image.mimes' => 'Định dạng của logo phải là jpeg, png, jpg hoặc gif'
        ]);

        if($valid->fails()){
            return response()->json([
                'success' => false,
                'message' => $valid->errors()
            ], 422);
        }

        try {

            DB::beginTransaction();

            $detail = json_decode($request->get('detail'));

            if (count($detail) < 1) {
                return response()->json([
                    'success' => false,
                    'message' => "cần ít nhất 1 chi tiết danh mục"
                ], 404);
            }
//
            $file = $request->file('image');
            $fileName = $file->getClientOriginalName() . '-' . time() . '.' . rand(1, 1000000);
//
            $url = Cloudinary::upload($file->getRealPath(), [
                'folder' => self::FOLDER,
                'public_id' => $fileName
            ])->getSecurePath();
//
            if (!$url) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tải ảnh không thành công'
                ], 500);
            }
//
            $public_id = Cloudinary::getPublicId();
            $is_active = $request->get('is_active') ? 1 : 0;
//
            $category = Category::create([
                'name' => $request->name,
                'image' => $url,
                'public_id' => $public_id,
                'is_active' => $is_active
            ]);

            foreach ($detail as $item) {
                $detail = Detail::firstOrCreate(
                    [
                        'name' => $item->name,
                    ],
                    [
                        'name' => $item->name,
                    ]
                );

                $category->details()->attach($detail->id);

                foreach ($item->attributes as $value) {
                    $attribute = Attribute::firstOrCreate(
                        [
                            'name' => $value->name
                        ],
                        [
                            'name' => $value->name
                        ]
                    );

                    $detail->attributes()->syncWithoutDetaching($attribute->id);

                    $category->attributes()->syncWithoutDetaching($attribute->id);
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Category created successfully',
                'data' => $category
            ], 201);

        }catch (\Exception $exception){
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => $exception->getMessage()
            ], 500);

        }
    }

    public function destroy($id){
        try {
            $category = Category::find($id);

            if(!$category){
                return response()->json([
                    'success' => false,
                    'message' => 'Không tìm thấy danh mục'
                ], 404);
            }

            $category->delete();

            return response()->json([
                'success' => true,
                'massage' => 'Xóa danh mục thành công'
            ], 200);

        }catch (ValidationException $exception){
            return response()->json([
                'success' => false,
                'message' => $exception->getMessage()
            ], 500);
        }
    }

    public function page(Request $request, $slug)
    {
        try {
            $category = Category::where('slug', $slug)->first();

            if(!$category){
                return response()->json([
                    'success' => false,
                    'message' => 'Không tìm thấy danh mục'
                ]);
            }

            $productModel = $category->products();

            $paramModel = (array) $request->query();

            foreach ($paramModel as $key => $value) {

                $variantModel = $category->variants()->where('slug', $key)->first();

                if(!$variantModel){
                    continue;
                }

                $queryVariant = explode(',', $value);

                $productModel->whereHas('products.variants', function ($query) use ($queryVariant) {
                    $query->whereIn('slug', $queryVariant);
                });

            }

            if($request->query('price')){
                $price = explode('-', $request->query('price'));
                $minPrice = $price[0];
                $maxPrice = $price[1];
                $productModel->whereHas('products', function ($query) use ($minPrice, $maxPrice) {
                    $query->whereBetween('price', [$minPrice, $maxPrice])
                        ->orWhereBetween('price_sale', [$minPrice, $maxPrice]);
                });
            }

            if($request->boolean('on_sale')){
                $productModel->whereHas('products', function ($query) {
                    $query->whereNotNull('price_sale');
                });
            }

            // Hoặc, nếu bạn cần xử lý các tham số theo dạng mảng liên kết:
            $allParamsArray = [];
            foreach ($paramModel as $key => $value) {
                $allParamsArray[$key] = $value;
            }

            $products = $productModel
                ->where('is_active', true)
                ->whereHas('products', function ($query) use ($request) {
                    $query
                        ->whereHas('variants.variant.category', function ($query) {
                            $query
                                ->join('product_configurations', 'variant_options.id', '=', 'product_configurations.variant_option_id')
                                ->join('product_items', 'product_items.id', '=', 'product_configurations.product_item_id')
                                ->join('products', 'products.id', '=', 'product_items.product_id')
                                ->join('variants', 'variants.id', '=', 'variant_options.variant_id')
                                ->whereColumn('variants.category_id', 'products.category_id');
                        })
                        ->where('quantity', '>', 1);
                })
                ->with([
                    'products' => function ($query) {
                        $query
                            ->whereHas('variants.variant.category', function ($query) {
                                $query
                                    ->join('product_configurations', 'variant_options.id', '=', 'product_configurations.variant_option_id')
                                    ->join('product_items', 'product_items.id', '=', 'product_configurations.product_item_id')
                                    ->join('products', 'products.id', '=', 'product_items.product_id')
                                    ->join('variants', 'variants.id', '=', 'variant_options.variant_id')
                                    ->whereColumn('variants.category_id', 'products.category_id');
                            })
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
                ])
                ->orderBy('id', 'desc')
                ->withSum('products', 'product_items.quantity',)
                ->withSum('orderDetails', 'quantity',)
                ->get();

            $variants = $category
                ->variants()
                ->with(['variants'])
                ->get()
                ->map(function ($item){
                    return [
                        'id' => $item->id,
                        'name' => $item->name,
                        'slug' => $item->slug,
                        'attributes' => $item->variants->map(function ($item) {
                            return [
                                'id' => $item->id,
                                'value' => $item->name,
                                'slug' => $item->slug,
                            ];
                        }),
                    ];
                });

            $maxPrice = $category
                ->join('products', 'categories.id', '=', 'products.category_id')
                ->join('product_items', 'products.id', '=', 'product_items.product_id')
                ->max('product_items.price');

            $minPrice = $category
                ->join('products', 'categories.id', '=', 'products.category_id')
                ->join('product_items', 'products.id', '=', 'product_items.product_id')
                ->selectRaw('MIN(COALESCE(product_items.price_sale, product_items.price)) as min_price')
                ->pluck('min_price')
                ->first();

            return response()->json([
                'success' => true,
                'data' => $products,
                'filter' => [
                    'variants' => $variants,
                    'price' => [
                        'maxPrice' => $maxPrice,
                        'minPrice' => $minPrice,
                    ]
                ],
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
