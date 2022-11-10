<?php
use App\Http\Controllers\LoginController;
use App\Http\Controllers\admin\CustomerController;
use App\Http\Controllers\admin\FuelStationController;
use App\Http\Controllers\admin\FuelTypeController;
use App\Http\Controllers\admin\CustomerOrderController;

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
/*LOGIN API*/
Route::post('login/custome', [LoginController::class, 'customeLogin']);
Route::post('login/admin', [LoginController::class, 'adminLogin']);


//MIDDLEWARE


Route::middleware('auth:sanctum')->group(function () {
    Route::get('logout', [LoginController::class, 'logout']);

    Route::post('admin/customer/add', [CustomerController::class, 'add']);
    Route::post('admin/customer/update', [CustomerController::class, 'update']);
    Route::get('admin/customer/index', [CustomerController::class, 'index']);
    Route::get('admin/customer/status', [CustomerController::class, 'status']);
    Route::get('admin/customer/profile', [CustomerController::class, 'profile']);
    Route::post('admin/customer/password', [CustomerController::class, 'changePassword']);
    Route::post('admin/customer/updateProfile', [CustomerController::class, 'updateProfile']);
    Route::post('admin/fuelStation/add', [FuelStationController::class, 'add']);
    Route::post('admin/fuelStation/update', [FuelStationController::class, 'update']);
    Route::get('admin/fuelStation/index', [FuelStationController::class, 'index']);
    Route::get('admin/fuelStation/status', [FuelStationController::class, 'status']);
    Route::post('admin/fuelStation/password', [FuelStationController::class, 'changePassword']);
    Route::post('admin/fuelStation/updateDetails', [FuelStationController::class, 'updateDetails']);
    Route::get('admin/fuelStation/details', [FuelStationController::class, 'details']);

    Route::post('admin/fuel/add', [FuelTypeController::class, 'add']);
    Route::post('admin/fuel/update', [FuelTypeController::class, 'update']);
    Route::get('admin/fuel/index', [FuelTypeController::class, 'index']);
    Route::get('admin/fuel/status', [FuelTypeController::class, 'status']);
    Route::get('admin/fuel/details', [FuelTypeController::class, 'details']);


    Route::post('admin/order/add',[CustomerOrderController::class,'add']);


    // return $request->user();
});
