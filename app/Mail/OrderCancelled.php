<?php

namespace App\Mail;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Contracts\Queue\ShouldBeUnique;

class OrderCancelled extends Mailable implements ShouldQueue, ShouldBeUnique
{
    use Queueable, SerializesModels;

    public $name;
    public $email;
    public $order;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($name, $email, Order $order)
    {
        $this->name = $name;
        $this->email = $email;
        $this->order = $order;
    }

    /**
     * Build the message.
     *
     * @return $this
     */


    public function build()
    {
        return $this->subject("Order {$this->order->orderRef} has been canceled.")
            ->view('emails.order-canceled');
    }
}
