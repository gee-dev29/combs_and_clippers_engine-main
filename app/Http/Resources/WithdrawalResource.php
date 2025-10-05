<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Carbon\Carbon;

class WithdrawalResource extends JsonResource
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
            'transaction_ref' => $this->transaction_ref,
            'currency' => $this->currency,
            'amount' => $this->amount,
            'status' => $this->status,
            'date' => Carbon::parse($this->created_at)->format('D M d, Y h:ia')  //format like this Mon Jun 21, 2018 02:20pm
        ];
    }
}
