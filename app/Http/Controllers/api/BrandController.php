<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\Brands;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class BrandController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $item = Brands::all();
            if ($item->isEmpty()) {
                return response()->json(
                    [
                        'success' => false,
                        'result' => [
                            'message' => "Không có dữ liệu"
                        ]
                    ],
                    404
                );
            }
            return response()->json([
                'success' => true,
                'result' => [
                    'data' => $item
                ]
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => true,
                'result' => [
                    'message' => $e
                ]
            ], 500);
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $request->validate([
                'name' => 'required|string|max:255|unique:brands,name',
                'logo' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048', // Kiểm tra logo là file hình ảnh và các điều kiện khác
            ]);

            // Lấy thông tin hình ảnh từ request
            $logo = $request->file('logo');
            $ext = $logo->getClientOriginalExtension(); // Lấy đuôi mở rộng của file
            $imageName = time() . '.' . $ext; // Đặt tên cho hình ảnh dựa trên thời gian hiện tại và đuôi mở rộng
            $logo->move(public_path('upload'), $imageName); // Di chuyển hình ảnh vào thư mục public/upload

            // Tạo mới brand với thông tin từ request
            $item = Brands::create([
                'name' => $request->name,
                'logo' => '/upload/' . $imageName, // Lưu đường dẫn của hình ảnh vào cơ sở dữ liệu
            ]);

            return response()->json([
                'success' => true,
                'result' => [
                    'data' => $item
                ]
            ], 201);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'result' => [
                    'message' => 'Có lỗi validate dữ liệu',
                    'errors' => $e->errors()
                ]
            ], 422);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'result' => [
                    'message' => "Lỗi máy chủ"
                ]
            ], 500);
        }
    }


    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        try {
            // Tìm brand theo ID
            $brand = Brands::findOrFail($id);

            return response()->json([
                'success' => true,
                'result' => [
                    'data' => $brand
                ]
            ], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'result' => [
                    'message' => 'Không tìm thấy brand'
                ]
            ], 404);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'result' => [
                    'message' => $e->getMessage()
                ]
            ], 500);
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        try {
            // Validate dữ liệu từ request
            $request->validate([
                'name' => 'required|string|max:255|unique:brands,name,' . $id,
                'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048', // Logo có thể null nếu không cần thay đổi
            ]);

            // Tìm brand cần cập nhật
            $brand = Brands::findOrFail($id);

            // Cập nhật các trường thông tin của brand
            $brand->name = $request->name;

            // Xử lý upload logo mới nếu có
            if ($request->hasFile('logo')) {
                $logo = $request->file('logo');
                $ext = $logo->getClientOriginalExtension();
                $imageName = time() . '.' . $ext;
                $logo->move(public_path('upload'), $imageName);
                // Xóa logo cũ nếu có và lưu lại đường dẫn logo mới
                $brand->logo = '/upload/' . $imageName;
            }

            // Lưu lại các thay đổi vào cơ sở dữ liệu
            $brand->save();

            return response()->json([
                'success' => true,
                'result' => [
                    'data' => $brand
                ]
            ], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'result' => [
                    'message' => 'Không tìm thấy brand'
                ]
            ], 404);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'result' => [
                    'message' => 'Có lỗi validate dữ liệu',
                    'errors' => $e->errors()
                ]
            ], 422);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'result' => [
                    'message' => $e->getMessage()
                ]
            ], 500);
        }
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $item = Brands::findOrFail($id);
            $item->delete();
            return response()->json(
                [
                    'success' => true,
                    'result' => ['message' => 'Xoá mục thành công!']
                ],
                200
            );
        } catch (ModelNotFoundException $e) {
            return response()->json(
                [
                    'success' => false,
                    'result' => ['message' => 'Không tìm thấy mục']
                ],
                404
            );
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'result' => ['message' => 'Lỗi máy chủ']
            ], 500);
        }
    }
    public function restore(string $id)
    {
        try {
            $item = Brands::withTrashed()->find($id);

            if ($item && $item->trashed()) {
                $item->restore();
                return response()->json([
                    'success' => true,
                    'result' => [
                        'message' => 'Đã khôi phục thành công'
                    ]
                ], 200);
            } else {
                return response()->json([
                    'success' => false,
                    'result' => [
                        'message' => 'Không tồn tại hoặc chưa bị xoá'
                    ]
                ], 404);
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'result' => [
                    'message' => 'Lỗi máy chủ'
                ]
            ], 500);
        }
    }
}
