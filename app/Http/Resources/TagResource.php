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
            'notes' => $this->whenLoaded(
                'notes',
                NoteResource::collection($this->notes)
                    ->except(['content', 'html_content', 'created_at', 'deleted_at'])
            ),
            'posts' => $this->whenLoaded(
                'posts',
                PostResource::collection($this->posts)
                    ->except(['content', 'html_content', 'created_at', 'deleted_at'])
            ),
        ];
    }
}
