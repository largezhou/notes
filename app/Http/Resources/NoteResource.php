<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\MissingValue;

class NoteResource extends JsonResource
{
    public function data($request)
    {
        $book = $this->whenLoaded('book');

        return [
            'id' => $this->id,
            'page' => $this->page,
            'title' => $this->title,
            'content' => $this->content,
            'html_content' => $this->html_content,
            'hidden' => $this->hidden,
            'deleted_at' => $this->deleted_at,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'book' => $book ? BookResource::make($book)->only(['id', 'title', 'deleted_at', 'hidden']) : new MissingValue(),
            'tags' => TagResource::collection($this->whenLoaded('tags')),
        ];
    }
}
