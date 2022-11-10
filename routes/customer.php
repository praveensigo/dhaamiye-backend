<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\LoginController;
use App\Http\Controllers\android\customer\ProfileController;
use App\Http\Controllers\android\customer\CheckController;
use App\Http\Controllers\android\customer\HomeController;

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

Route::post('login', [LoginController::class, 'customeLogin']);
Route::post('forgot-password', [CheckController::class, 'isMobileRegistered']);
Route::post('forgot-password', [CheckController::class, 'isMobileRegistered']);
Route::post('register/check', [CheckController::class, 'isRegistrable']);
Route::post('reset-password', [CheckController::class, 'resetPassword']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('profile', [ProfileController::class, 'index']);
    Route::post('change-password', [ProfileController::class, 'changePassword']);
    Route::post('profile/update', [ProfileController::class, 'update']);
    Route::get('home', [HomeController::class, 'index']);
});
