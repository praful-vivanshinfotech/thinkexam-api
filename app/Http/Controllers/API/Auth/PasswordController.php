<?php

namespace App\Http\Controllers\API\Auth;

use App\Http\Controllers\Controller;
use App\Mail\ResetPasswordMail;
use App\Repositories\Interfaces\UserRepositoryInterface;
use App\Services\JsonResponseService;
use Carbon\Carbon;
use DB;
use Illuminate\Http\Request;
use Log;
use Mail;
use Validator;
use Illuminate\Support\Str;

class PasswordController extends Controller
{
    /**
     * UserController constructor.
     *
     * @param UserRepositoryInterface $user
     *
     */
    public function __construct(
        UserRepositoryInterface $user
    ) {
        $this->user = $user;
        $this->jsonResponseService = new JsonResponseService();
    }

    /**
     * @OA\Post(
     * path="/api/forgot-password",
     * summary="Forgot Password",
     * description="Register email required",
     * operationId="api.forgot-password",
     * tags={"Forgot Password"},
     * @OA\RequestBody(
     *    required=true,
     *    description="Enter email address",
     *    @OA\JsonContent(
     *       required={"email"},
     *       @OA\Property(property="email", type="string", format="email", example="user1@mail.com"),
     *    ),
     * ),
     * @OA\Response(
     *    response=200,
     *    description="Success",
     *    @OA\JsonContent(
     *       @OA\Property(property="message", type="string", example="Success"),
     *    )
     * ),
     * @OA\Response(
     *    response=400,
     *    description="Unauthenticated",
     *    @OA\JsonContent(
     *       @OA\Property(property="message", type="string", example="Sorry, wrong email address. Please try again")
     *    )
     * ),
     * @OA\Response(
     *    response=422,
     *    description="Validation error",
     *    @OA\JsonContent(
     *       @OA\Property(property="message", type="string", example="Validation error"),
     *    )
     *  ),
     * @OA\Response(
     *    response=500,
     *    description="Internal server error",
     *    @OA\JsonContent(
     *       @OA\Property(property="message", type="string", example="Internal server error"),
     *    )
     * )
     * )
     */
    public function forgotPassword(Request $request)
    {
        $response = [];
        $response['data'] = new \stdClass();

        try {
            // Validate the form data
            $rules = [
                'email' => 'required',
            ];

            // Get Data From Request
            $postData = $request->only('email');

            $validator = Validator::make($postData, $rules);

            // validation fail message
            if ($validator->fails()) {
                return $this->jsonResponseService->sendResponse(false, $response, $validator->messages(), 422);
            }

            $user = $this->user->findByWhereCondition([['email', '=', $request->email]]);

            if ($user == null) {
                return $this->jsonResponseService->sendResponse(false, $response, __('message.INVALID_USER'), 400);
            }

            $token = Str::random(60);

            $details = [
                'token' => $token,
                'user' => $user,
                'resetLink' => config('constant.RESET_PASSWORD_URL') . $token . '?email=' . $user->email,
            ];

            //Create Password Reset otp
            DB::table('password_resets')->insert([
                'email' => $request->email,
                'token' => $token,
                'created_at' => \Carbon\Carbon::now(),
            ]);

            Mail::to($request->email)->send(new ResetPasswordMail($details));

            // All good so return the response
            return $this->jsonResponseService->sendResponse(true, $response, "A password reset OTP has been sent to your email address", 200);
        } catch (\Exception$e) {
            Log::error(__METHOD__ . " | Error: {$e->getMessage()}");
            return $this->jsonResponseService->sendResponse(false, $response, __('message.INTERNAL_SERVE_ERROR'), 500);
        }
    }

    /**
     * @OA\Post(
     * path="/api/forgot-password-submit",
     * summary="Submit OTP",
     * description="send OTP for forgot password",
     * operationId="api.submit-otp",
     * tags={"Forgot Password"},
     * @OA\RequestBody(
     *    required=true,
     *    description="Enter your password reset otp",
     *    @OA\JsonContent(
     *       required={"email","otp"},
     *       @OA\Property(property="email", type="string", format="email", example="user1@mail.com"),
     *       @OA\Property(property="otp", type="integer", format="otp", example="123456"),
     *    ),
     * ),
     * @OA\Response(
     *    response=200,
     *    description="Success",
     *    @OA\JsonContent(
     *       @OA\Property(property="message", type="string", example="Success"),
     *    )
     * ),
     * @OA\Response(
     *    response=401,
     *    description="Unauthenticated",
     *    @OA\JsonContent(
     *       @OA\Property(property="message", type="string", example="Sorry, wrong email address. Please try again")
     *    )
     * ),
     * @OA\Response(
     *    response=422,
     *    description="Validation error",
     *    @OA\JsonContent(
     *       @OA\Property(property="message", type="string", example="Validation error"),
     *    )
     *  ),
     * @OA\Response(
     *    response=400,
     *    description="Invalid OTP value.",
     *    @OA\JsonContent(
     *       @OA\Property(property="message", type="string", example="Invalid OTP value."),
     *    )
     *  ),
     * @OA\Response(
     *    response=500,
     *    description="Internal server error",
     *    @OA\JsonContent(
     *       @OA\Property(property="message", type="string", example="Internal server error"),
     *    )
     * )
     * )
     */
    public function submitOtp(Request $request)
    {
        $response = [];
        $response['data'] = new \stdClass();

        try {
            // Validate the form data
            $rules = [
                'email' => 'required',
                'otp' => 'required',
            ];

            // Get Data From Request
            $postData = $request->only('email', 'otp');

            $validator = Validator::make($postData, $rules);

            // validation fail message
            if ($validator->fails()) {
                return $this->jsonResponseService->sendResponse(false, $response, $validator->messages(), 422);
            }

            $user = $this->user->findByWhereCondition([['email', '=', $request->email]]);
            if ($user == null) {
                return $this->jsonResponseService->sendResponse(false, $response, __('message.UNAUTHORIZE_ACTION_FOUND'), 401);
            }

            //Get the otp just created above
            $tokenData = DB::table('password_resets')->where('email', $request->email)->where('created_at', '>', Carbon::now()->subMinute(5))->latest('created_at')->first();
            if (!empty($tokenData)) {
                if ($tokenData->token == $postData['otp']) {
                    // $tokenData = DB::table('password_resets')->where('email', $request->email)->delete();

                    // All good so return the response
                    return $this->jsonResponseService->sendResponse(true, $response, __('message.OTP_IS_VERIFIED'), 200);
                } else {
                    return $this->jsonResponseService->sendResponse(true, $response, __('message.OTP_DOES_NOT_MATCH'), 400);
                }
            } else {
                $tokenData = DB::table('password_resets')->where('email', $request->email)->delete();
                return $this->jsonResponseService->sendResponse(true, $response, __('message.OTP_DOES_NOT_MATCH'), 400);
            }

        } catch (\Exception$e) {
            Log::error(__METHOD__ . " | Error: {$e->getMessage()}");
            return $this->jsonResponseService->sendResponse(false, $response, __('message.INTERNAL_SERVE_ERROR'), 500);
        }
    }

    /**
     * @OA\Post(
     * path="/api/reset-password",
     * summary="Reset Password",
     * description="send your register email with new password",
     * operationId="api.reset-password",
     * tags={"Forgot Password"},
     * @OA\RequestBody(
     *    required=true,
     *    description="Enter your email and new password",
     *    @OA\JsonContent(
     *       required={"email","password","password_confirmation"},
     *       @OA\Property(property="email", type="string", format="email", example="user1@mail.com"),
     *       @OA\Property(property="otp", type="integer", format="otp", example="123456"),
     *       @OA\Property(property="password", type="string", format="password", example="PassWord12345"),
     *       @OA\Property(property="password_confirmation", type="string", format="password", example="PassWord12345"),
     *    ),
     * ),
     * @OA\Response(
     *    response=200,
     *    description="Success",
     *    @OA\JsonContent(
     *       @OA\Property(property="message", type="string", example="Success"),
     *    )
     * ),
     * @OA\Response(
     *    response=401,
     *    description="Unauthenticated",
     *    @OA\JsonContent(
     *       @OA\Property(property="message", type="string", example="Sorry, wrong email address. Please try again")
     *    )
     * ),
     * @OA\Response(
     *    response=400,
     *    description="Invalid OTP value.",
     *    @OA\JsonContent(
     *       @OA\Property(property="message", type="string", example="Invalid OTP value."),
     *    )
     *  ),
     * @OA\Response(
     *    response=422,
     *    description="Validation error",
     *    @OA\JsonContent(
     *       @OA\Property(property="message", type="string", example="Validation error"),
     *    )
     *  ),
     * @OA\Response(
     *    response=500,
     *    description="Internal server error",
     *    @OA\JsonContent(
     *       @OA\Property(property="message", type="string", example="Internal server error"),
     *    )
     * )
     * )
     */
    public function resetPassword(Request $request)
    {
        $response = [];
        $response['data'] = new \stdClass();

        try {

            // Validate the form data
            $rules = [
                'email' => 'required',
                'otp' => 'required',
                'password' => 'required',
                'password_confirmation' => 'same:password',
            ];

            // Get Data From Request
            $data = $request->only('email', 'password', 'password_confirmation', 'otp');

            $validator = Validator::make($data, $rules);
            // validation fail message
            if ($validator->fails()) {
                return $this->jsonResponseService->sendResponse(false, $response, $validator->messages(), 422);
            }

            $user = $this->user->findByWhereCondition([['email', '=', $data['email']]]);
            if ($user == null) {
                return $this->jsonResponseService->sendResponse(false, $response, __('message.UNAUTHORIZE_ACTION_FOUND'), 401);
            }

            //Get the otp just created above
            $tokenData = DB::table('password_resets')->where('email', $data['email'])->where('created_at', '>', Carbon::now()->subMinute(5))->latest('created_at')->first();
            if (!empty($tokenData)) {
                if ($tokenData->token == $data['otp']) {
                    $tokenData = DB::table('password_resets')->where('email', $data['email'])->delete();
                } else {
                    return $this->jsonResponseService->sendResponse(true, $response, __('message.OTP_DOES_NOT_MATCH'), 400);
                }
            } else {
                $tokenData = DB::table('password_resets')->where('email', $request->email)->delete();
                return $this->jsonResponseService->sendResponse(true, $response, __('message.OTP_DOES_NOT_MATCH'), 400);
            }

            if ($request->get('password') && !empty($request->get('password'))) {
                $postData['password'] = bcrypt($request->get('password'));
            }

            $this->user->update($user->id, $postData);

            // All good so return the response
            return $this->jsonResponseService->sendResponse(true, $response, __('message.PASSWORD_UPDATED_SUCCESSFULLY'), 200);

        } catch (\Exception$e) {
            Log::error(__METHOD__ . " | Error: {$e->getMessage()}");
            return $this->jsonResponseService->sendResponse(false, $response, __('message.INTERNAL_SERVE_ERROR'), 500);
        }
    }
}
