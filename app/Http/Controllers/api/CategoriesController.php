<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\Categories;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class CategoriesController extends Controller
{

    public function index()
    {
        try {
            $items = Categories::all();
            if ($items->isEmpty()) {
                return response()->json(
                    [
                        'success' => false,
                        'result' => [
                            'message' => "Không tìm thấy dữ liệu"
                        ]
                    ],
                    404
                );
            }
            return response()->json(
                [
                    'success' => true,
                    'result' => [
                        'data' => $items,
                    ]
                ],
                200
            );
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'result' => [
                    'message' => 'Lỗi serve'
                ]
            ], 500);
        }
    }



    public function store(Request $request)
    {
        try {
            $request->validate(
                [
                    'name' => 'required|string|max:255|unique:categories,name',
                    'description' => 'required|nullable|string',
                ],
                [
                    'name.required' => "Chưa nhập tên danh mục",
                    'name.unique' => "Tên danh mục đã tồn tại",
                    'description.required' => "Chưa nhập mô tả",
                ]
            );
            $item = Categories::create($request->all());
            if (!$item) {
                return response()->json(
                    [
                        'success' => false,
                        'result' => [
                            'message' => "Không thể thêm dữ liệu"
                        ]
                    ],
                    500
                );
            }
            return response()->json(
                [
                    'success' => true,
                    'result' => [
                        'data' => $item
                    ]
                ],
                200
            );
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'result' => [
                    'message' => 'Lỗi xác validate'
                ]
            ], 422);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'result' => [
                    'message' => "Lỗi máy chủ."
                ]
            ], 500);
        }
    }



    public function storeChild(Request $request)
    {
        try {
            $request->validate([
                'name' => 'required|string|max:255',
                'description' => 'nullable|string',
                'parent_id' => 'required|exists:categories,id',
            ]);

            $childCategory = Categories::create($request->all());
            if (!$childCategory) {
                return response()->json([
                    'success' => false,
                    'result' => [
                        'message' => 'Không thể thêm dữ liệu',
                    ]
                ], 404);
            }
            return response()->json([
                'success' => true,
                'result' => [
                    'data' => $childCategory,
                ]
            ], 200);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'result' => [
                    'message' => 'Lỗi xác thực.',
                ]
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'result' => [
                    'message' => 'Lỗi máy chủ.'
                ]
            ], 500);
        }
    }

    public function show(string $id)
    {
        try {
            $item = Categories::with('parent', 'children')->findOrFail($id);

            return response()->json([
                'success' => true,
                'result' => [
                    'data' => $item,
                ]
            ], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'result' => [
                    'message' => 'Dữ liệu không tồn tại'
                ]
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'result' => [
                    'message' => 'Lỗi máy chủ'
                ]
            ], 500);
        }
    }


    public function update(Request $request, string $id)
    {
        try {
            $validatedData = $request->validate([
                'name' => 'string|max:255',
                'description' => 'nullable|string',
                'parent_id' => 'exists:categories,id',
            ]);

            $item = Categories::findOrFail($id);

            $item->fill($validatedData);
            $item->save();

            return response()->json([
                'success' => true,
                'result' => [
                    'message' => 'Cập nhật danh mục thành công!',
                    'data' => $item
                ]
            ], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'result' => [
                    'message' => 'Danh mục không tồn tại!'
                ]
            ], 404);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'result' => [
                    'message' => 'Lỗi validate dữ liệu'
                ]
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'result' => [
                    'message' => 'Lỗi máy chủ'
                ]
            ], 500);
        }
    }


    public function updateChild(Request $request, string $id, string $child_id)
    {
        try {
            $validatedData = $request->validate([
                'name' => 'string|max:255',
                'description' => 'nullable|string',
            ]);

            $item = Categories::findOrFail($child_id);

            // Kiểm tra xem danh mục con có thuộc về danh mục cha không
            if ($item->parent_id != $id) {
                return response()->json([
                    'success' => false,
                    'result' => [
                        'message' => 'Danh mục con không thuộc về danh mục cha đã cho!'
                    ]
                ], 422);
            }

            // Lưu lại parent_id cũ để không thể sửa đổi
            $parent_id = $item->parent_id;

            // Cập nhật thông tin danh mục con
            $item->fill($validatedData);

            // Khôi phục lại parent_id cũ
            $item->parent_id = $parent_id;

            $item->save();

            return response()->json([
                'success' => true,
                'result' => [
                    'message' => 'Cập nhật danh mục con thành công!',
                    'data' => $item
                ]
            ], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'result' => [
                    'message' => 'Danh mục con không tồn tại!'
                ]
            ], 404);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'result' => [
                    'message' => 'Lỗi xác thực dữ liệu.',
                ]
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'result' => [
                    'message' => 'Lỗi máy chủ'
                ]
            ], 500);
        }
    }



    public function destroy(Request $request, string $id)
    {
        try {
            // Tìm kiếm danh mục cha cần xoá mềm
            $category = Categories::findOrFail($id);

            // Kiểm tra xem danh mục có danh mục con không
            if ($category->children()->exists()) {
                return response()->json([
                    'message' => 'Không thể xoá danh mục vì có danh mục con tồn tại!'
                ], 422);
            }

            $categoryName = $category->name;

            // Thực hiện xoá mềm danh mục cha nếu không có danh mục con
            $category->delete();

            return response()->json([
                'success' => true,
                'result' => [
                    'message' => 'Đã xoá danh mục '
                ]
            ], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'result' => [
                    'message' => 'Danh mục không tồn tại!'
                ]
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'result' => [
                    'message' => 'Lỗi máy chủ!'
                ]
            ], 500);
        }
    }
    public function restore(string $id)
    {
        try {
            $item = Categories::withTrashed()->find($id);

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
