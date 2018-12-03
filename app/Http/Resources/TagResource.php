<?php

namespace App\Http\Resources;

class TagResource extends JsonResource
{
    public function data($request)
    {
        $counts = null;
        if (isset($this->notes_count) && isset($this->posts_count)) {
            $counts = $this->notes_count + $this->posts_count;
        }

        return [
            'id'    => $this->id,
            'name'  => $this->name,
            'count' => $this->when(isset($counts), $counts),
            'notes' => NoteResource::collection($this->whenLoaded('notes'))
                ->except(['content', 'html_content', 'created_at', 'deleted_at']),
            'posts' => PostResource::collection($this->whenLoaded('posts'))
                ->except(['content', 'html_content', 'created_at', 'deleted_at']),
        ];
    }
}
