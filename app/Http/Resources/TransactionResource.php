<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class TransactionResource extends JsonResource
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
            'id' => $this['id'],
            'type' => $this['type'],
            'amount' => $this['amount'],
            'charges' => $this['charges'],
            'total' => (float) $this['amount'] + (float) $this['charges'],
            'status' => $this['status'],
            'narration' => $this['narration'],
            'reference' => $this['reference'],
            'currency' => $this['currency'],
            'date' => $this['created_at']->format('Y-m-d'),
            'time' => $this['created_at']->format('H:i:s'),
            'created_at' => $this['created_at']->format('Y-m-d H:i:s'),

            // Conditionally include full details based on request parameter
            'details' => $request->has('include_details') ? $this['details'] : null,
        ];
    }
}