<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Carbon\Carbon;

class OrderResource extends JsonResource
{
  /**
   * Transform the resource into an array.
   *
   * @param  \Illuminate\Http\Request  $request
   * @return array
   */
  public function toArray($request)
  {
    $statusArr = cc('transaction.status');
    return [
      'orderId' => $this->id,
      'address' => !is_null($this->orderAddress) ? $this->orderAddress->street . ' ' .  $this->orderAddress->city . ' ' . $this->orderAddress->state : 'No Address',
      'status' => $statusArr[$this->status],
      'cost' => $this->totalprice,
      'shipping' => $this->shipping,
      'total' => $this->total,
      'paymentRef' => $this->paymentRef,
      'orderRef' => $this->orderRef,
      'delivery_type'   => $this->delivery_type,
      'tracking_code' => $this->tracking_code,
      'confirmation_pin' => $this->confirmation_pin,
      'issues' => $this->issues,
      'date' => Carbon::parse($this->created_at)->format('M d, Y'),
      'customer' => $this->customer,
      'seller' => $this->merchant,
      'orderItems' => $this->orderItems,
      'disputes' => DisputeResource::collection($this->whenLoaded('disputes')),
      'orderLogistics' => $this->orderLogistics,
      'refund_allowed' => !is_null($this->store) ? $this->store->refund_allowed : false,
      'replacement_allowed' => !is_null($this->store) ? $this->store->replacement_allowed : false,
    ];
  }
}
