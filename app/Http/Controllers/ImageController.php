<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ImageController extends Controller
{
    public function store(Request $request)
    {
        $files = $this->handleUploadFile($request);

        return $this->created([
            'src' => '//' . explode('//', asset($files['image']))[1],
        ]);
    }
}
