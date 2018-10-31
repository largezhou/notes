<?php

namespace App\Http\Resources;

use Carbon\Carbon;

class BookResource extends JsonResource
{
    public function data($request)
    {
        return [
            'id'         => $this->id,
            'title'      => $this->title,
            'started_at' => $this->started_at,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'deleted_at' => $this->deleted_at,
            'hidden'     => $this->hidden,
            'cover'      => $this->cover,
            'read'       => $this->read,
            'total'      => $this->total,
        ];
    }
}
