<?php

namespace App\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;

class CustomerDetailsResource extends JsonResource
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
            'profile_image_link' => !is_null($this->profile_image_link) ? asset('storage/' . $this->profile_image_link) : "",
            'cover_image_link' => !is_null($this->cover_image_link) ? asset('storage/' . $this->cover_image_link) : "",
            'created_at' => Carbon::parse($this->created_at)->format('d M Y'),
            'totalItems' => $this->purchaseTrnx->sum('quantity'),
            'totalSpent' => $this->purchases->sum('totalprice'),
            'appointments' => AppointmentResource::collection($this->appointments),
        ];
    }
}
