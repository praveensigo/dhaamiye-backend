<?php
use App\Http\Controllers\LoginController;
use App\Http\Controllers\RegisterController;
use App\Http\Controllers\admin\CustomerController;
use App\Http\Controllers\admin\FuelStationController;
use App\Http\Controllers\admin\FuelTypeController;
use App\Http\Controllers\admin\CustomerOrderController;
use App\Http\Controllers\admin\DriverController;
use App\Http\Controllers\admin\TruckController;
use App\Http\Controllers\admin\CouponController;
use App\Http\Controllers\admin\SettingsController;
use App\Http\Controllers\admin\CmsController;
use App\Http\Controllers\admin\EnquiriesController;
use App\Http\Controllers\admin\SubAdminController;
use App\Http\Controllers\admin\ReportsController;
use App\Http\Controllers\admin\NotificationController;
use App\Http\Controllers\admin\SliderController;
use App\Http\Controllers\admin\RatingController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\admin\DashBoardController;
use App\Http\Controllers\admin\TruckFuelsController;

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
Route::post('login/user', [LoginController::class, 'userLogin']);
Route::post('login/admin', [LoginController::class, 'adminLogin']);
Route::post('login/user_with_otp', [LoginController::class, 'loginWithOtp']);
Route::post('login/driver', [LoginController::class, 'driverLogin']);
Route::post('login/fuel_station', [LoginController::class, 'fuelStationLogin']);
Route::post('login/user_with_otp/driver', [LoginController::class, 'loginWithOtpDriver']);
Route::post('login/user_with_otp/fuel_station', [LoginController::class, 'loginWithOtpFuelStation']);

/*REGISTER API*/
Route::post('register/customer', [RegisterController::class, 'customer']);
Route::post('register/fuel_station', [RegisterController::class, 'fuelStation']);
Route::post('register/driver', [RegisterController::class, 'driver']);
Route::post('register/sub_admin', [RegisterController::class, 'subAdmin']);

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
    Route::get('admin/fuel_station_fuels', [FuelStationController::class, 'FuelStationFuels']);

    Route::post('admin/fuel/add', [FuelTypeController::class, 'add']);
    Route::post('admin/fuel/update', [FuelTypeController::class, 'update']);
    Route::get('admin/fuel/index', [FuelTypeController::class, 'index']);
    Route::get('admin/fuel/status', [FuelTypeController::class, 'status']);
    Route::get('admin/fuel/details', [FuelTypeController::class, 'details']);


    Route::post('admin/order/add',[CustomerOrderController::class,'add']);

    Route::post('admin/driver/add', [DriverController::class, 'add']);
    Route::post('admin/driver/update', [DriverController::class, 'update']);
    Route::get('admin/drivers', [DriverController::class, 'index']);
    Route::get('admin/driver/details', [DriverController::class, 'details']);
    Route::post('admin/driver/approve', [DriverController::class, 'approve']);
    Route::get('admin/driver/status', [DriverController::class, 'status']);
    Route::get('admin/driver/pending_drivers', [DriverController::class, 'pendingIndex']);
    Route::get('admin/driver/pending_drivers/details', [DriverController::class, 'pendingDetails']);
    Route::post('admin/driver/change_password', [DriverController::class, 'changePassword']);
    Route::get('admin/driver/orders', [DriverController::class, 'orders']);
    Route::get('admin/driver/earnings', [DriverController::class, 'earnings']);
    Route::get('admin/getTrucks', [DriverController::class, 'getTrucks']);
    Route::get('admin/getFuelStations', [DriverController::class, 'getFuelStations']);
    Route::get('admin/getDrivers', [DriverController::class, 'getDrivers']);
    Route::get('admin/getCustomers', [DriverController::class, 'getCustomers']);
    Route::get('admin/getFuels', [DriverController::class, 'getFuels']);
    Route::get('admin/getRoles', [DriverController::class, 'getRoles']);
    Route::get('admin/getFuelStationDrivers', [DriverController::class, 'getFuelStationDrivers']);
    Route::get('admin/getFuelTrucks', [DriverController::class, 'getFuelTrucks']);


    Route::post('admin/truck/add', [TruckController::class, 'add']);
    Route::post('admin/truck/update', [TruckController::class, 'update']);
    Route::get('admin/trucks', [TruckController::class, 'index']);
    Route::get('admin/truck/details', [TruckController::class, 'details']);
    Route::get('admin/truck/status', [TruckController::class, 'status']);
    Route::get('admin/truck/approve', [TruckController::class, 'approve']);
    Route::get('admin/truck/pending_trucks', [TruckController::class, 'pendingIndex']);
    Route::get('admin/driver/pending_trucks/details', [TruckController::class, 'pendingDetails']);


    Route::post('admin/coupon/add', [CouponController::class, 'add']);
    Route::post('admin/coupon/edit', [CouponController::class, 'update']);
    Route::get('admin/coupons', [CouponController::class, 'index']);
    Route::get('admin/coupon/status', [CouponController::class, 'status']);

    Route::get('admin/settings/index',[SettingsController::class,'index']);
    Route::post('admin/edit',[SettingsController::class,'updateAdmin']);
    Route::post('admin/change_password',[SettingsController::class,'changePassword']);
    Route::post('admin/settings/charge_update',[SettingsController::class,'updateCharges']);
    Route::post('admin/settings/maintenance_update',[SettingsController::class,'updateMaintenance']);
    Route::post('admin/settings/version_control_update',[SettingsController::class,'updateVersionControl']);

    Route::post('admin/about/edit',[CmsController::class,'updateAbout']);
    Route::post('admin/policy/edit',[CmsController::class,'updatePolicy']);
    Route::post('admin/term/edit',[CmsController::class,'updateTerm']);
    Route::get('admin/about',[CmsController::class,'indexAbout']);
    Route::get('admin/terms',[CmsController::class,'indexTerms']);
    Route::get('admin/policy',[CmsController::class,'indexPolicy']);
    Route::get('admin/enquiries',[EnquiriesController::class,'index']);

    Route::post('admin/sub_admin/add',[SubAdminController::class,'add']);
    Route::post('admin/sub_admin/edit',[SubAdminController::class,'update']);
    Route::get('admin/sub_admin/status',[SubAdminController::class,'status']);
    Route::get('admin/sub_admins',[SubAdminController::class,'index']);
    Route::post('admin/sub_admin/add_module',[SubAdminController::class,'addModules']);

    Route::get('admin/sales_report',[ReportsController::class,'salesReport']);
    Route::get('admin/earning_report',[ReportsController::class,'earningReport']);
    Route::get('admin/sales_report_download',[ReportsController::class,'salesReportDownload']);
    Route::get('admin/earning_report_download',[ReportsController::class,'earningReportDownload']);

    Route::get('admin/notification', [NotificationController::class, 'index']);
    Route::post('admin/notification/add', [NotificationController::class, 'add']);
    Route::post('admin/notification/edit', [NotificationController::class, 'update']);
    Route::get('admin/notification/status', [NotificationController::class, 'status']);
    Route::post('admin/notification/delete', [NotificationController::class, 'delete']);


    Route::get('admin/fuelStation/orders', [FuelStationController::class, 'order_index']);
    Route::get('admin/fuelStation/trucks', [FuelStationController::class, 'trucks']);
    Route::get('admin/fuelStation/drivers', [FuelStationController::class, 'drivers']);
    Route::get('admin/fuelStation/fuelTypes', [FuelStationController::class, 'FuelTypes']);
    Route::post('admin/fuelStation/addFuel', [FuelStationController::class, 'addFuel']);
    Route::post('admin/fuelStation/updatePrice', [FuelStationController::class, 'updatePrice']);
    Route::post('admin/fuelStation/updateStock', [FuelStationController::class, 'updateStock']);
    Route::get('admin/fuelStation/fuelPriceLogs', [FuelStationController::class, 'FuelPriceLogs']);
    Route::get('admin/fuelStation/fuelStockLogs', [FuelStationController::class, 'FuelStockLogs']);
    Route::get('admin/fuelStation/paymentLogs', [FuelStationController::class, 'paymentLogs']);
    Route::get('admin/fuelStation/earningLogs', [FuelStationController::class, 'earningLogs']);

    Route::get('admin/sliders',[SliderController::class,'index']);
    Route::post('admin/slider/add',[SliderController::class,'add']);
    Route::get('admin/slider/status',[SliderController::class,'status']);
    Route::get('admin/slider/delete',[SliderController::class,'delete']);

    Route::get('admin/customerRatings',[RatingController::class,'customerRatings']);
    Route::get('admin/driverRatings',[RatingController::class,'driverRatings']);

    Route::get('admin/dashboard',[DashboardController::class,'index']);

    Route::get('admin/order/cancelOrder',[CustomerOrderController::class,'cancelOrder']);
    Route::post('admin/order/assignDriver',[CustomerOrderController::class,'assignDriver']);
    Route::get('admin/orders',[CustomerOrderController::class,'index']);
    Route::get('admin/order/details',[CustomerOrderController::class,'details']);
    
    Route::get('admin/order/startOrder',[CustomerOrderController::class,'startOrder']);
    Route::get('admin/order/completeOrder',[CustomerOrderController::class,'completeOrder']);

    Route::post('admin/fuelStation/updateDeposite', [FuelStationController::class, 'updateDeposite']);

    Route::get('admin/fuel_station/truck/fuels', [TruckFuelsController::class, 'index']);
    Route::post('admin/fuel_station/truck/add_fuel', [TruckFuelsController::class, 'add']);
    Route::post('admin/fuel_station/truck/update_stock', [TruckFuelsController::class, 'UpdateStock']);
    Route::get('admin/fuel_station/truck/stock_logs', [TruckFuelsController::class, 'StockLogs']);

    // return $request->user();
});
