<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\Detail;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class DetailController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $items = Detail::orderBy('created_at', 'desc')->get();
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
            'category_id' => 'required|string|max:255|exists:categories,id',
            'name' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()
            ], 400);
        }

        try {
            $item = Detail::create($request->all());
            if($request->category_id){
                $cats = Category::whereIn('id', explode(',', $request->category_id))->pluck('id');
            }else{
                $cats = [];
            }
            $item->category()->sync($cats);
            $item->update($request->all());
            return response()->json($item, 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Đã xảy ra lỗi khi tạo detail.'
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function edit(string $id)
    {
        try {
            $item = Detail::with('category')->findOrFail($id);
            return response()->json($item, 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy detail.'
            ], 404);
        }
    }


    public function update(Request $request, string $id)
    {
        $validator = Validator::make($request->all(), [
            'category_id' => 'sometimes|string|max:255|exists:categories,id',
            'name' => 'sometimes|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()
            ], 400);
        }

        try {
            if($request->category_id){
                $cats = Category::whereIn('id', explode(',', $request->category_id))->pluck('id');
            }else{
                $cats = [];
            }
            $item = Detail::findOrFail($id);
            $item->category()->sync($cats);
            $item->update($request->all());
            return response()->json($item, 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Đã xảy ra lỗi khi cập nhật detail.'
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $item = Detail::findOrFail($id);
            $item->category()->detach();
            $item->delete();
            return response()->json(null, 204);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Đã xảy ra lỗi khi xóa detail.'
            ], 500);
        }
    }
    public function restore($id)
    {
        try {
            $item = Detail::withTrashed()->findOrFail($id);
            $item->restore();
            return response()->json($item, 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Đã xảy ra lỗi khi khôi phục detail.'
            ], 500);
        }
    }

}
