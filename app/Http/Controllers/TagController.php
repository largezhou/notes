<?php

namespace App\Http\Controllers;

use App\Filters\TagFilter;
use App\Http\Requests\TagRequest;
use App\Http\Resources\TagResource;
use App\Models\Tag;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Http\Request;

class TagController extends Controller
{
    public function index(TagFilter $tagFilter)
    {
        $tags = Tag::query()
            ->withCount([
                'notes' => function ($query) {
                    $query->whereHas('book');
                },
                'posts',
            ])
            ->orderByRaw('(notes_count + posts_count) desc')
            ->filter($tagFilter)
            ->get();

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

    public function show(Tag $tag)
    {
        $tag->load([
            'notes' => function (MorphToMany $query) {
                $query->whereHas('book');
            },
            'posts',
            'notes.book',
            'notes.tags',
            'posts.tags',
        ]);

        return TagResource::make($tag);
    }
}
