<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\LoginController;
use App\Http\Controllers\RegisterController;
use App\Http\Controllers\GeneralController;
use App\Http\Controllers\android\driver\CheckController;
use App\Http\Controllers\android\driver\ProfileController;
use App\Http\Controllers\android\driver\HomeController;

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
Route::post('register/check', [CheckController::class, 'isRegistrable']);
Route::post('reset-password', [CheckController::class, 'resetPassword']);
Route::get('get-countrycodes', [GeneralController::class, 'getCountryCodes']);
Route::get('about', [GeneralController::class, 'getAbout']);
Route::get('privacy-policy', [GeneralController::class, 'getPrivacyPolicy']);
Route::get('terms-and-conditions', [GeneralController::class, 'getTermsandConditions']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('profile', [ProfileController::class, 'index']);
    Route::post('profile/change-password', [ProfileController::class, 'changePassword']);
    Route::post('profile/update', [ProfileController::class, 'update']);
    Route::post('profile/check-mobile', [ProfileController::class, 'isMobileUnique']);
    Route::get('home', [HomeController::class, 'index']);
    Route::get('orders/details', [HomeController::class, 'details']);
});
