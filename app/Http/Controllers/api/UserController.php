<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\User;
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
    public function profile(Request $request)
    {
        try {

            $user = $request->user();

            return response()->json([
                'success' => true,
                'data' => $user,
            ], 200);
        } catch (\Exception $e) {

            return response()->json([
                'success' => true,
                'message' => 'Máy chủ không hoạt động',
            ], 500);
        }
    }
    public function store(Request $request)
    {
        try {

            $validatedData = $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|string|email|max:255|unique:users',
                'password' => 'required|string|min:8',
                'image' => 'nullable|string',
                'phone' => 'nullable|string|max:20',
                'address_line1' => 'nullable|string|max:255',
                'address_line2' => 'nullable|string|max:255',
                'county' => 'nullable|string|max:255',
                'district' => 'nullable|string|max:255',
                'city' => 'nullable|string|max:255',
                'role_id' => 'nullable|integer',
                'in_active' => 'nullable|integer',
                'virtual' => 'nullable|integer',
            ]);

            $validatedData['password'] = bcrypt($request->password);
            $user = User::create($validatedData);

            return response()->json([
                'success' => true,
                'data' => $user,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Máy chủ không hoạt động',
            ], 500);
        }
    }
    public function update(Request $request, $id)
    {
        try {
            // Validate request data
            $validatedData = $request->validate([
                'name' => 'sometimes|required|string|max:255',
                'email' => 'sometimes|required|string|email|max:255|unique:users,email,' . $id,
                'password' => 'sometimes|required|string|min:8',
                'image' => 'nullable|string',
                'phone' => 'nullable|string|max:20',
                'address_line1' => 'nullable|string|max:255',
                'address_line2' => 'nullable|string|max:255',
                'county' => 'nullable|string|max:255',
                'district' => 'nullable|string|max:255',
                'city' => 'nullable|string|max:255',
                'role_id' => 'nullable|integer',
                'in_active' => 'nullable|integer',
                'virtual' => 'nullable|integer',
            ]);

            // Find user
            $user = User::findOrFail($id);

            // Update user fields
            if ($request->has('password')) {
                $validatedData['password'] = bcrypt($request->password);
            }
            $user->update($validatedData);

            return response()->json([
                'success' => true,
                'data' => $user,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Máy chủ không hoạt động',
            ], 500);
        }
    }
}
