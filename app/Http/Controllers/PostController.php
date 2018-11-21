<?php

namespace App\Http\Controllers;

use App\Http\Requests\PostRequest;
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

    public function show(Post $post)
    {
        $post->load('tags');

        return PostResource::make($post);
    }

    public function store(PostRequest $request)
    {
        $post = Post::create($request->validated());

        if ($tags = $request->get('tags')) {
            $post->handleSyncTags($tags);
        }

        return $this->created([
            'id' => $post->id,
        ]);
    }
}
