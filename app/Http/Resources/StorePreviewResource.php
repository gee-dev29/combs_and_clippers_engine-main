<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Pagination\LengthAwarePaginator;

class StorePreviewResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            // 'owner' => !is_null($this->owner) ? [
            //     'id' => $this->owner->id,
            //     'name' => $this->owner->name,
            //     'first_name' => $this->owner->firstName,
            //     'last_name' => $this->owner->lastName,
            //     'email' => $this->owner->email,
            //     'phone' => $this->owner->phone,
            //     'merchant_code' => $this->owner->merchant_code,
            //     'profile_image' => $this->owner->profile_image_link ? asset('storage/' . $this->owner->profile_image_link) : null,
            //     'cover_image' => $this->owner->cover_image_link ? asset('storage/' . $this->owner->cover_image_link) : null,
            //     'bio' => $this->owner->bio,
            //     'specialization' => $this->owner->specialization,
            //     'account_type' => $this->owner->account_type,
            // ] : [],
            'account_type' => 'Owner',
            'merchant_code' => !is_null($this->owner) ? $this->owner->merchant_code : null,
            'profile_image' => !is_null($this->owner) && $this->owner->profile_image_link ? asset('storage/' . $this->owner->profile_image_link) : null,
            "store_booths" => BoothRentalResource::collection($this->boothRent),
            'store_name' => $this->store_name,
            'store_phone' => !is_null($this->owner) ? $this->owner->phone : "",
            'store_type' => $this->store_type,
            'store_category' => $this->category,
            'store_sub_category' => $this->subCategory,
            'store_address' => $this->storeAddress,
            'website' => $this->website,
            'store_icon' => $this->store_icon,
            'store_banner' => $this->store_banner,
            'store_description' => $this->store_description,
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

            'service_types' => $this->serviceTypes->map(function ($storeServiceType) {
                return [
                    'id' => $storeServiceType->id,
                    'service_type_id' => $storeServiceType->service_type_id,
                    'service_type' => [
                        'id' => $storeServiceType->serviceType->id,
                        'name' => $storeServiceType->serviceType->name,
                    ],
                ];
            }),

            // Services Provided by Store
            'services' => $this->services->map(function ($service) {
                return [
                    'id' => $service->id,
                    'name' => $service->name,
                    'description' => $service->description,
                    'slug' => $service->slug,
                    'price' => $service->price,
                    'price_type' => $service->price_type,
                    'currency' => $service->currency,
                    'duration' => $service->duration,
                    'image_url' => $service->image_url ? asset('storage/' . $service->image_url) : null,
                    'status' => $service->status,
                    'is_available' => $service->is_available,
                    'location' => $service->location,
                    'home_service_charge' => $service->home_service_charge,
                ];
            }),
            'distance_km' => $this->distance_km,
            'distance_miles' => $this->distance_miles,
        ];
    }
}