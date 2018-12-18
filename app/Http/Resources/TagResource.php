<?php

namespace App\Http\Resources;

class TagResource extends JsonResource
{
    public function data($request)
    {
        return [
            'id'            => $this->id,
            'name'          => $this->name,
            'notes'         => NoteResource::collection($this->whenLoaded('notes'))
                ->except(['content', 'html_content', 'created_at']),
            'posts'         => PostResource::collection($this->whenLoaded('posts'))
                ->except(['content', 'html_content', 'created_at']),
            'targets_count' => $this->when(isset($this->targets_count), $this->targets_count),
        ];
    }
}
