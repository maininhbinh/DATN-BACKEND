<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\Parameter;
use Illuminate\Http\Request;

class ParametersController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $items = Parameter::all();
        return response()->json($items, 200, [], JSON_UNESCAPED_UNICODE);
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
        $request->validate([
            'name' => 'required|string|max:255|unique:parameters,name',
            'description' => 'required|nullable|string',
        ], [
            'name.required' => 'Tên là bắt buộc.',
            'name.string' => 'Tên phải là chuỗi ký tự.',
            'name.max' => 'Tên không được vượt quá 255 ký tự.',
            'name.unique' => 'Tham số đã tồn tại.',
            'description.required' => 'Mô tả là bắt buộc.',
            'description.string' => 'Mô tả phải là chuỗi ký tự.',
        ]);
        $item = Parameter::create($request->all());
        return response()->json([
            'message' => 'Thêm parameter thể thành công!',
            'category' => $item
        ], 201, [], JSON_UNESCAPED_UNICODE);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $item = Parameter::find($id);
        if ($item) {
            return response()->json($item, 200, [], JSON_UNESCAPED_UNICODE);
        } else {
            return response()->json(['message' => 'Biến thể không tồn tại'], 404, [], JSON_UNESCAPED_UNICODE);
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
    public function update(Request $request, string $id)
    {
        $item = Parameter::find($id);
        if ($item) {
            $request->validate([
                'name' => 'required|string|max:255|unique:parameters,name,' . $item->id,
                'description' => 'required|nullable|string',
            ]);
            $item->update($request->all());
            return response()->json([
                'message' => 'Cập nhật biến thể thành công!',
                'attribute' => $item
            ], 200, [], JSON_UNESCAPED_UNICODE);
        } else {
            return response()->json(['message' => 'Biến thể không tồn tại'], 404, [], JSON_UNESCAPED_UNICODE);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $item = Parameter::find($id);
        if ($item) {
            $item->delete();
            return response()->json(['message' => 'Xoá biến thể thành công!'], 200, [], JSON_UNESCAPED_UNICODE);
        } else {
            return response()->json(['message' => 'Biến thể không tồn tại'], 404, [], JSON_UNESCAPED_UNICODE);
        }
    }
}
