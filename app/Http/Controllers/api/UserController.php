<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;

/**
 * @OA\Get(
 *      path="/user/profile",
 *      summary="Protected Resource",
 *      description="User",
 *      security={{ "BearerAuth": {} }},
 *      tags={"User"},
 *      @OA\Response(
 *          response=200,
 *          description="Successful retrieval of protected resource",
 *          @OA\JsonContent(
 *              @OA\Property(property="data", type="string", example="A protected resource")
 *          )
 *      ),
 *      @OA\Response(
 *          response=401,
 *          description="Unauthorized"
 *      )
 * )
 */

class UserController extends Controller
{
    //

    public function profile(){
        try {

            $user = JWTAuth::parseToken()->authenticate();

            return response()->json([
                'success' => true,
                'result' => [
                    'data' => $user,
                ]
            ], 200);

        } catch (\Tymon\JWTAuth\Exceptions\UserNotDefinedException $e) {
            return response()->json([
                'success' => true,
                'result' => [
                    'message' => 'No Auththentication',
                    'error' => $e
                ]
            ], 401);
        }
    }
}
