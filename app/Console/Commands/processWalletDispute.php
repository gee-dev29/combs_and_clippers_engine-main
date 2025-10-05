<?php

namespace App\Console\Commands;

use stdClass;
use App\Models\WalletDispute;
use App\Repositories\VFDUtils;
use App\Services\WalletService;
use Illuminate\Console\Command;
use App\Models\PaymentDisbursement;
use App\Repositories\PepperestUtils;
use Illuminate\Support\Facades\Log as Logger;

class processWalletDispute extends Command
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
    protected $signature = 'process:wallet-dispute';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This handles wallet dispute, it processes the fund disbursement.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(VFDUtils $vfdUtil, PepperestUtils $peppUtil)
    {
        parent::__construct();
        //$this->provider = env('Wallet_Account_Provider');
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
        $disputeRequests = WalletDispute::where(['status' => WalletDispute::ACCEPTED])->get();
        Logger::info('wallet dispute Requests due for processing', [$disputeRequests]);
        if (!is_null($disputeRequests)) {
            foreach ($disputeRequests as $disputeReq) {
                //mark as processing so another worker doesn't pick up the same job before reaching completion
                if (!is_null($disputeReq->wallet_id) && !is_null($disputeReq->wallet)) {
                    $disputeReq->update(['withdrawal_status' => WalletDispute::PROCESSING]);
                    $receiver_acctno = $disputeReq->wallet->account_number;
                    $receiver_bankcode = $disputeReq->wallet->bank_code;
                    $amount = $disputeReq->transaction->amount;
                    $fromAcct = env('VFD_ACCOUNT_NO');
                    $receiver_name = $disputeReq->user->name;
                    $wallet_id = $disputeReq->wallet_id;
                    $narration = "Combs_and_clippers wallet refund:$receiver_name";
                    $this->info("Processing transaction...");
                    $walletService = new WalletService;
                    $wallet = $walletService->getWalletByaccountNo($receiver_acctno);
                    $payload = new stdClass;
                    $reference = "WALL-{$wallet->id}-refund-" . time();
                    $payload->ref = $reference;
                    $payload->paymentreference = $reference;
                    $payload->narration = $narration;
                    $payload->originatoraccountnumber = $disputeReq->transaction->from_account_no;
                    $payload->originatorname = $disputeReq->transaction->from_account_name;
                    $payload->bankname = $disputeReq->transaction->from_bank_name;
                    $payload->bankcode = $disputeReq->transaction->from_bank_code;
                    $payload->craccount = $disputeReq->transaction->to_account_no;
                    $payload->craccountname = $disputeReq->transaction->to_account_name;
                    //credit recipient wallet account with us
                    if (!is_null($wallet) && !is_null($wallet->user)) {
                        $walletService->creditWallet($wallet->user, $wallet, $amount, 'NGN', $payload);
                        $this->info("Recipient wallet #{$wallet->id} credited with {$amount}.");
                    }
                    $disputeReq->update(['status' => WalletDispute::CLOSED]);

                    PaymentDisbursement::create([
                        //'order_transaction_id' => $tranx->id, 
                        'transferRef' => $payload->ref,
                        'traceID' => $payload->ref,
                        //'orderPaymentRef' => $tranx->paymentRef, 
                        'fromAcc' => $fromAcct,
                        'toAcc' => $receiver_acctno,
                        'toAcc_bankcode' => $receiver_bankcode,
                        'amount' => $amount,
                        'narration' => $narration,
                        'responseCode' => 200,
                        'responseMessage' => 'successful',
                        'statusMessage' => 'successful'
                    ]);
                    $this->info("Process completed.");
                }
            }
        } else {
            $this->info("No wallet dispute due for processing");
        }
    }
}
