<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;
use App\Models\User;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

/**
 * @OA\Get(
 *      path="/user/profile",
 *      summary="Protected Resource",
 *      description="User",
 *      security={{ "BearerAuth": {} }},
 *      tags={"User"},
 *      @OA\Response(
 *          response=200,
 *          description="Successful retrieval of protected resource",
 *          @OA\JsonContent(
 *              @OA\Property(property="data", type="string", example="A protected resource")
 *          )
 *      ),
 *      @OA\Response(
 *          response=401,
 *          description="Unauthorized"
 *      )
 * )
 */

class UserController extends Controller
{

    public function index(){
        try {
            $items = User::orderBy('created_at', 'desc')->get();
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
    public function profile(Request $request)
    {
        try {

            $user = $request->user();

            return response()->json([
                'success' => true,
                'user' => $user,
            ], 200);

        } catch (\Exception $e) {

            return response()->json([
                'success' => true,
                'message' => 'Máy chủ không hoạt động',
            ], 500);
        }
    }

    public function edit($id)
    {
        try {

            $user = User::findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => $user
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Đã xảy ra lỗi khi lấy dữ liệu thương hiệu.'
            ], 500);
        }
    }
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'username' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:255',
            'county' => 'nullable|string|max:255',
            'district' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:255',
            'role_id' => 'nullable|integer',
            'is_active' => 'nullable|integer',
        ], [
            'image.image' => 'Hình ảnh phải là file hình ảnh',
            'image.mimes' => 'Định dạng của hình ảnh phải là jpeg, png, jpg hoặc gif',
        ]);

        $validatedData['password'] = bcrypt($request->password);

        // Upload image if exists
        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $fileName = $file->getClientOriginalName() . '-' . time() . '.' . rand(1, 1000000);

            $url = Cloudinary::upload($file->getRealPath(), [
                'folder' => 'users',  // Replace 'users' with your desired folder
                'public_id' => $fileName
            ])->getSecurePath();

            if (!$url) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tải file không thành công'
                ]);
            }

            $public_id = Cloudinary::getPublicId();

            $validatedData['image'] = $url;
            $validatedData['public_id'] = $public_id;
            $validatedData['is_active'] = (int)$request->get("is_active");
        }

        $user = User::create($validatedData);

        return response()->json([
            'success' => true,
            'data' => $user,
        ], 201);
    }

    public function update(Request $request, $id)
    {
        try {
            // Validate request data
            $validatedData = $request->validate([
                'username' => 'sometimes|required|string|max:255',
                'email' => 'sometimes|required|string|email|max:255|unique:users,email,' . $id,
                'password' => 'sometimes|required|string|min:8',
                'image' => 'nullable|image|mimes:jpeg,png,jpg,gif',
                'phone' => 'nullable|string|max:20',
                'address' => 'nullable|string|max:255',
                'county' => 'nullable|string|max:255',
                'district' => 'nullable|string|max:255',
                'city' => 'nullable|string|max:255',
                'role_id' => 'nullable|integer',
                'is_active' => 'nullable|integer',
            ], [
                'image.image' => 'Hình ảnh phải là file hình ảnh',
                'image.mimes' => 'Định dạng của hình ảnh phải là jpeg, png, jpg hoặc gif',
            ]);

            // Find user
            $user = User::findOrFail($id);

            // Update user fields
            if ($request->has('password')) {
                $validatedData['password'] = bcrypt($request->password);
            }

            // Upload image if exists
            if ($request->hasFile('image')) {
                // Delete the old image from Cloudinary
                if ($user->public_id) {
                    Cloudinary::destroy($user->public_id);
                }

                // Upload new image
                $file = $request->file('image');
                $fileName = $file->getClientOriginalName() . '-' . time() . '.' . rand(1, 1000000);

                $url = Cloudinary::upload($file->getRealPath(), [
                    'folder' => 'users',  // Replace 'users' with your desired folder
                    'public_id' => $fileName
                ])->getSecurePath();

                if (!$url) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Tải file không thành công'
                    ]);
                }

                $public_id = Cloudinary::getPublicId();

                $validatedData['image'] = $url;
                $validatedData['public_id'] = $public_id;
            }

            $user->update($validatedData);

            return response()->json([
                'success' => true,
                'data' => $user,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Máy chủ không hoạt động',
            ], 500);
        }
    }
    public function delete($id)
    {
        try {
            $user = User::findOrFail($id);
            if ($user->public_id) {
                Cloudinary::destroy($user->public_id);
            }
            $user->delete();
            return response()->json([
                'success' => true,
                'message' => 'Xóa người dùng thành công',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Máy chủ không hoạt động',
            ], 500);
        }
    }
}
