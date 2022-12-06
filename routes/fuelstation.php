<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\LoginController;

use App\Http\Controllers\fuelstation\DashboardController;
use App\Http\Controllers\fuelstation\ProfileController;

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

Route::post('login', [LoginController::class, 'userLogin']);
Route::post('login-with-otp', [LoginController::class, 'loginWithOtp']);

Route::middleware('auth:sanctum')->group(function () {
    
    Route::get('fuel_station/dashboard',[DashboardController::class,'index']);
   
   
    Route::get('fuel_station/profile',[ProfileController::class,'profile']);
    Route::post('fuel_station/update_profile', [ProfileController::class, 'updateProfile']);
    Route::post('fuel_station/change_password', [ProfileController::class, 'changePassword']);


    });
