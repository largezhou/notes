<?php

namespace App\Http\Resources;

class UserResource extends JsonResource
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
            'name' => $this->username,
        ];
    }
}
