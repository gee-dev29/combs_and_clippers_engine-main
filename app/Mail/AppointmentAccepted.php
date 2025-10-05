<?php

namespace App\Mail;

use App\Models\User;
use App\Models\Store;
use App\Models\Appointment;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Contracts\Queue\ShouldBeUnique;

class AppointmentAccepted extends Mailable implements ShouldQueue, ShouldBeUnique
{
    use Queueable, SerializesModels;

    public $client;
    public $appointment;
    public $merchant;
    public $store;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(User $client, Appointment $appointment, User $merchant, Store $store)
    {
        $this->client = $client;
        $this->appointment = $appointment;
        $this->merchant = $merchant;
        $this->store = $store;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $app_name = env("APP_NAME");
        $shopName = $this->merchant->shop_name ?? $this->merchant->name ?? 'the stylist';
        
        return $this->subject("Appointment Accepted - {$app_name}")
            ->view('emails.appointment-accepted');
    }
}