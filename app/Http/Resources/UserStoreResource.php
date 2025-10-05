<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class UserStoreResource extends JsonResource
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
            "id" => $this->id,
            "name" => $this->user->name,
            "user_id" => $this->user->id,
            "merchant_code" => $this->user->merchant_code,
            "specialization" => $this->user->specialization,
            "available" => $this->available_status,
            "current" => $this->current,
            "service_type_id" => !is_null($this->userStoreServiceType) ? $this->userStoreServiceType->serviceType->id : "",
            "service_type" => !is_null($this->userStoreServiceType) ? $this->userStoreServiceType->serviceType->name : "",
            'star_rating' => $this->user->reviewsReceived->avg('rating') ?? 0,
            'rentedBooths' => $this->user->rentedBooths,
            'profile_image_link' => !is_null($this->user->profile_image_link) ? asset('storage/' . $this->user->profile_image_link) : "",
            'cover_image_link' => !is_null($this->user->cover_image_link) ? asset('storage/' . $this->user->cover_image_link) : "",
        ];
    }
}