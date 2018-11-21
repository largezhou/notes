<?php

namespace App\Http\Controllers;

use App\Http\Resources\PostResource;
use App\Models\Post;
use Illuminate\Http\Request;

class PostController extends Controller
{
    public function index()
    {
        $posts = Post::latest()
            ->with('tags')
            ->paginate();

        return PostResource::collection($posts)->except(['updated_at', 'created_at', 'content', 'html_content']);
    }
}
