<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class DisputeResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
      $disputes = cc('disputes.status');
      return [
        'disputeID' => $this->id, 
        'orderID' => $this->order_id,
        'dispute_referenceid' => $this->dispute_referenceid, 
        'customer_email' => $this->customer_email, 
        'merchant_email' => $this->merchant_email, 
        'dispute_category' => $this->dispute_category, 
        'dispute_description' => $this->dispute_description, 
        'dispute_option' => $this->dispute_option, 
        'dispute_status' => $disputes[$this->dispute_status],  
        'comment' => $this->comment,
        'resolution_date' => $this->resolution_date,
        'disputeFiles' => $this->disputeFiles, 
      ];  
    }
}
