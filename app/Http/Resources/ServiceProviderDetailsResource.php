<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ServiceProviderDetailsResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'merchant_code' => $this->merchabt_code,
            'name' => $this->name,
            'avg_rating' => $this->reviewsReceived->avg('rating') ?? 0,
            'reviews_count' => $this->reviewsReceived->count(),
            'about' => $this->bio,
            'phone' => $this->phone,
            'joined' => $this->created_at,
            'services' => ServiceResource::collection($this->services),
            'reviews' => ReviewsResource::collection($this->reviewsReceived),
            'store' => $this->active_store ? [
                'id' => $this->active_store->id,
                'name' => $this->active_store->name,
                'address' => $this->active_store->storeAddress ? [
                    'id' => $this->active_store->storeAddress->id,
                    'street' => $this->active_store->storeAddress->street,
                    'city' => $this->active_store->storeAddress->city,
                    'state' => $this->active_store->storeAddress->state,
                    'zip_code' => $this->active_store->storeAddress->zip_code,
                    'full_address' => $this->active_store->storeAddress->address,
                    'days_available' => !is_null($this->active_store->days_available) ? json_decode($this->active_store->days_available) : $this->days_available,
                    'time_available' => !is_null($this->active_store->time_available) ? json_decode($this->active_store->time_available) : $this->time_available,
                ] : null,
            ] : null,
            'nearby_service_providers' => ServiceProviderPreviewResource::collection($this->nearbyServiceProviders ?? collect()),
            'joined_store' => $this->getCurrentStore(),

        ];
    }

    private function getCurrentStore()
    {
        $userStore = $this->userStores()->where('current', true)->first();
        return $userStore ? new StorePreviewMiniResource($userStore->store) : null;
    }
}