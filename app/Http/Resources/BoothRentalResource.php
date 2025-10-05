<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class BoothRentalResource extends JsonResource
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
            "store_id" => $this->store->id,
            "store_name" => $this->store->store_name,
            "store_address" =>  !is_null($this->store->owner) ? $this->store->owner->store_address : "",
            "store_owner" => $this->store->owner,
            "store" => new StorePreviewMiniResource($this->store),
            'service_provider_name' => !is_null($this->user) ? $this->user->name : "",
            'service_provider_id' => !is_null($this->user) ? $this->user->id : "",
            'profile_image_link' => (!is_null($this->user) && !is_null($this->user->profile_image_link)) ? asset('storage/' . $this->user->profile_image_link) : "",
            'cover_image_link' => (!is_null($this->user) && !is_null($this->user->cover_image_link)) ? asset('storage/' . $this->user->cover_image_link) : "",
            "amount" => $this->amount,
            "invite_code" => $this->invite_code,
            "payment_timeline" => $this->payment_timeline,
            "payment_days" => $this->payment_days,
            "service_type" => !is_null($this->serviceType) ? $this->serviceType->name : "",
            "service_type_id" => !is_null($this->serviceType) ? $this->serviceType->id : ""
        ];
    }
}