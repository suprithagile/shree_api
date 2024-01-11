<?php

use App\Http\Controllers\Api\V1\Auth\LoginApiController;
use App\Http\Controllers\Api\V1\Auth\RecoverPasswordApiController;
use App\Http\Controllers\Api\V1\Auth\UserRegistrationApiController;
use App\Http\Controllers\Api\V1\Admin\UserApiController;
use Illuminate\Http\Request;
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

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});
//Registration
//Route::post('register_user', [UserRegistrationApiController::class, 'sendRegistrationOtp']);
Route::post('register_user', [UserRegistrationApiController::class, 'userRegistration']);
Route::post('registration_otp_validate', [UserRegistrationApiController::class, 'validateRegistrationOtp']);

//Login
Route::post('/login', [LoginApiController::class, 'login']);

//Password Reset and Resend Otp
Route::post('/resetuserpassword', [ChangePasswordApiController::class, 'changePassword']);
Route::post('reset_password', [RecoverPasswordApiController::class, 'sendPasswordReset']);
Route::post('resend_otp_validate', [RecoverPasswordApiController::class, 'sendRegistrationOtp']);


Route::get('/fetchuser', [UserApiController::class, 'fetchUser']);
Route::group(['as' => 'api.', 'middleware' => ['auth:api']], function () {

    //UserApiController


    //MenuApiController
    Route::resource('menu', 'App\Http\Controllers\Api\V1\Admin\MenuApiController');
    Route::get('parentmenus', [MenuApiController::class, 'parentMenus']);
    Route::get('rolemenu', [MenuApiController::class, 'rolemenu']);
    Route::get('menutree', [MenuApiController::class, 'menutree']);
    Route::get('getmenuaccess/{roleid}', [MenuApiController::class, 'getmenuaccess']);
    Route::post('storemenuaccess', [MenuApiController::class, 'storemenuaccess']);
});
