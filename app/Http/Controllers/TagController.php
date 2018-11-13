<?php

namespace App\Http\Controllers;

use App\Filters\TagFilter;
use App\Http\Resources\TagResource;
use App\Models\Tag;
use Illuminate\Http\Request;

class TagController extends Controller
{
    public function index(TagFilter $tagFilter)
    {
        $tags = Tag::filter($tagFilter)->get();

        return TagResource::collection($tags);
    }
}
