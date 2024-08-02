<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\Comment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CommentController extends Controller
{

    public function index($productId)
    {
        try {
            // Lấy tất cả comments cho sản phẩm cụ thể
            $comments = Comment::where('product_id', $productId)
                ->with('user')
                ->get(['product_id', 'user_id', 'content', 'rating', 'created_at']);

            // Định dạng lại comments để bao gồm user_name
            $formattedComments = $comments->map(function ($comment) {
                return [
                    'product_id' => $comment->product_id,
                    'user_name' => $comment->user->username, // Giả sử trường 'name' là tên người dùng
                    'content' => $comment->content,
                    'rating' => $comment->rating,
                    'created_at' => $comment->created_at,
                ];
            });

            // Trả về comments đã được định dạng dưới dạng JSON
            return response()->json($formattedComments);
        } catch (\Exception $e) {
            // Xử lý lỗi và trả về phản hồi lỗi
            return response()->json(['error' => 'Unable to retrieve comments'], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            // Kiểm tra người dùng đã đăng nhập chưa
            if (!auth()->check()) {
                return response()->json(['error' => 'Unauthorized'], 401);
            }

            // Lấy user_id từ người dùng đã đăng nhập
            $userId = auth()->id();

            // Validate the request data
            $validator = Validator::make($request->all(), [
                'product_id' => 'required|exists:products,id',
                'content' => 'required|string',
                'rating' => 'required|integer|min:1|max:5',
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }

            // Tạo comment mới
            $comment = Comment::create([
                'user_id' => $userId,
                'product_id' => $request->product_id,
                'content' => $request->content,
                'rating' => $request->rating,
            ]);

            return response()->json($comment, 201);
        } catch (\Exception $e) {
            // Xử lý lỗi và trả về phản hồi lỗi
            return response()->json(['error' => 'Unable to create comment'], 500);
        }
    }
    public function destroy($id)
    {
        try {
            // Kiểm tra người dùng đã đăng nhập chưa
            if (!auth()->check()) {
                return response()->json(['error' => 'Unauthorized'], 401);
            }

            // Lấy user_id từ người dùng đã đăng nhập
            $userId = auth()->id();

            // Tìm comment theo ID
            $comment = Comment::findOrFail($id);

            // Kiểm tra quyền sở hữu comment
            if ($comment->user_id !== $userId) {
                return response()->json(['error' => 'Forbidden'], 403);
            }

            // Xóa comment
            $comment->delete();

            return response()->json(['message' => 'Comment deleted successfully']);
        } catch (\Exception $e) {
            // Xử lý lỗi và trả về phản hồi lỗi
            return response()->json(['error' => 'Unable to delete comment'], 500);
        }
    }
}
