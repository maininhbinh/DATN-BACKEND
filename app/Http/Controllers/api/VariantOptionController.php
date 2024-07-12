<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\VariantOption;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class VariantOptionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
        try {
            $item = VariantOption::orderBy('created_at', 'desc')->get();
            return response()->json($item, 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e
            ], 500);
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
        try {
            $item = VariantOption::findOrFail($id);
            return response()->json($item, 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy Value.'
            ], 404);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
        $validator = Validator::make($request->all(), [
            'variant_id' => 'required|string|max:255|exists:variants,id',
            'name' => 'sometimes|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()
            ], 400);
        }

        try {
            $item = VariantOption::findOrFail($id);
            $item->update($request->all());
            return response()->json($item, 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Đã xảy ra lỗi khi cập nhật Value.'
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
        try {
            $item = VariantOption::findOrFail($id);
            $item->delete();
            return response()->json([
                'success' => true,
                'message' => 'Xóa thành công'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e
            ], 500);
        }
    }
    public function restore($id)
    {
        try {
            $item = VariantOption::withTrashed()->findOrFail($id);
            $item->restore();
            return response()->json($item, 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Đã xảy ra lỗi khi khôi phục Value.'
            ], 500);
        }
    }

}
