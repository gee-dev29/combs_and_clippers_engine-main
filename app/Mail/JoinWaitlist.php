<?php

namespace App\Mail;

use App\Models\User;
use App\Models\Waitlist;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class JoinWaitlist extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $user;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(Waitlist $user)
    {
        $this->user = $user;
    }

    /**
     * Build the message.
     *
     * @return $this
     */


    public function build()
    {
        return $this->subject("Thank you for joining our waitlist!")
            ->view('emails.join-waitlist');
    }
}
