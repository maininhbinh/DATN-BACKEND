<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;
use Illuminate\Http\Request;
use App\Models\Brand;
use Illuminate\Validation\ValidationException;

class BrandController extends Controller
{
    //
    const FOLDER = 'developer';

    public function store(Request $request){
        try{

            $request->validate([
                'name' => 'required',
                'logo' => 'required|image|mimes:jpeg,png,jpg,gif',
            ],
            [
                'name.required' => 'Vui lòng nhập tên thương hiệu',
                'logo.required' => 'Thương hiệu phải kèm logo',
                'logo.image' => 'Logo phải là file hình ảnh',
                'logo.mimes' => 'Định dạng của logo phải là jpeg, png, jpg hoặc gif',
            ]);

            $logo = $request->hasFile('logo');

            if(!$logo){
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

            if(!$url){
                return response()->json([
                    'success' => false,
                    'message' => 'Tải file không thành công'
                ]);
            }

            $public_id = Cloudinary::getPublicId();

            $result = Brand::create([
               'name' => $request->name,
               'public_id' => $public_id,
               'logo' => $url
            ]);

            if(!$result){
                Cloudinary::destroy($public_id);
                return response()->json([
                    'success' => false,
                    'message' => 'Tạo thương hiệu không thành công'
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Tạo thương hiệu thành công'
            ]);

        }catch (\Exception $exception){
            return response()->json([
                'success' => false,
                'message' => $exception->getMessage()
            ]);
        }catch (ValidationException $exception){
            return response()->json([
                'success' => false,
                'message' => $exception->getMessage()
            ]);
        }
    }
}
