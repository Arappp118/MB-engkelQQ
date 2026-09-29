<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class NotificationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'          => $this->id,
            'title'       => $this->title,
            'message'     => $this->message,
            'type'        => $this->type,
            'entity_type' => $this->entity_type,
            'entity_id'   => $this->entity_id,
            'read_at'     => $this->read_at,
            'created_at'  => $this->created_at,
        ];
    }
}
