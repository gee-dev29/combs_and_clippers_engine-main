<?php

namespace App\Mail;

use App\Models\Order;
use App\Models\Dispute;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Contracts\Queue\ShouldBeUnique;

class SellerAcceptedDispute extends Mailable implements ShouldQueue, ShouldBeUnique
{
    use Queueable, SerializesModels;

    public $order;
    public $option;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(Order $order, Dispute $dispute)
    {
        $this->order = $order;
        if ($dispute->dispute_option == Dispute::TYPE_REFUND) {
            $this->option = 'refund';
        } else {
            $this->option = 'replacement';
        }
    }

    /**
     * Build the message.
     *
     * @return $this
     */


    public function build()
    {
        return $this->subject("Your Dispute on Order {$this->order->orderRef} has been accepted.")
            ->view('emails.seller-accepted-dispute');
    }
}
