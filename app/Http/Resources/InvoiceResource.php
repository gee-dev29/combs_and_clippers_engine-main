<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Carbon\Carbon;

class InvoiceResource extends JsonResource
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
          'invoiceID' => $this->id,
          'merchant_id' => $this->merchant_id,
          'customer_id' => $this->customer_id,
          'merchantName' => $this->merchantName,
          'merchantEmail' => $this->merchantEmail,
          'merchantPhone' => $this->merchantPhone,
          'merchantAddress' => $this->customerName,
          'merchantBusinessName' => !is_null($this->merchant) ? $this->merchant->businessname : '',
          'customer_id' => $this->customer_id,
          'customerName' => $this->customerName,
          'customerEmail' => $this->customerEmail,
          'customerPhone' => $this->customerPhone,
          'customerPhone' => $this->customerPhone,
          'customerAddress' => $this->customerAddress,
          'status' => $statusArr[$this->status],
          'invoiceRef' => $this->invoiceRef,
          'confirmed' => $this->confirmed,
          'items_count' => $this->items_count,
          'vat' => $this->vat,
          'totalcost' => $this->totalcost,
          'currency' => $this->currency,
          'deliveryPeriod' => $this->deliveryPeriod,
          'startDate' => Carbon::parse($this->startDate)->format('M d, Y'),
          'endDate' =>  Carbon::parse($this->endDate)->format('M d, Y'),
          'date' => Carbon::parse($this->created_at)->format('M d, Y'),  //format like this Jun 21, 2018
          'invoiceItems' => $this->invoiceItems,
          'invoiceFxItems' => $this->invoiceFxItems,
          'invoiceFiles' => $this->invoiceFiles,
        ];

        //parent::toArray($request);
    }

    


}
