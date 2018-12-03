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
        dd($path);
    }
}
