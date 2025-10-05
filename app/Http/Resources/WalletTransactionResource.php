<?php

namespace App\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;

class WalletTransactionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'wallet_id' => $this->wallet_id,
            'withdrawal_id' => $this->withdrawal_id,
            'type' => $this->type,
            'transaction_ref' => !is_null($this->withdrawal) ? $this->withdrawal->transferRef : $this->transaction_ref,
            'narration' => $this->narration,
            'currency' => $this->currency,
            'amount' => $this->amount,
            'status' => $this->status,
            'from_account_no' => $this->from_account_no,
            'from_account_name' => $this->from_account_name,
            'from_bank_name' => $this->from_bank_name,
            'from_bank_code' => $this->from_bank_code,
            'to_account_no' =>  $this->to_account_no,
            'to_account_name' => $this->to_account_name,
            'to_bank_name' => $this->to_bank_name,
            'to_bank_code' => $this->to_bank_code,
            'created_at' => Carbon::parse($this->created_at)->format('Y-m-d H:i:s'),
            'updated_at' => Carbon::parse($this->updated_at)->format('Y-m-d H:i:s'),
            'hasDispute' => $this->hasDispute(),
        ];
    }
}
