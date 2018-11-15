<?php

namespace App\Http\Resources;

class TagResource extends JsonResource
{
    public function data($request)
    {
        return [
            'id'    => $this->id,
            'name'  => $this->name,
            'count' => $this->when(isset($this->targets_count), $this->targets_count),
        ];
    }
}
