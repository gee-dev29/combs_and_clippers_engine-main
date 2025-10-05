<?php

namespace App\Mail;

use App\Models\User;
use App\Models\Waitlist;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class InviteToWaitlist extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $email;
    public $referrer;
    public $referral_code;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($email, $referrer, $referral_code)
    {
        $this->email = $email;
        $this->referrer = $referrer;
        $this->referral_code = $referral_code;
    }

    /**
     * Build the message.
     *
     * @return $this
     */


    public function build()
    {
        $app_name = env("APP_NAME");
        return $this->subject("Invitation to join {$app_name} waitlist!")
            ->view('emails.waitlist-invite');
    }
}
