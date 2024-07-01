<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\ProductDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ProductDetailController extends Controller
{
    public function index()
    {
        try {
            $items = ProductDetail::orderBy('created_at', 'desc')->get();
            return response()->json($items, 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Đã xảy ra lỗi khi lấy dữ liệu.'
            ], 500);
        }
    }


    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'product_id' => 'required|string|max:255|exists:products,id',
            'detail_id' => 'required|string|max:255|exists:details,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()
            ], 400);
        }

        try {
            $item = ProductDetail::create($request->all());
            return response()->json($item, 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Đã xảy ra lỗi khi tạo chi tiết sản phẩm.'
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function edit(string $id)
    {
        try {
            $item = ProductDetail::findOrFail($id);
            return response()->json($item, 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy chi tiết sản phẩm.'
            ], 404);
        }
    }


    public function update(Request $request, string $id)
    {
        $validator = Validator::make($request->all(), [
            'product_id' => 'required|string|max:255|exists:products,id',
            'detail_id' => 'required|string|max:255|exists:details,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()
            ], 400);
        }

        try {
            $item = ProductDetail::findOrFail($id);
            $item->update($request->all());
            return response()->json($item, 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Đã xảy ra lỗi khi cập nhật chi tiết sản phẩm.'
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $item = ProductDetail::findOrFail($id);
            $item->delete();
            return response()->json(null, 204);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Đã xảy ra lỗi khi xóa chi tiết sản phẩm.'
            ], 500);
        }
    }
    public function restore($id)
    {
        try {
            $item = ProductDetail::withTrashed()->findOrFail($id);
            $item->restore();
            return response()->json($item, 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Đã xảy ra lỗi khi khôi phục chi tiết sản phẩm.'
            ], 500);
        }
    }
}
