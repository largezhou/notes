<?php

namespace App\Http\Controllers;

use App\Models\Tag;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class TestController extends Controller
{
    public function test(Request $request, $path = null)
    {
        Tag::truncate();
        $tags = factory(Tag::class, 50)->create()->pluck('id');
        dd($tags);
    }
}
