<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class VoucherResource extends JsonResource
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
            'code' => $this->code,
            'id' => $this->id,
            'discount' => $this->discount,
            'user' => $this->user->name,
            'profile_image_link' => !is_null($this->user->profile_image_link) ? asset('storage/' . $this->user->profile_image_link) : "",
            'cover_image_link' => !is_null($this->user->cover_image_link) ? asset('storage/' . $this->user->cover_image_link) : "",
            'merchant_name' => $this->stylist->name,
            'merchant_account' => $this->stylist->account_type,
            'merchant_avg_rating' => $this->stylist->reviewsReceived->avg('rating') ?? 0,
            'voucher_expiry' => $this->expiry_date,
            'state' => $this->getVoucherState(),
        ];
    }

    /**
     * Determine the voucher state.
     *
     * @return string
     */
    private function getVoucherState()
    {
        if ($this->is_used) {
            return 'used';
        }

        if ($this->expiry_date < now()) {
            return 'expired';
        }

        return 'active';
    }
}