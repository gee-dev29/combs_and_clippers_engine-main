<?php

namespace App\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;

class DeliverySettingResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            //'country' => $this->country,
            'region' => $this->region,
            'delivery_time' => $this->delivery_time,
            'currency' => $this->currency,
            'delivery_fee' => $this->delivery_fee,
            'created_at' => Carbon::parse($this->created_at)->format('M d, Y'),
        ];
    }
}
