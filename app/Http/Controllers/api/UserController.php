<?php

namespace App\Http\Controllers\api;

use App\Events\OtpRequested;
use App\Http\Controllers\Controller;
use App\Mail\WelcomeEmail;
use App\Models\UserRegistration;
use Error;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;

/**
 * @OA\Post(
 *   path="/signup",
 *   summary="Đăng ký",
 *   tags={"User"},
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
 * )
 */

class UserController extends Controller
{
    //
    public function signup(Request $request) {
        // return response()->json([
        //     'data' => $request->all()
        // ]);

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

            $OTP = rand(0000,9999);
            $expiresAt = now()->addMinutes(1);

            event(new OtpRequested($request->get('email'), $request->get('username'), $OTP));

            UserRegistration::query()->create([
                'OTP'=>$OTP,
                'username' => $request->get('username'), 
                'email' => $request->get('email'), 
                'password' => bcrypt($request->get('password')), 
                'otp_expires_at' => $expiresAt
            ]);

            return response()->json([
                'success' => true,
                'result' => [
                    'data' => $request->all()
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
}
