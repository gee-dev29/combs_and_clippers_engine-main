<?php

namespace App\Http\Resources;

use Carbon\Carbon;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Http\Resources\Json\JsonResource;

class StoreResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'merchant' => $this->owner->name,
            'merchant_id' => $this->owner->id,
            'merchant_email' => $this->owner->email,
            'merchant_code' => $this->owner->merchant_code,
            'contact_number' => $this->owner->phone,
            'store_name' => $this->store_name,
            "store_booths" => BoothRentalResource::collection($this->boothRent),
            'store_type' => $this->store_type,
            'store_category' => $this->category,
            'store_sub_category' => $this->subCategory,
            'website' => $this->website,
            'store_icon' => $this->store_icon,
            'store_banner' => $this->store_banner,
            'store_description' => $this->store_description,
            'store_phone' => $this->owner->phone,
            'days_available' => !is_null($this->days_available) ? json_decode($this->days_available) : $this->days_available,
            'time_available' => !is_null($this->time_available) ? json_decode($this->time_available) : $this->time_available,
            'refund_allowed' => $this->refund_allowed,
            'replacement_allowed' => $this->replacement_allowed,
            'pickup_address' => $this->owner->pickup_address,
            'store_address' => $this->owner->store_address,
            'joined_on' => Carbon::parse($this->owner->created_at)->format('d M Y'),
            'totalItemsSold' => $this->owner->orderTrnx->sum('quantity'),
            'customers' => $this->owner->customers->count(),
            'reviews' => $this->owner->reviewsReceived->count(),
            'star_rating' => $this->owner->reviewsReceived->avg('rating') ?? 0,
            'star_rating_all' => $this->owner->reviewsReceived,
            'bookings' => $this->bookings->count(),
            'starting_price' => !is_null($this->lowestPricedService()) ? $this->lowestPricedService()->price : 0,
            "payment_preferences" => $this->payment_preferences,
            "booking_preferences" => $this->booking_preferences,
            "availability" => $this->availability,
            "booking_limits" => $this->booking_limits,
            "work_done_images" => $this->workdoneImages->map(function ($image) {
                return [
                    "id" => $image->id,
                    "image_url" => asset('storage/' . $image->image_url),
                ];
            }),
            "has_add_schedule" => !empty($this->days_available),
            "has_setup_service" => !empty($this->owner->services),
            "has_setup_portfolio" => !empty($this->workdoneImages),
            "has_create_bio" => !empty($this->owner->bio),
            "has_accept_payment" => in_array($this->payment_preferences['payment_preference'] ?? '', ['in_app', 'in app']),
            "has_create_profile_link" => !empty($this->owner->merchant_code),
            "has_setup_referal_reward" => !empty($this->rewards['referral_reward']),
            "has_setup_loyalty_reward" => !empty($this->rewards['loyalty_reward']),
            "has_schedule_protection" => !empty($this->owner->store_address) && !empty($this->days_available),


        ];
    }
}