<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\Categories;
use Illuminate\Http\Request;

class ApiCategoriesController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Lấy tất cả các danh mục cha từ cơ sở dữ liệu
        $categories = Categories::whereNull('parent_id')->get();

        // Trả về danh sách các danh mục cha dưới dạng JSON
        return response()->json($categories, 200, [], JSON_UNESCAPED_UNICODE);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $category = Categories::create($request->all());

        return response()->json([
            'message' => 'Thêm danh mục thành công !',
            'category' => $category
        ], 201, [], JSON_UNESCAPED_UNICODE);
    }
    public function storeChild(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'parent_id' => 'required|exists:categories,id',
        ]);

        $childCategory = Categories::create($request->all());

        return response()->json([
            'message' => 'Thêm danh mục con thành công!',
            'category' => $childCategory
        ], 201, [], JSON_UNESCAPED_UNICODE);
    }
    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $category = Categories::with('parent', 'children')->find($id);

        if (!$category) {
            return response()->json([
                'message' => 'Danh mục không tồn tại!'
            ], 404, [], JSON_UNESCAPED_UNICODE);
        }

        return response()->json([
            'category' => $category,
        ], 200, [], JSON_UNESCAPED_UNICODE);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $request->validate([
            'name' => 'string|max:255',
            'description' => 'nullable|string',
            'parent_id' => 'exists:categories,id',
        ]);

        $category = Categories::find($id);

        if (!$category) {
            return response()->json([
                'message' => 'Danh mục không tồn tại!'
            ], 404, [], JSON_UNESCAPED_UNICODE);
        }

        $category->fill($request->all());
        $category->save();

        return response()->json([
            'message' => 'Cập nhật danh mục thành công!',
            'category' => $category
        ], 200, [], JSON_UNESCAPED_UNICODE);
    }
    public function updateChild(Request $request, string $id, string $child_id)
    {
        $request->validate([
            'name' => 'string|max:255',
            'description' => 'nullable|string',
        ]);

        $category = Categories::find($child_id);

        if (!$category) {
            return response()->json([
                'message' => 'Danh mục con không tồn tại!'
            ], 404, [], JSON_UNESCAPED_UNICODE);
        }

        // Kiểm tra xem danh mục con có thuộc về danh mục cha không
        if ($category->parent_id != $id) {
            return response()->json([
                'message' => 'Danh mục con không thuộc về danh mục cha đã cho!'
            ], 422, [], JSON_UNESCAPED_UNICODE);
        }

        // Lưu lại parent_id cũ để không thể sửa đổi
        $parent_id = $category->parent_id;

        // Cập nhật thông tin danh mục con
        $category->fill($request->all());

        // Khôi phục lại parent_id cũ
        $category->parent_id = $parent_id;

        $category->save();

        return response()->json([
            'message' => 'Cập nhật danh mục con thành công!',
            'category' => $category
        ], 200, [], JSON_UNESCAPED_UNICODE);
    }
    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
    public function deleteCategory(Request $request, string $id)
    {
        // Tìm kiếm danh mục cha cần xoá mềm
        $category = Categories::find($id);

        if (!$category) {
            return response()->json([
                'message' => 'Danh mục không tồn tại!'
            ], 404, [], JSON_UNESCAPED_UNICODE);
        }

        // Kiểm tra xem danh mục có danh mục con không
        if ($category->children()->exists()) {
            return response()->json([
                'message' => 'Không thể xoá danh mục vì có danh mục con tồn tại!'
            ], 422, [], JSON_UNESCAPED_UNICODE);
        }

        $categoryName = $category->name;

        // Thực hiện xoá mềm danh mục cha nếu không có danh mục con
        $category->delete();

        return response()->json([
            'message' => 'Đã xoá danh mục ' . $categoryName . "!"
        ], 200, [], JSON_UNESCAPED_UNICODE);
    }
}
