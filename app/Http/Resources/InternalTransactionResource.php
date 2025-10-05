<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Carbon\Carbon;

class InternalTransactionResource extends JsonResource
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
            'customer' => $this->customer,
            'transaction_ref' => $this->transaction_ref,
            'narration' => $this->narration,
            'currency' => $this->currency,
            'amount' => $this->amount,
            'status' => $this->payment_status,
            'date' => Carbon::parse($this->created_at)->format('D M d, Y h:ia')  //format like this Mon Jun 21, 2018 02:20pm
        ];
    }
}
