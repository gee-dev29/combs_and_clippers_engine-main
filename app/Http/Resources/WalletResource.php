<?php

namespace App\Http\Resources;

use App\Models\Bank;
use Illuminate\Http\Resources\Json\JsonResource;

class WalletResource extends JsonResource
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
            'id'      => $this->id,
            'user_id'    => $this->user_id,
            'wallet_number'    => $this->wallet_number,
            'currency'   => $this->currency,
            'amount'   => $this->amount,
            'unclaimed_amount' => $this->unclaimed_amount,
            'account_number' => $this->account_number,
            'account_name' => !is_null($this->user) ? $this->user->name : "",
            'bank_code' => $this->bank_code,
            'bank_name' => !is_null($this->bank) ? $this->bank->bank : '',
            'provider' => $this->bank_code == Bank::WEMA ? 'mozfin' : 'vfd',
        ];
    }
}
