<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use App\Models\User;
use App\Models\Withdrawal;
use App\Repositories\PepperestUtils;
use App\Repositories\VFDUtils;
use App\Services\WalletService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class ProcessAutoWithdrawals extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'process:auto-withdrawals';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Processes automatic wallet withdrawals to user bank accounts';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    protected $walletService;
    protected $vfdUtil;
    protected $peppUtil;
    public function __construct(WalletService $walletService, VFDUtils $vfdUtil, PepperestUtils $peppUtil)
    {
        parent::__construct();
        $this->walletService = $walletService;
        $this->vfdUtil = $vfdUtil;
        $this->peppUtil = $peppUtil;
    }

    public function handle()
    {
        Log::info('Processing automatic withdrawals...');

        // Get users with auto withdrawals
        $users = User::whereIn('withdrawal_schedule', ['auto_daily', 'auto_weekly', 'auto_monthly'])
            ->whereNotNull('wallet')
            ->get();

        foreach ($users as $user) {
            $wallet = $user->wallet;
            $bankDetails = $user->bank_details; // Assuming the relation is defined in User model

            if (!$wallet || !$bankDetails) {
                Log::warning("User {$user->id} has no wallet or bank details. Skipping...");
                continue;
            }

            // Get last withdrawal
            $lastWithdrawal = Withdrawal::where('user_id', $user->id)
                ->where('account_number', $bankDetails->account_number)
                ->where('bank_code', $bankDetails->bank_code)
                ->where('withdrawal_status', Withdrawal::SUCCESSFUL)
                ->latest()
                ->first();

            // Check if the user is due for withdrawal
            if (!$this->isDueForWithdrawal($user, $lastWithdrawal)) {
                Log::info("User {$user->id} is not due for withdrawal. Skipping...");
                continue;
            }

            $amount = $wallet->amount;
            if ($amount < 1000) { // Prevent very small withdrawals
                Log::info("User {$user->id} has insufficient funds for withdrawal. Skipping...");
                continue;
            }

            // Generate transaction reference
            $reference = "WTH-{$user->id}-" . time();
            $narration = "Auto withdrawal for {$user->name}";

            // Charge the wallet
            $this->walletService->chargeWallet($user, $wallet, $amount, $reference, $wallet->currency, $narration);

            // Process bank transfer
            $transfer = $this->processBankTransfer($bankDetails, $amount, $narration);

            if ($transfer['success']) {
                // Save withdrawal record
                $withdrawal = Withdrawal::create([
                    'user_id' => $user->id,
                    'wallet_id' => $wallet->id,
                    'amount' => $amount,
                    'narration' => $narration,
                    'account_number' => $bankDetails->account_number,
                    'account_name' => $bankDetails->account_name,
                    'bank_name' => $bankDetails->bank_name,
                    'bank_code' => $bankDetails->bank_code,
                    'withdrawal_status' => Withdrawal::SUCCESSFUL,
                    'transferRef' => $transfer['reference']
                ]);

                Log::info("Withdrawal successful for user {$user->id}");

                // Send SMS & Email Notification
                $msg = "Hi {$user->name}, Your auto-withdrawal of NGN{$amount} has been processed. Ref: {$transfer['reference']}";
                $this->sendSMS($user->phoneNo, $msg);
                $this->sendWithdrawalEmail($withdrawal);
            } else {
                Log::error("Withdrawal failed for user {$user->id}. Error: {$transfer['error']}");
            }
        }
    }

    private function isDueForWithdrawal($user, $lastWithdrawal)
    {
        $now = Carbon::now();
        if (!$lastWithdrawal)
            return true;

        switch ($user->withdrawal_schedule) {
            case 'auto_daily':
                return $lastWithdrawal->created_at->lt($now->subDay());
            case 'auto_weekly':
                return $lastWithdrawal->created_at->lt($now->subWeek());
            case 'auto_monthly':
                return $lastWithdrawal->created_at->lt($now->subMonth());
            default:
                return false;
        }
    }

    private function processBankTransfer($bankDetails, $amount, $narration)
    {
        if ($bankDetails->bank_code == '999999') {
            $response = $this->vfdUtil->transferFundIntra(
                $bankDetails->account_number,
                $bankDetails->bank_code,
                $amount,
                $narration
            );
        } else {
            $response = $this->vfdUtil->transferFund(
                $bankDetails->account_number,
                $bankDetails->bank_code,
                $amount,
                $narration
            );
        }

        return [
            'success' => $response['error'] == 0,
            'reference' => $response['reference'] ?? null,
            'error' => $response['responseMessage'] ?? 'Unknown error'
        ];
    }

    private function sendSMS($phone, $message)
    {
        $this->peppUtil->sendSMS($phone, $message);
    }

    private function sendWithdrawalEmail($withdrawal)
    {
        $this->peppUtil->send_withdrawal_email($withdrawal);
    }
}