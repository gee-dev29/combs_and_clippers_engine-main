<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class BoothRentDue extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $user;
    public $days;
    public $keyword;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(User $user, $days = 0)
    {
        $this->user = $user;
        $this->days = $days;
        switch ($this->days) {
            case 2:
                $this->keyword = "will be expiring in 2 days";
                break;

            case 1:
                $this->keyword = "will be expiring today";
                break;

            default:
                $this->keyword = "has expired";
                break;
        }
    }

    /**
     * Build the message.
     *
     * @return $this
     */


    public function build()
    {
        switch ($this->days) {
            case 2:
                $subject = "Your Booth Rent renewal is Due in 2 days!";
                break;

            case 1:
                $subject = "Your Booth Rent renewal is Due Today!";
                break;

            default:
                $subject = "Your Booth Rent has expired!";
                break;
        }

        return $this->subject($subject)
            ->view('emails.boothrent-due');
    }
}
