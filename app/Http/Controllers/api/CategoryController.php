<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\Attribute;
use App\Models\Category;
use App\Models\Detail;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
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

            $category = Category::where('active', true)->with('details.attributes')->find($id);

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
        
       var_dump($request->all());
     die();

        try {
            $request->validate([
                'name' => 'required',
                'image' => 'required|image|mimes:jpeg,png,jpg,gif',
                'active' => 'required'
            ]);

            
            $image = $request->hasFile('image');

            if (!$image) {
                return response()->json([
                    'success' => false,
                    'message' => 'Ảnh danh mục không có'
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
                    'message' => 'Không thể tải ảnh'
                ], 500);
            }
//
            $public_id = Cloudinary::getPublicId();
            $active = $request->get('active') ? 1 : 0;

            $category = Category::find($id);

            if(!$category){
                return response()->json([
                    'success' => false,
                    'message' => 'Không tìm thấy danh mục'
                ], 404);
            }

            $newCategory = [
                'name' => $request->get('name'),
                'image' => $url,
                'active' => $active,
                'parent_id' => $request->get('parent_id'),
                'public_id' => $public_id
            ];

            $category->update($newCategory);

            return response()->json([
                'success' => true,
                'message' => 'Chỉnh sửa danh mục thành công'
            ]);

        }catch (\Exception $exception){
            return response()->json([
                'success' => false,
                'message' => $exception->getMessage()
            ], 500);
        }catch (ValidationException $exception){
            return response()->json([
                'success' => false,
                'message' => $exception->getMessage()
            ], 500);
        }
    }

    public function store(Request $request){
      
        try {

            $request->validate([
                'name' => 'required|unique:categories,name',
                'active' => 'required',
                'detail' => 'required',
                'image' => 'image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            ],[
                'name.required' => 'Không được để trống name',
                'name.unique' => 'Danh mục đã tồn tại trong cơ sở dữ liệu',
                'active.require' => 'Active cần phải được thể hiện',
                'image.image' => 'File phải là ảnh',
                'image.mimes' => 'Định dạng của logo phải là jpeg, png, jpg hoặc gif'
            ]);

            $image = $request->hasFile('image');
            $detail = json_decode($request->get('detail'));
            $parent_id = $request->get('parent_id') ?? null;

            if (count($detail) < 1) {
                return response()->json([
                    'success' => false,
                    'message' => "cần ít nhất 1 chi tiết danh mục"
                ], 404);
            }

            if (!$image) {
                return response()->json([
                    'success' => false,
                    'message' => 'Ảnh danh mục không có'
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
            $active = $request->get('active') ? 1 : 0;
//
            $category = Category::create([
                'name' => $request->name,
                'image' => $url,
                'public_id' => $public_id,
                'parent_id' => $parent_id,
                'active' => $active
            ]);

            foreach ($detail as $item) {
                $detail = Detail::create([
                    'category_id' => $category->id,
                    'name' => $item->name,
                ]);

                foreach ($item->attribute as $value) {

                    $attribute = Attribute::create([
                        'detail_id' => $detail->id,
                        'name' => $value->value
                    ]);
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Category created successfully',
                'data' => $category
            ], 201);
           

        } catch (QueryException $e) {

            return response()->json([
                'success' => false,
                'message' => 'Lỗi cơ sở dữ liệu: ' . $e->getMessage()
            ], 500);

        }catch(ValidationException $exception){

            return response()->json([
                'success' => false,
                'message' => $exception->getMessage()
            ], 500);

        }catch (\Exception $exception){

            return response()->json([
                'success' => false,
                'message' => $exception->getMessage()
            ], 500);

        }
    }

    public function destroy($id){
        var_dump($id); die();
        try {
            $category = Category::find($id);

            if(!$category){
                return response()->json([
                    'success' => false,
                    'message' => 'Không tìm thấy danh mục'
                ]);
            }

            $category->delete();

            return response()->json([
                'success' => true,
                'massage' => 'Xóa danh mục thành công'
            ]);

        }catch (ValidationException $exception){
            return response()->json([
                'success' => false,
                'message' => $exception->getMessage()
            ], 500);
        }
    }
}
