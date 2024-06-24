<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\Attribute;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AttributeController extends Controller
{
    public function index()
    {
        try {
            $items = Attribute::orderBy('created_at', 'desc')->get();
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
            'detail_id' => 'required|string|max:255|exists:details,id',
            'name' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()
            ], 400);
        }

        try {
            $item = Attribute::create($request->all());
            return response()->json($item, 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Đã xảy ra lỗi khi tạo attribute.'
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function edit(string $id)
    {
        try {
            $item = Attribute::findOrFail($id);
            return response()->json($item, 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy attribute.'
            ], 404);
        }
    }

    /**
     * Show the form for editing the specified resource.
     */

    public function update(Request $request, string $id)
    {
        $validator = Validator::make($request->all(), [
            'detail_id' => 'sometimes|string|max:255|exists:details,id',
            'name' => 'sometimes|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()
            ], 400);
        }

        try {
            $item = Attribute::findOrFail($id);
            $item->update($request->all());
            return response()->json($item, 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Đã xảy ra lỗi khi cập nhật attribute.'
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $item = Attribute::findOrFail($id);
            $item->delete();
            return response()->json(null, 204);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Đã xảy ra lỗi khi xóa attribute.'
            ], 500);
        }
    }
    public function restore($id)
    {
        try {
            $item = Attribute::withTrashed()->findOrFail($id);
            $item->restore();
            return response()->json($item, 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Đã xảy ra lỗi khi khôi phục attribute.'
            ], 500);
        }
    }
}
