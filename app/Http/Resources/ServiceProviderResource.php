<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Carbon\Carbon;

class ServiceProviderResource extends JsonResource
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
            'name' => $this->name,
            'firstName' => $this->firstName,
            'lastName' => $this->lastName,
            'phone' => $this->phone,
            'email' => $this->email,
            'bio' => $this->bio,
            'account_type' => $this->account_type,
            'specialization' => $this->specialization,
            'merchantCode' => $this->merchant_code,
            'referral_code' => $this->referral_code,
            'profile_image_link' => !is_null($this->profile_image_link) ? asset('storage/' . $this->profile_image_link) : "",
            'cover_image_link' => !is_null($this->cover_image_link) ? asset('storage/' . $this->cover_image_link) : "",
            'location' => $this->store_address,
            "work_done_images" => !is_null($this->store) ? $this->store->workdoneImages->map(function ($image) {
                return [
                    "id" => $image->id,
                    "image_url" => asset('storage/' . $image->image_url),
                ];
            }) : [],
            'avg_rating' => $this->reviewsReceived->avg('rating') ?? 0,
            'ratings_count' => $this->reviewsReceived->count(),
            'reviews_count' => $this->reviewsReceived->count(),
            'bookings_count' => $this->bookings_count,
            'store' => new StoreDetailsResource($this->whenLoaded('store')),
            'created_at' => Carbon::parse($this->created_at)->format('M d, Y / h:i:s'),
            'updated_at' => Carbon::parse($this->updated_at)->format('M d, Y / h:i:s'),
            //'starting_price' => $this->lowestPricedService()->price
            'joined_store' => $this->getCurrentStore(),
            'distance_km' => $this->distance_km ?? null,
            'distance_miles' => $this->distance_miles ?? null,

        ];

        return $data;

    }


    private function getCurrentStore()
    {
        $userStore = $this->userStores()->where('current', true)->first();
        return $userStore ? new StorePreviewMiniResource($userStore->store) : null;
    }


}