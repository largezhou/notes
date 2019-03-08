<?php

namespace App\Http\Controllers;

use App\Models\ReadRecord;
use Illuminate\Http\Request;

class ReadRecordController extends Controller
{
    public function index(Request $request, ReadRecord $readRecord)
    {
        return $readRecord->getTimeline($request->get('page'));
    }
}
