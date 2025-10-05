<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class BankDetails extends JsonResource
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
            "user_id" => $this->user_id,
            "bank_name" => $this->bank_name,
            "account_number" => $this->account_number,
            "routing_number" => $this->routing_number,
            "bank_code" => $this->bank_code,
            "account_name" => $this->account_name,
        ];


    }
}