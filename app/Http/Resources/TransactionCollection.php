<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;

class TransactionCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'data' => TransactionResource::collection($this->collection),
            'meta' => [
                'total_count' => $this->count(),
                'filters_available' => ['all', 'pending', 'processed'],
                'current_filter' => $request->input('filter', 'all'),
            ],
        ];
    }
}