<?php

namespace App\Console\Commands;

use DateTime;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Order;
use App\Models\Wallet;
use App\Models\Subscription;
use App\Repositories\Mailer;
use App\Repositories\MomoUtils;
use Illuminate\Console\Command;
use App\Models\UserSubscription;
use Illuminate\Support\Facades\Log as Logger;

class AutoRenewSubscription extends Command
{
    /**
     * The MomoUtils instance.
     *
     * @var MomoUtils
     */
    protected $Momo;

    /**
     * The Mailer instance.
     *
     * @var Mailer
     */
    protected $Mailer;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'subscriptions:auto_renew';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This renews sellers subscriptions every 6 hours.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(MomoUtils $momoUtils, Mailer $mailer)
    {
        parent::__construct();
        $this->Momo = $momoUtils;
        $this->Mailer = $mailer;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $expired_subscriptions = UserSubscription::where([['active', 0], ['expires_at', '<=', now()]])->get();

        if (!$expired_subscriptions->count()) {
            $this->error('No expired subscriptions found');
            return;
        }
        $count = count($expired_subscriptions);
        $messages = ["Found {$count} expired subscriptions"];

        foreach ($expired_subscriptions as $expired_subscription) {
            $subscription = Subscription::find($expired_subscription->subscription_id);
            $merchant = User::find($expired_subscription->user_id);
            if (!is_null($merchant)) {
                //send mail notification to merchant
                //$this->Mailer->sendSubscriptionDueEmail($merchant, 0);

                //skip renewal if merchant has renewed his subscription
                if ($merchant->hasActiveSubscription()) {
                    $messages[] = "Skipping - {$merchant->name} already renewed subscription.";
                    continue;
                }

                //subscribe user that just finished free trial to weekly subscription
                if ($subscription->isFree() || $subscription->hasFreeTrial()) {
                    $subscription = Subscription::find(Subscription::WEEKLY);
                }

                //send subscription renewal request
                $sub = $this->activateSubscription($subscription, $merchant);

                Logger::info("Subscription Renewal Response - ", $sub);

                if ($sub['error'] != 1) {
                    //send sms
                    $app_name = env('APP_NAME');
                    $msg = "Dear {$merchant->name}, your {$app_name} subscription renewal is awaiting your approval. Kindly approve payment to renew your subscription. \nThank you!";
                    $expires_at = new DateTime('+' . $subscription->invoice_period . ' ' . $subscription->invoice_interval);
                    $merchant_sub = UserSubscription::create(
                        [
                            'user_id' => $merchant->id,
                            'subscription_id' => $subscription->id,
                            'ext_trans_id' => $sub['ext_trans_id'],
                            'internal_trans_id' => $sub['internal_trans_id'],
                            'status' => $sub['status'],
                            'active' => 0,
                            'expires_at' => $expires_at,
                        ]
                    );
                    $messages[] = "{$merchant->name} subscription renewal request has been sent and awaiting approval";
                } else {
                    $messages[] = "{$merchant->name} subscription renewal request has failed";
                }
            }
        }
        $messages = implode("\n", $messages);
        $this->info($messages);
    }

    protected function activateSubscription($subscription, $merchant)
    {
        //call momo subscription
        $transactionId = @date('Ymdhis');
        //$msisdn = '256789999576';
        $msisdn = $merchant->phone;
        $service_code = $subscription->service_code;
        $sub = $this->Momo->subscribe($msisdn, $service_code, $transactionId);
        return $sub;
    }
}
