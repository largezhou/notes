<?php

namespace App\Http\Controllers;

class BackupController extends Controller
{
    public function store()
    {
        \Artisan::call('backup:run');

        return $this->noContent();
    }
}
