<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use App\Models\Wallet;
use App\Models\Appointment;
use App\Models\Transaction;
use App\Services\WalletService;
use Illuminate\Console\Command;
use App\Models\WalletTransaction;
use App\Models\InternalTransaction;
use Illuminate\Support\Facades\Log as Logger;

class CreditProviderWallet extends Command
{
    /**OrderTransaction
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'wallets:credit {--appointment_id= : ID of the appointment that the provider is credited for}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This releases providers funds for services that have been rendered.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        if (!empty($this->option('appointment_id'))) {
            $appointment_id = $this->option('appointment_id');
            $appointments = Appointment::where(['id' => $appointment_id, 'payment_status' => 1, 'disbursement_status' => 0])->get();
        } else {
            //['date', '<=', Carbon::today()], 
            $appointments = Appointment::where([['payment_status', 1], ['disbursement_status', 0]])->where(function ($query) {
                $query->where('status', 'Completed')
                    ->orWhere(function ($q) {
                        $q->where('status', 'Delivered')
                            ->whereNotNull('client_confirmed_at')
                            ->whereNull('merchant_confirmed_at')
                            ->where('client_confirmed_at', '<=', Carbon::now()->subHours(2));
                    });
            })->get();
        }

        Logger::info('wallets:credit - appointments', [$appointments]);

        if (!$appointments->count()) {
            $this->error('No appointments due for disbursement...');
            return;
        }

        $count = count($appointments);
        $messages = ["Found {$count} appointments due for disbursement"];

        foreach ($appointments as $appointment) {
            //get wallet transaction
            $walletT = WalletTransaction::where('transaction_ref', $appointment->payment_ref)->first();
            Logger::info('wallet Transaction', [$walletT]);
            if (!is_null($walletT)) {
                $this->debitWalletUnclaimedBal($walletT->wallet_id, $walletT->amount);
                $this->creditWallet($walletT->wallet_id, $walletT->amount);
                $walletT->update(['status' => WalletTransaction::SUCCESSFUL]);
                $appointment->update(['disbursement_status' => 1, 'status' => "Completed"]);
                $messages[] = "Wallet #{$walletT->wallet_id} has been credited with {$walletT->amount}";
            }
        }
        $messages = implode("\n", $messages);
        $this->info($messages);
    }

    protected function creditWallet($wallet_id, $amount)
    {
        $wallet = Wallet::find($wallet_id);
        if (!is_null($wallet)) {
            $balance = $wallet->amount;
            //update wallet amount
            $wallet->update(['amount' => ($balance + $amount)]);
        }
    }

    protected function debitWalletUnclaimedBal($wallet_id, $amount)
    {
        $wallet = Wallet::find($wallet_id);
        if (!is_null($wallet)) {
            $balance = $wallet->unclaimed_amount;
            //update wallet unclaimed_amount
            $wallet->update(['unclaimed_amount' => ($balance - $amount)]);
        }
    }
}