<?php

namespace App\Http\Controllers\api;

use App\Events\OtpRequested;
use App\Http\Controllers\Controller;
use App\Mail\WelcomeEmail;
use App\Models\User;
use App\Models\UserRegistration;
use Error;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Client\Response;
use Illuminate\Http\Request;
use Illuminate\Http\Response as HttpResponse;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;
use Nette\Utils\Strings;

/**
 * @OA\Post(
 *   path="/signup",
 *   summary="Đăng ký",
 *   tags={"User Authentication"},
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
 *      path="/verifyOTP",
 *      summary="Verify OTP",
 *      description="This endpoint verifies an OTP sent to the user's email.",
 *      tags={"User Authentication"},
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
 *          response=400,
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
 */

class UserController extends Controller
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

            event(new OtpRequested($request->get('email'), $request->get('username'), $OTP));

            UserRegistration::query()->create([
                'OTP'=> $OTP,
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

    public function verifyOTP(Request $request){
        try{

            $request->validate([
                'OTP' => 'required|min:4|max:4|string',
                'email' => 'required|email'
            ]);

            $checkUser = UserRegistration::query()->where('email', $request->get('email'))->where('OTP', $request->get('OTP'))->where('otp_expires_at', '>', now())->first();

            if(!$checkUser){
                return response()->json([
                    'success' => true,
                    'result' => [
                        'message' => 'Invalid OTP or OTP has expired.'
                    ]
                ], HttpResponse::HTTP_UNPROCESSABLE_ENTITY);
            }

            $addUser = ['username' => $checkUser->username, 'email' => $checkUser->email, 'password' => $checkUser->password, 'role_id' => User::USER];
            $user = User::query()->create($addUser)->get();

            return response()->json([
                'success' => true,
                'result' => [
                    'data' =>  $user
                ]
            ], 200);

        }catch (ValidationException $e) {

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
