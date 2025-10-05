<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use App\Repositories\VFDUtils;
use App\Models\TransactionAccount;
use App\Models\Appointment;
use App\Models\BoothRentalPayment;
use App\Models\User;
use App\Models\Order;
use Carbon\Carbon;
use \Exception;
use Illuminate\Support\Facades\Log as Logger;

class LinkMerchantVirtualAccount implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    public $tranxID;
    public $provider = 'VFD';
    public $transactionType;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($tranxID, $transactionType)
    {
        $this->onQueue('high');
        $this->tranxID = $tranxID;
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
            $transactionType = $this->transactionType;
            $this->provider = config('services.vfd.provider');
            //$expiry = env('Virtual_Account_Validity');
            Logger::info('tranxID : '. $tranxID . " transactionType: ".$transactionType . " provider: ". $this->provider);
            $processing_fee = config('services.vfd.processing_fee');

            if ($transactionType == 'appointment') {
                $appointment = Appointment::find($tranxID);
                $appointment->update(["processing_fee" => $processing_fee]);
                if(is_null($appointment)){
                    Logger::error("Appointment not found, ID:  {$tranxID}");
                    exit;
                }
                $tranxAcct = TransactionAccount::where("appointment_id", $tranxID)->first();
                $merchant = $appointment->serviceProvider;
                $wallet = $merchant->wallet;
                if ($this->provider == "VFD" && !is_null($wallet)) {
                    if (!is_null($tranxAcct)) {
                        $tranxAcct->update([
                            "account_number" => $wallet->account_number,
                            "account_name" => $merchant->name,
                            "bank_code" => $wallet->bank_code,
                            'provider' => $this->provider,
                            "amount" => $appointment->total_amount, 
                            "processing_fee" => $processing_fee, 
                            "total" => $appointment->total_amount + $processing_fee
                        ]);
                    } else {
                        TransactionAccount::create([
                            "appointment_id" => $tranxID,
                            "account_number" => $wallet->account_number,
                            "account_name" => $merchant->name,
                            "bank_code" => $wallet->bank_code,
                            'provider' => $this->provider,
                            "amount" => $appointment->total_amount, 
                            "processing_fee" => $processing_fee, 
                            "total" => $appointment->total_amount + $processing_fee
                        ]);
                    }
                }
            }else if ($transactionType == 'booth_rental_payment') {
                $booth_rent_pay = BoothRentalPayment::find($tranxID);
                $booth_rent_pay->update(["processing_fee" => $processing_fee]);
                if(is_null($booth_rent_pay)){
                    Logger::error("Booth Rental Payment not found, ID:  {$tranxID}");
                    exit;
                }
                $tranxAcct = TransactionAccount::where("booth_rental_payment_id", $tranxID)->first();
                $store = $booth_rent_pay->userStore->store;
                $merchant = User::find($store->merchant_id);
                $wallet = $merchant->wallet;
                Logger::info('wallet : ', [$wallet]);
                if ($this->provider == "VFD" && !is_null($wallet)) {
                    if (!is_null($tranxAcct)) {
                        $tranxAcct->update([
                            "account_number" => $wallet->account_number,
                            "account_name" => $merchant->name,
                            "bank_code" => $wallet->bank_code,
                            'provider' => $this->provider,
                            "amount" => $booth_rent_pay->amount, 
                            "processing_fee" => $processing_fee, 
                            "total" => $booth_rent_pay->amount + $processing_fee
                        ]);
                    } else {
                        TransactionAccount::create([
                            "booth_rental_payment_id" => $tranxID,
                            "account_number" => $wallet->account_number,
                            "account_name" => $merchant->name,
                            "bank_code" => $wallet->bank_code,
                            'provider' => $this->provider,
                            "amount" => $booth_rent_pay->amount, 
                            "processing_fee" => $processing_fee, 
                            "total" => $booth_rent_pay->amount + $processing_fee
                        ]);
                    }
                }
            }else if ($transactionType == 'order') {
                $tranxAcct = TransactionAccount::where("order_id", $tranxID)->first();
                $order = Order::find($tranxID);
                $merchant = User::find($order->merchant_id);
                $wallet = $merchant->wallet;
                if ($this->provider == "VFD" && !is_null($wallet)) {
                    if (!is_null($tranxAcct)) {
                        $tranxAcct->update([
                            "account_number" => $wallet->account_number,
                            "account_name" => $merchant->name,
                            "bank_code" => $wallet->bank_code,
                            'provider' => $this->provider,
                            "processing_fee" => $processing_fee, 
                            "total" => $order->total + $processing_fee
                        ]);
                    } else {
                        TransactionAccount::create([
                            "order_id" => $tranxID,
                            "account_number" => $wallet->account_number,
                            "account_name" => $merchant->name,
                            "bank_code" => $wallet->bank_code,
                            'provider' => $this->provider,
                            "processing_fee" => $processing_fee, 
                            "total" => $order->total + $processing_fee
                        ]);
                    }
                }
            }
        } catch (Exception $e) {
            Logger::error('Virtual Account error - ', [$e->getMessage() . ' - ' . $e->__toString()]);
        }
    }
}
