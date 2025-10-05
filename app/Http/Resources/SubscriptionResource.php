<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class SubscriptionResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'type' => $this->type,
            'plan' => $this->plan,
            'description' => $this->description,
            'currency' => $this->currency,
            'price' => $this->price,
            'invoice_period' => $this->invoice_period,
            'invoice_interval' => $this->invoice_interval,
            'trial_period' => $this->trial_period,
            'trial_interval' => $this->trial_interval
        ];
    }
}
