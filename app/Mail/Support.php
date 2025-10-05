<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class Support extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $name;
    public $email;
    public $mail_subject;
    public $contents;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($name, $email, $subject, $message)
    {
        $this->name = $name;
        $this->email = $email;
        $this->mail_subject = $subject;
        $this->contents = $message;
    }

    /**
     * Build the message.
     *
     * @return $this
     */


    public function build()
    {
        return $this->subject('Customer support request : '. $this->mail_subject)
            ->replyTo($this->email, $this->name)
            ->view('emails.support');
    }
}
