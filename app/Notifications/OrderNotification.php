<?php

namespace App\Notifications;

use Carbon\Carbon;
use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class OrderNotification extends Notification
{
    use Queueable;
    public $order;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(Order $order)
    {
        $this->order = $order;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['database'];
    }

    public function toDatabase($notifiable)
    {
        $statusArr = cc('transaction.status');
        if ($notifiable->account_type == "Buyer") {
            $subject = "{$this->order->store->store_name} has received your order";
        } else {
            $subject = "You have a new order from {$this->order->customer->name}";
        }
        return [
            'subject' => $subject,
            'url' => '/order?' . $this->order->orderRef,
            'orderId' => $this->order->id,
            'status' => $statusArr[$this->order->status],
            'cost' => $this->order->totalprice,
            'shipping' => $this->order->shipping,
            'total' => $this->order->total,
            'paymentRef' => $this->order->paymentRef,
            'orderRef' => $this->order->orderRef,
            'delivery_type'   => $this->order->delivery_type,
            'orderDate' => Carbon::parse($this->order->created_at)->format('M d, Y'),
            'timeAGo' => Carbon::parse($this->order->created_at)->diffForHumans(),
            'orderItems' => $this->order->orderItems,
        ];
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            //
        ];
    }
}
