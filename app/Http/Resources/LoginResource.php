<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class LoginResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'ip_address' => $this->ip_address,
            'user_agent' => $this->user_agent,
            'login_at' => !is_null($this->login_at) ? $this->login_at->toDayDateTimeString() : null,
            'login_successful' => $this->login_successful,
            'logout_at' =>!is_null($this->logout_at) ? $this->logout_at->toDayDateTimeString() : null,
            'location' => $this->location,
        ];
    }
}
