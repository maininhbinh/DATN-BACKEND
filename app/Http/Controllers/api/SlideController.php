<?php
namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\Slide;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;
use Illuminate\Http\Request;
use Illuminate\Http\Response;


class SlideController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'image' => 'required',
            'image_title' => 'required',
            'is_active' => 'required',
        ]);
        $uploadedFileUrl = Cloudinary::upload($request->file('image')->getRealPath())->getSecurePath();

        $publicId = Cloudinary::getPublicId();
        $slideData = [
            'image_url' => $uploadedFileUrl,
            'public_id' => $publicId,
            'image_title' => $request->image_title,
            'is_active' => $request->is_active,
        ];
        $slide = Slide::create($slideData);

        return response()->json(['message' => 'Image uploaded successfully', 'url' => $uploadedFileUrl]);
    }

    public function show()
    {
        $slides = Slide::all();
        return response()->json($slides);
    }

    public function edit($id)
    {
        try {

            $slider = Slide::findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => $slider
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Đã xảy ra lỗi khi lấy dữ liệu .'
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        $slide = Slide::find($id);

        if (!$slide) {
            return response()->json(['message' => 'Slide not found'], Response::HTTP_NOT_FOUND);
        }

        $request->validate([
            'image_title' => 'required',
            'is_active' => 'required',
        ]);

        $slide = Slide::findOrFail($id); // Tìm slide hoặc trả về lỗi 404 nếu không tìm thấy


        $old_image_url = $slide->image_url;
        $old_public_id = $slide->public_id;

        $image_url = $request->hasFile('image');

        if ($image_url) {
            $file = $request->file('image');
            $fileName = $file->getClientOriginalName() . '-' . time() . '.' . rand(1, 1000000);

            $url = Cloudinary::upload($file->getRealPath(), [
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
        $newData = [
            'image_title' => $request->image_title,
            'image_url' => isset($url) && $url ? $url : $old_image_url,
            'public_id' => isset($public_id) && $public_id ? $public_id : $old_public_id,
            'is_active' => $request->is_active,
        ];

        $slide->update($newData);

        return response()->json([
            'success' => true,
            'message' => 'Cập nhật Slicer thanh công',
            'data' => $slide
        ]);

    }

    public function destroy($id)
    {
        $slide = Slide::find($id);

        if (!$slide) {
            return response()->json(['message' => 'Slide not found'], Response::HTTP_NOT_FOUND);
        }
        Cloudinary::destroy($slide->public_id);
        $slide->delete();

        return response()->json(['data'=>$slide]);
    }
}
