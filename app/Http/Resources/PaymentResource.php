<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Carbon\Carbon;

class PaymentResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
        'id' => $this->id, 
        'transactionId' => $this->transcode,
        'paymentDescription' => $this->description,
        'status' => $this->payment_status, 
        'amount' => $this->amount,
        'date' => Carbon::parse($this->created_at)->format('M d, Y'),  //format like this Jun 21, 2018 
        'customer' => $this->customer,
        //'statusText' => Carbon::now()->gt(Carbon::parse($this->enddate)) ? 'over due by '. Carbon::now()->diffInDays(Carbon::parse($this->enddate)) . ' days' : 'Due in '. Carbon::now()->diffInDays(Carbon::parse($this->enddate)) . ' days', //format i.e Due in 4 days  
      ];  
    }
}