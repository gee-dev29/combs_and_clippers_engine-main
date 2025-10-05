<?php

namespace App\Notifications;

use Carbon\Carbon;
use App\Models\Appointment;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class AppointmentNotification extends Notification
{
    use Queueable;
    public $appointment;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(Appointment $appointment)
    {
        $this->appointment = $appointment;
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
        //return $this->appointment;
        $statusArr = cc('transaction.status');
        
        if ($notifiable->account_type == "Client") {
            $subject = "{$this->appointment->store->store_name} has received your appointment";
        } else {
            $subject = "You have a new appointment from {$this->appointment->customer->name}";
        }
        
        $services = $this->appointment->appointmentService->map(function ($appService) {
            return $appService->service;
        });
        return [
            'subject' => $subject,
            'url' => '/booking?' . $this->appointment->orderRef,
            'appointmentId' => $this->appointment->id,
            'status' => $this->appointment->status,
            'phone_number' => $this->appointment->phone_number,
            'paymentRef' => $this->appointment->payment_ref,
            'appointmentRef' => $this->appointment->appointment_ref,
            'appointmentDate' => Carbon::parse($this->appointment->date)->format('M d, Y'),
            'appointmentTime' => $this->appointment->time,
            'bookingDate' => Carbon::parse($this->appointment->created_at)->format('M d, Y'),
            'timeAGo' => Carbon::parse($this->appointment->created_at)->diffForHumans(),
            'services' => $services,
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
