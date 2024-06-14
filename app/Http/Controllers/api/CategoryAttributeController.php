<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\CategoryAttribute;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class CategoryAttributeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $item = CategoryAttribute::all();
            if ($item->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'result' => [
                        'message' => 'Không có dữ liệu'
                    ]
                ], 404);
            }
            return response()->json([
                'success' => true,
                'result' => [
                    'data' => $item
                ]
            ], 200);
        } catch (Exception $th) {
            return response()->json([
                'success' => false,
                'result' => [
                    'message' => 'Lỗi máy chủ'
                ]
            ], 500);
        }
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
        try {
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
            return response()->json([
                'success' => true,
                'result' => [
                    'data' => $item
                ]
            ], 201);
        } catch (ValidationException $th) {
            return response()->json([
                'success' => false,
                'result' => [
                    'message' => 'Lỗi xác thực dữ liệu'
                ]
            ], 422);
        } catch (Exception $th) {
            return response()->json([
                'success' => false,
                'result' => [
                    'message' => 'Lỗi máy chủ'
                ]
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        try {
            $item = CategoryAttribute::findOrFail($id);
            return response()->json(
                [
                    'success' => true,
                    'result' => [
                        'data' => $item,
                    ]
                ],
                200
            );
        } catch (ModelNotFoundException $e) {
            return response()->json(
                [
                    'success' => false,
                    'result' => [
                        'message' => 'Dữ liệu không tồn tại'
                    ]
                ],
                404
            );
        } catch (\Exception $e) {
            return response()->json(
                [
                    'success' => false,
                    'result' => [
                        'message' => 'Lỗi máy chủ'
                    ]
                ],
                500
            );
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
        try {
            $item = CategoryAttribute::findOrFail($id);
            if ($item) {
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

                $item->update($request->all());

                return response()->json([
                    'success' => true,
                    'result' => [
                        'message' => 'Cập nhật biến thể thành công!',
                        'data' => $item
                    ]
                ], 200);
            } else {
                return response()->json([
                    'success' => false,
                    'result' => [
                        'message' => 'Biến thể không tồn tại'
                    ]
                ], 404);
            }
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'result' => [
                    'message' => 'Lỗi xác thực dữ liệu.',
                ]
            ], 422);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'result' => [
                    'message' => 'Dữ liệu không tồn tại.'
                ]
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'result' => [
                    'message' => 'Lỗi máy chủ.'
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
            $item = CategoryAttribute::findOrFail($id);
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
            $item = CategoryAttribute::withTrashed()->find($id);

            if ($item && $item->trashed()) {
                $item->restore();
                return response()->json([
                    'success' => true,
                    'result' => [
                        'message' => 'Đã khôi phục thành công'
                    ]
                ], 200, [], JSON_UNESCAPED_UNICODE);
            } else {
                return response()->json([
                    'success' => false,
                    'result' => [
                        'message' => 'Không tồn tại hoặc chưa bị xoá'
                    ]
                ], 404, [], JSON_UNESCAPED_UNICODE);
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'result' => [
                    'message' => 'Lỗi máy chủ'
                ]
            ], 500, [], JSON_UNESCAPED_UNICODE);
        }
    }
}
