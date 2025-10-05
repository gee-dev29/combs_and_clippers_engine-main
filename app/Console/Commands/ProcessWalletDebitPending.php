<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use App\Models\Bank;
use App\Models\Withdrawal;
use App\Repositories\VFDUtils;
use Illuminate\Console\Command;
use App\Models\WalletTransaction;
use App\Models\PaymentDisbursement;
use App\Repositories\PepperestUtils;
use Illuminate\Support\Facades\Log as Logger;

class ProcessWalletDebitPending extends Command
{
    /**
     * The provider name.
     *
     * @var string
     */
    protected $provider;

    /**
     * The VFDUtils instance.
     *
     * @var VFDUtils
     */
    protected $vfdUtil;

    /**
     * The PepperestUtils instance.
     *
     * @var PepperestUtils
     */
    protected $peppUtil;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'process:wallet-debit-pending';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This handles wallet debit in processing status, it processes the fund disbursement.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(VFDUtils $vfdUtil, PepperestUtils $peppUtil)
    {
        parent::__construct();
        $this->provider = 'VFD';
        $this->vfdUtil = $vfdUtil;
        $this->peppUtil = $peppUtil;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        //$date_time = Carbon::now()->addDays(1);
        $debitRequests = Withdrawal::where(['withdrawal_status' => Withdrawal::PROCESSING])->get();
        Logger::info('wallet debit Requests due for processing', [$debitRequests]);
        if (!is_null($debitRequests)) {
            $statusArr = cc('transaction.statusArray');
            foreach ($debitRequests as $debitReq) {
                //mark withdrawal as processing so another worker doesn't pick up the same job before reaching completion
                $receiver_acctno = $debitReq->account_number;
                $receiver_bankcode = $debitReq->bank_code;
                $amount = $debitReq->amount;
                $fromAcct = env('VFD_ACCOUNT_NO');
                if (!is_null($debitReq->wallet_id) && !is_null($debitReq->wallet)) {
                    $name = $debitReq->user->name;
                    $debit_amount = $debitReq->amount;
                    $wallet_id = $debitReq->wallet_id;
                    $narration = "Combs_and_clippers wallet debit @ $name";
                    $bank = Bank::where('bankcode', $receiver_bankcode)->first();
                    //Logger::info('tranx bank', [$bank]);
                    if (!is_null($bank) && !is_null($bank->vfd_bankcode)) {
                        $receiver_bankcode = $bank->vfd_bankcode;
                    } else {
                        Logger::info('No Bank details for refund request', [$debitReq]);
                        continue;
                    }
                    $narration_new = !is_null($debitReq->narration) ? $debitReq->narration : $narration;
                    //Check transaction status if transferRef exists
                    if (!is_null($debitReq->transferRef)) {
                        $transfer = $this->vfdUtil->transactionStatusQuery($debitReq->transferRef);
                        if ($transfer['error'] == 1) {
                            if ($receiver_bankcode == '999999') {
                                $transfer = $this->vfdUtil->transferFundIntra($receiver_acctno, $receiver_bankcode, $amount, $narration_new);
                            } else {
                                $transfer = $this->vfdUtil->transferFund($receiver_acctno, $receiver_bankcode, $amount, $narration_new);
                            }
                        } elseif ($transfer['error'] == 0 && $transfer['transferInfo']->transactionStatus != "00") {
                            if ($receiver_bankcode == '999999') {
                                $transfer = $this->vfdUtil->transferFundIntra($receiver_acctno, $receiver_bankcode, $amount, $narration_new);
                            } else {
                                $transfer = $this->vfdUtil->transferFund($receiver_acctno, $receiver_bankcode, $amount, $narration_new);
                            }
                        }
                    } else {
                        if ($receiver_bankcode == '999999') {
                            $transfer = $this->vfdUtil->transferFundIntra($receiver_acctno, $receiver_bankcode, $amount, $narration_new);
                        } else {
                            $transfer = $this->vfdUtil->transferFund($receiver_acctno, $receiver_bankcode, $amount, $narration_new);
                        }
                    }


                    Logger::info('Transfer Response', [$transfer]);

                    if ($transfer['error'] == 0) {
                        $debitReq->update([
                            'withdrawal_status' => Withdrawal::SUCCESSFUL,
                            'transferRef' => $transfer["reference"]
                        ]);

                        //notify wallet owner of withdrawal
                        $this->peppUtil->send_withdrawal_email($debitReq);

                        $name = $debitReq->user->name;
                        $balance = $debitReq->wallet->amount;
                        $ref = $debitReq->account_name;
                        $msg = "Combs_and_clippers: Hi $name! \nYour wallet debit request has been processed: \nDR: $amount \nBal: $balance \nTo: $ref";
                        $this->peppUtil->sendSMS($debitReq->user->phoneNo, $msg);
                        $wallet_T = WalletTransaction::where('withdrawal_id', $debitReq->id)->first();
                        if (!is_null($wallet_T)) {
                            $wallet_T->update([
                                'status' => WalletTransaction::SUCCESSFUL,
                                'transaction_ref' => $transfer["reference"]
                            ]);
                        }

                        if (isset($transfer['transferInfo'])) {
                            //$res = $transfer['transferInfo'];
                            $transferRef = $transfer["reference"];
                            $traceID = $transfer["reference"];
                        } else {
                            $transferRef = "Mozfin";
                            $traceID = "Mozfin";
                        }

                        PaymentDisbursement::create([
                            //'order_transaction_id' => $tranx->id, 
                            'transferRef' => $transferRef,
                            'traceID' => $traceID,
                            //'orderPaymentRef' => $tranx->paymentRef, 
                            'fromAcc' => $fromAcct,
                            'toAcc' => $receiver_acctno,
                            'toAcc_bankcode' => $receiver_bankcode,
                            'amount' => $amount,
                            'narration' => $narration,
                            'responseCode' => $transfer['statusCode'],
                            'responseMessage' => $transfer['responseMessage'],
                            'statusMessage' => $transfer['responseMessage']
                        ]);
                    } else {
                        if ($this->provider == 'VFD' && isset($transfer["reference"])) {
                            $debitReq->update(['transferRef' => $transfer["reference"]]);
                        }
                    }
                    # code...
                }
            }
        }
    }
}
