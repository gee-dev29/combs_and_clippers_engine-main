<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ServiceProviderPreviewResource extends JsonResource
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
            'service_provier_id' => $this->id,
            'name' => $this->name,
            'merchant_code' => $this->merchant_code,
            'account_type' => $this->account_type,
            'profile_image_link' => !empty($this->profile_image_link) ? asset('storage/' . $this->profile_image_link) : "",
            'cover_image_link' => !is_null($this->cover_image_link) ? asset('storage/' . $this->cover_image_link) : "",
            'avg_rating' => $this->reviewsReceived->avg('rating') ?? 0,
            'bookings' => $this->bookings->count(),
            'favorited_count' => $this->favoritedByUsers->count(),
            'lowest_priced_service' => $this->lowestPricedService->first()->price ?? null,
            "work_done_images" => !is_null($this->store) ? $this->store->workdoneImages->map(function ($image) {
                return [
                    "id" => $image->id,
                    "image_url" => asset('storage/' . $image->image_url),
                ];
            }) : [],
            'joined_store' => $this->getCurrentStore(),

        ];
    }


    private function getCurrentStore()
    {
        $userStore = $this->userStores()->where('current', true)->first();
        return $userStore ? new StorePreviewMiniResource($userStore->store) : null;
    }
}