<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\CategoryAttribute;
use Illuminate\Http\Request;

class CategoryAttributeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $item = CategoryAttribute::all();
        return response()->json($item, 200, [], JSON_UNESCAPED_UNICODE);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate(
            [
                'category_id' => 'required|exists:categories,id',
                'parameter_id' => 'required|exists:parameters,id',
            ],
            [
                'category_id.required' => 'Không bỏ trống danh mục.',
                'category_id.exists' => 'Danh mục không tồn tại.',
                'parameter_id.required' => 'Không bỏ trống tham số.',
                'parameter_id.exists' => 'Không tồn tại parameter_id.',
            ]
        );
        $item = CategoryAttribute::create($request->all());
        return response()->json($item, 201, [], JSON_UNESCAPED_UNICODE);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $item = CategoryAttribute::find($id);
        if ($item) {
            return response()->json($item, 200, [], JSON_UNESCAPED_UNICODE);
        } else {
            return response()->json(['message' => 'Không tồn tại'], 404, [], JSON_UNESCAPED_UNICODE);
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
        $request->validate(
            [
                'category_id' => 'required|exists:categories,id',
                'parameter_id' => 'required|exists:parameters,id',
            ],
            [
                'category_id.required' => 'Không bỏ trống danh mục.',
                'category_id.exists' => 'Danh mục không tồn tại.',
                'parameter_id.required' => 'Không bỏ trống tham số.',
                'parameter_id.exists' => 'Không tồn tại parameter_id.',
            ]
        );

        $item = CategoryAttribute::find($id);

        if ($item) {
            $item->update($request->all());
            return response()->json($item, 200, [], JSON_UNESCAPED_UNICODE);
        } else {
            return response()->json(['message' => 'Không tồn tại'], 404, [], JSON_UNESCAPED_UNICODE);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $item = CategoryAttribute::find($id);

        if ($item) {
            $item->delete();
            return response()->json(['message' => 'Đã xóa thành công'], 200, [], JSON_UNESCAPED_UNICODE);
        } else {
            return response()->json(['message' => 'Không tồn tại'], 404, [], JSON_UNESCAPED_UNICODE);
        }
    }
    public function restore(string $id)
    {
        $item = CategoryAttribute::withTrashed()->find($id);

        if ($item && $item->trashed()) {
            $item->restore();
            return response()->json(['message' => 'Đã khôi phục thành công'], 200, [], JSON_UNESCAPED_UNICODE);
        } else {
            return response()->json(['message' => 'Không tồn tại hoặc chưa bị xoá'], 404, [], JSON_UNESCAPED_UNICODE);
        }
    }
}
