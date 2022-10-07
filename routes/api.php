<?php
use App\Http\Controllers\API\Auth\LoginController;
use App\Http\Controllers\API\Auth\PasswordController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
 */

// Login
Route::post('/login', [LoginController::class, 'login'])->name('api.login');
// Verify OTP
Route::post('/verify-otp', [LoginController::class, 'verifyOtp'])->name('api.verify-otp');
Route::post('/resend-otp', [LoginController::class, 'resendOtp'])->name('api.resend-otp');

// Forgot Password
Route::post('/forgot-password', [PasswordController::class, 'forgotPassword'])->name('api.forgot-password');
Route::post('/reset-password', [PasswordController::class, 'resetPassword'])->name('api.reset-password');

Route::group(['middleware' => 'auth:api'], function () {
    // Logout
    Route::post('/logout', [LoginController::class, 'logout'])->name('api.logout');
});
