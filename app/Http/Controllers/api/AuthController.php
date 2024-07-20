<?php

namespace App\Http\Controllers\api;

use App\Enums\Roles;
use App\Events\OtpRequested;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserRegistration;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

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
            ],
            [
                'username.required' => 'Chưa nhập tên đăng kí',
                'email.required' => 'Chưa nhập email',
                'password.required' => 'Chưa nhập password',
            ]
            );

            UserRegistration::where('email', $request->email)->where('otp_expires_at','<', now())->delete();

            $OTP = sprintf('%04d', rand(0000,9999));
            $expiresAt = now()->addMinutes(1);

            $title = '[OTP] đăng ký tài khoản';
            $content = "Xin chào quý khách $request->username OTP xác thực của quý khách là:";

            UserRegistration::create([
               'OTP'=> Hash::make($OTP),
               'username' => $request->username,
               'email' => $request->email,
               'password' => $request->password,
               'otp_expires_at' => $expiresAt
           ]);

            event(new OtpRequested($request->email, $content, $OTP, $title));

            return response()->json([
                'success' => true,
                'message' => 'Gửi email thành công'
            ], 200);

        }
        catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
        catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'serve error',
            ], 500);
        }

    }

    public function verifyOTP(Request $request){
        try{

            $request->validate([
                'OTP' => 'required|min:4|max:4|string',
                'email' => 'required|email|unique:users,email'
            ],
            [
                'OTP.required' => 'Chưa gửi OTP',
                'OTP.min' => 'OTP phải bằng 4',
                'OTP.max' => 'OTP phải bằng 4',
                'OTP.string' => 'OTP phải là string',
                'email.required' => 'Chưa gửi email',
            ]);

            $userRegistration = UserRegistration::where('email', $request->email)->latest()->first();

            if (!$userRegistration) {
                return response()->json([
                    'success' => false,
                    'result' => [
                        'message' => 'Không có người dùng'
                    ]
                ], 404);
            }

            if ($userRegistration->otp_expires_at < now()) {
                return response()->json([
                    'success' => false,
                    'result' => [
                        'message' => 'OTP hết hạn'
                    ]
                ], 422);
            }

            if (!Hash::check($request->OTP, $userRegistration->OTP)) {
                return response()->json([
                    'success' => false,
                    'result' => [
                        'message' => 'OTP không đúng'
                    ]
                ], 422);
            }

            $addUser = [
                'username' => $userRegistration->username,
                'email' => $userRegistration->email,
                'password' => bcrypt($userRegistration->password),
                'role_id' => Roles::getOrder(Roles::USER)
            ];

            $user = User::create($addUser);

            if (!$token = $user->createToken('authToken')->plainTextToken) {
                return response()->json(['error' => 'Unauthorized'], 401);
            }

            UserRegistration::where('email', $userRegistration->email)->delete();

            $user = User::find($user->id);

            return response()->json([
                'success' => true,
                'user' => $user,
                'access_token' => $token,
                'token_type' => 'Bearer',
            ], 200);

        }catch (ValidationException $e) {

            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 422);

        }
        catch (\Exception $e) {

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);

        }
    }

    public function forgotPassword(Request $request) {
        $request->validate([
            'email' => 'email|required',
        ],[
            'email.required' => 'Trường email là bắt buộc',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Khng tìm thấy người dùng'
            ]);
        }
    }

    public function login(Request $request)
    {

        try {

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

            if (!$user || !Hash::check($request->password, $user->password)) {
                return response()->json([
                    'success' => false,
                    'message' => 'email or password is incorrect'
                ], 422);
            };

            if (!$token = $user->createToken('authToken')->plainTextToken) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid OTP'
                ], 422);
            }

            return response()->json([
                'success' => true,
                'user' => $user,
                'access_token' => $token,
                'token_type' => 'Bearer',
            ]);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function logout() {

        try {
            Auth::user()->tokens()->delete();

            return response()->json([
                'success' => true,
                'message' => 'logout success'
            ], 200);

        }catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

}
