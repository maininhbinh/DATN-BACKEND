<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\Comment;
use Illuminate\Http\Request;

class CommentController extends Controller
{

    public function index($productId)
    {
        try {
            // Retrieve all comments for the specified product
            $comments = Comment::where('product_id', $productId)
                ->with('user')
                ->get(['product_id', 'user_id', 'content', 'rating']);

            // Format the comments to include user_name
            $formattedComments = $comments->map(function ($comment) {
                return [
                    'product_id' => $comment->product_id,
                    'user_name' => $comment->user->name, // Assuming 'name' is the user's name field
                    'content' => $comment->content,
                    'rating' => $comment->rating,
                ];
            });

            // Return the formatted comments as JSON
            return response()->json($formattedComments);
        } catch (\Exception $e) {
            // Handle the exception and return an error response
            return response()->json(['error' => 'Unable to retrieve comments'], 500);
        }
    }
}
