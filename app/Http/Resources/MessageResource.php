<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class MessageResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'from' => $this->sender->name,
            'to' => $this->receiver->name,
            'message' => $this->message,
            'attachment' => $this->attachment,
            'thread_id' => $this->thread_id,
            'time' => $this->created_at->diffForHumans()
        ];
    }
}
