<?php

namespace App\Mail;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Contracts\Queue\ShouldBeUnique;

class OrderStatusChange extends Mailable implements ShouldQueue, ShouldBeUnique
{
    use Queueable, SerializesModels;

    public $order;
    public $status;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(Order $order, $status)
    {
        $this->order = $order;
        $this->status = $status;
    }

    /**
     * Build the message.
     *
     * @return $this
     */


    public function build()
    {
        $app_name = env("APP_NAME");
        return $this->subject("Your {$app_name} Order {$this->order->orderRef} is {$this->status}.")
            ->view('emails.order-status-change');
    }
}
