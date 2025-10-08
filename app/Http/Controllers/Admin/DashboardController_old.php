<?php

namespace App\Http\Controllers\Admin;

use Exception;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Order;
use App\Models\Store;
use App\Models\Coupon;
use App\Models\Wallet;
use App\Models\Dispute;
use App\Models\Product;
use App\Models\Discount;
use App\Models\Referral;
use App\Jobs\SendBulkSMS;
use App\Models\AuditTrail;
use App\Repositories\Util;
use App\Models\DisputeFile;
use App\Models\Transaction;
use Illuminate\Support\Str;
use App\Models\StoreAddress;
use App\Repositories\Mailer;
use Illuminate\Http\Request;
use App\Models\PickupAddress;
use App\Models\ProductRequest;
use App\Exports\CustomersExport;
use App\Imports\MerchantsImport;
use App\Exports\AdminOrderExport;
use App\Exports\StoreVisitsExport;
use App\Jobs\RequestOrderDelivery;
use App\Jobs\RequestProductPickup;
use Illuminate\Support\Facades\DB;
use App\Exports\SubscriptionExport;
use App\Exports\TransactionsExport;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Facades\Excel;
use App\Jobs\SendBulkSMSToPhoneNumbers;
use Bmatovu\MtnMomo\Products\Collection;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;
// use Propaganistas\LaravelPhone\Rules\Phone; // Package removed



class DashboardController_old extends Controller
{

    public function dashboard()
    {
        $totalMerchants = DB::table('users')->where('account_type', 'Merchant')->count();
        $totalBuyers = DB::table('users')->where('account_type', 'Buyer')->count();
        $merchantLastWeek = DB::table('users')->where('account_type', 'Merchant')
            ->whereBetween('created_at', [Carbon::today()->subWeek()->startOfWeek(), Carbon::today()->subWeek()->endOfWeek()])
            ->count();

        $merchantThisWeek = DB::table('users')->where('account_type', 'Merchant')
            ->whereBetween('created_at', [Carbon::today()->startOfWeek(), Carbon::today()->endOfWeek()])
            ->count();


        if ($merchantLastWeek < 1) {
            $percentChangeInMerchant = 0;
        } else {
            $percentChangeInMerchant = ($merchantThisWeek - $merchantLastWeek) / $merchantLastWeek * 100;
        }


        $buyerLastWeek = DB::table('users')->where('account_type', 'Buyer')
            ->whereBetween('created_at', [Carbon::today()->subWeek()->startOfWeek(), Carbon::today()->subWeek()->endOfWeek()])
            ->count();

        $buyerThisWeek = DB::table('users')->where('account_type', 'Buyer')
            ->whereBetween('created_at', [Carbon::today()->startOfWeek(), Carbon::today()->endOfWeek()])
            ->count();


        if ($buyerLastWeek < 1) {
            $percentChangeInBuyer = 0;
        } else {
            $percentChangeInBuyer = ($buyerThisWeek - $buyerLastWeek) / $buyerLastWeek * 100;
        }


        $totalOrders = DB::table('orders')->where('payment_status', 1)->count();
        $lastWeekOrder = DB::table('orders')->where('payment_status', 1)
            ->whereBetween('created_at', [Carbon::today()->subWeek()->startOfWeek(), Carbon::today()->subWeek()->endOfWeek()])
            ->count();


        $thisWeekOrders = DB::table('orders')->where('payment_status', 1)
            ->whereBetween('created_at', [Carbon::today()->startOfWeek(), Carbon::today()->endOfWeek()])
            ->count();


        if ($lastWeekOrder < 1) {
            $percentChangeOrder = 0;
        } else {
            $percentChangeOrder = ($thisWeekOrders - $lastWeekOrder) / $lastWeekOrder * 100;
        }

        $totalRevenue = DB::table('orders')->where('payment_status', 1)
            ->select(DB::raw('sum(shipping) as shippingfee'))->get()[0];

        $lastWeekRevenue = DB::table('orders')->where('payment_status', 1)
            ->whereBetween('created_at', [Carbon::today()->subWeek()->startOfWeek(), Carbon::today()->subWeek()->endOfWeek()])
            ->select(DB::raw('sum(shipping) as shippingfee'))->get()[0];


        $thisWeekRevenue = DB::table('orders')->where('payment_status', 1)
            ->whereBetween('created_at', [Carbon::today()->startOfWeek(), Carbon::today()->endOfWeek()])
            ->select(DB::raw('sum(shipping) as shippingfee'))->get()[0];


        $totalRev = $lastWeekRevenue->totalRevenue ?? 0;

        if ($totalRev < 1) {
            $percentChangeInRevenue = 0;
        } else {
            $percentChangeInRevenue = ($thisWeekRevenue->totalRevenue - $lastWeekRevenue->totalRevenue) / $lastWeekRevenue->totalRevenue * 100;
        }


        $vendorsWithProduct = DB::table('products')->distinct('merchant_id')->count('merchant_id');

        return view('dashboard.dashboard', [
            'percentChangeOrder' => $percentChangeOrder,
            'thisWeekOrders' => $thisWeekOrders,
            'lastWeekOrder' => $lastWeekOrder,
            'totalorders' => $totalOrders,
            'totalMerchants' => $totalMerchants,
            'totalBuyers' => $totalBuyers,
            'merchantLastWeek' => $merchantLastWeek,
            'merchantThisWeek' => $merchantThisWeek,
            'percentChangeInMerchant' => ceil($percentChangeInMerchant),
            'buyerLastWeek' => $buyerLastWeek,
            'buyerThisWeek' => $buyerThisWeek,
            'percentChangeInBuyer' => ceil($percentChangeInBuyer),
            'vendorsWithProduct' => $vendorsWithProduct,
            'thisWeekRevenue' => $thisWeekRevenue,
            'lastWeekRevenue' => $lastWeekRevenue,
            'totalRevenue' => $totalRevenue,
            'percentChangeInRevenue' => ceil($percentChangeInRevenue),
            'menu' => ['account' => 'account']
        ]);
    }

    public function index()
    {
        $collection = DB::table('transactions')->orderBy('id', 'desc')
            ->get(['id', 'customer_email', 'merchant_email', 'amount', 'description', 'posting_date']);

        return view('dashboard.transactions', ['records' => collect($collection)]);
    }

    public function transactionFilter(Request $request)
    {
        $this->validate($request, [
            'date' => 'required'
        ]);
        $date = explode(' to ', $request->date);
        $from = $date[0];
        $to = $date[1] ?? Carbon::parse($from)->endOfWeek()->format('Y-m-d');
        $collection = DB::table('transactions')
            ->whereBetween('transactions.posting_date', [$from, $to])
            ->orderBy('id', 'desc')
            ->get(['id', 'customer_email', 'merchant_email', 'amount', 'description', 'posting_date']);
        switch ($request->input('action')) {
            case 'filter':
                return view('dashboard.transactions', ['records' => collect($collection)]);
                break;

            case 'export':
                return Excel::download(new TransactionsExport($collection), 'transactions.xlsx');
                break;
        }
    }

    public function details($id)
    {
        $details = DB::table('transactions')->find($id);
        return view('dashboard.details', ['details' => collect($details)]);
    }

    public function payments()
    {
        $payments = DB::table('payment_transactions')
            ->join('users', 'payment_transactions.user_id', '=', 'users.id')
            ->select('payment_transactions.id', 'users.name as customer', 'payment_transactions.channel', 'payment_transactions.trans_status', 'payment_transactions.created_at')
            ->orderBy('payment_transactions.id', 'desc')
            ->get();
        return view('dashboard.payments', ['payments' => collect($payments)]);
    }

    public function paymentDetails($id)
    {
        $details = DB::table('payment_transactions')
            ->join('users', 'payment_transactions.user_id', '=', 'users.id')
            ->select('payment_transactions.*', 'users.name as customer')
            ->where('payment_transactions.id', $id)
            ->first();
        return view('dashboard.pdetails', ['details' => collect($details)]);
    }

    public function order(Request $request)
    {
        $orders = DB::table('orders')
            ->join('users AS b', 'buyer_id', '=', 'b.id')
            ->join('users AS m', 'merchant_id', '=', 'm.id')
            ->leftJoin('order_logistics', 'orders.id', '=', 'order_logistics.order_id')
            ->select('orders.id', 'orders.orderRef', 'orders.delivery_type', 'b.name as buyer', 'm.name as merchant', 'orders.totalprice', 'order_logistics.amount as delivery_fee', 'orders.total', 'orders.payment_status', 'orders.created_at')
            ->orderBy('orders.id', 'desc');

        if ($request->filled('date')) {
            $date = explode(' to ', $request->date);
            $from = $date[0];
            if (!isset($date[1]) || empty($date[1])) {
                $orders = $orders->whereDate('orders.created_at', $from);
            } else {
                $to = $date[1];
                $orders = $orders->whereDate('orders.created_at', '>=', $from);
                $orders = $orders->whereDate('orders.created_at', '<=', $to);
            }
        }

        if ($request->filled('delivery_type')) {
            $orders = $orders->where('delivery_type', $request->delivery_type);
        }

        if ($request->filled('status')) {
            $orders = $orders->where('status', $request->status);
        }

        $orders = $orders->get();

        switch ($request->input('action')) {
            case 'filter':
                return view('dashboard.orders', ['collection' => $orders]);
                break;

            case 'export':
                $fileName = "orders_report_" . date('d-m-y_h-i-sa') . '.xlsx';
                return Excel::download(new AdminOrderExport($orders), $fileName);
                break;
        }
        return view('dashboard.orders', ['collection' => $orders]);
    }

    public function sendOrderNotification($id)
    {
        try {
            $order = Order::find($id);
            if (is_null($order)) {
                return back()->with('error', 'Order was not found.');
            }

            if ($order->payment_status != 1) {
                return back()->with('error', 'Notification can only be triggered on a paid Order.');
            }

            //send notifications
            $mailer = new Mailer;
            $mailer->sendOrderConfirmationEmail($order);
            $mailer->sendNewOrderEmail($order);

            //send sms
            $app_name = env("APP_NAME");
            $buyerMessage = "Dear {$order->buyer->name}, the payment for your order {$order->orderRef} has been received. Thank you for choosing {$app_name}!";
            $sellerMessage = "Dear {$order->seller->name}, We come with great news!, An order with reference ID: {$order->orderRef} has just been placed on your {$app_name} store.";

            return back()->with('success', 'Notification has been triggered successfully.');
        } catch (Exception $e) {
            return back()->with('error', 'Notification failed with error: ' . $e->getMessage());
        }
    }


    public function orderDetails($id)
    {
        $details = DB::table('orders')
            ->join('users AS b', 'buyer_id', '=', 'b.id')
            ->join('users AS m', 'merchant_id', '=', 'm.id')
            ->join('stores AS s', 's.merchant_id', '=', 'm.id')
            ->leftJoin('order_logistics', 'orders.id', '=', 'order_logistics.order_id')
            ->select('orders.*', 'b.name as buyer', 'm.name as merchant', 's.store_name as store_name', 'order_logistics.amount as delivery_fee', 'order_logistics.pickup_order_id as pickup_request_id', 'order_logistics.fulfilment_request_id as fulfilment_request_id')
            ->where('orders.id', $id)
            ->first();
        $items = DB::table('order_items')->where('order_id', $id)->get(['order_id', 'productname', 'price', 'quantity']);
        return view('dashboard.odetails', ['details' => collect($details), 'items' => $items]);
    }

    public function getRefunds()
    {
        $refunds = DB::table('request_refunds as r')
            ->leftJoin('users AS b', 'customer_email', '=', 'b.email')
            ->leftJoin('users AS m', 'merchant_email', '=', 'm.email')
            ->select('r.id', 'r.description', 'r.customer_email', 'r.merchant_email', 'r.date_request', 'r.transcode as trans_ref', 'b.name as cust_name', 'm.name as merchant', 'r.amount')
            ->orderBy('r.id', 'desc')
            ->get();
        return view('dashboard.refunds', ['collection' => $refunds]);
    }

    public function invoices()
    {
        $invoices = DB::table('invoices')->orderBy('id', 'desc')->get(['id', 'totalcost', 'merchantName', 'customerName', 'vat', 'totalcost', 'subTotal', 'created_at']);
        return view('dashboard.invoices', ['invoices' => $invoices]);
    }

    public function inDetails($id)
    {
        $details = collect(DB::table('invoices')->find($id));
        return view('dashboard.indetails', ['details' => $details, 'files' => collect(DB::table('invoices_files')->where('invoice_id', $id))]);
    }

    public function disputes()
    {
        $data = DB::table('disputes')->orderBy('id', 'desc')->get();
        return view('dashboard.disputes', ['data' => $data]);
    }

    public function disputeDetails($id)
    {
        $details = collect(Dispute::where('id', $id)->firstorfail());
        return view('dashboard.disputedetails', ['details' => $details, 'files' => DisputeFile::where('dispute_id', $id)->get()]);
    }

    public function shipment()
    {
        $data = DB::table('orders')
            ->join('users AS b', 'orders.buyer_id', '=', 'b.id')
            ->join('users AS m', 'orders.merchant_id', '=', 'm.id')
            ->join('stores AS s', 's.merchant_id', '=', 'm.id')
            ->leftJoin('order_logistics', 'orders.id', '=', 'order_logistics.order_id')
            ->select('orders.*', 'b.name as buyer', 'b.email as buyer_email', 'b.phone as buyer_phone', 'm.name as merchant', 's.store_name as store_name', 'm.email as merchant_email', 'm.phone as merchant_phone', 'order_logistics.fulfilment_request_id as fulfilment_id', 'order_logistics.delivery_status as delivery_status')
            ->where(['orders.delivery_type' => 'Delivery', 'orders.payment_status' => 1])
            ->orderBy('orders.id', 'desc')
            ->get();
        return view('dashboard.shipment', ['data' => $data]);
    }

    public function shipmentBooked($md)
    {
        $data = DB::table('orders')
            ->join('users AS b', 'orders.buyer_id', '=', 'b.id')
            ->join('users AS m', 'orders.merchant_id', '=', 'm.id')
            ->join('stores AS s', 's.merchant_id', '=', 'm.id')
            ->leftJoin('order_logistics', 'orders.id', '=', 'order_logistics.order_id')
            ->select('orders.*', 'b.name as buyer', 'b.email as buyer_email', 'b.phone as buyer_phone', 'm.name as merchant', 's.store_name as store_name', 'm.email as merchant_email', 'm.phone as merchant_phone', 'order_logistics.fulfilment_request_id as fulfilment_id', 'order_logistics.delivery_status as delivery_status')
            ->where(['orders.delivery_type' => 'Delivery', 'orders.payment_status' => 1, 'order_logistics.delivery_status' => $md])
            ->orderBy('orders.id', 'desc')
            ->get();
        return view('dashboard.booked_shipment', ['data' => $data]);
    }

    public function shipmentDetails($id)
    {
        $details = DB::table('orders')
            ->join('users AS b', 'orders.buyer_id', '=', 'b.id')
            ->join('users AS m', 'orders.merchant_id', '=', 'm.id')
            ->join('stores AS s', 's.merchant_id', '=', 'm.id')
            ->join('pickup_addresses AS p', 'p.merchant_id', '=', 'm.id')
            ->join('addresses AS a', 'orders.address_id', '=', 'a.id')
            ->leftJoin('order_logistics', 'orders.id', '=', 'order_logistics.order_id')
            ->select('orders.*', 'b.name as buyer', 'b.email as buyer_email', 'b.phone as buyer_phone', 'm.name as merchant', 's.store_name as store_name', 'm.email as merchant_email', 'm.phone as merchant_phone', 'order_logistics.fulfilment_request_id as fulfilment_id', 'order_logistics.delivery_status as delivery_status', 'p.address as pickup_address', 'a.address as drop_off_address')
            ->where(['orders.id' => $id, 'orders.delivery_type' => 'Delivery', 'orders.payment_status' => 1])
            ->orderBy('orders.id', 'desc')
            ->first();
        $items = DB::table('order_items')->where('order_id', $id)->get(['order_id', 'productname', 'price', 'quantity']);
        return view('dashboard.shipment_details', ['details' => collect($details), 'items' => $items]);
    }

    public function audit_trails()
    {
        $data = DB::table('audit_trails')->orderBy('id', 'desc')->get(['id', 'user_ip', 'user_id', 'event', 'location', 'created_at']);
        return view('dashboard.audit', ['data' => $data]);
    }

    public function auditDetails($id)
    {
        $details = collect(AuditTrail::where('id', $id)->first());
        return view('dashboard.audit_detail', ['details' => $details]);
    }

    public function customers(Request $request, $type)
    {
        $this->validate($request, [
            //'date' => 'required_if:action,filter'
        ]);

        $customers = User::select('users.id', 'users.account_type', 'users.name', 'users.phone', 'users.email', 'stores.store_name', 'store_categories.categoryname AS store_category', 'stores.store_description', 'stores.approved', 'users.created_at')
            ->leftJoin('stores', 'stores.merchant_id', '=', 'users.id')
            ->leftJoin('store_categories', 'store_categories.id', '=', 'stores.store_category')
            ->withExists('activeSubscriptions')
            ->withCount('products')
            ->where('users.account_type', $type)
            ->orderBy('users.created_at', 'desc');

        if ($request->filled('date')) {
            $date = explode(' to ', $request->date);
            $from = $date[0];
            if (!isset($date[1]) || empty($date[1])) {
                $customers = $customers->whereDate('users.created_at', $from);
            } else {
                $to = $date[1];
                $customers = $customers->whereDate('users.created_at', '>=', $from);
                $customers = $customers->whereDate('users.created_at', '<=', $to);
            }
        }

        if ($request->filled('has_product')) {
            if ($request->has_product == "1") {
                $customers = $customers->has('products');
            } else {
                $customers = $customers->doesntHave('products');
            }
        }

        if ($request->filled('has_pickup_address')) {
            if ($request->has_pickup_address == "1") {
                $customers = $customers->has('pickup_address');
            } else {
                $customers = $customers->doesntHave('pickup_address');
            }
        }

        if ($request->filled('has_store_address')) {
            if ($request->has_store_address == "1") {
                $customers = $customers->has('store_address');
            } else {
                $customers = $customers->doesntHave('store_address');
            }
        }

        if ($request->filled('has_sub')) {
            if ($request->has_sub == "1") {
                $customers = $customers->has('paidSubscriptions');
            } elseif ($request->has_sub == "2") {
                $customers = $customers->has('freeTrialSubscriptions');
            } else {
                $customers = $customers->doesntHave('activeSubscriptions');
            }
        }

        if ($request->filled('store_status')) {
            $customers = $customers->where('stores.approved', $request->store_status);
        }

        $customers = $customers->get();

        //dd($customers);

        switch ($request->input('action')) {
            case 'filter':
                return view('dashboard.customers', ['collection' => $customers, 'type' => $type]);
                break;

            case 'export':
                $fileName = "customers_report_" . date('d-m-y_h-i-sa') . '.xlsx';
                return Excel::download(new CustomersExport($customers), $fileName);
                break;
        }

        return view('dashboard.customers', ['collection' => $customers, 'type' => $type]);
    }

    public function addCustomerForm()
    {
        return view('dashboard.customer_add');
    }

    public function createNewsletter()
    {
        return view('dashboard.create_newsletter');
    }

    public function sendNewsletter(Request $request)
    {
        $this->validate($request, [
            'user_group' => 'required|string',
            'message' => 'required|string',
            'phones' => 'required_if:user_group,custom'
        ]);
        try {
            $user_group = $request->user_group;
            $message = $request->message;
            $phones = $request->phones;
            $phoneNumbers = explode(',', $phones);

            if ($user_group == 'all_vendor') {
                $users = User::where('account_type', 'Merchant')->get();
            } elseif ($user_group == 'no_product') {
                $users = User::where('account_type', 'Merchant')
                    ->doesntHave('products')
                    ->get();
            } elseif ($user_group == 'no_sub') {
                $users = User::where('account_type', 'Merchant')
                    ->doesntHave('activeSubscriptions')
                    ->get();
            } elseif ($user_group == 'new_today') {
                $users = User::where('account_type', 'Merchant')
                    ->whereDate('created_at', Carbon::today())
                    ->get();
            } elseif ($user_group == 'due_3') {
                $users = User::where('account_type', 'Merchant')
                    ->has('subscriptionDueIn3Days')
                    ->get();
            } elseif ($user_group == 'due_2') {
                $users = User::where('account_type', 'Merchant')
                    ->has('subscriptionDueIn2Days')
                    ->get();
            } elseif ($user_group == 'due_1') {
                $users = User::where('account_type', 'Merchant')
                    ->has('subscriptionDueIn1Day')
                    ->get();
            }

            // if ($user_group == 'custom') {
            //     SendBulkSMSToPhoneNumbers::dispatch($phoneNumbers, $message);
            // } else {
            //     if (!$users->count()) {
            //         return back()->with('error', 'No user exists in the selected user group');
            //     }
            //     SendBulkSMS::dispatch($users, $message);
            // }

            return back()->with('success', 'Newsletter successfully sent');
        } catch (Exception $e) {
            return back()->with('error', 'Newsletter failed with error: ' . $e->getMessage());
        }
    }

    public function getUserGroup(Request $request)
    {
        $this->validate($request, [
            'selectedUserGroup' => 'required|string'
        ]);
        try {
            $user_group = $request->selectedUserGroup;

            if ($user_group == 'all_vendor') {
                $users = User::where('account_type', 'Merchant')->get();
            } elseif ($user_group == 'no_product') {
                $users = User::where('account_type', 'Merchant')
                    ->doesntHave('products')
                    ->get();
            } elseif ($user_group == 'no_sub') {
                $users = User::where('account_type', 'Merchant')
                    ->doesntHave('activeSubscriptions')
                    ->get();
            } elseif ($user_group == 'new_today') {
                $users = User::where('account_type', 'Merchant')
                    ->whereDate('created_at', Carbon::today())
                    ->get();
            } elseif ($user_group == 'due_3') {
                $users = User::where('account_type', 'Merchant')
                    ->has('subscriptionDueIn3Days')
                    ->get();
            } elseif ($user_group == 'due_2') {
                $users = User::where('account_type', 'Merchant')
                    ->has('subscriptionDueIn2Days')
                    ->get();
            } elseif ($user_group == 'due_1') {
                $users = User::where('account_type', 'Merchant')
                    ->has('subscriptionDueIn1Day')
                    ->get();
            }

            if (!$users->count()) {
                $users = [];
            }

            // Return values as a JSON response
            return response()->json(["data" => $users]);
        } catch (Exception $e) {
            $users = [];
            return response()->json(["data" => $users]);
        }
    }

    public function bulkCustomerForm()
    {
        return view('dashboard.customer_bulk');
    }

    public function addBulkCustomer(Request $request)
    {
        $this->validate($request, [
            'bulk' => 'required',
        ]);
        try {
            $path = $request->file('bulk')->store('uploads');
            //Excel::import(new CustomersImport,request()->file('bulk')); 
            Excel::import(new MerchantsImport, $path);
            return back()->with('success', 'Bulk merchant uploaded successfully');
        } catch (Exception $e) {
            return back()->with('error', 'Bulk merchant uploads failed with error: ' . $e->getMessage());
        }
    }

    public function addCustomer(Request $request)
    {
        $this->validate($request, [
            'name' => 'required|string',
            'store_name' => 'required|string',
            'store_category' => 'required|integer',
            'email' => 'required|email|unique:users',
            'phone' => ['required', 'string', 'regex:/^[0-9+\-\s()]+$/'],
            'acct_type' => 'required|string',
        ]);

        try {
            $phone = $request->phone;
            $name = $request->input('name');
            $password = generatePassword();
            $name_arr = explode(" ", $name);
            $user = User::create([
                'name' => $name,
                'firstName' => isset($name_arr[0]) ? $name_arr[0] : null,
                'lastName' => isset($name_arr[1]) ? $name_arr[1] : null,
                'email' => $request->input('email'),
                'phone' => $phone,
                'password' => Hash::make($password),
                'referral_code' => generateReferralCode(),
                'account_type' => $request->input('acct_type'),
                'accountstatus' => 1,
                'token' => Str::random(64)
            ]);

            $user->update(['merchant_code' => generateShortUniqueID($user->email, $user->id)]);

            if ($request->filled('referral_code')) {
                $referrer = User::where('referral_code', $request->input('referral_code'))->first();
                if (!is_null($referrer)) {
                    Referral::create([
                        'referrer_id' => $referrer->id,
                        'customer_id' => $user->id,
                        'customer_type' => $user->account_type
                    ]);
                }
            }

            $wallet = new Wallet;
            $wallet->amount = 0;
            $wallet->save();

            $user->update(['wallet_id' => $wallet->id]);

            //create store
            $store_name = $request->store_name;
            $store_category = $request->store_category;

            $store = new Store;
            $store->merchant_id = $user->id;
            $store->store_name = $store_name;
            $store->store_category = $store_category;
            $store->save();

            //send verification and password email
            $mailer = new Mailer;
            $mailer->sendVerificationEmail($user);
            $mailer->sendPasswordEmail($user, $password);

            return redirect()->route('customers', ['type' => $request->acct_type])->with('success', 'Merchant account created successfully.');
        } catch (Exception $e) {
            return redirect()->back()->with('error', 'Merchant account creation failed with error: ' . $e->getMessage());
        }
    }

    public function customerdetails($id)
    {
        $customer = User::select('users.id', 'users.merchant_code', 'users.name', 'users.firstName', 'users.lastName', 'users.phone', 'users.email', 'users.account_type', 'users.accountstatus', 'users.referral_code', 'users.profile_image_link', 'users.email_verified', 'users.email_verified_at', 'users.created_at')
            ->withCount('activeSubscriptions')
            ->where('users.id', $id)
            ->first();
        $customer = collect($customer);

        //dd($customer);

        $orders = Order::where(['merchant_id' => $id, 'payment_status' => ORDER::PAYMENT_SUCCESSFUL])->latest()->get();

        $order_count = $orders->count();

        $products = Product::where('merchant_id', $id)->latest()->get();

        $product_count = $products->count();

        $store = Store::where('merchant_id', $id)->latest()->first();

        $pickupAddress = PickupAddress::where('merchant_id', $id)->latest()->first();

        $storeAddress = StoreAddress::where('merchant_id', $id)->latest()->first();

        return view('dashboard.cdetails', ['collection' => collect($customer), 'orders' => $orders, 'order_count' => $order_count, 'products' => $products, 'product_count' => $product_count, 'store' => $store, 'pickupAddress' => $pickupAddress, 'storeAddress' => $storeAddress]);
    }

    public function block(Request $request)
    {
        $request->validate(['id' => 'required']);

        $customer = User::find($request->id);
        if ($customer->accountstatus == 0) {
            DB::table('users')->where('id', $request->id)->update(['accountstatus' => 1]);
            $action = 'unblocked';
        } else {
            $action = 'blocked';
            DB::table('users')->where('id', $request->id)->update(['accountstatus' => 0]);
        }

        return back()->with('message', 'Successfully ' . $action . ' customer');
    }

    public function storeApproval(Request $request)
    {
        $request->validate(['store_id' => 'required']);

        $store = Store::find($request->store_id);
        if ($store->approved == 0) {
            $store->update(['approved' => 1]);
            $action = 'approved';
        } else {
            $action = 'disapproved';
            $store->update(['approved' => 0]);
        }

        return back()->with('message', 'Store ' . $action . ' successfully');
    }

    public function filterTransaction(Request $request)
    {
        $collection = DB::table('orders')
            ->join('users AS b', 'buyer_id', '=', 'b.id')
            ->join('users AS m', 'merchant_id', '=', 'm.id')
            ->select('orders.*', 'b.name as buyer', 'm.name as merchant')
            ->whereBetween('orders.created_at', [$request->from, $request->to])
            ->get();
        return view('dashboard.ddorder', ['collection' => $collection]);
    }


    public function downloadOrder(Request $request)
    {
        $fileName = 'order' . $request->from . '-' . $request->to;
        $table = DB::table('orders')
            ->join('users AS b', 'buyer_id', '=', 'b.id')
            ->join('users AS m', 'merchant_id', '=', 'm.id')
            ->select('orders.id', 'orders.total', 'orders.totalprice', 'orders.orderRef', 'b.name as buyer', 'm.name as merchant')
            ->whereBetween('orders.created_at', [$request->from, $request->to])
            ->orderBy('orders.id', 'desc')
            ->get();
        //prepare file
        $output = '';
        foreach ($table as $row) {
            $output .= implode(",", $row->toArray());
        }
        $headers = array(
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="ExportFileName.csv"',
        );
        return response()->stream($output, 200, $headers);
    }

    public function subscriptions(Request $request)
    {
        $this->validate($request, [
            //'date' => 'required_if:action,filter'
        ]);

        $subs = DB::table('users AS u')
            ->selectRaw("u.id, u.name, u.email, u.phone, s.plan, CONCAT(s.currency, FORMAT(s.price, 0)) as price, CONCAT(s.invoice_period, ' ', s.invoice_interval) as duration, us.status, us.active, us.auto_renew, us.created_at, us.expires_at")
            ->join('user_subscriptions AS us', 'us.user_id', '=', 'u.id')
            ->join('subscriptions AS s', 'us.subscription_id', '=', 's.id')
            ->orderBy('us.created_at', 'desc');

        if ($request->filled('date')) {
            $date = explode(' to ', $request->date);
            $from = $date[0];
            if (!isset($date[1]) || empty($date[1])) {
                $subs = $subs->whereDate('us.created_at', $from);
            } else {
                $to = $date[1];
                $subs = $subs->whereDate('us.created_at', '>=', $from);
                $subs = $subs->whereDate('us.created_at', '<=', $to);
            }
        }

        if ($request->filled('sub_plan')) {
            $subs = $subs->where('us.subscription_id', $request->sub_plan);
        }

        if ($request->filled('status')) {
            $subs = $subs->where('us.status', $request->status);
        }

        $subs = $subs->get();

        switch ($request->input('action')) {
            case 'filter':
                return view('dashboard.subscriptions', compact('subs'));
                break;
            case 'export':
                $fileName = "subscriptions_report_" . date('d-m-y_h-i-sa') . '.xlsx';
                return Excel::download(new SubscriptionExport($subs), $fileName);
                break;
        }

        return view('dashboard.subscriptions', compact('subs'));
    }

    public function sendyRequests()
    {
        $orders = $this->Sendy->getOrders();
        //dd($orders);
        return view('dashboard.sendy_requests', ['orders' => $orders]);
    }

    public function refundBuyer(Request $request)
    {
        $this->validate($request, [
            'orderID' => 'integer|required',
            'dispute_referenceid' => 'required|string',
        ]);

        try {
            $order = Order::where(['id' => $request['orderID']])->first();
            if (is_null($order)) {
                return back()->with('error', 'Order was not found.');
            }

            $dispute = Dispute::where([['order_id', $request->input('orderID')], ['dispute_referenceid', $request->input('dispute_referenceid')]])->first();
            if (is_null($dispute)) {
                return back()->with('error', 'Order Dispute was not found.');
            }

            $tranx = Transaction::where('transcode', $order->paymentRef)->first();
            if (is_null($tranx)) {
                return back()->with('error', 'Order transaction was not found.');
            }

            //process the refund
            $this->initiateRefund($order, $tranx);

            $dispute->update([
                'dispute_status' => Dispute::PROCESSING,
                'comment' => 'Refund initiated'
            ]);

            return back()->with('success', 'Refund has been initiated successfully.');

            //Send refund email
            //$this->peppUtil->send_order_dispute_resolution_email($order, $dispute);
        } catch (Exception $e) {
            //$this->reportExceptionOnBugsnag($e);
            return back()->with('error', 'Refund failed with error: ' . $e->getMessage());
        }
    }

    public function replaceOrder(Request $request)
    {
        $this->validate($request, [
            'orderID' => 'integer|required',
            'dispute_referenceid' => 'required|string',
        ]);

        try {

            $order = Order::where(['id' => $request['orderID']])->first();

            if (is_null($order)) {
                return back()->with('error', 'Order was not found.');
            }

            $dispute = Dispute::where([['order_id', $request->input('orderID')], ['dispute_referenceid', $request->input('dispute_referenceid')]])->first();

            if (is_null($dispute)) {
                return back()->with('error', 'Order Dispute was not found.');
            }

            //create a new order 
            // $newOrder = $order->replicate();
            // $newOrder->status = $statusArr['Processing'];
            // $newOrder->save();


            //send order for shipment
            $this->sendShipment($order->id);

            $dispute->update([
                'dispute_status' => Dispute::PROCESSING,
                'comment' => 'Order processed for replacement'
            ]);

            // $order->update([
            //     'status' => Order::REPLACED
            // ]);

            //Send email
            //$this->peppUtil->send_order_replaced_email($order, $dispute);
            return back()->with('success', 'Order has been processed for replacement.');
        } catch (Exception $e) {
            //$this->reportExceptionOnBugsnag($e);
            return back()->with('error', 'Order replacement failed with error: ' . $e->getMessage());
        }
    }

    public function cancelOrder(Request $request)
    {
        $this->validate($request, [
            'orderID' => 'required|integer',
            'reason' => 'required|string|max:255',
        ]);

        try {
            $reason = $request->reason;

            $order = Order::find($request['orderID']);
            if (is_null($order)) {
                return back()->with('error', 'Order not found.');
            }

            $tranx = Transaction::where('transcode', $order->paymentRef)->first();
            if (is_null($tranx)) {
                return back()->with('error', 'Order transaction was not found.');
            }

            foreach (ORDER::CANCEL_NOT_ALLOWED as $status => $errorMessage) {
                if ($order->status == $status) {
                    return back()->with('error', $errorMessage);
                }
            }

            $logistics = $order->orderLogistics;
            if (is_null($logistics) || is_null($logistics->fulfilment_request_id)) {
                //order has not been sent to Sendy or its a pickup order, so just update order status, initiate refund and send email
                $this->processCancelation($order, $reason);
                return back()->with('success', 'Order canceled and refund initiated successfully.');
            }

            $trackingInfo = $this->Sendy->trackOrder($logistics->fulfilment_request_id);
            if ($trackingInfo['error'] != 1) {
                if (in_array($trackingInfo['status'], ['IN_TRANSIT_TO_BUYER', 'ORDER_COMPLETED'])) {
                    return back()->with('error', 'Order cannot be canceled as it has been shipped or delivered');
                }

                $cancelOrder = $this->Sendy->cancelOrder($logistics->fulfilment_request_id, $reason);
                if ($cancelOrder['error'] != 1) {
                    $logistics->update(['delivery_status' => $cancelOrder['status']]);
                    $this->processCancelation($order, $reason);
                    return back()->with('success', 'Order canceled and refund initiated successfully.');
                }
                return back()->with('error', 'Order could not be canceled, please try again later');
            }
        } catch (Exception $e) {
            //$this->reportExceptionOnBugsnag($e);
            return back()->with('error', 'Order cancelation failed with error: ' . $e->getMessage());
        }
    }

    public function trackOrder(Request $request)
    {
        $this->validate($request, [
            'orderID' => 'required|integer'
        ]);

        try {
            $order = Order::find($request['orderID']);
            if (is_null($order)) {
                return back()->with('error', 'Order not found.');
            }

            if ($order->delivery_type != Order::TYPE_DELIVERY) {
                return back()->with('error', 'Order is a pickup order.');
            }

            $logistics = $order->orderLogistics;

            if (is_null($logistics) || is_null($logistics->fulfilment_request_id)) {
                return back()->with('error', 'Order logistics not found.');
            }

            $trackingInfo = $this->Sendy->trackOrder($logistics->fulfilment_request_id);

            if ($trackingInfo['error'] != 1) {
                return back()->with('success', 'Order status : ' . $trackingInfo['status']);
            }
            return back()->with('error', 'Order could not be tracked, please try again later');
        } catch (Exception $e) {
            //$this->reportExceptionOnBugsnag($e);
            return back()->with('error', 'Order tracking failed with error: ' . $e->getMessage());
        }
    }

    public function requestDelivery(Request $request)
    {
        $this->validate($request, [
            'orderID' => 'required|integer'
        ]);

        try {
            $order = Order::find($request['orderID']);
            if (is_null($order)) {
                return back()->with('error', 'Order not found.');
            }

            if ($order->delivery_type != Order::TYPE_DELIVERY) {
                return back()->with('error', 'Order is a pickup order.');
            }

            $logistics = $order->orderLogistics;

            if (!is_null($logistics) && !is_null($logistics->fulfilment_request_id)) {
                return back()->with('error', 'Delivery request has already been sent for this order.');
            }

            //request for order delivery to the Buyer by Sendy
            //RequestOrderDelivery::dispatchIf(cc('environment') == 'production', $order);

            return back()->with('success', 'Delivery request sent successfully');
        } catch (Exception $e) {
            //$this->reportExceptionOnBugsnag($e);
            return back()->with('error', 'Delivery request failed with error: ' . $e->getMessage());
        }
    }

    public function requestPickup(Request $request)
    {
        $this->validate($request, [
            'orderID' => 'required|integer'
        ]);

        try {
            $order = Order::find($request['orderID']);
            if (is_null($order)) {
                return back()->with('error', 'Order not found.');
            }

            if ($order->delivery_type != Order::TYPE_DELIVERY) {
                return back()->with('error', 'Order is a pickup order.');
            }

            $logistics = $order->orderLogistics;

            if (!is_null($logistics) && !is_null($logistics->pickup_order_id)) {
                return back()->with('error', 'Pickup request has already been sent for this order.');
            }

            //request for order pickup from the Merchant by Sendy
            //RequestProductPickup::dispatchIf(cc('environment') == 'production', $order);

            return back()->with('success', 'Pickup request sent successfully');
        } catch (Exception $e) {
            //$this->reportExceptionOnBugsnag($e);
            return back()->with('error', 'Pickup request failed with error: ' . $e->getMessage());
        }
    }

    public function markAsDelivered(Request $request)
    {
        $this->validate($request, [
            'orderID' => 'required|integer'
        ]);

        try {
            $order = Order::find($request['orderID']);
            if (is_null($order)) {
                return back()->with('error', 'Order not found.');
            }

            foreach (ORDER::MARK_AS_DELIVERED_NOT_ALLOWED as $status => $errorMessage) {
                if ($order->status == $status) {
                    return back()->with('error', $errorMessage);
                }
            }

            $deliveryStatus = 'Delivered';

            if ($order->delivery_type == Order::TYPE_DELIVERY) {
                $logistics = $order->orderLogistics;
                if (is_null($logistics)) {
                    return back()->with('error', 'Order logistics details not found.');
                }
                $logistics->update(['delivery_status' => $deliveryStatus]);
            }

            $order->update(['status' => Order::DELIVERED]);

            if ($order->disbursement_status != 1) {
                $wallet_id = $order->seller->wallet_id;
                $amount = $order->totalprice;
                $this->createInternalTransaction($order);
                $this->creditWallet($wallet_id, $amount);
                $order->update(['status' => ORDER::COMPLETED, 'disbursement_status' => 1]);
            }

            // $mailer = new Mailer;
            // $mailer->sendOrderStatusChangeEmail($order, $deliveryStatus);

            return back()->with('success', 'Order marked as delivered successfully');
        } catch (Exception $e) {
            //$this->reportExceptionOnBugsnag($e);
            return back()->with('error', 'Mark order as delivered failed with error: ' . $e->getMessage());
        }
    }

    public function downloadSampleSheet()
    {
        $path = public_path('sample/bulk_merchants.xlsx');
        $fileName = 'bulk_merchants.xlsx';
        return Response::download($path, $fileName, ['Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet']);
    }

    public function editProduct($id)
    {
        $product = Product::find($id);
        return view('dashboard.editProduct', compact('product'));
    }

    public function createProduct($merchantID)
    {
        return view('dashboard.createProduct', compact('merchantID'));
    }

    public function addProduct(Request $request)
    {
        $this->validate($request, [
            'merchantID' => 'required|integer',
            'productname' => 'required|string|max:255|regex:/^[a-zA-Z]+[\w\s-]*$/',
            'description' => 'required|string',
            'link' => 'string|nullable',
            'product_image' => 'required|mimes:jpeg,jpg,png,gif,bmp|max:5120',
            'optional_images.*' => 'nullable|mimes:jpeg,jpg,png,gif,bmp|max:5120',
            'price' => 'required|numeric',
            'deliveryperiod' => 'required|integer',
            'height' => 'numeric|nullable',
            'weight' => 'numeric|nullable',
            'length' => 'numeric|nullable',
            'width' => 'numeric|nullable',
            'quantity' => 'integer|required',
            'video_link' => 'string|nullable',
            'SKU' => 'string|nullable',
            'barcode' => 'integer|nullable',
            'product_type' => 'string|required',
            'category_id' => 'integer|nullable',
            'box_size_id' => 'integer|nullable',
            'store_id' => 'integer|nullable',
        ]);
        try {
            $store = Store::where('merchant_id', $request->merchantID)->latest()->first();
            if (!is_null($store)) {
                $store_id = $store->id;
                $merchant = User::find($request->merchantID);
                if (!is_null($merchant)) {
                    $productSlug = generateSlug($request['productname']);
                    $product = Product::create([
                        'merchant_email' => $merchant->email,
                        'productname' => $request['productname'],
                        'store_id' => $store_id,
                        'description' => $request['description'],
                        'product_slug' => $productSlug,
                        'price' => $request['price'],
                        'currency' => $this->currency,
                        'deliveryperiod' => $request['deliveryperiod'],
                        'link' => $request['link'],
                        'image_url' => '',
                        'other_images_url' => '',
                        'merchant_id' => $merchant->id,
                        'height' => $request['height'],
                        'weight' => $request['weight'],
                        'width' => $request['width'],
                        'length' => $request['length'],
                        'quantity' => $request['quantity'],
                        'video_link' => $request['video_link'],
                        'SKU' => $request['SKU'],
                        'barcode' => $request['barcode'],
                        'product_type' => $request['product_type'],
                        'category_id' => $request['category_id'],
                        'box_size_id' => $request['box_size_id'],
                        'product_code' => substr(Str::uuid(), 0, 6),
                    ]);

                    if ($request->hasFile('product_image')) {
                        $imageArray = $this->imageUtil->saveImgArray($request->file('product_image'), '/products/', $product->id, $request->hasFile('optional_images') ? $request->file('optional_images') : []);
                        if (!is_null($imageArray)) {
                            $primaryImg = array_shift($imageArray);
                            $otherImgs = $imageArray;
                            //$other_images_url=serialize($otherImgs);
                            $product->update(['image_url' => $primaryImg]);
                            if (!empty($otherImgs)) {
                                foreach ($otherImgs as $photo) {
                                    $productPhotos[] = ['image_link' => $photo];
                                }
                                $product->photos()->createMany($productPhotos);
                            }
                        }
                    }
                    //add product to sendy inventory
                    //AddProductToSendy::dispatchIf(cc('environment') == 'production', $product);
                    return back()->with('success', 'Product added successfully');
                }
                return back()->with('error', 'Merchant not found');
            }
            return back()->with('error', 'Store not found');
        } catch (Exception $e) {
            return back()->with('error', 'Product adding failed with error: ' . $e->getMessage());
        }
    }

    public function updateProduct(Request $request)
    {
        $this->validate($request, [
            'productID' => 'required|integer',
            'merchantID' => 'required|integer',
            'productname' => 'required|string|max:255|regex:/^[a-zA-Z]+[\w\s-]*$/',
            'description' => 'required|string',
            'link' => 'string|nullable',
            'product_image' => 'nullable|mimes:jpeg,jpg,png,gif,bmp|max:5120',
            'optional_images.*' => 'nullable|mimes:jpeg,jpg,png,gif,bmp|max:5120',
            'price' => 'required|numeric',
            'deliveryperiod' => 'required|integer',
            'height' => 'numeric|nullable',
            'weight' => 'numeric|nullable',
            'length' => 'numeric|nullable',
            'width' => 'numeric|nullable',
            'quantity' => 'integer|required',
            'video_link' => 'string|nullable',
            'SKU' => 'string|nullable',
            'barcode' => 'integer|nullable',
            'product_type' => 'string|nullable',
            'category_id' => 'integer|nullable',
            'box_size_id' => 'integer|nullable',
            'store_id' => 'integer|nullable',
        ]);

        try {
            $merchant = User::find($request->merchantID);
            if (!is_null($merchant)) {
                $productID = $request['productID'];
                $product = Product::where([['id', $productID], ['merchant_id', $merchant->id]])->first();
                if (!is_null($product)) {
                    $product->update([
                        'productname' => $request->filled('productname') ? $request->input('productname') : $product->productname,
                        'store_id' => $request->filled('store_id') ? $request->input('store_id') : $product->store_id,
                        'description' => $request->filled('description') ? $request->input('description') : $product->description,
                        'link' => $request->filled('link') ? $request->input('link') : $product->link,
                        'price' => $request->filled('price') ? $request->input('price') : $product->price,
                        'currency' => $this->currency,
                        'deliveryperiod' => $request->filled('deliveryperiod') ? $request->input('deliveryperiod') : $product->deliveryperiod,
                        'updated_at' => Carbon::now(),
                        'height' => $request->filled('height') ? $request->input('height') : $product->height,
                        'weight' => $request->filled('weight') ? $request->input('weight') : $product->weight,
                        'width' => $request->filled('width') ? $request->input('width') : $product->width,
                        'length' => $request->filled('length') ? $request->input('length') : $product->length,
                        'quantity' => $request->filled('quantity') ? $request->input('quantity') : $product->quantity,
                        'video_link' => $request->filled('video_link') ? $request->input('video_link') : $product->video_link,
                        'SKU' => $request->filled('SKU') ? $request->input('SKU') : $product->SKU,
                        'barcode' => $request->filled('barcode') ? $request->input('barcode') : $product->barcode,
                        'product_type' => $request->filled('product_type') ? $request->input('product_type') : $product->product_type,
                        'category_id' => $request->filled('category_id') ? $request->input('category_id') : $product->category_id,
                        'box_size_id' => $request->filled('box_size_id') ? $request->input('box_size_id') : $product->box_size_id,
                    ]);
                }

                if ($request->hasFile('product_image')) {
                    if (!is_null($product->image_url)) {
                        $this->imageUtil->deleteImage($product->image_url);
                    }
                    $imageArray = $this->imageUtil->saveImgArray($request->file('product_image'), '/products/', $product->id, $request->hasFile('optional_images') ? $request->file('optional_images') : []);

                    if (!is_null($imageArray)) {
                        $primaryImg = array_shift($imageArray);
                        $otherImgs = $imageArray;
                        $product->update(['image_url' => $primaryImg]);
                        if (!empty($otherImgs)) {
                            foreach ($otherImgs as $photo) {
                                $productPhotos[] = ['image_link' => $photo];
                            }
                            $product->photos()->createMany($productPhotos);
                        }
                    }
                }

                return back()->with('success', 'Product updated successfully');
            }
            return back()->with('error', 'Merchant not found');
        } catch (Exception $e) {
            return back()->with('error', 'Product update failed with error: ' . $e->getMessage());
        }
    }

    public function editStore($id)
    {
        $store = Store::find($id);
        return view('dashboard.editStore', compact('store'));
    }

    public function updateStore(Request $request)
    {
        $this->validate(
            $request,
            [
                'merchantID' => 'required|integer',
                'store_name' => 'required|string|max:255|regex:/^[a-zA-Z]+[\w\s-]*$/',
                'store_category' => 'required|integer',
                'website' => 'nullable|string|max:255',
                'store_icon' => 'nullable|mimes:jpeg,jpg,png,gif,bmp|max:5120',
                'store_banner' => 'nullable|mimes:jpeg,jpg,png,gif,bmp|max:5120',
                'store_description' => 'required|string',
                'refund_allowed' => 'integer|nullable',
                'replacement_allowed' => 'integer|nullable',
                'featured' => 'integer|nullable'
            ]
        );

        $merchantID = $request->merchantID;
        try {
            $customer = User::find($merchantID);
            if (!is_null($customer)) {
                $store = Store::updateOrCreate(
                    ['merchant_id' => $customer->id],
                    [
                        'store_name' => $request->input('store_name'),
                        'store_category' => $request->input('store_category'),
                        'website' => $request->input('website'),
                        'store_description' => $request->input('store_description'),
                        'refund_allowed' => $request->filled('refund_allowed') ? $request->input('refund_allowed') : 0,
                        'replacement_allowed' => $request->filled('replacement_allowed') ? $request->input('replacement_allowed') : 0,
                        'featured' => $request->filled('featured') ? $request->input('featured') : 0,
                    ]
                );

                if ($request->hasFile('store_icon')) {
                    $imageArray = $this->imageUtil->saveImgArray($request->file('store_icon'), '/merchants/stores/icons/', $store->id, []);
                    if (!is_null($imageArray)) {
                        $icon = array_shift($imageArray);
                        $store->update(['store_icon' => $icon]);
                    }
                }
                if ($request->hasFile('store_banner')) {
                    $imageArray = $this->imageUtil->saveImgArray($request->file('store_banner'), '/merchants/stores/banners/', $store->id, []);
                    if (!is_null($imageArray)) {
                        $banner = array_shift($imageArray);
                        $store->update(['store_banner' => $banner]);
                    }
                }

                return back()->with('success', 'Store updated successfully');
            }
            return back()->with('error', 'Merchant not found');
        } catch (Exception $e) {
            return back()->with('error', 'Store update failed with error: ' . $e->getMessage());
        }
    }

    public function productRequests()
    {
        $productRequests = ProductRequest::latest()->get();
        return view('dashboard.productRequests', compact('productRequests'));
    }

    public function deleteProduct(Request $request)
    {
        $this->validate($request, [
            'productID' => 'required|integer'
        ]);
        try {
            $productID = $request['productID'];
            $product = Product::find($productID);

            if (!is_null($product)) {
                // Delete associated photos
                $product->photos()->delete();

                foreach ($product->photos as $photo) {
                    //remove image from storage
                    $this->imageUtil->deleteImage($photo->image_link);
                }

                // Delete the product
                $product->delete();

                $this->imageUtil->deleteImage($product->image_url);

                return back()->with('success', 'Product deleted successfully');
            }

            return back()->with('error', 'Product not found');
        } catch (Exception $e) {
            return back()->with('error', 'Product deletion failed with error: ' . $e->getMessage());
        }
    }

    public function createPickupAddress($merchantID)
    {
        $pickupAddress = PickupAddress::where('merchant_id', $merchantID)->latest()->first();
        return view('dashboard.createPickupAddress', compact('merchantID', 'pickupAddress'));
    }

    public function addPickupAddress(Request $request)
    {
        $this->validate($request, [
            'merchantID' => 'required|integer',
            'street' => 'required|string',
            'city' => 'required|string',
            'state' => 'required|string'
        ]);

        try {
            $user = User::find($request->merchantID);
            if (!is_null($user)) {
                $address = $request->input('street') . ', ' . $request->input('city') . ', ' . $request->input('state') . ', ' . $this->country;
                //validate address
                $add_info = Util::validateAddressWithGoogle($user, $address);
                if ($add_info['error'] == 0) {
                    //create or update pickup address
                    $pickup_address = PickupAddress::updateOrCreate(
                        ['merchant_id' => $user->id],
                        [
                            'name' => $user->name,
                            'email' => $user->email,
                            'phone' => $user->phone,
                            'address' => $add_info['addressDetails']['address'],
                            'street' => $add_info['addressDetails']['street'],
                            'formatted_address' => $add_info['addressDetails']['formatted_address'],
                            'country' => $add_info['addressDetails']['country'],
                            'country_code' => $add_info['addressDetails']['country_code'],
                            'city' => $add_info['addressDetails']['city'],
                            'city_code' => $add_info['addressDetails']['city_code'],
                            'state' => $add_info['addressDetails']['state'],
                            'state_code' => $add_info['addressDetails']['state_code'],
                            'longitude' => $add_info['addressDetails']['longitude'],
                            'latitude' => $add_info['addressDetails']['latitude'],
                            'postal_code' => $add_info['addressDetails']['postal_code'],
                            'zip' => $add_info['addressDetails']['postal_code']
                        ]
                    );
                    return back()->with('success', 'Pickup Address added successfully');
                } else {
                    return back()->with('error', 'Pickup Address error: ' . $add_info['responseMessage']);
                }
            }
            return back()->with('error', 'Merchant not found');
        } catch (Exception $e) {
            return back()->with('error', 'Pickup Address failed with error: ' . $e->getMessage());
        }
    }

    public function createStoreAddress($merchantID)
    {
        $storeAddress = StoreAddress::where('merchant_id', $merchantID)->latest()->first();
        return view('dashboard.createStoreAddress', compact('merchantID', 'storeAddress'));
    }

    public function addStoreAddress(Request $request)
    {
        $this->validate($request, [
            'merchantID' => 'required|integer',
            'street' => 'required|string',
            'city' => 'required|string',
            'state' => 'required|string'
        ]);

        try {
            $user = User::find($request->merchantID);
            if (!is_null($user)) {
                $address = $request->input('street') . ', ' . $request->input('city') . ', ' . $request->input('state') . ', ' . $this->country;
                //validate address
                $add_info = Util::validateAddressWithGoogle($user, $address);
                if ($add_info['error'] == 0) {
                    //create or update store address
                    $store_address = StoreAddress::updateOrCreate(
                        ['merchant_id' => $user->id],
                        [
                            'name' => $user->name,
                            'email' => $user->email,
                            'phone' => $user->phone,
                            'address' => $add_info['addressDetails']['address'],
                            'street' => $add_info['addressDetails']['street'],
                            'formatted_address' => $add_info['addressDetails']['formatted_address'],
                            'country' => $add_info['addressDetails']['country'],
                            'country_code' => $add_info['addressDetails']['country_code'],
                            'city' => $add_info['addressDetails']['city'],
                            'city_code' => $add_info['addressDetails']['city_code'],
                            'state' => $add_info['addressDetails']['state'],
                            'state_code' => $add_info['addressDetails']['state_code'],
                            'longitude' => $add_info['addressDetails']['longitude'],
                            'latitude' => $add_info['addressDetails']['latitude'],
                            'postal_code' => $add_info['addressDetails']['postal_code'],
                            'zip' => $add_info['addressDetails']['postal_code']
                        ]
                    );
                    return back()->with('success', 'Store Address added successfully');
                } else {
                    return back()->with('error', 'Store Address error: ' . $add_info['responseMessage']);
                }
            }
            return back()->with('error', 'Merchant not found');
        } catch (Exception $e) {
            return back()->with('error', 'Store Address failed with error: ' . $e->getMessage());
        }
    }

    public function storeVisits(Request $request)
    {
        DB::statement("SET SQL_MODE=''");
        $visits = DB::table('store_visits_analytics');

        if ($request->filled('date')) {
            $date = explode(' to ', $request->date);
            $from = $date[0];
            if (!isset($date[1]) || empty($date[1])) {
                $visits = $visits->whereDate('Date', $from);
            } else {
                $to = $date[1];
                $visits = $visits->whereDate('Date', '>=', $from);
                $visits = $visits->whereDate('Date', '<=', $to);
            }
        }

        $visits = $visits->get();

        switch ($request->input('action')) {
            case 'filter':
                return view('dashboard.visits', ['collection' => $visits]);
                break;

            case 'export':
                $fileName = "store_visits_report_" . date('d-m-y_h-i-sa') . '.xlsx';
                return Excel::download(new StoreVisitsExport($visits), $fileName);
                break;
        }

        return view('dashboard.visits', ['collection' => $visits]);
    }

    public function discounts()
    {
        $discounts = Discount::all();
        return view('dashboard.discounts', compact('discounts'));
    }

    public function createDiscount()
    {
        $merchants = User::selectRaw('users.id, users.name, users.phone, users.email')
            ->whereHas('store', function ($query) {
                $query->where('approved', '=', 1);
            })
            ->has('products')
            ->where('users.account_type', 'Merchant')
            ->orderBy('users.created_at', 'desc')
            ->get();
        return view('dashboard.createDiscount', compact('merchants'));
    }

    public function fetchMerchantProducts($id)
    {
        $products = Product::where('merchant_id', $id)->pluck('productname', 'id');
        // Return values as a JSON response
        return response()->json($products);
    }

    public function addDiscount(Request $request)
    {
        $this->validate($request, [
            'merchantID' => 'required|integer',
            'discount_name' => 'required|string|max:255',
            'discount_type' => 'required|string|in:F,P',
            'discount' => 'required|integer',
            'start_date' => 'required|date|after:yesterday',
            'end_date' => 'required|date|after:start_date',
            'product_ids' => 'required|array',
            'product_ids.*' => 'integer'
        ]);

        try {
            $merchantID = $request->merchantID;
            $merchant = User::find($merchantID);

            if (is_null($merchant)) {
                return back()->with('error', 'Merchant not found');
            }
            $products = $request['product_ids'];

            $discount = Discount::create([
                'merchant_id' => $merchantID,
                'discount_name' => $request['discount_name'],
                'discount_type' => $request['discount_type'],
                'discount' => $request['discount'],
                'start_date' => $request['start_date'],
                'end_date' => $request['end_date']
            ]);

            $discount->products()->attach($products);

            return redirect()->route('discounts')->with('success', 'Discount created successfully');
        } catch (Exception $e) {
            return back()->with('error', 'Discount creation failed with error: ' . $e->getMessage());
        }
    }

    public function editDiscount($id)
    {
        $discount = Discount::find($id);
        return view('dashboard.editDiscount', compact('discount'));
    }

    public function updateDiscount(Request $request)
    {
        $this->validate($request, [
            'merchantID' => 'required|integer',
            'discountID' => 'required|integer',
            'discount_name' => 'required|string|max:255',
            'discount_type' => 'required|string|in:F,P',
            'discount' => 'required|numeric',
            'start_date' => 'required|date|after:yesterday',
            'end_date' => 'required|date|after:start_date',
            'product_ids' => 'required|array',
            'product_ids.*' => 'integer'
        ]);

        try {
            $merchantID = $request->merchantID;
            $discountID = $request->discountID;

            $merchant = User::find($merchantID);

            if (is_null($merchant)) {
                return back()->with('error', 'Merchant not found');
            }

            $products = $request['product_ids'];

            $discount = Discount::where([['id', $discountID], ['merchant_id', $merchantID]])->first();

            if (is_null($discount)) {
                return back()->with('error', 'Discount not found');
            }

            $discount->update([
                'discount_name' => $request['discount_name'],
                'discount_type' => $request['discount_type'],
                'discount' => $request['discount'],
                'start_date' => $request['start_date'],
                'end_date' => $request['end_date']
            ]);

            $discount->products()->sync($products);

            return redirect()->route('discounts')->with('success', 'Discount updated successfully');
        } catch (Exception $e) {
            return back()->with('error', 'Discount update failed with error: ' . $e->getMessage());
        }
    }

    public function removeDiscount(Request $request)
    {
        $this->validate($request, [
            'discountID' => 'required|integer'
        ]);

        try {
            $discountID = $request->discountID;
            $discount = Discount::where('id', $discountID)->first();
            if (!is_null($discount)) {
                $discount->products()->detach();
                $discount->delete();
                return back()->with('success', 'Discount deleted successfully');
            }
            return back()->with('error', 'Discount not found');
        } catch (Exception $e) {
            return back()->with('error', 'Discount deletion failed with error: ' . $e->getMessage());
        }
    }

    public function coupons()
    {
        $coupons = Coupon::all();
        return view('dashboard.coupons', compact('coupons'));
    }

    public function createCoupon()
    {
        return view('dashboard.createCoupon');
    }

    public function addCoupon(Request $request)
    {
        $this->validate($request, [
            'code' => 'required|string|max:255|unique:coupons',
            'discount_type' => 'required|string|in:F,P',
            'discount' => 'required|integer',
            'limit' => 'nullable|integer|min:1',
            'start_date' => 'required|date|after:yesterday',
            'end_date' => 'required|date|after:start_date'
        ]);

        try {
            $coupon = Coupon::create([
                'code' => $request['code'],
                'discount_type' => $request['discount_type'],
                'discount' => $request['discount'],
                'limit' => $request['limit'],
                'start_date' => $request['start_date'],
                'end_date' => $request['end_date']
            ]);

            return redirect()->route('coupons')->with('success', 'Coupon created successfully');
        } catch (Exception $e) {
            return back()->with('error', 'Coupon creation failed with error: ' . $e->getMessage());
        }
    }

    public function editCoupon($id)
    {
        $coupon = Coupon::find($id);
        return view('dashboard.editCoupon', compact('coupon'));
    }

    public function updateCoupon(Request $request)
    {
        $this->validate($request, [
            'couponID' => 'required|integer',
            'code' => 'required|string|max:255',
            'discount_type' => 'required|string|in:F,P',
            'discount' => 'required|numeric',
            'limit' => 'nullable|integer|min:1',
            'start_date' => 'required|date|after:yesterday',
            'end_date' => 'required|date|after:start_date'
        ]);

        try {
            $couponID = $request->couponID;
            if (count(Coupon::where('id', '!=', $couponID)->where('code', $request->code)->get()) > 0) {
                return back()->with('error', 'Coupon code already exists!');
            }

            $coupon = Coupon::find($couponID);

            if (is_null($coupon)) {
                return back()->with('error', 'Coupon not found');
            }

            $coupon->update([
                'code' => $request['code'],
                'discount_type' => $request['discount_type'],
                'discount' => $request['discount'],
                'limit' => $request['limit'],
                'start_date' => $request['start_date'],
                'end_date' => $request['end_date']
            ]);

            return redirect()->route('coupons')->with('success', 'Coupon updated successfully');
        } catch (Exception $e) {
            return back()->with('error', 'Coupon update failed with error: ' . $e->getMessage());
        }
    }

    public function removeCoupon(Request $request)
    {
        $this->validate($request, [
            'couponID' => 'required|integer'
        ]);

        try {
            $couponID = $request->couponID;
            $coupon = Coupon::find($couponID);
            if (!is_null($coupon)) {
                $coupon->delete();
                return back()->with('success', 'Coupon deleted successfully');
            }
            return back()->with('error', 'Coupon not found');
        } catch (Exception $e) {
            return back()->with('error', 'Coupon deletion failed with error: ' . $e->getMessage());
        }
    }
}