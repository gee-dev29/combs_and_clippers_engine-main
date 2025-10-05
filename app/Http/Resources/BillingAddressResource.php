<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class BillingAddressResource extends JsonResource
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
        'customerID' => $this->recipient, 
        'address_id' => $this->id,
        'name' => $this->name,  
        'phone' => $this->phone, 
        'email' => $this->email, 
        'address' => $this->address, 
        'formatted_address' => $this->formatted_address, 
        'street' => $this->street, 
        'city' => $this->city, 
        'state' => $this->state, 
        'postal_code' => $this->postal_code, 
        'country' => $this->country, 
        'longitude' => $this->longitude, 
        'latitude' => $this->latitude, 
      ];
   
    }
}