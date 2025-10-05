<?php

namespace App\Mail;

use App\Models\Order;
use App\Models\Dispute;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Contracts\Queue\ShouldBeUnique;

class BuyerRaisedDispute extends Mailable implements ShouldQueue, ShouldBeUnique
{
    use Queueable, SerializesModels;

    public $order;
    public $dispute;
    public $option;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(Order $order, Dispute $dispute)
    {
        $this->order = $order;
        $this->dispute = $dispute;
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
        $support_mail = cc('support_mail');
        return $this->subject("A Dispute has been raised on order {$this->order->orderRef}.")
            ->bcc($support_mail)
            ->view('emails.buyer-raised-dispute');
    }
}
