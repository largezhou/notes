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
        dd(noneProtocol(asset('/uploads/5fa998e40113ad8c563040e44c182d20.png')));
    }
}
