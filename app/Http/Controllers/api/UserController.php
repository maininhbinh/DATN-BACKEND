<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Request;

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


     public function profile(Request $request){
         try {

             $user = $request->user();

             return response()->json([
                 'success' => true,
                 'result' => [
                     'data' => $user,
                 ]
             ], 200);

         } catch (\Exception $e) {
             return response()->json([
                 'success' => true,
                 'result' => [
                     'error' => $e
                 ]
             ], 500);
         }

     }
}
