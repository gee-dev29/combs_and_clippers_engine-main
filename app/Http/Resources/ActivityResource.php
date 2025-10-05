<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Carbon\Carbon;

class ActivityResource extends JsonResource
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
          'model' => $this->model,
          'model_uid' => $this->model_uid,
          'description' => $this->description,
          'controller' => $this->controller,
          'action' => $this->action,
          'params' => $this->params,
          'before_action' => $this->before_action,
          'after_action' => $this->after_action,
          'date' => Carbon::parse($this->created_at)->format('M d, Y'),
          'customer' => $this->customer,
        ];
    }
}
