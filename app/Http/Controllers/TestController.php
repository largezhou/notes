<?php

namespace App\Http\Controllers;

use App\Models\BaseNote;
use App\Models\Note;
use App\Models\Post;
use App\Models\Tag;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Collection;

class TestController extends Controller
{
    public function test(Request $request, $path = null)
    {
        $tag = Tag::find(1);

        $items = $tag->baseNotes->all();

        $items = array_map(function (BaseNote $baseNote) {
            if ($baseNote->book_id) {
                return new Note($baseNote->toArray());
            } else {
                return new Post($baseNote->toArray());
            }
        }, $items);

        dd($tag->toArray(), $items);
    }
}
