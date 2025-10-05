<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Carbon\Carbon;

class ServiceResource extends JsonResource
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
            'serviceName' => $this->name,
            'serviceDescription' => $this->description,
            'merchantID' => $this->merchant->id,
            'merchant_email' => $this->merchant_email,
            'merchant_phone' => $this->merchant->phone,
            'status' => $this->status,
            'image_url' => $this->image_url,
            'other_images' => $this->photos,
            'store_id' => $this->store_id,
            'currency' => $this->currency,
            'amount' => $this->price,
            'slug' => $this->slug,
            'price_type' => $this->price_type,
            'duration' => $this->duration,
            'buffer' => $this->buffer,
            'payment_preference' => $this->payment_preference,
            'deposit' => $this->deposit,
            'location' => $this->location,
            'home_service_charge' => $this->home_service_charge,
            'allow_cancellation' => $this->allow_cancellation,
            'allowed_cancellation_period' => $this->allowed_cancellation_period,
            'allow_rescheduling' => $this->allow_rescheduling,
            'allowed_rescheduling_period' => $this->allowed_rescheduling_period,
            'booking_reminder' => $this->booking_reminder,
            'booking_reminder_period' => $this->booking_reminder_period,
            'limit_early_booking' => $this->limit_early_booking,
            'early_booking_limit_period' => $this->early_booking_limit_period,
            'limit_late_booking' => $this->limit_late_booking,
            'late_booking_limit_period' => $this->late_booking_limit_period,
            'checkout_label' => $this->checkout_label,
            'availability_hours' => $this->availabilityHours->map(function ($availability) {
                return [
                    'day' => $availability->day,
                    'start_time' => $availability->start_time,
                    'end_time' => $availability->end_time,
                ];
            }),
            'promo' => $this->promotions->map(function ($promotion) {
                return [
                    'discount_amount' => $promotion->discount_amount,
                    'start_date' => $promotion->start_date,
                    'end_date' => $promotion->end_date,
                    'status' => $promotion->status,
                ];
            }),
            'created_at' =>  Carbon::parse($this->created_at)->format('M d, Y / h:i:s'),
            'updated_at' =>  Carbon::parse($this->updated_at)->format('M d, Y / h:i:s'),
        ];
    }
}