<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\Parameter;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class ParametersController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $items = Parameter::all();
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
                    'message' => 'Lỗi không xác thực'
                ]
            ], 500);
        }
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
        try {
            $validatedData = $request->validate([
                'name' => 'required|string|max:255|unique:parameters,name',
                'description' => 'nullable|string',
            ], [
                'name.required' => 'Tên là bắt buộc.',
                'name.string' => 'Tên phải là chuỗi ký tự.',
                'name.max' => 'Tên không được vượt quá 255 ký tự.',
                'name.unique' => 'Tham số đã tồn tại.',
                'description.string' => 'Mô tả phải là chuỗi ký tự.',
            ]);

            $item = Parameter::create($validatedData);

            if (!$item) {
                return response()->json(
                    [
                        'success' => false,
                        'result' => [
                            'message' => 'Không thể tạo mục.'
                        ]
                    ],
                    500
                );
            }

            return response()->json(
                [
                    'success' => true,
                    'result' => [
                        'data' => $item,
                        'message' => 'Mục đã được tạo thành công.'
                    ]
                ],
                201
            );
        } catch (ValidationException $e) {
            return response()->json(
                [
                    'success' => false,
                    'result' => [
                        'message' => 'Lỗi xác thực.',
                    ]
                ],
                422
            );
        } catch (\Exception $e) {
            return response()->json(
                [
                    'success' => false,
                    'result' => [
                        'message' => 'Lỗi máy chủ.'
                    ]
                ],
                500
            );
        }
    }



    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        try {
            $item = Parameter::findOrFail($id);
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
            $item = Parameter::findOrFail($id);
            if ($item) {
                $request->validate([
                    'name' => 'required|string|max:255|unique:parameters,name,' . $item->id,
                    'description' => 'nullable|string',
                ], [
                    'name.required' => 'Tên là bắt buộc.',
                    'name.string' => 'Tên phải là chuỗi ký tự.',
                    'name.max' => 'Tên không được vượt quá 255 ký tự.',
                    'name.unique' => 'Tham số đã tồn tại.',
                    'description.string' => 'Mô tả phải là chuỗi ký tự.',
                ]);

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
            $item = Parameter::findOrFail($id);
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
        $item = Parameter::withTrashed()->find($id);

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
