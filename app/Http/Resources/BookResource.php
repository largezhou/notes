<?php

namespace App\Http\Resources;

class BookResource extends JsonResource
{
    public function data($request)
    {
        return [
            'id'          => $this->id,
            'title'       => $this->title,
            'started_at'  => date('Y-m-d', strtotime($this->started_at)),
            'created_at'  => $this->created_at,
            'updated_at'  => $this->updated_at,
            'deleted_at'  => $this->deleted_at,
            'hidden'      => $this->hidden,
            'cover'       => $this->cover ? url($this->cover) : '',
            'read'        => $this->read,
            'total'       => $this->total,
            'notes_count' => $this->when($this->notes_count, $this->notes_count),
        ];
    }
}
