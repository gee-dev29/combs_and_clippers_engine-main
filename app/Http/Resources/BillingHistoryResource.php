<?php

namespace App\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;

class BillingHistoryResource extends JsonResource
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
            'invoiceID' => $this->id,
            'merchant_id' => $this->merchant_id,
            'invoice_number' => $this->invoice_number,
            'billing_date' => Carbon::parse($this->billing_date)->format('M d, Y'),
            'status' => ($this->status == 1) ? 'Paid' : 'Unpaid',
            'currency' => $this->currency,
            'amount' => $this->amount,
            'plan' => $this->plan,
        ];
    }
}
