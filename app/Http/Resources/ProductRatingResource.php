<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Carbon\Carbon;

class ProductRatingResource extends JsonResource
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
            'rater' => $this->rater->name,
            'rating' => $this->rating,
            'title' => $this->title,
            'description' => $this->description,
            'rating_date' => Carbon::parse($this->created_at)->format('d-M-Y'),
        ];
    }
}
