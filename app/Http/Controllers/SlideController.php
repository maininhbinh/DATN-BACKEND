<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;


use App\Models\Slide;

use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;


class SlideController extends Controller
{
    public function createSlides(Request $request)
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

    public function getSlides()
    {
        $slides = Slide::all();
        return response()->json($slides);
    }

    public function update(Request $request, $id)
    {
        $slide = Slide::find($id);

        if (!$slide) {
            return response()->json(['message' => 'Slide not found'], Response::HTTP_NOT_FOUND);
        }

        $request->validate([
            'image' => 'required',
            'image_title' => 'required',
            'is_active' => 'required',
        ]);

        $slide = Slide::findOrFail($id); // Tìm slide hoặc trả về lỗi 404 nếu không tìm thấy

        $slide->update($request->all()); // Cập nhật slide với dữ liệu từ request

        return response()->json([
            'message' => 'Slide updated successfully!',
            'slide' => $slide
        ]);

        return response()->json(['data'=>$slide]);
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
