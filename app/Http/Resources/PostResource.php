<?php

namespace App\Http\Resources;

class PostResource extends JsonResource
{
    public function data($request)
    {
        return [
            'id'           => $this->id,
            'title'        => $this->title,
            'desc'         => $this->desc,
            'content'      => $this->content,
            'html_content' => $this->html_content,
            'hidden'       => $this->hidden,
            'deleted_at'   => $this->deleted_at,
            'created_at'   => $this->created_at,
            'updated_at'   => $this->updated_at,
            'tags'         => TagResource::collection($this->whenLoaded('tags')),
        ];
    }
}
