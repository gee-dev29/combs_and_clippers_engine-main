<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Pagination\LengthAwarePaginator;

class StoreDetailsResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'merchant_id' => $this->merchant_id,
            'store_name' => $this->store_name,
            'store_type' => $this->store_type,
            'store_category' => $this->category,
            'store_phone' => $this->owner->phone,
            "store_booths" => BoothRentalResource::collection($this->boothRent),
            'store_sub_category' => $this->subCategory,
            'website' => $this->website,
            'store_icon' => $this->store_icon,
            'store_banner' => $this->store_banner,
            'store_description' => $this->store_description,
            'featured' => $this->featured,
            'refund_allowed' => $this->refund_allowed,
            'replacement_allowed' => $this->replacement_allowed,
            'approved' => $this->approved,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'days_available' => !is_null($this->days_available) ? json_decode($this->days_available) : $this->days_available,
            'time_available' => !is_null($this->time_available) ? json_decode($this->time_available) : $this->time_available,
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
            // "has_setup_service" => !empty($this->owner->services),
            // "has_setup_service" => !empty($this->owner->services) && count($this->owner->services) > 0,
            "has_setup_service" => (!empty($this->owner->services) && count($this->owner->services) > 0) || 
                      ($this->serviceTypes && $this->serviceTypes->count() > 0),
            // "has_setup_portfolio" => !empty($this->workdoneImages),
            "has_setup_portfolio" => !is_null($this->workdoneImages) && $this->workdoneImages->count() > 0,
            "has_create_bio" => !empty($this->owner->bio),
            "has_accept_payment" => in_array($this->payment_preferences['payment_preference'] ?? '', ['in_app', 'in app']),
            // "has_create_profile_link" => !empty($this->owner->merchant_code),
            "has_create_profile_link" => $this->owner->has_edited_profile_link ?? false,
            "has_setup_referal_reward" => !empty($this->rewards['referral_reward']),
            "has_setup_loyalty_reward" => !empty($this->rewards['loyalty_reward']),
            "has_schedule_protection" => !empty($this->owner->store_address) && !empty($this->days_available),
            // "has_added_staff" => !empty($this->renters),
            "has_added_staff" => $this->renters->count() > 0,

        ];
    }
}