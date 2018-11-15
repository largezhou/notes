<?php

namespace App\Http\Controllers;

use App\Models\Tag;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class TestController extends Controller
{
    public function test(Request $request, $path = null)
    {
        dd(url('/uploads/e304475b06530d7a514b2353ce2ab3a7.jpg'));
    }
}
