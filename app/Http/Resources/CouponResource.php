<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Carbon\Carbon;

class CouponResource extends JsonResource
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
            'merchant_id' => $this->merchant_id,
            'coupon_code' => $this->code,
            'coupon_discount' => $this->discount,
            'coupon_limit' => $this->limit,
            'status' => ($this->status == 1) ? 'Active' : 'Inactive',
            'date_created' => !is_null($this->created_at) ? Carbon::parse($this->created_at)->format('M d, Y') : '',
            'end_date' =>Carbon::parse($this->end_date)->format('M d, Y'), 
            'details' => json_decode($this->details),
            
         ];

    }
}
