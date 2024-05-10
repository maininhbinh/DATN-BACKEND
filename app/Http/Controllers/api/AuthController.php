<?php

namespace App\Http\Controllers\api;

use App\Events\OtpRequested;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserRegistration;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Tymon\JWTAuth\Facades\JWTAuth;

/**
 * @OA\Post(
 *   path="/auth/signup",
 *   summary="Đăng ký",
 *   tags={"Authentication"},
 *   @OA\RequestBody(
 *       required=true,
 *       @OA\MediaType(
 *           mediaType="application/json",
 *           @OA\Schema(
 *               required={"email", "password", "confirm_password"},
 *               @OA\Property(property="username", type="string", default="your name"),
 *               @OA\Property(property="email", type="string", default="your_email@example.com"),
 *               @OA\Property(property="password", type="string", default="Abc1234"),
 *               @OA\Property(property="password_confirmation", type="string", default="Abc1234")
 *           )
 *       )
 *   ),
 *   @OA\Response(
 *       response=200,
 *       description="Successful operation"
 *   ),
 *   @OA\Response(
 *       response=422,
 *       description="validate"
 *   ),
 *   @OA\Response(
 *       response=500,
 *       description="server"
 *   )
 * ),
 * 
 * @OA\Post(
 *      path="/auth/verifyOTP",
 *      summary="Verify OTP",
 *      description="This endpoint verifies an OTP sent to the user's email.",
 *      tags={"Authentication"},
 *      @OA\RequestBody(
 *          required=true,
 *          @OA\MediaType(
 *              mediaType="application/json",
 *              @OA\Schema(
 *                  required={"OTP", "email"},
 *                  @OA\Property(
 *                      property="OTP",
 *                      type="string",
 *                      format="digits",
 *                      example="1234",
 *                      description="The OTP to verify."
 *                  ),
 *                  @OA\Property(
 *                      property="email",
 *                      type="string",
 *                      format="email",
 *                      example="your_email@example.com",
 *                      description="The email address associated with the OTP."
 *                  )
 *              )
 *          )
 *      ),
 *      @OA\Response(
 *          response=200,
 *          description="OTP verified successfully.",
 *          @OA\JsonContent(
 *              @OA\Property(property="message", type="string", example="OTP verified successfully.")
 *          )
 *      ),
 *      @OA\Response(
 *          response=422,
 *          description="Validation error",
 *          @OA\JsonContent(
 *              @OA\Property(property="message", type="string", example="Invalid OTP or email.")
 *          )
 *      ),
 *      @OA\Response(
 *          response=500,
 *          description="Internal server error",
 *          @OA\JsonContent(
 *              @OA\Property(property="message", type="string", example="An error occurred.")
 *          )
 *      )
 * )
 * @OA\Post(
 *      path="/auth/login",
 *      summary="login",
 *      description="This endpoint login",
 *      tags={"Authentication"},
 *      @OA\RequestBody(
 *          required=true,
 *          @OA\MediaType(
 *              mediaType="application/json",
 *              @OA\Schema(
 *                  required={"email", "password"},
 *                  @OA\Property(
 *                      property="email",
 *                      type="string",
 *                      format="email",
 *                      example="your_email@example.com",
 *                      description="The user's email."
 *                  ),
 *                  @OA\Property(
 *                      property="password",
 *                      type="string",
 *                      format="password",
 *                      example="Abc1234",
 *                      description="The user's password."
 *                  )
 *              )
 *          )
 *      ),
 *      @OA\Response(
 *          response=200,
 *          description="login successfully.",
 *          @OA\JsonContent(
 *              @OA\Property(property="message", type="string", example="login successfully.")
 *          )
 *      ),
 *      @OA\Response(
 *          response=422,
 *          description="Validation error",
 *          @OA\JsonContent(
 *              @OA\Property(property="message", type="string", example="Invalid email.")
 *          )
 *      ),
 *      @OA\Response(
 *          response=500,
 *          description="Internal server error",
 *          @OA\JsonContent(
 *              @OA\Property(property="message", type="string", example="An error occurred.")
 *          )
 *      )
 * )
 * 
 * @OA\Post(
 *      path="/auth/refreshToken",
 *      summary="auth Resource",
 *      description="refreshToken",
 *      security={{ "BearerAuth": {} }},
 *      tags={"Authentication"},
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
 * 
 * @OA\Post(
 *      path="/auth/logout",
 *      summary="auth Resource",
 *      description="Logout",
 *      security={{ "BearerAuth": {} }},
 *      tags={"Authentication"},
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
 *
 */

class AuthController extends Controller
{
    //
    public function signup(Request $request) {

        try{
            $request->validate([
                'username'=> 'required|max:250',
                'email' => 'required|email|unique:users,email',
                'password' => [
                    'required',
                    'string',
                    'min:6',
                    'max:250',
                    'confirmed',
                    'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).+$/'
                ]
            ]);

            $OTP = sprintf('%04d', rand(0000,9999));
            $expiresAt = now()->addMinutes(1);

            event(new OtpRequested($request->email, $request->username, $OTP));

            UserRegistration::create([
                'OTP'=> Hash::make($OTP),
                'username' => $request->username,
                'email' => $request->email,
                'password' => $request->password,
                'otp_expires_at' => $expiresAt
            ]);

            return response()->json([
                'success' => true,
                'result' => [
                    'message' => 'send email success'
                ]
            ], 200);
            
        }
        catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => $e,
            ], 422);
        }
        catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e,
            ], 500);
        }
        
    }

    public function verifyOTP(Request $request){
        try{

            $request->validate([
                'OTP' => 'required|min:4|max:4|string',
                'email' => 'required|email'
            ]);

            $user = UserRegistration::where('email', $request->email)->first();

            if (!$user || !Hash::check($request->OTP, $user->OTP)) {
                return response()->json([
                    'success' => false,
                    'result' => [
                        'message' => 'Invalid OTP'
                    ]
                ], 401);
            }
        
            if ($user->otp_expires_at < now()) {
                return response()->json([
                    'success' => false,
                    'result' => [
                        'message' => 'IOTP expired'
                    ]
                ], 401);
            }

            UserRegistration::where('id', $user->id)->delete();

            $addUser = [
                'username' => $user->username, 
                'email' => $user->email, 
                'password' => bcrypt($user->password), 
                'role_id' => User::Role_id['USER']
            ];
            
            $user = User::create($addUser)->first();

            if (!$token = auth('api')->login($user)) {
                return response()->json(['error' => 'Unauthorized'], 401);
            }
            
            return response()->json([
                'success' => true,
                'result' => [
                    'data' => $user,
                    'access_token' => $token,
                    // 'refresh_token' => $refreshToken,
                    'token_type' => 'bearer',
                    'expires_in' => JWTAuth::factory()->getTTL() * 60
                ]
            ], 200);

        }catch (ValidationException $e) {

            return response()->json([
                'success' => false,
                'result' => [
                    'message' => $e
                ],
            ], 422);

        }
        catch (\Exception $e) {

            return response()->json([
                'success' => false,
                'result' => [
                    'message' => $e
                ],
            ], 500);

        }
    }

    public function refreshToken(){

        try{

            $currentToken = JWTAuth::getToken();

            if(!$currentToken){
                return response()->json([
                    'success' => false,
                    'result' => [
                        'message' => 'Error token'
                    ]
                ]);
            }
            
            $newToken = JWTAuth::refresh();

            return response()->json([
                'success' => false,
                'result' => [
                    'token' => $newToken,
                    'token_type' => 'bearer',
                    'expires_in' => JWTAuth::factory()->getTTL() * 60
                ],
            ]);

        }catch(\Exception $e){
            return response()->json([
                'success' => false,
                'result' => [
                    'message' => $e
                ],
            ], 500);
        }

    }

    public function login(Request $request){

        try{

            $request->validate([
                'email' => 'required|email',
                'password' => [
                    'required',
                    'string',
                    'min:6',
                    'max:250',
                    'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).+$/'
                ]
            ]);

            $user = User::where('email', $request->email)->first();

            if(!$user || !Hash::check($request->password, $user->password)){
                return response()->json([
                    'success' => false,
                    'result' => [
                        'message' => 'email or password is incorrect'
                    ]
                ], 401);
            };

            if(!$token = auth('api')->login($user)){
                return response()->json([
                    'success' => false,
                    'result' => [
                        'message' => 'Invalid OTP'
                    ]
                ], 401);
            }

            return response()->json([
                'success' => true,
                'result' => [
                    'data' => $user,
                    'access_token' => $token,
                    // 'refresh_token' => $refreshToken,
                    'token_type' => 'bearer',
                    'expires_in' => JWTAuth::factory()->getTTL() * 60
                ]
            ]);

        }catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'result' => [
                    'message' => $e
                ]
            ], 422);
        }
        catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'result' => [
                    'message' => $e
                ]
            ], 500);
        }

    }

    public function logout() {

        auth()->logout();

        return response()->json([
            'success' => false,
                'result' => [
                    'message' => 'logout success'
                ]
            ], 200
        );
    }

}
