<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\PasswordController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\General\EncryptionController;

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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// use this route group to decrypt and log the request

Route::group(['middleware'=>['decrypt','logData'],'as'=>'account.'], function(){

    Route::post('register', [RegisterController::class, 'register']);
    Route::post('verify-otp', [RegisterController::class, 'otp_verification']);
    Route::post('resend-otp', [RegisterController::class, 'resend_otp']);

    Route::post('forget-password', [PasswordController::class, 'forget_otp']);
    Route::post('update-password', [PasswordController::class, 'update_password']);
    Route::post('change-password', [PasswordController::class, 'change_password'])->middleware('auth:api');

    Route::post('login', [LoginController::class, 'login']);
    Route::get('logout', [LoginController::class, 'logout'])->middleware('auth:api');

});

Route::post('decrypt', [EncryptionController::class, 'decrypt'])->middleware(['decrypt','logData']);
Route::post('encrypt', [EncryptionController::class, 'encrypt']);