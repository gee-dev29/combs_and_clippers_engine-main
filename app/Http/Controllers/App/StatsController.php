<?php

namespace App\Http\Controllers\App;

use Exception;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Order;
use App\Models\Store;
use App\Models\Product;
use Carbon\CarbonPeriod;
use App\Models\StoreVisit;
use App\Models\Transaction;
use Illuminate\Http\Request;
use App\Exports\OrdersExport;
use Illuminate\Support\Facades\DB;
use App\Models\InternalTransaction;
use App\Http\Controllers\Controller;
use Maatwebsite\Excel\Facades\Excel;
use App\Http\Resources\OrderResource;
use Illuminate\Support\Facades\Validator;

class StatsController extends Controller
{
    public function totalBuyers(Request $request)
    {
        $merchantID = $this->getAuthID($request);
        if (!$merchantID) {
            return $this->errorResponse('User not found', 404);
        }
        try {
            $last_month_start = Carbon::today()->subMonth()->startOfMonth();
            $last_month_end = Carbon::today()->subMonth()->endOfMonth();

            $current_month_start = Carbon::today()->startOfMonth();
            $current_month_end = Carbon::today()->endOfMonth();

            $today = Carbon::today();
            $last_week_start = Carbon::today()->subWeek()->startOfWeek();
            $last_week_end = Carbon::today()->subWeek()->endOfWeek();

            $this_week_start = Carbon::today()->startOfWeek();
            $this_week_end = Carbon::today()->endOfWeek();

            //buyers stats
            $total_buyers = Order::where(['merchant_id' => $merchantID, 'payment_status' => ORDER::PAYMENT_SUCCESSFUL])->distinct('buyer_id')->count('buyer_id');
            $last_month_buyers = Order::where(['merchant_id' => $merchantID, 'payment_status' => ORDER::PAYMENT_SUCCESSFUL])
                ->whereBetween('created_at', [
                    $last_month_start,
                    $last_month_end
                ])
                ->distinct('buyer_id')
                ->count('buyer_id');
            $this_month_buyers = Order::where(['merchant_id' => $merchantID, 'payment_status' => ORDER::PAYMENT_SUCCESSFUL])
                ->whereBetween('created_at', [
                    $current_month_start,
                    $current_month_end
                ])
                ->distinct('buyer_id')
                ->count('buyer_id');
            $percentChangeInBuyers = $last_month_buyers > 0 ? ceil((($this_month_buyers - $last_month_buyers) / $last_month_buyers * 100)) : 0;

            $active_today = Order::where(['merchant_id' => $merchantID, 'payment_status' => ORDER::PAYMENT_SUCCESSFUL])->whereDate('created_at', $today)->distinct('buyer_id')->count('buyer_id');
            $last_week_buyers = Order::where(['merchant_id' => $merchantID, 'payment_status' => ORDER::PAYMENT_SUCCESSFUL])
                ->whereBetween('created_at', [
                    $last_week_start,
                    $last_week_end
                ])
                ->distinct('buyer_id')
                ->count('buyer_id');
            $this_week_buyers = Order::where(['merchant_id' => $merchantID, 'payment_status' => ORDER::PAYMENT_SUCCESSFUL])
                ->whereBetween('created_at', [
                    $this_week_start,
                    $this_week_end
                ])
                ->distinct('buyer_id')
                ->count('buyer_id');
            $percentChangeInBuyersThisWeek = $last_week_buyers > 0 ? ceil((($this_week_buyers - $last_week_buyers) / $last_week_buyers * 100)) : 0;

            //store visitor stats
            $store_id = Store::where('merchant_id', $merchantID)->latest()->first()->id;
            $total_visits = StoreVisit::where(['merchant_id' => $merchantID, 'store_id' => $store_id])->count('id');
            $last_month_visits = StoreVisit::where(['merchant_id' => $merchantID, 'store_id' => $store_id])
                ->whereBetween('created_at', [
                    $last_month_start,
                    $last_month_end
                ])
                ->count('id');
            $this_month_visits = StoreVisit::where(['merchant_id' => $merchantID, 'store_id' => $store_id])
                ->whereBetween('created_at', [
                    $current_month_start,
                    $current_month_end
                ])
                ->count('id');
            $percentChangeInVisits = $last_month_visits > 0 ? ceil((($this_month_visits - $last_month_visits) / $last_month_visits * 100)) : 0;

            $data = [
                'buyers' => [
                    'total_buyers' => $total_buyers,
                    'last_month_buyers' => $last_month_buyers,
                    'this_month_buyers' => $this_month_buyers,
                    'percentChangeInBuyers' => $percentChangeInBuyers,
                    'active_today' => $active_today,
                    'last_week_buyers' => $last_week_buyers,
                    'this_week_buyers' => $this_week_buyers,
                    'percentChangeInBuyersThisWeek' => $percentChangeInBuyersThisWeek,
                ],
                'visitors' => [
                    'total_visits' => $total_visits,
                    'last_month_visits' => $last_month_visits,
                    'this_month_visits' => $this_month_visits,
                    'percentChangeInVisits' => $percentChangeInVisits,
                ]
            ];

            return response()->json(compact('data'), 200);
        } catch (Exception $e) {
            return response()->json(["ResponseStatus" => "Unsuccessful", "ResponseCode" => 500, 'Detail' => $e->getMessage(), "ResponseMessage" => 'Something went wrong'], 500);
        }
    }

    public function totalVisits(Request $request)
    {
        $merchantID = $this->getAuthID($request);
        if (!$merchantID) {
            return $this->errorResponse('User not found', 404);
        }
        try {
            $store_id = Store::where('merchant_id', $merchantID)->latest()->first()->id;

            //monthly breakdown
            $monthly_visits = StoreVisit::selectRaw("DATE_FORMAT(created_at,'%b') as month, count(id) as total_visits")
                ->where(['merchant_id' => $merchantID, 'store_id' => $store_id])
                ->whereBetween('created_at', [
                    Carbon::today()->startOfYear(),
                    Carbon::today()->endOfYear()
                ])
                ->groupBy('month')
                ->get()
                ->keyBy('month');

            $months = collect([]);
            foreach (CarbonPeriod::create(Carbon::today()->startOfYear(), '1 month', Carbon::today()->endOfYear()) as $month) {
                $months->push($month->format('M'));
            }

            $total_visits = $months->map(function ($month) use ($monthly_visits) {
                return $monthly_visits->get($month)->total_visits ?? 0;
            })->toArray();

            $months = $months->toArray();

            $monthly_visits = array_map(function ($m, $v) {
                return [
                    'month' => $m,
                    'total_visits' => $v
                ];
            }, $months, $total_visits);


            //last 3 months breakdown
            $three_months_visits = StoreVisit::selectRaw("DATE_FORMAT(created_at,'%b') as month, count(id) as total_visits")
                ->where(['merchant_id' => $merchantID, 'store_id' => $store_id])
                ->whereBetween('created_at', [
                    Carbon::today()->subMonth(2)->startOfMonth(),
                    Carbon::today()->endOfMonth()
                ])
                ->groupBy('month')
                ->get()
                ->keyBy('month');

            $months = collect([]);
            foreach (CarbonPeriod::create(Carbon::today()->subMonth(2)->startOfMonth(), '1 month', Carbon::today()->endOfMonth()) as $month) {
                $months->push($month->format('M'));
            }

            $total_visits = $months->map(function ($month) use ($three_months_visits) {
                return $three_months_visits->get($month)->total_visits ?? 0;
            })->toArray();

            $months = $months->toArray();

            $three_months_visits = array_map(function ($m, $v) {
                return [
                    'month' => $m,
                    'total_visits' => $v
                ];
            }, $months, $total_visits);

            // weekly breakdown
            $weekly_visits = StoreVisit::selectRaw("DATE_FORMAT(created_at,'%a') as day, count(id) as total_visits")
                ->where(['merchant_id' => $merchantID, 'store_id' => $store_id])
                ->whereBetween('created_at', [
                    Carbon::today()->startOfWeek(),
                    Carbon::today()->endOfWeek()
                ])
                ->groupBy('day')
                ->get()
                ->keyBy('day');

            $days = collect([]);
            foreach (CarbonPeriod::create(Carbon::today()->startOfWeek(), '1 day', Carbon::today()->endOfWeek()) as $day) {
                $days->push($day->format('D'));
            }

            $total_visits = $days->map(function ($day) use ($weekly_visits) {
                return $weekly_visits->get($day)->total_visits ?? 0;
            })->toArray();

            $days = $days->toArray();

            $weekly_visits = array_map(function ($d, $v) {
                return [
                    'day' => $d,
                    'total_visits' => $v
                ];
            }, $days, $total_visits);


            // one month breakdown
            $one_month_visit = StoreVisit::selectRaw("DATE_FORMAT(created_at,'%D') as date, count(id) as total_visits")
                ->where(['merchant_id' => $merchantID, 'store_id' => $store_id])
                ->whereBetween('created_at', [
                    Carbon::today()->startOfMonth(),
                    Carbon::today()->endOfMonth()
                ])
                ->groupBy('date')
                ->get()
                ->keyBy('date');


            $days = collect([]);
            foreach (CarbonPeriod::create(Carbon::today()->startOfMonth(), '1 day', Carbon::today()->endOfMonth()) as $day) {
                $days->push($day->format('jS'));
            }

            $total_visits = $days->map(function ($day) use ($one_month_visit) {
                return $one_month_visit->get($day)->total_visits ?? 0;
            })->toArray();

            $days = $days->toArray();

            $one_month_visit = array_map(function ($d, $v) {
                return [
                    'date' => $d,
                    'total_visits' => $v
                ];
            }, $days, $total_visits);


            // hourly breakdown
            $hourly_visits = StoreVisit::selectRaw("DATE_FORMAT(created_at,'%l%p') as hour, count(id) as total_visits")
                ->where(['merchant_id' => $merchantID, 'store_id' => $store_id])
                ->whereBetween('created_at', [
                    Carbon::today()->startOfDay(),
                    Carbon::today()->endOfDay()
                ])
                ->groupBy('hour')
                ->get()
                ->keyBy('hour');

            $hours = collect([]);
            foreach (CarbonPeriod::create(Carbon::today()->startOfDay(), '1 hour', Carbon::today()->endOfDay()) as $hour) {
                $hours->push($hour->format('gA'));
            }

            $total_visits = $hours->map(function ($hour) use ($hourly_visits) {
                return $hourly_visits->get($hour)->total_visits ?? 0;
            })->toArray();

            $hours = $hours->toArray();

            $hourly_visits = array_map(function ($h, $v) {
                return [
                    'time' => $h,
                    'total_visits' => $v
                ];
            }, $hours, $total_visits);

            return response()->json(compact('monthly_visits', 'three_months_visits', 'one_month_visit', 'weekly_visits', 'hourly_visits'), 200);
        } catch (Exception $e) {
            return response()->json(["ResponseStatus" => "Unsuccessful", "ResponseCode" => 500, 'Detail' => $e->getMessage(), "ResponseMessage" => 'Something went wrong'], 500);
        }
    }

    public function totalRevenue(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'type' => 'nullable|string',  // in ['All', 'Processing', 'Canceled', 'Shipped', 'Delivered']
        ]);

        if ($validator->fails()) {
            return $this->validationError($validator);
        }
        $merchantID = $this->getAuthID($request);
        if (!$merchantID) {
            return $this->errorResponse('User not found', 404);
        }
        try {
            $condition = ['merchant_id' => $merchantID, 'payment_status' => ORDER::PAYMENT_SUCCESSFUL];
            if ($request->filled('type')) {
                $statusArr = cc('transaction.statusArray');
                $type = $request->type;
                $condition = isset($statusArr[$type]) ? ['merchant_id' => $merchantID, 'payment_status' => ORDER::PAYMENT_SUCCESSFUL, 'status' => $statusArr[$type]] : ['merchant_id' => $merchantID, 'payment_status' => ORDER::PAYMENT_SUCCESSFUL];
            }
            $order_today = Order::where(['merchant_id' => $merchantID, 'payment_status' => ORDER::PAYMENT_SUCCESSFUL])->whereDate('created_at', Carbon::today())->get();
            $order_yesterday = Order::where(['merchant_id' => $merchantID, 'payment_status' => ORDER::PAYMENT_SUCCESSFUL])->whereDate('created_at', Carbon::yesterday())->get();

            $today_order = $order_today->count();
            $yesterday_order = $order_yesterday->count();
            $percentChangeInOrders = $yesterday_order > 0 ? ceil((($today_order - $yesterday_order) / $yesterday_order * 100)) : 0;

            $today_revenue = $order_today->sum('totalprice');
            $yesterday_revenue = $order_yesterday->sum('totalprice');
            $percentChangeInRevenue = $yesterday_revenue > 0 ? ceil((($today_revenue - $yesterday_revenue) / $yesterday_revenue * 100)) : 0;

            $today_avg_order_value = $order_today->avg('totalprice');
            $today_avg_order_value = $today_avg_order_value ?? 0;
            $yesterday_avg_order_value = $order_yesterday->avg('totalprice');
            $yesterday_avg_order_value = $yesterday_avg_order_value ?? 0;
            $percentChangeInAverage = $yesterday_avg_order_value > 0 ? ceil((($today_avg_order_value - $yesterday_avg_order_value) / $yesterday_avg_order_value * 100)) : 0;

            //monthly breakdown
            $monthly_report = Order::selectRaw("DATE_FORMAT(created_at,'%b') as month, sum(totalprice) as total_revenue, count(id) as total_orders")
                ->where($condition)
                ->whereBetween('created_at', [
                    Carbon::today()->startOfYear(),
                    Carbon::today()->endOfYear()
                ])
                ->groupBy('month')
                ->get()
                ->keyBy('month');

            $months = collect([]);
            foreach (CarbonPeriod::create(Carbon::today()->startOfYear(), '1 month', Carbon::today()->endOfYear()) as $month) {
                $months->push($month->format('M'));
            }

            $total_revenue = $months->map(function ($month) use ($monthly_report) {
                return $monthly_report->get($month)->total_revenue ?? 0;
            })->toArray();

            $total_orders = $months->map(function ($month) use ($monthly_report) {
                return $monthly_report->get($month)->total_orders ?? 0;
            })->toArray();

            $months = $months->toArray();

            $monthly_report = array_map(function ($m, $r, $o) {
                return [
                    'month' => $m,
                    'total_revenue' => $r,
                    'total_orders' => $o
                ];
            }, $months, $total_revenue, $total_orders);


            //last 3 months breakdown
            $three_months_revenue = Order::selectRaw("DATE_FORMAT(created_at,'%b') as month, sum(totalprice) as total_revenue, count(id) as total_orders")
                ->where($condition)
                ->whereBetween('created_at', [
                    Carbon::today()->subMonth(2)->startOfMonth(),
                    Carbon::today()->endOfMonth()
                ])
                ->groupBy('month')
                ->get()
                ->keyBy('month');

            $months = collect([]);
            foreach (CarbonPeriod::create(Carbon::today()->subMonth(2)->startOfMonth(), '1 month', Carbon::today()->endOfMonth()) as $month) {
                $months->push($month->format('M'));
            }

            $total_revenue = $months->map(function ($month) use ($three_months_revenue) {
                return $three_months_revenue->get($month)->total_revenue ?? 0;
            })->toArray();

            $total_orders = $months->map(function ($month) use ($three_months_revenue) {
                return $three_months_revenue->get($month)->total_orders ?? 0;
            })->toArray();

            $months = $months->toArray();

            $three_months_revenue = array_map(function ($m, $r, $o) {
                return [
                    'month' => $m,
                    'total_revenue' => $r,
                    'total_orders' => $o
                ];
            }, $months, $total_revenue, $total_orders);

            // weekly breakdown
            $weekly_report = Order::selectRaw("DATE_FORMAT(created_at,'%a') as day, sum(totalprice) as total_revenue, count(id) as total_orders")
                ->where($condition)
                ->whereBetween('created_at', [
                    Carbon::today()->startOfWeek(),
                    Carbon::today()->endOfWeek()
                ])
                ->groupBy('day')
                ->get()
                ->keyBy('day');

            $days = collect([]);
            foreach (CarbonPeriod::create(Carbon::today()->startOfWeek(), '1 day', Carbon::today()->endOfWeek()) as $day) {
                $days->push($day->format('D'));
            }

            $total_revenue = $days->map(function ($day) use ($weekly_report) {
                return $weekly_report->get($day)->total_revenue ?? 0;
            })->toArray();

            $total_orders = $days->map(function ($day) use ($weekly_report) {
                return $weekly_report->get($day)->total_orders ?? 0;
            })->toArray();

            $days = $days->toArray();

            $weekly_report = array_map(function ($d, $r, $o) {
                return [
                    'day' => $d,
                    'total_revenue' => $r,
                    'total_orders' => $o
                ];
            }, $days, $total_revenue, $total_orders);


            // one month breakdown
            $one_month_revenue = Order::selectRaw("DATE_FORMAT(created_at,'%D') as date, sum(totalprice) as total_revenue, count(id) as total_orders")
                ->where($condition)
                ->whereBetween('created_at', [
                    Carbon::today()->startOfMonth(),
                    Carbon::today()->endOfMonth()
                ])
                ->groupBy('date')
                ->get()
                ->keyBy('date');


            $days = collect([]);
            foreach (CarbonPeriod::create(Carbon::today()->startOfMonth(), '1 day', Carbon::today()->endOfMonth()) as $day) {
                $days->push($day->format('jS'));
            }

            $total_revenue = $days->map(function ($day) use ($one_month_revenue) {
                return $one_month_revenue->get($day)->total_revenue ?? 0;
            })->toArray();

            $total_orders = $days->map(function ($day) use ($one_month_revenue) {
                return $one_month_revenue->get($day)->total_orders ?? 0;
            })->toArray();

            $days = $days->toArray();

            $one_month_revenue = array_map(function ($d, $r, $o) {
                return [
                    'date' => $d,
                    'total_revenue' => $r,
                    'total_orders' => $o
                ];
            }, $days, $total_revenue, $total_orders);


            // hourly breakdown
            $hourly_report = Order::selectRaw("DATE_FORMAT(created_at,'%l%p') as hour, sum(totalprice) as total_revenue, count(id) as total_orders")
                ->where($condition)
                ->whereBetween('created_at', [
                    Carbon::today()->startOfDay(),
                    Carbon::today()->endOfDay()
                ])
                ->groupBy('hour')
                ->get()
                ->keyBy('hour');

            $hours = collect([]);
            foreach (CarbonPeriod::create(Carbon::today()->startOfDay(), '1 hour', Carbon::today()->endOfDay()) as $hour) {
                $hours->push($hour->format('gA'));
            }

            $total_revenue = $hours->map(function ($hour) use ($hourly_report) {
                return $hourly_report->get($hour)->total_revenue ?? 0;
            })->toArray();

            $total_orders = $hours->map(function ($hour) use ($hourly_report) {
                return $hourly_report->get($hour)->total_orders ?? 0;
            })->toArray();

            $hours = $hours->toArray();

            $hourly_report = array_map(function ($h, $r, $o) {
                return [
                    'time' => $h,
                    'total_revenue' => $r,
                    'total_orders' => $o
                ];
            }, $hours, $total_revenue, $total_orders);

            $total_revenue = Order::where($condition)->whereBetween('created_at', [Carbon::today()->startOfYear(), Carbon::today()->endOfYear()])->sum('totalprice');
            return response()->json(compact('today_order', 'percentChangeInOrders', 'today_revenue', 'percentChangeInRevenue', 'today_avg_order_value', 'percentChangeInAverage', 'monthly_report', 'three_months_revenue', 'one_month_revenue', 'weekly_report', 'hourly_report', 'total_revenue'), 200);
        } catch (Exception $e) {
            return response()->json(["ResponseStatus" => "Unsuccessful", "ResponseCode" => 500, 'Detail' => $e->getMessage(), "ResponseMessage" => 'Something went wrong'], 500);
        }
    }

    public function balanceOverTime(Request $request)
    {
        $merchantID = $this->getAuthID($request);
        $merchant = User::find($merchantID);

        if (!$merchant) {
            return $this->errorResponse('User not found', 404);
        }
        try {
            //monthly breakdown
            $monthly_deposits = InternalTransaction::selectRaw("DATE_FORMAT(created_at,'%b') as month, sum(amount) as deposits")
                ->where(['merchant_id' => $merchant->id, 'payment_status' => Transaction::SUCCESSFUL, 'type' => 'credit'])
                ->whereBetween('created_at', [
                    Carbon::today()->startOfYear(),
                    Carbon::today()->endOfYear()
                ])
                ->groupBy('month')
                ->get()
                ->keyBy('month');

            $monthly_withdrawal = InternalTransaction::selectRaw("DATE_FORMAT(created_at,'%b') as month, sum(amount) as withdrawals")
                ->where(['merchant_id' => $merchant->id, 'payment_status' => Transaction::SUCCESSFUL, 'type' => 'debit'])
                ->whereBetween('created_at', [
                    Carbon::today()->startOfYear(),
                    Carbon::today()->endOfYear()
                ])
                ->groupBy('month')
                ->get()
                ->keyBy('month');

            $months = collect([]);
            foreach (CarbonPeriod::create(Carbon::today()->startOfYear(), '1 month', Carbon::today()->endOfYear()) as $month) {
                $months->push($month->format('M'));
            }

            $total_deposit = $months->map(function ($month) use ($monthly_deposits) {
                return $monthly_deposits->get($month)->deposits ?? 0;
            })->toArray();

            $total_withdrawal = $months->map(function ($month) use ($monthly_withdrawal) {
                return $monthly_withdrawal->get($month)->withdrawals ?? 0;
            })->toArray();


            $months = $months->toArray();

            $balanceOverTime = array_map(function ($m, $d, $w) {
                return [
                    'month' => $m,
                    'total_deposit' => $d,
                    'total_withdrawal' => $w,
                    'balance' => ($d - $w),
                ];
            }, $months, $total_deposit, $total_withdrawal);

            return response()->json(compact('balanceOverTime'), 200);
        } catch (Exception $e) {
            return response()->json(["ResponseStatus" => "Unsuccessful", "ResponseCode" => 500, 'Detail' => $e->getMessage(), "ResponseMessage" => 'Something went wrong'], 500);
        }
    }

    public function exportReport(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'type' => 'nullable|string',  // in ['All', 'Processing', 'Canceled', 'Shipped', 'Delivered']
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date',
        ]);

        if ($validator->fails()) {
            return $this->validationError($validator);
        }
        $merchantID = $this->getAuthID($request);
        if (!$merchantID) {
            return $this->errorResponse('User not found', 404);
        }
        try {
            DB::statement("SET SQL_MODE=''");
            $orders = DB::table('orders')
                ->selectRaw("orderRef, users.name AS Buyer, DATE_FORMAT(orders.created_at, '%b %e, %Y') AS orderDate, status, GROUP_CONCAT(order_items.productname SEPARATOR ', ') AS productName, FORMAT(totalprice,2) ,
                CASE  
                WHEN  status = 0 THEN 'Canceled'  
                WHEN  status = 1 THEN 'Unpaid'  
                WHEN  status = 2 THEN 'Paid'  
                WHEN status = 3 THEN 'Processing' 
                WHEN status = 4 THEN 'shipped' 
                WHEN status = 5 THEN 'Delivered' 
                WHEN status = 6 THEN 'Completed' 
                WHEN status = 7 THEN 'Disputed' 
                WHEN status = 8 THEN 'Refunded' 
                WHEN status = 9 THEN 'Replaced' 
                ELSE 'Undefined' END AS status")
                ->join('users', 'users.id', '=', 'orders.buyer_id')
                ->join('order_items', 'orders.id', '=', 'order_items.order_id')
                ->where(['merchant_id' => $merchantID, 'payment_status' => ORDER::PAYMENT_SUCCESSFUL])
                ->groupBy('order_items.order_id');

            if ($request->filled('start_date')) {
                $orders = $orders->whereDate('orders.created_at', '>=', $request->start_date);
            }
            if ($request->filled('end_date')) {
                $orders = $orders->whereDate('orders.created_at', '<=', $request->end_date);
            }
            if ($request->filled('type')) {
                $statusArr = cc('transaction.statusArray');
                $type = $request->type;
                $orders = isset($statusArr[$type]) ? $orders->where('status', $statusArr[$type]) : $orders;
            }

            $orders = $orders->get();
            $home = asset('/storage');
            $filepath = '/reports/' . $merchantID . '/' . 'orders.xlsx';
            $absolutePath = $home . $filepath;
            $download = Excel::store(new OrdersExport($orders),  $filepath);
            if ($download) {
                return response()->json(['ResponseStatus' => 'Successful', 'file' => $absolutePath], 200);
            }
            return $this->errorResponse('Sorry! report could not be generated', 400);
        } catch (Exception $e) {
            return response()->json(["ResponseStatus" => "Unsuccessful", "ResponseCode" => 500, 'Detail' => $e->getMessage(), "ResponseMessage" => 'Something went wrong'], 500);
        }
    }

    public function recentOrders(Request $request)
    {
        $customerID = $this->getAuthID($request);
        $merchant = User::find($customerID);
        if (!$merchant) {
            return $this->errorResponse('User not found', 404);
        }
        try {
            $condition = ['merchant_id' => $merchant->id, 'payment_status' => ORDER::PAYMENT_SUCCESSFUL];
            $recentOrders = Order::where($condition)->latest()->limit(10)->get();
            $recentOrders = OrderResource::collection($recentOrders);
            return response()->json(compact('recentOrders'), 200);
        } catch (Exception $e) {
            return response()->json(["ResponseStatus" => "Unsuccessful", "ResponseCode" => 500, 'Detail' => $e->getMessage(), "ResponseMessage" => 'Something went wrong'], 500);
        }
    }

    public function overview(Request $request)
    {
        $customerID = $this->getAuthID($request);
        $merchant = User::find($customerID);
        if (!$merchant) {
            return $this->errorResponse('User not found', 404);
        }
        try {
            $condition = ['merchant_id' => $merchant->id, 'payment_status' => ORDER::PAYMENT_SUCCESSFUL];
            $total_customers = Order::where($condition)->distinct('buyer_id')->count('buyer_id');
            $total_orders = Order::where($condition)->count('id');
            $total_revenue = Order::where($condition)->sum('totalprice');
            $total_products = Product::where(['merchant_id' => $merchant->id])->count('id');
            $total_visits = StoreVisit::where(['merchant_id' => $merchant->id])->count('id');

            $data = [
                'total_customers' => $total_customers,
                'total_orders' => $total_orders,
                'total_revenue' => $total_revenue,
                'total_products' => $total_products,
                'total_visits' => $total_visits,
            ];

            return response()->json(compact('data'), 200);
        } catch (Exception $e) {
            return response()->json(["ResponseStatus" => "Unsuccessful", "ResponseCode" => 500, 'Detail' => $e->getMessage(), "ResponseMessage" => 'Something went wrong'], 500);
        }
    }
}
