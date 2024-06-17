<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\Category;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    //
    const FOLDER = 'develop';

    public function store(Request $request){
        try {
            $request->validate([
                'name' => 'required',
                'image' => 'required|image|mimes:jpeg,png,jpg,gif',
                'description' => 'required',
                'detail' => 'required|array',
            ]);

//            $logo = $request->hasFile('image');
//
//            if(!$logo){
//                return response()->json([
//                    'success' => false,
//                    'message' => 'Ảnh danh mục không có'
//                ]);
//            }
//
//            $file = $request->file('image');
//            $fileName = $file->getClientOriginalName() . '-' . time() . '.' . rand(1, 1000000);
//
//            $url = Cloudinary::upload($file->getRealPath(), [
//                'folder' => self::FOLDER,
//                'public_id' => $fileName
//            ])->getSecurePath();
//
//            if(!$url){
//                return response()->json([
//                    'success' => false,
//                    'message' => 'Tải file không thành công'
//                ]);
//            }
//
//            $public_id = Cloudinary::getPublicId();
//
//            $result = Category::create([
//                'name' => $request->name,
//                'image' => $url,
//                'public_id' => $public_id
//
//            ]);
//
//            if(!$result){
//                Cloudinary::destroy($public_id);
//                return response()->json([
//                    'success' => false,
//                    'message' => 'Tạo thương hiệu không thành công'
//                ]);
//            }

            return response()->json([
                'oke' => $request->detail,
            ]);

        }catch (\Exception $exception){
            return response()->json([
                'success' => false,
                'message' => $exception->getMessage()
            ]);
        }
    }
}
