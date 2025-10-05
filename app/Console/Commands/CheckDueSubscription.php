<?php

namespace App\Console\Commands;

use DateTime;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Subscription;
use App\Repositories\Mailer;
use Illuminate\Console\Command;
use App\Models\UserSubscription;
use Illuminate\Support\Facades\Log as Logger;

class CheckDueSubscription extends Command
{
    /**
     * The Mailer instance.
     *
     * @var Mailer
     */
    protected $Mailer;

    /**OrderTransaction
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'subscriptions:checkDue';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This checks for subscriptions that are due for renewal and sends email to users.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(Mailer $mailer)
    {
        parent::__construct();
        $this->Mailer = $mailer;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $today = Carbon::today();
        $twoDaysAfter = Carbon::today()->addDays(2);

        $dueInTwoDaysSubs = UserSubscription::where([['active', 1], ['status', 'SUCCESSFUL_TXN']])
            ->whereDate('expires_at', $twoDaysAfter)
            ->get();

        $dueTodaySubs = UserSubscription::where([['active', 1], ['status', 'SUCCESSFUL_TXN']])
            ->whereDate('expires_at', $today)
            ->get();

        if (!$dueInTwoDaysSubs->count() && !$dueTodaySubs->count()) {
            $this->error('No subscriptions are due for renewal today and in two days');
            return;
        }

        $messages = [];

        if ($dueInTwoDaysSubs->count()) {
            $countDueInTwoDaysSubs = count($dueInTwoDaysSubs);
            $messages[] = "Found {$countDueInTwoDaysSubs} subscriptions due in two days";
            foreach ($dueInTwoDaysSubs as $dueInTwoDaysSub) {
                $merchant = User::find($dueInTwoDaysSub->user_id);
                if (!is_null($merchant)) {
                    $this->Mailer->sendSubscriptionDueEmail($merchant, 2);
                    $messages[] = "{$merchant->name} subscription is due in two days and has been notified";
                }
            }
        }

        if ($dueTodaySubs->count()) {
            $countDueTodaySubs = count($dueTodaySubs);
            $messages[] = "Found {$countDueTodaySubs} subscriptions due today";
            foreach ($dueTodaySubs as $dueTodaySub) {
                $merchant = User::find($dueTodaySub->user_id);
                if (!is_null($merchant)) {
                    $this->Mailer->sendSubscriptionDueEmail($merchant, 1);
                    $messages[] = "{$merchant->name} subscription is due today and has been notified";
                }
            }
        }

        $messages = implode("\n", $messages);
        $this->info($messages);
    }
}
