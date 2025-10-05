<?php

namespace App\Http\Controllers\Admin;

use Carbon\Carbon;
use App\Models\User;
use App\Models\Store;
use App\Models\Wallet;
use App\Models\Service;
use App\Models\Withdrawal;
use App\Models\Appointment;
use App\Models\BoothRental;
use Illuminate\Http\Request;
use App\Models\UserBankDetails;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\WalletTransaction;
use App\Models\BoothRentalPayment;
use App\Models\InternalTransaction;
use App\Http\Controllers\Controller;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\BoothRentPaymentHistory;
use App\Exports\UserReport\UserReportExcel;
use App\Exports\StoreReport\StoreReportExcel;
use App\Exports\WalletReport\WalletReportExcel;
use App\Exports\GeneralReport\GeneralReportExcel;
use App\Exports\WithdrawalReport\WithdrawalReportExcel;
use App\Exports\AppointmentReport\AppointmentReportExcel;
use App\Exports\BankDetailsReport\BankDetailsReportExcel;
use App\Exports\BoothRentalReport\BoothRentalReportExcel;
use App\Exports\WalletTransactionReport\WalletTransactionReportExcel;
use App\Exports\BoothRentalPaymentReport\BoothRentalPaymentReportExcel;
use App\Exports\InternalTransactionReport\InternalTransactionReportExcel;

class ReportController extends Controller
{

    protected function getGeneralReportData(Request $request): array
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

        return [
            'totalUsers' => $totalUsers,
            'totalCustomers' => $totalCustomers,
            'totalMerchants' => $totalMerchants,
            'totalStores' => $totalStores,
            'featuredStores' => $featuredStores,
            'totalServices' => $totalServices,
            'totalAppointments' => $totalAppointments,
            'totalCompletedAppointments' => $totalCompletedAppointments,
            'totalRevenue' => $totalRevenue,
            'totalBoothRentals' => $totalBoothRentals,
            'activeBoothRentals' => $activeBoothRentals,
            'boothRentalRevenue' => $boothRentalRevenue,
            'totalWalletBalance' => $totalWalletBalance,
            'totalWithdrawals' => $totalWithdrawals,
            'pendingWithdrawals' => $pendingWithdrawals,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'newUsersInRange' => $newUsersInRange,
            'newCustomersInRange' => $newCustomersInRange,
            'newMerchantsInRange' => $newMerchantsInRange,
            'newStoresInRange' => $newStoresInRange,
            'newFeaturedStoresInRange' => $newFeaturedStoresInRange,
            'newServicesInRange' => $newServicesInRange,
            'totalAppointmentsInRange' => $totalAppointmentsInRange,
            'completedAppointmentsInRange' => $completedAppointmentsInRange,
            'totalRevenueInRange' => $totalRevenueInRange,
            'boothRentalsInRange' => $boothRentalsInRange,
            'activeBoothRentalsInRange' => $activeBoothRentalsInRange,
            'boothRentalRevenueInRange' => $boothRentalRevenueInRange,
            'walletBalanceInRange' => $walletBalanceInRange,
            'withdrawalsInRange' => $withdrawalsInRange,
            'pendingWithdrawalsInRange' => $pendingWithdrawalsInRange,
        ];
    }

    public function downloadGeneralReportExcel(Request $request)
    {
        // Reuse the logic from the generalReport method to get the data
        $reportData = $this->getGeneralReportData($request);

        return Excel::download(new GeneralReportExcel($reportData), 'general_report_' . now()->format('YmdHis') . '.xlsx');
    }

    public function downloadGeneralReportPdf(Request $request)
    {
        // Reuse the logic from the generalReport method to get the data
        $reportData = $this->getGeneralReportData($request);

        $pdf = Pdf::loadView('comb_and_clippers_admin.exports.general.general-report-pdf', [
            'reportData' => $reportData
        ]);
        return $pdf->download('general_report_' . now()->format('YmdHis') . '.pdf');
    }

    public function downloadAppointmentReportExcel(Request $request)
    {

        // Reuse the logic from the appointmentReports method to get the data
        $query = $this->getAppointmentReportQuery($request);
        $appointments = $query->get();
        $totalAppointments = $query->count();
        $totalAmount = $query->sum('total_amount');
        $totalTips = $query->sum('tip');
        $totalProcessingFees = $query->sum('processing_fee');


        return Excel::download(new AppointmentReportExcel($appointments, $totalAppointments, $totalAmount, $totalTips, $totalProcessingFees), 'appointment_report_' . now()->format('YmdHis') . '.xlsx');
    }

    public function downloadAppointmentReportPdf(Request $request)
    {
        // Reuse the logic from the appointmentReports method to get the data
        $query = $this->getAppointmentReportQuery($request);
        $appointments = $query->get();
        $totalAppointments = $query->count();
        $totalAmount = $query->sum('total_amount');
        $totalTips = $query->sum('tip');
        $totalProcessingFees = $query->sum('processing_fee');

        $pdf = Pdf::loadView('comb_and_clippers_admin.exports.appointment.appointment-report-pdf', [
            'appointments' => $appointments,
            'totalAppointments' => $totalAppointments,
            'totalAmount' => $totalAmount,
            'totalTips' => $totalTips,
            'totalProcessingFees' => $totalProcessingFees,
        ]);
        return $pdf->download('appointment_report_' . now()->format('YmdHis') . '.pdf');
    }

    protected function getAppointmentReportQuery(Request $request)
    {
        $query = Appointment::with(['store', 'customer', 'serviceProvider']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('date', [$request->start_date, $request->end_date]);
        } elseif ($request->filled('date')) {
            $query->whereDate('date', $request->date);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('customer', function ($q) use ($search) {
                $q->where('name', 'LIKE', "%$search%");
            })->orWhereHas('serviceProvider', function ($q) use ($search) {
                $q->where('name', 'LIKE', "%$search%");
            });
        }

        if ($request->filled('merchant_id')) {
            $query->where('merchant_id', $request->merchant_id);
        }

        if ($request->filled('store_id')) {
            $query->where('store_id', $request->store_id);
        }

        if ($request->filled('payment_status')) {
            $query->where('payment_status', $request->payment_status);
        }

        return $query->orderBy('date', 'desc');
    }

    public function downloadBoothRentalReportExcel(Request $request)
    {
        $query = $this->getBoothRentalReportQuery($request);
        $boothRentals = $query->get();
        $totalRentals = $boothRentals->count();
        $totalAmount = $query->sum('amount');

        return Excel::download(new BoothRentalReportExcel($boothRentals, $totalRentals, $totalAmount), 'booth_rental_report_' . now()->format('YmdHis') . '.xlsx');
    }

    public function downloadBoothRentalReportPdf(Request $request)
    {
        $query = $this->getBoothRentalReportQuery($request);
        $boothRentals = $query->get();
        $totalRentals = $boothRentals->count();
        $totalAmount = $query->sum('amount');

        $pdf = Pdf::loadView('comb_and_clippers_admin.exports.boothRental.booth-rental-report-pdf', [
            'boothRentals' => $boothRentals,
            'totalRentals' => $totalRentals,
            'totalAmount' => $totalAmount,
        ]);
        return $pdf->download('booth_rental_report_' . now()->format('YmdHis') . '.pdf');
    }

    protected function getBoothRentalReportQuery(Request $request)
    {
        $query = BoothRental::with(['store', 'user', 'serviceType']);

        if ($request->filled('from_date')) {
            $query->whereDate('created_at', '>=', $request->from_date);
        }

        if ($request->filled('to_date')) {
            $query->whereDate('created_at', '<=', $request->to_date);
        }

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

        return $query;
    }


    public function downloadBoothRentalPaymentReportExcel(Request $request)
    {
        $query = $this->getBoothRentalPaymentReportQuery($request);
        $boothPayments = $query->get();
        $totalRevenue = BoothRentPaymentHistory::whereIn('booth_rent_payment_id', $boothPayments->pluck('id'))->sum('amount_paid');
        $pendingPayments = $boothPayments->where('payment_status', 'upcoming')->sum('amount');
        $overduePayments = $boothPayments->where('payment_status', 'overdue')->sum('amount');
        $processingFees = $boothPayments->sum('processing_fee');

        return Excel::download(new BoothRentalPaymentReportExcel($boothPayments, $totalRevenue, $pendingPayments, $overduePayments, $processingFees), 'booth_rental_payments_report_' . now()->format('YmdHis') . '.xlsx');
    }

    public function downloadBoothRentalPaymentReportPdf(Request $request)
    {
        $query = $this->getBoothRentalPaymentReportQuery($request);
        $boothPayments = $query->get();
        $totalRevenue = BoothRentPaymentHistory::whereIn('booth_rent_payment_id', $boothPayments->pluck('id'))->sum('amount_paid');
        $pendingPayments = $boothPayments->where('payment_status', 'upcoming')->sum('amount');
        $overduePayments = $boothPayments->where('payment_status', 'overdue')->sum('amount');
        $processingFees = $boothPayments->sum('processing_fee');

        $pdf = Pdf::loadView('comb_and_clippers_admin.exports.boothRentalPayment.boothRentalPayment-report-pdf', [
            'boothPayments' => $boothPayments,
            'totalRevenue' => $totalRevenue,
            'pendingPayments' => $pendingPayments,
            'overduePayments' => $overduePayments,
            'processingFees' => $processingFees,
        ]);
        return $pdf->download('booth_rental_payments_report_' . now()->format('YmdHis') . '.pdf');
    }

    protected function getBoothRentalPaymentReportQuery(Request $request)
    {
        $query = BoothRentalPayment::with(['boothRental.user', 'boothRental.store', 'paymentHistories.boothRentPayment']);

        $query->when($request->filled('from_date') && $request->filled('to_date'), function ($q) use ($request) {
            $q->whereBetween('next_payment_date', [$request->from_date, $request->to_date]);
        });

        $query->when($request->filled('tenant'), function ($q) use ($request) {
            $q->whereHas('boothRental.user', function ($q) use ($request) {
                $q->where('name', 'LIKE', '%' . $request->tenant . '%');
            });
        });

        $query->when($request->filled('store_id'), function ($q) use ($request) {
            $q->whereHas('boothRental.store', function ($q) use ($request) {
                $q->where('id', $request->store_id);
            });
        });

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

        return $query;
    }


    public function downloadInternalTransactionReportExcel(Request $request)
    {
        $query = $this->getInternalTransactionReportQuery($request);
        $internalTransactions = $query->get();
        $totalTransactions = $internalTransactions->count();
        $totalAmount = $internalTransactions->sum('amount');
        $averageAmount = $totalTransactions > 0 ? $totalAmount / $totalTransactions : 0;

        return Excel::download(new InternalTransactionReportExcel($internalTransactions, $totalTransactions, $totalAmount, $averageAmount), 'internal_transactions_report_' . now()->format('YmdHis') . '.xlsx');
    }

    public function downloadInternalTransactionReportPdf(Request $request)
    {
        $query = $this->getInternalTransactionReportQuery($request);
        $internalTransactions = $query->get();
        $totalTransactions = $internalTransactions->count();
        $totalAmount = $internalTransactions->sum('amount');
        $averageAmount = $totalTransactions > 0 ? $totalAmount / $totalTransactions : 0;

        $pdf = Pdf::loadView('comb_and_clippers_admin.exports.internalTransaction.internalTransaction-report-pdf', [
            'internalTransactions' => $internalTransactions,
            'totalTransactions' => $totalTransactions,
            'totalAmount' => $totalAmount,
            'averageAmount' => $averageAmount,
        ]);
        return $pdf->download('internal_transactions_report_' . now()->format('YmdHis') . '.pdf');
    }

    protected function getInternalTransactionReportQuery(Request $request)
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

        return $query->latest();
    }

    public function downloadBankDetailsReportExcel(Request $request)
    {
        $query = $this->getBankDetailsReportQuery($request);
        $userBankDetails = $query->get();

        return Excel::download(new BankDetailsReportExcel($userBankDetails), 'user_bank_details_report_' . now()->format('YmdHis') . '.xlsx');
    }

    public function downloadBankDetailsReportPdf(Request $request)
    {
        $query = $this->getBankDetailsReportQuery($request);
        $userBankDetails = $query->get();

        $pdf = Pdf::loadView('comb_and_clippers_admin.exports.bankDetails.bank-details-report-pdf', [
            'userBankDetails' => $userBankDetails,
        ]);
        return $pdf->download('user_bank_details_report_' . now()->format('YmdHis') . '.pdf');
    }

    protected function getBankDetailsReportQuery(Request $request)
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

        return $query->latest();
    }


    public function downloadWalletTransactionReportExcel(Request $request)
    {
        $query = $this->getWalletTransactionReportQuery($request);
        $walletTransactions = $query->get();

        return Excel::download(new WalletTransactionReportExcel($walletTransactions), 'wallet_transactions_report_' . now()->format('YmdHis') . '.xlsx');
    }

    public function downloadWalletTransactionReportPdf(Request $request)
    {
        $query = $this->getWalletTransactionReportQuery($request);
        $walletTransactions = $query->get();

        $pdf = Pdf::loadView('comb_and_clippers_admin.exports.walletTransaction.wallet-transaction-report-pdf', [
            'walletTransactions' => $walletTransactions,
        ]);
        return $pdf->download('wallet_transactions_report_' . now()->format('YmdHis') . '.pdf');
    }

    protected function getWalletTransactionReportQuery(Request $request)
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

        return $query->latest();
    }

    public function downloadWalletsReportExcel(Request $request)
    {
        $query = $this->getWalletsReportQuery($request);
        $wallets = $query->get();

        return Excel::download(new WalletReportExcel($wallets), 'wallets_report_' . now()->format('YmdHis') . '.xlsx');
    }

    public function downloadWalletsReportPdf(Request $request)
    {
        $query = $this->getWalletsReportQuery($request);
        $wallets = $query->get();

        $pdf = Pdf::loadView('comb_and_clippers_admin.exports.wallet.wallet-report-pdf', [
            'wallets' => $wallets,
        ]);
        return $pdf->download('wallets_report_' . now()->format('YmdHis') . '.pdf');
    }

    protected function getWalletsReportQuery(Request $request)
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

        return $query->latest();
    }

    public function downloadWithdrawalReportExcel(Request $request)
    {
        $query = $this->getWithdrawalReportQuery($request);
        $withdrawals = $query->get();

        return Excel::download(new WithdrawalReportExcel($withdrawals), 'withdrawals_report_' . now()->format('YmdHis') . '.xlsx');
    }

    public function downloadWithdrawalReportPdf(Request $request)
    {
        $query = $this->getWithdrawalReportQuery($request);
        $withdrawals = $query->get();

        $pdf = Pdf::loadView('comb_and_clippers_admin.exports.withdrawal.withdrawal-report-pdf', [
            'withdrawals' => $withdrawals,
        ])->setPaper('a4', 'landscape'); // Suggest landscape for wider table

        return $pdf->download('withdrawals_report_' . now()->format('YmdHis') . '.pdf');
    }

    protected function getWithdrawalReportQuery(Request $request)
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

        return $query->latest();
    }

    public function downloadUsersReportExcel(Request $request)
    {
        $query = $this->getUsersReportQuery($request);
        $users = $query->get();

        return Excel::download(new UserReportExcel($users), 'users_report_' . now()->format('YmdHis') . '.xlsx');
    }

    public function downloadUsersReportPdf(Request $request)
    {
        $query = $this->getUsersReportQuery($request);
        $users = $query->get();

        $pdf = Pdf::loadView('comb_and_clippers_admin.exports.user.user-report-pdf', [
            'users' => $users,
        ])->setPaper('a4', 'landscape'); // Suggest landscape for wider table

        return $pdf->download('users_report_' . now()->format('YmdHis') . '.pdf');
    }

    protected function getUsersReportQuery(Request $request)
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

        return $query->latest();
    }


    public function downloadStoresReportExcel(Request $request)
    {
        $query = $this->getStoresReportQuery($request);
        $stores = $query->get();

        return Excel::download(new StoreReportExcel($stores), 'stores_report_' . now()->format('YmdHis') . '.xlsx');
    }

    public function downloadStoresReportPdf(Request $request)
    {
        $query = $this->getStoresReportQuery($request);
        $stores = $query->get();

        $pdf = Pdf::loadView('comb_and_clippers_admin.exports.store.store-report-pdf', [
            'stores' => $stores,
        ])->setPaper('a4', 'landscape');

        return $pdf->download('stores_report_' . now()->format('YmdHis') . '.pdf');
    }

    protected function getStoresReportQuery(Request $request)
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

        return $query->withCount(['renters', 'boothRent', 'bookings'])->latest();
    }

}