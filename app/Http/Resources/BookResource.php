<?php

namespace App\Http\Resources;

use Carbon\Carbon;

class BookResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request $request
     *
     * @return array
     */
    public function data($request)
    {
        return [
            'id'         => $this->id,
            'title'      => $this->title,
            'started_at' => Carbon::parse($this->started_at)->format('Y-m-d'),
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
