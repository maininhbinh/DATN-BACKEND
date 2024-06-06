<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\ValueAttribute;
use Illuminate\Http\Request;

class ApiValueAttributeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $item = ValueAttribute::all();
        return response()->json($item, 200, [], JSON_UNESCAPED_UNICODE);
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
        $validatedData = $request->validate(
            [
                'parameter_id' => 'required|exists:parameters,id',
                'value' => 'required|string|max:255',
            ],
            [
                'parameter_id.required' => "Trường ID tham số không được bỏ trống",
                'parameter_id.exists' => "ID tham số không hợp lệ",
                'value.required' => "Trường giá trị không được bỏ trống",
                'value.string' => "Giá trị phải là một chuỗi",
                'value.max' => "Giá trị không được vượt quá 255 ký tự",
            ]
        );


        // Create a new ValueAttribute
        $item = ValueAttribute::create($validatedData);

        return response()->json($item, 201, [], JSON_UNESCAPED_UNICODE);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $item = ValueAttribute::find($id);
        if ($item) {
            return response()->json($item, 200, [], JSON_UNESCAPED_UNICODE);
        } else {
            return response()->json(['message' => 'Giá trị không tồn tại'], 404, [], JSON_UNESCAPED_UNICODE);
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
      
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $item = ValueAttribute::find($id);

        if ($item) {
            $request->validate([
                'parameter_id' => 'required|exists:parameters,id',
                'value' => 'required|string|max:255',
            ]);

            $item->update($request->all());

            return response()->json([
                'message' => 'Cập nhật giá trị thành công!',
                'attribute' => $item
            ], 200, [], JSON_UNESCAPED_UNICODE);
        } else {
            return response()->json(['message' => 'Giá trị không tồn tại'], 404, [], JSON_UNESCAPED_UNICODE);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $item = ValueAttribute::find($id);
        if ($item) {
            $item->delete();
            return response()->json(['message' => 'Xoá giá trị thành công!'], 200, [], JSON_UNESCAPED_UNICODE);
        } else {
            return response()->json(['message' => 'Giá trị không tồn tại'], 404, [], JSON_UNESCAPED_UNICODE);
        }
    }
}
