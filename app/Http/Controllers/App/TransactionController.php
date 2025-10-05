<?php

namespace App\Http\Controllers\App;

use App\Models\User;
use App\Models\Withdrawal;
use App\Models\Appointment;
use Illuminate\Http\Request;
use App\Models\WalletTransaction;
use App\Models\PaymentTransaction;
use App\Models\InternalTransaction;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Http\Resources\TransactionResource;
use App\Http\Resources\TransactionCollection;

class TransactionController extends Controller
{

    public function index(Request $request)
    {
        $userId = $this->getAuthID($request);
        $user = User::find($userId);

        // Get filter parameter (default to 'all')
        $filter = $request->input('filter', 'all');



        // Start with empty collection
        $transactions = collect();

        // Get wallet transactions
        $walletTransactions = $this->getWalletTransactions($user->id, $filter);
        $transactions = $transactions->merge($walletTransactions);

        // Get withdrawals
        $withdrawals = $this->getWithdrawals($user->id, $filter);
        $transactions = $transactions->merge($withdrawals);

        // Get payment transactions
        $paymentTransactions = $this->getPaymentTransactions($user->id, $filter);
        $transactions = $transactions->merge($paymentTransactions);

        // Get appointment transactions
        $appointmentTransactions = $this->getAppointmentTransactions($user->id, $filter);
        $transactions = $transactions->merge($appointmentTransactions);

        // Get internal transactions
        $internalTransactions = $this->getInternalTransactions($user->id, $filter);
        $transactions = $transactions->merge($internalTransactions);

        // Sort by created_at descending (newest first)
        $sortedTransactions = $transactions->sortByDesc('created_at');

        // Paginate results
        $perPage = $request->input('per_page', 15);
        $page = $request->input('page', 1);

        $paginatedTransactions = $this->paginateCollection($sortedTransactions, $perPage, $page);

        return new TransactionCollection($paginatedTransactions);
    }

    /**
     * Get wallet transactions
     */
    private function getWalletTransactions($userId, $filter)
    {
        $query = WalletTransaction::whereHas('wallet', function ($q) use ($userId) {
            $q->where('user_id', $userId);
        });

        if ($filter === 'pending') {
            $query->where('status', WalletTransaction::PENDING);
        } elseif ($filter === 'processed') {
            $query->where('status', WalletTransaction::SUCCESSFUL);
        }

        return $query->get()->map(function ($transaction) {
            return [
                'id' => $transaction->id,
                'type' => 'wallet_transaction',
                'amount' => $transaction->amount,
                'charges' => 0, // Wallet transactions typically don't have charges
                'status' => $transaction->status,
                'narration' => $transaction->narration,
                'reference' => $transaction->transaction_ref,
                'currency' => $transaction->currency,
                'created_at' => $transaction->created_at,
                'details' => $transaction,
            ];
        });
    }

    /**
     * Get withdrawal transactions
     */
    private function getWithdrawals($userId, $filter)
    {
        $query = Withdrawal::where('user_id', $userId);

        if ($filter === 'pending') {
            $query->whereIn('withdrawal_status', [Withdrawal::PENDING, Withdrawal::PROCESSING]);
        } elseif ($filter === 'processed') {
            $query->where('withdrawal_status', Withdrawal::SUCCESSFUL);
        }

        return $query->get()->map(function ($withdrawal) {
            return [
                'id' => $withdrawal->id,
                'type' => 'withdrawal',
                'amount' => $withdrawal->amount,
                'charges' => $withdrawal->fee,
                'status' => $this->mapWithdrawalStatus($withdrawal->withdrawal_status),
                'narration' => $withdrawal->narration,
                'reference' => $withdrawal->transferRef,
                'currency' => null, // Withdrawal model doesn't have currency
                'created_at' => $withdrawal->created_at,
                'details' => $withdrawal,
            ];
        });
    }

    /**
     * Get payment transactions
     */
    private function getPaymentTransactions($userId, $filter)
    {
        $query = PaymentTransaction::where('user_id', $userId);

        if ($filter === 'pending') {
            $query->where('trans_status', 'pending')->orWhere('trans_status', 0);
        } elseif ($filter === 'processed') {
            $query->where('trans_status', 'success')->orWhere('trans_status', 1);
        }

        return $query->get()->map(function ($transaction) {
            return [
                'id' => $transaction->id,
                'type' => 'payment',
                'amount' => $transaction->amount,
                'charges' => 0, // PaymentTransaction doesn't appear to have charges
                'status' => $transaction->trans_status,
                'narration' => 'Payment via ' . $transaction->card_type . ' (' . $transaction->last_four_digit . ')',
                'reference' => $transaction->trans_ref,
                'currency' => $transaction->currency,
                'created_at' => $transaction->created_at,
                'details' => $transaction,
            ];
        });
    }

    /**
     * Get appointment transactions
     */
    private function getAppointmentTransactions($userId, $filter)
    {
        // Appointments as customer
        $customerQuery = Appointment::where('customer_id', $userId);

        if ($filter === 'pending') {
            $customerQuery->where('payment_status', 'pending')->orWhere('payment_status', 0);
        } elseif ($filter === 'processed') {
            $customerQuery->where('payment_status', 'paid')->orWhere('payment_status', 1);
        }

        $customerAppointments = $customerQuery->get();

        // Appointments as merchant
        $merchantQuery = Appointment::where('merchant_id', $userId);

        if ($filter === 'pending') {
            $merchantQuery->where('payment_status', 'pending')->orWhere('payment_status', 0);
        } elseif ($filter === 'processed') {
            $merchantQuery->where('payment_status', 'paid')->orWhere('payment_status', 1);
        }

        $merchantAppointments = $merchantQuery->get();

        // Combine and process
        $appointments = $customerAppointments->merge($merchantAppointments);

        return $appointments->map(function ($appointment) use ($userId) {
            $isCustomer = $appointment->customer_id === $userId;

            return [
                'id' => $appointment->id,
                'type' => 'appointment',
                'amount' => $appointment->total_amount,
                'charges' => $appointment->processing_fee,
                'status' => $appointment->payment_status,
                'narration' => $isCustomer
                    ? 'Appointment payment to ' . optional($appointment->serviceProvider)->name
                    : 'Appointment payment from ' . optional($appointment->customer)->name,
                'reference' => $appointment->appointment_ref,
                'currency' => $appointment->currency,
                'created_at' => $appointment->created_at,
                'details' => $appointment,
            ];
        });
    }

    /**
     * Get internal transactions
     */
    private function getInternalTransactions($userId, $filter)
    {
        $query = InternalTransaction::where('merchant_id', $userId)
            ->orWhere('customer_id', $userId);

        if ($filter === 'pending') {
            $query->where('payment_status', 'pending')->orWhere('payment_status', 0);
        } elseif ($filter === 'processed') {
            $query->where('payment_status', 'paid')->orWhere('payment_status', 1);
        }

        return $query->get()->map(function ($transaction) use ($userId) {
            $isMerchant = $transaction->merchant_id === $userId;

            return [
                'id' => $transaction->id,
                'type' => 'internal',
                'amount' => $transaction->amount,
                'charges' => 0, // Internal transactions don't appear to have charges
                'status' => $transaction->payment_status,
                'narration' => $transaction->narration,
                'reference' => $transaction->transaction_ref,
                'currency' => $transaction->currency,
                'created_at' => $transaction->created_at,
                'details' => $transaction,
            ];
        });
    }

    /**
     * Map withdrawal status values to user-friendly strings
     */
    private function mapWithdrawalStatus($status)
    {
        $statusMap = [
            Withdrawal::SUCCESSFUL => 'successful',
            Withdrawal::PENDING => 'pending',
            Withdrawal::FAILED => 'failed',
            Withdrawal::PROCESSING => 'processing',
        ];

        return $statusMap[$status] ?? 'unknown';
    }

    /**
     * Paginate a collection manually
     */
    private function paginateCollection($collection, $perPage, $page)
    {
        $offset = ($page - 1) * $perPage;

        return $collection->slice($offset, $perPage)->values();
    }
}