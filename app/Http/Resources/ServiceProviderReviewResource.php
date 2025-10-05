<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Carbon\Carbon;

class ServiceProviderReviewResource extends JsonResource
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
            'reviewer' => $this->reviewer->name,
            'reviewer_pic' => asset('storage/' . $this->reviewer->profile_image_link),
            'rating' => $this->rating,
            'review' => $this->review_text,
            'review_date' => Carbon::parse($this->created_at)->format('d-M-Y'),
        ];
    }
}
