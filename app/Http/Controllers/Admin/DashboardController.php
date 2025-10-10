<?php

namespace App\Http\Controllers\Admin;

use Exception;
use Carbon\Carbon;
use App\Models\Bank;
use App\Models\Blog;
use App\Models\User;
use App\Models\Store;
use App\Models\Wallet;
use App\Models\Service;
use App\Models\UserStore;
use App\Models\Withdrawal;
use App\Models\Appointment;
use App\Models\BoothRental;
use App\Models\ServiceType;
use Illuminate\Support\Str;
use App\Models\BlogCategory;
use App\Models\StoreAddress;
use Illuminate\Http\Request;
use App\Models\StoreCategory;
use App\Models\UserBankDetails;
use App\Models\WalletTransaction;
use App\Models\BoothRentalPayment;
use Illuminate\Support\Facades\DB;
use App\Models\InternalTransaction;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use App\Models\BoothRentPaymentHistory;
use App\Notifications\BoothRentalNotification;
use App\Notifications\PaymentReminderNotification;

class DashboardController extends Controller
{
    public function dashboard()
    {

        $upcomingAppointments = Appointment::where('payment_status', 1)
            ->whereDate('date', '>=', now())
            ->orderBy('date', 'asc')
            ->orderBy('time', 'asc')
            ->take(5)
            ->get();


        $topMerchants = User::whereHas('bookings')
            ->withCount('bookings')
            ->orderByDesc('bookings_count')
            ->take(3)
            ->get();


        $appointmentTrends = Appointment::selectRaw('DATE_FORMAT(date, "%b") as month, COUNT(*) as count')
            ->where('payment_status', 1)
            ->whereDate('date', '>=', now()->subMonths(5)->startOfMonth()) // Last 5 months
            ->groupBy('month')
            ->orderByRaw('MIN(date) ASC')
            ->pluck('count', 'month');

        $appointmentTrendslabels = $appointmentTrends->keys();  // Get month names
        $appointmentTrendsdata = $appointmentTrends->values();



        $withdrawalsToday = Withdrawal::whereDate('created_at', Carbon::today());
        $pendingWithdrawalsToday = (clone $withdrawalsToday)->where('withdrawal_status', Withdrawal::PENDING)->sum('amount');
        $successfulWithdrawalsToday = (clone $withdrawalsToday)->where('withdrawal_status', Withdrawal::SUCCESSFUL)->sum('amount');
        $failedWithdrawalsToday = (clone $withdrawalsToday)->where('withdrawal_status', Withdrawal::FAILED)->sum('amount');
        $processingWithdrawalsToday = (clone $withdrawalsToday)->where('withdrawal_status', Withdrawal::PROCESSING)->sum('amount');
        $internalWithdrawalsToday = (clone $withdrawalsToday)->where('is_internal', true)->sum('amount');

        // Withdrawal Breakdown for This Week
        $withdrawalsThisWeek = Withdrawal::whereBetween('created_at', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()]);
        $pendingWithdrawalsThisWeek = (clone $withdrawalsThisWeek)->where('withdrawal_status', Withdrawal::PENDING)->sum('amount');
        $successfulWithdrawalsThisWeek = (clone $withdrawalsThisWeek)->where('withdrawal_status', Withdrawal::SUCCESSFUL)->sum('amount');
        $failedWithdrawalsThisWeek = (clone $withdrawalsThisWeek)->where('withdrawal_status', Withdrawal::FAILED)->sum('amount');
        $processingWithdrawalsThisWeek = (clone $withdrawalsThisWeek)->where('withdrawal_status', Withdrawal::PROCESSING)->sum('amount');
        $internalWithdrawalsThisWeek = (clone $withdrawalsThisWeek)->where('is_internal', true)->sum('amount');
        // Get total revenue (all time)
        $totalRevenueData = [
            'appointments' => DB::table('appointments')->where('payment_status', 1)->whereNotIn('status', ['Cancelled'])->sum('total_amount'),
            'booth_rentals' => DB::table('booth_rent_payment_histories')->sum('amount_paid'),
            'internal_transactions' => DB::table('internal_transactions')->where('payment_status', 'successful')->sum('amount'),
            'transactions' => DB::table('transactions')->where('payment_status', 'successful')->sum('amount'),
            'wallet_transactions' => DB::table('wallet_transactions')->where('status', 'successful')->sum('amount'),
            'billing' => DB::table('billing_histories')->sum('amount'),
            'refunds' => DB::table('refunds')->sum('amount'),
        ];

        // Get revenue for the current year
        $yearlyRevenueData = [
            'appointments' => DB::table('appointments')->where('payment_status', 1)->whereNotIn('status', ['Cancelled'])->whereYear('created_at', now()->year)->sum('total_amount'),
            'booth_rentals' => DB::table('booth_rent_payment_histories')->whereYear('created_at', now()->year)->sum('amount_paid'),
            'internal_transactions' => DB::table('internal_transactions')->where('payment_status', 'successful')->whereYear('created_at', now()->year)->sum('amount'),
            'transactions' => DB::table('transactions')->where('payment_status', 'successful')->whereYear('created_at', now()->year)->sum('amount'),
            'wallet_transactions' => DB::table('wallet_transactions')->where('status', 'successful')->whereYear('created_at', now()->year)->sum('amount'),
            'billing' => DB::table('billing_histories')->whereYear('created_at', now()->year)->sum('amount'),
            'refunds' => DB::table('refunds')->whereYear('created_at', now()->year)->sum('amount'),
        ];

        // Get revenue for the current month
        $monthlyRevenueData = [
            'appointments' => DB::table('appointments')->where('payment_status', 1)->whereMonth('created_at', now()->month)->whereNotIn('status', ['Cancelled'])->sum('total_amount'),
            'booth_rentals' => DB::table('booth_rent_payment_histories')->whereMonth('created_at', now()->month)->sum('amount_paid'),
            'internal_transactions' => DB::table('internal_transactions')->where('payment_status', 'successful')->whereMonth('created_at', now()->month)->sum('amount'),
            'transactions' => DB::table('transactions')->where('payment_status', 'successful')->whereMonth('created_at', now()->month)->sum('amount'),
            'wallet_transactions' => DB::table('wallet_transactions')->where('status', 'successful')->whereMonth('created_at', now()->month)->sum('amount'),
            'billing' => DB::table('billing_histories')->whereMonth('created_at', now()->month)->sum('amount'),
            'refunds' => DB::table('refunds')->whereMonth('created_at', now()->month)->sum('amount'),
        ];

        // Get revenue for last month
        $lastMonthRevenueData = [
            'appointments' => DB::table('appointments')->where('payment_status', 1)->whereNotIn('status', ['Cancelled'])->whereMonth('created_at', now()->subMonth()->month)->sum('total_amount'),
            'booth_rentals' => DB::table('booth_rent_payment_histories')->whereMonth('created_at', now()->subMonth()->month)->sum('amount_paid'),
            'internal_transactions' => DB::table('internal_transactions')->where('payment_status', 'successful')->whereMonth('created_at', now()->subMonth()->month)->sum('amount'),
            'transactions' => DB::table('transactions')->where('payment_status', 'successful')->whereMonth('created_at', now()->subMonth()->month)->sum('amount'),
            'wallet_transactions' => DB::table('wallet_transactions')->where('status', 'successful')->whereMonth('created_at', now()->subMonth()->month)->sum('amount'),
            'billing' => DB::table('billing_histories')->whereMonth('created_at', now()->subMonth()->month)->sum('amount'),
            'refunds' => DB::table('refunds')->whereMonth('created_at', now()->subMonth()->month)->sum('amount'),
        ];

        // Get revenue for the current week
        $weeklyRevenueData = [
            'appointments' => DB::table('appointments')->where('payment_status', 1)->whereNotIn('status', ['Cancelled'])->whereBetween('created_at', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()])->sum('total_amount'),
            'booth_rentals' => DB::table('booth_rent_payment_histories')->whereBetween('created_at', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()])->sum('amount_paid'),
            'internal_transactions' => DB::table('internal_transactions')->where('payment_status', 'successful')->whereBetween('created_at', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()])->sum('amount'),
            'transactions' => DB::table('transactions')->where('payment_status', 'successful')->whereBetween('created_at', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()])->sum('amount'),
            'wallet_transactions' => DB::table('wallet_transactions')->where('status', 'successful')->whereBetween('created_at', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()])->sum('amount'),
            'billing' => DB::table('billing_histories')->whereBetween('created_at', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()])->sum('amount'),
            'refunds' => DB::table('refunds')->whereBetween('created_at', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()])->sum('amount'),
        ];

        // Calculate totals
        $totalRevenue = max(0, array_sum($totalRevenueData) - $totalRevenueData['refunds']);
        $yearlyRevenue = max(0, array_sum($yearlyRevenueData) - $yearlyRevenueData['refunds']);
        $monthlyRevenue = max(0, array_sum($monthlyRevenueData) - $monthlyRevenueData['refunds']);
        $lastMonthRevenue = max(0, array_sum($lastMonthRevenueData) - $lastMonthRevenueData['refunds']);
        $weeklyRevenue = max(0, array_sum($weeklyRevenueData) - $weeklyRevenueData['refunds']);

        // Calculate revenue improvement percentages
        $monthlyChange = $lastMonthRevenue > 0 ? (($monthlyRevenue - $lastMonthRevenue) / $lastMonthRevenue) * 100 : 0;
        $weeklyChange = $weeklyRevenue > 0 ? (($weeklyRevenue - $weeklyRevenue) / $weeklyRevenue) * 100 : 0;

        // Fetch revenue data for the last 6 weeks
        $weeklyRevenueTrends = collect();
        for ($i = 5; $i >= 0; $i--) {
            $startOfWeek = now()->subWeeks($i)->startOfWeek();
            $endOfWeek = now()->subWeeks($i)->endOfWeek();

            $weeklyRevenueTrends->push([
                'week' => $startOfWeek->format('W-Y'),
                'total' => max(
                    0,
                    DB::table('appointments')->where('payment_status', 1)->whereBetween('created_at', [$startOfWeek, $endOfWeek])->sum('total_amount') +
                    DB::table('booth_rent_payment_histories')->whereBetween('created_at', [$startOfWeek, $endOfWeek])->sum('amount_paid') +
                    DB::table('internal_transactions')->where('payment_status', 'successful')->whereBetween('created_at', [$startOfWeek, $endOfWeek])->sum('amount') +
                    DB::table('transactions')->where('payment_status', 'successful')->whereBetween('created_at', [$startOfWeek, $endOfWeek])->sum('amount') +
                    DB::table('wallet_transactions')->where('status', 'successful')->whereBetween('created_at', [$startOfWeek, $endOfWeek])->sum('amount') +
                    DB::table('billing_histories')->whereBetween('created_at', [$startOfWeek, $endOfWeek])->sum('amount') -
                    DB::table('refunds')->whereBetween('created_at', [$startOfWeek, $endOfWeek])->sum('amount')
                )
            ]);
        }

        // Fetch revenue data for the last 6 months
        $monthlyRevenueTrends = collect();
        for ($i = 5; $i >= 0; $i--) {
            $month = now()->subMonths($i)->format('Y-m');

            $monthlyRevenueTrends->push([
                'month' => $month,
                'total' => max(
                    0,
                    DB::table('appointments')->where('payment_status', 1)->whereMonth('created_at', now()->subMonths($i)->month)->sum('total_amount') +
                    DB::table('booth_rent_payment_histories')->whereMonth('created_at', now()->subMonths($i)->month)->sum('amount_paid') +
                    DB::table('internal_transactions')->where('payment_status', 'successful')->whereMonth('created_at', now()->subMonths($i)->month)->sum('amount') +
                    DB::table('transactions')->where('payment_status', 'successful')->whereMonth('created_at', now()->subMonths($i)->month)->sum('amount') +
                    DB::table('wallet_transactions')->where('status', 'successful')->whereMonth('created_at', now()->subMonths($i)->month)->sum('amount') +
                    DB::table('billing_histories')->whereMonth('created_at', now()->subMonths($i)->month)->sum('amount') -
                    DB::table('refunds')->whereMonth('created_at', now()->subMonths($i)->month)->sum('amount')
                )
            ]);
        }

        // Convert collections to arrays for Chart.js
        $weeklyLabels = $weeklyRevenueTrends->pluck('week')->toArray();
        $weeklyValues = $weeklyRevenueTrends->pluck('total')->toArray();
        $monthlyLabels = $monthlyRevenueTrends->pluck('month')->toArray();
        $monthlyValues = $monthlyRevenueTrends->pluck('total')->toArray();

        return view("dashboard.dashboard", compact(
            'pendingWithdrawalsToday',
            'successfulWithdrawalsToday',
            'failedWithdrawalsToday',
            'processingWithdrawalsToday',
            'internalWithdrawalsToday',
            'pendingWithdrawalsThisWeek',
            'successfulWithdrawalsThisWeek',
            'failedWithdrawalsThisWeek',
            'processingWithdrawalsThisWeek',
            'internalWithdrawalsThisWeek',
            'totalRevenueData',
            'totalRevenue',
            'yearlyRevenue',
            'monthlyRevenue',
            'weeklyRevenue',
            'monthlyChange',
            'weeklyChange',
            'weeklyLabels',
            'weeklyValues',
            'monthlyLabels',
            'monthlyValues',
            'upcomingAppointments',
            'topMerchants',
            'appointmentTrendslabels',// Get month names
            'appointmentTrendsdata'

        ));

    }


    public function accountIndex(Request $request)
    {
        $query = User::query();

        // Search by name, email, or phone
        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%")
                    ->orWhere('email', 'like', "%{$request->search}%")
                    ->orWhere('phone', 'like', "%{$request->search}%");
            });
        }

        // Filters
        if ($request->filled('account_type')) {
            $query->where('account_type', $request->account_type);
        }

        if ($request->filled('status')) {
            $query->where('accountstatus', $request->status);
        }

        if ($request->filled('email_verified')) {
            $query->where('email_verified', filter_var($request->email_verified, FILTER_VALIDATE_BOOLEAN));
        }

        if ($request->filled('created_at')) {
            $query->whereDate('created_at', $request->created_at);
        }

        if ($request->filled('has_subscription')) {
            $query->whereHas('subscriptions', function ($q) {
                $q->where('active', 1);
            });
        }

        if ($request->filled('auto_renewal')) {
            $query->whereHas('subscriptions', function ($q) use ($request) {
                $q->where('auto_renew', filter_var($request->auto_renewal, FILTER_VALIDATE_BOOLEAN));
            });
        }

        if ($request->filled('wallet_id')) {
            $query->where('wallet_id', 'like', "%{$request->wallet_id}%");
        }

        // Sorting
        if ($request->filled('sort')) {
            $sortColumn = in_array($request->sort, ['name', 'email']) ? $request->sort : 'created_at';
            $query->orderBy($sortColumn);
        } else {
            $query->orderBy('created_at', 'desc');
        }

        // Debugging SQL Query
        // dd($query->toSql(), $query->getBindings());

        // Paginate the results
        $accounts = $query->get();

        return view('comb_and_clippers_admin.account.index', compact('accounts'));
    }

    public function showAccount($id)
    {
        $user = User::findOrFail($id);

        return view('comb_and_clippers_admin.account.show', compact('user'));
    }

    public function destroyAccount(Request $request)
    {

        $account = User::find($request->id);
        if (!$account) {
            return redirect()->route("accounts")->with("error", "Account not found");
        }
        $account->delete();
        return redirect()->route("accounts")->with("success", "Account deleted successfully");

    }




    public function appointmentPayments(Request $request)
    {

        $appointmentsQuery = Appointment::with(['customer', 'serviceProvider', 'store']);


        if (!$request->hasAny(['customer', 'payment_status', 'status', 'from_date', 'to_date'])) {
            $appointmentsQuery->where('payment_status', 1)
                ->whereIn('status', ['Accepted', 'Completed']);
        }

        // Apply filters if provided
        $appointments = $appointmentsQuery
            ->when($request->customer, function ($query, $customer) {
                return $query->whereHas('customer', function ($q) use ($customer) {
                    $q->where('name', 'like', "%$customer%");
                });
            })
            ->when($request->filled('payment_status'), function ($query) use ($request) {
                return $query->where('payment_status', $request->payment_status);
            })
            ->when($request->status, function ($query, $status) {
                return $query->where('status', $status);
            })
            ->when($request->from_date, function ($query, $from) {
                return $query->whereDate('created_at', '>=', $from);
            })
            ->when($request->to_date, function ($query, $to) {
                return $query->whereDate('created_at', '<=', $to);
            })
            ->get();

        // Revenue: Only PAID + (ACCEPTED or COMPLETED)
        $totalRevenue = Appointment::where('payment_status', 1)
            ->whereIn('status', ['Accepted', 'Completed'])
            ->sum('total_amount');

        // Pending Payments: NOT PAID + (PENDING or ACCEPTED)
        $pendingPayments = Appointment::where('payment_status', 0)
            ->whereIn('status', ['Pending', 'Accepted'])
            ->sum('total_amount');

        // Cancelled but Paid: PAID + CANCELLED
        $cancelledPaid = Appointment::where('payment_status', 1)
            ->where('status', 'Cancelled')
            ->sum('total_amount');

        // Cancelled but Not Paid: NOT PAID + CANCELLED
        $cancelledUnpaid = Appointment::where('payment_status', 0)
            ->where('status', 'Cancelled')
            ->sum('total_amount');

        // Processing Fees: Total of all processing fees
        $processingFees = Appointment::sum('processing_fee');

        return view('comb_and_clippers_admin.payments.appointmentPayment', compact(
            'appointments',
            'totalRevenue',
            'pendingPayments',
            'cancelledPaid',
            'cancelledUnpaid',
            'processingFees'
        ));
    }


    public function boothRentPayments(Request $request)
    {
        $query = BoothRentalPayment::with(['boothRental.user', 'boothRental.store', 'paymentHistories.boothRentPayment']);

        // 🔍 Search by Tenant Name (using `filled()` to check if input is provided)
        $query->when($request->filled('tenant'), function ($q) use ($request) {
            $q->whereHas('boothRental.user', function ($q) use ($request) {
                $q->where('name', 'LIKE', '%' . $request->tenant . '%');
            });
        });

        // 🎯 Filter by Payment Status (using `filled()` to check if input is provided)
        $query->when($request->filled('payment_status'), function ($q) use ($request) {
            $status = $request->payment_status;
            $q->where(function ($q) use ($status) {
                $today = Carbon::today();
                if ($status === 'due') {
                    $q->whereDate('next_payment_date', $today);
                } elseif ($status === 'upcoming') {
                    $q->where(function ($q) use ($today) {
                        $q->whereDate('next_payment_date', '>', $today);
                        $q->whereDate('next_payment_date', '<=', $today->copy()->addDays(5));
                    });
                } elseif ($status === 'overdue') {
                    $q->whereDate('next_payment_date', '<', $today);
                } elseif ($status === 'paid') {
                    $q->where('payment_status', 'paid');
                } else {
                    $q->where('payment_status', $status);
                }
            });
        });

        // 📆 Filter by Date Range (using `filled()` to check if date fields are provided)
        $query->when($request->filled('from_date') && $request->filled('to_date'), function ($q) use ($request) {
            $q->whereBetween('next_payment_date', [$request->from_date, $request->to_date]);
        });

        // 🏪 Filter by Store (using `filled()` to check if store_id is provided)
        $query->when($request->filled('store_id'), function ($q) use ($request) {
            $q->whereHas('boothRental.store', function ($q) use ($request) {
                $q->where('id', $request->store_id);
            });
        });

        // Execute the query and get the results
        $boothPayments = $query->get();

        // 📊 Admin Summary Statistics
        $totalRevenue = BoothRentPaymentHistory::sum('amount_paid');
        $pendingPayments = BoothRentalPayment::where('payment_status', 'upcoming')->sum('amount');
        $overduePayments = BoothRentalPayment::where('payment_status', 'overdue')->sum('amount');
        $processingFees = $query->sum('processing_fee');

        // Get stores for filtering in the view
        $stores = Store::all();

        return view("comb_and_clippers_admin.payments.boothRentalPayment", compact('boothPayments', 'totalRevenue', 'pendingPayments', 'overduePayments', 'processingFees', 'stores'));
    }


    // add verification to  admin actions 
    public function markBoothRentAsPaid($id)
    {
        if (Auth::guard('admin')->check()) {
            $payment = BoothRentalPayment::findOrFail($id);

            $store = $payment->userStore->store;

            $boothRental = $payment->boothRental;

            $next_payment_date = $this->calculateNextPaymentDate($boothRental, Carbon::now());

            $payment->update([
                'payment_status' => 'paid',
                'last_payment_date' => Carbon::now(),
                'next_payment_date' => $next_payment_date,
            ]);

            BoothRentPaymentHistory::create([
                'booth_rent_payment_id' => $payment->id,
                'amount_paid' => $payment->amount,
                'payment_date' => Carbon::now(),
            ]);

            $payment->userStore->user->notify(new BoothRentalNotification($payment));
            $store->owner->notify(new BoothRentalNotification($payment));

            // Log the action with the admin user's ID and name
            Log::info('Admin (ID: ' . Auth::guard('admin')->id() . ', Name: ' . Auth::guard('admin')->user()->name . ') marked booth rent payment (ID: ' . $id . ') as paid.');

            return redirect()->back()->with('success', 'Payment marked as paid.');
        } else {
            return redirect()->back()->with('error', 'You are not authorized to perform this action.');
        }
    }

    public function sendBoothRentReminder($id)
    {
        if (Auth::guard('admin')->check()) {
            $payment = BoothRentalPayment::findOrFail($id);
            $tenant = $payment->boothRental->user;

            if ($tenant) {
                $tenant->notify(new PaymentReminderNotification($payment));
            }

            // Log the action with the admin user's ID and name
            Log::info('Admin (ID: ' . Auth::guard('admin')->id() . ', Name: ' . Auth::guard('admin')->user()->name . ') sent booth rent reminder (ID: ' . $id . ').');

            return redirect()->back()->with('success', 'Payment reminder sent.');
        } else {
            return redirect()->back()->with('error', 'You are not authorized to perform this action.');
        }
    }

    public function withdrawalPayments(Request $request)
    {
        $query = Withdrawal::with(['user', 'wallet']);

        // 🔍 Filter by User Name
        $query->when($request->filled('user_name'), function ($q) use ($request) {
            $q->whereHas('user', function ($q) use ($request) {
                $q->where('name', 'LIKE', '%' . $request->user_name . '%');
            });
        });

        // 🎯 Filter by Withdrawal Status (Pending, Processing, Successful, Failed)
        $query->when($request->filled('withdrawal_status'), function ($q) use ($request) {
            $q->where('withdrawal_status', $request->withdrawal_status);
        });

        // 📆 Filter by Date Range
        $query->when($request->filled('from_date') && $request->filled('to_date'), function ($q) use ($request) {
            $q->whereBetween('created_at', [$request->from_date, $request->to_date]);
        });

        // 🏦 Filter by Bank Name
        $query->when($request->filled('bank_name'), function ($q) use ($request) {
            $q->where('bank_name', 'LIKE', '%' . $request->bank_name . '%');
        });

        // 🆔 Filter by Account Number
        $query->when($request->filled('account_number'), function ($q) use ($request) {
            $q->where('account_number', $request->account_number);
        });

        // Execute the query
        $withdrawals = $query->orderBy('created_at', 'desc')->paginate(20);

        // 📊 Admin Summary Statistics
        $totalWithdrawals = Withdrawal::sum('amount');
        $pendingWithdrawals = Withdrawal::where('withdrawal_status', Withdrawal::PENDING)->sum('amount');
        $successfulWithdrawals = Withdrawal::where('withdrawal_status', Withdrawal::SUCCESSFUL)->sum('amount');
        $failedWithdrawals = Withdrawal::where('withdrawal_status', Withdrawal::FAILED)->sum('amount');

        return view("comb_and_clippers_admin.payments.withdrawalPayment", compact(
            'withdrawals',
            'totalWithdrawals',
            'pendingWithdrawals',
            'successfulWithdrawals',
            'failedWithdrawals'
        ));
    }


    public function stores(Request $request)
    {
        $query = Store::with([
            'owner',
            'category',
            'subCategory',
            'products',
            'storeAddress',
            'services',
            'bookings',
            'workdoneImages',
            'boothRent',
            'pickupAddress',
            'serviceTypes',
            'renters.user', // Fetch renter details
            'storeVisits'
        ]);

        // 🏪 Store Name
        $query->when($request->filled('store_name'), function ($q) use ($request) {
            $q->where('store_name', 'LIKE', '%' . $request->store_name . '%');
        });

        // 👤 Merchant Name
        $query->when($request->filled('merchant_name'), function ($q) use ($request) {
            $q->whereHas('owner', function ($q) use ($request) {
                $q->where('name', 'LIKE', '%' . $request->merchant_name . '%');
            });
        });

        // ✅ Approval Status
        $query->when($request->filled('approval_status'), function ($q) use ($request) {
            $q->where('approved', $request->approval_status);
        });

        // 🌟 Featured Stores
        $query->when($request->filled('featured'), function ($q) use ($request) {
            $q->where('featured', $request->featured);
        });

        // 🏷️ Category & Sub-Category
        $query->when($request->filled('category'), function ($q) use ($request) {
            $q->where('store_category', $request->category);
        });

        $query->when($request->filled('sub_category'), function ($q) use ($request) {
            $q->where('store_sub_category', $request->sub_category);
        });

        // 🌍 City, State, Country (from Store Address)
        $query->when($request->filled('city'), function ($q) use ($request) {
            $q->whereHas('storeAddress', function ($q) use ($request) {
                $q->where('city', 'LIKE', '%' . $request->city . '%');
            });
        });

        $query->when($request->filled('state'), function ($q) use ($request) {
            $q->whereHas('storeAddress', function ($q) use ($request) {
                $q->where('state', 'LIKE', '%' . $request->state . '%');
            });
        });

        $query->when($request->filled('country'), function ($q) use ($request) {
            $q->whereHas('storeAddress', function ($q) use ($request) {
                $q->where('country', 'LIKE', '%' . $request->country . '%');
            });
        });

        // 🛠️ Service Type
        $query->when($request->filled('service_type'), function ($q) use ($request) {
            $q->whereHas('serviceTypes.serviceType', function ($q) use ($request) {
                $q->where('name', 'LIKE', '%' . $request->service_type . '%');
            });
        });

        // 👀 Minimum Visits
        $query->when($request->filled('min_visits'), function ($q) use ($request) {
            $q->whereHas('storeVisits', function ($q) use ($request) {
                $q->havingRaw('COUNT(store_visits.id) >= ?', [$request->min_visits]);
            });
        });

        // 💰 Minimum Service Price
        $query->when($request->filled('min_price'), function ($q) use ($request) {
            $q->whereHas('services', function ($q) use ($request) {
                $q->where('price', '>=', $request->min_price);
            });
        });

        // 📅 Days Available
        $query->when($request->filled('days_available'), function ($q) use ($request) {
            $q->whereJsonContains('days_available', $request->days_available);
        });

        // 💳 Payment Preferences
        $query->when($request->filled('payment_method'), function ($q) use ($request) {
            $q->whereJsonContains('payment_preferences', $request->payment_method);
        });

        // 🔄 Refund Allowed
        $query->when($request->filled('refund_allowed'), function ($q) use ($request) {
            $q->where('refund_allowed', $request->refund_allowed);
        });

        // 🏠 Minimum Booth Renters
        $query->when($request->filled('min_renters'), function ($q) use ($request) {
            $q->withCount('renters')->having('renters_count', '>=', $request->min_renters);
        });


        // 🔍 Filter by Renter Name
        $query->when($request->filled('renter_name'), function ($q) use ($request) {
            $q->whereHas('renters.user', function ($q) use ($request) {
                $q->where('name', 'LIKE', '%' . $request->renter_name . '%');
            });
        });

        // ✉️ Filter by Renter Email
        $query->when($request->filled('renter_email'), function ($q) use ($request) {
            $q->whereHas('renters.user', function ($q) use ($request) {
                $q->where('email', 'LIKE', '%' . $request->renter_email . '%');
            });
        });

        // 🏢 Filter Stores with Only Booth Renters
        $query->when($request->filled('only_booth_renters'), function ($q) use ($request) {
            $q->whereHas('renters');
        });

        // 🔢 Fetch Paginated Results
        $stores = $query->orderBy('created_at', 'desc')->get();


        // 📊 Summary Statistics
        $totalStores = Store::count();
        $approvedStores = Store::where('approved', 1)->count();
        $pendingStores = Store::where('approved', 0)->count();
        $featuredStores = Store::where('featured', 1)->count();
        $storesWithVisits = Store::whereHas('storeVisits')->count();
        $storesWithServices = Store::whereHas('services')->count();
        $mostPopularCity = StoreAddress::select('city')
            ->groupBy('city')
            ->orderByRaw('COUNT(*) DESC')
            ->limit(1)
            ->value('city');

        // 📌 New Renters Statistics
        $totalRenters = UserStore::count();
        $storeWithMostRenters = Store::withCount('renters')
            ->orderByDesc('renters_count')
            ->first();

        return view("comb_and_clippers_admin.stores.stores", compact(
            'stores',
            'totalStores',
            'approvedStores',
            'pendingStores',
            'featuredStores',
            'storesWithVisits',
            'storesWithServices',
            'mostPopularCity',
            'totalRenters',
            'storeWithMostRenters'
        ));
    }


    public function showStore($id)
    {
        $store = Store::with([
            'owner',
            'category',
            'subCategory',
            'storeAddress',
            'services',
            'services.photos',
            'workdoneImages',
            'serviceTypes',
            'renters',
            'renters.user',
            'bookings',
            'boothRent',
            'storeVisits'
        ])->findOrFail($id);

        return view('comb_and_clippers_admin.stores.store-show', compact('store'));
    }

    public function deleteStore($id)
    {
        if (Auth::guard('admin')->check()) {
            try {
                $store = Store::findOrFail($id);



                $store->delete();

                // Log the action with the admin user's ID and name
                Log::info('Admin (ID: ' . Auth::guard('admin')->id() . ', Name: ' . Auth::guard('admin')->user()->name . ') deleted store (ID: ' . $id . ').');

                return redirect()->route('stores')->with('success', 'Store deleted successfully.');
            } catch (Exception $e) {
                // Log the error
                Log::error('Error deleting store (ID: ' . $id . '): ' . $e->getMessage());

                return redirect()->route('stores')->with('error', 'An error occurred while deleting the store.');
            }

        } else {
            return redirect()->route('stores')->with('error', 'You are not authorized to perform this action.');
        }
    }

    public function serviceTypes(Request $request)
    {
        // 🏷️ Base query with necessary eager loading
        $query = ServiceType::query()->with([
            'storeServiceTypes.store',
            'boothRentals.store',
            'interests'
        ]);

        // 🏷️ Service Type Name Filter
        if ($request->filled('service_type_name')) {
            $query->where('name', 'LIKE', '%' . $request->service_type_name . '%');
        }

        // 🔢 Fetch Paginated Results
        $serviceTypes = $query->orderBy('name')->paginate(10);

        // 📊 Summary Statistics
        $totalServiceTypes = ServiceType::count();

        // 📊 Most Booked Service Type (by counting appointments within storeServiceTypes)
        $mostBookedServiceType = ServiceType::withCount([
            'storeServiceTypes as total_appointments' => function ($query) {
                $query->selectRaw('COALESCE(SUM((SELECT COUNT(*) FROM appointments WHERE appointments.store_service_type_id = store_service_types.id)), 0)');
            }
        ])
            ->orderByDesc('total_appointments')
            ->first();

        // 📊 Most Rendered Service Type (most storeServiceTypes linked)
        $mostRenderedServiceType = ServiceType::withCount('storeServiceTypes')
            ->orderByDesc('store_service_types_count')
            ->first();

        // 📊 Service Types with Renters (fixing nested counts)
        $serviceTypesWithRenters = ServiceType::withCount('storeServiceTypes')
            ->with([
                'storeServiceTypes.store' => function ($query) {
                    $query->withCount('renters'); // Count renters for each store
                }
            ])
            ->orderByDesc('store_service_types_count')
            ->get();

        // 📊 Service Type Popularity by Interests
        $serviceTypesWithMostInterests = ServiceType::withCount('interests')
            ->orderByDesc('interests_count')
            ->get();

        return view("comb_and_clippers_admin.stores.serviceType", compact(
            'serviceTypes',
            'totalServiceTypes',
            'mostBookedServiceType',
            'mostRenderedServiceType',
            'serviceTypesWithRenters',
            'serviceTypesWithMostInterests'
        ));

    }

    public function createServiceType(Request $request)
    {
        if (Auth::guard('admin')->check()) {
            try {

                $name = $request->serviceType_name;

                if (!$name) {
                    return redirect()->back()->with('error', 'the service type name is required');
                }

                $serviceType = ServiceType::create([
                    'name' => $name
                ]);


                if (!$serviceType) {
                    return redirect()->back()->with('error', 'An error occurred while creating the serviceType.');
                }

                Log::info('Admin (ID: ' . Auth::guard('admin')->id() . ', Name: ' . Auth::guard('admin')->user()->name . ') created service type (ID: ' . $serviceType->id . ').');

                return redirect()->back()->with('success', 'ServiceType created successfully.');
            } catch (Exception $e) {
                // Log the error
                Log::error('Error creating serviceType  ' . $e->getMessage());

                return redirect()->back()->with('error', 'An error occurred while creating the serviceType.');
            }

        } else {
            return redirect()->back()->with('error', 'You are not authorized to perform this action.');
        }
    }

    public function updateServiceType(Request $request, $id)
    {
        if (Auth::guard('admin')->check()) {
            try {




                $name = $request->serviceType_name;

                if (!$name) {
                    return redirect()->back()->with('error', 'the service type name is required');
                }

                $serviceType = ServiceType::findOrFail($id);
                $serviceType->name = $request->serviceType_name;
                $serviceType->save();



                Log::info('Admin (ID: ' . Auth::guard('admin')->id() . ', Name: ' . Auth::guard('admin')->user()->name . ') updated service type (ID: ' . $serviceType->id . ').');

                return redirect()->back()->with('success', 'Service Type updated successfully.');
            } catch (Exception $e) {
                // Log the error
                Log::error('Error updating serviceType  ' . $e->getMessage());

                return redirect()->back()->with('error', 'An error occurred while updating the serviceType.');
            }

        } else {
            return redirect()->back()->with('error', 'You are not authorized to perform this action.');
        }
    }

    public function destroyServiceType($id)
    {
        if (Auth::guard('admin')->check()) {
            try {
                $serviceType = ServiceType::findOrFail($id);

                $serviceType->delete();


                Log::info('Admin (ID: ' . Auth::guard('admin')->id() . ', Name: ' . Auth::guard('admin')->user()->name . ') deleted service type (ID: ' . $id . ').');

                return redirect()->back()->with('success', 'ServiceType deleted successfully.');
            } catch (Exception $e) {
                // Log the error
                Log::error('Error deleting serviceType (ID: ' . $id . '): ' . $e->getMessage());

                return redirect()->back()->with('error', 'An error occurred while deleting the serviceType.');
            }

        } else {
            return redirect()->back()->with('error', 'You are not authorized to perform this action.');
        }
    }

    public function appointmentIndex(Request $request)
    {
        // 🏷️ Base query with relationships
        $query = Appointment::with(['customer', 'serviceProvider', 'store']);

        // 🏷️ Filtering by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // 📅 Filtering by date
        if ($request->filled('date')) {
            $query->whereDate('date', $request->date);
        }

        // 🔍 Searching by customer or merchant name
        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('customer', function ($q) use ($search) {
                $q->where('name', 'LIKE', "%$search%");
            })->orWhereHas('serviceProvider', function ($q) use ($search) {
                $q->where('name', 'LIKE', "%$search%");
            });
        }

        // 📜 Fetch paginated results
        $appointments = $query->orderBy('date', 'desc')->get();

        return view('comb_and_clippers_admin.appointment.index', compact('appointments'));
    }

    public function showAppointment(Request $request)
    {
        $appointment = Appointment::find($request->id);
        if (!$appointment) {
            return redirect()->route("appointments")->with("error", "Appointment not found");
        }
        return view('comb_and_clippers_admin.appointment.show', compact('appointment'));
    }

    public function destroyAppointment(Request $request)
    {

        $appointment = Appointment::find($request->id);
        if (!$appointment) {
            return redirect()->route("appointments")->with("error", "Appointment not found");
        }
        $appointment->delete();
        return redirect()->route("appointments")->with("success", "Appointment deleted successfully");

    }


    public function blogCategory()
    {
        $data = BlogCategory::active()->latest()->get();
        return view('dashboard.blog_category', ['data' => $data]);
    }


    public function blogCategoryCreate()
    {
        return view('dashboard.createBlogCategory');
    }

    public function blogCategoryAdd(Request $request)
    {

        $request->validate([
            'name' => 'required|unique:blog_categories,name|max:20|string'
        ]);
        $blogCategory = new BlogCategory;
        $blogCategory->name = $request->name;
        $blogCategory->slug = $request->name;
        $blogCategory->active = true;
        $blogCategory->save();
        return redirect()->route('blog.category.all')->with('success', 'Category created successfully');
    }

    public function blogCategoryShow($id)
    {

        $category = BlogCategory::find($id);

        return view('dashboard.editBlogCategory', ['category' => $category, 'id' => $id]);
    }

    public function blogCategoryEdit(Request $request, $id)
    {

        $request->validate([
            'name' => ['required', 'max:20', 'string', 'unique:blog_categories,name,except,name']
        ]);
        $blogCategory = BlogCategory::find($id);
        if (!$blogCategory) {

        }
        $blogCategory->name = $request->name;
        $blogCategory->slug = $request->name;
        $blogCategory->update();
        return redirect()->route('blog.category.all')->with('success', 'Category updated successfully');
    }

    public function blogCategoryDelete($id)
    {

        $countBlogs = (BlogCategory::whereHas('blog', function ($query) use ($id) {
            $query->where('blog_category_id', $id);
        })->count());
        if ($countBlogs > 0) {
            return redirect()->route('blog.category.all')->with('error', 'Category has blogs cannnot be Deleted');
        }
        BlogCategory::destroy($id);
        return redirect()->route('blog.category.all')->with('success', 'Category removed successfully');
    }


    public function blog()
    {
        $data = Blog::latest('updated_at')->get();
        return view('dashboard.blog', ['data' => $data]);
    }


    public function blogCreate()
    {
        return view('dashboard.createBlog');
    }

    public function blogAdd(Request $request)
    {

        $request->validate([
            'title' => 'required|string|max:255|unique:blogs',
            'excerpt' => 'required|string',
            'full_description' => 'required|string',
            'blog_category_id' => 'required',
            'status' => 'string|nullable',
            'cover_image' => 'required|mimes:jpeg,jpg,png,gif,bmp|max:5120',
            'images' => 'required',
            'images.*' => 'mimes:jpeg,jpg,png,gif,bmp|max:5120',
        ]);

        try {
            $blog = Blog::create([
                'title' => $request->title,
                'slug' => Str::slug($request->title),
                'excerpt' => $request->excerpt,
                'full_description' => $request->full_description,
                'status' => $request->status,
                'blog_category_id' => $request->blog_category_id,
            ]);

            if ($request->hasFile('cover_image')) {
                $fileName = $this->imageUtil->saveImg($request->file('cover_image'), '/blogs/', $blog->id, []);

                $coverLink = asset('/storage') . '/blogs/' . $blog->id . '/' . $fileName;
                $blog->update(['cover_image' => $coverLink]);
                // dd($coverLink);
            }

            if ($request->hasFile('images')) {

                $imageArray = $this->imageUtil->saveOptionalImgArray($request->file('images'), '/blogs/', $blog->id);

                if (!is_null($imageArray)) {
                    $blog->update(['images' => $imageArray]);
                }
            }

            return redirect()->route('blog.all')->with('success', 'Blog added successfully');
        } catch (Exception $e) {
            return back()->with('error', 'Blog adding failed with error: ' . $e->getMessage());
        }
    }

    public function blogShow($id)
    {

        $blog = Blog::find($id);

        return view('dashboard.editBlog', ['blog' => $blog, 'id' => $id]);
    }

    public function blogEdit(Request $request, $id)
    {

        $request->validate([
            'title' => 'required|string|max:255|unique:blogs,title,' . $id,
            'excerpt' => 'required|string',
            'full_description' => 'required|string',
            'blog_category_id' => 'required',
            'status' => 'string|nullable',
            'cover_image' => 'nullable|mimes:jpeg,jpg,png,gif,bmp|max:5120',
            'images' => 'nullable',
            'images.*' => 'nullable|mimes:jpeg,jpg,png,gif,bmp|max:5120',
        ]);

        try {
            $blog = Blog::findOrFail($id)->update([
                'title' => $request->title,
                'slug' => Str::slug($request->title),
                'excerpt' => $request->excerpt,
                'full_description' => $request->full_description,
                'status' => $request->status,
                'blog_category_id' => $request->blog_category_id,
            ]);

            if ($request->hasFile('cover_image')) {
                $fileName = $this->imageUtil->saveImg($request->file('cover_image'), '/blogs/', $blog->id, []);

                $coverLink = asset('/storage') . '/blogs/' . $blog->id . '/' . $fileName;
                $blog->update(['cover_image' => $coverLink]);
                // dd($coverLink);
            }

            if ($request->hasFile('images')) {

                $imageArray = $this->imageUtil->saveOptionalImgArray($request->file('images'), '/blogs/', $blog->id);

                if (!is_null($imageArray)) {
                    $blog->update(['images' => $imageArray]);
                }
            }

            return redirect()->route('blog.all')->with('success', 'Blog added successfully');
        } catch (Exception $e) {
            return back()->with('error', 'Blog adding failed with error: ' . $e->getMessage());
        }
    }

    public function blogDelete($id)
    {
        Blog::destroy($id);
        return redirect()->route('blog.all')->with('success', 'Blog removed successfully');
    }

    // REPORTS
    public function chooseReport(Request $request)
    {
        if (Auth::guard('admin')->check()) {

            $startDate = $request->input('start_date');
            $endDate = $request->input('end_date');

            // Base queries without date filters
            $usersQuery = User::query();
            $customersQuery = User::where('account_type', 'Client')->whereNotNull('account_type');
            $merchantsQuery = User::where('account_type', '!=', 'Client')->whereNotNull('account_type');
            $storesQuery = Store::query();
            $featuredStoresQuery = Store::where('featured', true);
            $servicesQuery = Service::query();
            $appointmentsQuery = Appointment::query();
            $completedAppointmentsQuery = Appointment::where('status', 'completed');
            $revenueQuery = Appointment::where('payment_status', 1);
            $boothRentalsQuery = BoothRental::query();
            $activeBoothRentalsQuery = BoothRentalPayment::where('payment_status', 'paid');
            $boothRentalRevenueQuery = BoothRentalPayment::query();
            $walletQuery = Wallet::query();
            $withdrawalsQuery = Withdrawal::query();
            $pendingWithdrawalsQuery = Withdrawal::where('withdrawal_status', Withdrawal::PENDING);

            // Store original queries for total counts
            $totalUsersQuery = clone $usersQuery;
            $totalCustomersQuery = clone $customersQuery;
            $totalMerchantsQuery = clone $merchantsQuery;
            $totalStoresQuery = clone $storesQuery;
            $totalFeaturedStoresQuery = clone $featuredStoresQuery;
            $totalServicesQuery = clone $servicesQuery;
            $totalAppointmentsQuery = clone $appointmentsQuery;
            $totalCompletedAppointmentsQuery = clone $completedAppointmentsQuery;
            $totalRevenueQuery = clone $revenueQuery;
            $totalBoothRentalsQuery = clone $boothRentalsQuery;
            $totalActiveBoothRentalsQuery = clone $activeBoothRentalsQuery;
            $totalBoothRentalRevenueQuery = clone $boothRentalRevenueQuery;
            $totalWalletQuery = clone $walletQuery;
            $totalWithdrawalsQuery = clone $withdrawalsQuery;
            $totalPendingWithdrawalsQuery = clone $pendingWithdrawalsQuery;

            // Apply date range filter if specified
            if ($startDate && $endDate) {
                $dateRange = [Carbon::parse($startDate)->startOfDay(), Carbon::parse($endDate)->endOfDay()];

                // Apply date filters to all queries for filtered results
                $usersQuery->whereBetween('created_at', $dateRange);
                $customersQuery->whereBetween('created_at', $dateRange);
                $merchantsQuery->whereBetween('created_at', $dateRange);
                $storesQuery->whereBetween('created_at', $dateRange);
                $featuredStoresQuery->whereBetween('created_at', $dateRange);
                $servicesQuery->whereBetween('created_at', $dateRange);
                $appointmentsQuery->whereBetween('created_at', $dateRange);
                $completedAppointmentsQuery->whereBetween('created_at', $dateRange);
                $revenueQuery->whereBetween('created_at', $dateRange);
                $boothRentalsQuery->whereBetween('created_at', $dateRange);
                $activeBoothRentalsQuery->whereBetween('created_at', $dateRange);
                $boothRentalRevenueQuery->whereBetween('created_at', $dateRange);
                $walletQuery->whereBetween('created_at', $dateRange);
                $withdrawalsQuery->whereBetween('created_at', $dateRange);
                $pendingWithdrawalsQuery->whereBetween('created_at', $dateRange);
            }

            // Get total counts (without date filter)
            $totalUsers = $totalUsersQuery->count();
            $totalCustomers = $totalCustomersQuery->count();
            $totalMerchants = $totalMerchantsQuery->count();
            $totalStores = $totalStoresQuery->count();
            $featuredStores = $totalFeaturedStoresQuery->count();
            $totalServices = $totalServicesQuery->count();
            $totalAppointments = $totalAppointmentsQuery->count();
            $totalCompletedAppointments = $totalCompletedAppointmentsQuery->count();
            $totalRevenue = $totalRevenueQuery->sum('total_amount');
            $totalBoothRentals = $totalBoothRentalsQuery->count();
            $activeBoothRentals = $totalActiveBoothRentalsQuery->count();
            $boothRentalRevenue = $totalBoothRentalRevenueQuery->sum('amount');
            $totalWalletBalance = $totalWalletQuery->sum('amount');
            $totalWithdrawals = $totalWithdrawalsQuery->count();
            $pendingWithdrawals = $totalPendingWithdrawalsQuery->count();

            // Get filtered counts (with date filter if specified)
            $newUsersInRange = $usersQuery->count();
            $newCustomersInRange = $customersQuery->count();
            $newMerchantsInRange = $merchantsQuery->count();
            $newStoresInRange = $storesQuery->count();
            $newFeaturedStoresInRange = $featuredStoresQuery->count();
            $newServicesInRange = $servicesQuery->count();
            $totalAppointmentsInRange = $appointmentsQuery->count();
            $completedAppointmentsInRange = $completedAppointmentsQuery->count();
            $totalRevenueInRange = $revenueQuery->sum('total_amount');
            $boothRentalsInRange = $boothRentalsQuery->count();
            $activeBoothRentalsInRange = $activeBoothRentalsQuery->count();
            $boothRentalRevenueInRange = $boothRentalRevenueQuery->sum('amount');
            $walletBalanceInRange = $walletQuery->sum('amount');
            $withdrawalsInRange = $withdrawalsQuery->count();
            $pendingWithdrawalsInRange = $pendingWithdrawalsQuery->count();

            return view("comb_and_clippers_admin.reports.report-index", compact(
                'totalUsers',
                'totalCustomers',
                'totalMerchants',
                'totalStores',
                'featuredStores',
                'totalServices',
                'totalAppointments',
                'totalCompletedAppointments',
                'totalRevenue',
                'totalBoothRentals',
                'activeBoothRentals',
                'boothRentalRevenue',
                'totalWalletBalance',
                'totalWithdrawals',
                'pendingWithdrawals',
                'startDate',
                'endDate',
                'newUsersInRange',
                'newCustomersInRange',
                'newMerchantsInRange',
                'newStoresInRange',
                'newFeaturedStoresInRange',
                'newServicesInRange',
                'totalAppointmentsInRange',
                'completedAppointmentsInRange',
                'totalRevenueInRange',
                'boothRentalsInRange',
                'activeBoothRentalsInRange',
                'boothRentalRevenueInRange',
                'walletBalanceInRange',
                'withdrawalsInRange',
                'pendingWithdrawalsInRange'
            ));

        } else {
            return redirect()->back()->with('error', 'You are not authorized to perform this action.');
        }
    }


    public function generalReport(Request $request)
    {
        // Initialize date range variables
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        // Base queries without date filters
        $usersQuery = User::query();
        $customersQuery = User::where('account_type', 'Client')->whereNotNull('account_type');
        $merchantsQuery = User::where('account_type', '!=', 'Client')->whereNotNull('account_type');
        $storesQuery = Store::query();
        $featuredStoresQuery = Store::where('featured', true);
        $servicesQuery = Service::query();
        $appointmentsQuery = Appointment::query();
        $completedAppointmentsQuery = Appointment::where('status', 'completed');
        $revenueQuery = Appointment::where('payment_status', 1);
        $boothRentalsQuery = BoothRental::query();
        $activeBoothRentalsQuery = BoothRentalPayment::where('payment_status', 'paid');
        $boothRentalRevenueQuery = BoothRentalPayment::query();
        $walletQuery = Wallet::query();
        $withdrawalsQuery = Withdrawal::query();
        $pendingWithdrawalsQuery = Withdrawal::where('withdrawal_status', Withdrawal::PENDING);

        // Store original queries for total counts
        $totalUsersQuery = clone $usersQuery;
        $totalCustomersQuery = clone $customersQuery;
        $totalMerchantsQuery = clone $merchantsQuery;
        $totalStoresQuery = clone $storesQuery;
        $totalFeaturedStoresQuery = clone $featuredStoresQuery;
        $totalServicesQuery = clone $servicesQuery;
        $totalAppointmentsQuery = clone $appointmentsQuery;
        $totalCompletedAppointmentsQuery = clone $completedAppointmentsQuery;
        $totalRevenueQuery = clone $revenueQuery;
        $totalBoothRentalsQuery = clone $boothRentalsQuery;
        $totalActiveBoothRentalsQuery = clone $activeBoothRentalsQuery;
        $totalBoothRentalRevenueQuery = clone $boothRentalRevenueQuery;
        $totalWalletQuery = clone $walletQuery;
        $totalWithdrawalsQuery = clone $withdrawalsQuery;
        $totalPendingWithdrawalsQuery = clone $pendingWithdrawalsQuery;

        // Apply date range filter if specified
        if ($startDate && $endDate) {
            $dateRange = [Carbon::parse($startDate)->startOfDay(), Carbon::parse($endDate)->endOfDay()];

            // Apply date filters to all queries for filtered results
            $usersQuery->whereBetween('created_at', $dateRange);
            $customersQuery->whereBetween('created_at', $dateRange);
            $merchantsQuery->whereBetween('created_at', $dateRange);
            $storesQuery->whereBetween('created_at', $dateRange);
            $featuredStoresQuery->whereBetween('created_at', $dateRange);
            $servicesQuery->whereBetween('created_at', $dateRange);
            $appointmentsQuery->whereBetween('created_at', $dateRange);
            $completedAppointmentsQuery->whereBetween('created_at', $dateRange);
            $revenueQuery->whereBetween('created_at', $dateRange);
            $boothRentalsQuery->whereBetween('created_at', $dateRange);
            $activeBoothRentalsQuery->whereBetween('created_at', $dateRange);
            $boothRentalRevenueQuery->whereBetween('created_at', $dateRange);
            $walletQuery->whereBetween('created_at', $dateRange);
            $withdrawalsQuery->whereBetween('created_at', $dateRange);
            $pendingWithdrawalsQuery->whereBetween('created_at', $dateRange);
        }

        // Get total counts (without date filter)
        $totalUsers = $totalUsersQuery->count();
        $totalCustomers = $totalCustomersQuery->count();
        $totalMerchants = $totalMerchantsQuery->count();
        $totalStores = $totalStoresQuery->count();
        $featuredStores = $totalFeaturedStoresQuery->count();
        $totalServices = $totalServicesQuery->count();
        $totalAppointments = $totalAppointmentsQuery->count();
        $totalCompletedAppointments = $totalCompletedAppointmentsQuery->count();
        $totalRevenue = $totalRevenueQuery->sum('total_amount');
        $totalBoothRentals = $totalBoothRentalsQuery->count();
        $activeBoothRentals = $totalActiveBoothRentalsQuery->count();
        $boothRentalRevenue = $totalBoothRentalRevenueQuery->sum('amount');
        $totalWalletBalance = $totalWalletQuery->sum('amount');
        $totalWithdrawals = $totalWithdrawalsQuery->count();
        $pendingWithdrawals = $totalPendingWithdrawalsQuery->count();

        // Get filtered counts (with date filter if specified)
        $newUsersInRange = $usersQuery->count();
        $newCustomersInRange = $customersQuery->count();
        $newMerchantsInRange = $merchantsQuery->count();
        $newStoresInRange = $storesQuery->count();
        $newFeaturedStoresInRange = $featuredStoresQuery->count();
        $newServicesInRange = $servicesQuery->count();
        $totalAppointmentsInRange = $appointmentsQuery->count();
        $completedAppointmentsInRange = $completedAppointmentsQuery->count();
        $totalRevenueInRange = $revenueQuery->sum('total_amount');
        $boothRentalsInRange = $boothRentalsQuery->count();
        $activeBoothRentalsInRange = $activeBoothRentalsQuery->count();
        $boothRentalRevenueInRange = $boothRentalRevenueQuery->sum('amount');
        $walletBalanceInRange = $walletQuery->sum('amount');
        $withdrawalsInRange = $withdrawalsQuery->count();
        $pendingWithdrawalsInRange = $pendingWithdrawalsQuery->count();

        return view('comb_and_clippers_admin.reports.general-report', compact(
            'totalUsers',
            'totalCustomers',
            'totalMerchants',
            'totalStores',
            'featuredStores',
            'totalServices',
            'totalAppointments',
            'totalCompletedAppointments',
            'totalRevenue',
            'totalBoothRentals',
            'activeBoothRentals',
            'boothRentalRevenue',
            'totalWalletBalance',
            'totalWithdrawals',
            'pendingWithdrawals',
            'startDate',
            'endDate',
            'newUsersInRange',
            'newCustomersInRange',
            'newMerchantsInRange',
            'newStoresInRange',
            'newFeaturedStoresInRange',
            'newServicesInRange',
            'totalAppointmentsInRange',
            'completedAppointmentsInRange',
            'totalRevenueInRange',
            'boothRentalsInRange',
            'activeBoothRentalsInRange',
            'boothRentalRevenueInRange',
            'walletBalanceInRange',
            'withdrawalsInRange',
            'pendingWithdrawalsInRange'
        ));
    }


    public function appointmentReports(Request $request)
    {
        // 🏷️ Base query with necessary relationships
        $query = Appointment::with(['store', 'customer', 'serviceProvider']);

        // 🏷️ Filtering by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // 📅 Filtering by date range
        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('date', [$request->start_date, $request->end_date]);
        } elseif ($request->filled('date')) {
            // If only a single date is provided, filter by that specific date
            $query->whereDate('date', $request->date);
        }

        // 🔍 Searching by customer or merchant name
        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('customer', function ($q) use ($search) {
                $q->where('name', 'LIKE', "%$search%");
            })->orWhereHas('serviceProvider', function ($q) use ($search) {
                $q->where('name', 'LIKE', "%$search%");
            });
        }

        // 🧑‍💼 Filtering by merchant
        if ($request->filled('merchant_id')) {
            $query->where('merchant_id', $request->merchant_id);
        }

        // 🏢 Filtering by store
        if ($request->filled('store_id')) {
            $query->where('store_id', $request->store_id);
        }

        // 💰 Filtering by payment status
        if ($request->filled('payment_status')) {
            $query->where('payment_status', $request->payment_status);
        }

        // ⚙️ Fetch filter options
        $merchants = User::where('account_type', 'merchant')->select('id', 'name')->get();
        $stores = Store::select('id', 'store_name')->get();
        $statuses = ['pending', 'confirmed', 'completed', 'cancelled'];
        $paymentStatuses = ['0' => 'Unpaid', '1' => 'Paid'];

        // 📊 Calculate summary data (using the query before pagination)
        $totalAppointments = $query->count();
        $totalAmount = $query->sum('total_amount');
        $totalTips = $query->sum('tip');
        $totalProcessingFees = $query->sum('processing_fee');

        // 📜 Fetch paginated results
        $appointments = $query->orderBy('date', 'desc')->get();

        return view('comb_and_clippers_admin.reports.appointment-report', compact(
            'appointments',
            'merchants',
            'stores',
            'statuses',
            'paymentStatuses',
            'totalAppointments',
            'totalAmount',
            'totalTips',
            'totalProcessingFees'
        ));
    }

    public function boothRentalReports(Request $request)
    {
        $query = BoothRental::with(['store', 'user', 'serviceType']);

        // Apply date filters
        if ($request->filled('from_date')) {
            $query->whereDate('created_at', '>=', $request->from_date);
        }

        if ($request->filled('to_date')) {
            $query->whereDate('created_at', '<=', $request->to_date);
        }

        // Apply other filters
        if ($request->filled('store_id')) {
            $query->where('store_id', $request->store_id);
        }

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->filled('payment_timeline')) {
            $query->where('payment_timeline', $request->payment_timeline);
        }

        if ($request->filled('service_type_id')) {
            $query->where('service_type_id', $request->service_type_id);
        }

        // Get all stores for filter dropdown
        $stores = Store::select('id', 'store_name')->get();
        $users = User::where('account_type', 'merchant')->select('id', 'name')->get();
        $serviceTypes = ServiceType::select('id', 'name')->get();
        $paymentTimelines = ['daily', 'weekly', 'monthly', 'yearly']; // Adjust based on your actual options

        // Get results with pagination
        $boothRentals = $query->get();

        // Calculate totals
        $totalRentals = $boothRentals->count();
        $totalAmount = $query->sum('amount');

        return view('comb_and_clippers_admin.reports.boothRental-report', compact(
            'boothRentals',
            'stores',
            'users',
            'serviceTypes',
            'paymentTimelines',
            'totalRentals',
            'totalAmount'
        ));
    }

    public function boothRentalPaymentReports(Request $request)
    {
        $query = BoothRentalPayment::with(['boothRental.user', 'boothRental.store', 'paymentHistories.boothRentPayment']);

        // 📅 Filter by Date Range
        $query->when($request->filled('from_date') && $request->filled('to_date'), function ($q) use ($request) {
            $q->whereBetween('next_payment_date', [$request->from_date, $request->to_date]);
        });

        // 🔍 Search by Tenant Name
        $query->when($request->filled('tenant'), function ($q) use ($request) {
            $q->whereHas('boothRental.user', function ($q) use ($request) {
                $q->where('name', 'LIKE', '%' . $request->tenant . '%');
            });
        });

        // 🏪 Filter by Store
        $query->when($request->filled('store_id'), function ($q) use ($request) {
            $q->whereHas('boothRental.store', function ($q) use ($request) {
                $q->where('id', $request->store_id);
            });
        });

        // 🎯 Filter by Payment Status
        $query->when($request->filled('payment_status'), function ($q) use ($request) {
            $status = $request->payment_status;
            $q->where(function ($q) use ($status) {
                $today = Carbon::today();
                if ($status === 'due') {
                    $q->whereDate('next_payment_date', $today);
                } elseif ($status === 'upcoming') {
                    $q->where(function ($q) use ($today) {
                        $q->whereDate('next_payment_date', '>', $today);
                        $q->whereDate('next_payment_date', '<=', $today->copy()->addDays(5));
                    });
                } elseif ($status === 'overdue') {
                    $q->whereDate('next_payment_date', '<', $today);
                } elseif ($status === 'paid') {
                    $q->where('payment_status', 'paid');
                } else {
                    $q->where('payment_status', $status);
                }
            });
        });

        // Execute the query and get the results
        $boothPayments = $query->get();

        // 📊 Admin Summary Statistics
        $totalRevenue = BoothRentPaymentHistory::sum('amount_paid');
        $pendingPayments = BoothRentalPayment::where('payment_status', 'upcoming')->sum('amount');
        $overduePayments = BoothRentalPayment::where('payment_status', 'overdue')->sum('amount');
        $processingFees = $query->sum('processing_fee');

        // Get stores for filtering in the view
        $stores = Store::all();

        return view("comb_and_clippers_admin.reports.boothRentalPayment-report", compact('boothPayments', 'totalRevenue', 'pendingPayments', 'overduePayments', 'processingFees', 'stores'));
    }

    public function internalTransactionReports(Request $request)
    {
        $query = InternalTransaction::with(['merchant', 'customer']);

        // Apply date filters
        if ($request->filled('from_date')) {
            $query->whereDate('created_at', '>=', $request->from_date);
        }

        if ($request->filled('to_date')) {
            $query->whereDate('created_at', '<=', $request->to_date);
        }

        // Apply other filters
        if ($request->filled('merchant_id')) {
            $query->where('merchant_id', $request->merchant_id);
        }

        if ($request->filled('customer_id')) {
            $query->where('customer_id', $request->customer_id);
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('payment_status')) {
            $query->where('payment_status', $request->payment_status);
        }

        // Get all merchants and customers for filter dropdowns
        $merchants = User::where('account_type', 'merchant')->select('id', 'name')->get();
        $customers = User::where('account_type', 'customer')->select('id', 'name')->get();

        // Get results with pagination
        $internalTransactions = $query->latest()->paginate(15);

        // Calculate totals
        $totalTransactions = $internalTransactions->total();
        $totalAmount = $query->sum('amount');
        $averageAmount = $totalTransactions > 0 ? $totalAmount / $totalTransactions : 0;

        return view('comb_and_clippers_admin.reports.internalTransaction-report', compact(
            'internalTransactions',
            'merchants',
            'customers',
            'totalTransactions',
            'totalAmount',
            'averageAmount'
        ));
    }

    public function bankDetailsReports(Request $request)
    {
        $query = UserBankDetails::with('user');

        // Apply date filters
        if ($request->filled('from_date')) {
            $query->whereDate('created_at', '>=', $request->from_date);
        }

        if ($request->filled('to_date')) {
            $query->whereDate('created_at', '<=', $request->to_date);
        }

        // Apply account type filter
        if ($request->filled('account_type')) {
            $query->whereHas('user', function ($q) use ($request) {
                $q->where('account_type', $request->account_type);
            });
        }

        // Apply bank name filter
        if ($request->filled('bank_name')) {
            $query->where('bank_name', $request->bank_name);
        }

        // Apply email verification filter
        if ($request->filled('verified_email')) {
            $query->whereHas('user', function ($q) use ($request) {
                $q->where('email_verified', $request->verified_email);
            });
        }

        // Get all banks for the filter dropdown
        $banks = Bank::all();

        // Get results with pagination
        $userBankDetails = $query->latest()->get();

        return view('comb_and_clippers_admin.reports.bankDetails-report', compact('userBankDetails', 'banks'));
    }

    public function walletTransactionReports(Request $request)
    {
        $query = WalletTransaction::with('wallet.user');

        // Apply date filters
        if ($request->filled('from_date')) {
            $query->whereDate('created_at', '>=', $request->from_date);
        }

        if ($request->filled('to_date')) {
            $query->whereDate('created_at', '<=', $request->to_date);
        }

        // Apply user filter
        if ($request->filled('user_id')) {
            $query->whereHas('wallet', function ($q) use ($request) {
                $q->where('user_id', $request->user_id);
            });
        }

        // Apply transaction type filter
        if ($request->filled('transaction_type')) {
            $query->where('transaction_type', $request->transaction_type);
        }

        // Apply wallet number filter
        if ($request->filled('wallet_number')) {
            $query->whereHas('wallet', function ($q) use ($request) {
                $q->where('wallet_number', $request->wallet_number);
            });
        }

        // Apply transaction reference filter
        if ($request->filled('transaction_reference')) {
            $query->where('transaction_reference', $request->transaction_reference);
        }

        // Get all users for the filter dropdown
        $users = User::all();

        // Get results with pagination
        $walletTransactions = $query->latest()->get();

        return view('comb_and_clippers_admin.reports.walletTransaction-report', compact('walletTransactions', 'users'));
    }

    public function walletsReports(Request $request)
    {
        $query = Wallet::with('user', 'bank');

        // Apply date filters
        if ($request->filled('from_date')) {
            $query->whereDate('created_at', '>=', $request->from_date);
        }

        if ($request->filled('to_date')) {
            $query->whereDate('created_at', '<=', $request->to_date);
        }

        // Apply user filter
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        // Apply currency filter
        if ($request->filled('currency')) {
            $query->where('currency', $request->currency);
        }

        // Apply wallet number filter
        if ($request->filled('wallet_number')) {
            $query->where('wallet_number', $request->wallet_number);
        }

        // Apply bank code filter
        if ($request->filled('bank_code')) {
            $query->where('bank_code', $request->bank_code);
        }

        // Get all users for the filter dropdown
        $users = User::all();

        // Get all banks for the filter dropdown
        $banks = Bank::all();

        // Get results with pagination
        $wallets = $query->latest()->get();

        return view('comb_and_clippers_admin.reports.wallet-report', compact('wallets', 'users', 'banks'));
    }

    public function withdrawalReports(Request $request)
    {
        $query = Withdrawal::with('user', 'wallet');

        // Apply date filters
        if ($request->filled('from_date')) {
            $query->whereDate('created_at', '>=', $request->from_date);
        }

        if ($request->filled('to_date')) {
            $query->whereDate('created_at', '<=', $request->to_date);
        }

        // Apply user filter
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        // Apply withdrawal status filter
        if ($request->filled('withdrawal_status')) {
            $query->where('withdrawal_status', $request->withdrawal_status);
        }

        // Apply bank name filter
        if ($request->filled('bank_name')) {
            $query->where('bank_name', 'LIKE', '%' . $request->bank_name . '%');
        }

        // Apply account number filter
        if ($request->filled('account_number')) {
            $query->where('account_number', $request->account_number);
        }

        // Apply transfer reference filter
        if ($request->filled('transferRef')) {
            $query->where('transferRef', $request->transferRef);
        }

        // Get all users for the filter dropdown
        $users = User::all();

        // Get results with pagination
        $withdrawals = $query->latest()->get();

        return view('comb_and_clippers_admin.reports.withdrawals-report', compact('withdrawals', 'users'));
    }


    public function usersReports(Request $request)
    {
        $query = User::query();

        // Apply date filters
        if ($request->filled('from_date')) {
            $query->whereDate('created_at', '>=', $request->from_date);
        }

        if ($request->filled('to_date')) {
            $query->whereDate('created_at', '<=', $request->to_date);
        }

        // Filter by account status
        if ($request->filled('accountstatus')) {
            $query->where('accountstatus', $request->input('accountstatus'));
        }

        // Filter by account type
        if ($request->filled('account_type')) {
            $query->where('account_type', $request->input('account_type'));
        }

        // Filter by bank name
        if ($request->filled('bank')) {
            $query->where('bank', 'LIKE', '%' . $request->input('bank') . '%');
        }

        // Filter by specialization
        if ($request->filled('specialization')) {
            $query->where('specialization', 'LIKE', '%' . $request->input('specialization') . '%');
        }

        // Filter by users who have a store
        if ($request->filled('has_store')) {
            if ($request->input('has_store') == '1') {
                $query->whereHas('store');
            } elseif ($request->input('has_store') == '0') {
                $query->whereDoesntHave('store');
            }
        }

        $users = $query->latest()->get();

        return view('comb_and_clippers_admin.reports.user-report', compact('users'));
    }

    public function storesReports(Request $request)
    {
        $query = Store::query()->with('owner', 'category');

        // Apply date filters
        if ($request->filled('from_date')) {
            $query->whereDate('created_at', '>=', $request->from_date);
        }

        if ($request->filled('to_date')) {
            $query->whereDate('created_at', '<=', $request->to_date);
        }

        // Filter by store name
        if ($request->filled('store_name')) {
            $query->where('store_name', 'LIKE', '%' . $request->input('store_name') . '%');
        }

        // Filter by owner name
        if ($request->filled('owner_name')) {
            $query->whereHas('owner', function ($q) use ($request) {
                $q->where('name', 'LIKE', '%' . $request->input('owner_name') . '%');
            });
        }

        // Filter by store category
        if ($request->filled('store_category')) {
            $query->where('store_category', $request->input('store_category'));
        }

        // Filter by approval status
        if ($request->filled('approved')) {
            $query->where('approved', $request->input('approved'));
        }

        // Filter by featured status
        if ($request->filled('featured')) {
            $query->where('featured', $request->input('featured'));
        }

        // Eager load counts for efficiency
        $stores = $query->withCount(['renters', 'boothRent', 'bookings'])->latest()->get();
        $storeCategories = StoreCategory::all();

        return view('comb_and_clippers_admin.reports.stores-report', compact('stores', 'storeCategories'));
    }
}