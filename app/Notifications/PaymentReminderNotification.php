<?php

namespace App\Notifications;

use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use App\Models\BoothRentalPayment;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class PaymentReminderNotification extends Notification
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
            $subject = "Your rent in {$this->boothRentalPayment->boothRental->store->store_name} is overdue please go and make payment";
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