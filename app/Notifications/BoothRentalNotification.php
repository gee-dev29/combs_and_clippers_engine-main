<?php

namespace App\Notifications;

use Carbon\Carbon;
use App\Models\BoothRentalPayment;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class BoothRentalNotification extends Notification
{
    use Queueable;
    public $boothRentalPayment;


    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(BoothRentalPayment $boothRentalPayment)
    {
        $this->boothRentalPayment = $boothRentalPayment;
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
        if ($notifiable->account_type == "Stylist") {
            $subject = "{$this->boothRentalPayment->boothRental->store->store_name} has received your rent";
        } else {
            $subject = "You just recieved rent from  {$this->boothRentalPayment->userStore->user->name}";
        }
        return [
            'subject' => $subject,
            'url' => '/booth-rent?' . $this->boothRentalPayment->id,
            'boothRentalPaymentId' => $this->boothRentalPayment->id,
            'paymentStatus' => $this->boothRentalPayment->payment_status,
            'cost' => $this->boothRentalPayment->boothRental->amount,
            'payment_days' => $this->boothRentalPayment->boothRental->payment_days,
            'last_payment_date' => Carbon::parse($this->boothRentalPayment->last_payment_date)->format('M d, Y'),
            'next_payment_date' => Carbon::parse($this->boothRentalPayment->next_payment_date)->format('M d, Y'),
            'payment_date' => Carbon::parse($this->boothRentalPayment->created_at)->format('M d, Y'),
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
