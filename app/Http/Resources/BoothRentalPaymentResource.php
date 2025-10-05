<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class BoothRentalPaymentResource extends JsonResource
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
            'booth_rental_id' => $this->booth_rental_id,
            'payment_status' => $this->payment_status,
            'next_payment_date' => $this->next_payment_date->format('D d M'),
            'amount' => $this->boothRental->amount,
            "processing_fee" => $this->processing_fee,
            'virtualAccount' => $this->virtualAccount,
            'service_provider_name' => $this->userStore->user->name,
            'service_provider_id' => $this->userStore->user->id,
            'profile_image_link' => !is_null($this->userStore->user->profile_image_link) ? asset('storage/' . $this->userStore->user->profile_image_link) : "",
            'cover_image_link' => !is_null($this->userStore->user->cover_image_link) ? asset('storage/' . $this->userStore->user->cover_image_link) : "",
            'service_offering' => !is_null($this->userStore->userStoreServiceType) ? $this->userStore->userStoreServiceType->serviceType->name : "",
            'service_type_id' => !is_null($this->userStore->userStoreServiceType) ? $this->userStore->userStoreServiceType->serviceType->id : "",
        ];
    }
}