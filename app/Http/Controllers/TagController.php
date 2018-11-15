<?php

namespace App\Http\Controllers;

use App\Filters\TagFilter;
use App\Http\Requests\TagRequest;
use App\Http\Resources\TagResource;
use App\Models\Tag;
use Illuminate\Http\Request;

class TagController extends Controller
{
    public function index(TagFilter $tagFilter)
    {
        $tags = Tag::withCount('targets')->orderBy('targets_count', 'desc')->filter($tagFilter)->get();

        return TagResource::collection($tags);
    }

    public function destroy(Tag $tag)
    {
        $tag->delete();

        return $this->noContent();
    }

    public function update(TagRequest $request, Tag $tag)
    {
        $tag->update($request->all());

        return $this->noContent();
    }
}
