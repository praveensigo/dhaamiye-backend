<?php

namespace App\Http\Controllers\fuelstation;

use App\Http\Controllers\Controller;
use App\Models\fuelstation\Driver;
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
        $user_id = auth('sanctum')->user()->user_id;

        $total_drivers = Driver::select('drivers.*', 'users.*')
            ->join('users', 'users.user_id', '=', 'drivers.id')
            ->where('users.reg_status', '1')
            ->where('users.role_id', '4')
            ->where('drivers.fuel_station_id', $user_id)
            ->where('drivers.status', '1')->get()->count();

        $deposite = DB::table('fuel_station_deposits')->select('fuel_station_deposits.*')
            ->where('fuel_station_id', $user_id)
            ->first();
        $fuel_station_deposite = $deposite->balance;

        $total_orders = DB::table('customer_orders')
            ->where('customer_orders.fuel_station_id', $user_id)
            ->where('customer_orders.status', '<>', '0')
            ->get()->count();

        $total_sales = DB::table('customer_order_payments')
            ->select(DB::raw('SUM(customer_order_payments.total_amount) as total_sales'))
            ->join('customer_orders', 'customer_orders.id', '=', 'customer_order_payments.order_id')
            ->where('customer_orders.fuel_station_id', $user_id)
            ->where('customer_order_payments.status', '2')
            ->first();

        $total_earnings = DB::table('customer_orders')
            ->select(DB::raw('(total - total_commission)as fual_station_earning'))
            ->leftjoin('customer_order_payments', 'customer_order_payments.order_id', '=', 'customer_orders.id')
            ->where('customer_order_payments.status', '2')
            ->where('customer_orders.fuel_station_id', $user_id)
            ->first();

        $todays_orders = DB::table('customer_orders')
            ->where('customer_orders.status', '<>', '0')
            ->where('customer_orders.fuel_station_id', $user_id)
            ->where(DB::raw('CAST(customer_orders.created_at as date)'), '=', $date)
            ->get()->count();

        $todays_earnings = DB::table('customer_orders')
            ->select(DB::raw('(total - total_commission)as fual_station_earning'))
            ->leftjoin('customer_order_payments', 'customer_order_payments.order_id', '=', 'customer_orders.id')
            ->where(DB::raw('CAST(customer_orders.created_at as date)'), '=', $date)
            ->where('customer_order_payments.status', '2')
            ->where('customer_orders.fuel_station_id', $user_id)
            ->first();

        $total_orders_week = DB::table('customer_orders')
            ->where('status', '<>', '0')
            ->where('customer_orders.fuel_station_id', $user_id)
            ->where('created_at', '>', Carbon::now()->startOfWeek())
            ->where('created_at', '<', Carbon::now()->endOfWeek())
            ->get()->count();

        $total_orders_month = DB::table('customer_orders')
            ->where('status', '<>', '0')
            ->where('customer_orders.fuel_station_id', $user_id)
            ->where('created_at', '>', Carbon::now()->startOfMonth())
            ->where('created_at', '<', Carbon::now()->endOfMonth())
            ->get()->count();

        $total = DB::table('customer_orders')
            ->where('customer_orders.fuel_station_id', $user_id)
            ->where('customer_orders.status', '<>', '0')
            ->get()->count();

        $pending_total = DB::table('customer_orders')->where('status', '1')
            ->where('customer_orders.fuel_station_id', $user_id)
            ->get()->count();
        $pending = $total == 0 ? 0 : $pending_total / $total * 100;
        $pendingrounded = round($pending, 2);

        $accepted_total = DB::table('customer_orders')->where('status', '2')
            ->where('customer_orders.fuel_station_id', $user_id)
            ->get()->count();
        $accepted = $total == 0 ? 0 : $accepted_total / $total * 100;
        $acceptedrounded = round($accepted, 2);

        $ongoing_total = DB::table('customer_orders')->where('status', '3')
            ->where('customer_orders.fuel_station_id', $user_id)
            ->get()->count();
        $ongoing = $total == 0 ? 0 : $ongoing_total / $total * 100;
        $ongoingrounded = round($ongoing, 2);

        $scheduled_total = DB::table('customer_orders')->where('status', '4')
            ->where('customer_orders.fuel_station_id', $user_id)
            ->get()->count();
        $scheduled = $total == 0 ? 0 : $scheduled_total / $total * 100;
        $scheduledrounded = round($scheduled, 2);

        $delivered_total = DB::table('customer_orders')->where('status', '5')
            ->where('customer_orders.fuel_station_id', $user_id)
            ->get()->count();
        $delivered = $total == 0 ? 0 : $delivered_total / $total * 100;
        $deliveredrounded = round($delivered, 2);

        $cancelled_total = DB::table('customer_orders')->where('status', '6')
            ->where('customer_orders.fuel_station_id', $user_id)
            ->get()->count();
        $cancelled = $total == 0 ? 0 : $cancelled_total / $total * 100;
        $cancelledrounded = round($cancelled, 2);

        $missed_total = DB::table('customer_orders')->where('status', '7')
            ->where('customer_orders.fuel_station_id', $user_id)
            ->get()->count();
        $missed = $total == 0 ? 0 : $missed_total / $total * 100;
        $missedrounded = round($missed, 2);

        $confirmed_month = array();
        $location = DB::table('driver_location')->select('driver_location.*')
        ->join('drivers', 'driver_location.driver_id', '=', 'drivers.id')
        ->where('drivers.fuel_station_id', $user_id)

        ->first();

        for ($iM = 1; $iM <= 12; $iM++) {
            $completed_month[] = DB::table('customer_orders')
                ->where('customer_orders.fuel_station_id', $user_id)
                ->where('status', '5')->whereYear('created_at', '=', $year)->whereMonth('created_at', '=', $iM)->get()->count();

        }

        $data = array(
            'total_drivers' => $total_drivers,
            'balance_deposite' => $fuel_station_deposite,
            'total_orders' => $total_orders,
            'total_sales' => $total_sales,
            'total_earnings' => $total_earnings,
            'total_orders_week' => $total_orders_week,
            'total_orders_month' => $total_orders_month,
            'today_earnings' => $todays_earnings,
            'today_orders' => $todays_orders,
            'completed_customer_orders' => $completed_month,
            'pending' => $pendingrounded,
            'accepted' => $acceptedrounded,
            'delivered' => $deliveredrounded,
            'cancelled' => $cancelledrounded,
            'ongoing' => $ongoingrounded,
            'scheduled' => $scheduledrounded,
            'missed' => $missedrounded,
            'driver location' => $location,

        );

        $res = Response::send(true, $data, $message = 'Success', 200);
        return $res;
    }
}
