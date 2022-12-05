<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\admin\Customer;
use App\Models\admin\CustomerOrderFuel;
use App\Models\admin\Driver;
use App\Models\admin\FuelStation;
use App\Models\service\ResponseSender as Response;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{

    public function index(Request $request)
    {
        $date = date('Y-m-d');
        $year = date('Y');

        $total_customers = Customer::select('customers.*', 'users.*')
            ->join('users', 'users.user_id', '=', 'customers.id')
            ->where('users.reg_status', '1')
            ->where('users.role_id', '3')
            ->where('customers.status', '1')->get()->count();
       
            $total_drivers = Driver::select('drivers.*', 'users.*')
            ->join('users', 'users.user_id', '=', 'drivers.id')
            ->where('users.reg_status', '1')
            ->where('users.role_id', '4')
            ->where('drivers.status', '1')->get()->count();
        
            $total_fuel_stations = FuelStation::select('fuel_stations.*', 'users.*')
            ->join('users', 'users.user_id', '=', 'fuel_stations.id')
            ->where('users.reg_status', '1')
            ->where('users.role_id', '5')
            ->where('fuel_stations.status', '1')->get()->count();
        
            $total_orders = DB::table('customer_orders')
            ->where('customer_orders.status', '<>', '0')
            ->get()->count();
        
        
            $total_sales = DB::table('customer_order_payments')
            ->select(DB::raw('SUM(customer_order_payments.total_amount) as total_sales'))
            ->where('customer_order_payments.status', '2')
            ->first();

        
            $total_earnings = DB::table('customer_orders')
            ->select(DB::raw('SUM(customer_orders.total_commission) as total_earnings'))
            ->leftjoin('customer_order_payments', 'customer_order_payments.order_id', '=', 'customer_orders.id')
            ->where('customer_order_payments.status', '2')
            ->first();
        
            $todays_orders = DB::table('customer_orders')
            ->where('customer_orders.status', '<>', '0')
            ->where(DB::raw('CAST(customer_orders.created_at as date)'), '=', $date)
            ->get()->count();
        
            $todays_earnings = DB::table('customer_orders')
            ->select(DB::raw('SUM(customer_orders.total_commission) as total_earnings'))
            ->leftjoin('customer_order_payments', 'customer_order_payments.order_id', '=', 'customer_orders.id')
            ->where(DB::raw('CAST(customer_orders.created_at as date)'), '=', $date)
            ->where('customer_order_payments.status', '2')
            ->first();

        
            $total = DB::table('customer_orders')
            ->where('customer_orders.status', '<>', '0')
            ->get()->count();

        
            $total_orders_week = DB::table('customer_orders')
            ->where('status', '<>', '0')
            ->where('created_at', '>', Carbon::now()->startOfWeek())
            ->where('created_at', '<', Carbon::now()->endOfWeek())
            ->get()->count();
        
            $total_orders_month = DB::table('customer_orders')
            ->where('status', '<>', '0')
            ->where('created_at', '>', Carbon::now()->startOfMonth())
            ->where('created_at', '<', Carbon::now()->endOfMonth())
            ->get()->count();

        
            $pending_total = DB::table('customer_orders')->where('status', '1')->get()->count();
            $pending = $total == 0 ? 0 : $pending_total / $total * 100;
            $pendingrounded = round($pending, 2);

            $accepted_total = DB::table('customer_orders')->where('status', '2')->get()->count();
            $accepted = $total == 0 ? 0 : $accepted_total / $total * 100;
            $acceptedrounded = round($accepted, 2);

            $ongoing_total = DB::table('customer_orders')->where('status', '3')->get()->count();
            $ongoing = $total == 0 ? 0 : $ongoing_total / $total * 100;
            $ongoingrounded = round($ongoing, 2);

            $scheduled_total = DB::table('customer_orders')->where('status', '4')->get()->count();
            $scheduled = $total == 0 ? 0 : $scheduled_total / $total * 100;
            $scheduledrounded = round($scheduled, 2);

        
            $delivered_total = DB::table('customer_orders')->where('status', '5')->get()->count();
            $delivered = $total == 0 ? 0 : $delivered_total / $total * 100;
            $deliveredrounded = round($delivered, 2);

        
            $cancelled_total = DB::table('customer_orders')->where('status', '6')->get()->count();
            $cancelled = $total == 0 ? 0 : $cancelled_total / $total * 100;
            $cancelledrounded = round($cancelled, 2);

        
            $missed_total = DB::table('customer_orders')->where('status', '7')->get()->count();
            $missed = $total == 0 ? 0 : $missed_total / $total * 100;
            $missedrounded = round($missed, 2);

        
            $confirmed_month = array();
        
            for ($iM = 1; $iM <= 12; $iM++) {
            $completed_month[] = DB::table('customer_orders')->where('status', '6')->whereYear('created_at', '=', $year)->whereMonth('created_at', '=', $iM)->get()->count();
        
            }

            for ($iM = 1; $iM <= 12; $iM++) {
            $registered_customer_month[] = Customer::select('customers.*', 'users.*')
                ->join('users', 'users.user_id', '=', 'customers.id')
                ->where('users.reg_status', '1')
                ->where('users.role_id', '3')
                ->where('customers.status', '1')->whereYear('users.created_at', '=', $year)->whereMonth('users.created_at', '=', $iM)->get()->count();
               }

             $total_fuel_orders = CustomerOrderFuel::select('customer_order_fuels.*')
            ->get()->count();

        
            $total_gasoline_orders = CustomerOrderFuel::select('customer_order_fuels.*')
            ->where('customer_order_fuels.fuel_type_id', '1')->get()->count();
            $gasoline = $total == 0 ? 0 : $total_gasoline_orders / $total_fuel_orders * 100;
           $gasolinerounded = round($gasoline, 2);

            $total_diesel_orders = CustomerOrderFuel::select('customer_order_fuels.*')
            ->where('customer_order_fuels.fuel_type_id', '2')->get()->count();
             $diesel = $total == 0 ? 0 : $total_diesel_orders / $total_fuel_orders * 100;
             $dieselrounded = round($diesel, 2);

        $data = array(
            'total_customers' => $total_customers,
            'total_drivers' => $total_drivers,
            'total_fuel_stations' => $total_fuel_stations,
            'total_orders' => $total_orders,
            'total_sales' => $total_sales,
            'total_earnings' => $total_earnings,
            'total_orders_week' => $total_orders_week,
            'total_orders_month' => $total_orders_month,
            'today_earnings' => $todays_earnings,
            'today_orders' => $todays_orders,
            'completed_customer_orders' => $completed_month,
            'registered_customer_month' => $registered_customer_month,
            'pending' => $pendingrounded,
            'accepted' => $acceptedrounded,
            'delivered' => $deliveredrounded,
            'cancelled' => $cancelledrounded,
            'ongoing' => $ongoingrounded,
            'scheduled' => $scheduledrounded,
            'missed' => $missedrounded,
            'diesel' => $dieselrounded,
            'gasoline' => $gasolinerounded,

        );

        $res = Response::send(true, $data, $message = 'Success', 200);
        return $res;
    }
}