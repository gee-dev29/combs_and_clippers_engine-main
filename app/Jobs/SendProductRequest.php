<?php

namespace App\Jobs;

use App\Repositories\Mailer;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Contracts\Queue\ShouldBeUnique;

class SendProductRequest implements ShouldQueue, ShouldBeUnique
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $users;
    protected $productRequest;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($users, $productRequest)
    {
        $this->onQueue('high');
        $this->users = $users;
        $this->productRequest = $productRequest;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(Mailer $mailer)
    {
        $users   = $this->users;
        $productRequest = $this->productRequest;

        foreach ($users as $user) {
            $mailer->sendProductRequestEmail($user, $productRequest);
        }
    }
}
