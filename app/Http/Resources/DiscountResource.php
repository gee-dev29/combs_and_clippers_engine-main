<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Carbon\Carbon;

class DiscountResource extends JsonResource
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
            'merchantID' => $this->merchant_id,
            'discount_name' => $this->discount_name,
            'discount_type' => $this->discount_type,
            'discount' => $this->discount,
            'discount_value' => $this->discount_type == "F" ? cc('default_currency') . $this->discount : $this->discount . "% off",
            'start_date' => Carbon::parse($this->start_date)->format('d M, Y'),
            'end_date' =>   Carbon::parse($this->end_date)->format('d M, Y'),
            'discount_products' => $this->products
        ];
    }
}
