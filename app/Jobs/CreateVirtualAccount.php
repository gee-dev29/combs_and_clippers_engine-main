<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use App\Repositories\VFDUtils;
use App\Models\TransactionAccount;
use Carbon\Carbon;
use \Exception;
use Illuminate\Support\Facades\Log as Logger;

class CreateVirtualAccount implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    public $tranxID;
    public $sellerName;
    public $amount;
    public $provider = 'VFD';
    public $transactionType;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($tranxID, $sellerName, $amount, $transactionType)
    {
        $this->onQueue('high');
        $this->tranxID = $tranxID;
        $this->sellerName = $sellerName;
        $this->amount = $amount;
        $this->transactionType = $transactionType;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(VFDUtils $vfdUtils)
    {
        try {
            $tranxID = $this->tranxID;
            $sellerName = $this->sellerName;
            $amount = $this->amount;
            $transactionType = $this->transactionType;
            $this->provider = env('Virtual_Account_Provider');
            $expiry = env('Virtual_Account_Validity');

            if ($transactionType == 'appointment') {
                $tranxAcct = TransactionAccount::where("appointment_id", $tranxID)->first();
                if ($this->provider == "VFD") {
                    $accountDetails = $vfdUtils->createTempAccount($sellerName, $sellerName, $amount, $expiry);
                    if ($accountDetails['error'] == 0 && $accountDetails['statusCode'] == '00') {
                        $trans_acct_details = $accountDetails['responseDetails'];

                        if (!is_null($tranxAcct)) {
                            $tranxAcct->update([
                                "account_number" => $trans_acct_details->accountNumber,
                                "account_name" => $trans_acct_details->accountName,
                                "initiationTranRef" => $accountDetails['initiationTranRef'],
                                "bank_code" => $accountDetails['bankCode'],
                                'provider' => $this->provider,
                                'expiresAt' => Carbon::now()->addMinutes($expiry)
                            ]);
                        } else {
                            TransactionAccount::create([
                                "appointment_id" => $tranxID,
                                "account_number" => $trans_acct_details->accountNumber,
                                "account_name" => $trans_acct_details->accountName,
                                "initiationTranRef" => $accountDetails['initiationTranRef'],
                                "bank_code" => $accountDetails['bankCode'],
                                'provider' => $this->provider,
                                'expiresAt' => Carbon::now()->addMinutes($expiry)
                            ]);
                        }
                    }
                }
            } else if ($transactionType == 'order') {
                $tranxAcct = TransactionAccount::where("order_id", $tranxID)->first();
                if ($this->provider == "VFD") {
                    $accountDetails = $vfdUtils->createTempAccount($sellerName, $sellerName, $amount, $expiry);
                    if ($accountDetails['error'] == 0 && $accountDetails['statusCode'] == '00') {
                        $trans_acct_details = $accountDetails['responseDetails'];

                        if (!is_null($tranxAcct)) {
                            $tranxAcct->update([
                                "account_number" => $trans_acct_details->accountNumber,
                                "account_name" => $trans_acct_details->accountName,
                                "initiationTranRef" => $accountDetails['initiationTranRef'],
                                "bank_code" => $accountDetails['bankCode'],
                                'provider' => $this->provider,
                                'expiresAt' => Carbon::now()->addMinutes($expiry)
                            ]);
                        } else {
                            TransactionAccount::create([
                                "order_id" => $tranxID,
                                "account_number" => $trans_acct_details->accountNumber,
                                "account_name" => $trans_acct_details->accountName,
                                "initiationTranRef" => $accountDetails['initiationTranRef'],
                                "bank_code" => $accountDetails['bankCode'],
                                //'account_id' => , 
                                'provider' => $this->provider,
                                'expiresAt' => Carbon::now()->addMinutes($expiry)
                            ]);
                        }
                    }
                }
            }
        } catch (Exception $e) {
            Logger::error('Virtual Account error - ', [$e->getMessage() . ' - ' . $e->__toString()]);
        }
    }
}
