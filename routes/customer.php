<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\LoginController;
use App\Http\Controllers\android\customer\ProfileController;
use App\Http\Controllers\android\customer\CheckController;
use App\Http\Controllers\android\customer\HomeController;
use App\Http\Controllers\android\customer\OrderController;
use App\Http\Controllers\android\customer\ReviewController;
use App\Http\Controllers\android\customer\FavoriteController;
use App\Http\Controllers\android\customer\PromotionController;

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
    Route::post('profile/change-password', [ProfileController::class, 'changePassword']);
    Route::post('profile/update', [ProfileController::class, 'update']);
    Route::get('home', [HomeController::class, 'index']);
    Route::get('search', [HomeController::class, 'search']);
    Route::get('fuel-types', [HomeController::class, 'getFuelStationFuels']);
    Route::post('check-stock', [OrderController::class, 'isQuantityInStock']);
    Route::post('apply-coupon', [OrderController::class, 'applyCoupon']);
    Route::post('request-fuel', [OrderController::class, 'requestFuel']);
    Route::post('book-now', [OrderController::class, 'bookNowSchedule']);
    Route::post('order/confirm', [OrderController::class, 'confirmOrder']);
    Route::get('my-orders', [OrderController::class, 'index']);
    Route::get('order/details', [OrderController::class, 'details']);
    Route::post('order/cancel', [OrderController::class, 'cancel']);
    Route::post('order/add-review', [ReviewController::class, 'add']);
    Route::post('favourites/add-remove', [FavoriteController::class, 'addRemove']);
    Route::get('favourites', [FavoriteController::class, 'index']);
    Route::get('promotions', [PromotionController::class, 'index']);
});
