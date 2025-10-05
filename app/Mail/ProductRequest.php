<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class ProductRequest extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $user;
    public $productRequest;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($user, $productRequest)
    {
        $this->user = $user;
        $this->productRequest = $productRequest;
    }

    /**
     * Build the message.
     *
     * @return $this
     */


    public function build()
    {
        $support_mail = cc('support_mail');
        return $this->subject("Request For Product.")
            ->replyTo($support_mail)
            ->view('emails.product-request');
    }
}
