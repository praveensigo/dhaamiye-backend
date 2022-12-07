<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\LoginController;
use App\Http\Controllers\RegisterController;
use App\Http\Controllers\GeneralController;

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

Route::post('login', [LoginController::class, 'driverLogin']);
Route::post('login-with-otp', [LoginController::class, 'loginWithOtp']);
Route::post('register', [RegisterController::class, 'driver']);
Route::post('forgot-password', [CheckController::class, 'isMobileRegistered']);
Route::post('forgot-password', [CheckController::class, 'isMobileRegistered']);
Route::post('register/check', [CheckController::class, 'isRegistrable']);
Route::post('reset-password', [CheckController::class, 'resetPassword']);
Route::get('get-countrycodes', [GeneralController::class, 'getCountryCodes']);
Route::get('about', [GeneralController::class, 'getAbout']);
Route::get('privacy-policy', [GeneralController::class, 'getPrivacyPolicy']);
Route::get('terms-and-conditions', [GeneralController::class, 'getTermsandConditions']);
