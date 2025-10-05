<?php
namespace App\Repositories;

use Illuminate\Support\Facades\Log as Logger;
use Notification;
use Carbon\Carbon;
//use Illuminate\Notifications\Notification;
use App\Notifications\OrderNotification;
use App\Notifications\GeneralNotification;

class NotificationUtils 
{
    
    public function __construct()
    {
        
    }

    public function orderNotification($user, $order, $subject){
      
      $details = [
              'orderRef' => $order->orderRef,
              'orderID' => $order->id,
              'subject' => $subject,
              'orderDesc' =>  $order->description,
              'ref' => $order->orderRef,
              'url' => '/order?' . $order->orderRef,
      ];

      //Notification::send($users, new OrderNotification($details));

      $user->notify(new OrderNotification($details));
    }

    public function orderNotificationToUsers($users, $order, $subject){
      
      $details = [
              'orderRef' => $order->orderRef,
              'orderID' => $order->id,
              'subject' => $subject,
              'orderDesc' =>  $order->description,
              'ref' => $order->orderRef,
              'url' => '/order?' . $order->orderRef,
      ];

      Notification::send($users, new OrderNotification($details));

      // $user->notify(new \App\Notifications\TaskComplete($details));
    }

    public function appointmentNotificationToUsers($users, $appointment, $subject){
      
        $statusArr = cc('transaction.status');
        
        // if ($notifiable->account_type == "Client") {
        //     $subject = "{$this->appointment->store->store_name} has received your appointment";
        // } else {
        //     $subject = "You have a new appointment from {$this->appointment->customer->name}";
        // }
        
        $services = $appointment->appointmentService->map(function ($appService) {
            return $appService->service;
        });
        $details = [
            'subject' => $subject,
            'url' => '/booking?' . $appointment->orderRef,
            'appointmentId' => $appointment->id,
            'status' => $appointment->status,
            'phone_number' => $appointment->phone_number,
            'paymentRef' => $appointment->payment_ref,
            'appointmentRef' => $appointment->appointment_ref,
            'appointmentDate' => Carbon::parse($appointment->date)->format('M d, Y'),
            'appointmentTime' => $appointment->time,
            'bookingDate' => Carbon::parse($appointment->created_at)->format('M d, Y'),
            'timeAGo' => Carbon::parse($appointment->created_at)->diffForHumans(),
            'services' => $services,
        ];
  
        Notification::send($users, new GeneralNotification($details));
    }

    public function boothRentPaymentNotificationToUsers($users, $boothRentalPayment, $subject){
      
        $details = [
            'subject' => $subject,
            'url' => '/booth-rent?' . $boothRentalPayment->id,
            'boothRentalPaymentId' => $boothRentalPayment->id,
            'paymentStatus' => $boothRentalPayment->payment_status,
            'cost' => $boothRentalPayment->boothRental->amount,
            'payment_days' => $boothRentalPayment->boothRental->payment_days,
            'last_payment_date' => Carbon::parse($boothRentalPayment->last_payment_date)->format('M d, Y'),
            'next_payment_date' => Carbon::parse($boothRentalPayment->next_payment_date)->format('M d, Y'),
            'payment_date' => Carbon::parse($boothRentalPayment->created_at)->format('M d, Y'),
        ];
  
        Notification::send($users, new GeneralNotification($details));
    }

}