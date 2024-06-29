<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;
use Illuminate\Http\Request;
use App\Models\Brand;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class BrandController extends Controller
{
    //
    const FOLDER = 'developer';
    public function index()
    {
        try {
            $items = Brand::orderBy('created_at', 'desc')->get();
            return response()->json([
                'sucsess' => true,
                'data' => $items
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Đã xảy ra lỗi khi lấy dữ liệu.'
            ], 500);
        }
    }
    public function store(Request $request){

        $valid = Validator::make(
            $request->all(),
            [
                'name' => 'required',
                'logo' => 'required|image|mimes:jpeg,png,jpg,gif',
            ],
            [
                'name.required' => 'Vui lòng nhập tên thương hiệu',
                'logo.required' => 'Thương hiệu phải kèm logo',
                'logo.image' => 'Logo phải là file hình ảnh',
                'logo.mimes' => 'Định dạng của logo phải là jpeg, png, jpg hoặc gif',
            ]
        );

        if ($valid->fails()) {
            return response()->json([
                'success' => false,
                'message' => $valid->errors()
            ]);
        }

        try {


            $logo = $request->hasFile('logo');

            if (!$logo) {
                return response()->json([
                    'success' => false,
                    'message' => 'Logo thượng hiệu không có'
                ]);
            }

            $file = $request->file('logo');
            $fileName = $file->getClientOriginalName() . '-' . time() . '.' . rand(1, 1000000);

            $url = Cloudinary::upload($file->getRealPath(), [
                'folder' => self::FOLDER,
                'public_id' => $fileName
            ])->getSecurePath();

            if (!$url) {
                throw new \Error('upload ảnh không thành công');
            }

            $public_id = Cloudinary::getPublicId();

            $result = Brand::create([
                'name' => $request->name,
                'public_id' => $public_id,
                'logo' => $url
            ]);

            if (!$result) {
                Cloudinary::destroy($public_id);
                return response()->json([
                    'success' => false,
                    'message' => 'Tạo thương hiệu không thành công'
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Tạo thương hiệu thành công',
                'data' => $result
            ]);

        } catch (\Exception $exception) {
            return response()->json([
                'success' => false,
                'message' => $exception->getMessage()
            ]);
        }
    }
    public function edit($id)
    {
        try {
            // Tìm thương hiệu theo ID
            $brand = Brand::findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => $brand
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Đã xảy ra lỗi khi lấy dữ liệu thương hiệu.'
            ], 500);
        }
    }
    public function update(Request $request, $id)
    {
        $valid = Validator::make(
            $request->all(),
            [
                'name' => 'required',
                'logo' => 'image|mimes:jpeg,png,jpg,gif',
            ],
            [
                'name.required' => 'Vui lòng nhập tên thương hiệu',
                'logo.image' => 'Logo phải là file hình ảnh',
                'logo.mimes' => 'Định dạng của logo phải là jpeg, png, jpg hoặc gif',
            ]
        );

        if ($valid->fails()) {
            return response()->json([
                'success' => false,
                'message' => $valid->errors()
            ]);
        }
        try {

            // Tìm thương hiệu theo ID
            $brand = Brand::find($id);
            if (!$brand) {
                return response()->json([
                    'success' => false,
                    'message' => 'Không tìm thấy thương hiệu'
                ], 404);
            }


            $old_url = $brand->logo;
            $old_public_id = $brand->public_id;


            $logo = $request->hasFile('logo');

            if ($logo) {
                $file = $request->file('logo');
                $fileName = $file->getClientOriginalName() . '-' . time() . '.' . rand(1, 1000000);

                $url = Cloudinary::upload($file->getRealPath(), [
                    'folder' => self::FOLDER,
                    'public_id' => $fileName
                ])->getSecurePath();

                if (!$url) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Tải file không thành công'
                    ], 500);
                }

                $public_id = Cloudinary::getPublicId();
            }

            // Cập nhật các thuộc tính của thương hiệu
            $newBrandData = [
                'name' => $request->name,
                'logo' => isset($url) && $url ? $url : $old_url,
                'public_id' => isset($public_id) && $public_id ? $public_id : $old_public_id,
            ];

            $brand->update($newBrandData);

            return response()->json([
                'success' => true,
                'message' => 'Cập nhật thương hiệu thành công',
                'data' => $brand
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }


    public function destroy($id)
    {
        try {
            $brand = Brand::findOrFail($id);

            Cloudinary::destroy($brand->public_id);
            $brand->delete();

            return response()->json([
                'success' => true,
                'message' => 'Xóa thương hiệu thành công'
            ], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy thương hiệu'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Đã xảy ra lỗi khi xóa thương hiệu.'
            ], 500);
        }
    }
}
