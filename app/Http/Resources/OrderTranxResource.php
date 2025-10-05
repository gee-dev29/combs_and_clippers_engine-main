<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Carbon\Carbon;

class OrderTranxResource extends JsonResource
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
          'orderID' => $this->id,
          'address' => !is_null($this->orderAddress) ? $this->orderAddress->street .' '.  $this->orderAddress->city .' '. $this->orderAddress->state : 'No Address',
          'status' => $statusArr[$this->status],
          'tracking_code' => $this->tracking_code,
          'issues' => $this->issues,
          'transactionType' => $this->transaction_type,
          'callback' => $this->callback,
          'description' => $this->description,
          'currency' => $this->currency,
          'cost' => $this->cost, //number_format($this->cost,2),
          'totalCost' => $this->total_cost, //number_format($this->total_cost,2),
          'orderRef' => $this->orderRef,
          'ext_tranx_ref' => $this->external_tranx_ref,
          'paymentRef' => $this->paymentRef,
          'payurl' => $this->payurl,
          'buyerID' => !is_null($this->buyer) ? $this->buyer->id : null,
          'buyerName' => !is_null($this->buyer) ? $this->buyer->name : null,
          'buyerEmail' => $this->buyer_email,
          'sellerID' => $this->seller_id,
          'sellerPhone' => $this->seller_phone,
          'sellerEmail' => $this->seller_email,
          'sellerName' => !is_null($this->seller) ? $this->seller->name : $this->seller_email,
          'pay_in_installments' => ($this->pay_in_installments== 1) ? true : false,
          'startDate'  => Carbon::parse($this->start_date)->format('d-m-Y'),
          'endDate'  => Carbon::parse($this->end_date)->format('d-m-Y'),
          'date' => Carbon::parse($this->created_at)->format('M d, Y'),  //format like this Jun 21, 2018
          'last_update'  => Carbon::parse($this->updated_at)->format('d-m-Y'),
          'orderDate' => Carbon::parse($this->created_at)->format('M d, Y / H:i:s'),
          'orderFiles' => $this->orderFiles,
          'orderProofFiles' => $this->orderProofs,
          'diputes' => $this->diputes,
          'deliveryExtension' => $this->deliveryExtension,
          'orderModication' => $this->OrderModification,
          
        ];

    }

    


}
