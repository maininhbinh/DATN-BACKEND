<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\Attribute;
use App\Models\Category;
use App\Models\Detail;
use App\Models\DetailCategory;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;
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

            $categories = Category::orderBy('id', 'DESC')->get();;

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

        }catch (\Exception $exception){
            return response()->json([
                'success' => false,
                'message' => $exception->getMessage()
            ]);
        }
    }

    public function edit($id){
        try {

            $category = Category::with('details.attributes', 'variants')->find($id);

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
                'image' => 'image|mimes:jpeg,png,jpg,gif',
                'is_active' => 'required'
            ],
            [
                'name' => 'không được để trống',
                'image.image' => 'file phải là ảnh'
            ]
        );

        if($valid->fails()){
            return response()->json([
                'success' => false,
                'message' => $valid->errors()
            ]);
        }

        try {
            DB::beginTransaction();

            $category = Category::find($id);

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
                'parent_id' => $request->get('parent_id'),
                'public_id' => $public_id
            ];

            $category->update($newCategory);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Chỉnh sửa danh mục thành công'
            ]);

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
            $parent_id = $request->get('parent_id') ?? null;

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
                'parent_id' => $parent_id,
                'is_active' => $is_active
            ]);

            foreach ($detail as $item) {
                $detail = Detail::create([
                    'name' => $item->name,
                ]);

                DetailCategory::create([
                    'detail_id' => $detail->id,
                    'category_id' => $category->id,
                ]);

                foreach ($item->attribute as $value) {
                    Attribute::create([
                        'detail_id' => $detail->id,
                        'name' => $value->value
                    ]);
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
}
