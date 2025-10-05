<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Pagination\LengthAwarePaginator;

class StorePreviewMiniResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'merchant' => !is_null($this->owner) ? $this->owner->name : "",
            'merchant_id' =>  !is_null($this->owner) ? $this->owner->id : "",
            'merchant_email' => !is_null($this->owner) ? $this->owner->email : "",
            'merchant_code' => !is_null($this->owner) ? $this->owner->merchant_code : "",
            'store_name' => $this->store_name,
            'store_type' => $this->store_type,
            'store_phone' => !is_null($this->owner) ? $this->owner->phone : "",
            'store_category' => $this->category,
            'store_sub_category' => $this->subCategory,
            'website' => $this->website,
            'store_icon' => $this->store_icon,
            'store_banner' => $this->store_banner,
            'store_description' => $this->store_description,
            'store_address' => $this->storeAddress,
            'days_available' => !is_null($this->days_available) ? json_decode($this->days_available) : $this->days_available,
            'time_available' => !is_null($this->time_available) ? json_decode($this->time_available) : $this->time_available,
            "payment_preferences" => $this->payment_preferences,
            "booking_preferences" => $this->booking_preferences,
            "availability" => $this->availability,
            "booking_limits" => $this->booking_limits,
            'reviews' => !is_null($this->owner) ? $this->owner->reviewsReceived->count() : 0,
            'star_rating' => !is_null($this->owner) ? $this->owner->reviewsReceived->avg('rating') : 0,
            'star_rating_all' => !is_null($this->owner) ? ReviewsResource::collection($this->owner->reviewsReceived) : [],
            "work_done_images" => $this->workdoneImages->map(function ($image) {
                return [
                    "id" => $image->id,
                    "image_url" => asset('storage/' . $image->image_url),
                ];
            }),
        ];
    }
}