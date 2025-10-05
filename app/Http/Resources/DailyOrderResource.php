<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Carbon\Carbon;

class DailyOrderResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $statusArr = ['Canceled', 'Pending', 'Shipped', 'Delivered'];
        return [
          'orderId' => $this->id,
          'address' => !is_null($this->orderAddress) ? $this->orderAddress->street .' '.  $this->orderAddress->city .' '. $this->orderAddress->state : 'No Address',
          'status' => $statusArr[$this->status],
          'cost' => $this->totalprice,
          'paymentRef' => $this->paymentRef,
          'orderRef' => $this->orderRef,
          'date' => Carbon::parse($this->created_at)->format('M d, Y'),  //format like this Jun 21, 2018 "2021-12-30 09:25" ->format('Y-m-d H:i')
          'orderDate' => Carbon::parse($this->created_at)->format('M d, Y / H:i:s'),
          'customerName' => $this->customer->name,
          'customerEmail' => $this->customer->email,
          'orderItems' => $this->orderItems,

        ];

        //parent::toArray($request);
    }

    


}
