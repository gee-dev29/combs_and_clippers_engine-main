<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ReviewsResource extends JsonResource
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
            "id" => $this->id,
            "reviewedby" => $this->reviewer->name,
            "reviewer_image_link" => !is_null($this->reviewer->profile_image_link) ? asset('storage/' . $this->reviewer->profile_image_link) : "",
            "date_reviewed" => $this->created_at,
            "review_text" => $this->review_text,
            "rating" => $this->rating

        ];
    }
}