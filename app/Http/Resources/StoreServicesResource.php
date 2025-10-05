<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class StoreServicesResource extends JsonResource
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
            "service_name" => $this->serviceType->name,
            "service_type_id" => $this->serviceType->id,
            "users" => $this->storeUsers->map(function ($storeUser) {
                return [
                    "id" => $storeUser->user->id,
                    "name" => $storeUser->user->name,
                ];
            }),
            'date_created' => $this->created_at->format('M d, Y'), //->format('D d M')
        ];
    }
}