<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\Introduce;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class IntroduceController extends Controller
{public function index()
    {
        //
        try {
            $item = Introduce::all();
            if ($item->isEmpty()) {
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
                        'data' => $item,
                    ]
                ],
                200
            );
        } catch (Exception $e) {
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
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $request->validate(
                [
                    'title' => 'required|string|max:255',
                    'slug' => 'required||string',
                    'content' => 'required||string',
                ]
            );
            $item = Introduce::create($request->all());
            if (!$item) {
                return response()->json(
                    [
                        'success' => false,
                        'result' => [
                            'message' => "Không thể thêm dữ liệu"
                        ]
                    ],
                    500
                );
            }
            return response()->json(
                [
                    'success' => true,
                    'result' => [
                        'data' => $item
                    ]
                ],
                200
            );
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'result' => [
                    'message' => 'Lỗi xác nhận dữ liệu '
                ]
            ], 422);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'result' => [
                    'message' => "Lỗi máy chủ."
                ]
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
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
            $item = Introduce::findOrFail($id);
            if ($item) {
                $request->validate(
                    [
                        'title' => 'required|string|max:255',
                        'slug' => 'required||string',
                        'content' => 'required||string',
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
            $item = Introduce::findOrFail($id);
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
            $item = Introduce::withTrashed()->find($id);

            if ($item && $item->trashed()) {
                $item->restore();
                return response()->json([
                    'success' => true,
                    'result' => [
                        'message' => 'Đã khôi phục thành công'
                    ]
                ], 200);
            } else {
                return response()->json([
                    'success' => false,
                    'result' => [
                        'message' => 'Không tồn tại hoặc chưa bị xoá'
                    ]
                ], 404);
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'result' => [
                    'message' => 'Lỗi máy chủ'
                ]
            ], 500);
        }
    }
}
