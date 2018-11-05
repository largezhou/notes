<?php

namespace App\Http\Controllers;

use App\Http\Resources\NoteResource;
use App\Models\Note;
use Illuminate\Http\Request;

class NoteController extends Controller
{
    public function index()
    {
        $notes = Note::latest()->with('book')->whereHas('book')->paginate();

        return NoteResource::collection($notes)->except(['updated_at', 'created_at', 'content', 'html_content']);
    }
}
