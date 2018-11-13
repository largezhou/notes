<?php

namespace App\Http\Resources;

class TagResource extends JsonResource
{
    public function data($request)
    {
        return [
            'id'   => $this->id,
            'name' => $this->name,
        ];
    }
}
