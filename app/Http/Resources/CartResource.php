<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Carbon\Carbon;

class CartResource extends JsonResource
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
      'customerID' => $this->buyer_id,
      'merchantID' => $this->merchant_id,
      'storeName' => !is_null($this->merchant->store) ? $this->merchant->store->store_name :  null,
      'storeIcon' => !is_null($this->merchant->store) ? $this->merchant->store->store_icon :  null,
      'storeBanner' => !is_null($this->merchant->store) ? $this->merchant->store->store_banner :  null,
      'merchantCode' => $this->merchant->merchant_code,
      'cart_id' => $this->id,
      'status' => $this->status,
      'items_count' => $this->items_count,
      'date_created' => Carbon::parse($this->created_at)->format('M d, Y'), //format like this Jun 21, 2018
      'items' => $this->cartItems,
      'max_delivery_period' => $this->max_delivery_period . ' working days',
      'min_delivery_period'  => $this->min_delivery_period . ' working days',
      'totalprice' => $this->totalprice,
      'shipping'   => $this->shipping,
      'delivery_type'   => $this->delivery_type,
      'total_sum'  => $this->total_sum,
    ];
  }
}
