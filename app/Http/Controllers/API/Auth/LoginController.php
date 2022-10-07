<?php

namespace App\Http\Controllers\API\Auth;

use App\Events\Logined;
use App\Http\Controllers\Controller;
use App\Http\Requests\API\LoginRequest;
use App\Http\Requests\API\ResendOTPRequest;
use App\Http\Requests\API\VerifyOTPRequest;
use App\Models\User;
use App\Repositories\Interfaces\UserRepositoryInterface;
use App\Services\JsonResponseService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Log;

class LoginController extends Controller
{
    /**
     * LoginController constructor.
     *
     * @param UserRepositoryInterface $user
     *
     */
    public function __construct(
        UserRepositoryInterface $user,
    ) {
        $this->user = $user;
        $this->jsonResponseService = new JsonResponseService();
        $this->smsCredential = new \Nexmo\Client\Credentials\Basic(config('constant.SMS_KEY'), config('constant.SMS_SECRET'));
        $this->smsClient = new \Nexmo\Client($this->smsCredential);
    }

    protected function authenticated(Request $request, $user)
    {
        event(new Logined());
    }

    /**
     * @OA\Post(
     * path="/api/login",
     * summary="Sign in",
     * description="Login by email, password",
     * operationId="api.login",
     * tags={"Auth"},
     * @OA\RequestBody(
     *    required=true,
     *    description="Pass user credentials",
     *    @OA\JsonContent(
     *       required={"email","password"},
     *       @OA\Property(property="email", type="string", format="email", example="admin@vivanshinfotech.com"),
     *       @OA\Property(property="password", type="string", format="password", example="apt@1888"),
     *    ),
     * ),
     * @OA\Response(
     *    response=200,
     *    description="Success",
     *    @OA\JsonContent(
     *       @OA\Property(property="message", type="string", example="Success"),
     *    )
     * ),
     *  @OA\Response(
     *    response=422,
     *    description="Validation error",
     *    @OA\JsonContent(
     *       @OA\Property(property="message", type="string", example="Validation error"),
     *    )
     *  ),
     * @OA\Response(
     *    response=404,
     *    description="User Not Found",
     *    @OA\JsonContent(
     *       @OA\Property(property="message", type="string", example="No User Found"),
     *    )
     *  ),
     * @OA\Response(
     *    response=401,
     *    description="Unauthenticated",
     *    @OA\JsonContent(
     *       @OA\Property(property="message", type="string", example="We didn't recognise your details. Please check your email or password.")
     *        )
     *     )
     * )
     * @OA\Response(
     *    response=403,
     *    description="User Not Activated",
     *    @OA\JsonContent(
     *       @OA\Property(property="message", type="string", example="Your account has been inactive, Please contact administrator to activate your account."),
     *    )
     *  ),
     * @OA\Response(
     *    response=500,
     *    description="Internal server error",
     *    @OA\JsonContent(
     *       @OA\Property(property="message", type="string", example="Internal server error"),
     *    )
     * ),
     */
    public function login(LoginRequest $request)
    {
        $response['data'] = new \stdClass();
        try {
            $user = $this->user->findByWhereCondition([['email', '=', $request->email]]);

            if (empty($user)) {
                return $this->jsonResponseService->sendResponse(false, $response, __('message.NO_USER_FOUND'), 404);
            }

            if ($user->status == config('constant.INACTIVE_FLAG')) {
                return $this->jsonResponseService->sendResponse(false, $response, __('message.INACTIVE_USER'), 403);
            }

            if (!Hash::check($request->password, $user->password)) {
                return $this->jsonResponseService->sendResponse(false, $response, __('message.WRONG_EMAIL_AND_PASSWORD'), 401);
            }

            // Check 2FA enable
            if ($user->two_factor_status == config('constant.2FA_CHECKER_ENABLE_FLAG')) {
                // Generate 2FA code
                $userCode = $this->user->generate2FACode($user->id);

                if (empty($userCode)) {
                    return $this->jsonResponseService->sendResponse(false, $response, __('message.INTERNAL_SERVE_ERROR'), 500);
                }
                $data = [
                    'user' => $user->email,
                ];
                // Sms sent on user phone number
                $message = $this->smsClient->message()->send([
                    'to' => $user->phone_number,
                    'from' => config('constant.APP_NAME'),
                    'text' => config('constant.SMS_MESSAGE') . $userCode->code,
                ]);

                $response['data'] = $data;
                return $this->jsonResponseService->sendResponse(true, $response, __('message.VERIFICATION_CODE_SENT_SUCCESSFULLY'), 200);
            }

            // Logout from other Devices
            $activeTokens = $this->user->getTokens($user->id);

            foreach ($activeTokens->tokens as $_activeToken) {
                $_activeToken->revoke();
            }

            auth()->login($user);

            // Create auth token
            $token = auth()->user()->createToken('API Token')->accessToken;

            $data = [
                'user' => $user->email,
                'token' => $token,
            ];

            $response['data'] = $data;

            // All good so return the response
            return $this->jsonResponseService->sendResponse(true, $response, __('message.LOGIN_SUCCESSFULLY'), 200);
        } catch (\Exception$e) {
            Log::error(__METHOD__ . " | Error: {$e->getMessage()}");
            return $this->jsonResponseService->sendResponse(false, $response, __('message.INTERNAL_SERVE_ERROR'), 500);
        }
    }

    /**
     * @OA\Post(
     * path="/api/verify-otp",
     * summary="Sign in",
     * description="Verify OTP by email, code",
     * operationId="api.verify-otp",
     * tags={"Auth"},
     * @OA\RequestBody(
     *    required=true,
     *    description="Pass user credentials",
     *    @OA\JsonContent(
     *       required={"email","code"},
     *       @OA\Property(property="email", type="string", format="email", example="admin@vivanshinfotech.com"),
     *       @OA\Property(property="code", type="integer", format="digits:4", example="1234"),
     *    ),
     * ),
     * @OA\Response(
     *    response=200,
     *    description="Success",
     *    @OA\JsonContent(
     *       @OA\Property(property="message", type="string", example="Success"),
     *    )
     * ),
     *  @OA\Response(
     *    response=422,
     *    description="Validation error",
     *    @OA\JsonContent(
     *       @OA\Property(property="message", type="string", example="Validation error"),
     *    )
     *  ),
     * @OA\Response(
     *    response=404,
     *    description="User Not Found",
     *    @OA\JsonContent(
     *       @OA\Property(property="message", type="string", example="No User Found"),
     *    )
     *  ),
     * @OA\Response(
     *    response=403,
     *    description="User Not Activated",
     *    @OA\JsonContent(
     *       @OA\Property(property="message", type="string", example="Your account has been inactive, Please contact administrator to activate your account."),
     *    )
     *  ),
     * @OA\Response(
     *    response=400,
     *    description="OTP not verified",
     *    @OA\JsonContent(
     *       @OA\Property(property="message", type="string", example="Your account has been inactive, Please contact administrator to activate your account."),
     *    )
     *  ),
     * @OA\Response(
     *    response=500,
     *    description="Internal server error",
     *    @OA\JsonContent(
     *       @OA\Property(property="message", type="string", example="Internal server error"),
     *    )
     * ),
     */
    public function verifyOtp(VerifyOTPRequest $request)
    {
        $response['data'] = new \stdClass();
        try {
            $user = $this->user->findByWhereCondition([['email', '=', $request->email]]);
            if (empty($user)) {
                return $this->jsonResponseService->sendResponse(false, $response, __('message.NO_USER_FOUND'), 404);
            }

            if ($user->status == config('constant.INACTIVE_FLAG')) {
                return $this->jsonResponseService->sendResponse(false, $response, __('message.INACTIVE_USER'), 403);
            }

            // Find the code for verification
            $userCode = $this->user->find2FACode($user->id, $request->code);

            // If code not verfied
            if (empty($userCode)) {
                return $this->jsonResponseService->sendResponse(true, $response, __('message.OTP_DOES_NOT_MATCH'), 400);
            }

            // Logout from other Devices
            $activeTokens = $this->user->getTokens($user->id);

            foreach ($activeTokens->tokens as $_activeToken) {
                $_activeToken->revoke();
            }

            auth()->login($user);

            // Create auth token
            $token = auth()->user()->createToken('API Token')->accessToken;

            $data = [
                'user' => $user->email,
                'token' => $token,
            ];

            $response['data'] = $data;

            // All good so return the response
            return $this->jsonResponseService->sendResponse(true, $response, __('message.LOGIN_SUCCESSFULLY'), 200);
        } catch (\Exception$e) {
            Log::error(__METHOD__ . " | Error: {$e->getMessage()}");
            return $this->jsonResponseService->sendResponse(false, $response, __('message.INTERNAL_SERVE_ERROR'), 500);
        }
    }

    /**
     * @OA\Post(
     * path="/api/resend-otp",
     * summary="Sign in",
     * description="Resend OTP by email",
     * operationId="api.resend-otp",
     * tags={"Auth"},
     * @OA\RequestBody(
     *    required=true,
     *    description="Pass user credentials",
     *    @OA\JsonContent(
     *       required={"email","code"},
     *       @OA\Property(property="email", type="string", format="email", example="admin@vivanshinfotech.com"),
     *    ),
     * ),
     * @OA\Response(
     *    response=200,
     *    description="Success",
     *    @OA\JsonContent(
     *       @OA\Property(property="message", type="string", example="Success"),
     *    )
     * ),
     *  @OA\Response(
     *    response=422,
     *    description="Validation error",
     *    @OA\JsonContent(
     *       @OA\Property(property="message", type="string", example="Validation error"),
     *    )
     *  ),
     * @OA\Response(
     *    response=404,
     *    description="User Not Found",
     *    @OA\JsonContent(
     *       @OA\Property(property="message", type="string", example="No User Found"),
     *    )
     *  ),
     * @OA\Response(
     *    response=403,
     *    description="User Not Activated",
     *    @OA\JsonContent(
     *       @OA\Property(property="message", type="string", example="Your account has been inactive, Please contact administrator to activate your account."),
     *    )
     *  ),
     * @OA\Response(
     *    response=403,
     *    description="User 2FA disable",
     *    @OA\JsonContent(
     *       @OA\Property(property="message", type="string", example="Sorry, two-factor authentication is disabled with this account."),
     *    )
     *  ),
     * @OA\Response(
     *    response=500,
     *    description="Internal server error",
     *    @OA\JsonContent(
     *       @OA\Property(property="message", type="string", example="Internal server error"),
     *    )
     * ),
     */
    public function resendOtp(ResendOTPRequest $request)
    {
        $response['data'] = new \stdClass();
        try {
            $user = $this->user->findByWhereCondition([['email', '=', $request->email]]);

            if (empty($user)) {
                return $this->jsonResponseService->sendResponse(false, $response, __('message.NO_USER_FOUND'), 404);
            }

            if ($user->status == config('constant.INACTIVE_FLAG')) {
                return $this->jsonResponseService->sendResponse(false, $response, __('message.INACTIVE_USER'), 403);
            }

            // Check 2FA enable
            if ($user->two_factor_status == config('constant.2FA_CHECKER_ENABLE_FLAG')) {
                // Generate 2FA code
                $userCode = $this->user->generate2FACode($user->id);

                if (empty($userCode)) {
                    return $this->jsonResponseService->sendResponse(false, $response, __('message.INTERNAL_SERVE_ERROR'), 500);
                }
                $data = [
                    'user' => $user->email,
                ];
                // Sms sent on user phone number
                $message = $this->smsClient->message()->send([
                    'to' => $user->phone_number,
                    'from' => config('constant.APP_NAME'),
                    'text' => config('constant.SMS_MESSAGE') . $userCode->code,
                ]);
                $response['data'] = $data;
                return $this->jsonResponseService->sendResponse(true, $response, __('message.VERIFICATION_CODE_SENT_SUCCESSFULLY'), 200);
            }
            return $this->jsonResponseService->sendResponse(true, $response, __('message.2FA_DISABLE'), 403);
        } catch (\Exception$e) {
            Log::error(__METHOD__ . " | Error: {$e->getMessage()}");
            return $this->jsonResponseService->sendResponse(false, $response, __('message.INTERNAL_SERVE_ERROR'), 500);
        }
    }

    /**
     * @OA\Post(
     * path="/api/logout",
     * summary="Logout",
     * description="pass the valid token and logout",
     * operationId="api.logout",
     * tags={"Auth"},
     * security={{"passport": {}}},
     * @OA\Response(
     *    response=200,
     *    description="Success",
     *    @OA\JsonContent(
     *       @OA\Property(property="message", type="string", example="Successfully Logout"),
     *    )
     * ),
     *  @OA\Response(
     *     response=422,
     *     description="Validation error",
     *     @OA\JsonContent(
     *        @OA\Property(property="message", type="string", example="Validation error"),
     *     )
     *   ),
     *  @OA\Response(
     *     response=401,
     *     description="Returns when user is not authenticated",
     *     @OA\JsonContent(
     *        @OA\Property(property="message", type="string", example="Not authorized"),
     *     )
     *  ),
     *  @OA\Response(
     *     response=500,
     *     description="Internal server error",
     *     @OA\JsonContent(
     *        @OA\Property(property="message", type="string", example="Internal server error"),
     *     )
     *  )
     * )
     */
    public function logout(Request $request)
    {
        $response['data'] = new \stdClass();
        try {
            $accessToken = auth('api')->user()->token();
            $token = $request->user()->tokens->find($accessToken);
            $token->revoke();

            // All good so return the response
            return $this->jsonResponseService->sendResponse(true, $response, __('message.USER_LOGOUT_MESSAGE'), 200);

        } catch (\Exception$e) {
            Log::error(__METHOD__ . " | Error: {$e->getMessage()}");
            return $this->jsonResponseService->sendResponse(false, $response, __('message.INTERNAL_SERVE_ERROR'), 500);
        }
    }
}
