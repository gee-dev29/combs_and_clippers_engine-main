<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ThreadResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'thread_id' => $this->id,
            'messages' => MessageResource::collection($this->messages),
        ];
    }
}
