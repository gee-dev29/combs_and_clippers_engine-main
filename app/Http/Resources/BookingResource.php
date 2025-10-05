<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Carbon\Carbon;

class BookingResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $statusArr = cc('transaction.status');
        return [
            'id' => $this->id,
            'payment_gateway' => $this->payment_gateway,
            'payment_ref' => $this->payment_ref,
            'payment_url' => $this->payment_url,
            'booking_ref' => $this->booking_ref,
            'booked_date' => $this->booked_date,
            'booked_time' => $this->booked_time,
            'currency' => $this->currency,
            'booking_price' => $this->booking_price,
            'home_service_fee' => $this->home_service_fee,
            'tax' => $this->tax,
            'total' => $this->total,
            'status' => $statusArr[$this->status],
            'payment_status' => $this->payment_status,
            'disbursement_status' => $this->disbursement_status,
            'payment_type' => $this->payment_type,
            'appointment_type' => $this->appointment_type,
            'reason_for_cancelation' => $this->reason_for_cancelation,
            'customer_details' => $this->customer,
            'service' => $this->service,
            'appointment_location' => $this->appointment_type,
            'location' => !is_null($this->address_id) ? $this->customerAddress : $this->vendorAddress,
            'bookingDate' => Carbon::parse($this->created_at)->format('M d, Y'),  //format like this Jun 21, 2018
        ];
    }
}
