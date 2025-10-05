<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{

    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        Commands\CreditProviderWallet::class,
        Commands\RenewSubscription::class,
        Commands\AutoRenewSubscription::class,
        Commands\CheckDueSubscription::class,
        Commands\TrackOrder::class,
        Commands\TelnetCheckCommand::class,
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        // $schedule->command('inspire')->hourly();
        $schedule->command('wallets:credit')->everyTenMinutes()->withoutOverlapping();
        if (appIsOnProduction()) {
            $schedule->command('wallets:credit')->everyTenMinutes()->withoutOverlapping();
            $schedule->command('process:wallet-debit')->everyTenMinutes()
            ->between('6:00', '22:00');
            $schedule->command('process:wallet-debit-pending')->hourly()
            ->between('7:00', '21:00');
            $schedule->command('process:wallet-dispute')->hourly()
            ->between('7:00', '21:00');


            // $schedule->command('subscriptions:renew')->hourly()->withoutOverlapping();
            // $schedule->command('subscriptions:checkDue')->daily()->withoutOverlapping();
            //$schedule->command('subscriptions:auto_renew')->everySixHours()->withoutOverlapping()->emailOutputTo('emmanuel6.obute@gmail.com');
            //$schedule->command('orders:track')->twiceDaily(10, 15)->withoutOverlapping();
            //$schedule->command('telnet:check')->dailyAt(6)->runInBackground()->withoutOverlapping()->emailOutputTo('emmanuel6.obute@gmail.com');
            //$schedule->command('transactions:queryStatus')->everyMinute()->withoutOverlapping()->emailOutputTo('emmanuel6.obute@gmail.com');
            
        }
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }

    /**
     * Get the timezone that should be used by default for scheduled events.
     *
     * @return \DateTimeZone|string|null
     */
    protected function scheduleTimezone()
    {
        return 'Africa/Lagos';
    }

    protected function bootstrappers()
    {
        return array_merge(
            [\Bugsnag\BugsnagLaravel\OomBootstrapper::class],
            parent::bootstrappers(),
        );
    }
}
