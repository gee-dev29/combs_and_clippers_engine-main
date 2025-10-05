<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class OrderItemResource extends JsonResource
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
        'order_id' => $this->order_id, 
        'status' => 'Active',
        'customer' => $this->customer,  
        'product' => $this->productInfo, 
        'recentTransactions' => $this->productTrnx,
        'totalTransactions' => $this->productTrnx->count(),
        'totalSpent' => $this->productTrnx->sum('amount'),
      ];
    }
}
