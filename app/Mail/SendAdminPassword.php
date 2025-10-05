<?php

namespace App\Mail;

use App\Models\Admin;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendAdminPassword extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $admin;
    public $password;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(Admin $admin, $password)
    {
        $this->admin = $admin;
        $this->password = $password;
    }

    /**
     * Build the message.
     *
     * @return $this
     */


    public function build()
    {
        return $this->subject("New Admin Account Info")
            ->view('emails.send-admin-password');
    }
}
