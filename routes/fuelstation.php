<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\LoginController;

use App\Http\Controllers\fuelstation\DashboardController;
use App\Http\Controllers\fuelstation\ProfileController;
use App\Http\Controllers\fuelstation\FuelController;
use App\Http\Controllers\fuelstation\NotificationController;
use App\Http\Controllers\fuelstation\RatingController;
use App\Http\Controllers\fuelstation\ReportController;
use App\Http\Controllers\fuelstation\PaymentController;

use App\Http\Controllers\fuelstation\FuelOrderController;
use App\Http\Controllers\fuelstation\DriverController;
use App\Http\Controllers\fuelstation\TruckController;


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

Route::post('login/fuel_station', [LoginController::class, 'fuelStationLogin']);
Route::post('login-with-otp', [LoginController::class, 'loginWithOtp']);

Route::middleware('auth:sanctum')->group(function () {
    
    Route::get('fuel_station/dashboard',[DashboardController::class,'index']);
   
   
    Route::get('fuel_station/profile',[ProfileController::class,'profile']);
    Route::post('fuel_station/profile/update_profile', [ProfileController::class, 'updateProfile']);
    Route::post('fuel_station/profile/change_password', [ProfileController::class, 'changePassword']);

    Route::get('fuel_station/fuel_types', [FuelController::class, 'index']);
    Route::post('fuel_station/fuel/add_fuel', [FuelController::class, 'addFuel']);
    Route::post('fuel_station/fuel/update_price', [FuelController::class, 'updatePrice']);
    Route::post('fuel_station/fuel/update_stock', [FuelController::class, 'updateStock']);
    Route::get('fuel_station/fuel/fuel_stock_logs', [FuelController::class, 'fuelStockLogs']);
    Route::get('fuel_station/fuel/fuel_price_logs', [FuelController::class, 'fuelPriceLogs']);
    
    Route::get('fuel_station/notification/received', [NotificationController::class, 'receivedIndex']);
    Route::get('fuel_station/notification/send', [NotificationController::class, 'sendIndex']);
    Route::post('fuel_station/notification/add', [NotificationController::class, 'add']);
    Route::post('fuel_station/notification/edit', [NotificationController::class, 'update']);
    Route::get('fuel_station/notification/status', [NotificationController::class, 'status']);
    Route::post('fuel_station/notification/delete', [NotificationController::class, 'delete']);

    Route::get('fuel_station/customerRatings',[RatingController::class,'customerRatings']);
    Route::get('fuel_station/driverRatings',[RatingController::class,'driverRatings']);


    Route::get('fuel_station/sales_report',[ReportController::class,'salesReport']);
    Route::get('fuel_station/earning_report',[ReportController::class,'earningReport']);
    Route::get('fuel_station/commission_report',[ReportController::class,'commissionReport']);
    Route::get('fuel_station/sales_report_download',[ReportController::class,'salesReportDownload']);
    Route::get('fuel_station/earning_report_download',[ReportController::class,'earningReportDownload']);
    Route::get('fuel_station/commission_report_download',[ReportController::class,'commissionReportDownload']);

    Route::get('fuel_station/payments',[PaymentController::class,'payments']);

    
    Route::post('fuel_station/order/add',[FuelOrderController::class,'add']);
    Route::get('fuel_station/order/cancelOrder',[FuelOrderController::class,'cancelOrder']);
    Route::post('fuel_station/order/assignDriver',[FuelOrderController::class,'assignDriver']);
    Route::get('fuel_station/orders',[FuelOrderController::class,'index']);
    Route::get('fuel_station/order/details',[FuelOrderController::class,'details']);
    
    
    Route::post('fuel_station/driver/add', [DriverController::class, 'add']);
    Route::post('fuel_station/driver/update', [DriverController::class, 'update']);
    Route::get('fuel_station/drivers', [DriverController::class, 'index']);
    Route::get('fuel_station/driver/details', [DriverController::class, 'details']);
    Route::post('fuel_station/driver/approve', [DriverController::class, 'approve']);
    Route::get('fuel_station/driver/status', [DriverController::class, 'status']);
    Route::get('fuel_station/driver/pending_drivers', [DriverController::class, 'pendingIndex']);
    Route::get('fuel_station/driver/pending_drivers/details', [DriverController::class, 'pendingDetails']);
    Route::post('fuel_station/driver/change_password', [DriverController::class, 'changePassword']);
    Route::get('fuel_station/driver/orders', [DriverController::class, 'orders']);
    Route::get('fuel_station/driver/earnings', [DriverController::class, 'earnings']);

    Route::post('fuel_station/truck/add', [TruckController::class, 'add']);
    Route::post('fuel_station/truck/update', [TruckController::class, 'update']);
    Route::get('fuel_station/trucks', [TruckController::class, 'index']);
    Route::get('fuel_station/truck/details', [TruckController::class, 'details']);
    Route::get('fuel_station/truck/status', [TruckController::class, 'status']);
    







    });
